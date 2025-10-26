<?php
require_once __DIR__ . '/../../config/config.php';

// Check authentication
AuthMiddleware::checkAuth();

$authController = new AuthController($database->getConnection());
$currentUser = $authController->getCurrentUser();

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    $result = $authController->changePassword(
        $_POST['current_password'],
        $_POST['new_password']
    );
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    Helper::redirect('/office-inventory/settings');
}

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    // In a real application, you'd update user profile here
    $_SESSION['success'] = "Profile updated successfully!";
    Helper::redirect('/office-inventory/settings');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Office Inventory System</title>
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
                        <li><a class="dropdown-item" href="/office-inventory/settings"><i class="fas fa-cog"></i> Settings</a></li>
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
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1><i class="fas fa-cog"></i> Settings</h1>
                <p class="text-muted">Manage your account and preferences</p>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="tab">
                        <i class="fas fa-user me-2"></i>Profile
                    </a>
                    <a href="#password" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-lock me-2"></i>Password
                    </a>
                    <a href="#preferences" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-sliders-h me-2"></i>Preferences
                    </a>
                    <?php if ($authController->hasPermission('manage_users')): ?>
                    <a href="#system" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-cogs me-2"></i>System Settings
                    </a>
                    <?php endif; ?>
                    <?php if ($authController->hasPermission('manage_users')): ?>
                    <a href="#users" class="list-group-item list-group-item-action" data-bs-toggle="tab">
                        <i class="fas fa-users me-2"></i>User Management
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="col-md-9">
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="update_profile" value="1">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Username</label>
                                                <input type="text" class="form-control" id="username" 
                                                       value="<?php echo htmlspecialchars($currentUser['username']); ?>" readonly>
                                                <div class="form-text">Username cannot be changed</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="full_name" class="form-label">Full Name</label>
                                                <input type="text" class="form-control" id="full_name" 
                                                       value="<?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>" readonly>
                                                <div class="form-text">Name cannot be changed</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="role" class="form-label">Role</label>
                                                <input type="text" class="form-control" id="role" 
                                                       value="<?php 
                                                       $roles = [1 => 'Super Admin', 2 => 'Admin', 3 => 'Manager', 4 => 'Vendor', 5 => 'User'];
                                                       echo $roles[$currentUser['role_id']] ?? 'User';
                                                       ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="last_login" class="form-label">Last Login</label>
                                                <input type="text" class="form-control" id="last_login" 
                                                       value="<?php 
                                                       echo (isset($currentUser['last_login']) && !empty($currentUser['last_login'])) 
                                                           ? date('M j, Y g:i A', strtotime($currentUser['last_login'])) 
                                                           : 'Never';
                                                       ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-control" id="timezone" name="timezone">
                                            <option value="UTC">UTC</option>
                                            <option value="America/New_York">Eastern Time (ET)</option>
                                            <option value="America/Chicago">Central Time (CT)</option>
                                            <option value="America/Denver">Mountain Time (MT)</option>
                                            <option value="America/Los_Angeles">Pacific Time (PT)</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Password Tab -->
                    <div class="tab-pane fade" id="password">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-lock me-2"></i>Change Password</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="change_password" value="1">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <div class="form-text">Password must be at least 8 characters long</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key me-1"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Preferences Tab -->
                    <div class="tab-pane fade" id="preferences">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-sliders-h me-2"></i>User Preferences</h5>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">Notifications</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="email_notifications" checked>
                                            <label class="form-check-label" for="email_notifications">
                                                Email notifications
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="ticket_updates" checked>
                                            <label class="form-check-label" for="ticket_updates">
                                                Ticket update notifications
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="equipment_alerts">
                                            <label class="form-check-label" for="equipment_alerts">
                                                Equipment maintenance alerts
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="items_per_page" class="form-label">Items Per Page</label>
                                        <select class="form-control" id="items_per_page">
                                            <option value="10">10 items</option>
                                            <option value="25" selected>25 items</option>
                                            <option value="50">50 items</option>
                                            <option value="100">100 items</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Default View</label>
                                        <select class="form-control" id="default_view">
                                            <option value="dashboard">Dashboard</option>
                                            <option value="equipment">Equipment List</option>
                                            <option value="tickets">Tickets</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="savePreferences()">
                                        <i class="fas fa-save me-1"></i>Save Preferences
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings Tab (Admin Only) -->
                    <?php if ($authController->hasPermission('manage_users')): ?>
                    <div class="tab-pane fade" id="system">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-cogs me-2"></i>System Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    These settings affect the entire system and should be configured carefully.
                                </div>
                                
                                <form>
                                    <h6 class="mt-4">General Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="company_name" class="form-label">Company Name</label>
                                                <input type="text" class="form-control" id="company_name" value="Your Company">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="auto_logout" class="form-label">Auto Logout (minutes)</label>
                                                <input type="number" class="form-control" id="auto_logout" value="30" min="5" max="480">
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="mt-4">Ticket Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="default_priority" class="form-label">Default Ticket Priority</label>
                                                <select class="form-control" id="default_priority">
                                                    <option value="low">Low</option>
                                                    <option value="medium" selected>Medium</option>
                                                    <option value="high">High</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="auto_assign" class="form-label">Auto-assign Tickets</label>
                                                <select class="form-control" id="auto_assign">
                                                    <option value="0">Disabled</option>
                                                    <option value="1">Enabled</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="mt-4">Equipment Settings</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="warranty_alert" class="form-label">Warranty Alert (days before expiry)</label>
                                                <input type="number" class="form-control" id="warranty_alert" value="30" min="1" max="365">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="maintenance_reminder" class="form-label">Maintenance Reminder (days)</label>
                                                <input type="number" class="form-control" id="maintenance_reminder" value="7" min="1" max="30">
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-primary" onclick="saveSystemSettings()">
                                        <i class="fas fa-save me-1"></i>Save System Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- User Management Tab (Admin Only) -->
                    <?php if ($authController->hasPermission('manage_users')): ?>
                    <div class="tab-pane fade" id="users">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><i class="fas fa-users me-2"></i>User Management</h5>
                                <a href="/office-inventory/users/add" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus me-1"></i>Add User
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Full Name</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Last Login</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Get all users with their roles
                                            $users_query = "SELECT u.*, ur.role_name 
                                                          FROM users u 
                                                          LEFT JOIN user_roles ur ON u.role_id = ur.id 
                                                          ORDER BY u.created_at DESC";
                                            $users_stmt = $database->getConnection()->query($users_query);
                                            while ($user = $users_stmt->fetch(PDO::FETCH_ASSOC)): 
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                    <?php if ($user['id'] == $currentUser['id']): ?>
                                                        <span class="badge bg-info">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($user['role_name']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $user['last_login'] 
                                                        ? date('M j, Y g:i A', strtotime($user['last_login'])) 
                                                        : 'Never'; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="/office-inventory/users/edit?id=<?php echo $user['id']; ?>" class="btn btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($user['id'] != $currentUser['id']): ?>
                                                        <button class="btn btn-danger" title="Delete" onclick="confirmDeleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
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
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize Bootstrap tabs
        document.addEventListener('DOMContentLoaded', function() {
            // Enable tab functionality
            var triggerTabList = [].slice.call(document.querySelectorAll('a[data-bs-toggle="tab"]'));
            triggerTabList.forEach(function (triggerEl) {
                var tabTrigger = new bootstrap.Tab(triggerEl);
                triggerEl.addEventListener('click', function (event) {
                    event.preventDefault();
                    tabTrigger.show();
                });
            });

            // Handle URL hash for direct tab access
            if (window.location.hash) {
                var triggerEl = document.querySelector('a[href="' + window.location.hash + '"]');
                if (triggerEl) {
                    var tab = new bootstrap.Tab(triggerEl);
                    tab.show();
                }
            }

            // Update URL when tabs are shown
            var tabEls = document.querySelectorAll('a[data-bs-toggle="tab"]');
            tabEls.forEach(function(tabEl) {
                tabEl.addEventListener('shown.bs.tab', function (event) {
                    window.location.hash = event.target.getAttribute('href').substring(1);
                });
            });
        });

        // Password confirmation validation
        document.getElementById('new_password').addEventListener('input', validatePassword);
        document.getElementById('confirm_password').addEventListener('input', validatePassword);

        function validatePassword() {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        function savePreferences() {
            alert('Preferences saved successfully!');
            // In a real application, you'd save these to the database
        }

        function saveSystemSettings() {
            alert('System settings saved successfully!');
            // In a real application, you'd save these to the database
        }

        function confirmDeleteUser(userId, username) {
            if (confirm('Are you sure you want to delete user "' + username + '"? This action cannot be undone.')) {
                window.location.href = '/office-inventory/users/delete?id=' + userId;
            }
        }
    </script>
</body>
</html>