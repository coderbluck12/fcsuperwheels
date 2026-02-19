<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receipt_id        = (int) $_POST['receipt_id'];
    $customer_name     = validate_input($_POST['customer_name']);
    $customer_address  = validate_input($_POST['customer_address']);
    $customer_phone    = validate_input($_POST['customer_phone']);
    $customer_email    = validate_input($_POST['customer_email']);
    $vehicle_make      = validate_input($_POST['vehicle_make']);
    $vehicle_model     = validate_input($_POST['vehicle_model']);
    $vehicle_year      = validate_input($_POST['vehicle_year']);
    $vehicle_chasis    = validate_input($_POST['vehicle_chasis']);
    $vehicle_color     = validate_input($_POST['vehicle_color']);
    $vehicle_price     = validate_input($_POST['vehicle_price']);
    $add_vehicle       = validate_input($_POST['add_vehicle']);
    $payment_type      = validate_input($_POST['payment_type']);
    $payment_method    = validate_input($_POST['payment_method']);
    $payment_reference = validate_input($_POST['payment_reference']);
    $amount_paid       = validate_input($_POST['amount_paid']);
    $payment_date      = validate_input($_POST['payment_date']);
    $add_payment       = validate_input($_POST['add_payment']);
    $signature_id      = !empty($_POST['signature_id']) ? (int)$_POST['signature_id'] : null;
    $visibility        = in_array($_POST['visibility'], ['yes','no']) ? $_POST['visibility'] : 'no';

    $sql = "UPDATE main_receipt SET
                customer_name     = :customer_name,
                customer_address  = :customer_address,
                customer_phone    = :customer_phone,
                customer_email    = :customer_email,
                vehicle_make      = :vehicle_make,
                vehicle_model     = :vehicle_model,
                vehicle_year      = :vehicle_year,
                vehicle_chasis    = :vehicle_chasis,
                vehicle_color     = :vehicle_color,
                vehicle_price     = :vehicle_price,
                add_vehicle       = :add_vehicle,
                payment_type      = :payment_type,
                payment_method    = :payment_method,
                payment_reference = :payment_reference,
                amount_paid       = :amount_paid,
                payment_date      = :payment_date,
                add_payment       = :add_payment,
                signature_id      = :signature_id,
                visibility        = :visibility
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':customer_name',     $customer_name);
    $stmt->bindParam(':customer_address',  $customer_address);
    $stmt->bindParam(':customer_phone',    $customer_phone);
    $stmt->bindParam(':customer_email',    $customer_email);
    $stmt->bindParam(':vehicle_make',      $vehicle_make);
    $stmt->bindParam(':vehicle_model',     $vehicle_model);
    $stmt->bindParam(':vehicle_year',      $vehicle_year);
    $stmt->bindParam(':vehicle_chasis',    $vehicle_chasis);
    $stmt->bindParam(':vehicle_color',     $vehicle_color);
    $stmt->bindParam(':vehicle_price',     $vehicle_price);
    $stmt->bindParam(':add_vehicle',       $add_vehicle);
    $stmt->bindParam(':payment_type',      $payment_type);
    $stmt->bindParam(':payment_method',    $payment_method);
    $stmt->bindParam(':payment_reference', $payment_reference);
    $stmt->bindParam(':amount_paid',       $amount_paid);
    $stmt->bindParam(':payment_date',      $payment_date);
    $stmt->bindParam(':add_payment',       $add_payment);
    if ($signature_id === null) {
        $stmt->bindValue(':signature_id', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':signature_id', $signature_id, PDO::PARAM_INT);
    }
    $stmt->bindParam(':visibility', $visibility);
    $stmt->bindParam(':id', $receipt_id, PDO::PARAM_INT);
    $stmt->execute();

    redirect_to('master_view_all_receipts.php?edit-success');
    exit;
}

// Load existing receipt
if (!isset($_GET['prefix_receipt_number'])) {
    echo "Receipt ID not provided.";
    exit;
}
$encrypted      = $_GET['prefix_receipt_number'];
$encryption_key = "31081990";
$receipt_id     = decryptData($encrypted, $encryption_key);

$stmt = $pdo->prepare("SELECT * FROM main_receipt WHERE id = ?");
$stmt->execute([$receipt_id]);
$receipt = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$receipt) {
    echo "Receipt not found.";
    exit;
}

// Fetch signatures
$sigStmt    = $pdo->query("SELECT id, signature_name, signature_file FROM signatures ORDER BY created_at DESC");
$signatures = $sigStmt->fetchAll(PDO::FETCH_ASSOC);
$current_sig = $receipt['signature_id'];

