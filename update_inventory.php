<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$sku = $_POST['sku'] ?? '';
$stock = $_POST['stock'] ?? null;
$price = $_POST['price'] ?? null;
$location = $_POST['location'] ?? null;

if (empty($sku)) {
    echo json_encode(['success' => false, 'message' => 'SKU is required']);
    exit;
}

try {
    $updates = [];
    $params = [];
    
    if ($stock !== null) {
        $updates[] = "stock = ?";
        $params[] = $stock;
    }
    if ($price !== null) {
        $updates[] = "price = ?";
        $params[] = $price;
    }
    if ($location !== null) {
        $updates[] = "location = ?";
        $params[] = $location;
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    
    $params[] = $sku;
    $sql = "UPDATE inventory SET " . implode(', ', $updates) . " WHERE sku = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    echo json_encode(['success' => true, 'message' => 'Inventory updated']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>