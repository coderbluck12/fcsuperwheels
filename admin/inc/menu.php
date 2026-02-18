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

<header class="header-mobile d-block d-lg-none">
    <div class="header-mobile__bar">
        <div class="container-fluid">
            <div class="header-mobile-inner">
                <a class="logo" href="<?php echo $path_to_root; ?>index.php">
                    <img src="<?php echo $path_to_root; ?>images/logo.png" alt="Superwheels" />
                </a>
                <button class="hamburger hamburger--slider" type="button">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <nav class="navbar-mobile">
        <div class="container-fluid">
            <ul class="navbar-mobile__list list-unstyled">
                <li>
                    <a href="<?php echo $path_to_root; ?>index.php">
                        <i class="fas fa-chart-bar"></i>Dashboard</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>inventory.php">
                        <i class="fas fa-car"></i>Inventory Manager</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>tickets.php">
                        <i class="fas fa-table"></i>My Tickets</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>new_ticket.php">
                        <i class="far fa-check-square"></i>Open New Ticket</a>
                </li>
                <?php if($is_admin): ?>
                <li>
                    <a href="<?php echo $path_to_root; ?>add_staff.php">
                        <i class="fas fa-calendar-alt"></i>Add staff</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>staffs.php">
                        <i class="fas fa-users"></i>Sumaco staffs</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>send_email.php">
                        <i class="fas fa-envelope"></i>Send email</a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="<?php echo $path_to_root; ?>logout.php">
                        <i class="fas fa-power-off"></i>Log out</a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<aside class="menu-sidebar d-none d-lg-block">
    <div class="logo">
        <a href="<?php echo $path_to_root; ?>index.php">
            <img src="<?php echo $path_to_root; ?>images/logo.png" alt="Cool Admin" />
        </a>
    </div>
    <div class="menu-sidebar__content js-scrollbar1">
        <nav class="navbar-sidebar">
            <ul class="list-unstyled navbar__list">
                <li>
                    <a href="<?php echo $path_to_root; ?>index.php">
                        <i class="fas fa-chart-bar"></i>Dashboard</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>inventory.php">
                        <i class="fas fa-car"></i>Inventory Manager</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>tickets.php">
                        <i class="fas fa-table"></i>My Tickets</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>new_ticket.php">
                        <i class="far fa-check-square"></i>Open New Ticket</a>
                </li>
                <?php if($is_admin): ?>
                <li>
                    <a href="<?php echo $path_to_root; ?>add_staff.php">
                        <i class="fas fa-calendar-alt"></i>Add staff</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>staffs.php">
                        <i class="fas fa-users"></i>Sumaco staffs</a>
                </li>
                <li>
                    <a href="<?php echo $path_to_root; ?>send_email.php">
                        <i class="fas fa-envelope"></i>Send email</a>
                </li>
                <?php endif; ?>
                <li>
                    <a href="<?php echo $path_to_root; ?>logout.php">
                        <i class="fas fa-power-off"></i>Log out</a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
<div class="page-container">
    <header class="header-desktop">
        <div class="section__content section__content--p30">
            <div class="container-fluid">
                <div class="header-wrap">
                    <div class="header-button">
                        <div class="account-wrap">
                            <div class="account-item clearfix js-item-menu">
                                <div class="content" style="color:#333;">
                                    <?php echo $display_user; ?> | <?php echo $display_branch; ?> | <?php echo $display_post; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>