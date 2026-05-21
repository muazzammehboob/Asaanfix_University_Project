<?php
/**
 * AsaanFix Pakistan - Admin: User Management
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $uid = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($uid && $uid !== getCurrentUserId()) {
        if ($action === 'toggle_status') {
            $u = dbQueryOne("SELECT is_active FROM users WHERE id = ?", [$uid]);
            if ($u) { dbExecute("UPDATE users SET is_active = ? WHERE id = ?", [$u['is_active'] ? 0 : 1, $uid]); logAdminAction('toggle_user_status', 'user', $uid); }
        } elseif ($action === 'delete') {
            dbExecute("DELETE FROM users WHERE id = ? AND role != 'admin'", [$uid]);
            logAdminAction('delete_user', 'user', $uid);
        }
        setFlash('success', 'User updated.');
    }
    redirect(APP_URL . '/admin/users.php?' . http_build_query($_GET));
}

$search = sanitize($_GET['q'] ?? '');
$role = sanitize($_GET['role'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$where = ["1=1"]; $params = [];
if ($search) { $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
if ($role) { $where[] = "role = ?"; $params[] = $role; }
$whereSQL = implode(' AND ', $where);
$cnt = dbQueryOne("SELECT COUNT(*) as c FROM users WHERE $whereSQL", $params)['c'];
$pagination = getPagination($cnt, $page, ADMIN_ITEMS_PER_PAGE);
$users = dbQuery("SELECT * FROM users WHERE $whereSQL ORDER BY created_at DESC LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}", $params);

$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];

$pageTitle = 'User Management';
$bodyClass = 'dashboard-page';
$currentPage = 'users';
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
    <h4 class="fw-bold mb-4">User Management</h4>

    <div class="filter-bar mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5"><input type="text" class="form-control" name="q" value="<?= sanitize($search) ?>" placeholder="Search name, email, phone..."></div>
            <div class="col-md-3">
                <select class="form-select" name="role">
                    <option value="">All Roles</option>
                    <option value="user" <?= $role==='user'?'selected':'' ?>>Users</option>
                    <option value="technician" <?= $role==='technician'?'selected':'' ?>>Technicians</option>
                    <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admins</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Search</button></div>
            <div class="col-md-2"><a href="<?= APP_URL ?>/admin/users.php" class="btn btn-outline-secondary w-100">Reset</a></div>
        </form>
    </div>

    <p class="text-muted small mb-3"><strong><?= $cnt ?></strong> users found</p>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light"><tr><th>User</th><th>Email</th><th>Phone</th><th>Role</th><th>City</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><div class="d-flex align-items-center gap-2">
                        <img src="<?= getAvatarUrl($u['avatar'], $u['name'] ?? '') ?>" width="32" height="32" class="rounded-circle" style="object-fit:cover;">
                        <strong><?= sanitize($u['name']) ?></strong>
                    </div></td>
                    <td><?= sanitize($u['email']) ?></td>
                    <td><?= sanitize($u['phone'] ?? '—') ?></td>
                    <td><span class="badge bg-<?= $u['role']==='admin'?'danger':($u['role']==='technician'?'info':'secondary') ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= sanitize($u['city'] ?? '—') ?></td>
                    <td><?= $u['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                    <td class="text-muted"><?= formatDate($u['created_at']) ?></td>
                    <td>
                        <?php if ($u['id'] !== getCurrentUserId()): ?>
                        <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="toggle_status">
                            <button class="btn btn-sm btn-outline-<?= $u['is_active']?'warning':'success' ?>" title="<?= $u['is_active']?'Deactivate':'Activate' ?>"><i class="bi bi-<?= $u['is_active']?'pause':'play' ?>"></i></button></form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')"><?= csrfField() ?><input type="hidden" name="user_id" value="<?= $u['id'] ?>"><input type="hidden" name="action" value="delete">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3"><?= renderPagination($pagination, APP_URL.'/admin/users.php?'.http_build_query(array_filter(['q'=>$search,'role'=>$role]))) ?></div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
