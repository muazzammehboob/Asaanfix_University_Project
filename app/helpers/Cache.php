<?php
/**
 * AsaanFix Pakistan - File Cache System
 * Simple but effective file-based caching for categories, services, stats
 */

class Cache {
    private static string $dir = '';
    
    /**
     * Initialize cache directory
     */
    private static function init(): void {
        if (empty(self::$dir)) {
            self::$dir = defined('CACHE_PATH') ? CACHE_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
            if (!is_dir(self::$dir)) {
                mkdir(self::$dir, 0755, true);
            }
        }
    }

    /**
     * Get cached value
     */
    public static function get(string $key, mixed $default = null): mixed {
        if (!CACHE_ENABLED) return $default;
        
        self::init();
        $file = self::$dir . md5($key) . '.cache';
        
        if (!file_exists($file)) return $default;
        
        $data = unserialize(file_get_contents($file));
        
        // Check expiration
        if ($data['expires'] > 0 && $data['expires'] < time()) {
            unlink($file);
            return $default;
        }
        
        return $data['value'];
    }

    /**
     * Set cached value
     */
    public static function set(string $key, mixed $value, int $ttl = 0): void {
        if (!CACHE_ENABLED) return;
        
        self::init();
        $file = self::$dir . md5($key) . '.cache';
        $ttl = $ttl ?: CACHE_TTL;
        
        $data = [
            'key'     => $key,
            'value'   => $value,
            'expires' => time() + $ttl,
            'created' => time(),
        ];
        
        file_put_contents($file, serialize($data), LOCK_EX);
    }

    /**
     * Check if key exists and is valid
     */
    public static function has(string $key): bool {
        return self::get($key) !== null;
    }

    /**
     * Delete a cached key
     */
    public static function forget(string $key): void {
        self::init();
        $file = self::$dir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Clear all cache
     */
    public static function flush(): void {
        self::init();
        $files = glob(self::$dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * Get or set (remember pattern)
     */
    public static function remember(string $key, int $ttl, callable $callback): mixed {
        $value = self::get($key);
        if ($value !== null) return $value;
        
        $value = $callback();
        self::set($key, $value, $ttl);
        return $value;
    }

    /**
     * Get cache stats
     */
    public static function stats(): array {
        self::init();
        $files = glob(self::$dir . '*.cache');
        $totalSize = 0;
        $expired = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $data = unserialize(file_get_contents($file));
            if ($data['expires'] > 0 && $data['expires'] < time()) $expired++;
        }
        
        return [
            'total_items' => count($files),
            'expired'     => $expired,
            'total_size'  => $totalSize,
            'directory'   => self::$dir,
        ];
    }
}
