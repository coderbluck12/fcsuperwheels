<?php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

// 1) Require the encrypted ID
if (!isset($_GET['prefix_invoice_number'])) {
    exit('Invoice ID not provided.');
}

// 2) Decrypt it
$key        = "31081990";
$encrypted  = $_GET['prefix_invoice_number'];
$id         = decryptData($encrypted, $key);

// 3) Set visibility = 'yes'
$stmt = $pdo->prepare("UPDATE main_invoice SET visibility = 'yes' WHERE id = ?");
$stmt->execute([$id]);

// 4) Redirect back with update flag
redirect_to('invoice_manager.php?update-success');
