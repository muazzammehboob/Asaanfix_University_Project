<?php
/**
 * AsaanFix Pakistan - Helper Functions
 * Core utility functions used throughout the application
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../app/helpers/Cache.php';
require_once __DIR__ . '/../app/helpers/ImageOptimizer.php';
require_once __DIR__ . '/../app/helpers/SEO.php';

// ─── SANITIZATION & VALIDATION ───

/**
 * Sanitize user input
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Pakistani phone number
 */
function isValidPhone(string $phone): bool {
    return preg_match('/^(\+92|0)?3[0-9]{9}$/', preg_replace('/[\s\-]/', '', $phone));
}

/**
 * Validate password strength (min 8 chars, 1 upper, 1 lower, 1 number)
 */
function isStrongPassword(string $password): bool {
    return strlen($password) >= 8 
        && preg_match('/[A-Z]/', $password) 
        && preg_match('/[a-z]/', $password) 
        && preg_match('/[0-9]/', $password);
}

// ─── IMAGE HELPERS ───

/**
 * Get avatar URL for a user - uses ui-avatars.com as fallback
 */
function getAvatarUrl(?string $avatar = null, ?string $name = null): string {
    // If user has uploaded a custom avatar (not the default), use it
    if ($avatar && $avatar !== 'default-avatar.png' && $avatar !== 'default-avatar.svg') {
        $localPath = UPLOADS_PATH . 'avatars' . DIRECTORY_SEPARATOR . $avatar;
        if (file_exists($localPath)) {
            return UPLOADS_URL . '/avatars/' . $avatar;
        }
    }
    // Generate a nice initial-based avatar via ui-avatars.com
    $displayName = $name ?? $_SESSION['user_name'] ?? 'User';
    $colors = ['2563EB','7C3AED','0891B2','059669','D97706','DC2626','4F46E5','0D9488'];
    $colorIndex = abs(crc32($displayName)) % count($colors);
    $bg = $colors[$colorIndex];
    return 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=' . $bg . '&color=fff&size=200&font-size=0.4&bold=true';
}

/**
 * Get a service/category placeholder image URL
 */
function getServiceImageUrl(string $category, int $index = 0): string {
    $images = [
        'mobile-repair' => [
            'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1556656793-08538906a9f8?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1512054502232-10a0a035d672?w=400&h=250&fit=crop',
        ],
        'laptop-repair' => [
            'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=400&h=250&fit=crop',
        ],
        'electrician' => [
            'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1555963966-b7ae5404b6ed?w=400&h=250&fit=crop',
            'https://picsum.photos/seed/electrician/400/250',
        ],
        'plumbing' => [
            'https://images.unsplash.com/photo-1504328345606-18bbc8c9d7d1?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?w=400&h=250&fit=crop',
            'https://picsum.photos/seed/plumbing/400/250',
        ],
        'ac-services' => [
            'https://images.unsplash.com/photo-1585338107529-13afc5f02586?w=400&h=250&fit=crop',
            'https://picsum.photos/seed/ac-repair/400/250',
            'https://picsum.photos/seed/ac-install/400/250',
        ],
        'appliance-repair' => [
            'https://images.unsplash.com/photo-1556911220-bff31c812dba?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=400&h=250&fit=crop',
            'https://picsum.photos/seed/appliance/400/250',
        ],
        'home-maintenance' => [
            'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=400&h=250&fit=crop',
            'https://picsum.photos/seed/home-clean/400/250',
        ],
        'it-support' => [
            'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=400&h=250&fit=crop',
            'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=400&h=250&fit=crop',
            'https://picsum.photos/seed/it-support/400/250',
        ],
    ];
    $catImages = $images[$category] ?? $images['home-maintenance'];
    return $catImages[$index % count($catImages)];
}

// ─── DATABASE HELPERS ───

/**
 * Execute a prepared query and return results
 */
function dbQuery(string $sql, array $params = []): array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Execute a prepared query and return single row
 */
function dbQueryOne(string $sql, array $params = []): ?array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();
    return $result ?: null;
}

