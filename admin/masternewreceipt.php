<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Basic HTTP authentication for master admin
$valid_username = 'admin';
$valid_password = 'password123';
if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
    || $_SERVER['PHP_AUTH_USER'] !== $valid_username
    || $_SERVER['PHP_AUTH_PW'] !== $valid_password) {
    header('WWW-Authenticate: Basic realm="Protected Page"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized Access.';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Generate Master Receipt</title>
  <link rel="stylesheet" href="vendor/bootstrap-4.1/bootstrap.min.css">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/navthing.css">
  <style>
    .noprint { display: block; }
    @media print { .noprint { display: none !important; } }
  </style>
</head>
<body>
<div class="page-wrapper">
  <?php include_once('inc/header.php'); ?>
  <div class="page-content--bgf7 watermark">
    <section class="au-breadcrumb2">
      <div class="container watermark">
        <div class="row">
          <div class="col-md-12">
            <div class="au-breadcrumb-content d-flex justify-content-between align-items-center">
              <div>
                <span>You are here: </span>
                <a href="dashboard.php">General Dashboard</a> /
                <a href="master_view_all_receipts.php">Master Receipts</a> /
                <span>Generate New</span>
              </div>
            </div>
          </div>
        </div>
		<?php include_once('inc/admin_menu.php'); ?>
      </div>
    </section>
    <div class="container mt-4 mb-5">
      <h3 class="mb-4">Generate New Master Receipt</h3>
      <form method="POST" action="master_receipt_processor.php" onsubmit="return validateNumbers()">
        <!-- Customer Info -->
        <fieldset class="form-group">
          <legend>Customer Information</legend>
          <input type="text" name="customer_name" class="form-control mb-2" placeholder="Customer Fullname *" required>
          <textarea name="customer_address" class="form-control mb-2" placeholder="Customer Address *" required></textarea>
          <input type="text" name="customer_phone" class="form-control mb-2" placeholder="Customer Phone Number *" required>
          <input type="email" name="customer_email" class="form-control mb-2" placeholder="Customer Email (optional)">
        </fieldset>
        <!-- Vehicle Info -->
        <fieldset class="form-group">
          <legend>Vehicle Information</legend>
          <input type="text" name="vehicle_make" class="form-control mb-2" placeholder="Make *" required>
          <input type="text" name="vehicle_model" class="form-control mb-2" placeholder="Model *" required>
          <input type="text" name="vehicle_year" class="form-control mb-2" placeholder="Year *" required>
          <input type="text" name="vehicle_chasis" class="form-control mb-2" placeholder="Chassis No *" required>
          <input type="text" name="vehicle_color" class="form-control mb-2" placeholder="Color *" required>
          <input type="number" name="vehicle_price" id="vehicle_price" class="form-control mb-2" placeholder="Vehicle Price (optional)">
          <input type="text" name="add_vehicle" class="form-control mb-2" placeholder="Additional Vehicle Info (optional)">
        </fieldset>
        <!-- Payment Info -->
        <fieldset class="form-group">
          <legend>Payment Information</legend>
          <select name="payment_type" id="payment_type" class="form-control mb-2" required>
            <option value="">Select payment type *</option>
            <option value="full">Full payment</option>
            <option value="installment">Installment</option>
          </select>
          <input type="text" name="payment_method" class="form-control mb-2" placeholder="Payment Method *" required>
          <input type="text" name="payment_reference" class="form-control mb-2" placeholder="Payment Reference (optional)">
          <input type="number" name="amount_paid" id="amount_paid" class="form-control mb-2" placeholder="Amount Paid (=N=) *" required>
          <input type="date" name="payment_date" class="form-control mb-2" required>
          <input type="text" name="add_payment" class="form-control mb-2" placeholder="Additional Payment Info (optional)">
        </fieldset>
        <!-- Optional Generated Date Override -->
        <fieldset class="form-group form-inline">
          <label class="mr-2">Date Generated (optional):</label>
          <input type="datetime-local" name="time_created" class="form-control" placeholder="Override creation time">
        </fieldset>
        <!-- Signature Selection -->
        <fieldset class="form-group">
          <legend>Authorized Signature</legend>
          <select name="signature_id" class="form-control mb-2">
            <option value="">-- No Signature --</option>
            <?php foreach(
              $pdo->query("SELECT id,signature_name FROM signatures ORDER BY created_at DESC")
                ->fetchAll(PDO::FETCH_ASSOC) as $sig): ?>
              <option value="<?= $sig['id'] ?>"><?= htmlspecialchars($sig['signature_name']) ?></option>
            <?php endforeach ?>
          </select>
        </fieldset>
        <!-- Visibility -->
        <fieldset class="form-group">
          <legend>Visibility</legend>
          <select name="visibility" class="form-control mb-2" required>
            <option value="yes" selected>Visible</option>
            <option value="no">Hidden</option>
          </select>
        </fieldset>
        <!-- Submit -->
        <button type="submit" name="receipt_submit" class="btn btn-success btn-block">Generate Receipt</button>
      </form>
    </div>
      </form>
    </div>
	   <!-- Footer -->
        <div class="row mt-4">
          <div class="col-12">
            <?php include_once('inc/footer.php'); ?>
          </div>
        </div>
  </div>
</div>
<script>
function validateNumbers() {
  const price = parseFloat(document.getElementById('vehicle_price').value);
  const paid  = parseFloat(document.getElementById('amount_paid').value);
  const type  = document.getElementById('payment_type').value;
  if (!isNaN(price) && paid > price) {
    alert('Amount paid cannot exceed vehicle price.'); return false;
  }
  if (type === 'full' && !isNaN(price) && paid < price) {
    alert('Full payment must match the vehicle price.'); return false;
  }
  return true;
}
</script>
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
