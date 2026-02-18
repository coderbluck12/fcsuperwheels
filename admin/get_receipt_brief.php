<?php
// get_receipt_brief.php
include_once('inc/session_manager.php');
include_once('inc/functions.php');

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['error'=>'Invalid ID']); exit; }

$sql = "SELECT id, prefix_receipt_number, customer_name, amount_paid, payment_type, DATE(time_created) created
        FROM main_receipt
        WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id'=>$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
    echo json_encode(['error'=>'Receipt not found']);
    exit;
}

echo json_encode([
  'receipt_no'=> $row['prefix_receipt_number'].$row['id'],
  'customer'  => $row['customer_name'],
  'amount'    => number_format($row['amount_paid'],2),
  'type'      => ucfirst($row['payment_type']),
  'created'   => $row['created']
]);
