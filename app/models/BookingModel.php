<?php
/**
 * AsaanFix Pakistan - Booking Model
 * Handles all booking-related database operations
 */

require_once __DIR__ . '/Model.php';

class BookingModel extends Model {
    protected string $table = 'bookings';

    /**
     * Get booking with full details
     */
    public function getWithDetails(int $id): ?array {
        return dbQueryOne(
            "SELECT b.*, s.name as service_name, s.base_price, 
                    c.name as category_name, c.icon as category_icon,
                    u.name as user_name, u.email as user_email, u.phone as user_phone,
                    tu.name as tech_name, tu.email as tech_email, tu.phone as tech_phone,
                    t.avg_rating as tech_rating
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             JOIN categories c ON s.category_id = c.id
             JOIN users u ON b.user_id = u.id
             JOIN technicians t ON b.technician_id = t.id
             JOIN users tu ON t.user_id = tu.id
             WHERE b.id = ?",
            [$id]
        );
    }

    /**
     * Update booking status with history tracking
     */
    public function updateStatus(int $bookingId, string $newStatus, ?int $changedBy = null, ?string $notes = null): bool {
        $validStatuses = array_keys(BOOKING_STATUSES);
        if (!in_array($newStatus, $validStatuses)) return false;

        // Update booking
        $updateData = ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')];
        if ($newStatus === 'completed') $updateData['completed_at'] = date('Y-m-d H:i:s');
        
        $this->update($bookingId, $updateData);

        // Record in history
        dbExecute(
            "INSERT INTO booking_status_history (booking_id, status, notes, changed_by) VALUES (?, ?, ?, ?)",
            [$bookingId, $newStatus, $notes, $changedBy]
        );

        // Send notification
        $booking = $this->find($bookingId);
        if ($booking) {
            $statusLabel = BOOKING_STATUSES[$newStatus]['label'] ?? ucfirst($newStatus);
            createNotification(
                $booking['user_id'],
                'Booking Status Updated',
                "Your booking #{$booking['booking_number']} is now: {$statusLabel}",
                'booking',
                APP_URL . '/user/booking-detail?id=' . $bookingId
            );
        }

        return true;
    }

    /**
     * Get status history for a booking
     */
    public function getStatusHistory(int $bookingId): array {
        return dbQuery(
            "SELECT bsh.*, u.name as changed_by_name 
             FROM booking_status_history bsh 
             LEFT JOIN users u ON bsh.changed_by = u.id 
             WHERE bsh.booking_id = ? ORDER BY bsh.created_at ASC",
            [$bookingId]
        );
    }

    /**
     * Get allowed next statuses for workflow
     */
    public function getAllowedTransitions(string $currentStatus): array {
        $transitions = [
            'pending'           => ['accepted', 'cancelled'],
            'accepted'          => ['technician_on_way', 'cancelled'],
            'technician_on_way' => ['in_progress', 'cancelled'],
            'in_progress'       => ['completed', 'disputed'],
            'completed'         => ['refunded', 'disputed'],
            'cancelled'         => ['refunded'],
            'refunded'          => [],
            'disputed'          => ['refunded', 'completed'],
        ];
        return $transitions[$currentStatus] ?? [];
    }
}
