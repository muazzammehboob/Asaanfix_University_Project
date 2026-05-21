<?php
/**
 * AsaanFix Pakistan - Security Middleware
 * Rate limiting, secure headers, CSP, login attempt tracking
 */

// Constants are loaded by the caller (header.php → functions.php → constants.php)
// Only load if not already defined (e.g., when used standalone)
if (!defined('APP_ENV')) {
    require_once __DIR__ . '/../../config/constants.php';
}

class Security {

    /**
     * Set all secure HTTP headers
     */
    public static function setSecureHeaders(): void {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(self), microphone=(), camera=()');
        
        // Content Security Policy
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://www.googletagmanager.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "img-src 'self' data: blob: https: http:",
            "connect-src 'self' https://ui-avatars.com",
            "frame-src 'self' https://sandbox.jazzcash.com.pk https://easypay.easypaisa.com.pk",
        ]);
        header("Content-Security-Policy: $csp");
        
        // HTTPS enforcement in production
        if (APP_ENV === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Rate limiting using file-based tracking
     */
    public static function rateLimit(string $identifier = '', int $maxRequests = 0, int $windowSeconds = 0): bool {
        $maxRequests = $maxRequests ?: RATE_LIMIT_REQUESTS;
        $windowSeconds = $windowSeconds ?: RATE_LIMIT_WINDOW;
        $identifier = $identifier ?: self::getClientIP();
        
        $rateLimitDir = CACHE_PATH . 'rate_limits' . DIRECTORY_SEPARATOR;
        if (!is_dir($rateLimitDir)) mkdir($rateLimitDir, 0755, true);
        
        $file = $rateLimitDir . md5($identifier) . '.json';
        $now = time();
        $requests = [];
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
            // Keep only requests within the window
            $requests = array_filter($data, fn($ts) => $ts > ($now - $windowSeconds));
        }
        
        if (count($requests) >= $maxRequests) {
            http_response_code(429);
            header('Retry-After: ' . $windowSeconds);
            return false; // Rate limited
        }
        
        $requests[] = $now;
        file_put_contents($file, json_encode(array_values($requests)), LOCK_EX);
        
        return true; // Allowed
    }

    /**
     * Track login attempts for brute-force protection
     */
    public static function checkLoginAttempts(string $email): bool {
        $lockoutDir = CACHE_PATH . 'login_attempts' . DIRECTORY_SEPARATOR;
        if (!is_dir($lockoutDir)) mkdir($lockoutDir, 0755, true);
        
        $file = $lockoutDir . md5($email . self::getClientIP()) . '.json';
        
        if (!file_exists($file)) return true;
        
        $data = json_decode(file_get_contents($file), true);
        
        // Check if locked out
        if (($data['attempts'] ?? 0) >= MAX_LOGIN_ATTEMPTS) {
            $lockoutUntil = ($data['last_attempt'] ?? 0) + LOGIN_LOCKOUT_TIME;
            if (time() < $lockoutUntil) {
                return false; // Still locked out
            }
            // Lockout expired, reset
            unlink($file);
            return true;
        }
        
        return true;
    }

    /**
     * Record a failed login attempt
     */
    public static function recordFailedLogin(string $email): void {
        $lockoutDir = CACHE_PATH . 'login_attempts' . DIRECTORY_SEPARATOR;
        if (!is_dir($lockoutDir)) mkdir($lockoutDir, 0755, true);
        
        $file = $lockoutDir . md5($email . self::getClientIP()) . '.json';
        $data = ['attempts' => 0, 'last_attempt' => 0];
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: $data;
        }
        
        $data['attempts']++;
        $data['last_attempt'] = time();
        $data['ip'] = self::getClientIP();
        
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    /**
     * Clear login attempts on successful login
     */
    public static function clearLoginAttempts(string $email): void {
        $lockoutDir = CACHE_PATH . 'login_attempts' . DIRECTORY_SEPARATOR;
        $file = $lockoutDir . md5($email . self::getClientIP()) . '.json';
        if (file_exists($file)) unlink($file);
    }

    /**
     * Get remaining lockout seconds
     */
    public static function getLockoutRemaining(string $email): int {
        $lockoutDir = CACHE_PATH . 'login_attempts' . DIRECTORY_SEPARATOR;
        $file = $lockoutDir . md5($email . self::getClientIP()) . '.json';
        
        if (!file_exists($file)) return 0;
        
        $data = json_decode(file_get_contents($file), true);
        if (($data['attempts'] ?? 0) < MAX_LOGIN_ATTEMPTS) return 0;
        
        $lockoutUntil = ($data['last_attempt'] ?? 0) + LOGIN_LOCKOUT_TIME;
        $remaining = $lockoutUntil - time();
        
        return max(0, $remaining);
    }

    /**
     * Get client IP address
     */
    public static function getClientIP(): string {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = explode(',', $_SERVER[$header])[0];
                return trim($ip);
            }
        }
        return '127.0.0.1';
    }

    /**
     * Generate a random filename for uploads (prevents enumeration)
     */
    public static function randomFileName(string $originalName): string {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        return bin2hex(random_bytes(16)) . '_' . time() . '.' . $ext;
    }

    /**
     * Validate file upload security
     */
    public static function validateUpload(array $file, array $allowedTypes = []): array {
        $errors = [];
        $allowedTypes = $allowedTypes ?: ALLOWED_IMAGE_TYPES;
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed.';
            return $errors;
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File too large. Maximum size: ' . round(MAX_FILE_SIZE / 1024 / 1024, 1) . 'MB';
        }
        
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes);
        }
        
        // Check for PHP in file contents (extra security)
        $content = file_get_contents($file['tmp_name'], false, null, 0, 1024);
        if (str_contains($content, '<?php') || str_contains($content, '<?=')) {
            $errors[] = 'Invalid file content detected.';
        }
        
        return $errors;
    }
}
