<?php
/**
 * AsaanFix Pakistan - PHP Dev Server Router
 * Usage: php -S localhost:8000 router.php
 * 
 * Provides clean URL support for PHP's built-in server
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing files directly (CSS, JS, images, etc.)
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css', 'js' => 'application/javascript',
        'svg' => 'image/svg+xml', 'png' => 'image/png',
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp', 'woff2' => 'font/woff2',
        'gif' => 'image/gif', 'ico' => 'image/x-icon',
        'json' => 'application/json', 'xml' => 'application/xml',
    ];
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
    }
    return false;
}

// ─── Custom Route Aliases ───
$customRoutes = [
    '/home'             => '/index.php',
    '/login'            => '/auth/login.php',
    '/register'         => '/auth/register.php',
    '/logout'           => '/auth/logout.php',
    '/forgot-password'  => '/auth/forgot-password.php',
    '/sitemap.xml'      => '/sitemap.php',
];

// Check custom routes first
if (isset($customRoutes[$uri])) {
    require __DIR__ . $customRoutes[$uri];
    return true;
}

// ─── API Routes ───
if (str_starts_with($uri, '/api/')) {
    $apiPath = __DIR__ . $uri . '.php';
    if (file_exists($apiPath)) {
        require $apiPath;
        return true;
    }
    // Handle sub-routes like /api/payment/jazzcash-callback
    $segments = explode('/', trim($uri, '/'));
    if (count($segments) >= 3) {
        $apiFile = __DIR__ . '/' . $segments[0] . '/' . $segments[1] . '.php';
        if (file_exists($apiFile)) {
            $_GET['action'] = $segments[2] ?? '';
            require $apiFile;
            return true;
        }
    }
}

// Map clean URLs to .php files
$phpFile = __DIR__ . $uri . '.php';
if ($uri !== '/' && file_exists($phpFile)) {
    require $phpFile;
    return true;
}

// Handle subdirectory routes: /admin/dashboard → /admin/dashboard.php
$segments = explode('/', trim($uri, '/'));
if (count($segments) >= 2) {
    $subFile = __DIR__ . '/' . implode('/', $segments) . '.php';
    if (file_exists($subFile)) {
        require $subFile;
        return true;
    }
}

// Default: serve index.php for root
if ($uri === '/') {
    require __DIR__ . '/index.php';
    return true;
}

// 404
http_response_code(404);
echo '<!DOCTYPE html><html><head><title>404 - Page Not Found</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head><body style="font-family:Inter,sans-serif;text-align:center;padding:5rem;background:#f8fafc;">
<div style="max-width:400px;margin:0 auto;">
<h1 style="font-size:6rem;color:#2563EB;margin:0;font-weight:800;">404</h1>
<p style="font-size:1.2rem;color:#64748B;margin:1rem 0;">The page you\'re looking for doesn\'t exist.</p>
<a href="/home" style="display:inline-block;padding:0.75rem 2rem;background:#2563EB;color:#fff;border-radius:0.5rem;text-decoration:none;font-weight:600;">Go Home</a>
</div></body></html>';
return true;
