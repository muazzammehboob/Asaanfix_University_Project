<?php
/**
 * AsaanFix Pakistan - Environment Configuration Loader
 * Loads .env file and provides env() helper function
 */

class Env {
    private static array $vars = [];
    private static bool $loaded = false;

    /**
     * Load environment variables from .env file
     */
    public static function load(string $path): void {
        if (self::$loaded) return;
        
        $envFile = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';
        
        if (!file_exists($envFile)) {
            // Fall back to .env.example if .env doesn't exist
            $envFile = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env.example';
            if (!file_exists($envFile)) return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) continue;
            
            // Parse KEY=VALUE
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                // Convert boolean strings
                $lowerValue = strtolower($value);
                if ($lowerValue === 'true') $value = true;
                elseif ($lowerValue === 'false') $value = false;
                elseif ($lowerValue === 'null' || $value === '') $value = null;
                
                self::$vars[$key] = $value;
                
                // Also set as environment variable
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }

    /**
     * Get an environment variable
     */
    public static function get(string $key, mixed $default = null): mixed {
        return self::$vars[$key] ?? $_ENV[$key] ?? $default;
    }
}

/**
 * Global env() helper function
 */
function env(string $key, mixed $default = null): mixed {
    return Env::get($key, $default);
}

// Auto-load from project root
Env::load(dirname(__DIR__));
