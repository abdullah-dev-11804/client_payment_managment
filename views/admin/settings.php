<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../../config/config.php'; // Adjust path to config.php

// Function to get reminder interval
function getReminderInterval($pdo) {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'reminder_interval'");
    $stmt->execute();
    return (int) $stmt->fetchColumn() ?: 10; // Default to 10 days if not set
}

// Function to get Stripe secret key
function getStripeKey($pdo) {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'stripe_secret_key'");
    $stmt->execute();
    return $stmt->fetchColumn() ?: ''; // Empty string if not set
}

// Process form submission
$success_message = '';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $pdo;

    // Update reminder interval
    if (isset($_POST['reminder_interval'])) {
        $interval = (int) $_POST['reminder_interval'];
        if ($interval > 0) {
            $stmt = $pdo->prepare("REPLACE INTO settings (`key`, `value`) VALUES ('reminder_interval', ?)");
            $stmt->execute([$interval]);
            $success_message .= "Reminder interval updated to $interval days! ";
        } else {
            $error_message .= "Reminder interval must be a positive number. ";
        }
    }

    // Update Stripe secret key
    if (isset($_POST['stripe_secret_key'])) {
        $stripe_key = trim($_POST['stripe_secret_key'] ?? '');
        if (!empty($stripe_key)) {
            $stmt = $pdo->prepare("REPLACE INTO settings (`key`, `value`) VALUES ('stripe_secret_key', ?)");
            $stmt->execute([$stripe_key]);
            $success_message .= "Stripe key updated!";
        } else {
            $error_message .= "Stripe key cannot be empty.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Admin Settings</h1>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo trim($success_message); ?></div>
        <?php elseif (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo trim($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="reminder_interval" class="form-label">Reminder Interval (days)</label>
                <input type="number" class="form-control" id="reminder_interval" name="reminder_interval" value="<?php echo getReminderInterval($pdo); ?>" min="1" required>
                <small class="form-text text-muted">Number of days before due date to send reminders.</small>
            </div>
            <div class="mb-3">
                <label for="stripe_secret_key" class="form-label">Stripe Secret Key</label>
                <input type="text" class="form-control" id="stripe_secret_key" name="stripe_secret_key" value="<?php echo htmlspecialchars(getStripeKey($pdo)); ?>" required>
                <small class="form-text text-muted">Enter your Stripe secret key for payment processing.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
            <a href="/client-payment-system/views/admin/dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>