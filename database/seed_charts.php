<?php
/**
 * AsaanFix Pakistan - Seed Chart Demo Data
 * Run once to add realistic data for Chart.js charts
 */
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Fix completed_at for existing completed bookings
$db->exec("UPDATE bookings SET completed_at = '2026-05-10 14:30:00' WHERE booking_number = 'FH-2026-0001' AND status = 'completed'");
$db->exec("UPDATE bookings SET completed_at = '2026-05-11 18:00:00' WHERE booking_number = 'FH-2026-0002' AND status = 'completed'");

// Add more bookings for chart data across multiple months
$extra = [
    ['FH-2026-0006', 2, 3, 11, 'completed', '2026-04-20', '10:00:00', 'F-8 Islamabad', 'Islamabad', '+923011111111', 'Toilet repair', 2500.00, '2026-04-20 13:00:00', '2026-04-18 09:00:00'],
    ['FH-2026-0007', 3, 1, 8, 'completed', '2026-04-25', '11:00:00', 'DHA Lahore', 'Lahore', '+923022222222', 'Switch repair', 1000.00, '2026-04-25 12:30:00', '2026-04-23 10:00:00'],
    ['FH-2026-0008', 4, 4, 2, 'completed', '2026-03-15', '14:00:00', 'Clifton Karachi', 'Karachi', '+923033333333', 'Battery change', 1800.00, '2026-03-15 15:00:00', '2026-03-13 08:00:00'],
    ['FH-2026-0009', 2, 5, 22, 'completed', '2026-03-28', '09:00:00', 'F-8 Islamabad', 'Islamabad', '+923011111111', 'Network setup', 3500.00, '2026-03-28 12:00:00', '2026-03-26 11:00:00'],
    ['FH-2026-0010', 3, 2, 13, 'cancelled', '2026-04-10', '16:00:00', 'DHA Lahore', 'Lahore', '+923022222222', 'AC install', 4000.00, null, '2026-04-08 14:00:00'],
    ['FH-2026-0011', 4, 1, 9, 'completed', '2026-04-05', '10:00:00', 'Clifton Karachi', 'Karachi', '+923033333333', 'Generator fix', 4500.00, '2026-04-05 14:00:00', '2026-04-03 09:00:00'],
    ['FH-2026-0012', 2, 3, 12, 'completed', '2026-05-01', '13:00:00', 'G-9 Islamabad', 'Islamabad', '+923011111111', 'Tank cleaning', 3000.00, '2026-05-01 17:00:00', '2026-04-29 10:00:00'],
    ['FH-2026-0013', 3, 4, 3, 'completed', '2026-02-15', '10:00:00', 'DHA Lahore', 'Lahore', '+923022222222', 'Software fix', 1200.00, '2026-02-15 11:30:00', '2026-02-13 09:00:00'],
    ['FH-2026-0014', 4, 5, 23, 'completed', '2026-02-28', '14:00:00', 'Clifton Karachi', 'Karachi', '+923033333333', 'PC repair', 2500.00, '2026-02-28 16:00:00', '2026-02-26 10:00:00'],
    ['FH-2026-0015', 2, 2, 15, 'completed', '2026-01-20', '11:00:00', 'F-8 Islamabad', 'Islamabad', '+923011111111', 'AC compressor', 4000.00, '2026-01-20 15:00:00', '2026-01-18 09:00:00'],
];

$stmt = $db->prepare("INSERT IGNORE INTO bookings (booking_number, user_id, technician_id, service_id, status, booking_date, booking_time, address, city, phone, description, total_amount, completed_at, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$count = 0;
foreach ($extra as $row) {
    try {
        $stmt->execute($row);
        $count++;
    } catch (Exception $e) {
        // Skip duplicates
    }
}

echo "Done! Added $count new bookings. Total: " . $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn() . "\n";
echo "Completed bookings: " . $db->query("SELECT COUNT(*) FROM bookings WHERE status='completed'")->fetchColumn() . "\n";
echo "With completed_at: " . $db->query("SELECT COUNT(*) FROM bookings WHERE completed_at IS NOT NULL")->fetchColumn() . "\n";
