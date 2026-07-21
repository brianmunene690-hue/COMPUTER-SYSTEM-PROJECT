<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$sku = $_POST['sku'] ?? '';
$name = $_POST['name'] ?? '';
$price = $_POST['price'] ?? 0;

if (empty($sku) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'SKU and Name are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO inventory (sku, name, price, stock, location, reorder_level) VALUES (?, ?, ?, 0, 'New', 5)");
    $stmt->execute([$sku, $name, $price]);
    echo json_encode(['success' => true, 'message' => 'Item added successfully']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>