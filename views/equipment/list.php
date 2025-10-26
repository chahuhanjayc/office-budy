<?php
require_once __DIR__ . '/../../config/config.php';

// Check authentication and permission
AuthMiddleware::checkAuth();
AuthMiddleware::checkPermission('manage_equipment');

$equipmentModel = new Equipment($database->getConnection());
$authController = new AuthController($database->getConnection());
$currentUser = $authController->getCurrentUser();

// Get equipment based on user role and department
$user_role = Session::getUserRole();
$user_dept = Session::get('user_department');

$equipment_query = "SELECT e.*, c.name as category_name, v.name as vendor_name, d.name as department_name
                   FROM equipment e 
                   LEFT JOIN categories c ON e.category_id = c.id 
                   LEFT JOIN vendors v ON e.vendor_id = v.id
                   LEFT JOIN departments d ON e.department_id = d.id";

// Apply filtering
if ($user_role == 3 || $user_role == 4) { // Manager or Vendor
    $equipment_query .= " WHERE e.department_id = ?";
    $params = [$user_dept];
} elseif ($user_role == 5) { // Regular User
    $equipment_query .= " WHERE 1=0"; // Users shouldn't see equipment list
    $params = [];
} else { // Admin/Super Admin
    $params = [];
}

$equipment_query .= " ORDER BY e.created_at DESC";

$stmt = $database->getConnection()->prepare($equipment_query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment List - Office Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/office-inventory/logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </li>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-8">
                <h1><i class="fas fa-laptop"></i> Equipment Management</h1>
                <p class="text-muted">Manage all office equipment and inventory</p>
            </div>
            <div class="col-4 text-end">
                <a href="/office-inventory/equipment/add" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Equipment
                </a>
            </div>
        </div>

        <!-- Equipment Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">All Equipment</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table id="equipmentTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Serial Number</th>
                                <th>Brand/Model</th>
                                <th>Status</th>
                                <th>Purchase Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['serial_number']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($row['brand']); ?>
                                    <?php if (!empty($row['model'])) echo ' / ' . htmlspecialchars($row['model']); ?>
                                </td>
                                <td>
                                    <?php 
                                    $status_badge = [
                                        'available' => 'success',
                                        'assigned' => 'warning', 
                                        'maintenance' => 'danger',
                                        'with_vendor' => 'info',
                                        'retired' => 'secondary'
                                    ];
                                    $status_text = [
                                        'available' => 'Available',
                                        'assigned' => 'Assigned',
                                        'maintenance' => 'Maintenance',
                                        'with_vendor' => 'With Vendor',
                                        'retired' => 'Retired'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $status_badge[$row['status']]; ?>">
                                        <?php echo $status_text[$row['status']]; ?>
                                    </span>
                                </td>
                                <td><?php echo Helper::formatDate($row['purchase_date']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/office-inventory/equipment/view?id=<?php echo $row['id']; ?>" class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/office-inventory/equipment/edit?id=<?php echo $row['id']; ?>" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-danger" title="Delete" onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($stmt->rowCount() == 0): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h4>No Equipment Found</h4>
                        <p class="text-muted">Get started by adding your first piece of equipment.</p>
                        <a href="/office-inventory/equipment/add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Equipment
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#equipmentTable').DataTable({
                pageLength: 25,
                order: [[0, 'desc']]
            });
        });

        function confirmDelete(equipmentId) {
            if (confirm('Are you sure you want to delete this equipment? This action cannot be undone.')) {
                window.location.href = '/office-inventory/equipment/delete?id=' + equipmentId;
            }
        }
    </script>
</body>
</html>