<?php
/**
 * AsaanFix Pakistan - Admin: Service Management
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = sanitize($_POST['name'] ?? '');
        $catId = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['base_price'] ?? 0);
        $duration = (int)($_POST['duration_minutes'] ?? 60);
        $desc = sanitize($_POST['description'] ?? '');
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        
        if ($name && $catId && $price > 0) {
            dbExecute("INSERT INTO services (category_id, name, slug, description, base_price, duration_minutes) VALUES (?,?,?,?,?,?)",
                [$catId, $name, $slug, $desc, $price, $duration]);
            logAdminAction('add_service', 'service', dbLastId());
            setFlash('success', 'Service added.');
        }
    } elseif ($action === 'toggle') {
        $id = (int)$_POST['service_id'];
        $svc = dbQueryOne("SELECT is_active FROM services WHERE id = ?", [$id]);
        if ($svc) { dbExecute("UPDATE services SET is_active = ? WHERE id = ?", [$svc['is_active']?0:1, $id]); }
        setFlash('success', 'Service updated.');
    } elseif ($action === 'delete') {
        dbExecute("DELETE FROM services WHERE id = ?", [(int)$_POST['service_id']]);
        setFlash('success', 'Service deleted.');
    }
    redirect(APP_URL . '/admin/services.php');
}

$services = dbQuery("SELECT s.*, c.name as category_name FROM services s JOIN categories c ON s.category_id = c.id ORDER BY c.sort_order, s.name");
$categories = dbQuery("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];

$pageTitle = 'Service Management';
$bodyClass = 'dashboard-page';
$currentPage = 'services';
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
        <h4 class="fw-bold mb-0">Service Management</h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addServiceModal"><i class="bi bi-plus-lg me-1"></i>Add Service</button>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light"><tr><th>Service</th><th>Category</th><th>Price</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($services as $s): ?>
                <tr>
                    <td class="fw-semibold"><?= sanitize($s['name']) ?></td>
                    <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= sanitize($s['category_name']) ?></span></td>
                    <td class="fw-semibold"><?= formatPrice($s['base_price']) ?></td>
                    <td><?= $s['duration_minutes'] ?> min</td>
                    <td><?= $s['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                    <td>
                        <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                            <button class="btn btn-sm btn-outline-<?= $s['is_active']?'warning':'success' ?>" title="Toggle"><i class="bi bi-<?= $s['is_active']?'pause':'play' ?>"></i></button></form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')"><?= csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Service</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST"><div class="modal-body"><?= csrfField() ?><input type="hidden" name="action" value="add">
        <div class="mb-3"><label class="form-label">Name *</label><input type="text" class="form-control" name="name" required></div>
        <div class="mb-3"><label class="form-label">Category *</label><select class="form-select" name="category_id" required><option value="">Select</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option><?php endforeach; ?></select></div>
        <div class="row g-3 mb-3"><div class="col-6"><label class="form-label">Base Price (PKR) *</label><input type="number" class="form-control" name="base_price" min="0" step="100" required></div>
        <div class="col-6"><label class="form-label">Duration (min)</label><input type="number" class="form-control" name="duration_minutes" value="60" min="15"></div></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
    </div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Add Service</button></div></form>
</div></div></div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