/**
 * Execute an INSERT/UPDATE/DELETE and return affected rows
 */
function dbExecute(string $sql, array $params = []): int {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Get last insert ID
 */
function dbLastId(): int {
    return (int) getDB()->lastInsertId();
}

// ─── BOOKING HELPERS ───

/**
 * Generate unique booking number
 */
function generateBookingNumber(): string {
    $year = date('Y');
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM bookings WHERE YEAR(created_at) = $year");
    $count = $stmt->fetch()['cnt'] + 1;
    return BOOKING_PREFIX . '-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

// ─── FORMAT HELPERS ───

/**
 * Format price in PKR
 */
function formatPrice(float $amount): string {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 0);
}

/**
 * Format date for display
 */
function formatDate(string $date): string {
    return date('M d, Y', strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime(string $datetime): string {
    return date('M d, Y h:i A', strtotime($datetime));
}

/**
 * Format time for display
 */
function formatTime(string $time): string {
    return date('h:i A', strtotime($time));
}

/**
 * Time ago helper
 */
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return formatDate($datetime);
}

// ─── STATUS HELPERS ───

/**
 * Get booking status badge HTML
 */
function getStatusBadge(string $status): string {
    if (defined('BOOKING_STATUSES') && isset(BOOKING_STATUSES[$status])) {
        $s = BOOKING_STATUSES[$status];
        return '<span class="badge bg-' . $s['color'] . ($s['color']==='warning'?' text-dark':'') . '"><i class="bi ' . $s['icon'] . ' me-1"></i>' . $s['label'] . '</span>';
    }
    // Fallback for legacy statuses
    $badges = [
        'pending'           => '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>',
        'accepted'          => '<span class="badge bg-info"><i class="bi bi-check-circle me-1"></i>Accepted</span>',
        'confirmed'         => '<span class="badge bg-info"><i class="bi bi-check-circle me-1"></i>Confirmed</span>',
        'technician_on_way' => '<span class="badge bg-primary"><i class="bi bi-geo-alt me-1"></i>Technician On Way</span>',
        'in_progress'       => '<span class="badge bg-primary"><i class="bi bi-gear me-1"></i>In Progress</span>',
        'completed'         => '<span class="badge bg-success"><i class="bi bi-check-all me-1"></i>Completed</span>',
        'cancelled'         => '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Cancelled</span>',
        'refunded'          => '<span class="badge bg-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Refunded</span>',
        'disputed'          => '<span class="badge bg-dark"><i class="bi bi-exclamation-triangle me-1"></i>Disputed</span>',
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
}

/**
 * Get star rating HTML
 */
function getStarRating(float $rating, bool $showNumber = true): string {
    $html = '<div class="star-rating d-inline-flex align-items-center">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        } else {
            $html .= '<i class="bi bi-star text-warning"></i>';
        }
    }
    if ($showNumber) {
        $html .= '<span class="ms-1 text-muted small">(' . number_format($rating, 1) . ')</span>';
    }
    $html .= '</div>';
    return $html;
}

// ─── FILE UPLOAD ───

/**
 * Handle image upload
 */
function uploadImage(array $file, string $directory, string $prefix = 'img'): ?string {
    // Try optimized upload first (WebP conversion + compression)
    $result = ImageOptimizer::upload($file, $directory, $prefix);
    if ($result) return $result;

    // Fallback to basic upload
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_FILE_SIZE) return null;
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) return null;
    
    if (!is_dir($directory)) mkdir($directory, 0755, true);
    
    // Randomized filename to prevent enumeration
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $path = $directory . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return $filename;
    }
    return null;
}

// ─── PAGINATION ───

/**
 * Get pagination data
 */
