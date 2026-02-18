<?php
include_once('inc/functions.php');

if (isset($_POST['receipt_submit'])) {
    // Sanitize inputs
    $customer_name     = validate_input($_POST['customer_name']);
    $customer_address  = validate_input($_POST['customer_address']);
    $customer_phone    = validate_input($_POST['customer_phone']);
    $customer_email    = validate_input($_POST['customer_email']);
    $vehicle_make      = validate_input($_POST['vehicle_make']);
    $vehicle_model     = validate_input($_POST['vehicle_model']);
    $vehicle_year      = validate_input($_POST['vehicle_year']);
    $vehicle_chasis    = validate_input($_POST['vehicle_chasis']);
    $vehicle_color     = validate_input($_POST['vehicle_color']);
    $vehicle_price     = validate_input($_POST['vehicle_price']);
    $payment_type      = validate_input($_POST['payment_type']);
    $payment_method    = validate_input($_POST['payment_method']);
    $payment_reference = validate_input($_POST['payment_reference']);
    $amount_paid       = validate_input($_POST['amount_paid']);
    $payment_date      = validate_input($_POST['payment_date']);
    $add_vehicle       = validate_input($_POST['add_vehicle']);
    $add_payment       = validate_input($_POST['add_payment']);
    $signature_id      = !empty($_POST['signature_id']) ? (int)$_POST['signature_id'] : null;

    $prefix_receipt_number = generateUniqueNumeric();
    $time_created = date('Y-m-d H:i:s');

    // Prepare SQL
    $stmt = $pdo->prepare("INSERT INTO main_receipt SET 
        customer_name = :customer_name, 
        customer_address = :customer_address,
        customer_phone = :customer_phone,
        customer_email = :customer_email, 
        vehicle_make = :vehicle_make, 
        vehicle_model = :vehicle_model, 
        vehicle_year = :vehicle_year, 
        vehicle_chasis = :vehicle_chasis, 
        vehicle_color = :vehicle_color, 
        vehicle_price = :vehicle_price, 
        payment_type = :payment_type, 
        payment_method = :payment_method, 
        payment_reference = :payment_reference, 
        amount_paid = :amount_paid, 
        payment_date = :payment_date, 
        prefix_receipt_number = :prefix_receipt_number,
        time_created = :time_created,
        add_payment = :add_payment,
        add_vehicle = :add_vehicle,
        signature_id = :signature_id
    ");

    // Bind parameters
    $stmt->bindParam(':customer_name', $customer_name);
    $stmt->bindParam(':customer_address', $customer_address);
    $stmt->bindParam(':customer_phone', $customer_phone);
    $stmt->bindParam(':customer_email', $customer_email);
    $stmt->bindParam(':vehicle_make', $vehicle_make);
    $stmt->bindParam(':vehicle_model', $vehicle_model);
    $stmt->bindParam(':vehicle_year', $vehicle_year);
    $stmt->bindParam(':vehicle_chasis', $vehicle_chasis);
    $stmt->bindParam(':vehicle_color', $vehicle_color);
    $stmt->bindParam(':vehicle_price', $vehicle_price);
    $stmt->bindParam(':payment_type', $payment_type);
    $stmt->bindParam(':payment_method', $payment_method);
    $stmt->bindParam(':payment_reference', $payment_reference);
    $stmt->bindParam(':amount_paid', $amount_paid);
    $stmt->bindParam(':payment_date', $payment_date);
    $stmt->bindParam(':prefix_receipt_number', $prefix_receipt_number);
    $stmt->bindParam(':time_created', $time_created);
    $stmt->bindParam(':add_payment', $add_payment);
    $stmt->bindParam(':add_vehicle', $add_vehicle);

    if (is_null($signature_id)) {
        $stmt->bindValue(':signature_id', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':signature_id', $signature_id, PDO::PARAM_INT);
    }

    $stmt->execute();
    $last_inserted_id = $pdo->lastInsertId();

    if ($stmt->rowCount() > 0) {
        $encryption_key = "31081990";
        $encrypted_id = encryptData($last_inserted_id, $encryption_key);
        redirect_to('view_receipt.php?prefix_receipt_number=' . urlencode($encrypted_id));
    } else {
        redirect_to('index.html?fail');
    }
} else {
    redirect_to('index.html');
}
?>
