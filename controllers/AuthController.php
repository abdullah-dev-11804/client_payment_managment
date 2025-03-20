<?php
require_once __DIR__ . '/../config/config.php';

class AuthController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Show login page
    public function showLogin() {
        require_once '../views/auth/login.php';
    }

    // Process login
    public function login() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // For debugging
            error_log("Login attempt - Email: $email");

            try {
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // For debugging
                error_log("User data: " . print_r($user, true));

                if ($user && $password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];

                    echo json_encode([
                        'success' => true,
                        'redirect' => $user['role'] === 'admin' 
                            ? '/client-payment-system/views/admin/dashboard.php'
                            : '/client-payment-system/views/client/dashboard.php'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Invalid email or password'
                    ]);
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'error' => 'Database error occurred'
                ]);
            }
            exit();
        }
    }

    public function logout() {
        session_destroy();
        $_SESSION = array();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-3600, '/');
        }
        header('Location: ../views/auth/login.php');
        exit();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle registration logic
            // ... add registration logic here
        } else {
            // Show registration form
            require_once VIEWS_PATH . '/auth/register.php';
        }
    }
}

// Handle POST request for login
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $auth = new AuthController();
    $auth->login();
}

// Handle GET request for logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new AuthController();
    $auth->logout();
}

file_put_contents('debug.log', 'End of AuthController.php reached' . "\n", FILE_APPEND);
?>