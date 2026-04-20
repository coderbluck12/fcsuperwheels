<?php
require_once __DIR__ . '/inc/functions.php';
global $pdo;

function addCol($table, $col, $def) {
    global $pdo;
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$col` $def");
        echo "Added $col to $table\n";
    } catch (Exception $e) {
        echo "Could not add $col to $table (might exist): " . $e->getMessage() . "\n";
    }
}

addCol('main_invoice', 'items_json', 'TEXT DEFAULT NULL');
addCol('main_receipt', 'items_json', 'TEXT DEFAULT NULL');

// Adding missing columns to main_invoice that invoice_processor uses but DESCRIBE showed missing
addCol('main_invoice', 'customer_address', 'TEXT DEFAULT NULL');
addCol('main_invoice', 'customer_phone', 'VARCHAR(50) DEFAULT NULL');
addCol('main_invoice', 'customer_email', 'VARCHAR(100) DEFAULT NULL');
addCol('main_invoice', 'vehicle_chasis', 'VARCHAR(100) DEFAULT NULL');
addCol('main_invoice', 'vehicle_color', 'VARCHAR(50) DEFAULT NULL');
addCol('main_invoice', 'quantity', 'INT(11) DEFAULT NULL');
addCol('main_invoice', 'vehicle_price', 'DECIMAL(15,2) DEFAULT NULL');
addCol('main_invoice', 'payment_instruction', 'VARCHAR(255) DEFAULT NULL');
addCol('main_invoice', 'add_payment', 'VARCHAR(255) DEFAULT NULL');
addCol('main_invoice', 'add_vehicle', 'VARCHAR(255) DEFAULT NULL');
addCol('main_invoice', 'signature_id', 'INT(11) DEFAULT NULL');

echo "Done.\n";
