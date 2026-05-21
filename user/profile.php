<?php
/**
 * AsaanFix Pakistan - User Profile
 */
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$userId = getCurrentUserId();
$user = getCurrentUser();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $name    = sanitize($_POST['name'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $city    = sanitize($_POST['city'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (strlen($name) < 3) $errors[] = 'Name must be at least 3 characters.';
    
    // Password change
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    if (!empty($newPass)) {
        if (!isStrongPassword($newPass)) $errors[] = 'New password must be 8+ chars with uppercase, lowercase, number.';
        if ($newPass !== $confirmPass) $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        dbExecute("UPDATE users SET name = ?, phone = ?, city = ?, address = ? WHERE id = ?",
            [$name, $phone, $city, $address, $userId]);
        
        if (!empty($newPass)) {
            dbExecute("UPDATE users SET password = ? WHERE id = ?", [password_hash($newPass, PASSWORD_DEFAULT), $userId]);
        }

        // Avatar upload
        if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarName = uploadImage($_FILES['avatar'], AVATAR_DIR, 'user');
            if ($avatarName) {
                dbExecute("UPDATE users SET avatar = ? WHERE id = ?", [$avatarName, $userId]);
                $_SESSION['user_avatar'] = $avatarName;
            }
        }

        $_SESSION['user_name'] = $name;
        $user = getCurrentUser();
        $success = true;
    }
}

$pageTitle = 'My Profile';
$bodyClass = 'dashboard-page';
$currentPage = 'profile';
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
$cities = ['Islamabad','Rawalpindi','Lahore','Karachi','Faisalabad','Peshawar','Quetta','Multan','Sialkot','Gujranwala','Hyderabad'];
?>

<div class="dashboard-content">
    <button class="btn btn-outline-primary d-lg-none mb-3" id="sidebarToggle"><i class="bi bi-list me-1"></i>Menu</button>
    <h4 class="fw-bold mb-4">My Profile</h4>

    <?php if ($success): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Profile updated successfully!</div>
    <?php endif; ?>
    <?php if ($errors): ?>
    <div class="alert alert-danger"><?php foreach($errors as $e): ?><div class="small"><?= $e ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card-custom p-4 text-center">
                    <img src="<?= getAvatarUrl($user['avatar'], $user['name'] ?? '') ?>" class="rounded-circle mb-3" width="120" height="120" style="object-fit:cover;" alt="">
                    <h6 class="fw-bold"><?= sanitize($user['name']) ?></h6>
                    <p class="text-muted small"><?= sanitize($user['email']) ?></p>
                    <label class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-camera me-1"></i>Change Photo
                        <input type="file" name="avatar" accept="image/*" class="d-none">
                    </label>
                    <hr>
                    <small class="text-muted">Member since <?= formatDate($user['created_at']) ?></small>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Personal Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" value="<?= sanitize($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <select class="form-select" name="city">
                                <option value="">Select City</option>
                                <?php foreach ($cities as $c): ?>
                                <option value="<?= $c ?>" <?= ($user['city'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?= sanitize($user['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3">Change Password</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="new_password" placeholder="Leave blank to keep current">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" name="confirm_password">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
