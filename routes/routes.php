<?php
// /var/www/html/client-payment-system/routes/routes.php
class Router {
    private $routes = [];

    public function addRoute($method, $url, $handler) {
        $this->routes[$method][$url] = $handler;
    }

    public function dispatch($url) {
        $method = $_SERVER['REQUEST_METHOD'];
        error_log("Dispatching URL: $url with method: $method");
        $url = rtrim($url, '/');
        if (isset($this->routes[$method][$url])) {
            $handler = $this->routes[$method][$url];
            list($controller, $method) = explode('@', $handler);
            $controllerPath = __DIR__ . '/../controllers/' . $controller . '.php';
            if (!file_exists($controllerPath)) {
                http_response_code(404);
                echo "404 - Controller not found";
                exit;
            }
            require_once $controllerPath;
            $controller = new $controller();
            $controller->$method();
        } else {
            http_response_code(404);
            echo "404 - Page not found";
            exit;
        }
    }
}

$router = new Router();

$router->addRoute('GET', '/', 'AuthController@showLogin');
$router->addRoute('GET', '/login', 'AuthController@showLogin');
$router->addRoute('POST', '/auth/login', 'AuthController@login');
$router->addRoute('GET', '/logout', 'AuthController@logout');
$router->addRoute('GET', '/dashboard', 'AdminController@dashboard');
$router->addRoute('GET', '/admin/dashboard', 'AdminController@dashboard');
$router->addRoute('GET', '/client/dashboard', 'ClientController@dashboard');
$router->addRoute('GET', '/admin/settings', 'AdminController@showSettings');
$router->addRoute('POST', '/admin/settings', 'AdminController@saveSettings');
$router->addRoute('GET', '/payment/initiate', 'PaymentController@initiate');
$router->addRoute('GET', '/payment/success', 'PaymentController@success');
$router->addRoute('GET', '/payment/cancelled', 'PaymentController@cancelled');

return $router;

