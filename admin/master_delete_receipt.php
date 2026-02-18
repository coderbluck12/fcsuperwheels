<?php
// Include your database connection file
include_once('inc/functions.php');

// Check if the prefix_receipt_number is provided via GET method
if(isset($_GET['prefix_receipt_number']) && !empty($_GET['prefix_receipt_number'])) {
    // Sanitize the prefix_receipt_number to prevent SQL injection
    $prefix_receipt_number = htmlspecialchars($_GET['prefix_receipt_number']);
	$encryption_key = "31081990";
	$prefix_receipt_number = decryptData($prefix_receipt_number, $encryption_key);

    // Prepare a delete statement
    $sql = "DELETE FROM main_receipt WHERE id = :prefix_receipt_number";

    try {
        // Prepare the SQL statement
        $stmt = $pdo->prepare($sql);

        // Bind the prefix_receipt_number parameter
        $stmt->bindParam(':prefix_receipt_number', $prefix_receipt_number, PDO::PARAM_STR);

        // Execute the delete statement
        if($stmt->execute()) {
            // Delete successful
            redirect_to('master_view_all_receipts.php?delete-success');
			//echo $prefix_receipt_number;
        } else {
            // Delete failed
            redirect_to('all_receipts.php?delete-fail');
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "Error: " . $e->getMessage();
    }

    // Close the database connection
    $pdo = null;
} else {
    // If prefix_receipt_number is not provided or empty, display an error message
    echo "Prefix receipt number is missing.";
}
?>
