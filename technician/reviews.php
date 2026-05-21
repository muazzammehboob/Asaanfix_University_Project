<?php
/**
 * AsaanFix Pakistan - Technician Reviews
 */
require_once __DIR__ . '/../includes/auth.php';
requireTechnician();
$tech = getCurrentTechnicianProfile();
$techId = $tech['id'];

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $reviewId = (int)($_POST['review_id'] ?? 0);
    $reply = sanitize($_POST['reply'] ?? '');
    if ($reviewId && !empty($reply)) {
        dbExecute("UPDATE reviews SET reply = ?, reply_at = NOW() WHERE id = ? AND technician_id = ?", [$reply, $reviewId, $techId]);
        setFlash('success', 'Reply added.');
    }
    redirect(APP_URL . '/technician/reviews.php');
}

$reviews = dbQuery(
    "SELECT r.*, u.name as user_name, u.avatar as user_avatar, s.name as service_name
     FROM reviews r JOIN users u ON r.user_id = u.id JOIN bookings b ON r.booking_id = b.id JOIN services s ON b.service_id = s.id
     WHERE r.technician_id = ? ORDER BY r.created_at DESC", [$techId]
);

$pendingBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE technician_id = ? AND status='pending'", [$techId])['c'];
$pageTitle = 'My Reviews';
$bodyClass = 'dashboard-page';
$currentPage = 'reviews';
$sidebarMenu = [
    'Main' => [
        ['page'=>'dashboard','url'=>APP_URL.'/technician/dashboard.php','icon'=>'bi-speedometer2','label'=>'Dashboard'],
        ['page'=>'bookings','url'=>APP_URL.'/technician/bookings.php','icon'=>'bi-calendar-check','label'=>'Bookings','badge'=>$pendingBookings ?: ''],
        ['page'=>'earnings','url'=>APP_URL.'/technician/earnings.php','icon'=>'bi-wallet2','label'=>'Earnings'],
        ['page'=>'reviews','url'=>APP_URL.'/technician/reviews.php','icon'=>'bi-star','label'=>'Reviews'],
    ],
    'Settings' => [
        ['page'=>'profile','url'=>APP_URL.'/technician/profile.php','icon'=>'bi-person-gear','label'=>'Profile & Skills'],
        ['page'=>'availability','url'=>APP_URL.'/technician/availability.php','icon'=>'bi-clock','label'=>'Availability'],
    ],
];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">My Reviews</h4>
        <div><?= getStarRating($tech['avg_rating']) ?> <small class="text-muted">(<?= $tech['total_reviews'] ?> total)</small></div>
    </div>

    <?php if (empty($reviews)): ?>
    <div class="card-custom p-5 text-center"><i class="bi bi-star display-3 text-muted"></i><h5 class="mt-3">No reviews yet</h5></div>
    <?php else: ?>
    <?php foreach ($reviews as $r): ?>
    <div class="card-custom p-4 mb-3">
        <div class="d-flex gap-3">
            <img src="<?= getAvatarUrl($r['user_avatar'], $r['user_name'] ?? '') ?>" width="44" height="44" class="rounded-circle" style="object-fit:cover;">
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <div><strong><?= sanitize($r['user_name']) ?></strong><br><small class="text-muted"><?= sanitize($r['service_name']) ?></small></div>
                    <small class="text-muted"><?= formatDate($r['created_at']) ?></small>
                </div>
                <div class="my-2"><?= getStarRating($r['rating'], false) ?></div>
                <p class="mb-0"><?= sanitize($r['comment']) ?></p>

                <?php if ($r['reply']): ?>
                <div class="mt-2 p-3 rounded-3" style="background:var(--surface);">
                    <small class="fw-bold text-primary"><i class="bi bi-reply me-1"></i>Your Reply:</small>
                    <p class="small mb-0 mt-1"><?= sanitize($r['reply']) ?></p>
                </div>
                <?php else: ?>
                <form method="POST" class="mt-3">
                    <?= csrfField() ?>
                    <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" name="reply" placeholder="Write a reply..." required>
                        <button class="btn btn-primary"><i class="bi bi-reply me-1"></i>Reply</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
