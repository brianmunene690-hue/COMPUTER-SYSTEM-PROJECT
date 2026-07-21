<?php
require_once 'db_config.php';

try {
    $pdo->exec("TRUNCATE TABLE activity_logs");
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>