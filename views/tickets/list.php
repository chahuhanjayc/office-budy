<?php
require_once __DIR__ . '/../../config/config.php';

// Check authentication
AuthMiddleware::checkAuth();

$authController = new AuthController($database->getConnection());
$currentUser = $authController->getCurrentUser();

// Get tickets based on user role and department
$user_role = Session::getUserRole();
$user_id = Session::get('user_id');
$user_dept = Session::get('user_department');

$ticket_query = "SELECT t.*, e.name as equipment_name, d.name as department_name,
                        creator.username as created_by_name,
                        assignee.username as assigned_to_name
                 FROM tickets t
                 LEFT JOIN equipment e ON t.equipment_id = e.id
                 LEFT JOIN departments d ON t.department_id = d.id
                 LEFT JOIN users creator ON t.created_by = creator.id
                 LEFT JOIN users assignee ON t.assigned_to = assignee.id";

// Apply filtering based on user role
if ($user_role == 3) { // Manager - see tickets assigned to them
    $ticket_query .= " WHERE t.assigned_to = ?";
    $params = [$user_id];
} elseif ($user_role == 4) { // Vendor - see tickets assigned to them
    $ticket_query .= " WHERE t.assigned_to = ?";
    $params = [$user_id];
} elseif ($user_role == 5) { // Regular User - see only their own tickets
    $ticket_query .= " WHERE t.created_by = ?";
    $params = [$user_id];
} else { // Admin/Super Admin - see all tickets
    $params = [];
}

$ticket_query .= " ORDER BY t.created_at DESC";

// Debug: Show the query and parameters
// echo "<!-- DEBUG Query: " . $ticket_query . " -->";
// echo "<!-- DEBUG Params: " . implode(', ', $params) . " -->";

$stmt = $database->getConnection()->prepare($ticket_query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}

// DEBUG: Add this temporarily
echo "<!-- DEBUG: User Role: $user_role, User ID: $user_id, User Dept: $user_dept -->";
echo "<!-- DEBUG: SQL Query: $ticket_query -->";
echo "<!-- DEBUG: Parameters: " . json_encode($params) . " -->";
echo "<!-- DEBUG: Found rows: " . $stmt->rowCount() . " -->";

// Also check what tickets exist in database
$debug_query = "SELECT id, title, department_id, assigned_to FROM tickets WHERE assigned_to = ?";
$debug_stmt = $database->getConnection()->prepare($debug_query);
$debug_stmt->execute([$user_id]);
echo "<!-- DEBUG: Tickets assigned to user: " . $debug_stmt->rowCount() . " -->";
while ($debug_ticket = $debug_stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "<!-- DEBUG Ticket: ID " . $debug_ticket['id'] . " - " . $debug_ticket['title'] . " -->";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Management - Office Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/office-inventory/">
                <i class="fas fa-boxes"></i> Office Inventory
            </a>
            
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo $currentUser['username']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/office-inventory/"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a class="dropdown-item" href="/office-inventory/equipment"><i class="fas fa-laptop"></i> Equipment</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/office-inventory/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-8">
                <h1><i class="fas fa-ticket-alt"></i> Ticket Management</h1>
                <p class="text-muted">Manage repair, replacement, and assistance requests</p>
            </div>
            <div class="col-4 text-end">
                <a href="/office-inventory/tickets/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Ticket
                </a>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <?php echo $authController->hasPermission('manage_tickets') ? 'All Tickets' : 'My Tickets'; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="ticketsTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Equipment</th>
                                <th>Created By</th>
                                <th>Assigned To</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['ticket_number']; ?></td>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td>
                                    <?php 
                                    $type_badges = [
                                        'repair' => 'warning',
                                        'replacement' => 'info',
                                        'it_assistance' => 'primary',
                                        'equipment_request' => 'success',
                                        'other' => 'secondary'
                                    ];
                                    $type_text = [
                                        'repair' => 'Repair',
                                        'replacement' => 'Replacement',
                                        'it_assistance' => 'IT Assistance',
                                        'equipment_request' => 'Equipment Request',
                                        'other' => 'Other'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $type_badges[$row['ticket_type']]; ?>">
                                        <?php echo $type_text[$row['ticket_type']]; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $priority_badges = [
                                        'low' => 'secondary',
                                        'medium' => 'info',
                                        'high' => 'warning',
                                        'critical' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $priority_badges[$row['priority']]; ?>">
                                        <?php echo ucfirst($row['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $status_badges = [
                                        'open' => 'primary',
                                        'in_progress' => 'warning',
                                        'on_hold' => 'info',
                                        'resolved' => 'success',
                                        'closed' => 'secondary'
                                    ];
                                    $status_text = [
                                        'open' => 'Open',
                                        'in_progress' => 'In Progress',
                                        'on_hold' => 'On Hold',
                                        'resolved' => 'Resolved',
                                        'closed' => 'Closed'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $status_badges[$row['status']]; ?>">
                                        <?php echo $status_text[$row['status']]; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['equipment_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['created_by_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['assigned_to_name'] ?? 'Unassigned'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/office-inventory/tickets/view?id=<?php echo $row['id']; ?>" class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($authController->hasPermission('manage_tickets') || $row['created_by'] == $currentUser['id'] || $row['assigned_to'] == $currentUser['id']): ?>
                                        <a href="/office-inventory/tickets/edit?id=<?php echo $row['id']; ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($authController->hasPermission('manage_tickets')): ?>
                                        <button class="btn btn-danger" title="Delete" onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($stmt->rowCount() == 0): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                        <h4>No Tickets Found</h4>
                        <p class="text-muted">
                            <?php echo $authController->hasPermission('manage_tickets') 
                                ? 'No tickets have been created yet.' 
                                : 'You haven\'t created any tickets yet.'; ?>
                        </p>
                        <a href="/office-inventory/tickets/create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Your First Ticket
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#ticketsTable').DataTable({
                pageLength: 25,
                order: [[8, 'desc']] // Sort by created date descending
            });
        });

        function confirmDelete(ticketId) {
            if (confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
                window.location.href = '/office-inventory/tickets/delete?id=' + ticketId;
            }
        }
    </script>
</body>
</html>