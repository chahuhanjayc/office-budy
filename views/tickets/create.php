<?php
require_once __DIR__ . '/../../config/config.php';

// Check authentication
AuthMiddleware::checkAuth();

$authController = new AuthController($database->getConnection());
$currentUser = $authController->getCurrentUser();

// Get equipment for dropdown (only available equipment)
$equipment_stmt = $database->getConnection()->query(
    "SELECT id, name, serial_number FROM equipment WHERE status = 'available' ORDER BY name"
);

// Get users for assignment dropdown - only show managers/admins who can handle tickets
$users_stmt = $database->getConnection()->query(
    "SELECT id, username FROM users WHERE is_active = 1 AND role_id IN (1, 2, 3) ORDER BY username"
);

// Handle form submission
if ($_POST) {
    try {
        // Generate ticket number
        $ticket_number = Helper::generateTicketNumber();
        
        // Prepare data
        $data = [
            'ticket_number' => $ticket_number,
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'ticket_type' => $_POST['ticket_type'],
            'priority' => $_POST['priority'],
            'created_by' => $currentUser['id'],
            'department_id' => $_POST['department_id'],
            'equipment_id' => !empty($_POST['equipment_id']) ? $_POST['equipment_id'] : null,
            'assigned_to' => !empty($_POST['assigned_to']) ? $_POST['assigned_to'] : null,
            'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null
        ];
        
        $query = "INSERT INTO tickets 
                 (ticket_number, title, description, ticket_type, priority, created_by, department_id, equipment_id, assigned_to, due_date) 
                 VALUES 
                 (:ticket_number, :title, :description, :ticket_type, :priority, :created_by, :department_id, :equipment_id, :assigned_to, :due_date)";

        $stmt = $database->getConnection()->prepare($query);
        
        if ($stmt->execute($data)) {
            $_SESSION['success'] = "Ticket created successfully! Ticket #: " . $ticket_number;
            Helper::redirect('/office-inventory/tickets');
        } else {
            $_SESSION['error'] = "Failed to create ticket. Please try again.";
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
    <title>Create Ticket - Office Inventory System</title>
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
                <li class="breadcrumb-item active">Create Ticket</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1><i class="fas fa-plus"></i> Create New Ticket</h1>
                <p class="text-muted">Submit a new repair, replacement, or assistance request</p>
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
                                       placeholder="Brief description of the issue"
                                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="ticket_type" class="form-label">Ticket Type *</label>
                                <select class="form-control" id="ticket_type" name="ticket_type" required>
                                    <option value="">Select Type</option>
                                    <option value="repair" <?php echo (isset($_POST['ticket_type']) && $_POST['ticket_type'] == 'repair') ? 'selected' : ''; ?>>Repair Request</option>
                                    <option value="replacement" <?php echo (isset($_POST['ticket_type']) && $_POST['ticket_type'] == 'replacement') ? 'selected' : ''; ?>>Replacement Request</option>
                                    <option value="it_assistance" <?php echo (isset($_POST['ticket_type']) && $_POST['ticket_type'] == 'it_assistance') ? 'selected' : ''; ?>>IT Assistance</option>
                                    <option value="equipment_request" <?php echo (isset($_POST['ticket_type']) && $_POST['ticket_type'] == 'equipment_request') ? 'selected' : ''; ?>>Equipment Request</option>
                                    <option value="other" <?php echo (isset($_POST['ticket_type']) && $_POST['ticket_type'] == 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="priority" class="form-label">Priority *</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="low" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                                    <option value="critical" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'critical') ? 'selected' : ''; ?>>Critical</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department *</label>
                                <select class="form-control" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php 
                                    $dept_stmt = $database->getConnection()->query("SELECT * FROM departments ORDER BY name");
                                    while ($dept = $dept_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $dept['id']; ?>"
                                            <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($dept['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="form-text">Select the department this ticket relates to</div>
                            </div>

                            <div class="mb-3">
                                <label for="equipment_id" class="form-label">Related Equipment (Optional)</label>
                                <select class="form-control" id="equipment_id" name="equipment_id">
                                    <option value="">Select Equipment</option>
                                    <?php while ($equipment = $equipment_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $equipment['id']; ?>" 
                                            <?php echo (isset($_POST['equipment_id']) && $_POST['equipment_id'] == $equipment['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($equipment['name']); ?>
                                            <?php if (!empty($equipment['serial_number'])) echo ' (' . htmlspecialchars($equipment['serial_number']) . ')'; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Assignment & Due Date -->
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-cog"></i> Additional Information</h5>
                            
                            <?php if ($authController->hasPermission('manage_tickets')): ?>
                            <div class="mb-3">
                                <label for="assigned_to" class="form-label">Assign To (Optional)</label>
                                <select class="form-control" id="assigned_to" name="assigned_to">
                                    <option value="">Unassigned</option>
                                    <?php 
                                    $users_stmt->execute(); // Reset pointer
                                    while ($user = $users_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $user['id']; ?>" 
                                            <?php echo (isset($_POST['assigned_to']) && $_POST['assigned_to'] == $user['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <div class="form-text">Only managers and admins can assign tickets</div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="due_date" class="form-label">Due Date (Optional)</label>
                                <input type="date" class="form-control" id="due_date" name="due_date"
                                       value="<?php echo isset($_POST['due_date']) ? htmlspecialchars($_POST['due_date']) : ''; ?>">
                                <div class="form-text">Set a target completion date</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="8" required 
                                          placeholder="Please provide detailed information about your request..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                <div class="form-text">Be as detailed as possible to help us resolve your issue quickly</div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="/office-inventory/tickets" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Tickets
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Submit Ticket
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set minimum due date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('due_date').min = today;
        
        // Auto-generate title based on type and equipment
        document.getElementById('ticket_type').addEventListener('change', updateTitleSuggestion);
        document.getElementById('equipment_id').addEventListener('change', updateTitleSuggestion);
        
        function updateTitleSuggestion() {
            const type = document.getElementById('ticket_type').value;
            const equipmentSelect = document.getElementById('equipment_id');
            const equipmentText = equipmentSelect.options[equipmentSelect.selectedIndex].text.split(' (')[0];
            
            if (type && equipmentText && equipmentText !== 'Select Equipment') {
                const titles = {
                    'repair': `Repair needed for ${equipmentText}`,
                    'replacement': `Replacement request for ${equipmentText}`,
                    'it_assistance': `IT assistance for ${equipmentText}`,
                    'equipment_request': `Request for ${equipmentText}`,
                    'other': `Issue with ${equipmentText}`
                };
                
                if (!document.getElementById('title').value) {
                    document.getElementById('title').value = titles[type];
                }
            }
        }
    </script>
</body>
</html>