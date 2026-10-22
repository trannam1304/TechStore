-- ==============================================================================
-- TechStore - Production Database Schema (DDL)
-- Topic 1: RESTful API · PHP, MySQL, HTML/CSS/JS
-- Nhiệm vụ T04: Thiết kế ERD và viết schema.sql (≥15 bảng)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `techstore` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `techstore`;

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- Xóa bảng cũ theo thứ tự ngược phụ thuộc khóa ngoại
-- -----------------------------------------------------------------------------
DROP VIEW IF EXISTS `cart`;
DROP VIEW IF EXISTS `wishlist`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `product_similarity`;
DROP TABLE IF EXISTS `product_views`;
DROP TABLE IF EXISTS `refresh_tokens`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `wishlists`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `carts`;
DROP TABLE IF EXISTS `product_specs`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `brands`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- =============================================================================
-- NHÓM 1: CORE ENTITIES (NGƯỜI DÙNG & DANH MỤC HÀNG HÓA)
-- =============================================================================

-- 1. Bảng users: Tài khoản người dùng (Khách hàng & Quản trị viên)
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL COMMENT 'Họ và tên người dùng',
    `email` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email đăng nhập duy nhất',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Mật khẩu băm Bcrypt',
    `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer' COMMENT 'Vai trò phân quyền',
    `phone` VARCHAR(20) DEFAULT NULL COMMENT 'Số điện thoại liên hệ',
    `address` TEXT DEFAULT NULL COMMENT 'Địa chỉ mặc định',
    `avatar` VARCHAR(500) DEFAULT NULL COMMENT 'Đường dẫn ảnh đại diện',
    `status` ENUM('active', 'inactive', 'banned') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái hoạt động',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng người dùng và phân quyền';

-- 2. Bảng categories: Danh mục ngành hàng công nghệ
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Tên danh mục (Điện thoại, Laptop...)',
    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Slug thân thiện URL',
    `icon` VARCHAR(50) DEFAULT NULL COMMENT 'Icon hoặc emoji danh mục',
    `description` TEXT DEFAULT NULL COMMENT 'Mô tả danh mục',
    `product_count` INT NOT NULL DEFAULT 0 COMMENT 'Số lượng sản phẩm thuộc danh mục',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng danh mục ngành hàng';

-- 3. Bảng brands: Thương hiệu / Hãng sản xuất
CREATE TABLE `brands` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL COMMENT 'Tên hãng (Apple, Samsung, ASUS...)',
    `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Slug thân thiện URL',
    `logo` VARCHAR(255) DEFAULT NULL COMMENT 'Logo hoặc ký tự thương hiệu',
    `description` TEXT DEFAULT NULL COMMENT 'Giới thiệu hãng',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng thương hiệu';

-- 4. Bảng products: Sản phẩm công nghệ
CREATE TABLE `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL COMMENT 'Tên đầy đủ của sản phẩm',
    `slug` VARCHAR(255) DEFAULT NULL COMMENT 'Slug thân thiện URL',
    `category_id` INT NOT NULL COMMENT 'FK tới categories',
    `category_name` VARCHAR(100) DEFAULT NULL COMMENT 'Tên danh mục đệm để query nhanh',
    `brand_id` INT NOT NULL COMMENT 'FK tới brands',
    `brand_name` VARCHAR(100) DEFAULT NULL COMMENT 'Tên hãng đệm để query nhanh',
    `price` DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Giá bán hiện tại (VND)',
    `original_price` DECIMAL(15, 2) DEFAULT NULL COMMENT 'Giá niêm yết gốc trước giảm giá',
    `stock` INT NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho',
    `rating` DECIMAL(3, 2) NOT NULL DEFAULT 5.00 COMMENT 'Điểm đánh giá trung bình (1.00 - 5.00)',
    `review_count` INT NOT NULL DEFAULT 0 COMMENT 'Tổng số lượt đánh giá',
    `view_count` INT NOT NULL DEFAULT 0 COMMENT 'Tổng lượt xem sản phẩm',
    `image` VARCHAR(500) DEFAULT NULL COMMENT 'Ảnh đại diện chính của sản phẩm',
    `images` JSON DEFAULT NULL COMMENT 'Mảng JSON danh sách ảnh',
    `ram` VARCHAR(50) DEFAULT NULL COMMENT 'Dung lượng RAM',
    `rom` VARCHAR(50) DEFAULT NULL COMMENT 'Bộ nhớ trong / Dung lượng ổ cứng',
    `chipset` VARCHAR(100) DEFAULT NULL COMMENT 'Vi xử lý CPU / SoC',
    `screen` VARCHAR(100) DEFAULT NULL COMMENT 'Thông số màn hình',
    `battery` VARCHAR(100) DEFAULT NULL COMMENT 'Dung lượng pin và công nghệ sạc',
    `camera` VARCHAR(100) DEFAULT NULL COMMENT 'Hệ thống camera',
    `description` TEXT DEFAULT NULL COMMENT 'Mô tả chi tiết sản phẩm',
    `featured` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Sản phẩm nổi bật trang chủ',
    `status` ENUM('active', 'inactive', 'out_of_stock') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái kinh doanh',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE RESTRICT,
    INDEX `idx_products_category` (`category_id`),
    INDEX `idx_products_brand` (`brand_id`),
    INDEX `idx_products_price` (`price`),
    INDEX `idx_products_featured` (`featured`),
    INDEX `idx_products_rating` (`rating`),
    INDEX `idx_products_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng thông tin sản phẩm';

