<?php
/**
 * AsaanFix Pakistan - Global Header Template
 * Include at the top of every page
 * 
 * Required variables before including:
 * $pageTitle - Page title string
 * Optional:
 * $pageDescription - Meta description
 * $extraCSS - Additional CSS files array
 * $bodyClass - Additional body classes
 * $pageKeywords - Meta keywords
 * $pageImage - Open Graph image URL
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../app/middleware/Security.php';

// Set secure HTTP headers
Security::setSecureHeaders();

$pageTitle = isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME;
$pageDescription = $pageDescription ?? 'Book trusted technicians for mobile repair, electrician, plumbing, AC services and more across Pakistan.';
$pageKeywords = $pageKeywords ?? 'fixithub, technician, repair, plumbing, electrician, pakistan, home services, mobile repair';
$extraCSS = $extraCSS ?? [];
$bodyClass = $bodyClass ?? '';

// Initialize SEO
SEO::init([
    'title'       => $pageTitle,
    'description' => $pageDescription,
    'keywords'    => $pageKeywords,
    'image'       => $pageImage ?? ASSETS_URL . '/images/og-banner.png',
]);

// Get notification/message counts for logged in users
$notifCount = 0;
$msgCount = 0;
if (isLoggedIn()) {
    $notifCount = getUnreadNotificationCount(getCurrentUserId());
    $msgCount = getUnreadMessageCount(getCurrentUserId());
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563EB">
    <meta name="app-url" content="<?= APP_URL ?>">
    <meta name="csrf-token" content="<?= generateCSRFToken() ?>">
    <title><?= sanitize($pageTitle) ?></title>

    <!-- SEO Meta Tags + Open Graph + Twitter Cards -->
    <?= SEO::renderMeta() ?>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= ASSETS_URL ?>/images/favicon.svg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- AOS Animations -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">

    <?php foreach ($extraCSS as $css): ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/<?= $css ?>">
    <?php endforeach; ?>

    <!-- JSON-LD Structured Data -->
    <?= SEO::renderJsonLd() ?>
</head>
<body class="<?= $bodyClass ?>">
<?php include __DIR__ . '/navbar.php'; ?>

<!-- Flash Messages (SweetAlert2 enhanced) -->
<?php $flash = getFlash(); if ($flash): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: '<?= $flash['type'] === 'danger' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : ($flash['type'] === 'success' ? 'success' : 'info')) ?>',
            title: '<?= $flash['type'] === 'success' ? 'Success!' : ($flash['type'] === 'danger' ? 'Error!' : 'Notice') ?>',
            text: <?= json_encode($flash['message']) ?>,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
        });
    }
});
</script>
<?php endif; ?>

<main>
