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


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Firstchoice Superwheels - Sales Receipt - <?php if(isset($decrypted_customer_name)) { echo strtoupper($decrypted_customer_name); }?> - <?php if(isset($decrypted_vehicle_make)) { echo $decrypted_vehicle_make.' '.$decrypted_vehicle_model.' '.$decrypted_vehicle_year; }?></title>

    <style>
		/* Define your CSS styles here */
        .address {
            /* Allow long words to break and wrap onto the next line */
            word-wrap: break-word; /* For older browsers */
            overflow-wrap: break-word; /* For newer browsers */
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
            position: relative; /* Added */
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
            opacity: 0.6; /* Adjust opacity for fading */
            pointer-events: none;
            z-index: 0; /* Ensure the watermark is behind the content */
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

        /** RTL **/
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
		/* CSS styling for better presentation */
        .customer-name {
            font-size: 1.1em;
			font-family: 'Courier New', Courier, monospace;
            color: black; /* Change color as needed */
            background-color: #e0e0eb; /* Change background color as needed */
            padding: 3px;
            border-radius: 3px;
            display: inline-block; /* Ensure the background color only covers the text */
        }
		#print-button {
		display: block;
		margin-top: 20px;
		}

		@media print {
			/* Hide the print button when printing */
			#print-button {
				display: none;
			}
		}
		
		tr.space {
            height: 65px; /* Adjust the height as needed */
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <!-- Watermark -->
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
                                <b>RECEIPT no:</b> <?php if(isset($decrypted_prefix_receipt_number)) { echo $decrypted_prefix_receipt_number; }?><br />
                                <b>Date receipt generated:</b> <?php if(isset($decrypted_time_created)) { echo date('F j, Y, g:i a', strtotime($decrypted_time_created)); }?><br />
                                <b>Payment date:</b> <?php if(isset($decrypted_payment_date)) { echo date('F j, Y', strtotime($decrypted_payment_date)); }?><br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td style="border:1;">
                                Firstchoice Superwheels<br />
                                <b>RC NO: 7378695</b><br />
                                Plot 10, Opposite Osun State Secretariat<br />
                                Abere, Osun State.<br />
                                Phone: +2347016754887<br />
                                Email: info@fcsuperwheels.com <br />
                                Website: www.fcsuperwheels.com
                            </td>

                            <td style="max-width: 200px; word-wrap: break-word; /* For older browsers */
            overflow-wrap: break-word; /* For newer browsers */">
                                <a class="customer-name">CUSTOMER<br />
                                Name:<b><?php if(isset($decrypted_customer_name)) { echo strtoupper($decrypted_customer_name); }?></b><br />
                                Address:<?php if(isset($decrypted_customer_address)) { echo $decrypted_customer_address; }?><br />
                                <?php if(isset($decrypted_customer_phone)) { echo 'Phone: '.$decrypted_customer_phone.'<br />'; }?>
                                <?php if(isset($decrypted_customer_email)) { echo 'Email: '.$decrypted_customer_email; }?></a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <!-- Invoice items and details -->
            <tr class="heading">
                <td>Item information</td>
                <td>Details</td>
                
            </tr>

            <tr class="item">
				<td>Name</td>
                <td><?php if(isset($decrypted_vehicle_make)) { echo $decrypted_vehicle_make.' '.$decrypted_vehicle_model.' '.$decrypted_vehicle_year; }?></td>
            </tr>
			<?php if(isset($decrypted_vehicle_price)&&!empty($decrypted_vehicle_price)) { echo '<tr class="item">
                <td>Actual Price</td>
                <td>=N= '.number_format($decrypted_vehicle_price, 2, '.', ',').'</td>
            </tr>'; }
			?>
			
            <tr class="item space">
                <td>Amount paid</td>
				<td>=N= <?php if(isset($decrypted_amount_paid)) { echo number_format($decrypted_amount_paid, 2, '.', ',').' ('.ucfirst($decrypted_payment_type); } ?> payment)</td>
            </tr>
			


            <!-- Vehicle description -->
            <tr class="heading">
                <td>Vehicle description</td>
                <td>Details</td>
            </tr>

            <tr class="item">
                <td>Chassis number</td>
                <td><?php if(isset($decrypted_vehicle_chasis)) { echo $decrypted_vehicle_chasis; }?></td>
            </tr>
            <tr class="item">
                <td>Colour</td>
                <td><?php if(isset($decrypted_vehicle_color)) { echo $decrypted_vehicle_color; }?></td>
            </tr>
			<?php if(isset($decrypted_add_vehicle)&&!empty($decrypted_add_vehicle)) { echo '<tr class="item">
                <td>Year</td>
                <td>'.$decrypted_vehicle_year.'</td>
				<tr class="item">
                <td>Additional vehicle informaion</td>
                <td>'.$decrypted_add_vehicle.'</td>
            </tr>'; }
			else
			{
				echo '<tr class="item">
                <td>Year</td>
                <td>'.$decrypted_vehicle_year.'</td>
            </tr>';
			}
			?>
<tr class="item space">
            <!-- Payment Method -->
            <tr class="heading">
                <td>Payment Information</td>
				<td>Details</td>
            </tr>

            <tr class="item">
				<td>Payment type</td>
				<td><?php if(isset($decrypted_payment_type)) { echo ucfirst($decrypted_payment_type); }?></td>
            </tr>
			<tr class="item">
				<td>Payment method</td>
				<td><?php if(isset($decrypted_payment_method)) { echo $decrypted_payment_method; }?></td>
            </tr>
			<?php if(isset($decrypted_payment_reference)&&!empty($decrypted_payment_reference)) { echo '<tr class="item">
                <td>Reference</td>
                <td>'.$decrypted_payment_reference.'</td>
            </tr>'; }
			?>
			<?php if(isset($decrypted_add_payment)&&!empty($decrypted_add_payment)) { echo '<tr class="total">
                <td>Additional payment informaion</td>
                <td>'.$decrypted_add_payment.'</td>
            </tr>'; }
			?>
			<tr class="item space">
			</tr>

            <!-- Total Payment Made -->
            <tr class="heading">
                <td>Total Payment Made</td>
                <td></td>
            </tr>

            <tr class="details">
                <td colspan="2"><h2 style="text-align:center;">=N= <?php if(isset($decrypted_amount_paid)) { echo number_format($decrypted_amount_paid, 2, '.', ','); }?> <?php //convertAmountToWords(1234.00); ?>  - <?php if(isset($decrypted_payment_type)) { echo ucfirst($decrypted_payment_type); }?> Payment <?php if($decrypted_payment_type=="installment") echo '(INCOMPLETE)'; else echo '(COMPLETE)'; ?></h2></td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer" style="text-alin:text-center;">
            <h3 style="text-align:center;">
               <?php
			   if($decrypted_payment_type=="full")
			   {
					echo 'Thank you for your prompt payment. Your transaction is complete. We appreciate your business.';
			   }
			   elseif($decrypted_payment_type=="installment")
			   {
					echo strtoupper('Thank you for your installment payment. Please note that your transaction is not yet complete. Kindly ensure timely completion of all installments. Thank you for choosing us.');
			   }
			   ?>
            </h3>
			<!-- Print button -->
    <div style="margin-left:46%;"><button id="print-button" onclick="window.print()" style="background-color:green; color:white; text-decoration:none;">Print Receipt</button>
    <a href="master_view_all_receipts.php" style="text-decoration:none;"><button id="print-button" style="background-color:red; color:white; text-decoration:none;">&nbsp&nbsp&nbsp X Close&nbsp&nbsp&nbsp</button></a>
	</div>
        </div>
    </div>
</body>
</html>
