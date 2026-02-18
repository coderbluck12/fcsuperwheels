<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Fetch available signatures
$sigStmt = $pdo->query("SELECT id, signature_name FROM signatures ORDER BY created_at DESC");
$signatures = $sigStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Generate New Receipt</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link href="css/theme.css" rel="stylesheet">
  <script>
    function validateNumbers() {
      const price = parseFloat(document.getElementById("vehicle_price").value);
      const paid = parseFloat(document.getElementById("amount_paid").value);
      const type = document.getElementById("payment_type").value;

      if (!isNaN(price) && paid > price) {
        alert("Amount paid cannot be greater than vehicle price.");
        return false;
      }
      if (type === "full" && !isNaN(price) && paid < price) {
        alert("Full payment must match the vehicle price.");
        return false;
      }
      return true;
    }
  </script>
</head>
<body>
<div class="page-wrapper">
  <?php include_once('inc/header.php'); ?>
  <div class="page-content--bgf7">
    <div class="container mt-4">
      <h3>Generate New Receipt</h3>
      <a href="dashboard.php" class="btn btn-sm btn-primary mb-3">← Back to Dashboard</a>

      <form method="POST" action="receipt_processor.php" onsubmit="return validateNumbers()" enctype="multipart/form-data">

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

        <!-- Signature -->
        <fieldset class="form-group">
          <legend>Authorized Signature</legend>
          <select name="signature_id" id="signature_id" class="form-control mb-2" <?= empty($signatures) ? 'disabled' : '' ?>>
            <option value="">-- No Signature --</option>
            <?php foreach ($signatures as $sig): ?>
              <option value="<?= $sig['id'] ?>"><?= htmlspecialchars($sig['signature_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($signatures)): ?>
            <small class="text-danger">No signatures found. <a href="signature_manager.php">Upload one here</a>.</small>
          <?php endif; ?>
        </fieldset>

        <!-- Submit -->
        <input type="submit" name="receipt_submit" class="btn btn-success btn-block" value="Generate Receipt">
      </form>
	   
    </div>
  </div>
  <!-- Footer -->
      <?php include_once('inc/footer.php'); ?>
</div>

<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
</body>
</html>