-- 5. Bảng product_images: Bộ sưu tập thư viện ảnh sản phẩm
CREATE TABLE `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL COMMENT 'FK tới products',
    `image_url` VARCHAR(500) NOT NULL COMMENT 'URL ảnh sản phẩm',
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 nếu là ảnh chính',
    `display_order` INT NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    INDEX `idx_product_images_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thư viện ảnh sản phẩm';

-- 6. Bảng product_specs: Thông số kỹ thuật chi tiết theo nhóm
CREATE TABLE `product_specs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL COMMENT 'FK tới products',
    `spec_group` VARCHAR(100) DEFAULT 'Cấu hình chung' COMMENT 'Nhóm thông số (Hiệu năng, Màn hình...)',
    `spec_key` VARCHAR(100) NOT NULL COMMENT 'Tên thông số (RAM, Chipset, Pin...)',
    `spec_value` TEXT NOT NULL COMMENT 'Giá trị thông số chi tiết',
    `display_order` INT NOT NULL DEFAULT 0 COMMENT 'Thứ tự hiển thị trong bảng spec',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_product_specs_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    INDEX `idx_product_specs_product` (`product_id`),
    INDEX `idx_product_specs_key` (`spec_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thông số kỹ thuật sản phẩm chi tiết';

-- =============================================================================
-- NHÓM 2: COMMERCE & SHOPPING (GIỎ HÀNG, ĐƠN HÀNG & THANH TOÁN)
-- =============================================================================

-- 7. Bảng carts: Giỏ hàng khách hàng
CREATE TABLE `carts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL COMMENT 'FK tới users',
    `product_id` INT NOT NULL COMMENT 'FK tới products',
    `quantity` INT NOT NULL DEFAULT 1 COMMENT 'Số lượng sản phẩm đặt trong giỏ',
    `variant` VARCHAR(100) DEFAULT NULL COMMENT 'Phiên bản / Màu sắc / Bộ nhớ',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_carts_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    INDEX `idx_carts_user` (`user_id`),
    INDEX `idx_carts_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Giỏ hàng của người dùng';

-- View tương thích backward cho bảng cart số ít
CREATE OR REPLACE VIEW `cart` AS SELECT * FROM `carts`;

