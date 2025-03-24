<?php
// /var/www/html/client-payment-system/public/payment_initiate.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../controllers/PaymentController.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instantiate PaymentController and call initiate()
$paymentController = new PaymentController();
$paymentController->initiate();
