<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// ———————————————————
// HTTP Basic Auth
// ———————————————————
$valid_username = 'admin';
$valid_password = 'password123';
if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
 || $_SERVER['PHP_AUTH_USER'] !== $valid_username
 || $_SERVER['PHP_AUTH_PW'] !== $valid_password
) {
    header('WWW-Authenticate: Basic realm="Protected Page"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Unauthorized Access.');
}

// ———————————————————
// Fetch signatures for the dropdown
// ———————————————————
$sigStmt    = $pdo->query("
    SELECT id, signature_name, signature_file
      FROM signatures
  ORDER BY created_at DESC
");
$signatures = $sigStmt->fetchAll(PDO::FETCH_ASSOC);

// ———————————————————
// Decrypt & load invoice
// ———————————————————
if (!isset($_GET['prefix_invoice_number'])) {
    exit('Invoice ID not provided.');
}
$enc = $_GET['prefix_invoice_number'];
$id  = decryptData($enc, "31081990");

$stmt = $pdo->prepare("SELECT * FROM main_invoice WHERE id = ?");
$stmt->execute([$id]);
$inv  = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inv) {
    exit('Invoice not found.');
}

// ———————————————————
// Helper to pre-format datetime-local
// ———————————————————
function toDtLocal($dt) {
    if (!$dt || $dt === '0000-00-00 00:00:00') return '';
    return date('Y-m-d\TH:i', strtotime($dt));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Invoice #<?= htmlspecialchars($inv['prefix_invoice_number'].$inv['id']) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
  <link href="css/theme.css" rel="stylesheet">
  <link rel="stylesheet" href="vendor/font-awesome-5/css/fontawesome-all.min.css">
  <link href="css/navthing.css" rel="stylesheet">
  <style>
    .signature-preview { margin-top:.5rem; max-height:80px; }
    @media print { .noprint{ display:none!important } }
  </style>
  <script>
    function calculateTotal() {
      let qty = parseFloat(document.getElementById('quantity').value)||0;
      let pr  = parseFloat(document.getElementById('vehicle_price').value)||0;
      document.getElementById('total_amount').value = (qty*pr).toFixed(2);
    }
    function validateInvoice() {
      let tot = parseFloat(document.getElementById('total_amount').value)||0;
      let paid= parseFloat(document.getElementById('amount_paid').value)||0;
      let t   = document.getElementById('payment_type').value;
      if(paid>tot){ alert("Paid > total");return false }
      if(t==='full'&&paid<tot){ alert("Full must cover total");return false }
      return true;
    }
    function previewSignature() {
      let sel = document.getElementById('signature_id'),
          img = document.getElementById('sigPreview');
      if(sel.value){
        img.src = sel.options[sel.selectedIndex].dataset.file;
        img.style.display = 'block';
      } else img.style.display = 'none';
    }
    window.addEventListener('DOMContentLoaded', ()=>{
      calculateTotal();
      previewSignature();
    });
  </script>
</head>
<body>
<div class="page-wrapper">
  <?php include_once('inc/menu.php'); ?>
  <div class="page-content--bgf7 watermark">
    <section class="au-breadcrumb2">
      <div class="container watermark">
        <div class="au-breadcrumb-content">
          <div class="au-breadcrumb-left">
            <span class="au-breadcrumb-span">You are here:</span>
            <ul class="list-unstyled list-inline au-breadcrumb__list">
              <li class="list-inline-item">
                <a href="master_view_all_invoices.php">All Invoices</a>
              </li>
              <li class="list-inline-item seprate"><span>/</span></li>
              <li class="list-inline-item active">Edit Invoice</li>
            </ul>
          </div>
        </div>
        <?php include_once('inc/admin_menu.php'); ?>
      </div>
    </section>

    <div class="container watermark mt-4 mb-5">
      <h3 class="mb-4">Edit Invoice #<?= htmlspecialchars($inv['prefix_invoice_number'].$inv['id']) ?></h3>
      <form method="POST" action="invoice_processor.php" onsubmit="return validateInvoice()">

        <!-- Invoice & Customer -->
        <fieldset class="form-group">
          <legend>Invoice &amp; Customer Info</legend>
          <label for="invoice_date">Invoice Date *</label>
          <input type="datetime-local" id="invoice_date" name="invoice_date"
                 class="form-control mb-2"
                 value="<?= toDtLocal($inv['invoice_date']) ?>"
                 required>

          <label for="customer_name">Customer Fullname *</label>
          <input type="text" id="customer_name" name="customer_name"
                 class="form-control mb-2"
                 value="<?= htmlspecialchars($inv['customer_name']) ?>"
                 required>

          <label for="customer_address">Customer Address *</label>
          <textarea id="customer_address" name="customer_address"
                    class="form-control mb-2" rows="2" required><?= htmlspecialchars($inv['customer_address']) ?></textarea>

          <label for="customer_phone">Customer Phone *</label>
          <input type="tel" id="customer_phone" name="customer_phone"
                 class="form-control mb-2"
                 value="<?= htmlspecialchars($inv['customer_phone']) ?>"
                 required>

          <label for="customer_email">Customer Email</label>
          <input type="email" id="customer_email" name="customer_email"
                 class="form-control mb-2"
                 value="<?= htmlspecialchars($inv['customer_email']) ?>">
        </fieldset>

        <!-- Vehicle / Item -->
        <fieldset class="form-group">
          <legend>Vehicle / Item Info</legend>
          <div class="form-row">
            <div class="col">
              <input type="text" name="vehicle_make" class="form-control mb-2"
                     placeholder="Make *"
                     value="<?= htmlspecialchars($inv['vehicle_make']) ?>"
                     required>
            </div>
            <div class="col">
              <input type="text" name="vehicle_model" class="form-control mb-2"
                     placeholder="Model *"
                     value="<?= htmlspecialchars($inv['vehicle_model']) ?>"
                     required>
            </div>
            <div class="col">
              <input type="text" name="vehicle_year" class="form-control mb-2"
                     placeholder="Year *"
                     value="<?= htmlspecialchars($inv['vehicle_year']) ?>"
                     required>
            </div>
          </div>
          <div class="form-row">
            <div class="col">
              <input type="text" name="vehicle_chasis" class="form-control mb-2"
                     placeholder="Chassis No *"
                     value="<?= htmlspecialchars($inv['vehicle_chasis']) ?>"
                     required>
            </div>
            <div class="col">
              <input type="text" name="vehicle_color" class="form-control mb-2"
                     placeholder="Color *"
                     value="<?= htmlspecialchars($inv['vehicle_color']) ?>"
                     required>
            </div>
          </div>
          <div class="form-row">
            <div class="col">
              <input type="number" name="quantity" id="quantity" class="form-control mb-2"
                     placeholder="Quantity *"
                     value="<?= $inv['quantity'] ?>" min="1"
                     oninput="calculateTotal()" required>
            </div>
            <div class="col">
              <input type="number" name="vehicle_price" id="vehicle_price"
                     class="form-control mb-2"
                     placeholder="Unit Price *"
                     value="<?= $inv['vehicle_price'] ?>"
                     oninput="calculateTotal()" required>
            </div>
            <div class="col">
              <input type="text" name="total_amount" id="total_amount"
                     class="form-control mb-2"
                     placeholder="Total"
                     value="<?= number_format($inv['total_amount'],2) ?>"
                     readonly>
            </div>
          </div>
          <label for="add_vehicle">Additional Vehicle Info</label>
          <input type="text" id="add_vehicle" name="add_vehicle"
                 class="form-control mb-2"
                 value="<?= htmlspecialchars($inv['add_vehicle']) ?>">
        </fieldset>

        <!-- Payment Details -->
        <fieldset class="form-group">
          <legend>Payment Details</legend>

          <label for="due_date">Due Date</label>
          <input type="datetime-local" id="due_date" name="due_date"
                 class="form-control mb-2"
                 value="<?= toDtLocal($inv['due_date']) ?>">

          <label for="payment_instruction">Payment Instruction</label>
          <input type="text" id="payment_instruction"
                 name="payment_instruction"
                 class="form-control mb-2"
                 value="<?= htmlspecialchars($inv['payment_instruction']) ?>">

          <label for="add_payment">Additional Payment Info</label>
          <input type="text" id="add_payment" name="add_payment"
                 class="form-control mb-2"
                 value="<?= htmlspecialchars($inv['add_payment']) ?>">
        </fieldset>

        <!-- Signature & Visibility -->
        <fieldset class="form-group">
          <legend>Authorized Signature &amp; Visibility</legend>

          <label for="signature_id">Signature</label>
          <select name="signature_id" id="signature_id"
                  class="form-control mb-2"
                  onchange="previewSignature()">
            <option value="">— No Signature —</option>
            <?php foreach($signatures as $s): ?>
            <option value="<?= $s['id'] ?>"
                    data-file="<?= htmlspecialchars($s['signature_file']) ?>"
                    <?= $inv['signature_id']==$s['id']?'selected':'' ?>>
              <?= htmlspecialchars($s['signature_name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <img id="sigPreview" class="signature-preview" style="display:none" src="" alt="Signature Preview">

          <label for="visibility">Visibility *</label>
          <select name="visibility" id="visibility" class="form-control mb-2" required>
            <option value="yes" <?= $inv['visibility']=='yes'?'selected':'' ?>>Visible</option>
            <option value="no"  <?= $inv['visibility']=='no' ?'selected':'' ?>>Hidden</option>
          </select>
        </fieldset>

          <!-- now -->
  <input type="hidden" name="invoice_id" value="<?= $inv['id'] ?>">

        <button type="submit" class="btn btn-success btn-block noprint">
          Update Invoice
        </button>
      </form>

      <!-- Footer -->
      <?php include_once('inc/footer.php'); ?>
    </div>
  </div>
</div>

<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
</body>
</html>
