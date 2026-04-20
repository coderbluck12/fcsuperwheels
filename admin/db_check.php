<?php
require_once __DIR__ . '/inc/functions.php';
global $pdo;

echo "--- main_invoice ---\n";
$stmt = $pdo->query("SHOW CREATE TABLE main_invoice");
print_r($stmt->fetch());

echo "--- main_receipt ---\n";
$stmt = $pdo->query("SHOW CREATE TABLE main_receipt");
print_r($stmt->fetch());
