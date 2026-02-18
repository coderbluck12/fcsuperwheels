<!DOCTYPE html>
<html>
<head>
<title>Edit Receipt</title>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    function validateNumbers() {
        var vehicle_price = parseFloat(document.getElementById("vehicle_price").value);
        var amount_paid = parseFloat(document.getElementById("amount_paid").value);
        var payment_type = document.getElementById("payment_type").value;

        if (amount_paid > vehicle_price) {
            alert("The amount paid cannot be greater than the actual price");
            return false;
        } else if(payment_type === "full" && amount_paid < vehicle_price){
            alert("The amount paid cannot be lesser than vehicle price for a full payment, change the payment type if this is installment payment");
            return false;
        }
        return true;
    }
</script>
</head>
<body>

<?php
include_once('inc/functions.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $pdo->prepare("UPDATE main_receipt SET customer_name=?, customer_address=?, customer_phone=?, customer_email=?, vehicle_make=?, vehicle_model=?, vehicle_year=?, vehicle_chasis=?, vehicle_color=?, vehicle_price=?, payment_type=?, payment_method=?, payment_reference=?, amount_paid=?, payment_date=?, add_payment=?, add_vehicle=?, signature_id=? WHERE id=?");

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
        !empty($_POST['signature_id']) ? $_POST['signature_id'] : null,
        $_POST['receipt_id']
    ];

    if ($stmt->execute($params)) {
        redirect_to('all_receipts.php?success-edit');
    } else {
        echo "Error updating receipt: " . $stmt->errorInfo()[2];
    }

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
    $selected_signature_id = $row['signature_id'];

    $signatures = $pdo->query("SELECT id, signature_name FROM signatures ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<h2>Edit Receipt</h2>

<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateNumbers()">
    <fieldset>
    <legend>Customer Information</legend>
    Customer Fullname *:  <input type="text" name="customer_name" value="<?php echo $customer_name; ?>" required /><br />
    Customer Address *:  <textarea name="customer_address" required><?php echo $customer_address; ?></textarea><br />
    Customer Phone number *:  <input type="text" name="customer_phone" value="<?php echo $customer_phone; ?>" required /><br />
    Customer Email (optional):  <input type="email" name="customer_email" value="<?php echo $customer_email; ?>" /><br />
    </fieldset>

    <br /><br />
    <fieldset>
    <legend>Vehicle Information</legend>
    Make *: <input type="text" name="vehicle_make" value="<?php echo $vehicle_make; ?>" required /><br />
    Model *:  <input type="text" name="vehicle_model" value="<?php echo $vehicle_model; ?>" required><br />
    Year *:  <input type="text" name="vehicle_year" value="<?php echo $vehicle_year; ?>" required /><br />
    Chasis no *: <input type="text" name="vehicle_chasis" value="<?php echo $vehicle_chasis; ?>" required /><br />
    Color *: <input type="text" name="vehicle_color" value="<?php echo $vehicle_color; ?>" required /><br />
    Actual Vehicle Price (=N=) (optional):  <input type="number" id="vehicle_price" name="vehicle_price" value="<?php echo $vehicle_price; ?>" /><br />
    Additional Vehicle information (optional):  <input type="text" name="add_vehicle" value="<?php echo $add_vehicle; ?>" /><br />
    </fieldset>

    <br /><br />
    <fieldset>
    <legend>Transaction details</legend>
    Payment type *: 
    <select id="payment_type" name="payment_type" required>
        <option value="" disabled hidden>Select payment type</option>
        <option value="full" <?php if($payment_type == "full") echo "selected"; ?>>Full payment</option>
        <option value="installment" <?php if($payment_type == "installment") echo "selected"; ?>>Installment payment</option>
    </select><br />
    Payment method *:  <input type="text" name="payment_method" value="<?php echo $payment_method; ?>" required /><br />
    Payment reference (optional):  <input type="text" name="payment_reference" value="<?php echo $payment_reference; ?>" /><br />
    Amount paid (=N=) *: <input type="number" id="amount_paid" name="amount_paid" value="<?php echo $amount_paid; ?>" required /><br />
    Date of payment *:  <input type="date" name="payment_date" value="<?php echo $payment_date; ?>" required /><br />
    Additional Payment information (optional):  <input type="text" name="add_payment" value="<?php echo $add_payment; ?>" /><br />
    Authorized Signature (optional): 
    <select name="signature_id">
        <option value="">-- Select signature --</option>
        <?php foreach ($signatures as $sig): ?>
            <option value="<?= $sig['id'] ?>" <?= ($sig['id'] == $selected_signature_id ? 'selected' : '') ?>>
                <?= htmlspecialchars($sig['signature_name']) ?>
            </option>
        <?php endforeach; ?>
    </select><br />
    <input type="hidden" name="receipt_id" value="<?php echo $receipt_id; ?>">
    </fieldset>

    <br />
    <input type="submit" name="receipt_submit" value="Update Receipt">
</form>

</body>
</html>
