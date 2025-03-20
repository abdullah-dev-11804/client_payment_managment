<?php
// /var/www/html/myproject/public/index.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/route/routes.php';
$router->dispatch($_SERVER['REQUEST_URI']);
// Basic routing
$requestUri = trim($_SERVER['REQUEST_URI'], '/');

// Remove base path from URI if present
$baseUri = trim(parse_url(SITE_URL, PHP_URL_PATH), '/');
if ($baseUri && strpos($requestUri, $baseUri) === 0) {
    $requestUri = substr($requestUri, strlen($baseUri));
    $requestUri = trim($requestUri, '/');
}

$router->dispatch($requestUri);

if (empty($requestUri)) {
    // Default route: redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/views/auth/login.php');
        exit;
    } else {
        header('Location: ' . SITE_URL . '/views/dashboard.php');
        exit;
    }
} else {
    // Simple routing logic (expand as needed)
    $file = PUBLIC_PATH . '/' . $requestUri;
    if (file_exists($file) && !is_dir($file)) {
        return false; // Let Apache/Nginx serve static files
    } else {
        http_response_code(404);
        echo "404 - Page not found";
        exit;
    }
}