<?php
/**
 * AsaanFix Pakistan - Application Constants
 * All values now sourced from .env where applicable
 */

require_once __DIR__ . '/env.php';

// Application
define('APP_NAME', env('APP_NAME', 'AsaanFix Pakistan'));
define('APP_VERSION', env('APP_VERSION', '2.0.0'));
define('APP_URL', env('APP_URL', 'http://localhost:8000'));
define('APP_ENV', env('APP_ENV', 'development'));
define('APP_DEBUG', env('APP_DEBUG', false));
define('APP_EMAIL', env('APP_EMAIL', 'info@asaanfix.pk'));
define('APP_PHONE', env('APP_PHONE', '+92 300 1234567'));

// Paths
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', ROOT_PATH . 'cache' . DIRECTORY_SEPARATOR);
define('ASSETS_URL', APP_URL . '/assets');
define('UPLOADS_URL', APP_URL . '/uploads');

// Uploads
define('MAX_FILE_SIZE', (int) env('MAX_FILE_SIZE', 5 * 1024 * 1024)); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('AVATAR_DIR', UPLOADS_PATH . 'avatars' . DIRECTORY_SEPARATOR);
define('SERVICE_IMG_DIR', UPLOADS_PATH . 'services' . DIRECTORY_SEPARATOR);

// Booking
define('BOOKING_PREFIX', 'FH');
define('MIN_BOOKING_HOURS', 2);
define('MAX_BOOKING_DAYS', 30);

// Booking Status Flow
define('BOOKING_STATUSES', [
    'pending'            => ['label' => 'Pending',            'color' => 'warning',   'icon' => 'bi-clock'],
    'accepted'           => ['label' => 'Accepted',           'color' => 'info',      'icon' => 'bi-check-circle'],
    'technician_on_way'  => ['label' => 'Technician On Way',  'color' => 'primary',   'icon' => 'bi-geo-alt'],
    'in_progress'        => ['label' => 'In Progress',        'color' => 'primary',   'icon' => 'bi-gear'],
    'completed'          => ['label' => 'Completed',          'color' => 'success',   'icon' => 'bi-check-all'],
    'cancelled'          => ['label' => 'Cancelled',          'color' => 'danger',    'icon' => 'bi-x-circle'],
    'refunded'           => ['label' => 'Refunded',           'color' => 'secondary', 'icon' => 'bi-arrow-counterclockwise'],
    'disputed'           => ['label' => 'Disputed',           'color' => 'dark',      'icon' => 'bi-exclamation-triangle'],
]);

// Notification Types
define('NOTIFICATION_TYPES', [
    'booking'  => ['label' => 'Booking',  'icon' => 'bi-calendar-check', 'color' => 'primary'],
    'message'  => ['label' => 'Message',  'icon' => 'bi-chat-dots',      'color' => 'info'],
    'payment'  => ['label' => 'Payment',  'icon' => 'bi-credit-card',    'color' => 'success'],
    'system'   => ['label' => 'System',   'icon' => 'bi-gear',           'color' => 'secondary'],
    'review'   => ['label' => 'Review',   'icon' => 'bi-star',           'color' => 'warning'],
    'admin'    => ['label' => 'Admin',    'icon' => 'bi-shield-check',   'color' => 'danger'],
]);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Security
define('CSRF_TOKEN_NAME', env('CSRF_TOKEN_NAME', 'csrf_token'));
define('MAX_LOGIN_ATTEMPTS', (int) env('MAX_LOGIN_ATTEMPTS', 5));
define('LOGIN_LOCKOUT_TIME', (int) env('LOGIN_LOCKOUT_TIME', 900));
define('RATE_LIMIT_REQUESTS', (int) env('RATE_LIMIT_REQUESTS', 60));
define('RATE_LIMIT_WINDOW', (int) env('RATE_LIMIT_WINDOW', 60));
define('JWT_SECRET', env('JWT_SECRET', 'fixithub_default_jwt_secret'));

// Currency
define('CURRENCY_SYMBOL', 'Rs.');
define('CURRENCY_CODE', 'PKR');

// Cache
define('CACHE_ENABLED', env('CACHE_ENABLED', true));
define('CACHE_TTL', (int) env('CACHE_TTL', 3600));

// Payment Methods
define('PAYMENT_METHODS', [
    'cash'          => 'Cash on Delivery',
    'jazzcash'      => 'JazzCash',
    'easypaisa'     => 'EasyPaisa',
    'bank_transfer' => 'Bank Transfer',
    'card'          => 'Credit/Debit Card',
]);
