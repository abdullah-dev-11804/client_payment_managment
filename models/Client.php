<?php
class Client {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $sql = "INSERT INTO clients (name, email, password) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$data['name'], $data['email'], password_hash($data['password'], PASSWORD_DEFAULT)]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM clients WHERE email = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>