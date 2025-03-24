<?php
require_once __DIR__ . '/../config/config.php';

class AdminController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }

    public function dashboard() {
        require_once VIEWS_PATH . '/admin/dashboard.php';
    }

    public function settings() {
        // Placeholder for other settings if needed
    }

    public function showStripeSettings() {
        $stripe_key = $this->getSetting('stripe_secret_key');
        require_once VIEWS_PATH . '/admin/stripe_settings.php';
    }

    public function saveStripeSettings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stripe_key = trim($_POST['stripe_secret_key'] ?? '');
            if (!empty($stripe_key)) {
                $this->saveSetting('stripe_secret_key', $stripe_key);
                header('Location: /admin/stripe-settings?success=1');
            } else {
                header('Location: /admin/stripe-settings?error=1');
            }
            exit;
        }
    }

    private function getSetting($key) {
        $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['value'] : null;
    }

    private function saveSetting($key, $value) {
        $stmt = $this->pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->execute([$key, $value, $value]);
    }
}