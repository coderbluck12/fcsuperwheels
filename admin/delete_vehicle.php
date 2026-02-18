<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'inc/session_manager.php';
require_once 'inc/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: inventory.php?error=invalid_id');
    exit;
}

try {
    // First, get the vehicle details to delete the image file
    $stmt = $pdo->prepare("SELECT image FROM vehicles WHERE id = ?");
    $stmt->execute([$id]);
    $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($vehicle) {
        // Delete the vehicle from database
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        
        // Delete the image file if it exists
        if (!empty($vehicle['image']) && file_exists($vehicle['image'])) {
            unlink($vehicle['image']);
        }
        
        header('Location: inventory.php?success=deleted');
    } else {
        header('Location: inventory.php?error=not_found');
    }
    exit;
} catch (PDOException $e) {
    error_log("Error deleting vehicle: " . $e->getMessage());
    header('Location: inventory.php?error=delete_failed');
    exit;
}
?>