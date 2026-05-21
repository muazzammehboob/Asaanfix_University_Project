<?php
/**
 * AsaanFix Pakistan - Login Page
 * Enhanced with rate limiting and login attempt protection
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../app/middleware/Security.php';
redirectIfLoggedIn();

$errors = [];
$email = '';
$lockoutRemaining = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCSRF();
    
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $errors[] = 'Please fill in all fields.';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Invalid email format.';
    } else {
        // Check rate limiting
        if (!Security::rateLimit('login_' . Security::getClientIP(), 10, 300)) {
            $errors[] = 'Too many requests. Please wait a few minutes.';
        }
        // Check login attempts lockout
        elseif (!Security::checkLoginAttempts($email)) {
            $lockoutRemaining = Security::getLockoutRemaining($email);
            $mins = ceil($lockoutRemaining / 60);
            $errors[] = "Account temporarily locked. Try again in {$mins} minute(s).";
        } else {
            $user = dbQueryOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
            
            if ($user && password_verify($password, $user['password'])) {
                // Clear failed attempts and update last login
                Security::clearLoginAttempts($email);
                dbExecute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
                setUserSession($user);
                setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                redirect(getDashboardUrl());
            } else {
                Security::recordFailedLogin($email);
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" data-aos="fade-up">
        <div class="text-center mb-4">
            <a href="<?= APP_URL ?>/home" class="text-decoration-none">
                <i class="bi bi-tools fs-1 text-primary"></i>
            </a>
        </div>
        <h2>Welcome Back</h2>
        <p class="auth-subtitle">Sign in to your AsaanFix account</p>

        <?php if ($errors): ?>
        <div class="alert alert-danger py-2">
            <?php foreach ($errors as $err): ?>
            <div class="small"><i class="bi bi-exclamation-circle me-1"></i><?= $err ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <?= csrfField() ?>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control border-start-0" id="email" name="email" 
                           value="<?= sanitize($email) ?>" placeholder="you@example.com" required>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label">Password</label>
                    <a href="<?= APP_URL ?>/forgot-password" class="small text-primary">Forgot Password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" placeholder="••••••••" required>
                    <button type="button" class="input-group-text bg-light border-start-0 toggle-password" data-target="#password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label small" for="remember">Keep me signed in</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="divider-text">or</div>

        <p class="text-center mb-0 small">
            Don't have an account? 
            <a href="<?= APP_URL ?>/register" class="fw-semibold">Create Account</a>
        </p>

        <!-- Demo credentials -->
        <div class="mt-4 p-3 rounded-3" style="background:var(--surface);border:1px dashed var(--border);">
            <p class="small fw-semibold mb-2 text-center"><i class="bi bi-info-circle me-1"></i>Quick Demo Login</p>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm demo-login" data-email="admin@fixithub.pk" data-pass="password">
                    <i class="bi bi-shield-lock me-1"></i>Login as Admin
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm demo-login" data-email="ahmed@example.com" data-pass="password">
                    <i class="bi bi-person me-1"></i>Login as User
                </button>
                <button type="button" class="btn btn-outline-success btn-sm demo-login" data-email="usman@example.com" data-pass="password">
                    <i class="bi bi-person-badge me-1"></i>Login as Technician
                </button>
            </div>
        </div>
        <script>
        document.querySelectorAll('.demo-login').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = document.querySelector('form');
                document.getElementById('email').value = this.dataset.email;
                document.getElementById('password').value = this.dataset.pass;
                form.submit();
            });
        });
        </script>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
