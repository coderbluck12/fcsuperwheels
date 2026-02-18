<?php
include_once('inc/functions.php');
include_once('inc/session_manager.php');

// 1) Require the single GET param
if (!isset($_GET['prefix_invoice_number'])) {
    exit('Invoice number not provided.');
}

// 2) Decrypt it
$key            = "31081990";
$encrypted      = $_GET['prefix_invoice_number'];
$invoice_id     = decryptData($encrypted, $key);

// 3) Fetch invoice + signature
$stmt = $pdo->prepare("
    SELECT
      i.*,
      s.signature_file AS company_signature
    FROM main_invoice i
    LEFT JOIN signatures s
      ON i.signature_id = s.id
    WHERE i.id = ?
    LIMIT 1
");
$stmt->execute([$invoice_id]);
$inv = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$inv) {
    exit('Invoice not found – id='.$invoice_id);
}


// 4) Helpers
function fmtDate($d, $format = 'F j, Y, g:i a') {
    return empty($d) ? 'N/A' : date($format, strtotime($d));
}
function fmtMoney($n) {
    return number_format($n, 2);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>
  Invoice <?= htmlspecialchars($inv['prefix_invoice_number']) ?> - FC Superwheels_<?= htmlspecialchars($inv['customer_name']) ?>
</title>

    <style>
        /* invoice layout (same as before) */
        .address { word-wrap: break-word; overflow-wrap: break-word; }
        .invoice-box {
            max-width:800px;margin:auto;padding:30px;
            border:1px solid #eee;box-shadow:0 0 10px rgba(0,0,0,0.15);
            font-size:16px;line-height:24px;
            font-family:'Helvetica Neue','Helvetica',Helvetica,Arial,sans-serif;
            color:#555;position:relative;
        }
        .invoice-box table{width:100%;line-height:inherit;text-align:left;}
        .invoice-box table td{padding:5px;vertical-align:top;}
        .invoice-box table tr td:nth-child(2){text-align:right;}
        .invoice-box table tr.top table td{padding-bottom:20px;}
        .invoice-box table tr.top table td.title{font-size:30px;line-height:45px;color:#333;}
        .invoice-box table tr.information table td{padding-bottom:20px;}
        .invoice-box table tr.heading td{background:#eee;border-bottom:1px solid #ddd;font-weight:bold;}
        .invoice-box table tr.details td{padding-bottom:20px;}
        .invoice-box table tr.item td{border-bottom:1px solid #eee;}
        .invoice-box table tr.item.last td{border-bottom:none;}
        .invoice-box table tr.total td:nth-child(2){border-top:2px solid #eee;font-weight:bold;}
        .invoice-box .watermark {
            position:absolute;top:50%;left:50%;
            transform:translate(-50%,-50%) rotate(-45deg);
            opacity:0.6;pointer-events:none;z-index:0;
        }
        @media only screen and (max-width:600px){
            .invoice-box table tr.top table td,
            .invoice-box table tr.information table td {
                width:100%;display:block;text-align:center;
            }
        }
        .customer-name {
            font-size:1.1em;font-family:'Courier New',Courier,monospace;
            color:black;background-color:#e0e0eb;
            padding:3px;border-radius:3px;display:inline-block;
        }
        .signature-section{width:100%;margin-top:4px;padding-top:2px;border-top:2px solid #000;}
        .signature-box{display:flex;justify-content:space-between;margin-top:4px;}
        .signature-box div{text-align:center;width:45%;}
        .signature-line,.date-line{border-bottom:1px solid #000;width:80%;margin:5px auto;}
        .signature-label{font-weight:bold;}
        .company-signature img{width:100px;height:auto;display:block;margin:0 auto;}
        /* noprint wrapper */
        .noprint button, .noprint a {
            padding:6px 12px;margin:5px;
            border:none;border-radius:5px;
            text-decoration:none;cursor:pointer;color:white;
        }
        .noprint .btn-print { background-color:green; }
        .noprint .btn-edit  { background-color:blue; }
        .noprint .btn-delete{ background-color:red; }
        .noprint .btn-close { background-color:gray; }
        @media print {
            .noprint { display: none !important; }
        }
		tr.space { height: 65px; }
		.company-signature {
  position: relative;
  text-align: center;
}

.company-signature img {
  position: relative;
  z-index: 2;
  height: 60px; /* match your current signature height */
}

.company-signature .stamp {
  position: absolute;
  top: 31px;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0.25;
  z-index: 3;
  width: 180px; /* Increase this as needed */
  height: auto;
}


    </style>
	
</head>
<body>
    <div class="invoice-box">
        <!-- Watermark -->
        <div class="watermark">
            <img src="sp_logo.png" style="width:600px;filter:grayscale(100%);opacity:0.2;" />
        </div>

        <table cellpadding="0" cellspacing="0">
            <tr class="top"><td colspan="2">
                <table><tr>
                    <td class="title">
                        <img src="sp_logo.png" style="width:100%;max-width:300px;" />
                    </td>
                    <td>
                        <span style="font-size:250%;"><strong>INVOICE</strong></span><br>
                        <b>Invoice Number:</b> <?= htmlspecialchars($inv['prefix_invoice_number']) ?><br>
                        <b>Invoice Date:</b> <?= fmtDate($inv['invoice_date']) ?><br>
                        <b>Due Date:</b>
                        <?php
                        if (
                          !empty($inv['due_date'])
                          && $inv['due_date'] !== '0000-00-00 00:00:00'
                          && ($ts = strtotime($inv['due_date'])) > 0
                        ) {
                            echo date('F j, Y', $ts);
                        } else {
                            echo 'N/A';
                        }
                        ?><br><br><br><br>
                    </td>
                </tr></table>
            </td></tr>

            <tr class="information"><td colspan="2">
                <table><tr>
                    <td style="border:1;">
                        Firstchoice Superwheels<br>
                        <b>RC NO: 7378695</b><br>
                        Plot 10, Opposite Osun State Secretariat<br>
                        Abere, Osun State.<br>
                        Phone: +234 701 675 4887<br>
                        Email: info@fcsuperwheels.com<br>
                        Website: www.fcsuperwheels.com
                    </td>
                    <td style="max-width:200px;word-wrap:break-word;overflow-wrap:break-word;">
                        <span class="customer-name">
                          <b>CUSTOMER</b><br>
                          <b>NAME:</b> <?= htmlspecialchars(strtoupper($inv['customer_name'])) ?><br>
                          <b>Address:</b> <?= htmlspecialchars($inv['customer_address']) ?><br>
                          <b>Phone:</b> <?= htmlspecialchars($inv['customer_phone']) ?><br>
                          <?php if($inv['customer_email']): ?>
                          <b>Email:</b> <?= htmlspecialchars($inv['customer_email']) ?>
                          <?php endif; ?>
                        </span>
                    </td>
                </tr></table>
            </td></tr>

            <tr class="heading"><td>Vehicle information</td><td>Details</td></tr>
            <tr class="item"><td>Name</td>
                <td><?= htmlspecialchars("{$inv['vehicle_make']} {$inv['vehicle_model']} {$inv['vehicle_year']}") ?></td>
            </tr>
            <tr class="item"><td>Color</td><td><?= htmlspecialchars($inv['vehicle_color']) ?></td></tr>
            <tr class="item space"><td>Chasis no:</td><td><?= htmlspecialchars($inv['vehicle_chasis']) ?></td></tr>
			
            <?php if($inv['add_vehicle']): ?>
            <tr class="item"><td>Additional Info</td><td><?= htmlspecialchars($inv['add_vehicle']) ?></td></tr>
            <?php endif; ?>
            <tr class="item space"><td></td><td></td></tr>

            <tr class="heading"><td>Cost Information</td><td>Details</td></tr>
            <tr class="item"><td>Quantity</td><td><?= htmlspecialchars($inv['quantity']) ?></td></tr>
            <tr class="item"><td>Vehicle Price</td><td>=N= <?= fmtMoney($inv['vehicle_price']) ?></td></tr>
            <tr class="item space"><td></td><td></td></tr>
            <tr class="item"><td>Total Amount</td><td>=N= <?= fmtMoney($inv['total_amount']) ?></td></tr>
            <tr class="item"><td>Amount in word(s)</td>
                <td><?= numbertoWords($inv['total_amount']) ?> Naira Only</td>
            </tr>
            <tr class="item space"><td></td><td></td></tr>

            <tr class="heading"><td>Payment Information</td><td></td></tr>
            <tr class="item">
                <td>
                  <p>
                    <b>Bank name:</b> Moniepoint Microfinance Bank<br>
                    <b>Account number:</b> 6674516741<br>
                    <b>Account name:</b> Superwheels Int Businesses
                  </p>
                </td>
                <td></td>
            </tr>
            <?php if($inv['payment_instruction']): ?>
            <tr class="item"><td>Instruction</td><td><?= htmlspecialchars($inv['payment_instruction']) ?></td></tr>
            <?php endif; ?>
            <?php if($inv['add_payment']): ?>
            <tr class="total"><td>Additional payment information</td><td><?= htmlspecialchars($inv['add_payment']) ?></td></tr>
            <?php endif; ?>
        </table>
<?php if($inv['company_signature']): ?>
      <div class="signature-section">
  <div class="signature-box">
  
    <!-- 1) Customer on the LEFT -->
    <div class="customer-signature">
      <p class="signature-label">Customer</p><br>
      <div class="signature-line"></div>
      <p>Date: <span class="date-line"></span></p>
    </div>
    
    <!-- 2) Authorised on the RIGHT -->
    <div class="company-signature">
      <p class="signature-label">Authorised Signatory</p>
      <?php if($inv['company_signature']): ?>
        <img src="<?= htmlspecialchars($inv['company_signature']) ?>" alt="Signature">
        <img src="stamp5.png" alt="Stamp" class="stamp">
      <?php endif; ?>
      <div class="signature-line"></div>
      <p><b>Date:</b> <?= fmtDate($inv['invoice_date'],'F j, Y') ?>
         <span class="date-line"></span></p>
    </div>
    
  </div>
</div>

<?php else: ?>
<br />
<br />
<br />
<?php endif; ?>






  <h5 style="text-align:center; margin-top:20px;">
          www.fcsuperwheels.com | info@fcsuperwheels.com | 07016754887
        </h5>
        <!-- noprint buttons -->
        <div class="noprint" style="text-align:center; margin-top:20px;">
	

          <button class="btn-print"   id="print-button" onclick="window.print()">Print Invoice</button>
          <a      class="btn-edit"    href="invoice_edit.php?prefix_invoice_number=<?= urlencode($encrypted) ?>">Edit Invoice</a>
          <!-- custom delete button -->
<button id="deleteBtn" class="noprint btn-delete">Delete Invoice</button>
          <a      class="btn-close"   href="invoice_manager.php">Close</a>
        </div>
    </div>

    <script>
      function confirmDelete() {
        if (confirm('Are you sure you want to delete this invoice? This action cannot be undone.')) {
          window.location.href =
            'invoice_delete.php?prefix_invoice_number='
            + encodeURIComponent('<?= $encrypted ?>');
        }
      }
    </script>
	<style>
  /* modal overlay sits above everything, hidden by default */
  #confirmDeleteOverlay {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;             /* hidden unless triggered */
    align-items: center;
    justify-content: center;
    z-index: 9999;
  }
  /* the modal box */
  #confirmDeleteModal {
    background: #fff;
    padding: 20px;
    border-radius: 6px;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    text-align: center;
  }
  #confirmDeleteModal h3 {
    margin-top: 0;
    color: #c00;
  }
  #confirmDeleteModal .btn {
    margin: 10px 5px 0;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    color: #fff;
    cursor: pointer;
  }
  #cancelDelete {
    background: #777;
  }
  #doDelete {
    background: #c00;
  }
</style>

<!-- your existing invoice HTML… -->



<!-- modal markup -->
<div id="confirmDeleteOverlay">
  <div id="confirmDeleteModal">
    <h3>Confirm Delete</h3>
    <p>Are you sure you want to delete this invoice? This cannot be undone.</p>
    <button id="cancelDelete" class="btn">Cancel</button>
    <button id="doDelete"     class="btn">Delete</button>
  </div>
</div>

<script>
  const overlay = document.getElementById('confirmDeleteOverlay');
  const deleteBtn = document.getElementById('deleteBtn');
  const cancelBtn = document.getElementById('cancelDelete');
  const doDelete  = document.getElementById('doDelete');
  // show the modal
  deleteBtn.addEventListener('click', () => overlay.style.display = 'flex');
  // hide the modal
  cancelBtn.addEventListener('click', () => overlay.style.display = 'none');
  // actually delete
  doDelete.addEventListener('click', () => {
    window.location.href = 'invoice_delete.php?prefix_invoice_number=<?= urlencode($encrypted) ?>';
  });
</script>

</body>
</html>
