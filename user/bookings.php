<?php
/**
 * AsaanFix Pakistan - User Bookings
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$userId = getCurrentUserId();
$status = sanitize($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$where = ["b.user_id = ?"];
$params = [$userId];
if ($status && in_array($status, ['pending','confirmed','in_progress','completed','cancelled'])) {
    $where[] = "b.status = ?";
    $params[] = $status;
}
$whereSQL = implode(' AND ', $where);
$countRow = dbQueryOne("SELECT COUNT(*) as c FROM bookings b WHERE $whereSQL", $params);
$pagination = getPagination($countRow['c'], $page);

$bookings = dbQuery(
    "SELECT b.*, s.name as service_name, s.slug as service_slug, u2.name as tech_name, u2.avatar as tech_avatar, u2.city as tech_city
     FROM bookings b 
     JOIN services s ON b.service_id = s.id 
     JOIN technicians t ON b.technician_id = t.id 
     JOIN users u2 ON t.user_id = u2.id 
     WHERE $whereSQL ORDER BY b.created_at DESC 
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}", $params
);

$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE user_id = ? AND status IN ('pending','confirmed','in_progress')", [$userId])['c'];

$pageTitle = 'My Bookings';
$bodyClass = 'dashboard-page';
$currentPage = 'bookings';
$sidebarMenu = [
    'Main' => [
        ['page'=>'dashboard','url'=>APP_URL.'/user/dashboard.php','icon'=>'bi-speedometer2','label'=>'Dashboard'],
        ['page'=>'bookings','url'=>APP_URL.'/user/bookings.php','icon'=>'bi-calendar-check','label'=>'My Bookings','badge'=>$activeBookings ?: ''],
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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">My Bookings</h4>
        <a href="<?= APP_URL ?>/services.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Booking</a>
    </div>

    <!-- Status Tabs -->
    <ul class="nav nav-pills mb-4 flex-nowrap overflow-auto">
        <?php
        $tabs = ['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
        foreach ($tabs as $key => $label): ?>
        <li class="nav-item">
            <a class="nav-link <?= $status === $key ? 'active' : '' ?> text-nowrap" href="<?= APP_URL ?>/user/bookings.php<?= $key ? "?status=$key" : '' ?>"><?= $label ?></a>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php if (empty($bookings)): ?>
    <div class="card-custom p-5 text-center">
        <i class="bi bi-calendar-x display-3 text-muted"></i>
        <h5 class="mt-3">No bookings found</h5>
        <p class="text-muted">You haven't made any bookings yet.</p>
        <a href="<?= APP_URL ?>/services.php" class="btn btn-primary">Browse Services</a>
    </div>
    <?php else: ?>
    <?php foreach ($bookings as $b): ?>
    <div class="card-custom p-3 mb-3 hover-lift">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <img src="<?= getAvatarUrl($b['tech_avatar'], $b['tech_name'] ?? '') ?>" width="48" height="48" class="rounded-circle" style="object-fit:cover;" alt="">
            </div>
            <div class="col">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="fw-bold text-primary"><?= $b['booking_number'] ?></span>
                    <?= getStatusBadge($b['status']) ?>
                </div>
                <h6 class="mb-0 mt-1 fw-semibold"><?= sanitize($b['service_name']) ?></h6>
                <small class="text-muted">by <?= sanitize($b['tech_name']) ?> • <?= sanitize($b['tech_city']) ?></small>
            </div>
            <div class="col-auto text-end">
                <div class="fw-bold"><?= formatPrice($b['total_amount']) ?></div>
                <small class="text-muted"><i class="bi bi-calendar me-1"></i><?= formatDate($b['booking_date']) ?> at <?= formatTime($b['booking_time']) ?></small>
            </div>
            <div class="col-auto">
                <a href="<?= APP_URL ?>/user/booking-detail.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-primary">Details</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <div class="mt-3"><?= renderPagination($pagination, APP_URL . '/user/bookings.php?' . http_build_query(array_filter(['status'=>$status]))) ?></div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
