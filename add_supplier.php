<?php
header('Content-Type: application/json');
require_once 'db_config.php';

$name = $_POST['name'] ?? '';
$contact = $_POST['contact'] ?? '';
$email = $_POST['email'] ?? '';

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Supplier name is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact, email) VALUES (?, ?, ?)");
    $stmt->execute([$name, $contact, $email]);
    echo json_encode(['success' => true, 'message' => 'Supplier added']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>