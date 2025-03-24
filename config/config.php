<?php
// /var/www/html/myproject/config/config.php

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Load environment variables from .env file
 * @param string $path Path to .env file
 * @throws Exception If .env file cannot be loaded
 */
function loadEnv(string $path): void {
    if (!file_exists($path)) {
        throw new Exception('.env file not found at ' . $path);
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new Exception('Failed to read .env file');
    }

    foreach ($lines as $line) {
        if (empty($line) || str_starts_with($line, '#')) {
            continue; // Skip comments and empty lines
        }

        if (strpos($line, '=') === false) {
            error_log("Skipping invalid .env line: $line");
            continue;
        }

        [$name, $value] = array_map('trim', explode('=', $line, 2));
        if (!empty($name) && !isset($_ENV[$name])) {
            $_ENV[$name] = $value;
        }
    }
}

// Load environment variables
try {
    $envPath = dirname(__DIR__) . '/.env'; // Adjusted to root directory
    loadEnv($envPath);
} catch (Exception $e) {
    error_log("Config error: " . $e->getMessage());
    die("Configuration error: " . $e->getMessage());
}

// Define constants using environment variables with defaults
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'localhost');
define('SMTP_PORT', (int) ($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? '');

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'client_payment_system');
define('DB_USER', $_ENV['DB_USER'] ?? 'project');
define('DB_PASS', $_ENV['DB_PASS'] ?? 'project@1234');

define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://216.128.135.50/client-payment-system');
define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'Client Payment Management System');

// Define paths
define('BASE_PATH', __DIR__ . '/..'); // Now /var/www/html/myproject
define('PUBLIC_PATH', BASE_PATH);     // Now same as BASE_PATH
define('VIEWS_PATH', BASE_PATH . '/views'); // Still correct
// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    error_log("Database connected successfully");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}