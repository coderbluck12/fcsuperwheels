<?php
// Ensure path variable exists
if (!isset($path_to_root)) { $path_to_root = ""; }

// Ensure user variables exist to prevent "Undefined Variable" errors
$display_user = isset($the_user) ? $the_user : 'Guest';
$display_branch = isset($user_branch) ? $user_branch : '';
$display_post = isset($user_post) ? $user_post : '';
$display_role = isset($user_role) ? $user_role : 'User';

// Check if user is admin (case-insensitive)
$is_admin = (strtolower($display_role) === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FC Superwheels Admin Manager</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- FontAwesome -->
    <link href="<?php echo $path_to_root; ?>vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo $path_to_root; ?>vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo $path_to_root; ?>css/theme.css" rel="stylesheet">
    <link href="<?php echo $path_to_root; ?>css/navthing.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 65px;
            --primary-color: #2563eb;
            --primary-light: #eff6ff;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --border-color: #e5e7eb;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f9fafb;
        }
        
        /* Light Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background-color: #ffffff;
            border-right: 1px solid var(--border-color);
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
        }
        
        .sidebar-logo {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
            background: #ffffff;
        }
        
        .sidebar-logo img {
            max-width: 180px;
            height: auto;
        }
        
        .sidebar-nav {
            padding: 1.5rem 0;
        }
        
        .nav-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-gray);
            margin-top: 1rem;
        }
        
        .sidebar-nav-item {
            padding: 0.875rem 1.5rem;
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.875rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-weight: 500;
            font-size: 0.9375rem;
        }
        
        .sidebar-nav-item:hover {
            background-color: var(--primary-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }
        
        .sidebar-nav-item.active {
            background-color: var(--primary-light);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
            font-weight: 600;
        }
        
        .sidebar-nav-item i {
            width: 20px;
            text-align: center;
            font-size: 1.125rem;
            color: var(--text-gray);
        }
        
        .sidebar-nav-item:hover i,
        .sidebar-nav-item.active i {
            color: var(--primary-color);
        }
        
        /* Top Header */
        .top-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 999;
            transition: left 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-dark);
            padding: 0.5rem;
        }
        
        .mobile-menu-btn:hover {
            color: var(--primary-color);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-dark);
            font-size: 0.875rem;
            font-weight: 500;
            background: var(--primary-light);
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
        }
        
        .user-info i {
            color: var(--primary-color);
            font-size: 1.25rem;
        }
        
        .user-name {
            color: var(--text-dark);
            font-weight: 600;
        }
        
        .user-info .divider {
            color: var(--border-color);
        }
        
        /* Main Content Area */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s ease;
        }
        
        /* Scrollbar styling for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Mobile Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .top-header {
                left: 0;
            }
            
            .main-wrapper {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.5);
                z-index: 999;
                display: none;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        @media (max-width: 576px) {
            .top-header {
                padding: 0 1rem;
            }
            
            .user-info {
                font-size: 0.75rem;
                padding: 0.5rem 0.875rem;
                gap: 0.5rem;
            }
            
            .user-info .branch,
            .user-info .post {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <a href="<?php echo $path_to_root; ?>dashboard.php">
                <img src="../images/logo.png" alt="FC Superwheels" />
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section-title">Main Menu</div>
            
            <a href="<?php echo $path_to_root; ?>dashboard.php" class="sidebar-nav-item">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="<?php echo $path_to_root; ?>inventory.php" class="sidebar-nav-item">
                <i class="fas fa-car"></i>
                <span>Inventory Manager</span>
            </a>
            
            <a href="<?php echo $path_to_root; ?>reports.php" class="sidebar-nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
            
            <a href="<?php echo $path_to_root; ?>car_requests.php" class="sidebar-nav-item">
                <i class="fas fa-clipboard"></i>
                <span>Car Requests</span>
            </a>
            
            <div class="nav-section-title">Receipts</div>

            <a href="<?php echo $path_to_root; ?>invoice_manager.php" class="sidebar-nav-item">
                <i class="fas fa-file-text"></i>
                <span>Invoice Manager</span>
            </a>
            
            <a href="<?php echo $path_to_root; ?>newreceipt.php" class="sidebar-nav-item">
                <i class="fas fa-plus-circle"></i>
                <span>New Receipt</span>
            </a>
            
            <a href="<?php echo $path_to_root; ?>view_all_receipts.php" class="sidebar-nav-item">
                <i class="fas fa-file-text"></i>
                <span>View Receipts</span>
            </a>
            
            <a href="<?php echo $path_to_root; ?>edit_receipt_all.php" class="sidebar-nav-item">
                <i class="fas fa-edit"></i>
                <span>Edit Receipts</span>
            </a>

            <a href="<?php echo $path_to_root; ?>signature_manager.php" class="sidebar-nav-item">
                <i class="fas fa-arrow-up"></i>
                <span>Signature Manager</span>
            </a>
            
            <?php if($is_admin): ?>
            <div class="nav-section-title">Administration</div>
            
            <a href="<?php echo $path_to_root; ?>add_staff.php" class="sidebar-nav-item">
                <i class="fas fa-user-plus"></i>
                <span>Add Staff</span>
            </a>
            <a href="<?php echo $path_to_root; ?>access_log.php" class="sidebar-nav-item">
                <i class="fas fa-history"></i>
                <span>Access Log</span>
            </a>
            
            <a href="<?php echo $path_to_root; ?>staffs.php" class="sidebar-nav-item">
                <i class="fas fa-users"></i>
                <span>Manage Staff</span>
            </a>
            
            <a href="<?php echo $path_to_root; ?>send_email.php" class="sidebar-nav-item">
                <i class="fas fa-envelope"></i>
                <span>Send Email</span>
            </a>
            <?php endif; ?>
            
            <div style="border-top: 1px solid var(--border-color); margin: 1rem 0;"></div>
            
            <a href="<?php echo $path_to_root; ?>logout.php" class="sidebar-nav-item" style="color: #dc2626;">
                <i class="fas fa-sign-out-alt" style="color: #dc2626;"></i>
                <span>Log Out</span>
            </a>
        </nav>
    </aside>
    
    <!-- Top Header -->
    <header class="top-header">
        <button class="mobile-menu-btn" id="mobileMenuBtn">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span class="user-name"><?php echo $display_user; ?></span>
            <?php if($display_branch): ?>
                <span class="divider">|</span>
                <span class="branch"><?php echo $display_branch; ?></span>
            <?php endif; ?>
            <?php if($display_post): ?>
                <span class="divider">|</span>
                <span class="post"><?php echo $display_post; ?></span>
            <?php endif; ?>
        </div>
    </header>
    
    <!-- Main Content Wrapper -->
    <div class="main-wrapper" />

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            }

            if(mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
            }
            
            if(sidebarOverlay) {
                sidebarOverlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>
</body>
</html>