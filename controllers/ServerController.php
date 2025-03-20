<?php
require_once '../config/config.php';

class ServerController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    private function jsonResponse($success, $data = [], $error = '', $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error]);
        exit;
    }

    public function create() {
        try {
            // Retrieve and sanitize input data
            $client_id = $_POST['client_id'] ?? null;
            $server_name = trim($_POST['server_name'] ?? '');
            $specifications = trim($_POST['specifications'] ?? '');
            $monthly_amount = floatval($_POST['monthly_amount'] ?? 0);
            $advance_months_paid = intval($_POST['advance_months_paid'] ?? 0); // Ensure integer
            $start_date = $_POST['start_date'] ?? '';
    
            // Validate required fields
            if (!$client_id || !$server_name || !$specifications || !$monthly_amount || !$start_date) {
                return $this->jsonResponse(false, [], 'All fields are required', 400);
            }
    
            // Restrict advance_months_paid to 0–12
            if ($advance_months_paid < 0 || $advance_months_paid > 12) {
                return $this->jsonResponse(false, [], 'Advance months paid must be between 0 and 12', 400);
            }
    
            // Calculate next_due_date
            $startDateObj = new DateTime($start_date);
            $monthsToAdd = ($advance_months_paid > 0) ? $advance_months_paid : 1;
            $startDateObj->modify("+$monthsToAdd months");
            $next_due_date = $startDateObj->format('Y-m-d');
    
            // Prepare and execute the INSERT statement
            $stmt = $this->pdo->prepare("
                INSERT INTO servers (
                    client_id, server_name, specifications, monthly_amount, 
                    advance_months_paid, start_date, next_due_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $client_id,
                $server_name,
                $specifications,
                $monthly_amount,
                $advance_months_paid,
                $start_date,
                $next_due_date
            ]);
    
            return $this->jsonResponse(true, ['message' => 'Server details saved successfully']);
        } catch (PDOException $e) {
            return $this->jsonResponse(false, [], $e->getMessage(), 500);
        } catch (Exception $e) {
            return $this->jsonResponse(false, [], 'Invalid date format', 400);
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $server = new ServerController();
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $server->create();
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}
?>