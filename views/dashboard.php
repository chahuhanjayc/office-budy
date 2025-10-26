<?php
require_once __DIR__ . '/../config/config.php';

// Check authentication
AuthMiddleware::checkAuth();

$authController = new AuthController($database->getConnection());
$equipmentModel = new Equipment($database->getConnection());

// Get user role and ID
$user_role = Session::getUserRole();
$user_id = Session::get('user_id');
$currentUser = $authController->getCurrentUser();

// Get role-specific statistics and tickets
$stats = $equipmentModel->getStats();

// Get tickets based on role
if ($user_role == 1 || $user_role == 2) { // Admin/Super Admin
    $tickets_query = "SELECT t.*, e.name as equipment_name, 
                             creator.username as created_by_name,
                             assignee.username as assigned_to_name
                      FROM tickets t
                      LEFT JOIN equipment e ON t.equipment_id = e.id
                      LEFT JOIN users creator ON t.created_by = creator.id
                      LEFT JOIN users assignee ON t.assigned_to = assignee.id
                      ORDER BY t.created_at DESC LIMIT 10";
    $tickets_stmt = $database->getConnection()->query($tickets_query);
    
    $ticket_stats_query = "SELECT 
        COUNT(*) as total_tickets,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tickets,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets
        FROM tickets";
    $ticket_stmt = $database->getConnection()->query($ticket_stats_query);
    $ticket_stats = $ticket_stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($user_role == 3) { // Manager
    $tickets_query = "SELECT t.*, e.name as equipment_name, 
                             creator.username as created_by_name,
                             assignee.username as assigned_to_name
                      FROM tickets t
                      LEFT JOIN equipment e ON t.equipment_id = e.id
                      LEFT JOIN users creator ON t.created_by = creator.id
                      LEFT JOIN users assignee ON t.assigned_to = assignee.id
                      WHERE t.assigned_to = ?
                      ORDER BY t.created_at DESC LIMIT 10";
    $tickets_stmt = $database->getConnection()->prepare($tickets_query);
    $tickets_stmt->execute([$user_id]);
    
    $ticket_stats_query = "SELECT 
        COUNT(*) as total_tickets,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tickets,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets
        FROM tickets WHERE assigned_to = ?";
    $ticket_stmt = $database->getConnection()->prepare($ticket_stats_query);
    $ticket_stmt->execute([$user_id]);
    $ticket_stats = $ticket_stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($user_role == 4) { // Vendor
    $tickets_query = "SELECT t.*, e.name as equipment_name, 
                             creator.username as created_by_name,
                             assignee.username as assigned_to_name
                      FROM tickets t
                      LEFT JOIN equipment e ON t.equipment_id = e.id
                      LEFT JOIN users creator ON t.created_by = creator.id
                      LEFT JOIN users assignee ON t.assigned_to = assignee.id
                      WHERE t.assigned_to = ?
                      ORDER BY t.created_at DESC LIMIT 10";
    $tickets_stmt = $database->getConnection()->prepare($tickets_query);
    $tickets_stmt->execute([$user_id]);
    
    $ticket_stats_query = "SELECT 
        COUNT(*) as total_tickets,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tickets,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets
        FROM tickets WHERE assigned_to = ?";
    $ticket_stmt = $database->getConnection()->prepare($ticket_stats_query);
    $ticket_stmt->execute([$user_id]);
    $ticket_stats = $ticket_stmt->fetch(PDO::FETCH_ASSOC);
} else { // User (role 5)
    $tickets_query = "SELECT t.*, e.name as equipment_name, 
                             creator.username as created_by_name,
                             assignee.username as assigned_to_name
                      FROM tickets t
                      LEFT JOIN equipment e ON t.equipment_id = e.id
                      LEFT JOIN users creator ON t.created_by = creator.id
                      LEFT JOIN users assignee ON t.assigned_to = assignee.id
                      WHERE t.created_by = ?
                      ORDER BY t.created_at DESC LIMIT 10";
    $tickets_stmt = $database->getConnection()->prepare($tickets_query);
    $tickets_stmt->execute([$user_id]);
    
    $ticket_stats_query = "SELECT 
        COUNT(*) as total_tickets,
        SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_tickets,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tickets,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_tickets
        FROM tickets WHERE created_by = ?";
    $ticket_stmt = $database->getConnection()->prepare($ticket_stats_query);
    $ticket_stmt->execute([$user_id]);
    $ticket_stats = $ticket_stmt->fetch(PDO::FETCH_ASSOC);
}

$tickets = $tickets_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Office Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-body { padding: 0.75rem; }
        .table th, .table td { padding: 0.5rem; }
        .card-header { padding: 0.75rem; }
        .btn-sm { padding: 0.25rem 0.5rem; }
        .navbar { padding: 0.25rem 0; }
        .container { max-width: 100%; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-boxes"></i> Office Buddy
            </a>
            
            <div class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo $currentUser['username']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/office-inventory/settings"><i class="fas fa-cog"></i> Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/office-inventory/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <div class="container mt-3">
        <!-- Welcome Message -->
        <div class="row mb-3">
            <div class="col-12">
                <h1 class="h4 mb-1">Dashboard</h1>
                <p class="text-muted small mb-2">Welcome back, <?php echo $currentUser['username']; ?>! 
                (<?php 
                    $role_names = [1 => 'Super Admin', 2 => 'Admin', 3 => 'Manager', 4 => 'Vendor', 5 => 'User'];
                    echo $role_names[$user_role];
                ?>)</p>
            </div>
        </div>

        <!-- ROLE-SPECIFIC DASHBOARDS -->

        <!-- ADMIN/SUPER ADMIN DASHBOARD (with dropdown) -->
        <?php if ($user_role == 1 || $user_role == 2): ?>
        <div class="row g-2 mb-3">
            <!-- Statistics Cards -->
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $stats['total_equipment']; ?></h5>
                                <small>Total Equipment</small>
                            </div>
                            <i class="fas fa-laptop"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $stats['available'] ?? '0'; ?></h5>
                                <small>Available</small>
                            </div>
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['total_tickets'] ?? '0'; ?></h5>
                                <small>Total Tickets</small>
                            </div>
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['open_tickets'] ?? '0'; ?></h5>
                                <small>Open Tickets</small>
                            </div>
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Content Area -->
        <div class="row">
            <div class="col-12">
                <!-- Tickets View (Default) -->
                <div class="card admin-view" id="admin-tickets-view">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0">Recent Tickets</h6>
                        <div class="btn-group">
                            <div class="dropdown me-2">
                                <button class="btn btn-outline-primary dropdown-toggle btn-sm" type="button" id="adminViewDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-eye"></i> View: Tickets
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item admin-view-btn" href="#" data-view="tickets">
                                        <i class="fas fa-ticket-alt"></i> Tickets
                                    </a></li>
                                    <li><a class="dropdown-item admin-view-btn" href="#" data-view="equipment">
                                        <i class="fas fa-laptop"></i> Equipment
                                    </a></li>
                                </ul>
                            </div>
                            <a href="/office-inventory/tickets/create" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Create Ticket
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Created By</th>
                                        <th>Assigned To</th>
                                        <th>Created Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><?php echo $ticket['ticket_number']; ?></td>
                                        <td><?php echo htmlspecialchars($ticket['title']); ?></td>
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
                                            <span class="badge bg-<?php echo $status_badges[$ticket['status']]; ?>">
                                                <?php echo $status_text[$ticket['status']]; ?>
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
                                            <span class="badge bg-<?php echo $priority_badges[$ticket['priority']]; ?>">
                                                <?php echo ucfirst($ticket['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($ticket['created_by_name']); ?></td>
                                        <td><?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($tickets)): ?>
                            <p class="text-muted text-center py-2 mb-0 small">No tickets found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Equipment View (Hidden by default) -->
                <div class="card admin-view" id="admin-equipment-view" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0">Equipment Inventory</h6>
                        <div class="btn-group">
                            <div class="dropdown me-2">
                                <button class="btn btn-outline-primary dropdown-toggle btn-sm" type="button" id="adminViewDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-eye"></i> View: Equipment
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item admin-view-btn" href="#" data-view="tickets">
                                        <i class="fas fa-ticket-alt"></i> Tickets
                                    </a></li>
                                    <li><a class="dropdown-item admin-view-btn" href="#" data-view="equipment">
                                        <i class="fas fa-laptop"></i> Equipment
                                    </a></li>
                                </ul>
                            </div>
                            <a href="/office-inventory/equipment/add" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add New Equipment
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Get equipment data for admin
                        $equipment_query = "SELECT e.*, c.name as category_name, d.name as department_name
                                           FROM equipment e
                                           LEFT JOIN categories c ON e.category_id = c.id
                                           LEFT JOIN departments d ON e.department_id = d.id
                                           ORDER BY e.created_at DESC LIMIT 10";
                        $equipment_stmt = $database->getConnection()->query($equipment_query);
                        $equipment_items = $equipment_stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Serial Number</th>
                                        <th>Status</th>
                                        <th>Department</th>
                                        <th>Purchase Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipment_items as $equipment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($equipment['name']); ?></td>
                                        <td><?php echo htmlspecialchars($equipment['category_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($equipment['serial_number'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php 
                                            $equipment_status_badges = [
                                                'available' => 'success',
                                                'assigned' => 'primary',
                                                'maintenance' => 'warning',
                                                'retired' => 'secondary',
                                                'with_vendor' => 'info'
                                            ];
                                            $equipment_status_text = [
                                                'available' => 'Available',
                                                'assigned' => 'Assigned',
                                                'maintenance' => 'Maintenance',
                                                'retired' => 'Retired',
                                                'with_vendor' => 'With Vendor'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $equipment_status_badges[$equipment['status']]; ?>">
                                                <?php echo $equipment_status_text[$equipment['status']]; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($equipment['department_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $equipment['purchase_date'] ? date('M j, Y', strtotime($equipment['purchase_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/office-inventory/equipment/view?id=<?php echo $equipment['id']; ?>" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($authController->hasPermission('manage_equipment')): ?>
                                                <a href="/office-inventory/equipment/edit?id=<?php echo $equipment['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($equipment_items)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-laptop fa-2x text-muted mb-2"></i>
                                <h6>No Equipment Found</h6>
                                <p class="text-muted small">No equipment items found in the system.</p>
                                <a href="/office-inventory/equipment/add" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Your First Equipment
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- MANAGER/USER DASHBOARD -->
        <?php elseif ($user_role == 3 || $user_role == 5): ?>
        <div class="row g-2 mb-3">
            <!-- Statistics Cards -->
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['total_tickets'] ?? '0'; ?></h5>
                                <small><?php echo $user_role == 3 ? 'Assigned' : 'My'; ?> Tickets</small>
                            </div>
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['open_tickets'] ?? '0'; ?></h5>
                                <small>Open</small>
                            </div>
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['in_progress_tickets'] ?? '0'; ?></h5>
                                <small>In Progress</small>
                            </div>
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['resolved_tickets'] ?? '0'; ?></h5>
                                <small>Resolved</small>
                            </div>
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tickets Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0">
                            <?php 
                            $table_titles = [
                                3 => 'Tickets Assigned to You',
                                5 => 'Your Submitted Tickets'
                            ];
                            echo $table_titles[$user_role];
                            ?>
                        </h6>
                        <a href="/office-inventory/tickets/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Ticket
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <?php if ($user_role == 3): ?>
                                        <th>Created By</th>
                                        <?php endif; ?>
                                        <?php if ($user_role == 5): ?>
                                        <th>Assigned To</th>
                                        <?php endif; ?>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><?php echo $ticket['ticket_number']; ?></td>
                                        <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                        <td>
                                            <?php 
                                            $type_text = [
                                                'repair' => 'Repair',
                                                'replacement' => 'Replacement',
                                                'it_assistance' => 'IT Assistance',
                                                'equipment_request' => 'Equipment Request',
                                                'other' => 'Other'
                                            ];
                                            echo $type_text[$ticket['ticket_type']];
                                            ?>
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
                                            <span class="badge bg-<?php echo $status_badges[$ticket['status']]; ?>">
                                                <?php echo $status_text[$ticket['status']]; ?>
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
                                            <span class="badge bg-<?php echo $priority_badges[$ticket['priority']]; ?>">
                                                <?php echo ucfirst($ticket['priority']); ?>
                                            </span>
                                        </td>
                                        <?php if ($user_role == 3): ?>
                                        <td><?php echo htmlspecialchars($ticket['created_by_name']); ?></td>
                                        <?php endif; ?>
                                        <?php if ($user_role == 5): ?>
                                        <td><?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?></td>
                                        <?php endif; ?>
                                        <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/office-inventory/tickets/view?id=<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($authController->hasPermission('manage_tickets') || $ticket['created_by'] == $currentUser['id'] || $ticket['assigned_to'] == $currentUser['id']): ?>
                                                <a href="/office-inventory/tickets/edit?id=<?php echo $ticket['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($tickets)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-ticket-alt fa-2x text-muted mb-2"></i>
                                <h6>No Tickets Found</h6>
                                <p class="text-muted small mb-2">
                                    <?php 
                                    $empty_messages = [
                                        3 => 'No tickets are assigned to you yet.',
                                        5 => 'You haven\'t submitted any tickets yet.'
                                    ];
                                    echo $empty_messages[$user_role];
                                    ?>
                                </p>
                                <a href="/office-inventory/tickets/create" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create Your First Ticket
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- VENDOR DASHBOARD (with dropdown) -->
        <?php elseif ($user_role == 4): ?>
        <div class="row g-2 mb-3">
            <!-- Statistics Cards (Visible for both views) -->
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['total_tickets'] ?? '0'; ?></h5>
                                <small>My Tickets</small>
                            </div>
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['open_tickets'] ?? '0'; ?></h5>
                                <small>Pending</small>
                            </div>
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><?php echo $ticket_stats['resolved_tickets'] ?? '0'; ?></h5>
                                <small>Completed</small>
                            </div>
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vendor Content Area -->
        <div class="row">
            <div class="col-12">
                <!-- Tickets View (Default) -->
                <div class="card vendor-view" id="vendor-tickets-view">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0">Your Vendor Tickets</h6>
                        <div class="btn-group">
                            <div class="dropdown me-2">
                                <button class="btn btn-outline-primary dropdown-toggle btn-sm" type="button" id="vendorViewDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-eye"></i> View: Tickets
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item vendor-view-btn" href="#" data-view="tickets">
                                        <i class="fas fa-ticket-alt"></i> Tickets
                                    </a></li>
                                    <li><a class="dropdown-item vendor-view-btn" href="#" data-view="equipment">
                                        <i class="fas fa-laptop"></i> Equipment
                                    </a></li>
                                </ul>
                            </div>
                            <a href="/office-inventory/tickets/create" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Create Ticket
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Priority</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                    <tr>
                                        <td><?php echo $ticket['ticket_number']; ?></td>
                                        <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                                        <td>
                                            <?php 
                                            $type_text = [
                                                'repair' => 'Repair',
                                                'replacement' => 'Replacement',
                                                'it_assistance' => 'IT Assistance',
                                                'equipment_request' => 'Equipment Request',
                                                'other' => 'Other'
                                            ];
                                            echo $type_text[$ticket['ticket_type']];
                                            ?>
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
                                            <span class="badge bg-<?php echo $status_badges[$ticket['status']]; ?>">
                                                <?php echo $status_text[$ticket['status']]; ?>
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
                                            <span class="badge bg-<?php echo $priority_badges[$ticket['priority']]; ?>">
                                                <?php echo ucfirst($ticket['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($ticket['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/office-inventory/tickets/view?id=<?php echo $ticket['id']; ?>" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($authController->hasPermission('manage_tickets') || $ticket['created_by'] == $currentUser['id'] || $ticket['assigned_to'] == $currentUser['id']): ?>
                                                <a href="/office-inventory/tickets/edit?id=<?php echo $ticket['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($tickets)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-ticket-alt fa-2x text-muted mb-2"></i>
                                <h6>No Tickets Found</h6>
                                <p class="text-muted small mb-2">You don't have any vendor tickets yet.</p>
                                <a href="/office-inventory/tickets/create" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create Your First Ticket
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Equipment View (Hidden by default) -->
                <div class="card vendor-view" id="vendor-equipment-view" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="card-title mb-0">Equipment Inventory</h6>
                        <div class="btn-group">
                            <div class="dropdown me-2">
                                <button class="btn btn-outline-primary dropdown-toggle btn-sm" type="button" id="vendorViewDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-eye"></i> View: Equipment
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item vendor-view-btn" href="#" data-view="tickets">
                                        <i class="fas fa-ticket-alt"></i> Tickets
                                    </a></li>
                                    <li><a class="dropdown-item vendor-view-btn" href="#" data-view="equipment">
                                        <i class="fas fa-laptop"></i> Equipment
                                    </a></li>
                                </ul>
                            </div>
                            <a href="/office-inventory/equipment/add" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add New Equipment
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        // Get equipment data for vendor
                        $equipment_query = "SELECT e.*, c.name as category_name, d.name as department_name
                                           FROM equipment e
                                           LEFT JOIN categories c ON e.category_id = c.id
                                           LEFT JOIN departments d ON e.department_id = d.id
                                           ORDER BY e.created_at DESC LIMIT 10";
                        $equipment_stmt = $database->getConnection()->query($equipment_query);
                        $equipment_items = $equipment_stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Serial Number</th>
                                        <th>Status</th>
                                        <th>Department</th>
                                        <th>Purchase Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($equipment_items as $equipment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($equipment['name']); ?></td>
                                        <td><?php echo htmlspecialchars($equipment['category_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($equipment['serial_number'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php 
                                            $equipment_status_badges = [
                                                'available' => 'success',
                                                'assigned' => 'primary',
                                                'maintenance' => 'warning',
                                                'retired' => 'secondary',
                                                'with_vendor' => 'info'
                                            ];
                                            $equipment_status_text = [
                                                'available' => 'Available',
                                                'assigned' => 'Assigned',
                                                'maintenance' => 'Maintenance',
                                                'retired' => 'Retired',
                                                'with_vendor' => 'With Vendor'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $equipment_status_badges[$equipment['status']]; ?>">
                                                <?php echo $equipment_status_text[$equipment['status']]; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($equipment['department_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $equipment['purchase_date'] ? date('M j, Y', strtotime($equipment['purchase_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="/office-inventory/equipment/view?id=<?php echo $equipment['id']; ?>" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($authController->hasPermission('manage_equipment')): ?>
                                                <a href="/office-inventory/equipment/edit?id=<?php echo $equipment['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (empty($equipment_items)): ?>
                            <div class="text-center py-3">
                                <i class="fas fa-laptop fa-2x text-muted mb-2"></i>
                                <h6>No Equipment Found</h6>
                                <p class="text-muted small">No equipment items found in the system.</p>
                                <a href="/office-inventory/equipment/add" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Your First Equipment
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Admin view switcher
        document.addEventListener('DOMContentLoaded', function() {
            const adminViewButtons = document.querySelectorAll('.admin-view-btn');
            const adminViewSections = document.querySelectorAll('.admin-view');
            const adminDropdownButton = document.getElementById('adminViewDropdown');
            
            adminViewButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const view = this.getAttribute('data-view');
                    
                    // Hide all admin views
                    adminViewSections.forEach(section => {
                        section.style.display = 'none';
                    });
                    
                    // Show selected admin view
                    document.getElementById(`admin-${view}-view`).style.display = 'block';
                    
                    // Update admin dropdown button text
                    const viewText = view === 'tickets' ? 'Tickets' : 'Equipment';
                    adminDropdownButton.innerHTML = `<i class="fas fa-eye"></i> View: ${viewText}`;
                });
            });
        });

        // Vendor view switcher
        document.addEventListener('DOMContentLoaded', function() {
            const vendorViewButtons = document.querySelectorAll('.vendor-view-btn');
            const vendorViewSections = document.querySelectorAll('.vendor-view');
            const vendorDropdownButton = document.getElementById('vendorViewDropdown');
            
            vendorViewButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const view = this.getAttribute('data-view');
                    
                    // Hide all vendor views
                    vendorViewSections.forEach(section => {
                        section.style.display = 'none';
                    });
                    
                    // Show selected vendor view
                    document.getElementById(`vendor-${view}-view`).style.display = 'block';
                    
                    // Update vendor dropdown button text
                    const viewText = view === 'tickets' ? 'Tickets' : 'Equipment';
                    vendorDropdownButton.innerHTML = `<i class="fas fa-eye"></i> View: ${viewText}`;
                });
            });
        });
    </script>
</body>
</html>