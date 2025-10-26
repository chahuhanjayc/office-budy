<?php
require_once __DIR__ . '/../../config/config.php'; // Fixed path

$authController = new AuthController($database->getConnection());
$result = $authController->logout();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Office Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .logout-container {
            max-width: 500px;
            margin: 100px auto;
            padding: 40px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logout-icon {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logout-container">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h2>Goodbye!</h2>
            <p class="text-muted mb-4">You have been successfully logged out of the system.</p>
            <p class="text-muted mb-4">We hope to see you again soon!</p>
            <div class="d-grid gap-2">
                <a href="/office-inventory/login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login Again
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>