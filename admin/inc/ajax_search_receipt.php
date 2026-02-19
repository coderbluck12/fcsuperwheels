<?php
// Prevent any previous output or whitespace from corrupting the JSON
ob_start();

// Check these paths carefully! 
// If these files don't exist, PHP will echo an error, breaking your JSON.
include_once('functions.php'); 
// Assuming $pdo is defined in functions.php

// Clear the buffer in case an include triggered a warning
ob_clean(); 

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Note: Ensure $pdo is actually available here from your includes
    $stmt = $pdo->prepare("
        SELECT id, prefix_receipt_number, customer_name, vehicle_make, vehicle_model 
        FROM main_receipt 
        WHERE (customer_name LIKE :q 
           OR CAST(prefix_receipt_number AS CHAR) LIKE :q)
        AND visibility = 'yes'
        ORDER BY id DESC 
        LIMIT 10
    ");
    
    $stmt->execute([':q' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $encryption_key = "31081990"; 
    $formatted_results = [];

    foreach ($results as $row) {
        $encrypted_id = encryptData($row['id'], $encryption_key);
        
        $formatted_results[] = [
            'encrypted_id'          => $encrypted_id,
            'prefix_receipt_number' => $row['prefix_receipt_number'],
            'customer_name'         => $row['customer_name'],
            'vehicle'               => $row['vehicle_make'] . ' ' . $row['vehicle_model']
        ];
    }

    echo json_encode($formatted_results);

} catch (Exception $e) {
    // If it fails, send a valid JSON error instead of an HTML one
    echo json_encode(['error' => $e->getMessage()]);
}
exit;