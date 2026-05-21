<?php
/**
 * AsaanFix Pakistan - Technician Earnings
 */
require_once __DIR__ . '/../includes/auth.php';
requireTechnician();

$tech = getCurrentTechnicianProfile();
$techId = $tech['id'];

$totalEarnings = dbQueryOne("SELECT COALESCE(SUM(total_amount),0) as s FROM bookings WHERE technician_id = ? AND status='completed'", [$techId])['s'];
$monthEarnings = dbQueryOne("SELECT COALESCE(SUM(total_amount),0) as s FROM bookings WHERE technician_id = ? AND status='completed' AND MONTH(completed_at)=MONTH(NOW()) AND YEAR(completed_at)=YEAR(NOW())", [$techId])['s'];
$weekEarnings = dbQueryOne("SELECT COALESCE(SUM(total_amount),0) as s FROM bookings WHERE technician_id = ? AND status='completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)", [$techId])['s'];
$avgPerJob = $tech['total_jobs'] > 0 ? $totalEarnings / $tech['total_jobs'] : 0;

$earningsHistory = dbQuery(
    "SELECT b.booking_number, b.total_amount, b.completed_at, s.name as service_name, u.name as user_name, p.method as payment_method
     FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.user_id = u.id LEFT JOIN payments p ON p.booking_id = b.id
     WHERE b.technician_id = ? AND b.status = 'completed' ORDER BY b.completed_at DESC LIMIT 20", [$techId]
);

$pendingBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE technician_id = ? AND status='pending'", [$techId])['c'];
$pageTitle = 'Earnings';
$bodyClass = 'dashboard-page';
$currentPage = 'earnings';
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
    <h4 class="fw-bold mb-4">Earnings</h4>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--success);"><i class="bi bi-cash-stack"></i></div>
                <h3><?= formatPrice($totalEarnings) ?></h3><p>Total Earnings</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--primary);"><i class="bi bi-calendar-month"></i></div>
                <h3><?= formatPrice($monthEarnings) ?></h3><p>This Month</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--info);"><i class="bi bi-calendar-week"></i></div>
                <h3><?= formatPrice($weekEarnings) ?></h3><p>This Week</p>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-icon text-white" style="background:var(--accent);"><i class="bi bi-graph-up"></i></div>
                <h3><?= formatPrice($avgPerJob) ?></h3><p>Avg per Job</p>
            </div>
        </div>
    </div>

    <div class="card-custom p-4">
        <h5 class="fw-bold mb-3">Earnings History</h5>
        <?php if (empty($earningsHistory)): ?>
        <p class="text-center text-muted py-3">No completed jobs yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Booking</th><th>Service</th><th>Customer</th><th>Payment</th><th>Date</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                <?php foreach ($earningsHistory as $e): ?>
                <tr>
                    <td class="fw-semibold text-primary small"><?= $e['booking_number'] ?></td>
                    <td class="small"><?= sanitize($e['service_name']) ?></td>
                    <td class="small"><?= sanitize($e['user_name']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= ucfirst($e['payment_method'] ?? 'Cash') ?></span></td>
                    <td class="small"><?= $e['completed_at'] ? formatDate($e['completed_at']) : '—' ?></td>
                    <td class="text-end fw-bold text-success"><?= formatPrice($e['total_amount']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
