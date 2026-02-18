<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>List of Receipts</title>
</head>
<body>
    <h1>List of Receipts</h1>
    <table border="1">
        <tr>
            <th>Receipt Number</th>
            <th>Customer Name</th>
            <th>Vehicle</th>
            <th>Payment Type</th>
            <th>Date generated</th>
            <th>Payment date</th>
            <th>Amount paid (=N=)</th>
            <th>Actions</th>
        </tr>
        <?php
        // Connect to your database
        include_once('inc/functions.php');
        
        // Fetch receipt data from the database
        $stmt = $pdo->query("SELECT * FROM main_receipt");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Extract data from the row
            $receipt_id = $row['id'];
            $customer_name = $row['customer_name'];
            $vehicle_make = $row['vehicle_make'];
            $vehicle_model = $row['vehicle_model'];
            $vehicle_year = $row['vehicle_year'];
            $payment_type = $row['payment_type'];
            $create_date = date('F j, Y, g:i a', strtotime($row['time_created']));
            $payment_date = date('F j, Y, g:i a', strtotime($row['payment_date']));
            $amount_paid = number_format($row['amount_paid'], 2, '.', ',');
            $prefix_receipt_number = $row['prefix_receipt_number'];
			$encryption_key = "31081990";
			$encrypted_id = encryptData($receipt_id, $encryption_key);
            ?>
            <tr>
                <td><?php echo $receipt_id.$prefix_receipt_number; ?></td>
                <td><?php echo $customer_name; ?></td>
                <td><?php echo $vehicle_make . ' ' . $vehicle_model . ' ' . $vehicle_year; ?></td>
                <td><?php echo ucfirst($payment_type); ?></td>
                <td><?php echo $create_date; ?></td>
                <td><?php echo $payment_date; ?></td>
                <td><?php echo $amount_paid; ?></td>
                <td>
                    <a href="view_receipt.php?prefix_receipt_number=<?php echo urlencode($encrypted_id); ?>">View</a> |
                    <a href="edit_receipt.php?prefix_receipt_number=<?php echo urlencode($encrypted_id); ?>">Edit</a> |
                    <a href="delete_receipt.php?prefix_receipt_number=<?php echo urlencode($encrypted_id); ?>" onclick="return confirm('Are you sure you want to delete this receipt?')">Delete</a>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
</body>
</html>
