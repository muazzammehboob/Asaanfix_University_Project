<?php
/**
 * AsaanFix Pakistan - User Reviews
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$userId = getCurrentUserId();

$reviews = dbQuery(
    "SELECT r.*, s.name as service_name, u2.name as tech_name, u2.avatar as tech_avatar
     FROM reviews r 
     JOIN bookings b ON r.booking_id = b.id 
     JOIN services s ON b.service_id = s.id 
     JOIN technicians t ON r.technician_id = t.id 
     JOIN users u2 ON t.user_id = u2.id 
     WHERE r.user_id = ? ORDER BY r.created_at DESC", [$userId]
);

$pageTitle = 'My Reviews';
$bodyClass = 'dashboard-page';
$currentPage = 'reviews';
$sidebarMenu = [
    'Main' => [
        ['page'=>'dashboard','url'=>APP_URL.'/user/dashboard.php','icon'=>'bi-speedometer2','label'=>'Dashboard'],
        ['page'=>'bookings','url'=>APP_URL.'/user/bookings.php','icon'=>'bi-calendar-check','label'=>'My Bookings'],
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
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>
    <h4 class="fw-bold mb-4">My Reviews</h4>

    <?php if (empty($reviews)): ?>
    <div class="card-custom p-5 text-center">
        <i class="bi bi-star display-3 text-muted"></i>
        <h5 class="mt-3">No reviews yet</h5>
        <p class="text-muted">Complete a booking to leave a review.</p>
    </div>
    <?php else: ?>
    <?php foreach ($reviews as $r): ?>
    <div class="card-custom p-4 mb-3">
        <div class="d-flex gap-3">
            <img src="<?= getAvatarUrl($r['tech_avatar'], $r['tech_name'] ?? '') ?>" width="48" height="48" class="rounded-circle" style="object-fit:cover;">
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="fw-bold mb-0"><?= sanitize($r['tech_name']) ?></h6>
                        <small class="text-muted"><?= sanitize($r['service_name']) ?></small>
                    </div>
                    <small class="text-muted"><?= formatDate($r['created_at']) ?></small>
                </div>
                <div class="my-2"><?= getStarRating($r['rating'], false) ?></div>
                <p class="mb-0"><?= sanitize($r['comment']) ?></p>
                <?php if ($r['reply']): ?>
                <div class="mt-2 p-3 rounded-3" style="background:var(--surface);">
                    <small class="fw-bold"><i class="bi bi-reply me-1"></i>Technician Reply:</small>
                    <p class="small mb-0 mt-1"><?= sanitize($r['reply']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
