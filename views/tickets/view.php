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
$ticket_query = "SELECT t.*, e.name as equipment_name, e.serial_number,
                        creator.username as created_by_name,
                        assignee.username as assigned_to_name
                 FROM tickets t
                 LEFT JOIN equipment e ON t.equipment_id = e.id
                 LEFT JOIN users creator ON t.created_by = creator.id
                 LEFT JOIN users assignee ON t.assigned_to = assignee.id
                 WHERE t.id = ?";

$stmt = $database->getConnection()->prepare($ticket_query);
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    $_SESSION['error'] = "Ticket not found";
    Helper::redirect('/office-inventory/tickets');
}

// Check if user has permission to view this ticket
if (!$authController->hasPermission('manage_tickets') && $ticket['created_by'] != $currentUser['id'] && $ticket['assigned_to'] != $currentUser['id']) {
    $_SESSION['error'] = "You don't have permission to view this ticket";
    Helper::redirect('/office-inventory/tickets');
}

// Get ticket responses
$responses_query = "SELECT tr.*, u.username as responded_by_name
                    FROM ticket_responses tr
                    LEFT JOIN users u ON tr.responded_by = u.id
                    WHERE tr.ticket_id = ?
                    ORDER BY tr.created_at ASC";
$responses_stmt = $database->getConnection()->prepare($responses_query);
$responses_stmt->execute([$ticket_id]);
$responses = $responses_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle response submission
if ($_POST && isset($_POST['response_text'])) {
    try {
        $response_query = "INSERT INTO ticket_responses (ticket_id, responded_by, response_text, internal_note) 
                          VALUES (?, ?, ?, ?)";
        $response_stmt = $database->getConnection()->prepare($response_query);
        
        $internal_note = isset($_POST['internal_note']) ? 1 : 0;
        $response_stmt->execute([$ticket_id, $currentUser['id'], $_POST['response_text'], $internal_note]);
        
// Update ticket status if changed (allow assigned users to update status)
if (isset($_POST['status']) && ($authController->hasPermission('manage_tickets') || $ticket['assigned_to'] == $currentUser['id'])) {
    $update_query = "UPDATE tickets SET status = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $database->getConnection()->prepare($update_query);
    $update_stmt->execute([$_POST['status'], $ticket_id]);
    
    // If resolving or closing, set resolved date
    if (in_array($_POST['status'], ['resolved', 'closed']) && empty($ticket['resolved_date'])) {
        $resolve_query = "UPDATE tickets SET resolved_date = NOW() WHERE id = ?";
        $resolve_stmt = $database->getConnection()->prepare($resolve_query);
        $resolve_stmt->execute([$ticket_id]);
    }
}
        
        $_SESSION['success'] = "Response added successfully!";
        Helper::redirect("/office-inventory/tickets/view?id=$ticket_id");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error adding response: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket - Office Inventory System</title>
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
                <li class="breadcrumb-item active">Ticket #<?php echo $ticket['ticket_number']; ?></li>
            </ol>
        </nav>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Ticket Header -->
        <div class="row mb-4">
            <div class="col-8">
                <h1>
                    <i class="fas fa-ticket-alt"></i> 
                    <?php echo htmlspecialchars($ticket['title']); ?>
                </h1>
                <p class="text-muted">Ticket #<?php echo $ticket['ticket_number']; ?></p>
            </div>
            <div class="col-4 text-end">
                <a href="/office-inventory/tickets" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Tickets
                </a>
                <?php if ($authController->hasPermission('manage_tickets') || $ticket['created_by'] == $currentUser['id'] || $ticket['assigned_to'] == $currentUser['id']): ?>
                <a href="/office-inventory/tickets/edit?id=<?php echo $ticket_id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Ticket
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- Ticket Information -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Ticket Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>Status:</th>
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
                            </tr>
                            <tr>
                                <th>Type:</th>
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
                            </tr>
                            <tr>
                                <th>Priority:</th>
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
                            </tr>
                            <tr>
                                <th>Created By:</th>
                                <td><?php echo htmlspecialchars($ticket['created_by_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Assigned To:</th>
                                <td><?php echo htmlspecialchars($ticket['assigned_to_name'] ?? 'Unassigned'); ?></td>
                            </tr>
                            <tr>
                                <th>Due Date:</th>
                                <td><?php echo $ticket['due_date'] ? date('M j, Y', strtotime($ticket['due_date'])) : 'Not set'; ?></td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td><?php echo date('M j, Y g:i A', strtotime($ticket['created_at'])); ?></td>
                            </tr>
                            <?php if ($ticket['resolved_date']): ?>
                            <tr>
                                <th>Resolved:</th>
                                <td><?php echo date('M j, Y g:i A', strtotime($ticket['resolved_date'])); ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <!-- Equipment Information -->
                <?php if ($ticket['equipment_name']): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-laptop"></i> Related Equipment</h5>
                    </div>
                    <div class="card-body">
                        <p><strong><?php echo htmlspecialchars($ticket['equipment_name']); ?></strong></p>
                        <?php if ($ticket['serial_number']): ?>
                        <p class="text-muted">Serial: <?php echo htmlspecialchars($ticket['serial_number']); ?></p>
                        <?php endif; ?>
                        <a href="/office-inventory/equipment/view?id=<?php echo $ticket['equipment_id']; ?>" class="btn btn-sm btn-outline-primary">
                            View Equipment
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Ticket Content and Responses -->
            <div class="col-md-8">
                <!-- Original Ticket Description -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-file-alt"></i> Description</h5>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                    </div>
                </div>

                <!-- Responses -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-comments"></i> Responses</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($responses)): ?>
                            <p class="text-muted">No responses yet.</p>
                        <?php else: ?>
                            <div class="response-list">
                                <?php foreach ($responses as $response): ?>
                                <div class="response-item mb-3 p-3 border rounded <?php echo $response['internal_note'] ? 'bg-light' : ''; ?>">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong><?php echo htmlspecialchars($response['responded_by_name']); ?></strong>
                                        <small class="text-muted">
                                            <?php echo date('M j, Y g:i A', strtotime($response['created_at'])); ?>
                                            <?php if ($response['internal_note']): ?>
                                                <span class="badge bg-secondary">Internal Note</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div class="response-text">
                                        <?php echo nl2br(htmlspecialchars($response['response_text'])); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add Response Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-reply"></i> Add Response</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="response_text" class="form-label">Your Response *</label>
                                <textarea class="form-control" id="response_text" name="response_text" rows="4" required 
                                          placeholder="Type your response here..."></textarea>
                            </div>
                            
                            <?php if ($authController->hasPermission('manage_tickets') || $ticket['assigned_to'] == $currentUser['id']): ?>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Update Status</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Keep current status</option>
                                            <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                                            <option value="in_progress" <?php echo $ticket['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="on_hold" <?php echo $ticket['status'] == 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
                                            <option value="resolved" <?php echo $ticket['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="internal_note" name="internal_note">
                                        <label class="form-check-label" for="internal_note">Internal Note (not visible to requester)</label>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Response
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>