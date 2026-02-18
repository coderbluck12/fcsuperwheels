<?php
// 1. ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. INCLUDE DATABASE CONNECTION
if (file_exists('admin/inc/functions.php')) {
    require_once('admin/inc/functions.php');
} elseif (file_exists('inc/functions.php')) {
    require_once('inc/functions.php');
} else {
    // Fallback: Manual Connection if file not found
    try {
        $db_host = 'localhost';
        $db_user = 'tertgxyp_seyi';
        $db_password = 'Fcnest001@';
        $database = 'tertgxyp_fcsuperwheels';
        $pdo = new PDO("mysql:host=$db_host;dbname=$database;charset=utf8", $db_user, $db_password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database Connection Failed: " . $e->getMessage());
    }
}

// 3. GET VEHICLE ID FROM URL
$vehicle_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 4. FETCH VEHICLE DETAILS
try {
    $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicle_id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$vehicle) {
        header("Location: index.php");
        exit;
    }
} catch (Exception $e) {
    die("Error fetching vehicle: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']; ?> | Firstchoice Superwheels</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --success-color: #10b981;
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body { 
            background-color: var(--gray-50);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navigation */
        .navbar {
            background-color: white !important;
            border-bottom: 2px solid var(--primary-color);
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.025em;
            color: var(--gray-900) !important;
        }

        .navbar-brand i {
            color: var(--primary-color);
        }

        .back-btn {
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.2s;
        }

        .back-btn:hover {
            color: var(--primary-color);
        }

        /* Main Content */
        .vehicle-details-section {
            padding: 3rem 0;
            flex: 1;
        }

        .detail-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Image Section */
        .vehicle-image-section {
            background-color: var(--gray-100);
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .vehicle-detail-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vehicle-img-placeholder {
            font-size: 8rem;
            color: var(--gray-400);
        }

        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 0.875rem;
            text-transform: uppercase;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 25px;
            letter-spacing: 0.025em;
            backdrop-filter: blur(10px);
        }

        .status-available {
            background-color: rgba(209, 250, 229, 0.95);
            color: #065f46;
            border: 1px solid #10b981;
        }

        .status-sold {
            background-color: rgba(254, 226, 226, 0.95);
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .status-pending {
            background-color: rgba(254, 243, 199, 0.95);
            color: #92400e;
            border: 1px solid #f59e0b;
        }

        /* Info Section */
        .vehicle-info-section {
            padding: 2rem;
        }

        .vehicle-title-section {
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }

        .vehicle-main-title {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .vehicle-subtitle {
            font-size: 1.25rem;
            color: var(--gray-600);
            margin-bottom: 1rem;
        }

        .price-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .price-label {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-600);
        }

        .price-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* Details Grid */
        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background-color: var(--gray-50);
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-600);
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-900);
        }

        /* Additional Info Section */
        .additional-info {
            background-color: var(--gray-50);
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .info-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--gray-600);
            font-size: 0.9375rem;
        }

        .info-value {
            color: var(--gray-900);
            font-weight: 500;
            font-size: 0.9375rem;
        }

        /* Action Buttons */
        .action-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid var(--gray-200);
            display: flex;
            gap: 1rem;
        }

        .btn-primary-action {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-action:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-secondary-action {
            background-color: white;
            color: var(--gray-700);
            border: 2px solid var(--gray-300);
            padding: 0.875rem 2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary-action:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* Footer */
        footer {
            background-color: var(--gray-100);
            color: var(--gray-600);
            padding: 2rem 0;
            margin-top: auto;
            border-top: 1px solid var(--gray-200);
        }

        footer p {
            margin: 0;
            font-size: 0.875rem;
        }
        .sidebar-logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="sidebar-logo" href="index.php">
                <img src="./images/logo.png" alt="FC Superwheels" />
            </a>
            <div class="ms-auto">
                <a href="inventory.php" class="back-btn">
                    <i class="bi bi-arrow-left"></i>
                    Back to Inventory
                </a>
            </div>
        </div>
    </nav>

    <!-- Vehicle Details Section -->
    <div class="vehicle-details-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="detail-card">
                        <!-- Vehicle Image -->
                        <div class="vehicle-image-section">
                            <?php if (!empty($vehicle['image'])): ?>
                                <img src="<?php echo (file_exists('admin/'.$vehicle['image']) ? 'admin/' : '') . $vehicle['image']; ?>" class="vehicle-detail-img" alt="Vehicle">
                            <?php else: ?>
                                <i class="bi bi-car-front vehicle-img-placeholder"></i>
                            <?php endif; ?>
                            
                            <?php 
                                $statusClass = 'status-available';
                                if ($vehicle['status'] == 'Sold') $statusClass = 'status-sold';
                                elseif ($vehicle['status'] == 'Pending') $statusClass = 'status-pending';
                            ?>
                            <div class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $vehicle['status']; ?>
                            </div>
                        </div>

                        <!-- Vehicle Info -->
                        <div class="vehicle-info-section">
                            <div class="vehicle-title-section">
                                <h1 class="vehicle-main-title">
                                    <?php echo $vehicle['year'] . ' ' . $vehicle['make']; ?>
                                </h1>
                                <p class="vehicle-subtitle"><?php echo $vehicle['model']; ?></p>
                                
                                <div class="price-section">
                                    <div>
                                        <span class="price-label">Purchase Price</span>
                                        <div class="price-value">₦<?php echo number_format($vehicle['purchase_price'], 2); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Vehicle Details Grid -->
                            <div class="details-grid">
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="bi bi-palette-fill"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Color</div>
                                        <div class="detail-value"><?php echo $vehicle['color'] ?: 'Not Specified'; ?></div>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="bi bi-speedometer2"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">VIN Number</div>
                                        <div class="detail-value"><?php echo $vehicle['vin'] ?: 'Not Available'; ?></div>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="bi bi-calendar-check"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Year</div>
                                        <div class="detail-value"><?php echo $vehicle['year']; ?></div>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="bi bi-tag-fill"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Vehicle ID</div>
                                        <div class="detail-value">#<?php echo str_pad($vehicle['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="action-section">
                                <a class="btn-primary-action" href="index.php#request">
                                    Request Car
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="detail-card">
                        <div class="additional-info">
                            <h3 class="info-title">Vehicle Information</h3>
                            
                            <div class="info-item">
                                <span class="info-label">Make</span>
                                <span class="info-value"><?php echo $vehicle['make']; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Model</span>
                                <span class="info-value"><?php echo $vehicle['model']; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Year</span>
                                <span class="info-value"><?php echo $vehicle['year']; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Color</span>
                                <span class="info-value"><?php echo $vehicle['color'] ?: 'N/A'; ?></span>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Purchase Date</span>
                                <span class="info-value"><?php echo date('F d, Y', strtotime($vehicle['purchase_date'])); ?></span>
                            </div>
                            
                            <?php if (!empty($vehicle['sale_date'])): ?>
                            <div class="info-item">
                                <span class="info-label">Sale Date</span>
                                <span class="info-value"><?php echo date('F d, Y', strtotime($vehicle['sale_date'])); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value"><?php echo $vehicle['status']; ?></span>
                            </div>
                            
                            <?php if (!empty($vehicle['notes'])): ?>
                            <div class="info-item" style="flex-direction: column; align-items: flex-start;">
                                <span class="info-label mb-2">Notes</span>
                                <span class="info-value"><?php echo nl2br(htmlspecialchars($vehicle['notes'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Firstchoice Superwheels. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function contactUs() {
            alert('Contact Us Feature\n\nPlease call or email us to inquire about this vehicle.\n\nThis would typically open a contact form or display contact information.');
        }
    </script>
</body>
</html>