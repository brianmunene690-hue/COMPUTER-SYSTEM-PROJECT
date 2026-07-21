<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$id = $_POST['id'] ?? null;
$role = $_POST['role'] ?? '';

if (!$id || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$role, $id]);
    echo json_encode(['success' => true, 'message' => 'User role updated']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>