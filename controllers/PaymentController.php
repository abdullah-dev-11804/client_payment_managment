<?php
require_once __DIR__ . '/../config/config.php';

class PaymentController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
       // session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
            header('Location: /login');
            exit;
        }
    }
    public function initiate() {
        $server_id = $_GET['server_id'] ?? null;
        $months = (int)($_GET['months'] ?? 1);
        if (!$server_id || $months < 1 || $months > 12) {
            echo "Invalid server ID or number of months.";
            exit;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM servers WHERE id = ? AND client_id = ?");
        $stmt->execute([$server_id, $_SESSION['user_id']]);
        $server = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$server) {
            echo "Server not found or you don’t have permission.";
            exit;
        }

        if (!get_setting('stripe_secret_key')) {
            echo "Stripe is not configured. Please contact the admin.";
            exit;
        }

        $total_amount = $server['monthly_amount'] * $months;

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => "Server Payment: {$server['server_name']} ($months months)",
                    ],
                    'unit_amount' => $server['monthly_amount'] * 100,
                ],
                'quantity' => $months,
            ]],
            'mode' => 'payment',
            'success_url' => SITE_URL . '/public/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => SITE_URL . '/public/payment_cancelled.php',

            'metadata' => [
                'server_id' => $server['id'],
                'months' => $months,
            ],
        ]);

        header('Location: ' . $checkout_session->url);
        exit;
    }

    public function success() {
        $session_id = $_GET['session_id'] ?? null;
        if (!$session_id) {
            echo "Invalid session.";
            exit;
        }

        try {
            $session = \Stripe\Checkout\Session::retrieve($session_id);
            if ($session->payment_status !== 'paid') {
                echo "Payment not completed.";
                exit;
            }

            $server_id = $session->metadata->server_id;
            $months = (int)$session->metadata->months;

            $stmt = $this->pdo->prepare("SELECT id FROM payments WHERE stripe_payment_intent_id = ?");
            $stmt->execute([$session->payment_intent]);
            if ($stmt->fetch()) {
                echo "Payment already processed.";
                exit;
            }

            $stmt = $this->pdo->prepare("SELECT * FROM servers WHERE id = ?");
            $stmt->execute([$server_id]);
            $server = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$server) {
                echo "Server not found.";
                exit;
            }

            $period_start = new DateTime($server['next_due_date']);
            $new_next_due_date = clone $period_start;
            $new_next_due_date->modify("+$months months");
            $period_end = clone $new_next_due_date;
            $period_end->modify('-1 day');

            $stmt = $this->pdo->prepare(
                "INSERT INTO payments (server_id, amount, payment_date, period_start, period_end, stripe_payment_intent_id) 
                VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $server_id,
                $server['monthly_amount'] * $months,
                date('Y-m-d'),
                $period_start->format('Y-m-d'),
                $period_end->format('Y-m-d'),
                $session->payment_intent,
            ]);

            $stmt = $this->pdo->prepare("UPDATE servers SET next_due_date = ?, advance_months_paid = ? WHERE id = ?");
            $stmt->execute([$new_next_due_date->format('Y-m-d'), $months, $server_id]);

            header('Location: /client/dashboard?payment=success');
            exit;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function cancelled() {
        header('Location: /client/dashboard?payment=cancelled');
        exit;
    }
}

function get_setting($key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['value'] : null;
}