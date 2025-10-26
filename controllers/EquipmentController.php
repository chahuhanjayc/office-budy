<?php
class EquipmentController {
    private $db;
    private $equipment;

    public function __construct($db) {
        $this->db = $db;
        $this->equipment = new Equipment($db);
    }

    public function delete($equipment_id) {
        $this->equipment->id = $equipment_id;
        
        if ($this->equipment->delete()) {
            return ['success' => true, 'message' => 'Equipment deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Cannot delete equipment. It may be assigned to an employee, has active returns, or open tickets.'];
        }
    }

    // We'll add more methods here later for other operations
}
?>