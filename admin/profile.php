<?php
/**
 * AsaanFix Pakistan - Admin Profile
 */
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$userId = getCurrentUserId();
$user = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $name  = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (strlen($name) < 3) $errors[] = 'Name must be at least 3 characters.';

    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    if (!empty($newPass)) {
        if (!isStrongPassword($newPass)) $errors[] = 'Password must be 8+ chars with uppercase, lowercase, number.';
        if ($newPass !== $confirmPass) $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        dbExecute("UPDATE users SET name = ?, phone = ? WHERE id = ?", [$name, $phone, $userId]);
        if (!empty($newPass)) {
            dbExecute("UPDATE users SET password = ? WHERE id = ?", [password_hash($newPass, PASSWORD_DEFAULT), $userId]);
        }
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarName = uploadImage($_FILES['avatar'], AVATAR_DIR, 'admin');
            if ($avatarName) { dbExecute("UPDATE users SET avatar = ? WHERE id = ?", [$avatarName, $userId]); $_SESSION['user_avatar'] = $avatarName; }
        }
        $_SESSION['user_name'] = $name;
        $user = getCurrentUser();
        logAdminAction('update_profile', 'user', $userId);
        $success = true;
    }
}

$pendingTechs = dbQueryOne("SELECT COUNT(*) as c FROM technicians WHERE status='pending'")['c'];
$activeBookings = dbQueryOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('pending','confirmed','in_progress')")['c'];
$pageTitle = 'Admin Profile';
$bodyClass = 'dashboard-page';
$currentPage = 'profile';
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
    <h4 class="fw-bold mb-4">Admin Profile</h4>

    <?php if ($success): ?><div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Profile updated!</div><?php endif; ?>
    <?php if ($errors): ?><div class="alert alert-danger"><?php foreach($errors as $e): ?><div class="small"><?= $e ?></div><?php endforeach; ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card-custom p-4 text-center">
                    <img src="<?= getAvatarUrl($user['avatar'], $user['name'] ?? '') ?>" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;">
                    <h6 class="fw-bold"><?= sanitize($user['name']) ?></h6>
                    <p class="text-muted small"><?= sanitize($user['email']) ?></p>
                    <span class="badge bg-danger mb-3">Administrator</span>
                    <label class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-camera me-1"></i>Change Photo<input type="file" name="avatar" accept="image/*" class="d-none"></label>
                    <hr><small class="text-muted">Member since <?= formatDate($user['created_at']) ?></small>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Personal Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" class="form-control" name="name" value="<?= sanitize($user['name']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" class="form-control" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>"></div>
                    </div>
                </div>
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Change Password</h5>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">New Password</label><input type="password" class="form-control" name="new_password" placeholder="Leave blank to keep current"></div>
                        <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" class="form-control" name="confirm_password"></div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
