<?php
/**
 * AsaanFix Pakistan - Technician Availability
 */
require_once __DIR__ . '/../includes/auth.php';
requireTechnician();
$tech = getCurrentTechnicianProfile();
$techId = $tech['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;
    $from = sanitize($_POST['available_from'] ?? '09:00');
    $to = sanitize($_POST['available_to'] ?? '18:00');
    $days = isset($_POST['days']) ? implode(',', $_POST['days']) : '';
    
    dbExecute("UPDATE technicians SET is_available = ?, available_from = ?, available_to = ?, working_days = ? WHERE id = ?",
        [$isAvailable, $from, $to, $days, $techId]);
    $tech = getCurrentTechnicianProfile();
    setFlash('success', 'Availability updated!');
    redirect(APP_URL . '/technician/availability.php');
}

$pendingBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE technician_id = ? AND status='pending'", [$techId])['c'];
$pageTitle = 'Availability';
$bodyClass = 'dashboard-page';
$currentPage = 'availability';
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
$workingDays = explode(',', $tech['working_days'] ?? '');
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>
    <h4 class="fw-bold mb-4">Availability Settings</h4>

    <div class="card-custom p-4" style="max-width:600px;">
        <form method="POST">
            <?= csrfField() ?>

            <div class="mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_available" id="isAvail" <?= $tech['is_available'] ? 'checked' : '' ?> style="width:3rem;height:1.5rem;">
                    <label class="form-check-label fw-bold ms-2 fs-5" for="isAvail">
                        <?= $tech['is_available'] ? '<span class="text-success">Available</span>' : '<span class="text-danger">Unavailable</span>' ?>
                    </label>
                </div>
                <small class="text-muted">Toggle off if you're not accepting new bookings.</small>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="form-label fw-semibold">Available From</label>
                    <input type="time" class="form-control" name="available_from" value="<?= $tech['available_from'] ?? '09:00' ?>">
                </div>
                <div class="col-6">
                    <label class="form-label fw-semibold">Available To</label>
                    <input type="time" class="form-control" name="available_to" value="<?= $tech['available_to'] ?? '18:00' ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Working Days</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day): ?>
                    <div>
                        <input type="checkbox" class="btn-check" name="days[]" value="<?= $day ?>" id="day<?= $day ?>" <?= in_array($day, $workingDays) ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary btn-sm px-3" for="day<?= $day ?>"><?= $day ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-2"></i>Save Availability</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
