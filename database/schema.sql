-- ===================================================
-- TechStore - Complete Database Schema & Seed Data
-- Topic 1: RESTful API · PHP, MySQL, HTML/CSS/JS
-- ===================================================

CREATE DATABASE IF NOT EXISTS `techstore` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `techstore`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `product_similarity`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- 1. Bảng users
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng categories
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `product_count` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng brands
CREATE TABLE `brands` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng products
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `category_id` INT NOT NULL,
    `category_name` VARCHAR(100) DEFAULT NULL,
    `brand_id` INT NOT NULL,
    `brand_name` VARCHAR(100) DEFAULT NULL,
    `price` DECIMAL(15, 2) NOT NULL DEFAULT 0,
    `original_price` DECIMAL(15, 2) DEFAULT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `rating` DECIMAL(3, 2) NOT NULL DEFAULT 5.0,
    `review_count` INT NOT NULL DEFAULT 0,
    `image` VARCHAR(500) DEFAULT NULL,
    `images` JSON DEFAULT NULL,
    `ram` VARCHAR(50) DEFAULT NULL,
    `rom` VARCHAR(50) DEFAULT NULL,
    `chipset` VARCHAR(100) DEFAULT NULL,
    `screen` VARCHAR(100) DEFAULT NULL,
    `battery` VARCHAR(100) DEFAULT NULL,
    `camera` VARCHAR(100) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng coupons
CREATE TABLE `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `discount_type` ENUM('percent', 'amount') NOT NULL DEFAULT 'percent',
    `value` DECIMAL(15, 2) NOT NULL DEFAULT 0,
    `min_order` DECIMAL(15, 2) NOT NULL DEFAULT 0,
    `max_discount` DECIMAL(15, 2) NOT NULL DEFAULT 0,
    `description` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('active', 'expired', 'disabled') NOT NULL DEFAULT 'active',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng orders
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(50) NOT NULL UNIQUE,
    `user_id` INT DEFAULT NULL,
    `customer_name` VARCHAR(255) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `customer_email` VARCHAR(255) DEFAULT NULL,
    `shipping_address` TEXT NOT NULL,
    `payment_method` ENUM('cod', 'vietqr', 'bank_transfer') NOT NULL DEFAULT 'cod',
    `payment_status` ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
    `status` ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `total_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(15, 2) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng order_items
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `price` DECIMAL(15, 2) NOT NULL DEFAULT 0,
    `quantity` INT NOT NULL DEFAULT 1,
    `variant` VARCHAR(100) DEFAULT NULL,
    `image` VARCHAR(500) DEFAULT NULL,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng cart
CREATE TABLE `cart` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `variant` VARCHAR(100) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng reviews
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id` INT DEFAULT NULL,
    `user_name` VARCHAR(255) NOT NULL,
    `rating` INT NOT NULL DEFAULT 5,
    `comment` TEXT DEFAULT NULL,
    `status` ENUM('approved', 'pending', 'hidden') NOT NULL DEFAULT 'approved',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Bảng wishlist
CREATE TABLE `wishlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Bảng audit_logs
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `user_name` VARCHAR(255) NOT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Bảng product_similarity (cho Thuật toán gợi ý sản phẩm Cosine / Apriori)
CREATE TABLE `product_similarity` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `similar_product_id` INT NOT NULL,
    `score` DECIMAL(5, 4) NOT NULL DEFAULT 0.0000,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sim_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sim_target` FOREIGN KEY (`similar_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================
-- SEED DATA
-- ===================================================

-- 1. Seed Users
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `phone`, `address`, `status`, `created_at`) VALUES
(1, 'Hữu Nam (Tech Lead)', 'admin@techstore.local', '$2y$10$Ybdv3nmywSWPJHfZ3ctjw.WnqsMxlJDYIeFTNTlWsCA3msHJAtPdu', 'admin', '0901234567', '227 Nguyễn Văn Cừ, Quận 5, TP.HCM', 'active', '2026-01-01 08:00:00'),
(2, 'Tuấn Kiệt', 'user@techstore.local', '$2y$10$/dkVsYj6pdq.uOBODUyrjO4bDu8ojB1sQ82KXU2RH5ttHB0HUD56q', 'customer', '0987654321', '123 Cách Mạng Tháng 8, Quận 10, TP.HCM', 'active', '2026-02-15 09:30:00'),
(3, 'Thành Trung', 'trung@techstore.local', '$2y$10$iX30CqZAu2KOMjcnNgVOPu7yE4nGuqCHcEb4PYKFF/0kz5pqbigdK', 'admin', '0912345678', '456 Hai Bà Trưng, Quận 1, TP.HCM', 'active', '2026-03-01 10:00:00');

-- 2. Seed Categories
INSERT INTO `categories` (`id`, `name`, `icon`, `slug`, `product_count`) VALUES
(1, 'Điện thoại', '📱', 'dien-thoai', 28),
(2, 'Laptop', '💻', 'laptop', 20),
(3, 'Tablet', '📋', 'tablet', 14),
(4, 'Âm thanh', '🎧', 'am-thanh', 16),
(5, 'Smartwatch', '⌚', 'smartwatch', 12),
(6, 'Phụ kiện', '🔌', 'phu-kien', 35);

