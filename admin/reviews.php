<?php
/**
 * AsaanFix Pakistan - Admin: Reviews
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $id = (int)($_POST['review_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle' && $id) {
        $r = dbQueryOne("SELECT is_visible FROM reviews WHERE id = ?", [$id]);
        if ($r) dbExecute("UPDATE reviews SET is_visible = ? WHERE id = ?", [$r['is_visible']?0:1, $id]);
    } elseif ($action === 'delete' && $id) {
        dbExecute("DELETE FROM reviews WHERE id = ?", [$id]);
    }
    setFlash('success', 'Review updated.');
    redirect(APP_URL . '/admin/reviews.php');
}

$reviews = dbQuery(
    "SELECT r.*, u1.name as user_name, u2.name as tech_name, s.name as service_name
     FROM reviews r JOIN users u1 ON r.user_id = u1.id JOIN technicians t ON r.technician_id = t.id JOIN users u2 ON t.user_id = u2.id
     JOIN bookings b ON r.booking_id = b.id JOIN services s ON b.service_id = s.id ORDER BY r.created_at DESC LIMIT 50"
);

$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];
$pageTitle = 'Review Management';
$bodyClass = 'dashboard-page';
$currentPage = 'reviews';
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
    <h4 class="fw-bold mb-4">Review Management</h4>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light"><tr><th>Customer</th><th>Technician</th><th>Service</th><th>Rating</th><th>Comment</th><th>Visible</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($reviews as $r): ?>
                <tr>
                    <td class="fw-semibold"><?= sanitize($r['user_name']) ?></td>
                    <td><?= sanitize($r['tech_name']) ?></td>
                    <td><?= sanitize($r['service_name']) ?></td>
                    <td><?= getStarRating($r['rating'], false) ?></td>
                    <td class="truncate-2" style="max-width:200px;"><?= sanitize($r['comment']) ?></td>
                    <td><?= $r['is_visible'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">Hidden</span>' ?></td>
                    <td class="text-muted"><?= formatDate($r['created_at']) ?></td>
                    <td>
                        <form method="POST" class="d-inline"><?= csrfField() ?><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="toggle">
                            <button class="btn btn-sm btn-outline-<?= $r['is_visible']?'warning':'success' ?>"><i class="bi bi-eye<?= $r['is_visible']?'-slash':'' ?>"></i></button></form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete?')"><?= csrfField() ?><input type="hidden" name="review_id" value="<?= $r['id'] ?>"><input type="hidden" name="action" value="delete">
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
