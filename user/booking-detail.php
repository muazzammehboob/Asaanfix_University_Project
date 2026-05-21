<?php
/**
 * AsaanFix Pakistan - Booking Detail (User)
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$bookingId = (int)($_GET['id'] ?? 0);
$userId = getCurrentUserId();

$booking = dbQueryOne(
    "SELECT b.*, s.name as service_name, s.slug as service_slug, c.name as category_name,
            u2.name as tech_name, u2.avatar as tech_avatar, u2.phone as tech_phone, u2.city as tech_city,
            t.avg_rating as tech_rating, t.id as tech_profile_id
     FROM bookings b 
     JOIN services s ON b.service_id = s.id 
     JOIN categories c ON s.category_id = c.id
     JOIN technicians t ON b.technician_id = t.id 
     JOIN users u2 ON t.user_id = u2.id 
     WHERE b.id = ? AND b.user_id = ?", [$bookingId, $userId]
);

if (!$booking) { setFlash('danger', 'Booking not found.'); redirect(APP_URL . '/user/bookings.php'); }

// Status history
$history = dbQuery("SELECT bsh.*, u.name as changed_by_name FROM booking_status_history bsh LEFT JOIN users u ON bsh.changed_by = u.id WHERE bsh.booking_id = ? ORDER BY bsh.created_at ASC", [$bookingId]);

// Payment
$payment = dbQueryOne("SELECT * FROM payments WHERE booking_id = ?", [$bookingId]);

// Review
$review = dbQueryOne("SELECT * FROM reviews WHERE booking_id = ? AND user_id = ?", [$bookingId, $userId]);

// Handle cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    requireCSRF();
    if ($_POST['action'] === 'cancel' && in_array($booking['status'], ['pending','confirmed'])) {
        $reason = sanitize($_POST['cancel_reason'] ?? 'Cancelled by user');
        dbExecute("UPDATE bookings SET status = 'cancelled', cancelled_by = 'user', cancel_reason = ? WHERE id = ?", [$reason, $bookingId]);
        dbExecute("INSERT INTO booking_status_history (booking_id, status, notes, changed_by) VALUES (?, 'cancelled', ?, ?)", [$bookingId, $reason, $userId]);
        $techUserId = dbQueryOne("SELECT user_id FROM technicians WHERE id = ?", [$booking['tech_profile_id']])['user_id'];
        createNotification($techUserId, 'Booking Cancelled', "Booking #{$booking['booking_number']} has been cancelled.", 'booking');
        setFlash('info', 'Booking cancelled.');
        redirect(APP_URL . '/user/booking-detail.php?id=' . $bookingId);
    }
    if ($_POST['action'] === 'review' && $booking['status'] === 'completed' && !$review) {
        $rating = max(1, min(5, (int)$_POST['rating']));
        $comment = sanitize($_POST['comment'] ?? '');
        dbExecute("INSERT INTO reviews (booking_id, user_id, technician_id, rating, comment) VALUES (?, ?, ?, ?, ?)",
            [$bookingId, $userId, $booking['tech_profile_id'], $rating, $comment]);
        // Update technician avg rating
        $avgRow = dbQueryOne("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM reviews WHERE technician_id = ?", [$booking['tech_profile_id']]);
        dbExecute("UPDATE technicians SET avg_rating = ?, total_reviews = ? WHERE id = ?", [round($avgRow['avg_r'], 2), $avgRow['cnt'], $booking['tech_profile_id']]);
        setFlash('success', 'Review submitted! Thank you.');
        redirect(APP_URL . '/user/booking-detail.php?id=' . $bookingId);
    }
}

$pageTitle = 'Booking ' . $booking['booking_number'];
$bodyClass = 'dashboard-page';
$currentPage = 'bookings';
$activeBookings = 0;
$sidebarMenu = [
    'Main' => [
        ['page'=>'dashboard','url'=>APP_URL.'/user/dashboard.php','icon'=>'bi-speedometer2','label'=>'Dashboard'],
        ['page'=>'bookings','url'=>APP_URL.'/user/bookings.php','icon'=>'bi-calendar-check','label'=>'My Bookings'],
        ['page'=>'reviews','url'=>APP_URL.'/user/reviews.php','icon'=>'bi-star','label'=>'My Reviews'],
    ],
    'Account' => [
        ['page'=>'profile','url'=>APP_URL.'/user/profile.php','icon'=>'bi-person','label'=>'Profile'],
        ['page'=>'messages','url'=>APP_URL.'/user/messages.php','icon'=>'bi-chat-dots','label'=>'Messages'],
        ['page'=>'notifications','url'=>APP_URL.'/user/notifications.php','icon'=>'bi-bell','label'=>'Notifications'],
    ],
];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>

    <a href="<?= APP_URL ?>/user/bookings.php" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left me-1"></i>Back</a>

    <div class="row g-4">
        <!-- Main -->
        <div class="col-lg-8">
            <div class="card-custom p-4 mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-bold mb-1"><?= $booking['booking_number'] ?></h5>
                        <small class="text-muted">Booked on <?= formatDateTime($booking['created_at']) ?></small>
                    </div>
                    <?= getStatusBadge($booking['status']) ?>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--surface);">
                            <small class="text-muted d-block">Service</small>
                            <strong><?= sanitize($booking['service_name']) ?></strong>
                            <small class="text-muted d-block"><?= sanitize($booking['category_name']) ?></small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--surface);">
                            <small class="text-muted d-block">Schedule</small>
                            <strong><?= formatDate($booking['booking_date']) ?></strong>
                            <small class="text-muted d-block">at <?= formatTime($booking['booking_time']) ?></small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--surface);">
                            <small class="text-muted d-block">Address</small>
                            <strong><?= sanitize($booking['address']) ?></strong>
                            <small class="text-muted d-block"><?= sanitize($booking['city']) ?></small>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--surface);">
                            <small class="text-muted d-block">Amount</small>
                            <strong class="text-primary fs-5"><?= formatPrice($booking['total_amount']) ?></strong>
                            <small class="text-muted d-block">Payment: <?= ucfirst($payment['method'] ?? 'Cash') ?> (<?= ucfirst($payment['status'] ?? 'Pending') ?>)</small>
                        </div>
                    </div>
                </div>

                <?php if ($booking['description']): ?>
                <div class="mb-4">
                    <h6 class="fw-bold">Problem Description</h6>
                    <p class="text-secondary mb-0"><?= nl2br(sanitize($booking['description'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Timeline -->
                <h6 class="fw-bold mb-3">Status Timeline</h6>
                <div class="booking-timeline">
                    <?php foreach ($history as $h): ?>
                    <div class="timeline-item">
                        <div class="timeline-dot <?= $h['status'] === 'completed' ? 'completed' : '' ?>"></div>
                        <div class="ms-2">
                            <strong class="small"><?= ucfirst(str_replace('_',' ',$h['status'])) ?></strong>
                            <span class="text-muted small ms-2"><?= formatDateTime($h['created_at']) ?></span>
                            <?php if ($h['notes']): ?><p class="text-muted small mb-0"><?= sanitize($h['notes']) ?></p><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cancel -->
                <?php if (in_array($booking['status'], ['pending','confirmed'])): ?>
                <div class="mt-4 pt-3 border-top">
                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                        <i class="bi bi-x-circle me-1"></i>Cancel Booking
                    </button>
                </div>
                <!-- Cancel Modal -->
                <div class="modal fade" id="cancelModal" tabindex="-1">
                    <div class="modal-dialog"><div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Cancel Booking</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="cancel">
                            <div class="modal-body">
                                <p>Are you sure you want to cancel booking <strong><?= $booking['booking_number'] ?></strong>?</p>
                                <label class="form-label">Reason (optional)</label>
                                <textarea class="form-control" name="cancel_reason" rows="2"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Keep</button>
                                <button type="submit" class="btn btn-danger">Yes, Cancel</button>
                            </div>
                        </form>
                    </div></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Review Form -->
            <?php if ($booking['status'] === 'completed' && !$review): ?>
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-star me-2"></i>Leave a Review</h5>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="review">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rating</label>
                        <select class="form-select" name="rating" required style="max-width:200px;">
                            <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                            <option value="4">⭐⭐⭐⭐ Good</option>
                            <option value="3">⭐⭐⭐ Average</option>
                            <option value="2">⭐⭐ Poor</option>
                            <option value="1">⭐ Terrible</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Your Review</label>
                        <textarea class="form-control" name="comment" rows="3" placeholder="Share your experience..."></textarea>
                    </div>
                    <button class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Review</button>
                </form>
            </div>
            <?php elseif ($review): ?>
            <div class="card-custom p-4">
                <h6 class="fw-bold mb-2">Your Review</h6>
                <?= getStarRating($review['rating']) ?>
                <p class="mt-2 mb-0"><?= sanitize($review['comment']) ?></p>
                <?php if ($review['reply']): ?>
                <div class="mt-3 p-3 rounded-3" style="background:var(--surface);">
                    <small class="fw-bold">Technician Reply:</small>
                    <p class="mb-0 small"><?= sanitize($review['reply']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card-custom p-4">
                <h6 class="fw-bold mb-3">Technician</h6>
                <div class="text-center mb-3">
                    <img src="<?= getAvatarUrl($booking['tech_avatar'], $booking['tech_name'] ?? '') ?>" width="72" height="72" class="rounded-circle mb-2" style="object-fit:cover;">
                    <h6 class="fw-bold mb-1"><?= sanitize($booking['tech_name']) ?></h6>
                    <p class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i><?= sanitize($booking['tech_city']) ?></p>
                    <?= getStarRating($booking['tech_rating']) ?>
                </div>
                <a href="<?= APP_URL ?>/technician-profile.php?id=<?= $booking['tech_profile_id'] ?>" class="btn btn-outline-primary btn-sm w-100">View Profile</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
