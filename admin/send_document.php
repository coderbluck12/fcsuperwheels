<?php
include_once('inc/functions.php');
include_once('inc/session_manager.php');

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    exit('Invalid request parameters');
}

$type = $_GET['type'];
$encrypted_id = $_GET['id'];
$key = "31081990";
$id = decryptData($encrypted_id, $key);

if ($type === 'invoice') {
    $stmt = $pdo->prepare("SELECT * FROM main_invoice WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$record) exit("Invoice not found.");
    
    $customer_name = $record['customer_name'];
    $customer_email = $record['customer_email'] ?? '';
    $ref_num = $record['prefix_invoice_number'];
    $subject = "Invoice $ref_num from Firstchoice Superwheels";
    $amount = "=N=" . number_format($record['total_amount'], 2);
    $items = json_decode($record['items_json'] ?? '', true);
    $back_url = "view_invoice.php?prefix_invoice_number=" . urlencode($encrypted_id);
} else if ($type === 'receipt') {
    $stmt = $pdo->prepare("SELECT * FROM main_receipt WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$record) exit("Receipt not found.");
    
    $customer_name = $record['customer_name'];
    $customer_email = $record['customer_email'] ?? '';
    $ref_num = $record['prefix_receipt_number'];
    $subject = "Payment Receipt $ref_num from Firstchoice Superwheels";
    $amount = "=N=" . number_format($record['amount_paid'], 2);
    $items = json_decode($record['items_json'] ?? '', true);
    $back_url = "view_receipt.php?prefix_receipt_number=" . urlencode($encrypted_id);
} else {
    exit('Invalid document type');
}

if (empty($customer_email) || filter_var($customer_email, FILTER_VALIDATE_EMAIL) === false) {
    echo "<script>alert('No valid email address found for this customer.\\nPlease update the record first.'); window.location.href='$back_url';</script>";
    exit();
}

// Build HTML email
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<style>
  body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; }
  .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
  h2 { color: #0056b3; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  table th, table td { border: 1px solid #ddd; padding: 10px; text-align: left; }
  table th { background-color: #f5f5f5; }
  .footer { margin-top: 30px; font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
  .brand { font-weight: bold; font-size: 18px; color: #0056b3; }
</style>
</head>
<body>
<div class="container">
    <div class="brand">Firstchoice Superwheels</div>
    <h2><?= ucfirst($type) ?> Details (Ref: <?= htmlspecialchars($ref_num) ?>)</h2>
    <p>Dear <?= htmlspecialchars($customer_name) ?>,</p>
    <p>Please find the summary of your <?= $type ?> below:</p>
    
    <?php if (!empty($items) && is_array($items)): ?>
        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Color</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $idx => $it): ?>
                <tr>
                    <td><?= htmlspecialchars(trim(($it['make']??'')." ".($it['model']??'')." ".($it['year']??''))) ?></td>
                    <td><?= htmlspecialchars($it['color']??'') ?></td>
                    <td>=N= <?= number_format((float)($it['price']??0), 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <table>
            <tr>
                <th>Vehicle</th>
                <td><?= htmlspecialchars($record['vehicle_make'] . ' ' . $record['vehicle_model'] . ' ' . $record['vehicle_year']) ?></td>
            </tr>
            <tr>
                <th>Color</th>
                <td><?= htmlspecialchars($record['vehicle_color'] ?? '') ?></td>
            </tr>
        </table>
    <?php endif; ?>
    
    <p style="font-size: 1.2em; font-weight: bold; margin-top: 20px;">
        Total Amount <?= $type==='receipt'?'Paid':'' ?>: <?= $amount ?>
    </p>

    <div class="footer">
        Firstchoice Superwheels<br>
        Plot 10, Opposite Osun State Secretariat, Abere, Osun State.<br>
        Phone: +234 701 675 4887 | Email: info@fcsuperwheels.com<br>
        <em>If you require the fully signed & stamped official document, please reply to this email.</em>
    </div>
</div>
</body>
</html>
<?php
$message = ob_get_clean();

// Headers for HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: FirstChoice Superwheels <info@fcsuperwheels.com>" . "\r\n";

if (mail($customer_email, $subject, $message, $headers)) {
    echo "<script>alert('Document successfully sent to $customer_email'); window.location.href='$back_url';</script>";
} else {
    echo "<script>alert('Failed to send email. Please check your mail server configuration.'); window.location.href='$back_url';</script>";
}
