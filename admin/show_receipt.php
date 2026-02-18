<?php
include_once('inc/functions.php');

// Decrypt data received via GET parameters
$encryption_key = "31081990";
$decrypted_customer_name = decryptData($_GET['customer_name'], $encryption_key);
$decrypted_customer_address = decryptData($_GET['customer_address'], $encryption_key);
$decrypted_customer_phone = decryptData($_GET['customer_phone'], $encryption_key);
$decrypted_customer_email = decryptData($_GET['customer_email'], $encryption_key);
$decrypted_vehicle_make = decryptData($_GET['vehicle_make'], $encryption_key);
$decrypted_vehicle_model = decryptData($_GET['vehicle_model'], $encryption_key);
$decrypted_vehicle_year = decryptData($_GET['vehicle_year'], $encryption_key);
$decrypted_vehicle_chasis = decryptData($_GET['vehicle_chasis'], $encryption_key);
$decrypted_vehicle_color = decryptData($_GET['vehicle_color'], $encryption_key);
$decrypted_vehicle_price = decryptData($_GET['vehicle_price'], $encryption_key);
$decrypted_payment_type = decryptData($_GET['payment_type'], $encryption_key);
$decrypted_payment_method = decryptData($_GET['payment_method'], $encryption_key);
$decrypted_payment_reference = decryptData($_GET['payment_reference'], $encryption_key);
$decrypted_amount_paid = decryptData($_GET['amount_paid'], $encryption_key);
$decrypted_payment_date = decryptData($_GET['payment_date'], $encryption_key);
$decrypted_prefix_receipt_number = decryptData($_GET['prefix_receipt_number'], $encryption_key);
$decrypted_time_created = decryptData($_GET['time_created'], $encryption_key);
$decrypted_add_payment = decryptData($_GET['add_payment'], $encryption_key);
$decrypted_add_vehicle = decryptData($_GET['add_vehicle'], $encryption_key);

