<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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

    // 只處理 POST 請求
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('不支援的請求方法');
    }

    // 讀取請求資料
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception('未接收到更新資料');
    }
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('資料格式錯誤: ' . json_last_error_msg());
    }
    
    // 檢查必要欄位
    if (empty($data['orderId']) || empty($data['status'])) {
        throw new Exception('缺少必要參數');
    }
    
    // 取得訂單ID（可能是訂單號碼或ID）
    $orderId = $data['orderId'];
    $status = strtoupper($data['status']);
    
    // 允許的狀態變更
    $allowedStatuses = ['PENDING', 'PAID', 'COOKING', 'PREPARING', 'READY', 'COMPLETED', 'CANCELLED'];
    if (!in_array($status, $allowedStatuses)) {
        throw new Exception('無效的訂單狀態');
    }
    
    // 判斷是訂單號碼還是ID
    if (is_numeric($orderId) && strlen($orderId) < 5) {
        // 單純數字且長度小於5，視為訂單ID
        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            'id' => $orderId,
            'status' => $status
        ]);
    } else {
        // 否則視為訂單號碼
        $stmt = $pdo->prepare("
            UPDATE orders
            SET status = :status, updated_at = NOW()
            WHERE order_number = :order_number
        ");
        $stmt->execute([
            'order_number' => $orderId,
            'status' => $status
        ]);
    }
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('找不到符合的訂單');
    }
    
    // 回應成功
    echo json_encode([
        'success' => true,
        'message' => '訂單狀態已更新'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => '更新訂單狀態失敗',
        'message' => $e->getMessage()
    ]);
}
?> 