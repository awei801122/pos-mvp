<?php
$host = 'localhost';
$db   = 'pos_db';
$user = 'root';     // 改成你的 MySQL 帳號
$pass = '';         // 改成你的密碼
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => '資料庫連線失敗']);
    exit;
}
?>