<?php
// 資料庫連接設定
$db_config = [
    'host' => 'localhost',      // 資料庫主機
    'dbname' => 'pos_system',   // 資料庫名稱
    'user' => 'root',          // 資料庫用戶名
    'pass' => '',              // 資料庫密碼
    'charset' => 'utf8mb4'     // 字符集
];

try {
    // 建立 PDO 連接
    $dsn = "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $pdo = new PDO($dsn, $db_config['user'], $db_config['pass']);
    
    // 設置錯誤模式
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
} catch (PDOException $e) {
    error_log("資料庫連接失敗: " . $e->getMessage());
    // 不要在生產環境中顯示詳細錯誤信息
    // echo "資料庫連接失敗: " . $e->getMessage();
}
?>