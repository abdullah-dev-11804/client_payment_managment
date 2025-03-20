<?php
require_once '../config/config.php';

class HostingPlanController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function create() {
        try {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $storage = trim($_POST['storage']);
            $bandwidth = trim($_POST['bandwidth']);
            $domains = intval($_POST['domains']);
            $status = trim($_POST['status']);

            $stmt = $this->pdo->prepare("
                INSERT INTO hosting_plans (name, description, price, storage, bandwidth, domains, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$name, $description, $price, $storage, $bandwidth, $domains, $status]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function get() {
        try {
            $id = $_GET['id'];
            $stmt = $this->pdo->prepare("SELECT * FROM hosting_plans WHERE id = ?");
            $stmt->execute([$id]);
            $plan = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($plan) {
                echo json_encode(['success' => true, 'plan' => $plan]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Plan not found']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function update() {
        try {
            $id = $_POST['id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $storage = trim($_POST['storage']);
            $bandwidth = trim($_POST['bandwidth']);
            $domains = intval($_POST['domains']);
            $status = trim($_POST['status']);

            $stmt = $this->pdo->prepare("
                UPDATE hosting_plans 
                SET name = ?, description = ?, price = ?, storage = ?, 
                    bandwidth = ?, domains = ?, status = ? 
                WHERE id = ?
            ");
            
            $stmt->execute([$name, $description, $price, $storage, $bandwidth, $domains, $status, $id]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function delete() {
        try {
            $id = $_POST['id'];
            $stmt = $this->pdo->prepare("DELETE FROM hosting_plans WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan = new HostingPlanController();
    
    switch($_POST['action']) {
        case 'create':
            $plan->create();
            break;
        case 'update':
            $plan->update();
            break;
        case 'delete':
            $plan->delete();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    $plan = new HostingPlanController();
    $plan->get();
}
?>