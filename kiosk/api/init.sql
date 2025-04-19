-- 建立資料庫
CREATE DATABASE IF NOT EXISTS pos_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_system;

-- 建立菜單表
CREATE TABLE IF NOT EXISTS menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name_zh VARCHAR(100) NOT NULL COMMENT '中文名稱',
    name_en VARCHAR(100) COMMENT '英文名稱',
    price DECIMAL(10,2) NOT NULL COMMENT '價格',
    category VARCHAR(50) COMMENT '分類',
    description TEXT COMMENT '描述',
    image VARCHAR(255) COMMENT '圖片路徑',
    is_active TINYINT(1) DEFAULT 1 COMMENT '是否啟用',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    UNIQUE KEY `uk_name_zh` (`name_zh`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 建立訂單主表（改名為 order_list）
CREATE TABLE IF NOT EXISTS order_list (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) NOT NULL COMMENT '訂單編號',
    order_time DATETIME COMMENT '訂單時間',
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '總金額',
    status ENUM('PENDING', 'PAID', 'PREPARING', 'READY', 'COMPLETED', 'CANCELLED') NOT NULL DEFAULT 'PENDING' COMMENT '訂單狀態',
    payment_method ENUM('CASH', 'CARD', 'MOBILE') NOT NULL DEFAULT 'CASH' COMMENT '支付方式',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    UNIQUE KEY `uk_order_number` (`order_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 建立訂單詳細表（改名為 order_detail）
CREATE TABLE IF NOT EXISTS order_detail (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL COMMENT '訂單ID',
    menu_id INT NOT NULL COMMENT '菜單項目ID',
    item_name VARCHAR(100) NOT NULL COMMENT '項目名稱',
    quantity INT NOT NULL DEFAULT 1 COMMENT '數量',
    unit_price DECIMAL(10,2) NOT NULL COMMENT '單價',
    total_price DECIMAL(10,2) NOT NULL COMMENT '總價',
    note TEXT COMMENT '備註',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    FOREIGN KEY (order_id) REFERENCES order_list(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 插入測試數據（如果不存在）
INSERT IGNORE INTO menu (name_zh, name_en, price, category, description, image, is_active) VALUES
('經典漢堡', 'Classic Burger', 120, '漢堡', '100% 純牛肉漢堡，搭配新鮮生菜、番茄和特製醬料', 'img/food-1.jpg', 1),
('起司漢堡', 'Cheese Burger', 135, '漢堡', '經典漢堡加上濃郁切達起司片', 'img/food-2.jpg', 1),
('雙層牛肉漢堡', 'Double Beef Burger', 180, '漢堡', '雙層純牛肉餅，加倍滿足', 'img/food-3.jpg', 1);