<?php
/**
 * AsaanFix Pakistan - Admin Notifications
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$userId = getCurrentUserId();

if (isset($_GET['mark_read'])) {
    dbExecute("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$userId]);
    setFlash('success', 'All notifications marked as read.');
    redirect(APP_URL . '/admin/notifications.php');
}

$notifications = dbQuery("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50", [$userId]);
$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];

$pageTitle = 'Notifications';
$bodyClass = 'dashboard-page';
$currentPage = 'notifications';
$sidebarMenu = [
    'Overview' => [['page'=>'dashboard','url'=>APP_URL.'/admin/dashboard.php','icon'=>'bi-speedometer2','label'=>'Dashboard']],
    'Management' => [
        ['page'=>'users','url'=>APP_URL.'/admin/users.php','icon'=>'bi-people','label'=>'Users'],
        ['page'=>'technicians','url'=>APP_URL.'/admin/technicians.php','icon'=>'bi-person-badge','label'=>'Technicians','badge'=>$pendingTechs?:''],
        ['page'=>'bookings','url'=>APP_URL.'/admin/bookings.php','icon'=>'bi-calendar-check','label'=>'Bookings','badge'=>$activeBookings?:''],
        ['page'=>'services','url'=>APP_URL.'/admin/services.php','icon'=>'bi-grid','label'=>'Services'],
        ['page'=>'categories','url'=>APP_URL.'/admin/categories.php','icon'=>'bi-tag','label'=>'Categories'],
        ['page'=>'reviews','url'=>APP_URL.'/admin/reviews.php','icon'=>'bi-star','label'=>'Reviews'],
    ],
    'System' => [
        ['page'=>'reports','url'=>APP_URL.'/admin/reports.php','icon'=>'bi-graph-up','label'=>'Reports'],
        ['page'=>'logs','url'=>APP_URL.'/admin/logs.php','icon'=>'bi-journal-text','label'=>'Activity Logs'],
        ['page'=>'settings','url'=>APP_URL.'/admin/settings.php','icon'=>'bi-gear','label'=>'Settings'],
    ],
];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Notifications</h4>
        <a href="?mark_read=1" class="btn btn-sm btn-outline-primary"><i class="bi bi-check-all me-1"></i>Mark All Read</a>
    </div>

    <?php if (empty($notifications)): ?>
    <div class="card-custom p-5 text-center"><i class="bi bi-bell-slash display-3 text-muted"></i><h5 class="mt-3">No notifications</h5></div>
    <?php else: ?>
    <?php foreach ($notifications as $n): $iconMap = ['booking'=>'bi-calendar-check','review'=>'bi-star','payment'=>'bi-credit-card','message'=>'bi-chat-dots','system'=>'bi-info-circle']; ?>
    <div class="card-custom p-3 mb-2 <?= $n['is_read'] ? '' : 'border-start border-primary border-3' ?>">
        <div class="d-flex gap-3 align-items-start">
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle" style="width:40px;height:40px;background:rgba(var(--primary-rgb),0.1);">
                <i class="bi <?= $iconMap[$n['type']] ?? 'bi-bell' ?> text-primary"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-0 fw-semibold" style="font-size:0.9rem;"><?= sanitize($n['title']) ?></h6>
                <p class="text-muted small mb-1"><?= sanitize($n['message']) ?></p>
                <small class="text-muted"><?= timeAgo($n['created_at']) ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
