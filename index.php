<?php
// Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

// Get the current path
$path = $_SERVER['REQUEST_URI'];

// Remove the base path
$path = str_replace('/office-inventory', '', $path);

// Remove query string
$path = explode('?', $path)[0];

// Default to home if empty
if ($path === '' || $path === '/') {
    $path = '/';
}

echo "<!-- Debug: Path = $path -->"; // Debug output

// Simple routing
switch ($path) {
    case '/':
        require __DIR__ . '/views/dashboard.php';
        break;
    case '/login':
        require __DIR__ . '/views/auth/login.php';
        break;
    case '/logout':
        // Choose ONE of these options:
        
        // Option 1: Beautiful logout page
        require __DIR__ . '/views/auth/logout.php';
        break;
        
        // Option 2: Simple redirect (comment out above line and uncomment below)
        // require __DIR__ . '/logout.php';
        // break;
        
    case '/equipment':
        require __DIR__ . '/views/equipment/list.php';
        break;
    case '/equipment/add':
        require __DIR__ . '/views/equipment/add.php';
        break;
    case '/equipment/view':
        if (isset($_GET['id'])) {
            require __DIR__ . '/views/equipment/view.php';
        } else {
            http_response_code(404);
            echo "Equipment ID not specified";
        }
        break;
    case '/equipment/edit':
        if (isset($_GET['id'])) {
            require __DIR__ . '/views/equipment/edit.php';
        } else {
            http_response_code(404);
            echo "Equipment ID not specified";
        }
        break;
    case '/equipment/delete':
        if (isset($_GET['id'])) {
            require __DIR__ . '/controllers/EquipmentController.php';
            $equipmentController = new EquipmentController($database->getConnection());
            $result = $equipmentController->delete($_GET['id']);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }
            Helper::redirect('/office-inventory/equipment');
        } else {
            http_response_code(404);
            echo "Equipment ID not specified";
        }
        break;
		case '/tickets':
		require __DIR__ . '/views/tickets/list.php';
		break;
		case '/tickets/create':
			require __DIR__ . '/views/tickets/create.php';
			break;
		case '/tickets/view':
			if (isset($_GET['id'])) {
				require __DIR__ . '/views/tickets/view.php';
			} else {
				http_response_code(404);
				echo "Ticket ID not specified";
			}
			break;
		case '/tickets/edit':
			if (isset($_GET['id'])) {
				require __DIR__ . '/views/tickets/edit.php';
			} else {
				http_response_code(404);
				echo "Ticket ID not specified";
			}
			break;
		case '/tickets/delete':
			if (isset($_GET['id'])) {
				require __DIR__ . '/controllers/TicketController.php';
				$ticketController = new TicketController($database->getConnection());
				$result = $ticketController->delete($_GET['id']);
				
				if ($result['success']) {
					$_SESSION['success'] = $result['message'];
				} else {
					$_SESSION['error'] = $result['message'];
				}
				Helper::redirect('/office-inventory/tickets');
			}
			break;
		case '/settings':
			require __DIR__ . '/views/settings/index.php';
			break;
		case '/users/add':
		require __DIR__ . '/views/users/add.php';
		break;
		case '/users/edit':
			if (isset($_GET['id'])) {
				require __DIR__ . '/views/users/edit.php';
			} else {
				http_response_code(404);
				echo "User ID not specified";
			}
			break;
		case '/users/delete':
			if (isset($_GET['id'])) {
				require __DIR__ . '/controllers/UserController.php';
				$userController = new UserController($database->getConnection());
				$result = $userController->delete($_GET['id']);
				
				if ($result['success']) {
					$_SESSION['success'] = $result['message'];
				} else {
					$_SESSION['error'] = $result['message'];
				}
				Helper::redirect('/office-inventory/settings#users');
			}
			break;
		default:
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        echo "<p>The requested URL <strong>$path</strong> was not found.</p>";
        echo "<p><a href='/office-inventory/'>Go to Homepage</a></p>";
        break;
}
?>