<?php
require_once __DIR__ . '/../../config/config.php';

// Check authentication and permission
AuthMiddleware::checkAuth();
AuthMiddleware::checkPermission('manage_users');

$authController = new AuthController($database->getConnection());
$currentUser = $authController->getCurrentUser();

// Get user ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "User ID not specified";
    Helper::redirect('/office-inventory/settings#users');
}

$user_id = $_GET['id'];

// Get user details
$user_query = "SELECT u.*, ur.role_name 
               FROM users u 
               LEFT JOIN user_roles ur ON u.role_id = ur.id 
               WHERE u.id = ?";
$stmt = $database->getConnection()->prepare($user_query);
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User not found";
    Helper::redirect('/office-inventory/settings#users');
}

// Get roles for dropdown
$roles_stmt = $database->getConnection()->query("SELECT * FROM user_roles ORDER BY id");

// Handle form submission
if ($_POST) {
    try {
        $first_name = Helper::sanitize($_POST['first_name']);
        $last_name = Helper::sanitize($_POST['last_name']);
        $username = Helper::sanitize($_POST['username']);
        $role_id = $_POST['role_id'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Check if username already exists (excluding current user)
        $check_query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $check_stmt = $database->getConnection()->prepare($check_query);
        $check_stmt->execute([$username, $user_id]);
        
        if ($check_stmt->rowCount() > 0) {
            $_SESSION['error'] = "Username already exists";
        } else {
            $department_id = ($role_id == 3 || $role_id == 4) ? $_POST['department_id'] : NULL;
            
            // If password is provided
            if (!empty($_POST['password'])) {
                $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query = "UPDATE users SET first_name = ?, last_name = ?, username = ?, password_hash = ?, role_id = ?, department_id = ?, is_active = ? WHERE id = ?";
                $stmt = $database->getConnection()->prepare($query);
                $stmt->execute([$first_name, $last_name, $username, $password_hash, $role_id, $department_id, $is_active, $user_id]);
            } else {
                $query = "UPDATE users SET first_name = ?, last_name = ?, username = ?, role_id = ?, department_id = ?, is_active = ? WHERE id = ?";
                $stmt = $database->getConnection()->prepare($query);
                $stmt->execute([$first_name, $last_name, $username, $role_id, $department_id, $is_active, $user_id]);
            }

            if ($stmt->rowCount() > 0) {
                $_SESSION['success'] = "User updated successfully!";
                Helper::redirect('/office-inventory/settings#users');
            } else {
                $_SESSION['error'] = "No changes were made or failed to update user.";
            }
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
    <title>Edit User - Office Inventory System</title>
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
                <li class="breadcrumb-item"><a href="/office-inventory/settings">Settings</a></li>
                <li class="breadcrumb-item"><a href="/office-inventory/settings#users">User Management</a></li>
                <li class="breadcrumb-item active">Edit User</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1><i class="fas fa-edit"></i> Edit User</h1>
                <p class="text-muted">Update user account information</p>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- User Form -->
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
				<div class="row">
					<div class="col-md-6">
						<h5 class="mb-3"><i class="fas fa-info-circle"></i> Account Information</h5>
						
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<label for="first_name" class="form-label">First Name *</label>
									<input type="text" class="form-control" id="first_name" name="first_name" required
										   value="<?php echo htmlspecialchars($user['first_name']); ?>">
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<label for="last_name" class="form-label">Last Name *</label>
									<input type="text" class="form-control" id="last_name" name="last_name" required
										   value="<?php echo htmlspecialchars($user['last_name']); ?>">
								</div>
							</div>
						</div>

						<div class="mb-3">
							<label for="username" class="form-label">Username *</label>
							<input type="text" class="form-control" id="username" name="username" required
								   value="<?php echo htmlspecialchars($user['username']); ?>">
						</div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password"
                                       minlength="8">
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-cog"></i> Account Settings</h5>
                            
                            <div class="mb-3">
                                <label for="role_id" class="form-label">Role *</label>
                                <select class="form-control" id="role_id" name="role_id" required>
                                    <?php 
                                    $roles_stmt->execute(); // Reset pointer
                                    while ($role = $roles_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $role['id']; ?>"
                                            <?php echo $user['role_id'] == $role['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($role['role_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
							
							<div class="mb-3" id="departmentField" style="display: none;">
									<label for="department_id" class="form-label">Department *</label>
									<select class="form-control" id="department_id" name="department_id">
										<option value="">Select Department</option>
										<?php 
										$dept_stmt = $database->getConnection()->query("SELECT * FROM departments ORDER BY name");
										while ($dept = $dept_stmt->fetch(PDO::FETCH_ASSOC)): ?>
											<option value="<?php echo $dept['id']; ?>"
												<?php echo (isset($user['department_id']) && $user['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
												<?php echo htmlspecialchars($dept['name']); ?>
											</option>
										<?php endwhile; ?>
									</select>
									<div class="form-text">Required for Manager and Vendor roles</div>
								</div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                        <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">Active Account</label>
                                </div>
                                <div class="form-text">Inactive users cannot log in to the system</div>
                            </div>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6><i class="fas fa-history me-2"></i>Account Activity</h6>
                                    <p class="mb-1"><strong>Last Login:</strong> 
                                        <?php echo $user['last_login'] 
                                            ? date('M j, Y g:i A', strtotime($user['last_login'])) 
                                            : 'Never'; ?>
                                    </p>
                                    <p class="mb-1"><strong>Created:</strong> 
                                        <?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?>
                                    </p>
                                    <p class="mb-0"><strong>Last Updated:</strong> 
                                        <?php echo $user['updated_at'] 
                                            ? date('M j, Y g:i A', strtotime($user['updated_at'])) 
                                            : 'Never'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="/office-inventory/settings#users" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to User Management
                                </a>
                                <div>
                                    <?php if ($user['id'] != $currentUser['id']): ?>
                                    <a href="/office-inventory/users/delete?id=<?php echo $user['id']; ?>" class="btn btn-danger me-2" 
                                       onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i> Delete User
                                    </a>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update User
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
<script>
    // Password confirmation validation
    document.getElementById('password').addEventListener('input', validatePassword);
    document.getElementById('confirm_password').addEventListener('input', validatePassword);

    function validatePassword() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }

    // Generate random password
    function generatePassword() {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('password').value = password;
        document.getElementById('confirm_password').value = password;
        validatePassword();
    }

    // Toggle password visibility
    function togglePasswordVisibility(fieldId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.querySelector(`[onclick="togglePasswordVisibility('${fieldId}')] i`);
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.className = 'fas fa-eye-slash';
        } else {
            passwordField.type = 'password';
            toggleIcon.className = 'fas fa-eye';
        }
    }

    // Show/hide department field based on role
    function toggleDepartmentField() {
        const roleId = document.getElementById('role_id').value;
        const departmentField = document.getElementById('departmentField');
        const departmentSelect = document.getElementById('department_id');
        
        // Show department field for Manager (role_id=3) and Vendor (role_id=4)
        if (roleId == 3 || roleId == 4) {
            departmentField.style.display = 'block';
            departmentSelect.required = true;
        } else {
            departmentField.style.display = 'none';
            departmentSelect.required = false;
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        // Add toggle icons to password fields
        const passwordFields = ['password', 'confirm_password'];
        
        passwordFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                const wrapper = document.createElement('div');
                wrapper.className = 'position-relative';
                
                // Create toggle button
                const toggleBtn = document.createElement('button');
                toggleBtn.type = 'button';
                toggleBtn.className = 'btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2';
                toggleBtn.style.background = 'none';
                toggleBtn.style.border = 'none';
                toggleBtn.style.zIndex = '5';
                toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
                toggleBtn.onclick = function() { togglePasswordVisibility(fieldId); };
                
                // Wrap the field
                field.parentNode.insertBefore(wrapper, field);
                wrapper.appendChild(field);
                wrapper.appendChild(toggleBtn);
                
                // Add padding to prevent text behind icon
                field.style.paddingRight = '2.5rem';
            }
        });

        // Add generate password button (show in edit.php too since it's useful)
        const passwordField = document.getElementById('password');
        if (passwordField) {
            const generateBtn = document.createElement('button');
            generateBtn.type = 'button';
            generateBtn.className = 'btn btn-outline-primary btn-sm mt-2';
            generateBtn.innerHTML = '<i class="fas fa-dice me-1"></i>Generate Strong Password';
            generateBtn.onclick = generatePassword;
            passwordField.closest('.mb-3').appendChild(generateBtn);
        }

        // Initialize department field
        toggleDepartmentField();
    });

    // Add event listener for role change
    document.getElementById('role_id').addEventListener('change', toggleDepartmentField);
</script>
</body>
</html>