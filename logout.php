<?php
require_once __DIR__ . '/config/config.php';

$authController = new AuthController($database->getConnection());
$result = $authController->logout();

if ($result['success']) {
    $_SESSION['success'] = "You have been logged out successfully. See you again!";
} else {
    $_SESSION['error'] = $result['message'];
}

Helper::redirect('/office-inventory/login');
?>