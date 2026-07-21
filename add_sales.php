<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$item = $_POST['item'] ?? '';
$total = $_POST['total'] ?? 0;
$staff = $_POST['staff'] ?? '';

if (empty($item)) {
    echo json_encode(['success' => false, 'message' => 'Item is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO sales (item, total, staff, time) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$item, $total, $staff]);
    echo json_encode(['success' => true, 'message' => 'Sale recorded']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>