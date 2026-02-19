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

// Determine display price: listing_price preferred, fallback to purchase_price
$display_price = !empty($vehicle['listing_price']) ? $vehicle['listing_price'] : ($vehicle['purchase_price'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?> | Firstchoice Superwheels</title>
    <meta name="description" content="<?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model'] . ' available at Firstchoice Superwheels.'); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1e3c72;
            --primary-mid: #2a5298;
            --primary-light: #4a6fa5;
            --accent: #f59e0b;
            --success: #059669;
            --danger: #dc2626;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-900: #111827;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--gray-50);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--gray-700);
        }

        /* ── Navbar ── */
        .site-nav {
            background: white;
            border-bottom: 3px solid var(--primary);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        .site-nav .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-logo img {
            height: 52px;
            width: auto;
            object-fit: contain;
        }

        .nav-links { display: flex; align-items: center; gap: 1.5rem; }

        .nav-link-item {
            color: var(--gray-600);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-link-item:hover { color: var(--primary); }

        .btn-nav-back {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: var(--gray-100);
            color: var(--gray-700);
            border: 1.5px solid var(--gray-200);
            padding: 0.5rem 1rem;
            border-radius: 7px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-nav-back:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* ── Breadcrumb ── */
        .breadcrumb-bar {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            padding: 0.625rem 0;
        }

        .breadcrumb-bar .breadcrumb {
            margin: 0;
            font-size: 0.8125rem;
        }

        .breadcrumb-bar .breadcrumb-item a {
            color: var(--primary-mid);
            text-decoration: none;
        }

        .breadcrumb-bar .breadcrumb-item.active { color: var(--gray-500); }

        /* ── Page Layout ── */
        .vehicle-page { padding: 2.5rem 0; flex: 1; }

        /* ── Image Hero ── */
        .image-hero {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            background: var(--gray-100);
            aspect-ratio: 16/10;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }

        .image-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .image-hero:hover img { transform: scale(1.02); }

        .image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--gray-300);
            font-size: 5rem;
            min-height: 300px;
        }

        .image-placeholder p {
            font-size: 0.9rem;
            color: var(--gray-500);
            margin-top: 0.75rem;
            font-size: 0.875rem;
        }

        /* Status floating badge */
        .status-pill {
            position: absolute;
            top: 16px;
            right: 16px;
            padding: 0.375rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            backdrop-filter: blur(8px);
        }

        .pill-available { background: rgba(209,250,229,0.95); color: #065f46; border: 1.5px solid #10b981; }
        .pill-sold      { background: rgba(254,226,226,0.95); color: #991b1b; border: 1.5px solid #ef4444; }
        .pill-reserved  { background: rgba(254,243,199,0.95); color: #92400e; border: 1.5px solid #f59e0b; }

        /* Thumbnail strip (shows same image repeated for demo feel) */
        .thumb-strip {
            display: flex;
            gap: 0.625rem;
            margin-top: 0.875rem;
        }

        .thumb-item {
            width: 72px;
            height: 52px;
            border-radius: 7px;
            overflow: hidden;
            border: 2px solid transparent;
            cursor: pointer;
            transition: border-color 0.2s;
            flex-shrink: 0;
        }

        .thumb-item.active { border-color: var(--primary); }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }

        /* ── Info Panel ── */
        .info-panel { padding-left: 1.5rem; }

        .vehicle-make-model {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-mid);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.375rem;
        }

        .vehicle-heading {
            font-size: 2.125rem;
            font-weight: 800;
            color: var(--gray-900);
            line-height: 1.15;
            margin-bottom: 0.75rem;
        }

        /* Price card */
        .price-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 100%);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .price-label-text {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            opacity: 0.8;
            margin-bottom: 0.25rem;
        }

        .price-amount {
            font-size: 2.25rem;
            font-weight: 800;
            color: white;
            line-height: 1;
        }

        .price-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Specs grid */
        .specs-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .spec-tile {
            background: white;
            border: 1.5px solid var(--gray-200);
            border-radius: 10px;
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .spec-tile:hover {
            border-color: var(--primary-light);
            box-shadow: 0 2px 8px rgba(30,60,114,0.08);
        }

        .spec-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .spec-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-500);
            margin-bottom: 0.1rem;
        }

        .spec-value {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.2;
        }

        /* CTA buttons */
        .cta-group {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .btn-cta-primary {
            flex: 1;
            min-width: 140px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 100%);
            color: white;
            border: none;
            padding: 0.875rem 1.5rem;
            border-radius: 9px;
            font-size: 0.9375rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cta-primary:hover {
            filter: brightness(1.12);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30,60,114,0.3);
            color: white;
        }

        .btn-cta-secondary {
            flex: 1;
            min-width: 140px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 0.875rem 1.5rem;
            border-radius: 9px;
            font-size: 0.9375rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-cta-secondary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }

        /* Trust chips */
        .trust-chips {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .trust-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: var(--gray-100);
            color: var(--gray-600);
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .trust-chip i { color: var(--success); }

        /* ── Sidebar Card ── */
        .sidebar-card {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            position: sticky;
            top: 100px;
        }

        .sidebar-card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-mid) 100%);
            color: white;
            padding: 1.25rem 1.5rem;
        }

        .sidebar-card-header h3 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-list { padding: 0 1.5rem; }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.875rem 0;
            border-bottom: 1px solid var(--gray-100);
            gap: 1rem;
        }

        .info-row:last-child { border-bottom: none; }

        .info-row-label {
            font-size: 0.8125rem;
            color: var(--gray-500);
            font-weight: 500;
        }

        .info-row-value {
            font-size: 0.875rem;
            color: var(--gray-900);
            font-weight: 600;
            text-align: right;
        }

        /* Status badge inline */
        .status-inline {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-inline-available { background: #d1fae5; color: #065f46; }
        .status-inline-sold      { background: #fee2e2; color: #991b1b; }
        .status-inline-reserved  { background: #fef3c7; color: #92400e; }

        /* Contact box inside sidebar */
        .contact-box {
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
            padding: 1.25rem 1.5rem;
        }

        .contact-box p {
            font-size: 0.8125rem;
            color: var(--gray-600);
            margin-bottom: 0.875rem;
            line-height: 1.5;
        }

        .contact-phone {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.0625rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            margin-bottom: 0.5rem;
        }

        .contact-phone:hover { color: var(--primary-mid); }

        /* ── Sold overlay note ── */
        .sold-banner {
            background: linear-gradient(90deg, #fee2e2 0%, #fef2f2 100%);
            border: 1.5px solid #fca5a5;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #991b1b;
            font-weight: 600;
        }

        /* ── Footer ── */
        footer {
            background: var(--primary);
            color: rgba(255,255,255,0.75);
            padding: 2rem 0;
            margin-top: auto;
        }

        footer p { font-size: 0.875rem; margin: 0; }
        footer a { color: rgba(255,255,255,0.9); text-decoration: none; }
        footer a:hover { color: white; }

        /* ── Responsive ── */
        @media (max-width: 992px) {
            .info-panel { padding-left: 0; margin-top: 1.5rem; }
            .vehicle-heading { font-size: 1.75rem; }
            .sidebar-card { position: static; margin-top: 1.5rem; }
        }

        @media (max-width: 576px) {
            .vehicle-page { padding: 1.5rem 0; }
            .specs-grid { grid-template-columns: 1fr 1fr; }
            .price-amount { font-size: 1.75rem; }
            .cta-group { flex-direction: column; }
            .btn-cta-primary, .btn-cta-secondary { min-width: 100%; flex: none; }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="site-nav">
        <div class="container">
            <a class="nav-logo" href="index.php">
                <img src="./images/logo.png" alt="Firstchoice Superwheels">
            </a>
            <div class="nav-links">
                <a href="index.php" class="nav-link-item">Home</a>
                <a href="inventory.php" class="nav-link-item">Inventory</a>
                <a href="inventory.php" class="btn-nav-back">
                    <i class="bi bi-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="inventory.php">Inventory</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Vehicle Details -->
    <section class="vehicle-page">
        <div class="container">
            <div class="row">

                <!-- LEFT: Image + Info -->
                <div class="col-lg-8">

                    <!-- ── Image Hero ── -->
                    <div class="image-hero">
                        <?php
                            $img_src = '';
                            if (!empty($vehicle['image'])) {
                                $paths = [
                                    $vehicle['image'],
                                    'admin/' . $vehicle['image'],
                                    '../' . $vehicle['image'],
                                ];
                                foreach ($paths as $p) {
                                    if (file_exists($p)) { $img_src = $p; break; }
                                }
                            }
                        ?>
                        <?php if ($img_src): ?>
                            <img src="<?php echo htmlspecialchars($img_src); ?>"
                                 alt="<?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?>">
                        <?php else: ?>
                            <div class="image-placeholder">
                                <i class="bi bi-car-front"></i>
                                <p>No photo available</p>
                            </div>
                        <?php endif; ?>

                        <?php
                            $sc = 'pill-available';
                            if ($vehicle['status'] == 'Sold')     $sc = 'pill-sold';
                            elseif ($vehicle['status'] == 'Reserved') $sc = 'pill-reserved';
                        ?>
                        <span class="status-pill <?php echo $sc; ?>">
                            <i class="bi bi-<?php echo $vehicle['status'] == 'Available' ? 'check-circle' : ($vehicle['status'] == 'Sold' ? 'x-circle' : 'clock'); ?>"></i>
                            <?php echo htmlspecialchars($vehicle['status']); ?>
                        </span>
                    </div>

                    <!-- ── Info Panel ── -->
                    <div class="info-panel mt-4">

                        <?php if ($vehicle['status'] == 'Sold'): ?>
                        <div class="sold-banner">
                            <i class="bi bi-exclamation-circle-fill" style="font-size:1.25rem;"></i>
                            This vehicle has been sold. Contact us to check for similar vehicles.
                        </div>
                        <?php endif; ?>

                        <div class="vehicle-make-model">
                            <?php echo htmlspecialchars($vehicle['make']); ?> &bull; <?php echo htmlspecialchars($vehicle['year']); ?>
                        </div>

                        <h1 class="vehicle-heading">
                            <?php echo htmlspecialchars($vehicle['year'] . ' ' . $vehicle['make'] . ' ' . $vehicle['model']); ?>
                        </h1>

                        <!-- Price Card -->
                        <div class="price-card">
                            <div>
                                <div class="price-label-text">Listing Price</div>
                                <div class="price-amount">
                                    ₦<?php echo number_format($display_price, 2); ?>
                                </div>
                            </div>
                            <div class="price-icon">
                                <i class="bi bi-currency-exchange"></i>
                            </div>
                        </div>

                        <!-- Specs Grid -->
                        <div class="specs-grid">
                            <div class="spec-tile">
                                <div class="spec-icon"><i class="bi bi-calendar-check"></i></div>
                                <div>
                                    <div class="spec-label">Year</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($vehicle['year']); ?></div>
                                </div>
                            </div>

                            <div class="spec-tile">
                                <div class="spec-icon"><i class="bi bi-palette-fill"></i></div>
                                <div>
                                    <div class="spec-label">Color</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($vehicle['color'] ?: 'N/A'); ?></div>
                                </div>
                            </div>

                            <div class="spec-tile">
                                <div class="spec-icon"><i class="bi bi-person-fill"></i></div>
                                <div>
                                    <div class="spec-label">Make</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($vehicle['make']); ?></div>
                                </div>
                            </div>

                            <div class="spec-tile">
                                <div class="spec-icon"><i class="bi bi-car-front-fill"></i></div>
                                <div>
                                    <div class="spec-label">Model</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($vehicle['model']); ?></div>
                                </div>
                            </div>

                            <?php if (!empty($vehicle['vin'])): ?>
                            <div class="spec-tile" style="grid-column: 1 / -1;">
                                <div class="spec-icon"><i class="bi bi-upc-scan"></i></div>
                                <div>
                                    <div class="spec-label">Chassis Number</div>
                                    <div class="spec-value" style="font-family: monospace; font-size: 0.875rem;"><?php echo htmlspecialchars($vehicle['vin']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- CTA Buttons -->
                        <?php if ($vehicle['status'] !== 'Sold'): ?>
                        <div class="cta-group">
                            <a href="index.php#request" class="btn-cta-primary">
                                <i class="bi bi-telephone-fill"></i> Request This Car
                            </a>
                            <a href="index.php#contact" class="btn-cta-secondary">
                                <i class="bi bi-chat-dots"></i> Contact Us
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="cta-group">
                            <a href="inventory.php" class="btn-cta-primary">
                                <i class="bi bi-search"></i> Browse Similar Cars
                            </a>
                        </div>
                        <?php endif; ?>

                        <!-- Trust Chips -->
                        <div class="trust-chips">
                            <div class="trust-chip"><i class="bi bi-shield-check"></i> Quality Verified</div>
                            <div class="trust-chip"><i class="bi bi-award"></i> Trusted Dealer</div>
                            <div class="trust-chip"><i class="bi bi-headset"></i> After-sale Support</div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT: Sidebar -->
                <div class="col-lg-4 mt-4 mt-lg-0">
                    <div class="sidebar-card">
                        <div class="sidebar-card-header">
                            <h3><i class="bi bi-info-circle"></i> Vehicle Details</h3>
                        </div>

                        <div class="info-list">
                            <div class="info-row">
                                <span class="info-row-label">Make</span>
                                <span class="info-row-value"><?php echo htmlspecialchars($vehicle['make']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-row-label">Model</span>
                                <span class="info-row-value"><?php echo htmlspecialchars($vehicle['model']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-row-label">Year</span>
                                <span class="info-row-value"><?php echo htmlspecialchars($vehicle['year']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-row-label">Color</span>
                                <span class="info-row-value"><?php echo htmlspecialchars($vehicle['color'] ?: 'Not Specified'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-row-label">Listing Price</span>
                                <span class="info-row-value" style="color: var(--primary); font-size: 1rem;">₦<?php echo number_format($display_price, 2); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-row-label">Listed On</span>
                                <span class="info-row-value"><?php echo date('M d, Y', strtotime($vehicle['purchase_date'])); ?></span>
                            </div>
                            <?php if (!empty($vehicle['sale_date'])): ?>
                            <div class="info-row">
                                <span class="info-row-label">Sale Date</span>
                                <span class="info-row-value"><?php echo date('M d, Y', strtotime($vehicle['sale_date'])); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-row">
                                <span class="info-row-label">Status</span>
                                <span class="info-row-value">
                                    <?php
                                        $sc2 = 'status-inline-available';
                                        $ic2 = 'check-circle-fill';
                                        if ($vehicle['status'] == 'Sold')     { $sc2 = 'status-inline-sold';     $ic2 = 'x-circle-fill'; }
                                        elseif ($vehicle['status'] == 'Reserved') { $sc2 = 'status-inline-reserved'; $ic2 = 'clock-fill'; }
                                    ?>
                                    <span class="status-inline <?php echo $sc2; ?>">
                                        <i class="bi bi-<?php echo $ic2; ?>"></i>
                                        <?php echo htmlspecialchars($vehicle['status']); ?>
                                    </span>
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-row-label">Vehicle ID</span>
                                <span class="info-row-value" style="font-family: monospace;">#<?php echo str_pad($vehicle['id'], 4, '0', STR_PAD_LEFT); ?></span>
                            </div>
                        </div>

                        <div class="contact-box">
                            <p>Interested in this vehicle? Reach us directly and we'll get back to you promptly.</p>
                            <a href="tel:+2347016754887" class="contact-phone">
                                <i class="bi bi-telephone-fill"></i> +234 701 675 4887
                            </a>
                            <a href="index.php#contact" class="btn-cta-primary" style="padding: 0.75rem 1rem; font-size: 0.875rem;">
                                <i class="bi bi-envelope"></i> Send Enquiry
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <p>&copy; <?php echo date('Y'); ?> Firstchoice Superwheels. All rights reserved.</p>
                <div style="display: flex; gap: 1.5rem;">
                    <a href="index.php">Home</a>
                    <a href="inventory.php">Inventory</a>
                    <a href="index.php#contact">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>