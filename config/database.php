<?php
/**
 * AsaanFix Pakistan - Database Configuration
 * Manages PDO connection to MySQL database
 * Now reads credentials from .env file
 */

require_once __DIR__ . '/env.php';

// Database credentials from environment
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'fixithub_pakistan'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

/**
 * Get PDO database connection (singleton pattern)
 * @return PDO
 */
function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error in production, show generic message
            error_log("Database Connection Error: " . $e->getMessage());
            if (env('APP_DEBUG', false)) {
                die("Database Error: " . $e->getMessage());
            }
            die("Database connection failed. Please try again later.");
        }
    }
    
    return $pdo;
}
