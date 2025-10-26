<?php
class Equipment {
    private $conn;
    private $table_name = "equipment";

    public $id;
    public $category_id;
    public $name;
    public $serial_number;
    public $model;
    public $brand;
    public $status;
    public $purchase_date;
    public $purchase_price;
    public $warranty_expiry;
    public $vendor_id;
    public $notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new equipment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET category_id=:category_id, name=:name, serial_number=:serial_number, 
                     model=:model, brand=:brand, status=:status, purchase_date=:purchase_date, 
                     purchase_price=:purchase_price, warranty_expiry=:warranty_expiry, 
                     vendor_id=:vendor_id, notes=:notes";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":serial_number", $this->serial_number);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":purchase_price", $this->purchase_price);
        $stmt->bindParam(":warranty_expiry", $this->warranty_expiry);
        $stmt->bindParam(":vendor_id", $this->vendor_id);
        $stmt->bindParam(":notes", $this->notes);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Read all equipment with category name
    public function read() {
        $query = "SELECT e.*, c.name as category_name, v.name as vendor_name
                 FROM " . $this->table_name . " e 
                 LEFT JOIN categories c ON e.category_id = c.id 
                 LEFT JOIN vendors v ON e.vendor_id = v.id
                 ORDER BY e.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update equipment
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET category_id=:category_id, name=:name, serial_number=:serial_number, 
                     model=:model, brand=:brand, status=:status, purchase_date=:purchase_date, 
                     purchase_price=:purchase_price, warranty_expiry=:warranty_expiry, 
                     vendor_id=:vendor_id, notes=:notes
                 WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":serial_number", $this->serial_number);
        $stmt->bindParam(":model", $this->model);
        $stmt->bindParam(":brand", $this->brand);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":purchase_date", $this->purchase_date);
        $stmt->bindParam(":purchase_price", $this->purchase_price);
        $stmt->bindParam(":warranty_expiry", $this->warranty_expiry);
        $stmt->bindParam(":vendor_id", $this->vendor_id);
        $stmt->bindParam(":notes", $this->notes);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Delete equipment
    public function delete() {
        // First check if equipment exists and can be deleted
        if (!$this->checkDeleteConstraints()) {
            return false;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if equipment can be deleted (no dependencies)
    private function checkDeleteConstraints() {
        // Check if equipment is assigned to anyone
        $check_assignments = "SELECT COUNT(*) as assignment_count FROM assignments WHERE equipment_id = :id AND actual_return_date IS NULL";
        $stmt1 = $this->conn->prepare($check_assignments);
        $stmt1->bindParam(":id", $this->id);
        $stmt1->execute();
        $assignments = $stmt1->fetch(PDO::FETCH_ASSOC);

        // Check if equipment has active vendor returns
        $check_returns = "SELECT COUNT(*) as return_count FROM vendor_returns WHERE equipment_id = :id AND status NOT IN ('cancelled', 'replaced')";
        $stmt2 = $this->conn->prepare($check_returns);
        $stmt2->bindParam(":id", $this->id);
        $stmt2->execute();
        $returns = $stmt2->fetch(PDO::FETCH_ASSOC);

        // Check if equipment has active tickets
        $check_tickets = "SELECT COUNT(*) as ticket_count FROM tickets WHERE equipment_id = :id AND status NOT IN ('closed', 'resolved')";
        $stmt3 = $this->conn->prepare($check_tickets);
        $stmt3->bindParam(":id", $this->id);
        $stmt3->execute();
        $tickets = $stmt3->fetch(PDO::FETCH_ASSOC);

        return ($assignments['assignment_count'] == 0 && $returns['return_count'] == 0 && $tickets['ticket_count'] == 0);
    }

    // Get equipment by ID
    public function readOne() {
        $query = "SELECT e.*, c.name as category_name, v.name as vendor_name
                 FROM " . $this->table_name . " e 
                 LEFT JOIN categories c ON e.category_id = c.id 
                 LEFT JOIN vendors v ON e.vendor_id = v.id
                 WHERE e.id = :id 
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Set property values
            foreach($row as $key => $value) {
                if(property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return true;
        }
        return false;
    }

    // Get equipment with all related data for view page
    public function readWithDetails() {
        $query = "SELECT e.*, c.name as category_name, v.name as vendor_name,
                         emp.name as assigned_to_name, a.assigned_date
                  FROM " . $this->table_name . " e 
                  LEFT JOIN categories c ON e.category_id = c.id 
                  LEFT JOIN vendors v ON e.vendor_id = v.id
                  LEFT JOIN assignments a ON e.id = a.equipment_id AND a.actual_return_date IS NULL
                  LEFT JOIN employees emp ON a.employee_id = emp.id
                  WHERE e.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    // Get equipment statistics
    public function getStats() {
        $query = "SELECT 
                    COUNT(*) as total_equipment,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned,
                    SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
                    SUM(CASE WHEN status = 'with_vendor' THEN 1 ELSE 0 END) as with_vendor
                 FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>