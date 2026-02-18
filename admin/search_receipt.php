<?php
// search_receipt.php
include_once('inc/session_manager.php');
include_once('inc/functions.php');    // for encryptData()

$key = "31081990";

$term = trim($_GET['term'] ?? '');
if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id,
               CONCAT(prefix_receipt_number, id) AS receipt_no,
               customer_name
        FROM main_receipt
        WHERE visibility='yes'
          AND (CONCAT(prefix_receipt_number, id) LIKE :t OR customer_name LIKE :t)
        ORDER BY time_created DESC
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([':t'=>"%{$term}%"]);

$results = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $enc = encryptData($row['id'], $key);
    $results[] = [
      'label' => "{$row['receipt_no']} — {$row['customer_name']}",
      'value' => $row['receipt_no'],
      'id'    => $row['id'],
      'enc'   => $enc
    ];
}
echo json_encode($results);
