<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Fetch available signatures
$sigStmt    = $pdo->query("SELECT id, signature_name, signature_file FROM signatures ORDER BY created_at DESC");
$signatures = $sigStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Generate New Invoice</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
  <link href="css/theme.css" rel="stylesheet">
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
    window.addEventListener('DOMContentLoaded',()=>{
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
        <div class="row">
          <div class="col-md-12">
            <div class="au-breadcrumb-content">
              <div class="au-breadcrumb-left">
                <span class="au-breadcrumb-span">You are here:</span>
                <ul class="list-unstyled list-inline au-breadcrumb__list">
                  <li class="list-inline-item"><a href="master_view_all_invoices.php">All Invoices</a></li>
                  <li class="list-inline-item seprate"><span>/</span></li>
                  <li class="list-inline-item active">Generate New Invoice</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        <?php include_once('inc/admin_menu.php'); ?>
      </div>
    </section>

    <div class="container watermark mt-4 mb-5">
      <h3 class="mb-4">Generate New Invoice</h3>
      <form method="POST" action="invoice_processor.php" onsubmit="return validateInvoice()">

        <!-- Invoice & Customer -->
        <fieldset class="form-group">
          <legend>Invoice &amp; Customer Info</legend>

          <label for="invoice_date">Invoice Date *</label>
          <input type="datetime-local" id="invoice_date" name="invoice_date"
                 class="form-control mb-2" value="<?= date('Y-m-d\TH:i') ?>" required>

          <label for="customer_name">Customer Fullname *</label>
          <input type="text" id="customer_name" name="customer_name"
                 class="form-control mb-2" placeholder="Customer Fullname *" required>

          <label for="customer_address">Customer Address *</label>
          <textarea id="customer_address" name="customer_address"
                    class="form-control mb-2" rows="2"
                    placeholder="Customer Address *" required></textarea>

          <label for="customer_phone">Customer Phone *</label>
          <input type="tel" id="customer_phone" name="customer_phone"
                 class="form-control mb-2" placeholder="Customer Phone *" required>

          <label for="customer_email">Customer Email</label>
          <input type="email" id="customer_email" name="customer_email"
                 class="form-control mb-2" placeholder="Customer Email">
        </fieldset>

        <!-- Vehicle / Item -->
        <fieldset class="form-group">
          <legend>Vehicle / Item Info</legend>

          <div class="form-row">
            <div class="col">
              <label for="vehicle_make">Make *</label>
              <input type="text" id="vehicle_make" name="vehicle_make"
                     class="form-control mb-2" placeholder="Make *" required>
            </div>
            <div class="col">
              <label for="vehicle_model">Model *</label>
              <input type="text" id="vehicle_model" name="vehicle_model"
                     class="form-control mb-2" placeholder="Model *" required>
            </div>
            <div class="col">
              <label for="vehicle_year">Year *</label>
              <input type="text" id="vehicle_year" name="vehicle_year"
                     class="form-control mb-2" placeholder="Year *" required>
            </div>
          </div>

          <div class="form-row">
            <div class="col">
              <label for="vehicle_chasis">Chassis No *</label>
              <input type="text" id="vehicle_chasis" name="vehicle_chasis"
                     class="form-control mb-2" placeholder="Chassis No *" required>
            </div>
            <div class="col">
              <label for="vehicle_color">Color *</label>
              <input type="text" id="vehicle_color" name="vehicle_color"
                     class="form-control mb-2" placeholder="Color *" required>
            </div>
          </div>

          <div class="form-row">
            <div class="col">
              <label for="quantity">Quantity *</label>
              <input type="number" id="quantity" name="quantity"
                     class="form-control mb-2" placeholder="Quantity *"
                     value="1" min="1" oninput="calculateTotal()" required>
            </div>
            <div class="col">
              <label for="vehicle_price">Unit Price *</label>
              <input type="number" id="vehicle_price" name="vehicle_price"
                     class="form-control mb-2" placeholder="Unit Price *"
                     oninput="calculateTotal()" required>
            </div>
            <div class="col">
              <label for="total_amount">Total</label>
              <input type="text" id="total_amount" name="total_amount"
                     class="form-control mb-2" placeholder="Total" readonly>
            </div>
          </div>

          <label for="add_vehicle">Additional Vehicle Info</label>
          <input type="text" id="add_vehicle" name="add_vehicle"
                 class="form-control mb-2" placeholder="Additional Vehicle Info">
        </fieldset>

        <!-- Payment Details -->
        <fieldset class="form-group">
          <legend>Payment Details</legend>


          <label for="due_date">Due Date (optional)</label>
          <input type="datetime-local" id="due_date" name="due_date"
                 class="form-control mb-2">

          <label for="payment_instruction">Payment Instruction</label>
          <input type="text" id="payment_instruction" name="payment_instruction"
                 class="form-control mb-2" placeholder="Payment Instruction">

          <label for="add_payment">Additional Payment Info</label>
          <input type="text" id="add_payment" name="add_payment"
                 class="form-control mb-2" placeholder="Additional Payment Info">
        </fieldset>

        <!-- Signature & Visibility -->
        <fieldset class="form-group">
          <legend>Authorized Signature &amp; Visibility</legend>

          <label for="signature_id">Authorized Signature</label>
          <select name="signature_id" id="signature_id"
                  class="form-control mb-2" onchange="previewSignature()"
                  <?= empty($signatures)?'disabled':'' ?>>
            <option value="">— No Signature —</option>
            <?php foreach($signatures as $s): ?>
              <option
                value="<?= $s['id'] ?>"
                data-file="<?= htmlspecialchars($s['signature_file']) ?>">
                <?= htmlspecialchars($s['signature_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <img id="sigPreview" class="signature-preview" style="display:none"
               src="" alt="Signature Preview">

          <label for="visibility">Visibility *</label>
          <select name="visibility" id="visibility"
                  class="form-control mb-2" required>
            <option value="yes">Visible</option>
            <option value="no" selected>Hidden</option>
          </select>
        </fieldset>

        <button type="submit" class="btn btn-success btn-block noprint">
          Generate Invoice
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
