<?php
include_once('inc/session_manager.php');

// Fetching all receipts with pagination
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$query_invoices_all = "SELECT * FROM `main_receipt` WHERE `visibility` = 'yes' ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt_invoices_all = $pdo->prepare($query_invoices_all);
$stmt_invoices_all->execute();
$invoices_all = $stmt_invoices_all->fetchAll();
$num_invoices_all = $stmt_invoices_all->rowCount();

// Pagination logic
$total_query = "SELECT COUNT(*) FROM `main_receipt` WHERE `visibility` = 'yes'";
$total_result = $pdo->query($total_query);
$total_rows = $total_result->fetchColumn();
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>FC SUPERWHEELS ADMIN MANAGER</title>

    <!-- Bootstrap CSS-->
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
    <link href="css/theme.css" rel="stylesheet" media="all">
    <link href="css/navthing.css" rel="stylesheet" media="all">
    
    <style>
        /* Custom CSS for styling */
        .announcement {
            background-color: #38880B;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .stat-number {
            font-size: 48px;
            color: white;
        }
    </style>
</head>

<body class="animsition">
    <div class="page-wrapper">
        <?php include_once('inc/menu.php'); ?>
        
        <div class="page-content--bgf7">
            <!-- BREADCRUMB-->
            <section class="au-breadcrumb2">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="au-breadcrumb-content">
                                <div class="au-breadcrumb-left">
                                    <span class="au-breadcrumb-span">You are here:</span>
                                    <ul class="list-unstyled list-inline au-breadcrumb__list">
                                        <li class="list-inline-item active">
                                            <a href="dashboard.php">Home</a>
                                        </li>
                                        <li class="list-inline-item seprate">
                                            <span>/</span>
                                        </li>
                                        <li class="list-inline-item">Admin Dashboard</li>
                                    </ul>
                                </div>
                                <?php include_once('inc/invoice_search.php'); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <?php if (isset($comment)) { echo '<br />' . $comment; } ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Announcement Section -->
            <div class="container mt-4">
                <div class="announcement">
                    <p>This system helps you manage receipts efficiently, keeping track of transactions.</p>
                </div>

                <!-- Receipt List with Pagination -->
                <div class="container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices_all as $invoice) { ?>
                            <tr>
                                <td><?php echo $invoice['id']; ?></td>
                                <td><?php echo $invoice['customer_name']; ?></td>
                                <td><?php echo $invoice['amount']; ?></td>
                                <td><?php echo $invoice['payment_type']; ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <li class="page-item <?php if ($page == 1) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                            <li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php } ?>
                            <li class="page-item <?php if ($page == $total_pages) echo 'disabled'; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div>
            <?php include_once('inc/footer.php'); ?>
        </div>
    </div>

    <!-- Jquery JS-->
    <script src="vendor/jquery-3.2.1.min.js"></script>
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>

    <!-- Main JS-->
    <script src="js/main.js"></script>
</body>

</html>
