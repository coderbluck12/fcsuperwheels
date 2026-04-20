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
      let grandTotal = 0;
      document.querySelectorAll('.item-block').forEach(block => {
        let qty = parseFloat(block.querySelector('.item-qty').value)||0;
        let pr  = parseFloat(block.querySelector('.item-price').value)||0;
        let total = (qty*pr);
        block.querySelector('.item-total').value = total.toFixed(2);
        grandTotal += total;
      });
      if(document.getElementById('grand_total_display')) {
          document.getElementById('grand_total_display').innerText = grandTotal.toFixed(2);
      }
    }
    function validateInvoice() {
      calculateTotal();
      let tot = 0;
      document.querySelectorAll('.item-total').forEach(inp => tot += (parseFloat(inp.value)||0));
      let paid= parseFloat(document.getElementById('amount_paid') ? document.getElementById('amount_paid').value : 0)||0;
      let t   = document.getElementById('payment_type') ? document.getElementById('payment_type').value : '';
      if(document.getElementById('amount_paid') && paid>tot){ alert("Paid > total");return false }
      if(t==='full'&&paid<tot){ alert("Full must cover total");return false }
      
      // Pack items into JSON
      let items = [];
      document.querySelectorAll('.item-block').forEach((block) => {
        items.push({
           make: block.querySelector('[name="vehicle_make[]"]').value,
           model: block.querySelector('[name="vehicle_model[]"]').value,
           year: block.querySelector('[name="vehicle_year[]"]').value,
           chasis: block.querySelector('[name="vehicle_chasis[]"]').value,
           color: block.querySelector('[name="vehicle_color[]"]').value,
           quantity: block.querySelector('[name="quantity[]"]').value,
           price: block.querySelector('[name="vehicle_price[]"]').value,
           total: block.querySelector('.item-total').value,
           add_vehicle: block.querySelector('[name="add_vehicle[]"]').value
        });
      });
      document.getElementById('items_json').value = JSON.stringify(items);
      return true;
    }
    function previewSignature() {
      let sel = document.getElementById('signature_id'),
          img = document.getElementById('sigPreview');
      if(sel && sel.value){
        img.src = sel.options[sel.selectedIndex].dataset.file;
        img.style.display = 'block';
      } else if(img) {
        img.style.display = 'none';
      }
    }
    function addItemBlock() {
      let container = document.getElementById('itemsContainer');
      let firstBlock = container.querySelector('.item-block');
      let newBlock = firstBlock.cloneNode(true);
      newBlock.querySelectorAll('input').forEach(inp => {
          if(!inp.classList.contains('item-qty')) inp.value = '';
          else inp.value = '1';
      });
      // add a remove button
      if(!newBlock.querySelector('.btn-remove-item')) {
          let btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn btn-sm btn-danger btn-remove-item mb-2';
          btn.innerText = 'Remove Item';
          btn.onclick = function() { newBlock.remove(); calculateTotal(); };
          newBlock.insertBefore(btn, newBlock.firstChild);
      }
      container.appendChild(newBlock);
    }
    window.addEventListener('DOMContentLoaded',()=>{
      calculateTotal();
      previewSignature();
    });
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

        <!-- Vehicle / Items -->
        <fieldset class="form-group border p-3 mb-3 bg-white shadow-sm shadow-sm" style="border-radius: 8px;">
          <legend class="w-auto px-2 font-weight-bold text-primary">Vehicle / Item Info</legend>
          <div id="itemsContainer">
          <div class="item-block border p-3 mb-3" style="background:#fcfcfc; border-radius:5px;">
          <div class="form-row">
            <div class="col">
              <label>Make *</label>
              <input type="text" name="vehicle_make[]"
                     class="form-control mb-2" placeholder="Make *" required>
            </div>
            <div class="col">
              <label>Model *</label>
              <input type="text" name="vehicle_model[]"
                     class="form-control mb-2" placeholder="Model *" required>
            </div>
            <div class="col">
              <label>Year *</label>
              <input type="text" name="vehicle_year[]"
                     class="form-control mb-2" placeholder="Year *" required>
            </div>
          </div>

          <div class="form-row">
            <div class="col">
              <label>Chassis No *</label>
              <input type="text" name="vehicle_chasis[]"
                     class="form-control mb-2" placeholder="Chassis No *" required>
            </div>
            <div class="col">
              <label>Color *</label>
              <input type="text" name="vehicle_color[]"
                     class="form-control mb-2" placeholder="Color *" required>
            </div>
          </div>

          <div class="form-row">
            <div class="col">
              <label>Quantity *</label>
              <input type="number" name="quantity[]"
                     class="form-control mb-2 item-qty" placeholder="Quantity *"
                     value="1" min="1" oninput="calculateTotal()" required>
            </div>
            <div class="col">
              <label>Unit Price *</label>
              <input type="number" name="vehicle_price[]"
                     class="form-control mb-2 item-price" placeholder="Unit Price *"
                     oninput="calculateTotal()" required>
            </div>
            <div class="col">
              <label>Total</label>
              <input type="text" name="total_amount[]"
                     class="form-control mb-2 item-total" placeholder="Total" readonly>
            </div>
          </div>

          <label>Additional Vehicle Info</label>
          <input type="text" name="add_vehicle[]"
                 class="form-control mb-2" placeholder="Additional Vehicle Info">
          </div>
          </div>
          
          <button type="button" class="btn btn-primary btn-sm mb-3" onclick="addItemBlock()">+ Add Another Item</button>
          
          <div class="text-right">
              <h5 class="text-secondary">Grand Total: =N= <span id="grand_total_display">0.00</span></h5>
          </div>
        </fieldset>
        
        <input type="hidden" name="items_json" id="items_json">

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
