<?php
class UserController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function delete($user_id) {
        try {
            // Prevent self-deletion
            if ($user_id == $_SESSION['user_id']) {
                return ['success' => false, 'message' => 'You cannot delete your own account'];
            }

            $delete_query = "DELETE FROM users WHERE id = ?";
            $stmt = $this->db->prepare($delete_query);
            
            if ($stmt->execute([$user_id])) {
                return ['success' => true, 'message' => 'User deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
}
?>