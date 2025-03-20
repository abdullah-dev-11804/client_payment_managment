<?php
// /var/www/html/client-payment-system/models/ServerModel.php
require_once __DIR__ . '/../config/config.php';

class ServerModel {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get servers with due dates in the future
     * @return array Servers with future due dates
     */
    public function getServersWithFutureDueDates() {
        $today = date('Y-m-d');
        echo "Fetching servers with due dates after: $today\n";

        $stmt = $this->pdo->prepare("
            SELECT s.*, u.email, u.phone
            FROM servers s
            JOIN users u ON s.client_id = u.id
            WHERE s.next_due_date >= :today
        ");
        $stmt->execute(['today' => $today]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Query returned " . count($results) . " rows\n";
        return $results;
    }
}
?>