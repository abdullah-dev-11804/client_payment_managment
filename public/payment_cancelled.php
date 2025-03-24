<?php
// /var/www/html/client-payment-system/public/payment_cancelled.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../controllers/PaymentController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$paymentController = new PaymentController();
$paymentController->cancelled();
