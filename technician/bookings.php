<?php
/**
 * AsaanFix Pakistan - Technician Bookings Management
 */
require_once __DIR__ . '/../includes/auth.php';
requireTechnician();

$userId = getCurrentUserId();
$tech = getCurrentTechnicianProfile();
$techId = $tech['id'];

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    $booking = dbQueryOne("SELECT * FROM bookings WHERE id = ? AND technician_id = ?", [$bookingId, $techId]);
    if ($booking) {
        $newStatus = null;
        $note = '';
        if ($action === 'accept' && $booking['status'] === 'pending') { $newStatus = 'confirmed'; $note = 'Accepted by technician'; }
        elseif ($action === 'start' && $booking['status'] === 'confirmed') { $newStatus = 'in_progress'; $note = 'Work started'; }
        elseif ($action === 'complete' && $booking['status'] === 'in_progress') { $newStatus = 'completed'; $note = 'Work completed'; }
        elseif ($action === 'reject' && $booking['status'] === 'pending') { $newStatus = 'cancelled'; $note = sanitize($_POST['reason'] ?? 'Rejected by technician'); }
        
        if ($newStatus) {
            dbExecute("UPDATE bookings SET status = ? " . ($newStatus === 'completed' ? ", completed_at = NOW()" : "") . ($newStatus === 'cancelled' ? ", cancelled_by = 'technician', cancel_reason = '$note'" : "") . " WHERE id = ?", [$newStatus, $bookingId]);
            dbExecute("INSERT INTO booking_status_history (booking_id, status, notes, changed_by) VALUES (?, ?, ?, ?)", [$bookingId, $newStatus, $note, $userId]);
            if ($newStatus === 'completed') {
                dbExecute("UPDATE technicians SET total_jobs = total_jobs + 1 WHERE id = ?", [$techId]);
                dbExecute("UPDATE payments SET status = 'completed', paid_at = NOW() WHERE booking_id = ?", [$bookingId]);
            }
            createNotification($booking['user_id'], 'Booking ' . ucfirst(str_replace('_',' ',$newStatus)), "Your booking #{$booking['booking_number']} is now " . str_replace('_',' ',$newStatus) . ".", 'booking', '/user/booking-detail.php?id=' . $bookingId);
            setFlash('success', 'Booking updated to ' . str_replace('_',' ',$newStatus) . '.');
        }
    }
    redirect(APP_URL . '/technician/bookings.php?status=' . ($_GET['status'] ?? ''));
}

$status = sanitize($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$where = ["b.technician_id = ?"];
$params = [$techId];
if ($status) { $where[] = "b.status = ?"; $params[] = $status; }
$whereSQL = implode(' AND ', $where);
$cnt = dbQueryOne("SELECT COUNT(*) as c FROM bookings b WHERE $whereSQL", $params)['c'];
$pagination = getPagination($cnt, $page);

$bookings = dbQuery(
    "SELECT b.*, s.name as service_name, u.name as user_name, u.avatar as user_avatar, u.phone as user_phone
     FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.user_id = u.id
     WHERE $whereSQL ORDER BY FIELD(b.status,'pending','confirmed','in_progress','completed','cancelled'), b.created_at DESC
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}", $params
);

$pendingBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE technician_id = ? AND status = 'pending'", [$techId])['c'];
$pageTitle = 'Manage Bookings';
$bodyClass = 'dashboard-page';
$currentPage = 'bookings';
$sidebarMenu = [
    'Main' => [
        ['page'=>'dashboard','url'=>APP_URL.'/technician/dashboard.php','icon'=>'bi-speedometer2','label'=>'Dashboard'],
        ['page'=>'bookings','url'=>APP_URL.'/technician/bookings.php','icon'=>'bi-calendar-check','label'=>'Bookings','badge'=>$pendingBookings ?: ''],
        ['page'=>'earnings','url'=>APP_URL.'/technician/earnings.php','icon'=>'bi-wallet2','label'=>'Earnings'],
        ['page'=>'reviews','url'=>APP_URL.'/technician/reviews.php','icon'=>'bi-star','label'=>'Reviews'],
    ],
    'Settings' => [
        ['page'=>'profile','url'=>APP_URL.'/technician/profile.php','icon'=>'bi-person-gear','label'=>'Profile & Skills'],
        ['page'=>'availability','url'=>APP_URL.'/technician/availability.php','icon'=>'bi-clock','label'=>'Availability'],
    ],
];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>
    <h4 class="fw-bold mb-4">Manage Bookings</h4>

    <ul class="nav nav-pills mb-4 flex-nowrap overflow-auto">
        <?php foreach (['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $k => $l): ?>
        <li class="nav-item"><a class="nav-link <?= $status===$k?'active':'' ?> text-nowrap" href="?<?= $k?"status=$k":'' ?>"><?= $l ?></a></li>
        <?php endforeach; ?>
    </ul>

    <?php if (empty($bookings)): ?>
    <div class="card-custom p-5 text-center"><i class="bi bi-calendar-x display-3 text-muted"></i><h5 class="mt-3">No bookings found</h5></div>
    <?php else: ?>
    <?php foreach ($bookings as $b): ?>
    <div class="card-custom p-3 mb-3">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <img src="<?= getAvatarUrl($b['user_avatar'], $b['user_name'] ?? '') ?>" width="48" height="48" class="rounded-circle" style="object-fit:cover;">
            </div>
            <div class="col">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="fw-bold text-primary"><?= $b['booking_number'] ?></span>
                    <?= getStatusBadge($b['status']) ?>
                </div>
                <h6 class="mb-0 mt-1 fw-semibold"><?= sanitize($b['service_name']) ?></h6>
                <small class="text-muted">by <?= sanitize($b['user_name']) ?> • <?= sanitize($b['city']) ?> • <i class="bi bi-telephone"></i> <?= sanitize($b['user_phone']) ?></small>
            </div>
            <div class="col-auto text-end">
                <div class="fw-bold"><?= formatPrice($b['total_amount']) ?></div>
                <small class="text-muted"><?= formatDate($b['booking_date']) ?> at <?= formatTime($b['booking_time']) ?></small>
            </div>
            <div class="col-auto">
                <!-- Action Buttons -->
                <?php if ($b['status'] === 'pending'): ?>
                <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><input type="hidden" name="action" value="accept">
                    <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Accept</button></form>
                <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><input type="hidden" name="action" value="reject">
                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button></form>
                <?php elseif ($b['status'] === 'confirmed'): ?>
                <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><input type="hidden" name="action" value="start">
                    <button class="btn btn-sm btn-primary"><i class="bi bi-play me-1"></i>Start</button></form>
                <?php elseif ($b['status'] === 'in_progress'): ?>
                <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><input type="hidden" name="action" value="complete">
                    <button class="btn btn-sm btn-success"><i class="bi bi-check-all me-1"></i>Complete</button></form>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($b['description']): ?>
        <div class="mt-2 p-2 rounded-2 small text-muted" style="background:var(--surface);"><i class="bi bi-chat-text me-1"></i><?= sanitize($b['description']) ?></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <div class="mt-3"><?= renderPagination($pagination, APP_URL . '/technician/bookings.php?' . http_build_query(array_filter(['status'=>$status]))) ?></div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
