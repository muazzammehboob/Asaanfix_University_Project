<?php
/**
 * AsaanFix Pakistan - Forgot Password
 */
require_once __DIR__ . '/../includes/auth.php';
redirectIfLoggedIn();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    $email = sanitize($_POST['email'] ?? '');
    
    if (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $user = dbQueryOne("SELECT id, name FROM users WHERE email = ? AND is_active = 1", [$email]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            dbExecute("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?", [$token, $expiry, $user['id']]);
            // In production, send email with reset link
            $success = true;
        } else {
            // Don't reveal if email exists
            $success = true;
        }
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" data-aos="fade-up">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:64px;height:64px;background:rgba(var(--primary-rgb),0.1);">
                <i class="bi bi-key fs-3 text-primary"></i>
            </div>
        </div>
        <h2>Forgot Password?</h2>
        <p class="auth-subtitle">Enter your email and we'll send you a reset link</p>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            If an account with that email exists, a password reset link has been sent. Please check your inbox.
        </div>
        <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-primary w-100">Back to Login</a>
        <?php else: ?>
            <?php if ($errors): ?>
            <div class="alert alert-danger py-2">
                <?php foreach ($errors as $err): ?>
                <div class="small"><i class="bi bi-exclamation-circle me-1"></i><?= $err ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <?= csrfField() ?>
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                    <i class="bi bi-envelope me-2"></i>Send Reset Link
                </button>
            </form>
            <p class="text-center mb-0 small">
                Remember your password? <a href="<?= APP_URL ?>/auth/login.php" class="fw-semibold">Sign In</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
