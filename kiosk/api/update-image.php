<?php
require_once __DIR__ . '/db.php';

try {
    $stmt1 = $pdo->prepare("UPDATE menu SET image = ? WHERE id = ?");
    $stmt1->execute(['assets/images/food-1.jpg', 1]);
    $stmt1->execute(['assets/images/food-2.jpg', 2]);
    $stmt1->execute(['assets/images/food-3.jpg', 3]);

    echo json_encode(['status' => 'success', 'message' => '圖片路徑已更新']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>