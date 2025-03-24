<?php
// /var/www/html/myproject/public/index.php
require_once __DIR__ . '/vendor/autoload.php';
//\Stripe\Stripe::setApiKey(get_setting('stripe_secret_key'));
// Include config and start session if not already started
require_once __DIR__ . '/config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$requestUri = trim($_SERVER['REQUEST_URI'], '/');
// Remove base URI if present
$baseUri = trim(parse_url(SITE_URL, PHP_URL_PATH), '/');
if ($baseUri && strpos($requestUri, $baseUri) === 0) {
    $requestUri = substr($requestUri, strlen($baseUri));
    $requestUri = trim($requestUri, '/');
}

if ($requestUri === 'index.php' || empty($requestUri)) {
    // Default route: redirect based on authentication status
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/views/auth/login.php');
        exit;
    } else {
        header('Location: ' . SITE_URL . '/views/admin/dashboard.php');
        exit;
    }
} else {
    $file = PUBLIC_PATH . '/' . $requestUri;
    if (file_exists($file) && !is_dir($file)) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            include $file;
        } else {
            header('Content-Type: ' . mime_content_type($file));
            readfile($file);
        }
        exit;
    } else {
        http_response_code(404);
        echo "404 - Page not found";
        exit;
    }
}