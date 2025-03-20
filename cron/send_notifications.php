<?php
// /var/www/html/client-payment-system/cron/send_notifications.php
require_once __DIR__ . '/../config/config.php'; // Database connection (PDO assumed)
require_once __DIR__ . '/../models/serverModel.php'; // ServerModel class
require_once __DIR__ . '/../helpers/NotificationService.php'; // Email/SMS service

// Debugging: Confirm script start
echo "Script started at " . date('Y-m-d H:i:s') . "\n";
file_put_contents('/var/www/html/cron_env.log', print_r(getenv(), true), FILE_APPEND);

// Function to get admin-configured reminder interval from settings table
function getReminderInterval($pdo) {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'reminder_interval'");
    $stmt->execute();
    return (int) $stmt->fetchColumn() ?: 10; // Default to 10 days if not set
}

// Initialize models and services
$serverModel = new ServerModel();
$notificationService = new NotificationService();
$reminderInterval = getReminderInterval($pdo); // Get dynamic interval (e.g., 10 days)

// Get servers with future due dates (we'll assume ServerModel handles this)
$servers = $serverModel->getServersWithFutureDueDates();

// Debugging: Show how many servers were found
echo "Found " . count($servers) . " servers with future due dates.\n";

if (empty($servers)) {
    echo "No servers with future due dates found.\n";
} else {
    foreach ($servers as $server) {
        $userEmail = $server['email'];
        $userPhone = $server['phone'];
        $dueDate = $server['next_due_date'];
        $serverName = $server['server_name'] ?? 'Server #' . $server['id'];

        // Debugging: Show server details
        echo "Processing server: ID={$server['id']}, Name={$serverName}, Due={$dueDate}, Email={$userEmail}, Phone=" . ($userPhone ?: 'N/A') . "\n";

        // Check the last notification for this server and due date
        $stmt = $pdo->prepare("
            SELECT sent_at 
            FROM notification_log 
            WHERE server_id = ? AND due_date = ? AND notification_type = 'email' 
            ORDER BY sent_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$server['id'], $dueDate]);
        $lastNotification = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate days since last notification
        $daysSinceLast = $lastNotification 
            ? (new DateTime())->diff(new DateTime($lastNotification['sent_at']))->days 
            : $reminderInterval + 1; // If no notification, assume it's overdue

        // Send email if interval has passed or no notification exists
        if ($daysSinceLast >= $reminderInterval) {
            $subject = "Payment Due Reminder";
            $body = "Dear user, your payment for {$serverName} is due on {$dueDate}. Please pay soon!";
            try {
                $notificationService->sendEmail($userEmail, $subject, $body);
                echo "Email sent to {$userEmail}\n";

                // Log the notification
                $stmt = $pdo->prepare("
                    INSERT INTO notification_log (server_id, due_date, notification_type, sent_at) 
                    VALUES (?, ?, 'email', NOW())
                ");
                $stmt->execute([$server['id'], $dueDate]);
                echo "Logged email notification for server {$server['id']}\n";
            } catch (Exception $e) {
                echo "Failed to send email to {$userEmail}: " . $e->getMessage() . "\n";
                error_log("Email error for server {$server['id']}: " . $e->getMessage());
            }

            // Send SMS if phone number exists (uncommented for completeness)
            if (!empty($userPhone)) {
                $message = "Payment due on {$dueDate} for {$serverName}.";
                try {
                    $notificationService->sendSMS($userPhone, $message);
                    echo "SMS sent to {$userPhone}\n";

                    // Log the SMS notification
                    $stmt = $pdo->prepare("
                        INSERT INTO notification_log (server_id, due_date, notification_type, sent_at) 
                        VALUES (?, ?, 'sms', NOW())
                    ");
                    $stmt->execute([$server['id'], $dueDate]);
                    echo "Logged SMS notification for server {$server['id']}\n";
                } catch (Exception $e) {
                    echo "Failed to send SMS to {$userPhone}: " . $e->getMessage() . "\n";
                    error_log("SMS error for server {$server['id']}: " . $e->getMessage());
                }
            }
        } else {
            echo "No email sent to {$userEmail} - last notification was {$daysSinceLast} days ago (interval: {$reminderInterval}).\n";
        }
    }
}

echo "Script completed at " . date('Y-m-d H:i:s') . "\n";
?>