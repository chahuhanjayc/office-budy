<?php
require_once __DIR__ . '/../../config/config.php';

// Check authentication
AuthMiddleware::checkAuth();

$authController = new AuthController($database->getConnection());
$currentUser = $authController->getCurrentUser();

// Get ticket ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Ticket ID not specified";
    Helper::redirect('/office-inventory/tickets');
}

$ticket_id = $_GET['id'];

// Get ticket details
$ticket_query = "SELECT t.*, e.name as equipment_name
                 FROM tickets t
                 LEFT JOIN equipment e ON t.equipment_id = e.id
                 WHERE t.id = ?";
$stmt = $database->getConnection()->prepare($ticket_query);
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    $_SESSION['error'] = "Ticket not found";
    Helper::redirect('/office-inventory/tickets');
}

// Check if user has permission to edit this ticket
if (!$authController->hasPermission('manage_tickets') && $ticket['created_by'] != $currentUser['id'] && $ticket['assigned_to'] != $currentUser['id']) {
    $_SESSION['error'] = "You don't have permission to edit this ticket";
    Helper::redirect('/office-inventory/tickets');
}
// Get equipment and users for dropdowns
$equipment_stmt = $database->getConnection()->query(
    "SELECT id, name, serial_number FROM equipment ORDER BY name"
);
$users_stmt = $database->getConnection()->query(
    "SELECT id, username FROM users WHERE is_active = 1 AND role_id IN (1, 2, 3) ORDER BY username"
);

// Handle form submission
if ($_POST) {
    try {
        $update_data = [
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'ticket_type' => $_POST['ticket_type'],
            'priority' => $_POST['priority'],
            'equipment_id' => !empty($_POST['equipment_id']) ? $_POST['equipment_id'] : null,
            'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
            'id' => $ticket_id
        ];

// Only managers/admins can update assigned_to, but assigned users can update status
if ($authController->hasPermission('manage_tickets')) {
    $update_data['assigned_to'] = !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null;
}
// Allow assigned users to update status
if ($authController->hasPermission('manage_tickets') || $ticket['assigned_to'] == $currentUser['id']) {
    $update_data['status'] = $_POST['status'];
}

        $query = "UPDATE tickets SET 
                 title = :title,
                 description = :description,
                 ticket_type = :ticket_type,
                 priority = :priority,
                 equipment_id = :equipment_id,
                 due_date = :due_date,
                 updated_at = NOW()";

if ($authController->hasPermission('manage_tickets')) {
    $query .= ", assigned_to = :assigned_to";
}
if ($authController->hasPermission('manage_tickets') || $ticket['assigned_to'] == $currentUser['id']) {
    $query .= ", status = :status";
}

        $query .= " WHERE id = :id";

        $stmt = $database->getConnection()->prepare($query);
        
        if ($stmt->execute($update_data)) {
            $_SESSION['success'] = "Ticket updated successfully!";
            Helper::redirect("/office-inventory/tickets/view?id=$ticket_id");
        } else {
            $_SESSION['error'] = "Failed to update ticket. Please try again.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ticket - Office Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <li><a class="dropdown-item" href="/office-inventory/tickets"><i class="fas fa-ticket-alt"></i> Tickets</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/office-inventory/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/office-inventory/">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/office-inventory/tickets">Tickets</a></li>
                <li class="breadcrumb-item"><a href="/office-inventory/tickets/view?id=<?php echo $ticket_id; ?>">Ticket #<?php echo $ticket['ticket_number']; ?></a></li>
                <li class="breadcrumb-item active">Edit Ticket</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1><i class="fas fa-edit"></i> Edit Ticket</h1>
                <p class="text-muted">Update ticket information</p>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Ticket Form -->
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Ticket Information</h5>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Ticket Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required 
                                       value="<?php echo htmlspecialchars($ticket['title']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="ticket_type" class="form-label">Ticket Type *</label>
                                <select class="form-control" id="ticket_type" name="ticket_type" required>
                                    <option value="repair" <?php echo $ticket['ticket_type'] == 'repair' ? 'selected' : ''; ?>>Repair Request</option>
                                    <option value="replacement" <?php echo $ticket['ticket_type'] == 'replacement' ? 'selected' : ''; ?>>Replacement Request</option>
                                    <option value="it_assistance" <?php echo $ticket['ticket_type'] == 'it_assistance' ? 'selected' : ''; ?>>IT Assistance</option>
                                    <option value="equipment_request" <?php echo $ticket['ticket_type'] == 'equipment_request' ? 'selected' : ''; ?>>Equipment Request</option>
                                    <option value="other" <?php echo $ticket['ticket_type'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority *</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="low" <?php echo $ticket['priority'] == 'low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo $ticket['priority'] == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo $ticket['priority'] == 'high' ? 'selected' : ''; ?>>High</option>
                                    <option value="critical" <?php echo $ticket['priority'] == 'critical' ? 'selected' : ''; ?>>Critical</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="equipment_id" class="form-label">Related Equipment</label>
                                <select class="form-control" id="equipment_id" name="equipment_id">
                                    <option value="">No Equipment</option>
                                    <?php while ($equipment = $equipment_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $equipment['id']; ?>" 
                                            <?php echo $ticket['equipment_id'] == $equipment['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($equipment['name']); ?>
                                            <?php if (!empty($equipment['serial_number'])) echo ' (' . htmlspecialchars($equipment['serial_number']) . ')'; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Assignment & Status -->
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-cog"></i> Management</h5>
                            
                            <?php if ($authController->hasPermission('manage_tickets')): ?>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                                    <option value="in_progress" <?php echo $ticket['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="on_hold" <?php echo $ticket['status'] == 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
                                    <option value="resolved" <?php echo $ticket['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="assigned_to" class="form-label">Assign To</label>
                                <select class="form-control" id="assigned_to" name="assigned_to">
                                    <option value="">Unassigned</option>
                                    <?php 
                                    $users_stmt->execute(); // Reset pointer
                                    while ($user = $users_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $user['id']; ?>" 
                                            <?php echo $ticket['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date</label>
                                <input type="date" class="form-control" id="due_date" name="due_date"
                                       value="<?php echo $ticket['due_date']; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="8" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="/office-inventory/tickets/view?id=<?php echo $ticket_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancel
                                </a>
                                <div>
                                    <a href="/office-inventory/tickets/delete?id=<?php echo $ticket_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this ticket? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete Ticket
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Ticket
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>