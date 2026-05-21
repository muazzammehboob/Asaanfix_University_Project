<?php
/**
 * AsaanFix Pakistan - Admin: Activity Logs
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$page = max(1, (int)($_GET['page'] ?? 1));
$cnt = dbQueryOne("SELECT COUNT(*) as c FROM admin_logs")['c'];
$pagination = getPagination($cnt, $page, ADMIN_ITEMS_PER_PAGE);
$logs = dbQuery("SELECT al.*, u.name as admin_name FROM admin_logs al JOIN users u ON al.admin_id = u.id ORDER BY al.created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}");

$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];
$pageTitle = 'Activity Logs';
$bodyClass = 'dashboard-page';
$currentPage = 'logs';
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
    <h4 class="fw-bold mb-4">Activity Logs</h4>

    <?php if (empty($logs)): ?>
    <div class="card-custom p-5 text-center"><i class="bi bi-journal-text display-3 text-muted"></i><h5 class="mt-3">No logs yet</h5></div>
    <?php else: ?>
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light"><tr><th>Admin</th><th>Action</th><th>Entity</th><th>IP</th><th>Time</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $l): ?>
                <tr>
                    <td class="fw-semibold"><?= sanitize($l['admin_name']) ?></td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= sanitize($l['action']) ?></span></td>
                    <td><?= sanitize($l['entity_type'] ?? '') ?> <?= $l['entity_id'] ? '#'.$l['entity_id'] : '' ?></td>
                    <td class="text-muted"><?= sanitize($l['ip_address'] ?? '') ?></td>
                    <td class="text-muted"><?= formatDateTime($l['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3"><?= renderPagination($pagination, APP_URL.'/admin/logs.php') ?></div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
