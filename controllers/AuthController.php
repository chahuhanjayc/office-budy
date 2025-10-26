<?php
class AuthController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    // Handle user login - UPDATED: changed from email to username
    public function login($username, $password) {
        // Validate inputs
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }

        $this->user->username = $username;

        // Check if username exists - UPDATED: using usernameExists instead of emailExists
        if ($this->user->usernameExists()) {
            // Verify password
            if (password_verify($password, $this->user->password_hash)) {
                // Check if user is active
                if ($this->user->is_active) {
                    // Update last login
                    $this->user->updateLastLogin();

                    // Set session variables - UPDATED: removed user_email
                    Session::set('user_id', $this->user->id);
                    Session::set('username', $this->user->username);
                    Session::set('first_name', $this->user->first_name);
                    Session::set('last_name', $this->user->last_name);
                    Session::set('user_role', $this->user->role_id);
                    
                    // Get and store user permissions
                    $permissions = $this->user->getPermissions();
                    Session::set('user_permissions', $permissions);
                    Session::set('user_department', $this->user->department_id);

                    // Log the login
                    $this->logActivity('user_login', 'User logged in successfully');

                    return ['success' => true, 'message' => 'Login successful', 'role_id' => $this->user->role_id];
                } else {
                    return ['success' => false, 'message' => 'Account is deactivated'];
                }
            }
        }

        return ['success' => false, 'message' => 'Invalid username or password'];
    }

    // Handle user logout
    public function logout() {
        // Log the logout
        $this->logActivity('user_logout', 'User logged out');
        
        // Destroy session
        Session::destroy();
        
        return ['success' => true, 'message' => 'Logout successful'];
    }

    // Check if user is authenticated
    public function isAuthenticated() {
        return Session::isLoggedIn();
    }

    // Check if user has specific permission
    public function hasPermission($permission) {
        return Session::hasPermission($permission);
    }

    // Get current user info - UPDATED: removed email field
    public function getCurrentUser() {
        $user_id = Session::get('user_id');
        
        if (!$user_id) {
            return null;
        }

        // Get complete user data from database
        $query = "SELECT u.*, ur.role_name 
                  FROM users u 
                  LEFT JOIN user_roles ur ON u.role_id = ur.id 
                  WHERE u.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data) {
            return null;
        }

        return [
            'id' => $user_data['id'],
            'username' => $user_data['username'],
            'first_name' => $user_data['first_name'],
            'last_name' => $user_data['last_name'],
            'role_id' => $user_data['role_id'],
            'role_name' => $user_data['role_name'],
            'employee_id' => $user_data['employee_id'],
            'is_active' => $user_data['is_active'],
            'last_login' => $user_data['last_login'] ?? null,
            'created_at' => $user_data['created_at'],
            'updated_at' => $user_data['updated_at'],
            'permissions' => Session::get('user_permissions') ?? []
        ];
    }

    // Change password
    public function changePassword($current_password, $new_password) {
        $user_id = Session::get('user_id');
        
        if (!$user_id) {
            return ['success' => false, 'message' => 'User not authenticated'];
        }

        // Get current user data
        $query = "SELECT password_hash FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify current password
            if (password_verify($current_password, $row['password_hash'])) {
                // Update to new password
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $update_query = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
                $update_stmt = $this->db->prepare($update_query);
                $update_stmt->bindParam(":password_hash", $new_password_hash);
                $update_stmt->bindParam(":id", $user_id);
                
                if ($update_stmt->execute()) {
                    $this->logActivity('password_change', 'Password changed successfully');
                    return ['success' => true, 'message' => 'Password changed successfully'];
                } else {
                    return ['success' => false, 'message' => 'Failed to update password'];
                }
            } else {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
        }
        
        return ['success' => false, 'message' => 'User not found'];
    }

    // Log user activity
    private function logActivity($action, $description) {
        // Temporarily disabled - audit_logs table doesn't exist yet
        return;
        
        /*
        $user_id = Session::get('user_id');
        
        if ($user_id) {
            $query = "INSERT INTO audit_logs (user_id, action, description, ip_address, user_agent) 
                     VALUES (:user_id, :action, :description, :ip_address, :user_agent)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":action", $action);
            $stmt->bindParam(":description", $description);
            $stmt->bindParam(":ip_address", $_SERVER['REMOTE_ADDR']);
            $stmt->bindParam(":user_agent", $_SERVER['HTTP_USER_AGENT']);
            
            $stmt->execute();
        }
        */
    }
}
?>