<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $first_name;
    public $last_name;
    public $password_hash;
    public $role_id;
    public $employee_id;
    public $is_active;
    public $last_login;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Check if user exists by username
    public function usernameExists() {
        $query = "SELECT id, first_name, last_name, password_hash, role_id, is_active 
                 FROM " . $this->table_name . " 
                 WHERE username = :username AND is_active = 1 
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->password_hash = $row['password_hash'];
            $this->role_id = $row['role_id'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    // Get full name
    public function getFullName() {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Update last login
    public function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " 
                 SET last_login = NOW() 
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    // Get user permissions
    public function getPermissions() {
        $query = "SELECT p.permission_name 
                 FROM role_permissions rp 
                 JOIN permissions p ON rp.permission_id = p.id 
                 WHERE rp.role_id = :role_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":role_id", $this->role_id);
        $stmt->execute();

        $permissions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[] = $row['permission_name'];
        }
        return $permissions;
    }

    // Check if user has specific permission
    public function hasPermission($permission) {
        $permissions = $this->getPermissions();
        return in_array($permission, $permissions);
    }
}
?>