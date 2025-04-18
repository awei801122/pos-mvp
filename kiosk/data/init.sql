CREATE DATABASE IF NOT EXISTS pos_db DEFAULT CHARSET utf8mb4;
USE pos_db;

CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_zh VARCHAR(100) NOT NULL,
    name_en VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(50),
    stock INT DEFAULT 999,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO menu (name_zh, name_en, price, image, category)
VALUES 
('炸雞腿飯', 'Fried Chicken Rice', 90, 'assets/images/chicken.jpg', '主食'),
('紅燒牛肉麵', 'Braised Beef Noodles', 120, 'assets/images/beef-noodle.jpg', '主食'),
('珍珠奶茶', 'Bubble Milk Tea', 50, 'assets/images/bubble-tea.jpg', '飲品');