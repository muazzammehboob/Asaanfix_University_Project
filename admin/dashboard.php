<?php
/**
 * AsaanFix Pakistan - Admin Dashboard with Chart.js Analytics
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$totalUsers = dbQueryOne("SELECT COUNT(*) as c FROM users WHERE role='user'")['c'];
$totalTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians")['c'];
$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$totalBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','accepted','technician_on_way','in_progress')")['c'];
$completedBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status='completed'")['c'];
$totalRevenue = dbQueryOne("SELECT COALESCE(SUM(total_amount),0) as s FROM bookings WHERE status='completed'")['s'];
$monthRevenue = dbQueryOne("SELECT COALESCE(SUM(total_amount),0) as s FROM bookings WHERE status='completed' AND MONTH(completed_at)=MONTH(NOW()) AND YEAR(completed_at)=YEAR(NOW())")['s'];

// Chart data: Monthly revenue (last 6 months)
$chartRevenue = dbQuery("SELECT DATE_FORMAT(completed_at, '%Y-%m') as month, SUM(total_amount) as revenue, COUNT(*) as jobs FROM bookings WHERE status='completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month ASC");

// Chart data: Bookings by status
$chartStatus = dbQuery("SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status");

// Chart data: Top 5 services
$chartServices = dbQuery("SELECT s.name, COUNT(b.id) as cnt FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.status='completed' GROUP BY s.id ORDER BY cnt DESC LIMIT 5");

// Chart data: Daily bookings this month
$chartDaily = dbQuery("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM bookings WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW()) GROUP BY day ORDER BY day ASC");

$recentBookings = dbQuery(
    "SELECT b.*, s.name as service_name, u1.name as user_name, u2.name as tech_name
     FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u1 ON b.user_id = u1.id
     JOIN technicians t ON b.technician_id = t.id JOIN users u2 ON t.user_id = u2.id
     ORDER BY b.created_at DESC LIMIT 8"
);

$recentUsers = dbQuery("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 5");

$pageTitle = 'Admin Dashboard';
$bodyClass = 'dashboard-page';
$currentPage = 'dashboard';
$sidebarMenu = [
    'Overview' => [
        ['page'=>'dashboard','url'=>APP_URL.'/admin/dashboard','icon'=>'bi-speedometer2','label'=>'Dashboard'],
    ],
    'Management' => [
        ['page'=>'users','url'=>APP_URL.'/admin/users','icon'=>'bi-people','label'=>'Users'],
        ['page'=>'technicians','url'=>APP_URL.'/admin/technicians','icon'=>'bi-person-badge','label'=>'Technicians','badge'=>$pendingTechs ?: ''],
        ['page'=>'bookings','url'=>APP_URL.'/admin/bookings','icon'=>'bi-calendar-check','label'=>'Bookings','badge'=>$activeBookings ?: ''],
        ['page'=>'services','url'=>APP_URL.'/admin/services','icon'=>'bi-grid','label'=>'Services'],
        ['page'=>'categories','url'=>APP_URL.'/admin/categories','icon'=>'bi-tag','label'=>'Categories'],
        ['page'=>'reviews','url'=>APP_URL.'/admin/reviews','icon'=>'bi-star','label'=>'Reviews'],
    ],
    'System' => [
        ['page'=>'reports','url'=>APP_URL.'/admin/reports','icon'=>'bi-graph-up','label'=>'Reports'],
        ['page'=>'logs','url'=>APP_URL.'/admin/logs','icon'=>'bi-journal-text','label'=>'Activity Logs'],
        ['page'=>'settings','url'=>APP_URL.'/admin/settings','icon'=>'bi-gear','label'=>'Settings'],
    ],
];
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="fw-bold mb-1">Admin Dashboard</h4><p class="text-muted mb-0">Platform overview and analytics</p></div>
        <span class="text-muted small"><i class="bi bi-calendar me-1"></i><?= date('M d, Y') ?></span>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card"><div class="stat-icon text-white" style="background:var(--primary);"><i class="bi bi-people"></i></div>
                <h3><?= $totalUsers ?></h3><p>Total Users</p></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card"><div class="stat-icon text-white" style="background:var(--success);"><i class="bi bi-person-badge"></i></div>
                <h3><?= $totalTechs ?></h3><p>Technicians</p>
                <?php if ($pendingTechs): ?><small class="text-warning"><i class="bi bi-clock"></i> <?= $pendingTechs ?> pending</small><?php endif; ?></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card"><div class="stat-icon text-white" style="background:var(--info);"><i class="bi bi-calendar-check"></i></div>
                <h3><?= $totalBookings ?></h3><p>Total Bookings</p>
                <small class="text-primary"><?= $activeBookings ?> active</small></div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card"><div class="stat-icon text-white" style="background:var(--accent);"><i class="bi bi-cash-stack"></i></div>
                <h3><?= formatPrice($totalRevenue) ?></h3><p>Total Revenue</p>
                <small class="text-success"><?= formatPrice($monthRevenue) ?> this month</small></div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Revenue Chart -->
        <div class="col-lg-8">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Revenue Overview</h5>
                <canvas id="revenueChart" height="280"></canvas>
            </div>
        </div>
        <!-- Booking Status Pie -->
        <div class="col-lg-4">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart me-2 text-info"></i>Booking Status</h5>
                <canvas id="statusChart" height="280"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Top Services Bar Chart -->
        <div class="col-lg-6">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-warning"></i>Top Services</h5>
                <canvas id="servicesChart" height="250"></canvas>
            </div>
        </div>
        <!-- Daily Bookings Line Chart -->
        <div class="col-lg-6">
            <div class="card-custom p-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-calendar3 me-2 text-success"></i>Daily Bookings (This Month)</h5>
                <canvas id="dailyChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Bookings -->
        <div class="col-lg-8">
            <div class="card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Recent Bookings</h5>
                    <a href="<?= APP_URL ?>/admin/bookings" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light"><tr><th>#</th><th>Customer</th><th>Service</th><th>Technician</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td class="fw-semibold text-primary"><?= $b['booking_number'] ?></td>
                            <td><?= sanitize($b['user_name']) ?></td>
                            <td><?= sanitize($b['service_name']) ?></td>
                            <td><?= sanitize($b['tech_name']) ?></td>
                            <td class="fw-semibold"><?= formatPrice($b['total_amount']) ?></td>
                            <td><?= getStatusBadge($b['status']) ?></td>
                            <td class="text-muted"><?= formatDate($b['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions + Recent Users -->
        <div class="col-lg-4">
            <div class="card-custom p-4 mb-4">
                <h6 class="fw-bold mb-3">Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="<?= APP_URL ?>/admin/technicians?status=pending" class="btn btn-outline-warning btn-sm text-start"><i class="bi bi-clock me-2"></i>Pending Approvals <span class="badge bg-warning text-dark float-end"><?= $pendingTechs ?></span></a>
                    <a href="<?= APP_URL ?>/admin/bookings" class="btn btn-outline-primary btn-sm text-start"><i class="bi bi-calendar me-2"></i>Manage Bookings</a>
                    <a href="<?= APP_URL ?>/admin/services" class="btn btn-outline-success btn-sm text-start"><i class="bi bi-grid me-2"></i>Manage Services</a>
                    <a href="<?= APP_URL ?>/admin/reports" class="btn btn-outline-info btn-sm text-start"><i class="bi bi-graph-up me-2"></i>View Reports</a>
                </div>
            </div>

            <div class="card-custom p-4">
                <h6 class="fw-bold mb-3">New Users</h6>
                <?php foreach ($recentUsers as $u): ?>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <img src="<?= getAvatarUrl($u['avatar'], $u['name'] ?? '') ?>" width="32" height="32" class="rounded-circle" style="object-fit:cover;">
                    <div class="flex-grow-1">
                        <div class="small fw-medium"><?= sanitize($u['name']) ?></div>
                        <small class="text-muted"><?= ucfirst($u['role']) ?> • <?= timeAgo($u['created_at']) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;

    const colors = {
        primary: '#2563EB', success: '#059669', warning: '#D97706',
        danger: '#DC2626', info: '#0891B2', secondary: '#6B7280',
        purple: '#7C3AED', dark: '#1F2937'
    };

    // Revenue Chart (Line + Bar combo)
    const revLabels = <?= json_encode(array_map(fn($r) => date('M Y', strtotime($r['month'].'-01')), $chartRevenue)) ?>;
    const revData = <?= json_encode(array_map(fn($r) => (float)$r['revenue'], $chartRevenue)) ?>;
    const revJobs = <?= json_encode(array_map(fn($r) => (int)$r['jobs'], $chartRevenue)) ?>;
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: revLabels,
            datasets: [{
                label: 'Revenue (Rs.)',
                data: revData,
                backgroundColor: colors.primary + '33',
                borderColor: colors.primary,
                borderWidth: 2,
                borderRadius: 8,
                order: 2,
            }, {
                label: 'Jobs Completed',
                data: revJobs,
                type: 'line',
                borderColor: colors.success,
                backgroundColor: colors.success + '22',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                order: 1,
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });

    // Status Doughnut
    const statLabels = <?= json_encode(array_map(fn($s) => ucfirst(str_replace('_',' ',$s['status'])), $chartStatus)) ?>;
    const statData = <?= json_encode(array_map(fn($s) => (int)$s['cnt'], $chartStatus)) ?>;
    const statColors = <?= json_encode(array_map(function($s) {
        $colorMap = ['pending'=>'#D97706','accepted'=>'#0891B2','technician_on_way'=>'#2563EB','in_progress'=>'#2563EB','completed'=>'#059669','cancelled'=>'#DC2626','refunded'=>'#6B7280','disputed'=>'#1F2937','confirmed'=>'#0891B2'];
        return $colorMap[$s['status']] ?? '#6B7280';
    }, $chartStatus)) ?>;
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: statLabels, datasets: [{ data: statData, backgroundColor: statColors, borderWidth: 2 }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } } } }
    });

    // Top Services Horizontal Bar
    const svcLabels = <?= json_encode(array_map(fn($s) => $s['name'], $chartServices)) ?>;
    const svcData = <?= json_encode(array_map(fn($s) => (int)$s['cnt'], $chartServices)) ?>;
    new Chart(document.getElementById('servicesChart'), {
        type: 'bar',
        data: { labels: svcLabels, datasets: [{ label: 'Bookings', data: svcData, backgroundColor: [colors.primary, colors.success, colors.warning, colors.info, colors.purple], borderRadius: 6 }] },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
    });

    // Daily Bookings Line
    const dayLabels = <?= json_encode(array_map(fn($d) => date('M d', strtotime($d['day'])), $chartDaily)) ?>;
    const dayData = <?= json_encode(array_map(fn($d) => (int)$d['cnt'], $chartDaily)) ?>;
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: { labels: dayLabels, datasets: [{ label: 'Bookings', data: dayData, borderColor: colors.success, backgroundColor: colors.success + '22', fill: true, tension: 0.3, pointRadius: 3 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
