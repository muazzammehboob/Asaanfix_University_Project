<?php
/**
 * AsaanFix Pakistan - Technician Profile Setup
 */
require_once __DIR__ . '/../includes/auth.php';
requireTechnician();

$userId = getCurrentUserId();
$user = getCurrentUser();
$tech = getCurrentTechnicianProfile();
$techId = $tech['id'];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');
    $skills = sanitize($_POST['skills'] ?? '');
    $hourlyRate = (float)($_POST['hourly_rate'] ?? 0);
    $expYears = (int)($_POST['experience_years'] ?? 0);
    $serviceAreas = sanitize($_POST['service_areas'] ?? '');
    $cnic = sanitize($_POST['cnic'] ?? '');
    $selectedServices = $_POST['services'] ?? [];

    if (strlen($name) < 3) $errors[] = 'Name is required.';
    if ($hourlyRate < 100) $errors[] = 'Hourly rate must be at least Rs. 100.';

    if (empty($errors)) {
        dbExecute("UPDATE users SET name = ?, phone = ? WHERE id = ?", [$name, $phone, $userId]);
        dbExecute("UPDATE technicians SET bio = ?, skills = ?, hourly_rate = ?, experience_years = ?, service_areas = ?, cnic = ? WHERE id = ?",
            [$bio, $skills, $hourlyRate, $expYears, $serviceAreas, $cnic, $techId]);
        
        // Update services
        dbExecute("DELETE FROM technician_services WHERE technician_id = ?", [$techId]);
        foreach ($selectedServices as $svcId) {
            dbExecute("INSERT INTO technician_services (technician_id, service_id) VALUES (?, ?)", [$techId, (int)$svcId]);
        }

        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarName = uploadImage($_FILES['avatar'], AVATAR_DIR, 'tech');
            if ($avatarName) { dbExecute("UPDATE users SET avatar = ? WHERE id = ?", [$avatarName, $userId]); $_SESSION['user_avatar'] = $avatarName; }
        }

        $_SESSION['user_name'] = $name;
        $tech = getCurrentTechnicianProfile();
        $success = true;
    }
}

$allServices = dbQuery("SELECT s.*, c.name as cat_name FROM services s JOIN categories c ON s.category_id = c.id WHERE s.is_active = 1 ORDER BY c.sort_order, s.name");
$myServiceIds = array_column(dbQuery("SELECT service_id FROM technician_services WHERE technician_id = ?", [$techId]), 'service_id');

$pendingBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE technician_id = ? AND status='pending'", [$techId])['c'];
$pageTitle = 'Profile & Skills';
$bodyClass = 'dashboard-page';
$currentPage = 'profile';
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
    <h4 class="fw-bold mb-4">Profile & Skills</h4>

    <?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Profile updated!</div><?php endif; ?>
    <?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e): ?><div class="small"><?= $e ?></div><?php endforeach; ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card-custom p-4 text-center">
                    <img src="<?= getAvatarUrl($user['avatar'], $user['name'] ?? '') ?>" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;">
                    <h6 class="fw-bold"><?= sanitize($user['name']) ?></h6>
                    <?= getStarRating($tech['avg_rating']) ?>
                    <p class="small text-muted mt-1"><?= $tech['total_reviews'] ?> reviews • <?= $tech['total_jobs'] ?> jobs</p>
                    <span class="badge bg-<?= $tech['status']==='approved'?'success':($tech['status']==='pending'?'warning':'danger') ?>"><?= ucfirst($tech['status']) ?></span>
                    <hr>
                    <label class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-camera me-1"></i>Change Photo<input type="file" name="avatar" accept="image/*" class="d-none"></label>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Personal Info</h5>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" class="form-control" name="name" value="<?= sanitize($user['name']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>"></div>
                        <div class="col-md-6"><label class="form-label">CNIC</label><input type="text" class="form-control" name="cnic" value="<?= sanitize($tech['cnic'] ?? '') ?>" placeholder="3520112345678" maxlength="13"></div>
                        <div class="col-md-6"><label class="form-label">Experience (years)</label><input type="number" class="form-control" name="experience_years" value="<?= $tech['experience_years'] ?>" min="0" max="50"></div>
                        <div class="col-12"><label class="form-label">Bio / About You</label><textarea class="form-control" name="bio" rows="3"><?= sanitize($tech['bio'] ?? '') ?></textarea></div>
                    </div>
                </div>
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Professional Details</h5>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Hourly Rate (PKR) *</label><input type="number" class="form-control" name="hourly_rate" value="<?= $tech['hourly_rate'] ?>" min="100" step="50" required></div>
                        <div class="col-md-6"><label class="form-label">Skills (comma separated)</label><input type="text" class="form-control" name="skills" value="<?= sanitize($tech['skills'] ?? '') ?>" placeholder="Wiring, AC Repair, Plumbing"></div>
                        <div class="col-12"><label class="form-label">Service Areas</label><input type="text" class="form-control" name="service_areas" value="<?= sanitize($tech['service_areas'] ?? '') ?>" placeholder="Islamabad, Rawalpindi"></div>
                    </div>
                </div>
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Services I Offer</h5>
                    <div class="row g-2">
                        <?php $lastCat = ''; foreach ($allServices as $s):
                            if ($s['cat_name'] !== $lastCat) { $lastCat = $s['cat_name']; echo '<div class="col-12"><strong class="small text-muted">'.$lastCat.'</strong></div>'; }
                        ?>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="services[]" value="<?= $s['id'] ?>" id="svc<?= $s['id'] ?>" <?= in_array($s['id'], $myServiceIds) ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="svc<?= $s['id'] ?>"><?= sanitize($s['name']) ?> (<?= formatPrice($s['base_price']) ?>)</label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-2"></i>Save Profile</button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