$sigFiles = [];
foreach ($signatures as $s) {
    $sigFiles[$s['id']] = $s['signature_file'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Master Receipt</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/navthing.css">
    <style>
        .signature-preview { height: 60px; border: 1px solid #ccc; padding: 2px; }
    </style>
  <script>
    function validateNumbers() {
      const price = parseFloat(document.getElementById("vehicle_price").value),
            paid  = parseFloat(document.getElementById("amount_paid").value),
            type  = document.getElementById("payment_type").value;
      if (!isNaN(price) && paid > price) {
        alert("Amount paid cannot exceed vehicle price.");
        return false;
      }
      if (type === "full" && paid < price) {
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
                <span>Modify master receipt</span>
              </div>
            </div>
          </div>
        </div>
		<?php include_once('inc/admin_menu.php'); ?>
      </div>
    </section>
    <div class="container mt-4 mb-5">
     <h3 class="mb-4">Edit Master Receipt</h3>

      <form method="POST" action="" onsubmit="return validateNumbers()">
        <input type="hidden" name="receipt_id" value="<?= $receipt_id ?>">

        <!-- Customer Info -->
        <fieldset class="form-group">
          <legend>Customer Information</legend>
          <input type="text"    name="customer_name"    class="form-control mb-2" placeholder="Customer Fullname *"    required value="<?= htmlspecialchars($receipt['customer_name']) ?>">
          <textarea name="customer_address" class="form-control mb-2" placeholder="Customer Address *" required><?= htmlspecialchars($receipt['customer_address']) ?></textarea>
          <input type="text"    name="customer_phone"   class="form-control mb-2" placeholder="Customer Phone *"      required value="<?= htmlspecialchars($receipt['customer_phone']) ?>">
          <input type="email"   name="customer_email"   class="form-control mb-2" placeholder="Customer Email (opt.)"  value="<?= htmlspecialchars($receipt['customer_email']) ?>">
        </fieldset>

        <!-- Vehicle Info -->
        <fieldset class="form-group">
          <legend>Vehicle Information</legend>
          <input type="text"    name="vehicle_make"     class="form-control mb-2" placeholder="Make *"     required value="<?= htmlspecialchars($receipt['vehicle_make']) ?>">
          <input type="text"    name="vehicle_model"    class="form-control mb-2" placeholder="Model *"    required value="<?= htmlspecialchars($receipt['vehicle_model']) ?>">
          <input type="text"    name="vehicle_year"     class="form-control mb-2" placeholder="Year *"     required value="<?= htmlspecialchars($receipt['vehicle_year']) ?>">
          <input type="text"    name="vehicle_chasis"   class="form-control mb-2" placeholder="Chassis No *" required value="<?= htmlspecialchars($receipt['vehicle_chasis']) ?>">
          <input type="text"    name="vehicle_color"    class="form-control mb-2" placeholder="Color *"    required value="<?= htmlspecialchars($receipt['vehicle_color']) ?>">
          <input
  type="number"
  id="vehicle_price"
  name="vehicle_price"
  class="form-control mb-2"
  value="<?= ($receipt['vehicle_price'] !== null && $receipt['vehicle_price'] != 0) ? htmlspecialchars($receipt['vehicle_price']) : '' ?>"
  placeholder="Enter vehicle price (optional)"
>

          <input type="text"    name="add_vehicle"      class="form-control mb-2" placeholder="Additional Vehicle Info (opt.)" value="<?= htmlspecialchars($receipt['add_vehicle']) ?>">
        </fieldset>

        <!-- Payment Info -->
        <fieldset class="form-group">
          <legend>Payment Information</legend>
          <select name="payment_type" id="payment_type" class="form-control mb-2" required>
            <option value="">Select payment type *</option>
            <option value="full"        <?= $receipt['payment_type']==='full' ? 'selected':'' ?>>Full payment</option>
            <option value="installment" <?= $receipt['payment_type']==='installment'?'selected':'' ?>>Installment</option>
          </select>
          <input type="text"   name="payment_method"    class="form-control mb-2" placeholder="Payment Method *"   required value="<?= htmlspecialchars($receipt['payment_method']) ?>">
          <input type="text"   name="payment_reference" class="form-control mb-2" placeholder="Payment Reference"  value="<?= htmlspecialchars($receipt['payment_reference']) ?>">
          <input type="number" name="amount_paid"       id="amount_paid"    class="form-control mb-2" placeholder="Amount Paid (=N=) *" required value="<?= htmlspecialchars($receipt['amount_paid']) ?>">
          <input type="date"   name="payment_date"      class="form-control mb-2" required value="<?= htmlspecialchars(date('Y-m-d',strtotime($receipt['payment_date']))) ?>">
          <input type="text"   name="add_payment"       class="form-control mb-2" placeholder="Additional Payment Info" value="<?= htmlspecialchars($receipt['add_payment']) ?>">
        </fieldset>

        <!-- Signature -->
        <fieldset class="form-group">
          <legend>Authorized Signature</legend>
          <select name="signature_id" id="signature_id" class="form-control mb-2">
            <option value="">-- No Signature --</option>
            <?php foreach ($signatures as $sig): ?>
              <option value="<?= $sig['id'] ?>"
                <?= $sig['id']==$current_sig?'selected':'' ?>>
                <?= htmlspecialchars($sig['signature_name']) ?>
              </option>
            <?php endforeach ?>
          </select>
          <img id="sigPreview"
               src="<?= $current_sig ? htmlspecialchars($sigFiles[$current_sig]) : '' ?>"
               style="max-height:60px; <?= $current_sig?'display:block;':'display:none;' ?>">
        </fieldset>

        <!-- Visibility -->
        <fieldset class="form-group">
          <legend>Visibility</legend>
          <select name="visibility" class="form-control mb-3">
            <option value="yes" <?= $receipt['visibility']==='yes'?'selected':'' ?>>Visible</option>
            <option value="no"  <?= $receipt['visibility']==='no'?'selected':'' ?>>Hidden</option>
          </select>
        </fieldset>

        <button type="submit" class="btn btn-success btn-block">Update Receipt</button>
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
  const sigFiles = <?= json_encode($sigFiles) ?>;
  document.getElementById('signature_id').addEventListener('change', function(){
    const sel = this.value,
          img = document.getElementById('sigPreview');
    if (sel && sigFiles[sel]) {
      img.src = sigFiles[sel];
      img.style.display = 'block';
    } else {
      img.style.display = 'none';
    }
  });
</script>
</body>
</html>
