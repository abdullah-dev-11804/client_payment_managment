<?php
// views/admin/settings.php
require_once __DIR__ . '/../../config/config.php'; // Adjust path to config.php

// Function to get reminder interval (copied from send_notifications.php)
function getReminderInterval($pdo) {
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = 'reminder_interval'");
    $stmt->execute();
    return (int) $stmt->fetchColumn() ?: 10; // Default to 10 days if not set
}

// Process form submission
if (isset($_POST['reminder_interval'])) {
    $interval = (int) $_POST['reminder_interval'];
    $stmt = $pdo->prepare("REPLACE INTO settings (`key`, `value`) VALUES ('reminder_interval', ?)");
    $stmt->execute([$interval]);
    echo "Interval updated to $interval days!";
}
?>

<form method="POST" action="">
    <label>Reminder Interval (days): </label>
    <input type="number" name="reminder_interval" value="<?php echo getReminderInterval($pdo); ?>">
    <button type="submit">Save</button>
</form>