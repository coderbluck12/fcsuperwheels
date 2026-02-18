<?php
include_once('inc/functions.php');

if (isset($_GET['prefix_receipt_number'])) {
    $receipt_id = $_GET['prefix_receipt_number'];
    $encryption_key = "31081990";
    $receipt_id = decryptData($receipt_id, $encryption_key);

    $stmt = $pdo->prepare("SELECT r.*, s.signature_file FROM main_receipt r LEFT JOIN signatures s ON r.signature_id = s.id WHERE r.id = ?");
    $stmt->execute([$receipt_id]);
    $receipt = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($receipt) {
        $encrypted_id = encryptData($receipt_id, $encryption_key);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Firstchoice Superwheels - Sales Receipt - <?= strtoupper($receipt['customer_name']); ?> - <?= $receipt['vehicle_make'] . ' ' . $receipt['vehicle_model'] . ' ' . $receipt['vehicle_year']; ?></title>
    <style>
        .address { word-wrap: break-word; overflow-wrap: break-word; }
        .invoice-box {
            max-width: 800px; margin: auto; padding: 30px;
            border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px; line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555; position: relative;
        }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(2) { text-align: right; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 30px; line-height: 45px; color: #333; }
        .invoice-box table tr.information table td { padding-bottom: 20px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
        .invoice-box .watermark {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.6; pointer-events: none; z-index: 0;
        }
        .customer-name {
            font-size: 1.1em; font-family: 'Courier New', Courier, monospace;
            color: black; background-color: #e0e0eb; padding: 3px; border-radius: 3px; display: inline-block;
        }
        tr.space { height: 65px; }
        .signature {
    margin-top: 10px;
    text-align: right;
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

.signature .signature-wrapper {
    display: inline-block;
    position: relative;
    text-align: center;
}

.signature .signature-img {
    height: 60px;
    margin-top: 5px;
    position: relative;
    z-index: 1;
}

.signature .stamp {
  position: absolute;
  bottom: -29px;
  left: 50%;
  transform: translateX(-50%);
  opacity: 0.25;
  z-index: 3;
  width: 160px; /* Increase this as needed */
  height: auto;
}


.signature .date {
    margin-top: 0px;
    font-size: 14px;
    color: #444;
}

        .noprint button, .noprint a {
            padding: 6px 12px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            color: white;
        }
        .btn-print { background-color: green; }
        .btn-edit { background-color: blue; }
        .btn-delete { background-color: red; }
        .btn-close { background-color: gray; }

        .btn-print:hover { background-color: darkgreen; }
        .btn-edit:hover { background-color: darkblue; }
        .btn-delete:hover { background-color: darkred; }
        .btn-close:hover { background-color: #333; }

        @media print { .noprint { display: none !important; } }

        #deleteModal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); z-index: 9999;
            justify-content: center; align-items: center;
        }
        #deleteModalContent {
            background: white; padding: 20px 30px; border-radius: 10px;
            max-width: 400px; text-align: center;
            box-shadow: 0 0 10px #000;
        }
        #deleteModalContent button {
            padding: 8px 16px; margin: 10px;
            border: none; border-radius: 5px;
            color: white; cursor: pointer;
        }
        #deleteYes { background-color: red; }
        #deleteNo { background-color: gray; }
    </style>
    <script>
        let deleteUrl = '';
        function confirmDelete(url) {
            deleteUrl = url;
            document.getElementById('deleteModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('deleteModal').style.display = 'none';
            deleteUrl = '';
        }
        function proceedDelete() {
            if (deleteUrl) {
                window.location.href = deleteUrl;
            }
        }
    </script>
</head>
<body>
<div class="invoice-box">
    <div class="watermark">
        <img src="sp_logo.png" style="width: 600px; filter: grayscale(100%); opacity: 0.2;" />
    </div>
     <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="2">
                <table>
                    <tr>
                        <td class="title">
                            <img src="sp_logo.png" style="width: 100%; max-width: 300px" />
                        </td>
                        <td>
                            <b>RECEIPT no:</b> <?= $receipt['prefix_receipt_number']; ?><br />
                            <b>Date receipt generated:</b> <?= date('F j, Y, g:i a', strtotime($receipt['time_created'])); ?><br />
                            <b>Payment date:</b> <?= date('F j, Y', strtotime($receipt['payment_date'])); ?><br />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="information">
            <td colspan="2">
                <table>
                    <tr>
                        <td>
                            Firstchoice Superwheels<br />
                            <b>RC NO: 7378695</b><br />
                            Plot 10, Opposite Osun State Secretariat<br />
                            Abere, Osun State.<br />
                            Phone: +2347016754887<br />
                            Email: info@fcsuperwheels.com<br />
                            Website: www.fcsuperwheels.com
                        </td>
                        <td style="max-width: 200px; word-wrap: break-word; overflow-wrap: break-word;">
                            <span class="customer-name">CUSTOMER<br />
                            Name: <b><?= strtoupper($receipt['customer_name']); ?></b><br />
                            Address: <?= $receipt['customer_address']; ?><br />
                            Phone: <?= $receipt['customer_phone']; ?><br />
                            Email: <?= $receipt['customer_email']; ?></span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr class="heading"><td>Item information</td><td>Details</td></tr>
        <tr class="item"><td>Name</td><td><?= $receipt['vehicle_make'] . ' ' . $receipt['vehicle_model'] . ' ' . $receipt['vehicle_year']; ?></td></tr>
        <?php if (!empty($receipt['vehicle_price'])): ?>
            <tr class="item"><td>Actual Price</td><td>=N=<?= number_format($receipt['vehicle_price'], 2); ?></td></tr>
        <?php endif; ?>
        <tr class="item space"><td>Amount paid</td><td>=N=<?= number_format($receipt['amount_paid'], 2) . ' (' . ucfirst($receipt['payment_type']) . ' payment)'; ?></td></tr>
        <tr class="heading"><td>Vehicle description</td><td>Details</td></tr>
        <tr class="item"><td>Chassis number</td><td><?= $receipt['vehicle_chasis']; ?></td></tr>
        <tr class="item"><td>Colour</td><td><?= $receipt['vehicle_color']; ?></td></tr>
        <tr class="item"><td>Year</td><td><?= $receipt['vehicle_year']; ?></td></tr>
        <?php if (!empty($receipt['add_vehicle'])): ?>
            <tr class="item"><td>Additional vehicle information</td><td><?= $receipt['add_vehicle']; ?></td></tr>
        <?php endif; ?>
        <tr class="heading"><td>Payment Information</td><td>Details</td></tr>
        <tr class="item"><td>Payment type</td><td><?= ucfirst($receipt['payment_type']); ?></td></tr>
        <tr class="item"><td>Payment method</td><td><?= $receipt['payment_method']; ?></td></tr>
        <?php if (!empty($receipt['payment_reference'])): ?>
            <tr class="item"><td>Reference</td><td><?= $receipt['payment_reference']; ?></td></tr>
        <?php endif; ?>
        <?php if (!empty($receipt['add_payment'])): ?>
            <tr class="total"><td>Additional payment information</td><td><?= $receipt['add_payment']; ?></td></tr>
        <?php endif; ?>
        <tr class="heading"><td>Total Payment Made</td><td></td></tr>
        <tr class="details">
            <td colspan="2" style="text-align:center;">
                <h2>=N=<?= number_format($receipt['amount_paid'], 2); ?> - <?= ucfirst($receipt['payment_type']); ?> Payment <?= $receipt['payment_type'] === 'installment' ? '(INCOMPLETE)' : '(COMPLETE)'; ?></h2>
            </td>
        </tr>
    </table>
    <?php if (!empty($receipt['signature_file'])): ?>
    <div class="signature">
        <p>Authorized Signature:</p>
       <div class="signature-wrapper">
    <img src="<?= htmlspecialchars($receipt['signature_file']); ?>" alt="Signature" class="signature-img">
    <img src="stamp5.png" alt="Stamp" class="stamp">
    <p class="date">Signed on <?= date('F j, Y', strtotime($receipt['payment_date'])); ?></p>
</div>

    </div>
    <?php endif; ?>
    <div class="footer text-center mt-4">
        <h4 style="text-align:center;">
            <?= $receipt['payment_type'] === 'full' ?
                'Thank you for your prompt payment. Your transaction is complete. We appreciate your business.' :
                strtoupper('Thank you for your installment payment. Please note that your transaction is not yet complete. Kindly ensure timely completion of all installments. Thank you for choosing us.') ?>
        </h4>
        <div class="mt-3 noprint">
            <a class="btn-print" href="javascript:window.print()">Print Receipt</a>
            <a class="btn-edit" href="modifyreceipt.php?prefix_receipt_number=<?= urlencode($encrypted_id); ?>">Edit Receipt</a>
            <a class="btn-delete" href="#" onclick="confirmDelete('delete_receipt.php?prefix_receipt_number=<?= urlencode($encrypted_id); ?>'); return false;">Delete Receipt</a>
            <a class="btn-close" href="dashboard.php">Close</a>
        </div>
    </div>
</div>

<!-- Custom Delete Modal -->
<div id="deleteModal">
    <div id="deleteModalContent">
        <h3>Are you sure?</h3>
        <p>This receipt will be permanently deleted.</p>
        <button id="deleteYes" onclick="proceedDelete()">Yes, Delete</button>
        <button id="deleteNo" onclick="closeModal()">Cancel</button>
    </div>
</div>
</body>
</html>
<?php
    } else {
        echo "Receipt not found.";
    }
} else {
    echo "Receipt ID not provided.";
}
?>
