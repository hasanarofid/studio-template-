<?php
header('Content-Type: application/json');
require_once 'db_config.php';

try {
    $stmt = $pdo->query("SELECT * FROM templates ORDER BY created_at DESC");
    $templates = $stmt->fetchAll();
    echo json_encode(['success' => true, 'templates' => $templates]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
