<?php
require_once '../config/config.php';

class ClientController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Standardized JSON Response
    private function jsonResponse($success, $data = [], $error = '', $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error]);
        exit;
    }

    // Create User
    public function create() {
        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $company_name = trim($_POST['company_name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $phone = trim($_POST['phone'] ?? ''); // Added phone retrieval
    
            // Validate required fields (removed $status)
            if (!$name || !$email || !$password || !$company_name || !$role || !$phone) {
                return $this->jsonResponse(false, [], 'All fields are required', 400);
            }
    
            // Validate role
            if (!in_array($role, ['admin', 'client'])) {
                return $this->jsonResponse(false, [], 'Invalid role', 400);
            }
    
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->jsonResponse(false, [], 'Invalid email format', 400);
            }
    
            // Check if email already exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return $this->jsonResponse(false, [], 'Email already exists', 409);
            }
    
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
            $stmt = $this->pdo->prepare("
                INSERT INTO users (username, email, password, company_name, role, phone) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $name, // Changed from $username to $name
                $email,
                $hashedPassword, // Changed from $password
                $company_name,
                $role,
                $phone
            ]);
            
            $userId = $this->pdo->lastInsertId();
    
            return $this->jsonResponse(true, [
                'message' => 'User created successfully',
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            return $this->jsonResponse(false, [], $e->getMessage(), 500);
        }
    }

    // Get User
    public function get() {
        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                return $this->jsonResponse(false, [], 'User ID is required', 400);
            }

            $stmt = $this->pdo->prepare("
                SELECT id, username, email, company_name, role, created_at 
                FROM users 
                WHERE id = ? AND role IN ('admin', 'client')
            ");
            $stmt->execute([$id]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$client) {
                return $this->jsonResponse(false, [], 'Client not found', 404);
            }

            return $this->jsonResponse(true, ['client' => $client]);
        } catch (PDOException $e) {
            error_log('Error in get method: ' . $e->getMessage());
            return $this->jsonResponse(false, [], 'Database error', 500);
        }
    }

    // Update User
    public function update() {
        try {
            $id = $_POST['id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $company_name = trim($_POST['company_name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $password = trim($_POST['password'] ?? '');
    
            if (!$id) {
                return $this->jsonResponse(false, [], 'User ID is required', 400);
            }
    
            // Check if user exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return $this->jsonResponse(false, [], 'User not found', 404);
            }
    
            // Validate role
            if ($role && !in_array($role, ['admin', 'client'])) {
                return $this->jsonResponse(false, [], 'Invalid role', 400);
            }
    
            // Validate email
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->jsonResponse(false, [], 'Invalid email format', 400);
            }
    
            // Check if email exists for other users
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                return $this->jsonResponse(false, [], 'Email already exists', 409);
            }
    
            // Update user details (removed status)
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, password = ?, company_name = ?, role = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $hashedPassword, $company_name, $role, $id]);
            } else {
                $stmt = $this->pdo->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, company_name = ?, role = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $company_name, $role, $id]);
            }
    
            return $this->jsonResponse(true, ['message' => 'User updated successfully']);
        } catch (PDOException $e) {
            return $this->jsonResponse(false, [], $e->getMessage(), 500);
        }
    }

    // Soft Delete User
    public function delete() {
        try {
            // Get JSON data
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            // Debug logging
            error_log('Delete request data: ' . print_r($data, true));
            
            $id = $data['id'] ?? null;
            
            if (!$id) {
                return $this->jsonResponse(false, [], 'User ID is required', 400);
            }

            // Check if user exists
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                return $this->jsonResponse(false, [], 'User not found', 404);
            }

            // Delete the user
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ? AND role IN ('admin', 'client')");
            $stmt->execute([$id]);

            return $this->jsonResponse(true, ['message' => 'User deleted successfully']);
        } catch (PDOException $e) {
            error_log('Error in delete method: ' . $e->getMessage());
            return $this->jsonResponse(false, [], $e->getMessage(), 500);
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client = new ClientController();
    
    // Get JSON data for POST requests
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData, true);
    
    // Use either POST data or JSON data
    $action = $_POST['action'] ?? $data['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $client->create();
            break;
        case 'update':
            $client->update();
            break;
        case 'delete':
            $client->delete();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $client = new ClientController();
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get':
            $client->get();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}
?>