<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include session management
include_once('./inc/session_manager.php');
include_once('./inc/functions.php');
include_once('./inc/access_log.php');

// Log inventory page access
log_access('VIEW_INVENTORY', 'inventory.php');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    $make = validate_input($_POST['make']);
    $model = validate_input($_POST['model']);
    $year = (int)$_POST['year'];
    $vin = validate_input($_POST['vin']);
    $color = validate_input($_POST['color']);
    $purchase_price = (float)$_POST['purchase_price'];
    $purchase_date = $_POST['purchase_date'];
    
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = 'uploads/vehicles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO vehicles (make, model, year, vin, color, purchase_price, purchase_date, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$make, $model, $year, $vin, $color, $purchase_price, $purchase_date, $image_path]);
        log_access('ADD_VEHICLE', 'inventory.php', null, null, "Added $make $model");
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>Vehicle added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
        // Redirect to prevent form resubmission
        header("Location: inventory.php?success=1");
        exit;
    } catch (PDOException $e) {
        log_access('ADD_VEHICLE_FAILED', 'inventory.php', null, null, "Failed to add vehicle: " . $e->getMessage());
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Error adding vehicle: ' . $e->getMessage() . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    }
}

// Show success message after redirect
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'deleted') {
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>Vehicle deleted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    } else {
        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>Vehicle added successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    }
}

// Show error messages
if (isset($_GET['error'])) {
    $error_messages = [
        'invalid_id' => 'Invalid vehicle ID provided.',
        'not_found' => 'Vehicle not found.',
        'delete_failed' => 'Failed to delete vehicle. Please try again.'
    ];
    $error_msg = isset($error_messages[$_GET['error']]) ? $error_messages[$_GET['error']] : 'An error occurred.';
    $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>' . $error_msg . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
}

// Fetch all vehicles
$stmt = $pdo->query("SELECT * FROM vehicles ORDER BY created_at DESC");
$vehicles = $stmt->fetchAll();

// Calculate statistics
$total_vehicles = count($vehicles);
$total_value = array_sum(array_column($vehicles, 'purchase_price'));
$available_count = count(array_filter($vehicles, function($v) { return $v['status'] == 'Available'; }));
$sold_count = count(array_filter($vehicles, function($v) { return $v['status'] == 'Sold'; }));

$path_to_root = './';
include($path_to_root . 'inc/header.php');
?>

