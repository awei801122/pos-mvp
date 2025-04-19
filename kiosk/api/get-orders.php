<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php';

// 處理 OPTIONS 請求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if (!isset($pdo)) {
        throw new Exception('資料庫連接失敗');
    }

    // 取得訂單列表
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
    
    $sql = "
        SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
    ";
    
    // 依狀態篩選
    if ($status && $status !== 'all') {
        $sql .= " WHERE o.status = :status";
    }
    
    $sql .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT :limit";
    
    $stmt = $pdo->prepare($sql);
    
    if ($status && $status !== 'all') {
        $stmt->bindValue(':status', strtoupper($status), PDO::PARAM_STR);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 取得訂單明細
    foreach ($orders as &$order) {
        $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $order['id']]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 轉換訂單屬性名稱以符合前端顯示需求
        $order['orderNumber'] = $order['order_number'];
        $order['orderTime'] = $order['created_at'];
        $order['total'] = $order['total_amount'];
        $order['paymentMethod'] = strtolower($order['payment_method']);
        $order['items'] = $items;
    }
    
    echo json_encode($orders);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => '載入訂單失敗',
        'message' => $e->getMessage()
    ]);
}
?> 