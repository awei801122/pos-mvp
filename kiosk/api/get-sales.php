<?php
header('Content-Type: application/json');
$date = $_GET['date'] ?? date('Y-m-d');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=pos_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE DATE(order_time) = ?");
    $stmt->execute([$date]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as &$order) {
        $itemStmt = $pdo->prepare("SELECT item_name, quantity, price, total_price, note, category FROM order_items WHERE order_id = ?");
        $itemStmt->execute([$order['id']]);
        $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['success' => true, 'data' => $orders]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>