<?php
require_once 'inc/session_manager.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: inventory.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
$stmt->execute([$id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    header('Location: inventory.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_vehicle'])) {
    $make = validate_input($_POST['make']);
    $model = validate_input($_POST['model']);
    $year = (int)$_POST['year'];
    $vin = validate_input($_POST['vin']);
    $color = validate_input($_POST['color']);
    $status = validate_input($_POST['status']);
    $purchase_price = (float)$_POST['purchase_price'];
    $listing_price = (float)$_POST['listing_price'];
    $sale_price = !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $purchase_date = $_POST['purchase_date'];
    $sale_date = !empty($_POST['sale_date']) ? $_POST['sale_date'] : null;

    try {
        $stmt = $pdo->prepare("UPDATE vehicles SET make = ?, model = ?, year = ?, vin = ?, color = ?, status = ?, purchase_price = ?, listing_price = ?, sale_price = ?, purchase_date = ?, sale_date = ? WHERE id = ?");
        $stmt->execute([$make, $model, $year, $vin, $color, $status, $purchase_price, $listing_price, $sale_price, $purchase_date, $sale_date, $id]);
        $message = '<div class="alert alert-success alert-dismissible fade show">Vehicle updated successfully! <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        
        // Refresh vehicle data
        $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        $vehicle = $stmt->fetch();
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger alert-dismissible fade show">Error: ' . $e->getMessage() . ' <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
}

$path_to_root = './';
include 'inc/header.php';
?>

<style>
    .edit-wrapper {
        padding: 2.5rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 3px solid #2563eb;
    }

    .edit-title h1 {
        font-size: 2rem;
        font-weight: 800;
        color: #111827;
        margin: 0 0 0.5rem 0;
    }

    .vehicle-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
    }

    .back-link {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        border: 2px solid #2563eb;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .back-link:hover {
        background: #2563eb;
        color: white;
    }

    .edit-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 2rem;
    }

    .form-section-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: #111827;
        margin: 1.5rem 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .form-section-title:first-child {
        margin-top: 0;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-control, .form-select {
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0.75rem;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .form-control:focus, .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .btn-update {
        background: #2563eb;
        color: white;
        border: none;
        padding: 0.875rem 2rem;
        border-radius: 6px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-update:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
    }

    @media (max-width: 768px) {
        .edit-wrapper {
            padding: 1.5rem;
        }

        .top-bar {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
    }
</style>

<div class="edit-wrapper">
    <div class="top-bar">
        <div class="edit-title">
            <h1>Edit Vehicle</h1>
            <div class="vehicle-subtitle">
                <?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'); ?>
            </div>
        </div>
        <a href="inventory.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Back to Inventory
        </a>
    </div>

    <?php echo $message; ?>

    <div class="edit-card">
        <form method="POST">
            <div class="form-section-title">Vehicle Information</div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Make *</label>
                    <input type="text" name="make" class="form-control" value="<?php echo htmlspecialchars($vehicle['make']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Model *</label>
                    <input type="text" name="model" class="form-control" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Year *</label>
                    <input type="number" name="year" class="form-control" value="<?php echo $vehicle['year']; ?>" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">VIN</label>
                    <input type="text" name="vin" class="form-control" value="<?php echo htmlspecialchars($vehicle['vin']); ?>" maxlength="17">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Color</label>
                    <input type="text" name="color" class="form-control" value="<?php echo htmlspecialchars($vehicle['color']); ?>">
                </div>
            </div>

            <div class="form-section-title">Status & Pricing</div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="Available" <?php echo $vehicle['status'] == 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Sold" <?php echo $vehicle['status'] == 'Sold' ? 'selected' : ''; ?>>Sold</option>
                        <option value="Reserved" <?php echo $vehicle['status'] == 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Cost Price *</label>
                    <input type="number" step="0.01" name="purchase_price" class="form-control" value="<?php echo $vehicle['purchase_price']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Listing Price *</label>
                    <input type="number" step="0.01" name="listing_price" class="form-control" value="<?php echo $vehicle['listing_price']; ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sale Price</label>
                    <input type="number" step="0.01" name="sale_price" class="form-control" value="<?php echo $vehicle['sale_price']; ?>" placeholder="Optional">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Purchase Date</label>
                    <input type="date" name="purchase_date" class="form-control" value="<?php echo $vehicle['purchase_date']; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sale Date</label>
                    <input type="date" name="sale_date" class="form-control" value="<?php echo $vehicle['sale_date']; ?>" placeholder="Optional">
                </div>
            </div>

            <div class="mt-4 pt-3 border-top">
                <button type="submit" name="update_vehicle" class="btn-update">
                    <i class="bi bi-check-lg me-2"></i>Update Vehicle
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'inc/footer.php'; ?>