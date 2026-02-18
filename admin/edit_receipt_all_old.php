<?php
include_once('inc/session_manager.php');
if(isset($_GET['delete-success']))
{
	 $comment = '<a style="color:green;text-align:center;"><b>Receipt deleted successfully</b></a>';
}
if(isset($_GET['edit-success']))
{
	 $comment = '<a style="color:green;text-align:center;"><b>Receipt edited successfully</b></a>';
}
if(isset($_GET['delete-fail']))
{
	 $comment = '<a style="color:red;text-align:center;"><b>Failure to delete</b></a>';
}
if(isset($_GET['edit-fail']))
{
	 $comment = '<a style="color:red;text-align:center;"><b>Failure to edit</b></a>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keywords" content="">

    <!-- Title Page-->
    <title>EDIT RECEIPTS</title>

    <!-- Fontfaces CSS-->
    <link href="css/font-face.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">

    <!-- Bootstrap CSS-->
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">

    <!-- Vendor CSS-->
    <link href="vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
    <link href="vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Main CSS-->
    <link href="css/theme.css" rel="stylesheet" media="all">

</head>
<body>
<div class="page-wrapper">
<?php  include_once('inc/menu.php'); ?>
 <!-- PAGE CONTENT-->
        <div class="page-content--bgf7">
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
                                      <li class="list-inline-item">Edit receipt</li>
                                  </ul>
                              </div>
                             <a href="view_all_receipts.php"><button onclick="view_all_receipts.php" class="au-btn au-btn--small au-btn--blue m-b-20">BACK TO ALL RECEIPTS</button></a>
                               <a href="newreceipt.php" class="au-btn au-btn--small au-btn--blue m-b-20" style="background-color:yellow;color:black;">GENERATE NEW RECEIPT <?php if(isset($other_currency)) { echo $other_currency; }?></button></a>
                             <!--button onclick="exportTableToExcel('receiptsPayments','all_receipts')" class="au-btn au-btn--small au-btn--green m-b-20">DOWNLOAD LIST INTO EXCEL FORMAT</button-->
							 
                          </div>
                      </div>
                  </div>
              </div>
          </section>
		    <div class="container">
			<?php if(isset($comment)) { echo $comment.'<br />';  }   ?>
        <h2 class="mt-5 mb-4">Edit Receipts</h2>
		<div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Receipt Number</th>
					<th>Customer Name</th>
					<th>Vehicle</th>
					<th>Payment Type</th>
					<th>Date generated</th>
					<th>Payment date</th>
					<th>Amount paid (=N=)</th>
					<th>Edit</th>
                </tr>
            </thead>
            <tbody>
               <?php
        // Connect to your database
        include_once('inc/functions.php');
        
        // Fetch receipt data from the database
        $stmt = $pdo->query("SELECT * FROM main_receipt WHERE `visibility` = 'yes' ORDER BY `id` DESC");
		$receipt_counter = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Extract data from the row
            $receipt_id = $row['id'];
            $customer_name = $row['customer_name'];
            $vehicle_make = $row['vehicle_make'];
            $vehicle_model = $row['vehicle_model'];
            $vehicle_year = $row['vehicle_year'];
            $payment_type = $row['payment_type'];
            $create_date = date('F j, Y, g:i a', strtotime($row['time_created']));
            $payment_date = date('F j, Y, g:i a', strtotime($row['payment_date']));
            $amount_paid = number_format($row['amount_paid'], 2, '.', ',');
            $prefix_receipt_number = $row['prefix_receipt_number'];
			$encryption_key = "31081990";
			$encrypted_id = encryptData($receipt_id, $encryption_key);
			$receipt_counter++;
            ?>
            <tr>
                <td><?php echo $receipt_counter; ?></td>
                <td><?php echo $receipt_id.$prefix_receipt_number; ?></td>
                <td><?php echo $customer_name; ?></td>
                <td><?php echo $vehicle_make . ' ' . $vehicle_model . ' ' . $vehicle_year; ?></td>
                <td><?php echo ucfirst($payment_type); ?></td>
                <td><?php echo $create_date; ?></td>
                <td><?php echo $payment_date; ?></td>
                <td><?php echo $amount_paid; ?></td>
                <td>
                    <a href="modifyreceipt.php?prefix_receipt_number=<?php echo urlencode($encrypted_id); ?>" class="au-btn au-btn--small au-btn--brown m-b-20" style="background:brown;">Edit</a>
                    
                </td>
            </tr>
			
            <?php
        }
        ?>
            </tbody>
        </table>
		</div>
    </div> 

<?php
							  if(isset($result_mode))
							  {
							  ?>
                             <button onclick="exportTableToExcel('tblData','all_items_summary.php')" class="au-btn au-btn--small au-btn--green m-b-20">DOWNLOAD RECEIPT LIST INTO EXCEL FORMAT</button>
							 <?php
							  }
							 ?>
<script>
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    // Specify file name
    filename = filename?filename+'.xls':'excel_data.xls';

    // Create download link element
    downloadLink = document.createElement("a");

    document.body.appendChild(downloadLink);

    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

        // Setting the file name
        downloadLink.download = filename;

        //triggering the function
        downloadLink.click();
    }
}

</script>
</div>
<br />
<br />
<br />
<?php include_once('inc/footer.php'); ?>
</div>
</div>
<!-- Jquery JS-->
<script src="vendor/jquery-3.2.1.min.js"></script>
<!-- Bootstrap JS-->
<script src="vendor/bootstrap-4.1/popper.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
<!-- Vendor JS       -->
<script src="vendor/slick/slick.min.js">
</script>
<script src="vendor/wow/wow.min.js"></script>
<script src="vendor/animsition/animsition.min.js"></script>
<script src="vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
</script>
<script src="vendor/counter-up/jquery.waypoints.min.js"></script>
<script src="vendor/counter-up/jquery.counterup.min.js">
</script>
<script src="vendor/circle-progress/circle-progress.min.js"></script>
<script src="vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="vendor/chartjs/Chart.bundle.min.js"></script>
<script src="vendor/select2/select2.min.js">
</script>

<!-- Main JS-->
<script src="js/main.js"></script>

</body>

</html>
<!-- end document-->
