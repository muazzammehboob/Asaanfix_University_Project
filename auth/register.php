<?php
/**
 * AsaanFix Pakistan - Registration Page
 */
require_once __DIR__ . '/../includes/auth.php';
redirectIfLoggedIn();

$errors = [];
$old = ['name' => '', 'email' => '', 'phone' => '', 'city' => '', 'role' => 'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $old['name']  = sanitize($_POST['name'] ?? '');
    $old['email'] = sanitize($_POST['email'] ?? '');
    $old['phone'] = sanitize($_POST['phone'] ?? '');
    $old['city']  = sanitize($_POST['city'] ?? '');
    $old['role']  = in_array($_POST['role'] ?? '', ['user', 'technician']) ? $_POST['role'] : 'user';
    $password     = $_POST['password'] ?? '';
    $confirm      = $_POST['password_confirm'] ?? '';
    $agree        = isset($_POST['agree']);

    // Validation
    if (empty($old['name']) || strlen($old['name']) < 3) $errors[] = 'Name must be at least 3 characters.';
    if (!isValidEmail($old['email'])) $errors[] = 'Please enter a valid email address.';
    if (!empty($old['phone']) && !isValidPhone($old['phone'])) $errors[] = 'Please enter a valid Pakistani phone number.';
    if (!isStrongPassword($password)) $errors[] = 'Password must be 8+ characters with uppercase, lowercase, and number.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (!$agree) $errors[] = 'You must agree to the Terms of Service.';
    if (empty($old['city'])) $errors[] = 'Please select your city.';
    
    // Check duplicate email
    if (empty($errors)) {
        $existing = dbQueryOne("SELECT id FROM users WHERE email = ?", [$old['email']]);
        if ($existing) $errors[] = 'An account with this email already exists.';
    }
    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));
        
        dbExecute(
            "INSERT INTO users (name, email, phone, password, role, city, verification_token, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$old['name'], $old['email'], $old['phone'], $hashedPassword, $old['role'], $old['city'], $token]
        );
        
        $userId = dbLastId();
        
        // If technician, create technician profile
        if ($old['role'] === 'technician') {
            dbExecute("INSERT INTO technicians (user_id, status) VALUES (?, 'pending')", [$userId]);
            createNotification(1, 'New Technician Application', $old['name'] . ' has applied as a technician.', 'system', '/admin/technicians.php');
        }
        
        // Auto login
        $user = dbQueryOne("SELECT * FROM users WHERE id = ?", [$userId]);
        setUserSession($user);
        setFlash('success', 'Account created successfully! Welcome to AsaanFix.');
        redirect(getDashboardUrl());
    }
}

$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/header.php';

$cities = ['Islamabad','Rawalpindi','Lahore','Karachi','Faisalabad','Peshawar','Quetta','Multan','Sialkot','Gujranwala','Hyderabad','Abbottabad','Mardan','Bahawalpur','Sargodha'];
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width:520px;" data-aos="fade-up">
        <div class="text-center mb-4">
            <a href="<?= APP_URL ?>" class="text-decoration-none">
                <i class="bi bi-tools fs-1 text-primary"></i>
            </a>
        </div>
        <h2>Create Account</h2>
        <p class="auth-subtitle">Join AsaanFix Pakistan today</p>

        <?php if ($errors): ?>
        <div class="alert alert-danger py-2">
            <?php foreach ($errors as $err): ?>
            <div class="small"><i class="bi bi-exclamation-circle me-1"></i><?= $err ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <?= csrfField() ?>

            <!-- Role Selection -->
            <div class="mb-3">
                <label class="form-label">I want to</label>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="role" id="roleUser" value="user" <?= $old['role'] === 'user' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary w-100 py-2" for="roleUser">
                            <i class="bi bi-person d-block fs-4 mb-1"></i>
                            <small class="fw-semibold">Book Services</small>
                        </label>
                    </div>
                    <div class="col-6">
                        <input type="radio" class="btn-check" name="role" id="roleTech" value="technician" <?= $old['role'] === 'technician' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary w-100 py-2" for="roleTech">
                            <i class="bi bi-tools d-block fs-4 mb-1"></i>
                            <small class="fw-semibold">Work as Tech</small>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= $old['name'] ?>" placeholder="Muhammad Ahmed" required minlength="3">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= $old['email'] ?>" placeholder="you@example.com" required>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= $old['phone'] ?>" placeholder="03001234567">
                </div>
                <div class="col-md-6">
                    <label for="city" class="form-label">City</label>
                    <select class="form-select" id="city" name="city" required>
                        <option value="">Select City</option>
                        <?php foreach ($cities as $c): ?>
                        <option value="<?= $c ?>" <?= $old['city'] === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Min 8 characters" required minlength="8">
                </div>
                <div class="col-md-6">
                    <label for="password_confirm" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Repeat password" required>
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                <label class="form-check-label small" for="agree">
                    I agree to the <a href="<?= APP_URL ?>/terms.php" target="_blank">Terms of Service</a> and <a href="<?= APP_URL ?>/privacy.php" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                <i class="bi bi-person-plus me-2"></i>Create Account
            </button>
        </form>

        <div class="divider-text">or</div>
        <p class="text-center mb-0 small">Already have an account? <a href="<?= APP_URL ?>/auth/login.php" class="fw-semibold">Sign In</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
