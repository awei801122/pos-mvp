<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // 讀取分類（從菜單中提取不重複的分類）
            $stmt = $pdo->query("SELECT DISTINCT category as id, category as name FROM menu WHERE is_active = 1 ORDER BY category");
            $categories = $stmt->fetchAll();
            
            // 為每個分類添加id
            foreach ($categories as $key => $category) {
                $categories[$key]['id'] = $key + 1;
                $categories[$key]['description'] = '';
            }
            
            // 讀取菜單項目
            $stmt = $pdo->query("SELECT id, name_zh as name, price, category, description, image as image_url, is_active FROM menu WHERE is_active = 1 ORDER BY category, name_zh");
            $items = $stmt->fetchAll();
            
            // 處理項目，添加必要字段
            foreach ($items as &$item) {
                // 設置category_id (基於分類名稱匹配)
                $categoryKey = array_search($item['category'], array_column($categories, 'name'));
                $item['category_id'] = $categories[$categoryKey]['id'];
                
                // 確保圖片路徑正確
                if (!empty($item['image_url'])) {
                    // 相對路徑
                    $item['image_url'] = $item['image_url'];
                }
                
                // 添加庫存相關字段（模擬數據）
                $item['is_available'] = 1;
                $item['track_inventory'] = 0;
                $item['inventory_count'] = 100;
            }
            
            echo json_encode([
                'success' => true,
                'categories' => $categories,
                'items' => $items
            ]);
            break;

        case 'POST':
            // 新增菜單項目
            if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                throw new Exception('沒有權限執行此操作');
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                INSERT INTO menu (name_zh, name_en, price, category, description, image)
                VALUES (:name_zh, :name_en, :price, :category, :description, :image)
            ");
            
            $stmt->execute([
                'name_zh' => $data['name_zh'],
                'name_en' => $data['name_en'],
                'price' => $data['price'],
                'category' => $data['category'],
                'description' => $data['description'],
                'image' => $data['image']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => '新增成功',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        case 'PUT':
            // 更新菜單項目
            if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                throw new Exception('沒有權限執行此操作');
            }

            $data = json_decode(file_get_contents('php://input'), true);
            
            $stmt = $pdo->prepare("
                UPDATE menu 
                SET name_zh = :name_zh,
                    name_en = :name_en,
                    price = :price,
                    category = :category,
                    description = :description,
                    image = :image,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $data['id'],
                'name_zh' => $data['name_zh'],
                'name_en' => $data['name_en'],
                'price' => $data['price'],
                'category' => $data['category'],
                'description' => $data['description'],
                'image' => $data['image']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => '更新成功'
            ]);
            break;

        case 'DELETE':
            // 刪除菜單項目（軟刪除）
            if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
                throw new Exception('沒有權限執行此操作');
            }

            $id = $_GET['id'] ?? null;
            if (!$id) {
                throw new Exception('缺少必要參數');
            }
            
            $stmt = $pdo->prepare("
                UPDATE menu 
                SET is_active = 0,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            
            $stmt->execute(['id' => $id]);
            
            echo json_encode([
                'success' => true,
                'message' => '刪除成功'
            ]);
            break;

        default:
            throw new Exception('不支援的請求方法');
    }
} catch (Exception $e) {
    error_log('菜單 API 錯誤: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => '讀取菜單失敗',
        'message' => $e->getMessage()
    ]);
}
?>