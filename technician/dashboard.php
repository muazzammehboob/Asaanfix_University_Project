<?php
/**
 * AsaanFix Pakistan - Technician Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';
requireTechnician();

$userId = getCurrentUserId();
$tech = getCurrentTechnicianProfile();
if (!$tech) { setFlash('danger', 'Technician profile not found.'); redirect(APP_URL); }

$techId = $tech['id'];
$pendingBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE technician_id = ? AND status = 'pending'", [$techId])['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE technician_id = ? AND status IN ('confirmed','in_progress')", [$techId])['c'];
$completedBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE technician_id = ? AND status = 'completed'", [$techId])['c'];
$totalEarnings = dbQueryOne("SELECT COALESCE(SUM(total_amount),0) as s FROM bookings WHERE technician_id = ? AND status = 'completed'", [$techId])['s'];

$recentBookings = dbQuery(
    "SELECT b.*, s.name as service_name, u.name as user_name, u.avatar as user_avatar
     FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.user_id = u.id
     WHERE b.technician_id = ? ORDER BY b.created_at DESC LIMIT 5", [$techId]
);

$recentReviews = dbQuery(
    "SELECT r.*, u.name as user_name, u.avatar as user_avatar
     FROM reviews r JOIN users u ON r.user_id = u.id
     WHERE r.technician_id = ? ORDER BY r.created_at DESC LIMIT 3", [$techId]
);

$pageTitle = 'Technician Dashboard';
$bodyClass = 'dashboard-page';
$currentPage = 'dashboard';
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

    <?php if ($tech['status'] === 'pending'): ?>
    <div class="alert alert-warning"><i class="bi bi-hourglass-split me-2"></i><strong>Profile Under Review</strong> — Your application is being reviewed. You'll be notified once approved.</div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Welcome, <?= sanitize($tech['name']) ?>! 🔧</h4>
            <p class="text-muted mb-0">Here's your business overview</p>
        </div>
        <div><?= getStarRating($tech['avg_rating']) ?> <small class="text-muted">(<?= $tech['total_reviews'] ?> reviews)</small></div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--warning);"><i class="bi bi-clock"></i></div>
                <h3><?= $pendingBookings ?></h3><p>Pending Requests</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--primary);"><i class="bi bi-gear"></i></div>
                <h3><?= $activeBookings ?></h3><p>Active Jobs</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--success);"><i class="bi bi-check-circle"></i></div>
                <h3><?= $completedBookings ?></h3><p>Completed</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--info);"><i class="bi bi-wallet2"></i></div>
                <h3><?= formatPrice($totalEarnings) ?></h3><p>Total Earnings</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Bookings -->
        <div class="col-lg-8">
            <div class="card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Recent Bookings</h5>
                    <a href="<?= APP_URL ?>/technician/bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <?php if (empty($recentBookings)): ?>
                <p class="text-center text-muted py-4">No bookings yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Customer</th><th>Service</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentBookings as $b): ?>
                        <tr class="cursor-pointer" onclick="window.location='<?= APP_URL ?>/technician/bookings.php'">
                            <td><div class="d-flex align-items-center gap-2">
                                <img src="<?= getAvatarUrl($b['user_avatar'], $b['user_name'] ?? '') ?>" width="32" height="32" class="rounded-circle" style="object-fit:cover;">
                                <span class="small fw-medium"><?= sanitize($b['user_name']) ?></span>
                            </div></td>
                            <td class="small"><?= sanitize($b['service_name']) ?></td>
                            <td class="small"><?= formatDate($b['booking_date']) ?></td>
                            <td class="fw-semibold small"><?= formatPrice($b['total_amount']) ?></td>
                            <td><?= getStatusBadge($b['status']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="col-lg-4">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3">Recent Reviews</h5>
                <?php if (empty($recentReviews)): ?>
                <p class="text-muted small text-center">No reviews yet.</p>
                <?php else: ?>
                <?php foreach ($recentReviews as $r): ?>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex gap-2 align-items-center mb-1">
                        <img src="<?= getAvatarUrl($r['user_avatar'], $r['user_name'] ?? '') ?>" width="28" height="28" class="rounded-circle" style="object-fit:cover;">
                        <strong class="small"><?= sanitize($r['user_name']) ?></strong>
                    </div>
                    <?= getStarRating($r['rating'], false) ?>
                    <p class="small text-muted mt-1 mb-0 truncate-2"><?= sanitize($r['comment']) ?></p>
                </div>
                <?php endforeach; ?>
                <a href="<?= APP_URL ?>/technician/reviews.php" class="btn btn-sm btn-outline-primary w-100">All Reviews</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
