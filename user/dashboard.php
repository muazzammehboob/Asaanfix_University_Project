<?php
/**
 * AsaanFix Pakistan - User Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$userId = getCurrentUserId();
$user = getCurrentUser();

// Stats
$totalBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE user_id = ?", [$userId])['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE user_id = ? AND status IN ('pending','confirmed','in_progress')", [$userId])['c'];
$completedBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE user_id = ? AND status = 'completed'", [$userId])['c'];
$totalSpent = dbQueryOne("SELECT COALESCE(SUM(total_amount),0) as s FROM bookings WHERE user_id = ? AND status = 'completed'", [$userId])['s'];

// Recent bookings
$recentBookings = dbQuery(
    "SELECT b.*, s.name as service_name, u2.name as tech_name 
     FROM bookings b 
     JOIN services s ON b.service_id = s.id 
     JOIN technicians t ON b.technician_id = t.id 
     JOIN users u2 ON t.user_id = u2.id 
     WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 5", [$userId]
);

$pageTitle = 'Dashboard';
$bodyClass = 'dashboard-page';
$currentPage = 'dashboard';
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
    <!-- Mobile sidebar toggle -->
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Welcome, <?= sanitize($user['name']) ?>! 👋</h4>
            <p class="text-muted mb-0">Here's what's happening with your bookings</p>
        </div>
        <a href="<?= APP_URL ?>/services.php" class="btn btn-primary d-none d-md-inline-flex">
            <i class="bi bi-plus-lg me-1"></i>Book Service
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--primary);"><i class="bi bi-calendar-check"></i></div>
                <h3><?= $totalBookings ?></h3>
                <p>Total Bookings</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--warning);"><i class="bi bi-clock"></i></div>
                <h3><?= $activeBookings ?></h3>
                <p>Active Bookings</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--success);"><i class="bi bi-check-circle"></i></div>
                <h3><?= $completedBookings ?></h3>
                <p>Completed</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--info);"><i class="bi bi-wallet2"></i></div>
                <h3><?= formatPrice($totalSpent) ?></h3>
                <p>Total Spent</p>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card-custom p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Recent Bookings</h5>
            <a href="<?= APP_URL ?>/user/bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <?php if (empty($recentBookings)): ?>
        <div class="text-center py-4">
            <i class="bi bi-calendar-x display-4 text-muted"></i>
            <p class="mt-2 text-muted">No bookings yet. <a href="<?= APP_URL ?>/services.php">Book your first service!</a></p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Booking #</th><th>Service</th><th>Technician</th><th>Date</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                    <tr class="cursor-pointer" onclick="window.location='<?= APP_URL ?>/user/booking-detail.php?id=<?= $b['id'] ?>'">
                        <td class="fw-semibold text-primary"><?= $b['booking_number'] ?></td>
                        <td><?= sanitize($b['service_name']) ?></td>
                        <td><?= sanitize($b['tech_name']) ?></td>
                        <td><?= formatDate($b['booking_date']) ?></td>
                        <td class="fw-semibold"><?= formatPrice($b['total_amount']) ?></td>
                        <td><?= getStatusBadge($b['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
