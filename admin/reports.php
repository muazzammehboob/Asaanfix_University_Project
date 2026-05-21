<?php
/**
 * AsaanFix Pakistan - Admin: Reports
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Revenue by month (last 6 months)
$monthlyRevenue = dbQuery("SELECT DATE_FORMAT(completed_at, '%Y-%m') as month, SUM(total_amount) as revenue, COUNT(*) as jobs FROM bookings WHERE status='completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month ASC");

// Bookings by status
$statusStats = dbQuery("SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status");

// Top services
$topServices = dbQuery("SELECT s.name, COUNT(b.id) as booking_count, SUM(b.total_amount) as revenue FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.status = 'completed' GROUP BY s.id ORDER BY booking_count DESC LIMIT 5");

// Top technicians
$topTechs = dbQuery("SELECT u.name, t.avg_rating, t.total_jobs, t.total_reviews FROM technicians t JOIN users u ON t.user_id = u.id WHERE t.status = 'approved' ORDER BY t.total_jobs DESC LIMIT 5");

// City distribution
$cityStats = dbQuery("SELECT city, COUNT(*) as cnt FROM bookings WHERE city IS NOT NULL GROUP BY city ORDER BY cnt DESC LIMIT 8");

$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];

$pageTitle = 'Reports & Analytics';
$bodyClass = 'dashboard-page';
$currentPage = 'reports';
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
    <h4 class="fw-bold mb-4">Reports & Analytics</h4>

    <div class="row g-4">
        <!-- Monthly Revenue -->
        <div class="col-lg-8">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up me-2"></i>Monthly Revenue (Last 6 Months)</h5>
                <?php if (empty($monthlyRevenue)): ?>
                <p class="text-center text-muted py-3">No data yet.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light"><tr><th>Month</th><th>Jobs</th><th>Revenue</th><th>Visual</th></tr></thead>
                        <tbody>
                        <?php $maxRev = max(array_column($monthlyRevenue, 'revenue') ?: [1]); foreach ($monthlyRevenue as $m): $pct = ($m['revenue']/$maxRev)*100; ?>
                        <tr>
                            <td class="fw-semibold"><?= date('M Y', strtotime($m['month'].'-01')) ?></td>
                            <td><?= $m['jobs'] ?></td>
                            <td class="fw-bold text-success"><?= formatPrice($m['revenue']) ?></td>
                            <td style="width:40%;"><div class="progress" style="height:20px;"><div class="progress-bar bg-primary" style="width:<?= $pct ?>%"></div></div></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Booking Status Breakdown -->
        <div class="col-lg-4">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2"></i>Booking Status</h5>
                <?php foreach ($statusStats as $ss):
                    $colors = ['pending'=>'warning','confirmed'=>'info','in_progress'=>'primary','completed'=>'success','cancelled'=>'danger','disputed'=>'dark'];
                ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-<?= $colors[$ss['status']] ?? 'secondary' ?>"><?= ucfirst(str_replace('_',' ',$ss['status'])) ?></span>
                    <strong><?= $ss['cnt'] ?></strong>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Services -->
        <div class="col-lg-6">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-trophy me-2"></i>Top Services</h5>
                <?php foreach ($topServices as $i => $ts): ?>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div><span class="badge bg-primary me-2"><?= $i+1 ?></span><?= sanitize($ts['name']) ?></div>
                    <div class="text-end"><strong><?= $ts['booking_count'] ?></strong> jobs<br><small class="text-success"><?= formatPrice($ts['revenue']) ?></small></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Technicians -->
        <div class="col-lg-6">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-check me-2"></i>Top Technicians</h5>
                <?php foreach ($topTechs as $i => $tt): ?>
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div><span class="badge bg-success me-2"><?= $i+1 ?></span><?= sanitize($tt['name']) ?></div>
                    <div class="text-end"><strong><?= $tt['total_jobs'] ?></strong> jobs • ⭐ <?= $tt['avg_rating'] ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- City Stats -->
        <div class="col-12">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Bookings by City</h5>
                <div class="row g-2">
                    <?php foreach ($cityStats as $cs): ?>
                    <div class="col-md-3 col-6">
                        <div class="p-3 rounded-3 text-center" style="background:var(--surface);">
                            <h5 class="fw-bold text-primary mb-0"><?= $cs['cnt'] ?></h5>
                            <small class="text-muted"><?= sanitize($cs['city']) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
