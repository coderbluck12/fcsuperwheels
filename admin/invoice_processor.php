<?php
include_once('inc/functions.php');

// only handle real form POSTS
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('invoice_manager.php');
}
function in($k){ return validate_input($_POST[$k] ?? ''); }

// — collect every form field into $data (minus the unneeded payment_* fields) —
$data = [
    'invoice_date'        => in('invoice_date'),
    'customer_name'       => in('customer_name'),
    'customer_address'    => in('customer_address'),
    'customer_phone'      => in('customer_phone'),
    'customer_email'      => in('customer_email'),
    'vehicle_make'        => in('vehicle_make'),
    'vehicle_model'       => in('vehicle_model'),
    'vehicle_year'        => in('vehicle_year'),
    'vehicle_chasis'      => in('vehicle_chasis'),
    'vehicle_color'       => in('vehicle_color'),
    'quantity'            => (int) in('quantity'),
    'vehicle_price'       => in('vehicle_price'),
    'total_amount'        => in('total_amount'),
    'due_date'            => in('due_date'),
    'payment_instruction' => in('payment_instruction'),
    'add_payment'         => in('add_payment'),
    'add_vehicle'         => in('add_vehicle'),
    'visibility'          => in('visibility'),
    'signature_id'        => (int)($_POST['signature_id'] ?? 0),
];

// Decide: UPDATE or INSERT?
if (!empty($_POST['invoice_id'])) {
    // ————— UPDATE existing invoice —————
    $data['id'] = (int)$_POST['invoice_id'];

    $sql = "UPDATE main_invoice SET
        invoice_date        = :invoice_date,
        customer_name       = :customer_name,
        customer_address    = :customer_address,
        customer_phone      = :customer_phone,
        customer_email      = :customer_email,
        vehicle_make        = :vehicle_make,
        vehicle_model       = :vehicle_model,
        vehicle_year        = :vehicle_year,
        vehicle_chasis      = :vehicle_chasis,
        vehicle_color       = :vehicle_color,
        quantity            = :quantity,
        vehicle_price       = :vehicle_price,
        total_amount        = :total_amount,
        due_date            = :due_date,
        payment_instruction = :payment_instruction,
        add_payment         = :add_payment,
        add_vehicle         = :add_vehicle,
        visibility          = :visibility,
        signature_id        = :signature_id
      WHERE id = :id
    ";
    $op = 'edit-success';

} else {
    // ————— INSERT new invoice —————
    $data['prefix_invoice_number'] = generateUniqueNumeric();
    $data['time_created']          = date('Y-m-d H:i:s');

    $sql = "INSERT INTO main_invoice SET
        invoice_date           = :invoice_date,
        customer_name          = :customer_name,
        customer_address       = :customer_address,
        customer_phone         = :customer_phone,
        customer_email         = :customer_email,
        vehicle_make           = :vehicle_make,
        vehicle_model          = :vehicle_model,
        vehicle_year           = :vehicle_year,
        vehicle_chasis         = :vehicle_chasis,
        vehicle_color          = :vehicle_color,
        quantity               = :quantity,
        vehicle_price          = :vehicle_price,
        total_amount           = :total_amount,
        due_date               = :due_date,
        payment_instruction    = :payment_instruction,
        add_payment            = :add_payment,
        add_vehicle            = :add_vehicle,
        visibility             = :visibility,
        prefix_invoice_number  = :prefix_invoice_number,
        time_created           = :time_created,
        signature_id           = :signature_id
    ";
    $op = 'create-success';
}

// prepare, bind and execute
$stmt = $pdo->prepare($sql);
foreach ($data as $k => $v) {
    $stmt->bindValue(":$k", $v);
}
$stmt->execute();

// figure out the PK
if (empty($_POST['invoice_id'])) {
    $pk = $pdo->lastInsertId();
} else {
    $pk = $data['id'];
}

// redirect with just one GET param
$key = encryptData($pk, "31081990");
$enc = urlencode($key);
redirect_to("view_invoice.php?prefix_invoice_number={$enc}&{$op}");

