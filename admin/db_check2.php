<?php
require_once __DIR__ . '/inc/functions.php';
global $pdo;

echo "--- DESCRIBE main_invoice ---\n";
$stmt = $pdo->query("DESCRIBE main_invoice");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "--- DESCRIBE main_receipt ---\n";
$stmt = $pdo->query("DESCRIBE main_receipt");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
