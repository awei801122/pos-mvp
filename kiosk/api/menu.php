<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM menu WHERE is_active = 1");
    $menu = $stmt->fetchAll();
    echo json_encode($menu);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => '讀取菜單失敗']);
}
?>