-- 8. Bảng coupons: Mã ưu đãi giảm giá
CREATE TABLE `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã voucher (ví dụ TECH10, VIETQR50)',
    `discount_type` ENUM('percent', 'amount') NOT NULL DEFAULT 'percent' COMMENT 'percent: giảm %, amount: giảm số tiền',
    `value` DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Giá trị giảm (% hoặc số tiền VND)',
    `min_order` DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Giá trị đơn tối thiểu để áp dụng',
    `max_discount` DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Giảm tối đa (áp dụng cho loại percent)',
    `usage_limit` INT NOT NULL DEFAULT 1000 COMMENT 'Giới hạn số lần sử dụng tối đa',
    `used_count` INT NOT NULL DEFAULT 0 COMMENT 'Số lần mã đã được sử dụng',
    `description` VARCHAR(255) DEFAULT NULL COMMENT 'Mô tả chương trình khuyến mãi',
    `status` ENUM('active', 'expired', 'disabled') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái mã',
    `start_date` DATETIME DEFAULT NULL COMMENT 'Ngày bắt đầu áp dụng',
    `end_date` DATETIME DEFAULT NULL COMMENT 'Ngày hết hạn',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_coupons_code` (`code`),
    INDEX `idx_coupons_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mã giảm giá voucher';

-- 9. Bảng orders: Đơn hàng
CREATE TABLE `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Mã đơn hàng duy nhất (ví dụ TS-749201)',
    `user_id` INT DEFAULT NULL COMMENT 'FK tới users (NULL nếu khách vãng lai)',
    `customer_name` VARCHAR(255) NOT NULL COMMENT 'Tên người nhận',
    `customer_phone` VARCHAR(20) NOT NULL COMMENT 'Số điện thoại nhận hàng',
    `customer_email` VARCHAR(255) DEFAULT NULL COMMENT 'Email nhận thông báo',
    `shipping_address` TEXT NOT NULL COMMENT 'Địa chỉ giao hàng',
    `payment_method` ENUM('cod', 'vietqr', 'bank_transfer') NOT NULL DEFAULT 'cod' COMMENT 'Phương thức thanh toán',
    `payment_status` ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái thanh toán',
    `status` ENUM('pending', 'confirmed', 'shipping', 'completed', 'cancelled') NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái giao nhận đơn',
    `subtotal` DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Tổng tiền hàng trước giảm giá',
    `discount_amount` DECIMAL(15, 2) DEFAULT 0 COMMENT 'Số tiền được giảm',
    `shipping_fee` DECIMAL(15, 2) DEFAULT 0 COMMENT 'Phí vận chuyển',
    `total_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Tổng tiền thanh toán cuối cùng',
    `coupon_code` VARCHAR(50) DEFAULT NULL COMMENT 'Mã giảm giá đã dùng (nếu có)',
    `note` TEXT DEFAULT NULL COMMENT 'Ghi chú đơn hàng của khách',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_orders_code` (`order_code`),
    INDEX `idx_orders_user` (`user_id`),
    INDEX `idx_orders_status` (`status`),
    INDEX `idx_orders_payment_status` (`payment_status`),
    INDEX `idx_orders_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Đơn hàng mua sắm';

-- 10. Bảng order_items: Chi tiết sản phẩm trong đơn hàng
CREATE TABLE `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL COMMENT 'FK tới orders',
    `product_id` INT NOT NULL COMMENT 'FK tới products',
    `product_name` VARCHAR(255) NOT NULL COMMENT 'Lưu tên sản phẩm tại thời điểm mua',
    `price` DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Giá sản phẩm tại thời điểm mua',
    `quantity` INT NOT NULL DEFAULT 1 COMMENT 'Số lượng',
    `variant` VARCHAR(100) DEFAULT NULL COMMENT 'Phiên bản / màu sắc',
    `image` VARCHAR(500) DEFAULT NULL COMMENT 'Ảnh chụp sản phẩm lúc mua',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
    INDEX `idx_order_items_order` (`order_id`),
    INDEX `idx_order_items_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiết mặt hàng trong đơn';

-- 11. Bảng payments: Giao dịch thanh toán (VietQR / COD / Chuyển khoản)
CREATE TABLE `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL COMMENT 'FK tới orders',
    `payment_code` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Mã giao dịch thanh toán duy nhất',
    `payment_method` ENUM('cod', 'vietqr', 'bank_transfer') NOT NULL DEFAULT 'cod' COMMENT 'Hình thức thanh toán',
    `amount` DECIMAL(15, 2) NOT NULL DEFAULT 0 COMMENT 'Số tiền thanh toán',
    `status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái giao dịch',
    `transaction_id` VARCHAR(100) DEFAULT NULL COMMENT 'Mã đối soát giao dịch ngân hàng / cổng thanh toán',
    `qr_code_url` VARCHAR(500) DEFAULT NULL COMMENT 'URL sinh mã QR chuẩn VietQR',
    `payment_note` TEXT DEFAULT NULL COMMENT 'Nội dung chuyển khoản hoặc ghi chú',
    `paid_at` DATETIME DEFAULT NULL COMMENT 'Thời điểm thanh toán thành công',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    INDEX `idx_payments_order` (`order_id`),
    INDEX `idx_payments_status` (`status`),
    INDEX `idx_payments_code` (`payment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lịch sử giao dịch thanh toán';

-- =============================================================================
-- NHÓM 3: INTERACTION & SECURITY (ĐÁNH GIÁ, YÊU THÍCH & BẢO MẬT AUTH)
-- =============================================================================