// Signature integration
$decrypted_signature_id = isset($_GET['signature_id']) ? decryptData($_GET['signature_id'], $encryption_key) : null;
$signature_file = null;
if (!empty($decrypted_signature_id)) {
    $stmt = $pdo->prepare("SELECT signature_file FROM signatures WHERE id = ?");
    $stmt->execute([$decrypted_signature_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $signature_file = $row['signature_file'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Firstchoice Superwheels - Sales Receipt - <?php if(isset($decrypted_customer_name)) { echo strtoupper($decrypted_customer_name); }?> - <?php if(isset($decrypted_vehicle_make)) { echo $decrypted_vehicle_make.' '.$decrypted_vehicle_model.' '.$decrypted_vehicle_year; }?></title>

    <style>
        .address {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
            position: relative;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.top table td.title {
            font-size: 30px;
            line-height: 45px;
            color: #333;
        }
        .invoice-box table tr.information table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .invoice-box .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.6;
            pointer-events: none;
            z-index: 0;
        }
        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }
            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }
        .invoice-box.rtl table {
            text-align: right;
        }
        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }
        .customer-name {
            font-size: 1.1em;
            font-family: 'Courier New', Courier, monospace;
            color: black;
            background-color: #e0e0eb;
            padding: 3px;
            border-radius: 3px;
            display: inline-block;
        }
        #print-button {
            display: block;
            margin-top: 20px;
        }
        @media print {
            #print-button {
                display: none;
            }
        }
        tr.space {
            height: 65px;
        }
    </style>
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
                                <b>RECEIPT no:</b> <?= $decrypted_prefix_receipt_number ?><br />
                                <b>Date receipt generated:</b> <?= date('F j, Y, g:i a', strtotime($decrypted_time_created)) ?><br />
                                <b>Payment date:</b> <?= date('F j, Y', strtotime($decrypted_payment_date)) ?><br />
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
                                Plot 10<br />
                                Opposite Osun State Secretariat<br />
                                Abere, Osun State.<br />
                                Phone: +2348066087940<br />
                                Email: info@fcsuperwheels.com<br />
                                Website: www.fcsuperwheels.com
                            </td>
                            <td style="max-width: 200px;">
                                <span class="customer-name">
                                CUSTOMER<br />
                                Name: <b><?= strtoupper($decrypted_customer_name) ?></b><br />
                                Address: <?= $decrypted_customer_address ?><br />
                                Phone: <?= $decrypted_customer_phone ?><br />
                                Email: <?= $decrypted_customer_email ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading"><td>Item information</td><td>Details</td></tr>
            <tr class="item"><td>Name</td><td><?= $decrypted_vehicle_make.' '.$decrypted_vehicle_model.' '.$decrypted_vehicle_year ?></td></tr>
            <?php if (!empty($decrypted_vehicle_price)): ?>
                <tr class="item"><td>Actual Price</td><td>=N= <?= number_format($decrypted_vehicle_price, 2) ?></td></tr>
            <?php endif; ?>
            <tr class="item space"><td>Amount paid</td><td>=N= <?= number_format($decrypted_amount_paid, 2) ?> (<?= ucfirst($decrypted_payment_type) ?> payment)</td></tr>

            <tr class="heading"><td>Vehicle description</td><td>Details</td></tr>
            <tr class="item"><td>Chassis number</td><td><?= $decrypted_vehicle_chasis ?></td></tr>
            <tr class="item"><td>Colour</td><td><?= $decrypted_vehicle_color ?></td></tr>
            <tr class="item"><td>Year</td><td><?= $decrypted_vehicle_year ?></td></tr>
            <?php if (!empty($decrypted_add_vehicle)): ?>
                <tr class="item"><td>Additional vehicle information</td><td><?= $decrypted_add_vehicle ?></td></tr>
            <?php endif; ?>

            <tr class="heading"><td>Payment Information</td><td>Details</td></tr>
            <tr class="item"><td>Payment type</td><td><?= ucfirst($decrypted_payment_type) ?></td></tr>
            <tr class="item"><td>Payment method</td><td><?= $decrypted_payment_method ?></td></tr>
            <?php if (!empty($decrypted_payment_reference)): ?>
                <tr class="item"><td>Reference</td><td><?= $decrypted_payment_reference ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($decrypted_add_payment)): ?>
                <tr class="total"><td>Additional payment information</td><td><?= $decrypted_add_payment ?></td></tr>
            <?php endif; ?>

            <tr class="heading"><td>Total Payment Made</td><td></td></tr>
            <tr class="details">
                <td colspan="2" style="text-align:center;">
                    <h2>=N= <?= number_format($decrypted_amount_paid, 2) ?> - <?= ucfirst($decrypted_payment_type) ?> Payment
                    <?= $decrypted_payment_type === 'installment' ? '(INCOMPLETE)' : '(COMPLETE)' ?></h2>
                </td>
            </tr>
        </table>

        <!-- Signature Display -->
        <?php if (!empty($signature_file)): ?>
            <div style="margin-top: 40px; text-align:right;">
                <p><b>Authorized Signature:</b></p>
                <img src="<?= htmlspecialchars($signature_file) ?>" alt="Signature" style="height:60px;">
            </div>
        <?php endif; ?>

        <!-- Footer -->
        <div class="footer" style="text-align:center; margin-top:30px;">
            <h3>
                <?php if ($decrypted_payment_type === "full"): ?>
                    Thank you for your prompt payment. Your transaction is complete. We appreciate your business.
                <?php else: ?>
                    THANK YOU FOR YOUR INSTALLMENT PAYMENT. YOUR TRANSACTION IS NOT YET COMPLETE. KINDLY COMPLETE REMAINING BALANCE.
                <?php endif; ?>
            </h3>
            <div style="margin-left:46%;">
                <button id="print-button" onclick="window.print()" style="background-color:green; color:white;">Print Receipt</button>
                <a href="dashboard.php"><button id="print-button" style="background-color:red; color:white;">&nbsp;&nbsp;&nbsp; X Close&nbsp;&nbsp;&nbsp;</button></a>
            </div>
        </div>
    </div>
</body>
</html>
