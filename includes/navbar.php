<?php
/**
 * AsaanFix Pakistan - Navigation Bar
 * Responsive navbar with role-based menu items & active state
 */

// Detect current page for active nav highlighting
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Strip subfolder prefix for nav matching
$appPath = parse_url(APP_URL, PHP_URL_PATH) ?: '';
if ($appPath && str_starts_with($currentUri, $appPath)) {
    $currentUri = substr($currentUri, strlen($appPath)) ?: '/';
}
$navPages = [
    'home'        => ['/', '/home', '/index.php'],
    'services'    => ['/services', '/services.php', '/service-detail', '/service-detail.php'],
    'technicians' => ['/technicians', '/technicians.php', '/technician-profile', '/technician-profile.php'],
    'about'       => ['/about', '/about.php'],
    'contact'     => ['/contact', '/contact.php'],
];
function isNavActive(string $page): string {
    global $currentUri, $navPages;
    if (isset($navPages[$page])) {
        foreach ($navPages[$page] as $path) {
            if ($currentUri === $path) return ' active';
        }
    }
    return '';
}
?>
<nav class="navbar navbar-expand-lg fixed-top navbar-main" id="mainNavbar">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="<?= APP_URL ?>/home">
            <i class="bi bi-tools brand-icon"></i>
            <span>Asaan<span class="text-primary">Fix</span></span>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-label="Toggle navigation">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Main Menu -->
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link<?= isNavActive('home') ?>" href="<?= APP_URL ?>/home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= isNavActive('services') ?>" href="<?= APP_URL ?>/services">Services</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= isNavActive('technicians') ?>" href="<?= APP_URL ?>/technicians">Technicians</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= isNavActive('about') ?>" href="<?= APP_URL ?>/about">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= isNavActive('contact') ?>" href="<?= APP_URL ?>/contact">Contact</a>
                </li>
            </ul>

            <!-- Right Side -->
            <div class="navbar-nav ms-auto align-items-lg-center gap-2">
                <?php if (isLoggedIn()): ?>
                    <!-- Notifications -->
                    <li class="nav-item dropdown list-unstyled">
                        <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown" aria-label="Notifications">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($notifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge"><?= $notifCount > 9 ? '9+' : $notifCount ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" style="width: 320px;">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold">Notifications</h6>
                                <?php if ($notifCount > 0): ?>
                                <a href="#" class="small text-primary mark-all-read">Mark all read</a>
                                <?php endif; ?>
                            </div>
                            <div class="notification-list" id="notificationList" style="max-height: 300px; overflow-y: auto;">
                                <div class="p-3 text-center text-muted small">
                                    <i class="bi bi-bell-slash d-block fs-3 mb-2"></i>No new notifications
                                </div>
                            </div>
                            <div class="p-2 border-top text-center">
                                <a href="<?= APP_URL ?>/<?= getCurrentUserRole() === 'admin' ? 'admin' : (getCurrentUserRole() === 'technician' ? 'technician' : 'user') ?>/notifications" class="small text-primary">View All</a>
                            </div>
                        </div>
                    </li>

                    <!-- Messages -->
                    <li class="nav-item list-unstyled">
                        <a class="nav-link position-relative" href="<?= APP_URL ?>/user/messages" aria-label="Messages">
                            <i class="bi bi-chat-dots fs-5"></i>
                            <?php if ($msgCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary"><?= $msgCount > 9 ? '9+' : $msgCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- User Menu -->
                    <li class="nav-item dropdown list-unstyled">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                            <div class="avatar-sm">
                                <img src="<?= getAvatarUrl($_SESSION['user_avatar'] ?? null) ?>" alt="Avatar" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                            </div>
                            <span class="d-none d-lg-inline"><?= sanitize($_SESSION['user_name'] ?? 'User') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <small class="text-muted text-uppercase"><?= getCurrentUserRole() ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= getDashboardUrl() ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/<?= getCurrentUserRole() === 'admin' ? 'admin' : (getCurrentUserRole() === 'technician' ? 'technician' : 'user') ?>/profile"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <?php if (getCurrentUserRole() === 'user'): ?>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/user/bookings"><i class="bi bi-calendar-check me-2"></i>My Bookings</a></li>
                            <?php endif; ?>
                            <?php if (getCurrentUserRole() === 'technician'): ?>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/technician/earnings"><i class="bi bi-wallet2 me-2"></i>Earnings</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/login" class="btn btn-outline-primary btn-sm px-3">Login</a>
                    <a href="<?= APP_URL ?>/register" class="btn btn-primary btn-sm px-3">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<!-- Navbar spacer -->
<div style="height: 72px;"></div>
