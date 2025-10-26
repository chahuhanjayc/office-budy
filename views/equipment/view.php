<?php
require_once __DIR__ . '/../../config/config.php';

// Check authentication and permission
AuthMiddleware::checkAuth();
AuthMiddleware::checkPermission('manage_equipment');

$equipmentModel = new Equipment($database->getConnection());
$authController = new AuthController($database->getConnection());
$currentUser = $authController->getCurrentUser();

// Get equipment ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Equipment ID not specified";
    Helper::redirect('/office-inventory/equipment');
}

$equipmentModel->id = $_GET['id'];
$equipment = $equipmentModel->readWithDetails();

if (!$equipment) {
    $_SESSION['error'] = "Equipment not found";
    Helper::redirect('/office-inventory/equipment');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Equipment - Office Inventory System</title>
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
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/office-inventory/">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/office-inventory/equipment">Equipment</a></li>
                <li class="breadcrumb-item active">View Equipment</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-8">
                <h1><i class="fas fa-laptop"></i> <?php echo htmlspecialchars($equipment['name']); ?></h1>
                <p class="text-muted">Equipment Details</p>
            </div>
            <div class="col-4 text-end">
                <a href="/office-inventory/equipment/edit?id=<?php echo $equipment['id']; ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Equipment
                </a>
                <a href="/office-inventory/equipment" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Equipment Details -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-info-circle"></i> Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Name:</th>
                                <td><?php echo htmlspecialchars($equipment['name']); ?></td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td><?php echo htmlspecialchars($equipment['category_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Serial Number:</th>
                                <td><?php echo htmlspecialchars($equipment['serial_number'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Brand/Model:</th>
                                <td>
                                    <?php echo htmlspecialchars($equipment['brand'] ?? 'N/A'); ?>
                                    <?php if (!empty($equipment['model'])) echo ' / ' . htmlspecialchars($equipment['model']); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
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
                                    <span class="badge bg-<?php echo $status_badge[$equipment['status']]; ?>">
                                        <?php echo $status_text[$equipment['status']]; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-shopping-cart"></i> Purchase Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Purchase Date:</th>
                                <td><?php echo Helper::formatDate($equipment['purchase_date']); ?></td>
                            </tr>
                            <tr>
                                <th>Purchase Price:</th>
                                <td>
                                    <?php if (!empty($equipment['purchase_price'])): ?>
                                        $<?php echo number_format($equipment['purchase_price'], 2); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Warranty Expiry:</th>
                                <td><?php echo Helper::formatDate($equipment['warranty_expiry']); ?></td>
                            </tr>
                            <tr>
                                <th>Vendor:</th>
                                <td><?php echo htmlspecialchars($equipment['vendor_name'] ?? 'N/A'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <?php if (!empty($equipment['notes'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-sticky-note"></i> Notes</h5>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($equipment['notes'])); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Assignment Info -->
        <?php if ($equipment['status'] == 'assigned' && !empty($equipment['assigned_to_name'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-user-check"></i> Current Assignment</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th width="30%">Assigned To:</th>
                                <td><?php echo htmlspecialchars($equipment['assigned_to_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Assigned Date:</th>
                                <td><?php echo Helper::formatDate($equipment['assigned_date']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>