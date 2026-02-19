<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// Fetch available signatures
$sigStmt = $pdo->query("SELECT id, signature_name, signature_file FROM signatures ORDER BY created_at DESC");
$signatures = $sigStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE main_receipt SET customer_name=?, customer_address=?, customer_phone=?, customer_email=?, vehicle_make=?, vehicle_model=?, vehicle_year=?, vehicle_chasis=?, vehicle_color=?, vehicle_price=?, payment_type=?, payment_method=?, payment_reference=?, amount_paid=?, payment_date=?, add_payment=?, add_vehicle=?, signature_id=? WHERE id=?");

    $signature_id = $_POST['signature_id'] === '' ? null : $_POST['signature_id'];

    $params = [
        $_POST['customer_name'],
        $_POST['customer_address'],
        $_POST['customer_phone'],
        $_POST['customer_email'],
        $_POST['vehicle_make'],
        $_POST['vehicle_model'],
        $_POST['vehicle_year'],
        $_POST['vehicle_chasis'],
        $_POST['vehicle_color'],
        $_POST['vehicle_price'],
        $_POST['payment_type'],
        $_POST['payment_method'],
        $_POST['payment_reference'],
        $_POST['amount_paid'],
        $_POST['payment_date'],
        $_POST['add_payment'],
        $_POST['add_vehicle'],
        $signature_id,
        $_POST['receipt_id']
    ];

    if ($stmt->execute($params)) {
        //redirect_to('view_all_receipts.php?edit-success');
		redirect_to('view_receipt.php?prefix_receipt_number=' . urlencode(encryptData($_POST['receipt_id'], '31081990')) . '&edit-success');

    } else {
        echo "Error updating receipt: " . $stmt->errorInfo()[2];
    }

    $stmt = null;
    $pdo = null;
} else {
    $receipt_id = decryptData($_GET['prefix_receipt_number'], "31081990");
    $stmt = $pdo->prepare("SELECT * FROM main_receipt WHERE id = ?");
    $stmt->execute([$receipt_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $customer_name = $row['customer_name'];
    $customer_address = $row['customer_address'];
    $customer_phone = $row['customer_phone'];
    $customer_email = $row['customer_email'];
    $vehicle_make = $row['vehicle_make'];
    $vehicle_model = $row['vehicle_model'];
    $vehicle_year = $row['vehicle_year'];
    $vehicle_chasis = $row['vehicle_chasis'];
    $vehicle_color = $row['vehicle_color'];
    $vehicle_price = $row['vehicle_price'];
    $payment_type = $row['payment_type'];
    $payment_method = $row['payment_method'];
    $payment_reference = $row['payment_reference'];
    $amount_paid = $row['amount_paid'];
    $payment_date = date('Y-m-d', strtotime($row['payment_date']));
    $prefix_receipt_number = $row['prefix_receipt_number'];
    $add_payment = $row['add_payment'];
    $add_vehicle = $row['add_vehicle'];
    $current_signature_id = $row['signature_id'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Receipt</title>
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <style>
        .signature-preview { height: 60px; border: 1px solid #ccc; padding: 2px; }
    </style>
</head>
<body>
<div class="page-wrapper">
    <?php include_once('inc/header.php'); ?>
    <div class="page-content--bgf7">
        <div class="container mt-4">
            <h3>Edit Receipt</h3>
			 <a href="dashboard.php" class="btn btn-sm btn-primary mb-3">← Back to Dashboard</a>
            <form method="POST" action="" onsubmit="return validateNumbers()">
                <fieldset class="form-group">
                    <legend>Customer Information</legend>
                    <input type="text" name="customer_name" class="form-control mb-2" placeholder="Customer Fullname *" value="<?= htmlspecialchars($customer_name) ?>" required>
                    <textarea name="customer_address" class="form-control mb-2" placeholder="Customer Address *" required><?= htmlspecialchars($customer_address) ?></textarea>
                    <input type="text" name="customer_phone" class="form-control mb-2" placeholder="Customer Phone Number *" value="<?= htmlspecialchars($customer_phone) ?>" required>
                    <input type="email" name="customer_email" class="form-control mb-2" placeholder="Customer Email (optional)" value="<?= htmlspecialchars($customer_email) ?>">
                </fieldset>

                <fieldset class="form-group">
                    <legend>Vehicle Information</legend>
                    <input type="text" name="vehicle_make" class="form-control mb-2" placeholder="Make *" value="<?= htmlspecialchars($vehicle_make) ?>" required>
                    <input type="text" name="vehicle_model" class="form-control mb-2" placeholder="Model *" value="<?= htmlspecialchars($vehicle_model) ?>" required>
                    <input type="text" name="vehicle_year" class="form-control mb-2" placeholder="Year *" value="<?= htmlspecialchars($vehicle_year) ?>" required>
                    <input type="text" name="vehicle_chasis" class="form-control mb-2" placeholder="Chassis No *" value="<?= htmlspecialchars($vehicle_chasis) ?>" required>
                    <input type="text" name="vehicle_color" class="form-control mb-2" placeholder="Color *" value="<?= htmlspecialchars($vehicle_color) ?>" required>
                    <input type="number" name="vehicle_price" id="vehicle_price" class="form-control mb-2" placeholder="Vehicle Price (optional)" value="<?= ($vehicle_price !== null && $vehicle_price != 0) ? htmlspecialchars($vehicle_price) : '' ?>">
                    <input type="text" name="add_vehicle" class="form-control mb-2" placeholder="Additional Vehicle Info (optional)" value="<?= htmlspecialchars($add_vehicle) ?>">
                </fieldset>

                <fieldset class="form-group">
                    <legend>Payment Information</legend>
                    <select name="payment_type" id="payment_type" class="form-control mb-2" required>
                        <option value="">Select Payment Type</option>
                        <option value="full" <?= $payment_type == 'full' ? 'selected' : '' ?>>Full</option>
                        <option value="installment" <?= $payment_type == 'installment' ? 'selected' : '' ?>>Installment</option>
                    </select>
                    <input type="text" name="payment_method" class="form-control mb-2" placeholder="Payment Method *" value="<?= htmlspecialchars($payment_method) ?>" required>
                    <input type="text" name="payment_reference" class="form-control mb-2" placeholder="Payment Reference (optional)" value="<?= htmlspecialchars($payment_reference) ?>">
                    <input type="number" name="amount_paid" id="amount_paid" class="form-control mb-2" placeholder="Amount Paid (=N=) *" value="<?= htmlspecialchars($amount_paid) ?>" required>
                    <input type="date" name="payment_date" class="form-control mb-2" value="<?= $payment_date ?>" required>
                    <input type="text" name="add_payment" class="form-control mb-2" placeholder="Additional Payment Info (optional)" value="<?= htmlspecialchars($add_payment) ?>">
                </fieldset>

                <fieldset class="form-group">
                    <legend>Authorized Signature (optional)</legend>
                    <select name="signature_id" id="signature_id" class="form-control mb-2" onchange="updateSignaturePreview()">
                        <option value="">-- No Signature --</option>
                        <?php foreach ($signatures as $sig): ?>
                            <option value="<?= $sig['id'] ?>" <?= $sig['id'] == $current_signature_id ? 'selected' : '' ?> data-src="<?= $sig['signature_file'] ?>">
                                <?= htmlspecialchars($sig['signature_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="sig-preview" class="mt-2" style="display: <?= $current_signature_id ? 'block' : 'none' ?>;">
                        <label>Signature Preview:</label><br>
                        <img id="sig-img" src="<?php
                            foreach ($signatures as $sig) {
                                if ($sig['id'] == $current_signature_id) {
                                    echo $sig['signature_file'];
                                    break;
                                }
                            }
                        ?>" alt="Signature" class="signature-preview">
                    </div>
                </fieldset>

                <input type="hidden" name="receipt_id" value="<?= $receipt_id ?>">
                <input type="submit" name="receipt_submit" class="btn btn-success" value="Update Receipt">
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
function validateNumbers() {
    var vehicle_price = parseFloat(document.getElementById("vehicle_price").value);
    var amount_paid = parseFloat(document.getElementById("amount_paid").value);
    var payment_type = document.getElementById("payment_type").value;

    if (!isNaN(vehicle_price) && amount_paid > vehicle_price) {
        alert("The amount paid cannot be greater than the actual price");
        return false;
    } else if (payment_type === "full" && !isNaN(vehicle_price) && amount_paid < vehicle_price) {
        alert("The amount paid cannot be less than the vehicle price for full payment");
        return false;
    } else {
        return true;
    }
}

function updateSignaturePreview() {
    const select = document.getElementById('signature_id');
    const selected = select.options[select.selectedIndex];
    const src = selected.getAttribute('data-src');
    const preview = document.getElementById('sig-preview');
    const img = document.getElementById('sig-img');

    if (src) {
        preview.style.display = 'block';
        img.src = src;
    } else {
        preview.style.display = 'none';
        img.src = '';
    }
}
</script>
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
</body>
</html>
