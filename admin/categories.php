<?php
/**
 * AsaanFix Pakistan - Admin: Category Management
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = sanitize($_POST['name'] ?? '');
        $icon = sanitize($_POST['icon'] ?? 'bi-tools');
        $desc = sanitize($_POST['description'] ?? '');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $order = (int)($_POST['sort_order'] ?? 0);
        if ($name) {
            dbExecute("INSERT INTO categories (name, slug, icon, description, sort_order) VALUES (?,?,?,?,?)", [$name, $slug, $icon, $desc, $order]);
            logAdminAction('add_category', 'category', dbLastId());
            setFlash('success', 'Category added.');
        }
    } elseif ($action === 'toggle') {
        $id = (int)$_POST['cat_id'];
        $cat = dbQueryOne("SELECT is_active FROM categories WHERE id = ?", [$id]);
        if ($cat) dbExecute("UPDATE categories SET is_active = ? WHERE id = ?", [$cat['is_active']?0:1, $id]);
    } elseif ($action === 'delete') {
        dbExecute("DELETE FROM categories WHERE id = ?", [(int)$_POST['cat_id']]);
        setFlash('success', 'Category deleted.');
    }
    redirect(APP_URL . '/admin/categories.php');
}

$categories = dbQuery("SELECT c.*, (SELECT COUNT(*) FROM services WHERE category_id = c.id) as service_count FROM categories c ORDER BY c.sort_order");
$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];

$pageTitle = 'Category Management';
$bodyClass = 'dashboard-page';
$currentPage = 'categories';
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
        <h4 class="fw-bold mb-0">Categories</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCatModal"><i class="bi bi-plus-lg me-1"></i>Add Category</button>
    </div>

    <div class="row g-3">
        <?php foreach ($categories as $c): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card-custom p-4">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-wrapper" style="width:48px;height:48px;border-radius:12px;background:rgba(var(--primary-rgb),0.1);display:flex;align-items:center;justify-content:center;">
                            <i class="bi <?= sanitize($c['icon']) ?> text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0"><?= sanitize($c['name']) ?></h6>
                            <small class="text-muted"><?= $c['service_count'] ?> services</small>
                        </div>
                    </div>
                    <?= $c['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                </div>
                <p class="text-muted small truncate-2 mb-3"><?= sanitize($c['description'] ?? '') ?></p>
                <div class="d-flex gap-1">
                    <form method="POST"><?= csrfField() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="cat_id" value="<?= $c['id'] ?>">
                        <button class="btn btn-sm btn-outline-<?= $c['is_active']?'warning':'success' ?>"><?= $c['is_active']?'Disable':'Enable' ?></button></form>
                    <form method="POST" onsubmit="return confirm('Delete category and all its services?')"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="cat_id" value="<?= $c['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCatModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST"><div class="modal-body"><?= csrfField() ?><input type="hidden" name="action" value="add">
        <div class="mb-3"><label class="form-label">Name *</label><input type="text" class="form-control" name="name" required></div>
        <div class="row g-3 mb-3"><div class="col-6"><label class="form-label">Icon Class</label><input type="text" class="form-control" name="icon" value="bi-tools" placeholder="bi-tools"></div>
        <div class="col-6"><label class="form-label">Sort Order</label><input type="number" class="form-control" name="sort_order" value="0"></div></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add Category</button></div></form>
</div></div></div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
