<?php
require_once '../config/config.php';

class AdminController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function dashboard() {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: ../views/auth/login.php');
            exit();
        }

        // Load the dashboard view
        require_once '../views/admin/dashboard.php';
    }

    public function clients() {
        // Add client management logic here
    }

    public function payments() {
        // Add payment management logic here
    }

    public function settings() {
        // Add settings management logic here
    }
}

// Only process if direct action is needed
if (isset($_POST['action'])) {
    $admin = new AdminController();
    $action = $_POST['action'];
    
    switch($action) {
        case 'dashboard':
            $admin->dashboard();
            break;
        // Add other action cases as needed
    }
}
?>