-- 3. Seed Brands
INSERT INTO `brands` (`id`, `name`, `logo`) VALUES
(1, 'Apple', ''),
(2, 'Samsung', 'Samsung'),
(3, 'Xiaomi', 'Xiaomi'),
(4, 'ASUS', 'ASUS'),
(5, 'Sony', 'Sony'),
(6, 'Anker', 'Anker'),
(7, 'Dell', 'Dell');

-- 4. Seed Products
INSERT INTO `products` (`id`, `name`, `category_id`, `category_name`, `brand_id`, `brand_name`, `price`, `original_price`, `stock`, `rating`, `review_count`, `image`, `images`, `ram`, `rom`, `chipset`, `screen`, `battery`, `camera`, `description`, `featured`, `created_at`) VALUES
(1, 'iPhone 16 Pro Max 256GB', 1, 'Điện thoại', 1, 'Apple', 34990000.00, 37990000.00, 25, 4.9, 142, 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&auto=format&fit=crop&q=80', '["https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&auto=format&fit=crop&q=80", "https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800&auto=format&fit=crop&q=80"]', '8GB', '256GB', 'Apple A18 Pro 3nm', '6.9 inch Super Retina XDR OLED 120Hz', '4685 mAh, Sạc nhanh 30W', 'Chính 48MP + Góc rộng 48MP + Tele 5x 12MP', 'iPhone 16 Pro Max thiết kế Titan sa mạc đẳng cấp, chip A18 Pro mạnh mẽ và nút Camera Control hoàn toàn mới.', 1, '2026-09-20 00:00:00'),
(2, 'Samsung Galaxy S24 Ultra 5G 12GB/256GB', 1, 'Điện thoại', 2, 'Samsung', 29990000.00, 33990000.00, 18, 4.8, 98, 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop&q=80', '["https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&auto=format&fit=crop&q=80"]', '12GB', '256GB', 'Snapdragon 8 Gen 3 for Galaxy', '6.8 inch Dynamic AMOLED 2X 120Hz', '5000 mAh, Sạc nhanh 45W', '200MP + 50MP + 12MP + 10MP, Zoom 100x', 'Galaxy S24 Ultra đột phá với quyền năng Galaxy AI, khung viền Titan và bút S-Pen tích hợp.', 1, '2026-08-15 00:00:00'),
(3, 'MacBook Pro 14 M3 Pro 18GB/512GB Space Black', 2, 'Laptop', 1, 'Apple', 49990000.00, 52990000.00, 12, 4.95, 64, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&auto=format&fit=crop&q=80', '["https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&auto=format&fit=crop&q=80"]', '18GB', '512GB SSD', 'Apple M3 Pro (11 CPU, 14 GPU)', '14.2 inch Liquid Retina XDR 120Hz ProMotion', '70Wh, Thời lượng pin 18 giờ', '1080p FaceTime HD camera', 'MacBook Pro 14 M3 Pro hiệu năng đồ họa ấn tượng, tản nhiệt tối ưu và màu đen Space Black cuốn hút.', 1, '2026-07-10 00:00:00'),
(4, 'Laptop ASUS ROG Zephyrus G16 OLED RTX 4070', 2, 'Laptop', 4, 'ASUS', 59990000.00, 64990000.00, 8, 4.85, 41, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80', '["https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80"]', '32GB LPDDR5X', '1TB PCIe 4.0 SSD', 'Intel Core Ultra 9 185H', '16.0 inch 2.5K OLED 240Hz 0.2ms ROG Nebula', '90Wh, Sạc nhanh 100W Type-C', '1080p FHD IR Camera hỗ trợ Windows Hello', 'Laptop Gaming siêu mỏng nhẹ khung nhôm CNC, màn hình OLED 240Hz sắc nét đỉnh cao cùng card đồ họa RTX 4070.', 1, '2026-06-01 00:00:00'),
(5, 'iPad Air 6 M2 11 inch 128GB Wi-Fi', 3, 'Tablet', 1, 'Apple', 16490000.00, 17990000.00, 30, 4.75, 52, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80', '["https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80"]', '8GB', '128GB', 'Apple M2 8-core CPU', '11 inch Liquid Retina IPS TrueTone', '28.93 Wh, Sử dụng liên tục 10h', '12MP Wide trước & sau, Center Stage', 'iPad Air 6 trang bị vi xử lý Apple M2 mạnh mẽ vượt trội, hỗ trợ bút Apple Pencil Pro thế hệ mới.', 0, '2026-05-22 00:00:00'),
(6, 'Tai nghe Sony WH-1000XM5 Chống Ồn', 4, 'Âm thanh', 5, 'Sony', 7490000.00, 8990000.00, 22, 4.9, 115, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80', '["https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80"]', NULL, NULL, 'Sony Integrated Processor V1 + QN1', 'Driver 30mm màng loa carbon cao cấp', 'Pin 30 giờ, Sạc nhanh 3 phút nghe 3 giờ', '8 micro thu âm đàm thoại AI lọc ồn', 'Công nghệ chống ồn số 1 thị trường, âm thanh chuẩn Hi-Res Audio Wireless LDAC đem lại trải nghiệm thư thái tuyệt đối.', 1, '2026-05-18 00:00:00'),
(7, 'Apple Watch Ultra 2 GPS + Cellular 49mm', 5, 'Smartwatch', 1, 'Apple', 20990000.00, 22990000.00, 15, 4.88, 73, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80', '["https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80"]', NULL, '64GB', 'Apple S9 SiP 64-bit lõi kép', 'OLED LTPO Always-On 3000 nits', 'Pin lên đến 36 giờ (72 giờ chế độ nguồn điện thấp)', NULL, 'Đồng hồ thông minh dành cho thể thao chuyên nghiệp và thám hiểm, vỏ Titan nhẹ bền bỉ và độ sáng 3000 nits rực rỡ.', 0, '2026-05-05 00:00:00'),
(8, 'Củ sạc nhanh Anker 65W GaNPrime 3 cổng', 6, 'Phụ kiện', 6, 'Anker', 890000.00, 1150000.00, 50, 4.8, 160, 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80', '["https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"]', NULL, NULL, 'Công nghệ GaNPrime thế hệ mới', NULL, 'Bảo vệ nhiệt ActiveShield 2.0', NULL, 'Củ sạc siêu nhỏ gọn, tản nhiệt an toàn, sạc cùng lúc 3 thiết bị MacBook, iPhone, iPad với công suất tối đa 65W.', 0, '2026-04-12 00:00:00');

-- 5. Seed Coupons
INSERT INTO `coupons` (`id`, `code`, `discount_type`, `value`, `min_order`, `max_discount`, `description`, `status`) VALUES
(1, 'TECH10', 'percent', 10.00, 1000000.00, 500000.00, 'Giảm 10% tối đa 500k cho đơn từ 1 triệu', 'active'),
(2, 'VIETQR50', 'amount', 50000.00, 500000.00, 50000.00, 'Giảm 50.000đ khi thanh toán VietQR', 'active'),
(3, 'GIAM100K', 'amount', 100000.00, 2000000.00, 100000.00, 'Giảm ngay 100k cho đơn từ 2 triệu', 'active');

-- 6. Seed Orders
INSERT INTO `orders` (`id`, `order_code`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `shipping_address`, `payment_method`, `payment_status`, `status`, `total_amount`, `discount_amount`, `created_at`) VALUES
(1001, 'TS-749201', 2, 'Tuấn Kiệt', '0987654321', 'user@techstore.local', '227 Nguyễn Văn Cừ, P4, Quận 5, TP.HCM', 'vietqr', 'paid', 'completed', 34990000.00, 500000.00, '2026-10-19 09:30:00'),
(1002, 'TS-839102', NULL, 'Nguyễn Văn Minh', '0912345678', 'minh@gmail.com', '15 Lê Duẩn, Bến Nghé, Quận 1, TP.HCM', 'vietqr', 'pending', 'pending', 7490000.00, 50000.00, '2026-10-21 08:15:00');

-- 7. Seed Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `variant`, `image`) VALUES
(1, 1001, 1, 'iPhone 16 Pro Max 256GB', 34990000.00, 1, '256GB - Titan', 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&auto=format&fit=crop&q=80'),
(2, 1002, 6, 'Tai nghe Sony WH-1000XM5 Chống Ồn', 7490000.00, 1, 'Đen', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80');

-- 8. Seed Cart Items
INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `variant`) VALUES
(1, 2, 1, 1, '256GB - Titan Sa Mạc');

-- 9. Seed Reviews
INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `user_name`, `rating`, `comment`, `status`, `created_at`) VALUES
(1, 1, 1, 'Nguyễn Văn Nam', 5, 'Hàng chuẩn chính hãng CellphoneS, giao nhanh trong 2h tại TP.HCM, đóng gói cẩn thận 10/10!', 'approved', '2026-10-18 00:00:00'),
(2, 1, 2, 'Trần Thị Mai', 5, 'Máy dùng rất mượt mà, camera nét đỉnh chóp. Nhân viên tư vấn nhiệt tình.', 'approved', '2026-10-15 00:00:00'),
(3, 2, 3, 'Lê Hoàng Minh', 4, 'Sản phẩm tốt trong tầm giá, quét VietQR thanh toán 5s là xong luôn.', 'approved', '2026-10-12 00:00:00');

-- 10. Seed Wishlist
INSERT INTO `wishlist` (`id`, `user_id`, `product_id`) VALUES
(1, 2, 1),
(2, 2, 6);

-- 11. Seed Audit Logs
INSERT INTO `audit_logs` (`id`, `user_id`, `user_name`, `action`, `description`, `created_at`) VALUES
(1, 1, 'admin@techstore.local', 'UPDATE_ORDER_STATUS', 'Cập nhật đơn hàng TS-749201 sang completed', '2026-10-19 10:00:00'),
(2, 1, 'admin@techstore.local', 'CREATE_PRODUCT', 'Thêm sản phẩm mới iPhone 16 Pro Max', '2026-09-20 14:20:00');

SET FOREIGN_KEY_CHECKS = 1;
