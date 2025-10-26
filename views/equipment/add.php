<?php
require_once __DIR__ . '/../../config/config.php';

// Check authentication and permission
AuthMiddleware::checkAuth();
AuthMiddleware::checkPermission('manage_equipment');

$equipmentModel = new Equipment($database->getConnection());
$authController = new AuthController($database->getConnection());
$currentUser = $authController->getCurrentUser();

// Get categories for dropdown
$category_stmt = $database->getConnection()->query("SELECT * FROM categories ORDER BY name");
$vendor_stmt = $database->getConnection()->query("SELECT * FROM vendors ORDER BY name");

// Handle form submission
if ($_POST) {
    try {
        $equipmentModel->category_id = $_POST['category_id'];
        $equipmentModel->name = Helper::sanitize($_POST['name']);
        $equipmentModel->serial_number = Helper::sanitize($_POST['serial_number']);
        $equipmentModel->model = Helper::sanitize($_POST['model']);
        $equipmentModel->brand = Helper::sanitize($_POST['brand']);
        $equipmentModel->status = $_POST['status'];
        $equipmentModel->purchase_date = $_POST['purchase_date'];
        $equipmentModel->purchase_price = $_POST['purchase_price'];
        $equipmentModel->warranty_expiry = $_POST['warranty_expiry'] ?: null;
        $equipmentModel->vendor_id = $_POST['vendor_id'] ?: null;
        $equipmentModel->notes = Helper::sanitize($_POST['notes']);

        if ($equipmentModel->create()) {
            $_SESSION['success'] = "Equipment added successfully!";
            Helper::redirect('/office-inventory/equipment');
        } else {
            $_SESSION['error'] = "Failed to add equipment. Please try again.";
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
    <title>Add Equipment - Office Inventory System</title>
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
                        <li><a class="dropdown-item" href="/office-inventory/equipment"><i class="fas fa-laptop"></i> Equipment</a></li>
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
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/office-inventory/">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="/office-inventory/equipment">Equipment</a></li>
                        <li class="breadcrumb-item active">Add Equipment</li>
                    </ol>
                </nav>
                <h1><i class="fas fa-plus"></i> Add New Equipment</h1>
                <p class="text-muted">Add a new piece of equipment to your inventory</p>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Equipment Form -->
        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Basic Information</h5>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Equipment Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php while ($category = $category_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $category['id']; ?>" 
                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number"
                                       value="<?php echo isset($_POST['serial_number']) ? htmlspecialchars($_POST['serial_number']) : ''; ?>">
                                <div class="form-text">Unique identifier for the equipment</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="brand" class="form-label">Brand</label>
                                        <input type="text" class="form-control" id="brand" name="brand"
                                               value="<?php echo isset($_POST['brand']) ? htmlspecialchars($_POST['brand']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" class="form-control" id="model" name="model"
                                               value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status & Purchase Info -->
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-cog"></i> Status & Purchase</h5>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="available" <?php echo (isset($_POST['status']) && $_POST['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                    <option value="maintenance" <?php echo (isset($_POST['status']) && $_POST['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="retired" <?php echo (isset($_POST['status']) && $_POST['status'] == 'retired') ? 'selected' : ''; ?>>Retired</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="purchase_date" class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                                       value="<?php echo isset($_POST['purchase_date']) ? htmlspecialchars($_POST['purchase_date']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="purchase_price" class="form-label">Purchase Price ($)</label>
                                <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price"
                                       value="<?php echo isset($_POST['purchase_price']) ? htmlspecialchars($_POST['purchase_price']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry"
                                       value="<?php echo isset($_POST['warranty_expiry']) ? htmlspecialchars($_POST['warranty_expiry']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="vendor_id" class="form-label">Vendor</label>
                                <select class="form-control" id="vendor_id" name="vendor_id">
                                    <option value="">Select Vendor</option>
                                    <?php 
                                    $vendor_stmt->execute(); // Reset pointer
                                    while ($vendor = $vendor_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $vendor['id']; ?>" 
                                            <?php echo (isset($_POST['vendor_id']) && $_POST['vendor_id'] == $vendor['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($vendor['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="/office-inventory/equipment" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Equipment List
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Equipment
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
        // Set today's date as default for purchase date
        document.getElementById('purchase_date').value = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>