<?php
require_once '../config/config.php';

// Redirect to login page by default
header('Location: /client-payment-system/views/auth/login.php');
exit();
?>