<style>
    :root {
        --primary-color: #2563eb;
        --primary-dark: #1d4ed8;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-600: #4b5563;
        --gray-700: #374151;
        --gray-800: #1f2937;
        --gray-900: #111827;
    }

    .inventory-container {
        padding: 2rem;
        max-width: 100%;
    }

    .page-header {
        background-color: #ffffff;
        border-bottom: 2px solid var(--gray-200);
        padding: 1.5rem;
        margin-bottom: 2rem;
        border-radius: 8px;
    }

    .page-header h1 {
        font-weight: 600;
        font-size: 1.875rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: var(--gray-900);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 1.25rem;
        transition: all 0.2s;
    }

    .stat-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .stat-icon.primary { background-color: #eff6ff; color: var(--primary-color); }
    .stat-icon.success { background-color: #f0fdf4; color: var(--success-color); }
    .stat-icon.warning { background-color: #fffbeb; color: var(--warning-color); }
    .stat-icon.danger { background-color: #fef2f2; color: var(--danger-color); }

    .stat-label {
        color: var(--gray-600);
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .content-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        overflow: hidden;
    }

    .content-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: var(--gray-50);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .content-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-900);
        margin: 0;
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        border: none;
        color: white;
        padding: 0.625rem 1.25rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-primary-custom:hover {
        background-color: var(--primary-dark);
        color: white;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 900px;
    }

    .modern-table thead th {
        background-color: var(--gray-50);
        color: var(--gray-700);
        font-weight: 600;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        padding: 0.875rem 1rem;
        text-align: left;
        border-bottom: 2px solid var(--gray-200);
        white-space: nowrap;
    }

    .modern-table tbody tr {
        transition: background-color 0.15s;
        border-bottom: 1px solid var(--gray-200);
    }

    .modern-table tbody tr:hover {
        background-color: var(--gray-50);
    }

    .modern-table tbody td {
        padding: 0.875rem 1rem;
        color: var(--gray-700);
        font-size: 0.875rem;
        vertical-align: middle;
    }

    .vehicle-image-wrapper {
        width: 70px;
        height: 50px;
        border-radius: 6px;
        overflow: hidden;
        background-color: var(--gray-100);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--gray-200);
    }

    .vehicle-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .status-badge {
        padding: 0.375rem 0.875rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        display: inline-block;
    }

    .status-available {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }

    .status-sold {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger-color);
    }

    .action-buttons {
        display: flex;
        gap: 0.375rem;
    }

    .btn-action {
        padding: 0.375rem 0.75rem;
        border-radius: 5px;
        font-size: 0.8125rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        white-space: nowrap;
    }

    .btn-action-view {
        background-color: rgba(79, 70, 229, 0.1);
        color: var(--primary-color);
    }

    .btn-action-view:hover {
        background-color: rgba(79, 70, 229, 0.2);
        color: var(--primary-dark);
    }

    .btn-action-edit {
        background-color: rgba(245, 158, 11, 0.1);
        color: var(--warning-color);
    }

    .btn-action-edit:hover {
        background-color: rgba(245, 158, 11, 0.2);
        color: #d97706;
    }

    .btn-action-delete {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger-color);
    }

    .btn-action-delete:hover {
        background-color: rgba(239, 68, 68, 0.2);
        color: #dc2626;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .inventory-container {
            padding: 1rem;
        }
        
        .page-header h1 {
            font-size: 1.5rem;
        }
        
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .content-card-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="inventory-container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h1>
                <i class="bi bi-car-front-fill"></i>
                Vehicle Inventory
            </h1>
            <button type="button" class="btn-primary-custom" onclick="openAddVehicleModal()">
                <i class="bi bi-plus-lg"></i>
                Add Vehicle
            </button>
        </div>
    </div>

    <?php echo $message; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-label">Total Vehicles</div>
                    <h2 class="stat-value"><?php echo $total_vehicles; ?></h2>
                </div>
                <div class="stat-icon primary">
                    <i class="bi bi-car-front"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-label">Total Value</div>
                    <h2 class="stat-value">₦<?php echo number_format($total_value, 0); ?></h2>
                </div>
                <div class="stat-icon success">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-label">Available</div>
                    <h2 class="stat-value"><?php echo $available_count; ?></h2>
                </div>
                <div class="stat-icon warning">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-label">Sold</div>
                    <h2 class="stat-value"><?php echo $sold_count; ?></h2>
                </div>
                <div class="stat-icon danger">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="content-card">
        <div class="content-card-header">
            <h2 class="content-card-title">All Vehicles</h2>
            <div style="color: var(--gray-600); font-size: 0.875rem;">
                <i class="bi bi-funnel"></i> Showing <?php echo count($vehicles); ?> vehicles
            </div>
        </div>
        
        <div class="table-responsive">
            <?php if (count($vehicles) > 0): ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Vehicle</th>
                        <th>VIN</th>
                        <th>Color</th>
                        <th>Status</th>
                        <th>Purchase Price</th>
                        <th>Purchase Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><span style="color: var(--gray-600); font-weight: 600;">#<?php echo str_pad($vehicle['id'], 4, '0', STR_PAD_LEFT); ?></span></td>
                        <td>
                            <div class="vehicle-image-wrapper">
                                <?php if ($vehicle['image']): ?>
                                    <img src="<?php echo $vehicle['image']; ?>" alt="Vehicle">
                                <?php else: ?>
                                    <i class="bi bi-image" style="color: var(--gray-400); font-size: 1.5rem;"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--gray-900);"><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></div>
                            <div style="color: var(--gray-600); font-size: 0.875rem;"><?php echo $vehicle['year']; ?></div>
                        </td>
                        <td>
                            <code style="font-size: 0.75rem; background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: 4px;">
                                <?php echo $vehicle['vin'] ?: 'N/A'; ?>
                            </code>
                        </td>
                        <td><?php echo $vehicle['color'] ?: '-'; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($vehicle['status']); ?>">
                                <?php echo $vehicle['status']; ?>
                            </span>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: var(--gray-900);">
                                ₦<?php echo number_format($vehicle['purchase_price'], 2); ?>
                            </span>
                        </td>
                        <td>
                            <span style="color: var(--gray-600); font-size: 0.875rem;">
                                <?php echo date('M d, Y', strtotime($vehicle['purchase_date'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="vehicle_details.php?id=<?php echo $vehicle['id']; ?>" class="btn-action btn-action-view">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="edit_vehicle.php?id=<?php echo $vehicle['id']; ?>" class="btn-action btn-action-edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <button onclick="confirmDelete(<?php echo $vehicle['id']; ?>, '<?php echo addslashes($vehicle['make'] . ' ' . $vehicle['model']); ?>')" class="btn-action btn-action-delete">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem; color: var(--gray-500);">
                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">
                    <i class="bi bi-car-front"></i>
                </div>
                <h3 style="color: var(--gray-700); font-weight: 600; margin-bottom: 0.5rem;">No vehicles in inventory</h3>
                <p style="color: var(--gray-500);">Get started by adding your first vehicle to the inventory.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Add New Vehicle
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-building me-1"></i> Make *</label>
                            <input type="text" name="make" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-car-front me-1"></i> Model *</label>
                            <input type="text" name="model" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-calendar me-1"></i> Year *</label>
                            <input type="number" name="year" class="form-control" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-palette me-1"></i> Color</label>
                            <input type="text" name="color" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-upc me-1"></i> VIN</label>
                        <input type="text" name="vin" class="form-control" maxlength="17">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-currency-dollar me-1"></i> Purchase Price *</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-calendar-event me-1"></i> Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-image me-1"></i> Vehicle Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_vehicle" class="btn-primary-custom">
                        <i class="bi bi-check-lg me-1"></i> Save Vehicle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Are you sure you want to delete <strong id="vehicleNameToDelete"></strong>?</p>
                <p class="text-muted mt-2 mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="bi bi-trash me-1"></i> Delete Vehicle
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Bootstrap modal
function openAddVehicleModal() {
    const modalElement = document.getElementById('addVehicleModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}

// Confirm delete function
function confirmDelete(vehicleId, vehicleName) {
    const modalElement = document.getElementById('deleteConfirmModal');
    const vehicleNameElement = document.getElementById('vehicleNameToDelete');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    
    if (modalElement && vehicleNameElement && confirmBtn) {
        vehicleNameElement.textContent = vehicleName;
        confirmBtn.href = 'delete_vehicle.php?id=' + vehicleId;
        
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
}
</script>

<?php include './inc/footer.php'; ?>