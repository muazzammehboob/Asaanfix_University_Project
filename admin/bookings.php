<?php
/**
 * AsaanFix Pakistan - Admin: Booking Management
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$status = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$where = ["1=1"]; $params = [];
if ($status) { $where[] = "b.status = ?"; $params[] = $status; }
if ($search) { $where[] = "(b.booking_number LIKE ? OR u1.name LIKE ? OR u2.name LIKE ?)"; $params = array_merge($params,["%$search%","%$search%","%$search%"]); }
$whereSQL = implode(' AND ', $where);
$cnt = dbQueryOne("SELECT COUNT(*) as c FROM bookings b JOIN users u1 ON b.user_id = u1.id JOIN technicians t ON b.technician_id = t.id JOIN users u2 ON t.user_id = u2.id WHERE $whereSQL", $params)['c'];
$pagination = getPagination($cnt, $page, ADMIN_ITEMS_PER_PAGE);

$bookings = dbQuery(
    "SELECT b.*, s.name as service_name, u1.name as user_name, u2.name as tech_name, p.status as pay_status, p.method as pay_method
     FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u1 ON b.user_id = u1.id
     JOIN technicians t ON b.technician_id = t.id JOIN users u2 ON t.user_id = u2.id
     LEFT JOIN payments p ON p.booking_id = b.id
     WHERE $whereSQL ORDER BY b.created_at DESC
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}", $params
);

$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];

$pageTitle = 'Booking Management';
$bodyClass = 'dashboard-page';
$currentPage = 'bookings';
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
    <h4 class="fw-bold mb-4">Booking Management</h4>

    <div class="filter-bar mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5"><input type="text" class="form-control" name="q" value="<?= sanitize($search) ?>" placeholder="Search booking #, customer, technician..."></div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <?php foreach(['pending','confirmed','in_progress','completed','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button></div>
            <div class="col-md-2"><a href="<?= APP_URL ?>/admin/bookings.php" class="btn btn-outline-secondary w-100">Reset</a></div>
        </form>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light"><tr><th>Booking #</th><th>Customer</th><th>Service</th><th>Technician</th><th>Date</th><th>Amount</th><th>Payment</th><th>Status</th></tr></thead>
                <tbody>
                <?php if (empty($bookings)): ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">No bookings found</td></tr>
                <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td class="fw-semibold text-primary"><?= $b['booking_number'] ?></td>
                    <td><?= sanitize($b['user_name']) ?></td>
                    <td><?= sanitize($b['service_name']) ?></td>
                    <td><?= sanitize($b['tech_name']) ?></td>
                    <td class="text-nowrap"><?= formatDate($b['booking_date']) ?><br><small class="text-muted"><?= formatTime($b['booking_time']) ?></small></td>
                    <td class="fw-semibold"><?= formatPrice($b['total_amount']) ?></td>
                    <td><span class="badge bg-<?= ($b['pay_status']??'pending')==='completed'?'success':'warning' ?>"><?= ucfirst($b['pay_status'] ?? 'pending') ?></span></td>
                    <td><?= getStatusBadge($b['status']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3"><?= renderPagination($pagination, APP_URL.'/admin/bookings.php?'.http_build_query(array_filter(['q'=>$search,'status'=>$status]))) ?></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
