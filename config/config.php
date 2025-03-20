<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env file not found');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new Exception('Failed to read .env file');
    }

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and empty lines
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        // Split into name and value
        if (strpos($line, '=') !== false) {
            list($name, $value) = array_map('trim', explode('=', $line, 2));
            $name = trim($name);
            $value = trim($value);

            // Set the environment variable if it doesn't already exist
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }
        } else {
            error_log("Invalid line in .env file: $line");
        }
    }
}

// Load .env file
try {
    $envPath = __DIR__ . '/../.env';
// echo "Loading .env file from: $envPath\n"; // Debug
loadEnv($envPath);
} catch (Exception $e) {
    die("Error loading .env file: " . $e->getMessage());

}




// config.php

// SMTP Configuration
define('SMTP_HOST', $_ENV['SMTP_HOST']);
define('SMTP_PORT', $_ENV['SMTP_PORT']);
define('SMTP_USER', $_ENV['SMTP_USER']);
define('SMTP_PASS', $_ENV['SMTP_PASS']);



























// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables
$env = parse_ini_file(__DIR__ . '/../.env');


// Database configuration
$host = 'localhost';
$dbname = 'client_payment_system';
$username = 'project';               // User you created
$password = 'project@1234'; // Password you set

// Application Configuration
define('SITE_URL', 'http://216.128.135.50/client-payment-system');
define('SITE_NAME', 'Client Payment Management System');


// Twilio Configuration
// define('TWILIO_ACCOUNT_SID', 'your-account-sid');
// define('TWILIO_AUTH_TOKEN', 'your-auth-token');
// define('TWILIO_PHONE_NUMBER', 'your-twilio-number');

// // Stripe Configuration
// define('STRIPE_SECRET_KEY', 'your-stripe-secret-key');
// define('STRIPE_PUBLISHABLE_KEY', 'your-stripe-publishable-key');

// Error Reporting
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Global constants
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEWS_PATH', BASE_PATH . '/views');

// Database Connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("Database connected successfully");
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
}
?>