function getPagination(int $totalItems, int $currentPage, int $perPage = ITEMS_PER_PAGE): array {
    $totalPages = max(1, ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $totalItems,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

/**
 * Render pagination HTML
 */
function renderPagination(array $pagination, string $baseUrl): string {
    if ($pagination['total_pages'] <= 1) return '';
    
    $separator = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous
    $html .= '<li class="page-item ' . ($pagination['has_prev'] ? '' : 'disabled') . '">';
    $html .= '<a class="page-link" href="' . $baseUrl . $separator . 'page=' . ($pagination['current_page'] - 1) . '"><i class="bi bi-chevron-left"></i></a></li>';
    
    // Pages
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        if ($i == 1 || $i == $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2) {
            $html .= '<li class="page-item ' . ($i == $pagination['current_page'] ? 'active' : '') . '">';
            $html .= '<a class="page-link" href="' . $baseUrl . $separator . 'page=' . $i . '">' . $i . '</a></li>';
        } elseif (abs($i - $pagination['current_page']) == 3) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next
    $html .= '<li class="page-item ' . ($pagination['has_next'] ? '' : 'disabled') . '">';
    $html .= '<a class="page-link" href="' . $baseUrl . $separator . 'page=' . ($pagination['current_page'] + 1) . '"><i class="bi bi-chevron-right"></i></a></li>';
    
    $html .= '</ul></nav>';
    return $html;
}

// ─── NOTIFICATION HELPERS ───

/**
 * Create a notification
 */
function createNotification(int $userId, string $title, string $message, string $type = 'system', ?string $link = null): void {
    dbExecute(
        "INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)",
        [$userId, $title, $message, $type, $link]
    );
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount(int $userId): int {
    $result = dbQueryOne("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
    return $result['cnt'] ?? 0;
}

/**
 * Get unread message count
 */
function getUnreadMessageCount(int $userId): int {
    $result = dbQueryOne("SELECT COUNT(*) as cnt FROM messages WHERE receiver_id = ? AND is_read = 0", [$userId]);
    return $result['cnt'] ?? 0;
}

// ─── REDIRECT HELPERS ───

/**
 * Redirect to URL
 */
function redirect(string $url): void {
    header("Location: $url");
    exit;
}

/**
 * Redirect back
 */
function redirectBack(): void {
    $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
    redirect($referer);
}

/**
 * JSON response for AJAX
 */
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ─── ADMIN LOG ───

/**
 * Log admin action
 */
function logAdminAction(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void {
    if (!isLoggedIn() || getCurrentUserRole() !== 'admin') return;
    
    dbExecute(
        "INSERT INTO admin_logs (admin_id, action, entity_type, entity_id, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [
            getCurrentUserId(), $action, $entityType, $entityId, $details,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]
    );
}

// ─── CACHED QUERY HELPERS ───

/**
 * Get categories with caching
 */
function getCachedCategories(): array {
    return Cache::remember('categories_active', 1800, function() {
        return dbQuery("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
    });
}

/**
 * Get services with caching
 */
function getCachedServices(int $limit = 50): array {
    return Cache::remember('services_active_' . $limit, 1800, function() use ($limit) {
        return dbQuery(
            "SELECT s.*, c.name as category_name, c.icon as category_icon, c.slug as category_slug 
             FROM services s JOIN categories c ON s.category_id = c.id 
             WHERE s.is_active = 1 ORDER BY s.name ASC LIMIT ?",
            [$limit]
        );
    });
}

/**
 * Get homepage stats with caching
 */
function getCachedStats(): array {
    return Cache::remember('homepage_stats', 600, function() {
        return [
            'technicians' => dbQueryOne("SELECT COUNT(*) as cnt FROM technicians WHERE status='approved'")['cnt'] ?? 0,
            'bookings'    => dbQueryOne("SELECT COUNT(*) as cnt FROM bookings WHERE status='completed'")['cnt'] ?? 0,
            'users'       => dbQueryOne("SELECT COUNT(*) as cnt FROM users WHERE role='user' AND is_active=1")['cnt'] ?? 0,
        ];
    });
}

/**
 * Clear relevant caches when data changes
 */
function clearServiceCaches(): void {
    Cache::forget('categories_active');
    Cache::forget('services_active_50');
    Cache::forget('homepage_stats');
}
