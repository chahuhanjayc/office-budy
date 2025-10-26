<?php
class TicketController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function delete($ticket_id) {
        try {
            // First delete related responses
            $delete_responses = "DELETE FROM ticket_responses WHERE ticket_id = ?";
            $stmt1 = $this->db->prepare($delete_responses);
            $stmt1->execute([$ticket_id]);
            
            // Then delete the ticket
            $delete_ticket = "DELETE FROM tickets WHERE id = ?";
            $stmt2 = $this->db->prepare($delete_ticket);
            
            if ($stmt2->execute([$ticket_id])) {
                return ['success' => true, 'message' => 'Ticket deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Failed to delete ticket'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function index() {
        $currentUser = $_SESSION['user']; // Assuming user data is in session
        $userRole = $currentUser['role_id'];
        $userId = $currentUser['id'];
        
        $sql = "SELECT t.*, e.name as equipment_name, d.name as department_name,
                       creator.username as created_by_name,
                       assignee.username as assigned_to_name
                FROM tickets t
                LEFT JOIN equipment e ON t.equipment_id = e.id
                LEFT JOIN departments d ON t.department_id = d.id
                LEFT JOIN users creator ON t.created_by = creator.id
                LEFT JOIN users assignee ON t.assigned_to = assignee.id";
        
        $params = [];
        
        // Apply filters based on user role
        if ($userRole == 3) { // Manager
            $sql .= " WHERE t.assigned_to = ?";
            $params[] = $userId;
        } elseif ($userRole == 5) { // Regular User
            $sql .= " WHERE t.created_by = ?"; 
            $params[] = $userId;
        }
        // Admin/Super Admin (roles 1,2) see all tickets - no WHERE clause
        
        $sql .= " ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>