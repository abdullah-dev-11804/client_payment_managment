<?php
class Router {
    private $routes = [];
    
    public function addRoute($url, $handler) {
        $this->routes[$url] = $handler;
    }
    
    public function dispatch($url) {
        // Remove trailing slashes
        $url = rtrim($url, '/');
        
        // If URL is empty, redirect to login
        if (empty($url) || $url == '/') {
            header('Location: /client-payment-system/views/auth/login.php');
            exit();
        }

        if (array_key_exists($url, $this->routes)) {
            $handler = $this->routes[$url];
            list($controller, $method) = explode('@', $handler);
            
            $controllerPath = "../controllers/{$controller}.php";
            if (!file_exists($controllerPath)) {
                header('Location: /client-payment-system/views/auth/login.php');
                exit();
            }

            require_once $controllerPath;
            $controller = new $controller();
            $controller->$method();
        } else {
            header('Location: /client-payment-system/views/auth/login.php');
            exit();
        }
    }
}

// Initialize router
$router = new Router();

// Add routes
$router->addRoute('/login', 'AuthController@showLogin');
$router->addRoute('/auth/login', 'AuthController@login');
$router->addRoute('/logout', 'AuthController@logout');
$router->addRoute('/dashboard', 'AdminController@dashboard');
$router->addRoute('/admin/dashboard', 'AdminController@dashboard');
$router->addRoute('/client/dashboard', 'ClientController@dashboard');

return $router;
?>