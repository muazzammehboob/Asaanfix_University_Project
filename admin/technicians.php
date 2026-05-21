<?php
/**
 * AsaanFix Pakistan - Admin: Technician Management
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $tid = (int)($_POST['tech_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($tid) {
        $tech = dbQueryOne("SELECT t.*, u.name, u.email FROM technicians t JOIN users u ON t.user_id = u.id WHERE t.id = ?", [$tid]);
        if ($tech) {
            if ($action === 'approve') {
                dbExecute("UPDATE technicians SET status = 'approved' WHERE id = ?", [$tid]);
                createNotification($tech['user_id'], 'Application Approved', 'Your technician application has been approved! You can now receive bookings.', 'system', '/technician/dashboard.php');
                logAdminAction('approve_technician', 'technician', $tid);
            } elseif ($action === 'reject') {
                dbExecute("UPDATE technicians SET status = 'rejected' WHERE id = ?", [$tid]);
                createNotification($tech['user_id'], 'Application Rejected', 'Your technician application was not approved. Please contact support.', 'system');
                logAdminAction('reject_technician', 'technician', $tid);
            } elseif ($action === 'suspend') {
                dbExecute("UPDATE technicians SET status = 'suspended' WHERE id = ?", [$tid]);
                logAdminAction('suspend_technician', 'technician', $tid);
            }
            setFlash('success', 'Technician updated.');
        }
    }
    redirect(APP_URL . '/admin/technicians.php?' . http_build_query($_GET));
}

$status = sanitize($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$where = ["1=1"]; $params = [];
if ($status) { $where[] = "t.status = ?"; $params[] = $status; }
$whereSQL = implode(' AND ', $where);
$cnt = dbQueryOne("SELECT COUNT(*) as c FROM technicians t WHERE $whereSQL", $params)['c'];
$pagination = getPagination($cnt, $page, ADMIN_ITEMS_PER_PAGE);

$techs = dbQuery(
    "SELECT t.*, u.name, u.email, u.phone, u.avatar, u.city, u.created_at as user_since
     FROM technicians t JOIN users u ON t.user_id = u.id
     WHERE $whereSQL ORDER BY FIELD(t.status,'pending','approved','rejected','suspended'), t.created_at DESC
     LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}", $params
);

$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];

$pageTitle = 'Technician Management';
$bodyClass = 'dashboard-page';
$currentPage = 'technicians';
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
    <h4 class="fw-bold mb-4">Technician Management</h4>

    <ul class="nav nav-pills mb-4">
        <?php foreach (['' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'suspended' => 'Suspended'] as $k => $l): ?>
        <li class="nav-item"><a class="nav-link <?= $status===$k?'active':'' ?>" href="?<?= $k?"status=$k":'' ?>"><?= $l ?></a></li>
        <?php endforeach; ?>
    </ul>

    <?php foreach ($techs as $t): ?>
    <div class="card-custom p-3 mb-3">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <img src="<?= getAvatarUrl($t['avatar'], $t['name'] ?? '') ?>" width="52" height="52" class="rounded-circle" style="object-fit:cover;">
            </div>
            <div class="col">
                <h6 class="fw-bold mb-0"><?= sanitize($t['name']) ?></h6>
                <small class="text-muted"><?= sanitize($t['email']) ?> • <?= sanitize($t['phone'] ?? '') ?> • <?= sanitize($t['city'] ?? '') ?></small>
                <div class="mt-1 d-flex flex-wrap gap-2 small">
                    <?= getStarRating($t['avg_rating']) ?>
                    <span class="text-muted"><?= $t['total_jobs'] ?> jobs</span>
                    <span class="text-muted"><?= $t['experience_years'] ?> yrs exp</span>
                    <span class="text-muted"><?= formatPrice($t['hourly_rate']) ?>/hr</span>
                </div>
                <?php if ($t['skills']): ?><div class="mt-1"><?php foreach(array_slice(explode(',',$t['skills']),0,4) as $s): ?><span class="badge bg-light text-dark border me-1" style="font-size:0.7rem;"><?= sanitize(trim($s)) ?></span><?php endforeach; ?></div><?php endif; ?>
            </div>
            <div class="col-auto text-end">
                <span class="badge bg-<?= $t['status']==='approved'?'success':($t['status']==='pending'?'warning':($t['status']==='rejected'?'danger':'dark')) ?> mb-2 d-block"><?= ucfirst($t['status']) ?></span>
                <div class="d-flex gap-1">
                    <?php if ($t['status'] === 'pending'): ?>
                    <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="tech_id" value="<?= $t['id'] ?>"><input type="hidden" name="action" value="approve"><button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Approve</button></form>
                    <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="tech_id" value="<?= $t['id'] ?>"><input type="hidden" name="action" value="reject"><button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></button></form>
                    <?php elseif ($t['status'] === 'approved'): ?>
                    <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="tech_id" value="<?= $t['id'] ?>"><input type="hidden" name="action" value="suspend"><button class="btn btn-sm btn-outline-warning">Suspend</button></form>
                    <?php elseif (in_array($t['status'], ['rejected','suspended'])): ?>
                    <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="tech_id" value="<?= $t['id'] ?>"><input type="hidden" name="action" value="approve"><button class="btn btn-sm btn-outline-success">Re-Approve</button></form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($techs)): ?><div class="card-custom p-5 text-center"><i class="bi bi-person-slash display-3 text-muted"></i><h5 class="mt-3">No technicians found</h5></div><?php endif; ?>
    <div class="mt-3"><?= renderPagination($pagination, APP_URL.'/admin/technicians.php?'.http_build_query(array_filter(['status'=>$status]))) ?></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
