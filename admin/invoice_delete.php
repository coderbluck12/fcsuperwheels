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

// 3) Delete from database
$stmt = $pdo->prepare("DELETE FROM main_invoice WHERE id = ?");
$stmt->execute([$id]);

// 4) Redirect back with success flag
redirect_to('invoice_manager.php?delete-success');