-- 12. Bảng reviews: Đánh giá & bình luận sản phẩm
CREATE TABLE `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL COMMENT 'FK tới products',
    `user_id` INT DEFAULT NULL COMMENT 'FK tới users (NULL nếu khách)',
    `user_name` VARCHAR(255) NOT NULL COMMENT 'Tên hiển thị người đánh giá',
    `rating` INT NOT NULL DEFAULT 5 COMMENT 'Số sao đánh giá (1-5)',
    `comment` TEXT DEFAULT NULL COMMENT 'Nội dung nhận xét',
    `status` ENUM('approved', 'pending', 'hidden') NOT NULL DEFAULT 'approved' COMMENT 'Trạng thái duyệt bình luận',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_reviews_product` (`product_id`),
    INDEX `idx_reviews_user` (`user_id`),
    INDEX `idx_reviews_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Đánh giá và bình luận sản phẩm';

-- 13. Bảng wishlists: Danh sách sản phẩm yêu thích
CREATE TABLE `wishlists` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL COMMENT 'FK tới users',
    `product_id` INT NOT NULL COMMENT 'FK tới products',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_wishlists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wishlists_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_wishlists_user_product` (`user_id`, `product_id`),
    INDEX `idx_wishlists_user` (`user_id`),
    INDEX `idx_wishlists_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh sách sản phẩm yêu thích';

-- View tương thích backward cho bảng wishlist số ít
CREATE OR REPLACE VIEW `wishlist` AS SELECT * FROM `wishlists`;

-- 14. Bảng refresh_tokens: Quản lý JWT Refresh Token đăng nhập
CREATE TABLE `refresh_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL COMMENT 'FK tới users',
    `token_hash` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Hash của refresh token',
    `expires_at` DATETIME NOT NULL COMMENT 'Thời điểm token hết hiệu lực',
    `revoked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 nếu token đã bị hủy thu hồi',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_refresh_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    INDEX `idx_refresh_tokens_user` (`user_id`),
    INDEX `idx_refresh_tokens_hash` (`token_hash`),
    INDEX `idx_refresh_tokens_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quản lý phiên đăng nhập và refresh token';

-- =============================================================================
-- NHÓM 4: INTELLIGENCE & AUDIT (GỢI Ý SẢN PHẨM, TRACKING & NHẬT KÝ HỆ THỐNG)
-- =============================================================================

-- 15. Bảng product_views: Lịch sử và sự kiện xem sản phẩm (Tracking phục vụ thuật toán)
CREATE TABLE `product_views` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL COMMENT 'FK tới products',
    `user_id` INT DEFAULT NULL COMMENT 'FK tới users nếu đã đăng nhập',
    `session_id` VARCHAR(100) DEFAULT NULL COMMENT 'Mã phiên khách vãng lai',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Địa chỉ IP truy cập',
    `viewed_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm xem sản phẩm',
    CONSTRAINT `fk_product_views_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_views_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_product_views_product` (`product_id`),
    INDEX `idx_product_views_user` (`user_id`),
    INDEX `idx_product_views_time` (`viewed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký xem sản phẩm phục vụ gợi ý cá nhân hóa';

-- 16. Bảng product_similarity: Độ tương đồng sản phẩm (Cosine Similarity & Apriori Baskets)
CREATE TABLE `product_similarity` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL COMMENT 'Sản phẩm gốc',
    `similar_product_id` INT NOT NULL COMMENT 'Sản phẩm tương tự được gợi ý',
    `score` DECIMAL(5, 4) NOT NULL DEFAULT 0.0000 COMMENT 'Điểm tương đồng từ 0.0000 đến 1.0000',
    `algorithm` VARCHAR(50) NOT NULL DEFAULT 'cosine' COMMENT 'Thuật toán: cosine (đặc trưng cấu hình) / apriori (thường mua cùng)',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sim_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sim_target` FOREIGN KEY (`similar_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_similarity_pair_algo` (`product_id`, `similar_product_id`, `algorithm`),
    INDEX `idx_similarity_source` (`product_id`),
    INDEX `idx_similarity_target` (`similar_product_id`),
    INDEX `idx_similarity_score` (`score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Độ tương đồng sản phẩm phục vụ thuật toán Recommendation Engine';

-- 17. Bảng audit_logs: Nhật ký hoạt động quản trị viên và hệ thống
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL COMMENT 'FK tới users (người thực hiện thao tác)',
    `user_name` VARCHAR(255) NOT NULL COMMENT 'Tên hoặc email người thao tác',
    `action` VARCHAR(100) NOT NULL COMMENT 'Tên hành động (CREATE_PRODUCT, UPDATE_ORDER...)',
    `description` TEXT DEFAULT NULL COMMENT 'Mô tả chi tiết thay đổi',
    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Địa chỉ IP thực hiện thao tác',
    `user_agent` VARCHAR(255) DEFAULT NULL COMMENT 'Trình duyệt / Thiết bị client',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_audit_logs_user` (`user_id`),
    INDEX `idx_audit_logs_action` (`action`),
    INDEX `idx_audit_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký thao tác hệ thống';

SET FOREIGN_KEY_CHECKS = 1;
