<?php
include_once('inc/functions.php');
try {
    $stmt = $pdo->query("SELECT id, status, sale_price, sale_date FROM vehicles WHERE status = 'Sold'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($results)) {
        echo "No sold vehicles found in database.\n";
    } else {
        foreach ($results as $row) {
            echo "ID: " . $row['id'] . " | Status: " . $row['status'] . " | Price: " . $row['sale_price'] . " | Date: " . $row['sale_date'] . "\n";
        }
    }
    
    $stmt2 = $pdo->query("SELECT DISTINCT status FROM vehicles");
    echo "\nAll available statuses in DB:\n";
    while($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        echo "- " . $row['status'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
