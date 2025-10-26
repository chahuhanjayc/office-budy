<?php
session_start();

define('BASE_URL', 'http://localhost/office-inventory');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once 'database.php';

// Auto-load classes
spl_autoload_register(function ($class_name) {
    $directories = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../middleware/',
        __DIR__ . '/../utils/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Create database connection
$database = new Database();
?>