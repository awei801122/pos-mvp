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
            // 讀取分類
            $stmt = $pdo->query("SELECT id, name, description FROM categories ORDER BY display_order");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 讀取菜單項目
            $stmt = $pdo->query("
                SELECT 
                    id, 
                    name_zh AS name, 
                    price, 
                    category AS category_id, 
                    CONCAT('img/products/food-', id, '.jpg') AS image_url, 
                    is_active AS is_available 
                FROM menu 
                WHERE is_active = 1
            ");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        'success' => false,
        'error' => '讀取菜單失敗',
        'message' => $e->getMessage()
    ]);
}
?>