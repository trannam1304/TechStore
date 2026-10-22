# -*- coding: utf-8 -*-
"""
Script tạo file database/seed.sql chuẩn hóa và đồng bộ database/data.json
Đáp ứng nhiệm vụ T05:
- >= 40 sản phẩm (thực tế 42 sản phẩm)
- 8 hãng (Apple, Samsung, Xiaomi, ASUS, Sony, Dell, Anker, Logitech)
- 5 danh mục (Điện thoại, Laptop, Tablet, Âm thanh, Smartwatch & Phụ kiện)
- User mẫu (2 Admin + 5 Customer)
- ~30 đơn mẫu (thực tế 30 đơn, Order items liên kết phục vụ thuật toán gợi ý Apriori / Cosine)
- Đầy đủ bảng phụ: product_images, product_specs, payments, reviews, wishlists, coupons, product_views, product_similarity, refresh_tokens, audit_logs.
"""

import json
import os

# 1. Users
users = [
    {
        "id": 1,
        "name": "Hữu Nam (Tech Lead)",
        "email": "admin@techstore.local",
        "password_hash": "$2y$10$Ybdv3nmywSWPJHfZ3ctjw.WnqsMxlJDYIeFTNTlWsCA3msHJAtPdu",
        "role": "admin",
        "phone": "0901234567",
        "address": "227 Nguyễn Văn Cừ, Phường 4, Quận 5, TP.HCM",
        "avatar": "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=200",
        "status": "active",
        "created_at": "2026-01-01 08:00:00"
    },
    {
        "id": 2,
        "name": "Tuấn Kiệt",
        "email": "user@techstore.local",
        "password_hash": "$2y$10$/dkVsYj6pdq.uOBODUyrjO4bDu8ojB1sQ82KXU2RH5ttHB0HUD56q",
        "role": "customer",
        "phone": "0987654321",
        "address": "123 Cách Mạng Tháng 8, Phường 7, Quận 10, TP.HCM",
        "avatar": "https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=200",
        "status": "active",
        "created_at": "2026-02-15 09:30:00"
    },
    {
        "id": 3,
        "name": "Thành Trung",
        "email": "trung@techstore.local",
        "password_hash": "$2y$10$iX30CqZAu2KOMjcnNgVOPu7yE4nGuqCHcEb4PYKFF/0kz5pqbigdK",
        "role": "admin",
        "phone": "0912345678",
        "address": "456 Hai Bà Trưng, Phường Tân Định, Quận 1, TP.HCM",
        "avatar": "https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200",
        "status": "active",
        "created_at": "2026-03-01 10:00:00"
    },
    {
        "id": 4,
        "name": "Nguyễn Hương Lan",
        "email": "lan.nguyen@gmail.com",
        "password_hash": "$2y$10$/dkVsYj6pdq.uOBODUyrjO4bDu8ojB1sQ82KXU2RH5ttHB0HUD56q",
        "role": "customer",
        "phone": "0934567890",
        "address": "88 Điện Biên Phủ, Phường Đa Kao, Quận 1, TP.HCM",
        "avatar": "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200",
        "status": "active",
        "created_at": "2026-03-10 14:15:00"
    },
    {
        "id": 5,
        "name": "Trần Minh Hoàng",
        "email": "hoang.tran@gmail.com",
        "password_hash": "$2y$10$/dkVsYj6pdq.uOBODUyrjO4bDu8ojB1sQ82KXU2RH5ttHB0HUD56q",
        "role": "customer",
        "phone": "0978112233",
        "address": "15 Lê Duẩn, Phường Bến Nghé, Quận 1, TP.HCM",
        "avatar": "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200",
        "status": "active",
        "created_at": "2026-04-05 11:20:00"
    },
    {
        "id": 6,
        "name": "Lê Nhật Quang",
        "email": "quang.le@gmail.com",
        "password_hash": "$2y$10$/dkVsYj6pdq.uOBODUyrjO4bDu8ojB1sQ82KXU2RH5ttHB0HUD56q",
        "role": "customer",
        "phone": "0918776655",
        "address": "42 Nguyễn Huệ, Phường Bến Nghé, Quận 1, TP.HCM",
        "avatar": "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200",
        "status": "active",
        "created_at": "2026-05-12 16:40:00"
    },
    {
        "id": 7,
        "name": "Phạm Phương Mai",
        "email": "mai.pham@gmail.com",
        "password_hash": "$2y$10$/dkVsYj6pdq.uOBODUyrjO4bDu8ojB1sQ82KXU2RH5ttHB0HUD56q",
        "role": "customer",
        "phone": "0909887766",
        "address": "254 Hoàng Văn Thụ, Phường 4, Quận Tân Bình, TP.HCM",
        "avatar": "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200",
        "status": "active",
        "created_at": "2026-06-01 08:50:00"
    }
]

# 2. Categories (5 danh mục)
categories = [
    {"id": 1, "name": "Điện thoại", "icon": "📱", "slug": "dien-thoai", "description": "Smartphone cao cấp từ Apple, Samsung, Xiaomi, ROG Phone", "product_count": 10},
    {"id": 2, "name": "Laptop", "icon": "💻", "slug": "laptop", "description": "Laptop doanh nhân, văn phòng mỏng nhẹ và Laptop Gaming hiệu năng cao", "product_count": 10},
    {"id": 3, "name": "Tablet", "icon": "📋", "slug": "tablet", "description": "Máy tính bảng phục vụ học tập, sáng tạo đồ họa và giải trí", "product_count": 6},
    {"id": 4, "name": "Âm thanh", "icon": "🎧", "slug": "am-thanh", "description": "Tai nghe chống ồn chủ động, tai nghe True Wireless và loa bluetooth", "product_count": 8},
    {"id": 5, "name": "Smartwatch & Phụ kiện", "icon": "⌚", "slug": "smartwatch-phu-kien", "description": "Đồng hồ thông minh theo dõi sức khỏe, củ cáp sạc nhanh GaN và chuột phím", "product_count": 8}
]

# 3. Brands (8 hãng)
brands = [
    {"id": 1, "name": "Apple", "slug": "apple", "logo": "", "description": "Thương hiệu công nghệ hàng đầu thế giới với hệ sinh thái iPhone, Mac, iPad cao cấp."},
    {"id": 2, "name": "Samsung", "slug": "samsung", "logo": "Samsung", "description": "Gã khổng lồ công nghệ Hàn Quốc tiên phong màn hình AMOLED và trí tuệ nhân tạo Galaxy AI."},
    {"id": 3, "name": "Xiaomi", "slug": "xiaomi", "logo": "Xiaomi", "description": "Tập đoàn công nghệ với định vị cấu hình vượt trội trên giá thành và camera hợp tác cùng Leica."},
    {"id": 4, "name": "ASUS", "slug": "asus", "logo": "ASUS", "description": "Nhà sản xuất máy tính và thiết bị gaming hàng đầu với dòng Zenbook tinh tế và ROG huyền thoại."},
    {"id": 5, "name": "Sony", "slug": "sony", "logo": "Sony", "description": "Biểu tượng âm thanh và hình ảnh Nhật Bản với chuẩn âm thanh đỉnh cao Hi-Res và chống ồn 1000X."},
    {"id": 6, "name": "Dell", "slug": "dell", "logo": "Dell", "description": "Thương hiệu máy tính xách tay và máy trạm bền bỉ trứ danh cùng dòng cao cấp Dell XPS đột phá."},
    {"id": 7, "name": "Anker", "slug": "anker", "logo": "Anker", "description": "Hãng phụ kiện sạc nhanh và âm thanh cá nhân số 1 toàn cầu ứng dụng vật liệu bán dẫn GaN."},
    {"id": 8, "name": "Logitech", "slug": "logitech", "logo": "Logitech", "description": "Thương hiệu Thụy Sĩ đứng đầu thế giới về thiết bị ngoại vi văn phòng công thái học và gaming cao cấp."}
]

# 4. Products (42 sản phẩm)
products = [
    # 1-10: Điện thoại (Category 1)
    {
        "id": 1, "name": "iPhone 16 Pro Max 256GB", "slug": "iphone-16-pro-max-256gb",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 1, "brand_name": "Apple",
        "price": 34990000, "original_price": 37990000, "stock": 25, "rating": 4.95, "review_count": 142, "view_count": 1850,
        "image": "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&auto=format&fit=crop&q=80", "https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "256GB", "chipset": "Apple A18 Pro 3nm", "screen": "6.9 inch Super Retina XDR OLED 120Hz",
        "battery": "4685 mAh, Sạc nhanh 30W", "camera": "Chính 48MP + Góc rộng 48MP + Tele 5x 12MP",
        "description": "iPhone 16 Pro Max thiết kế Titan sa mạc đẳng cấp, vi xử lý A18 Pro mạnh mẽ và nút Camera Control hoàn toàn mới mang đến trải nghiệm quay chụp điện ảnh đỉnh cao.",
        "featured": 1, "created_at": "2026-09-20 00:00:00"
    },
    {
        "id": 2, "name": "iPhone 16 128GB", "slug": "iphone-16-128gb",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 1, "brand_name": "Apple",
        "price": 21990000, "original_price": 23990000, "stock": 35, "rating": 4.88, "review_count": 89, "view_count": 1200,
        "image": "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "128GB", "chipset": "Apple A18 3nm", "screen": "6.1 inch Super Retina XDR OLED",
        "battery": "3561 mAh, Sạc nhanh 25W", "camera": "48MP Fusion + 12MP Ultra Wide",
        "description": "iPhone 16 với màu sắc Ultramarine hiện đại, nút chụp Camera Control và hiệu năng bứt phá phục vụ Apple Intelligence.",
        "featured": 1, "created_at": "2026-09-22 00:00:00"
    },
    {
        "id": 3, "name": "iPhone 15 Pro 128GB", "slug": "iphone-15-pro-128gb",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 1, "brand_name": "Apple",
        "price": 24990000, "original_price": 28990000, "stock": 18, "rating": 4.85, "review_count": 110, "view_count": 950,
        "image": "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "128GB", "chipset": "Apple A17 Pro", "screen": "6.1 inch Super Retina XDR OLED 120Hz ProMotion",
        "battery": "3274 mAh, Sạc nhanh 20W", "camera": "48MP + 12MP + 12MP 3x",
        "description": "iPhone 15 Pro khung viền Titan cấp hàng không vũ trụ nhẹ và bền, cổng USB-C tốc độ cao và nút Action tùy biến.",
        "featured": 0, "created_at": "2026-08-10 00:00:00"
    },
    {
        "id": 4, "name": "Samsung Galaxy S24 Ultra 5G 12GB/256GB", "slug": "samsung-galaxy-s24-ultra",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 2, "brand_name": "Samsung",
        "price": 29990000, "original_price": 33990000, "stock": 20, "rating": 4.90, "review_count": 105, "view_count": 1600,
        "image": "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&auto=format&fit=crop&q=80"],
        "ram": "12GB", "rom": "256GB", "chipset": "Snapdragon 8 Gen 3 for Galaxy", "screen": "6.8 inch Dynamic AMOLED 2X 120Hz 2600 nits",
        "battery": "5000 mAh, Sạc nhanh 45W", "camera": "200MP + 50MP + 12MP + 10MP, Zoom 100x",
        "description": "Galaxy S24 Ultra khai mở kỷ nguyên Galaxy AI, khung viền Titan sang trọng và bút S-Pen quyền năng.",
        "featured": 1, "created_at": "2026-08-15 00:00:00"
    },
    {
        "id": 5, "name": "Samsung Galaxy S24+ 12GB/256GB", "slug": "samsung-galaxy-s24-plus",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 2, "brand_name": "Samsung",
        "price": 21490000, "original_price": 26990000, "stock": 22, "rating": 4.82, "review_count": 64, "view_count": 820,
        "image": "https://images.unsplash.com/photo-1580910051074-3eb694886505?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800&auto=format&fit=crop&q=80"],
        "ram": "12GB", "rom": "256GB", "chipset": "Exynos 2400 Deca-Core", "screen": "6.7 inch QHD+ Dynamic AMOLED 2X 120Hz",
        "battery": "4900 mAh, Sạc 45W", "camera": "50MP + 12MP + 10MP 3x",
        "description": "Galaxy S24+ sở hữu màn hình QHD+ sắc nét, dung lượng pin tăng cường và thiết kế phẳng thanh lịch.",
        "featured": 0, "created_at": "2026-07-20 00:00:00"
    },
    {
        "id": 6, "name": "Samsung Galaxy Z Fold6 5G 256GB", "slug": "samsung-galaxy-z-fold6",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 2, "brand_name": "Samsung",
        "price": 41990000, "original_price": 43990000, "stock": 12, "rating": 4.87, "review_count": 45, "view_count": 980,
        "image": "https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=800&auto=format&fit=crop&q=80"],
        "ram": "12GB", "rom": "256GB", "chipset": "Snapdragon 8 Gen 3 for Galaxy", "screen": "7.6 inch Foldable Dynamic AMOLED 2X 120Hz",
        "battery": "4400 mAh, Sạc 25W", "camera": "50MP + 12MP + 10MP",
        "description": "Galaxy Z Fold6 thiết kế phẳng mỏng nhẹ hơn bao giờ hết, bản lề Flex Hinge kép mở ra không gian đa nhiệm đỉnh cao.",
        "featured": 1, "created_at": "2026-08-01 00:00:00"
    },
    {
        "id": 7, "name": "Xiaomi 14 Ultra 5G 16GB/512GB Leica", "slug": "xiaomi-14-ultra",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 28990000, "original_price": 32990000, "stock": 15, "rating": 4.89, "review_count": 78, "view_count": 1100,
        "image": "https://images.unsplash.com/photo-1567581935884-3349723552ca?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1567581935884-3349723552ca?w=800&auto=format&fit=crop&q=80"],
        "ram": "16GB", "rom": "512GB", "chipset": "Snapdragon 8 Gen 3", "screen": "6.73 inch LTPO AMOLED 120Hz 3000 nits",
        "battery": "5000 mAh, Sạc nhanh 90W", "camera": "4 camera Leica 50MP cảm biến 1 inch",
        "description": "Xiaomi 14 Ultra hợp tác cùng Leica mang lại hệ thống 4 ống kính quang học Summilux, khẩu độ vô cấp cho ảnh chụp xuất sắc.",
        "featured": 1, "created_at": "2026-06-15 00:00:00"
    },
    {
        "id": 8, "name": "Xiaomi 14T Pro 5G 12GB/512GB", "slug": "xiaomi-14t-pro",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 16990000, "original_price": 17990000, "stock": 30, "rating": 4.80, "review_count": 52, "view_count": 750,
        "image": "https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&auto=format&fit=crop&q=80"],
        "ram": "12GB", "rom": "512GB", "chipset": "MediaTek Dimensity 9300+", "screen": "6.67 inch AMOLED 144Hz 4000 nits",
        "battery": "5000 mAh, Sạc 120W HyperCharge", "camera": "50MP Leica + 50MP Tele + 12MP Ultra Wide",
        "description": "Xiaomi 14T Pro camera Leica thế hệ mới, sạc siêu tốc 120W và hiệu năng Dimensity 9300+ cực khủng.",
        "featured": 0, "created_at": "2026-09-28 00:00:00"
    },
    {
        "id": 9, "name": "ASUS ROG Phone 8 Pro 16GB/512GB", "slug": "asus-rog-phone-8-pro",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 4, "brand_name": "ASUS",
        "price": 27990000, "original_price": 29990000, "stock": 10, "rating": 4.92, "review_count": 63, "view_count": 890,
        "image": "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=800&auto=format&fit=crop&q=80"],
        "ram": "16GB", "rom": "512GB", "chipset": "Snapdragon 8 Gen 3", "screen": "6.78 inch Samsung AMOLED 165Hz LTPO",
        "battery": "5500 mAh, Sạc 65W HyperCharge", "camera": "50MP Sony IMX890 chống rung Gimbal 6 trục",
        "description": "Quái vật gaming ROG Phone 8 Pro với màn hình LED AniMe Vision mặt lưng, tản nhiệt GameCool 8 và nút cảm ứng AirTrigger.",
        "featured": 1, "created_at": "2026-07-05 00:00:00"
    },
    {
        "id": 10, "name": "Samsung Galaxy A55 5G 8GB/128GB", "slug": "samsung-galaxy-a55-5g",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 2, "brand_name": "Samsung",
        "price": 9490000, "original_price": 9990000, "stock": 40, "rating": 4.75, "review_count": 115, "view_count": 1400,
        "image": "https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "128GB", "chipset": "Exynos 1480 4nm", "screen": "6.6 inch Super AMOLED 120Hz",
        "battery": "5000 mAh, Sạc 25W", "camera": "50MP OIS + 12MP + 5MP",
        "description": "Galaxy A55 viền kim loại sang trọng, bảo mật cấp phần cứng Knox Vault và kháng nước bụi chuẩn IP67.",
        "featured": 0, "created_at": "2026-05-10 00:00:00"
    },

    # 11-20: Laptop (Category 2)
    {
        "id": 11, "name": "MacBook Pro 14 M3 Pro 18GB/512GB Space Black", "slug": "macbook-pro-14-m3-pro",
        "category_id": 2, "category_name": "Laptop", "brand_id": 1, "brand_name": "Apple",
        "price": 49990000, "original_price": 52990000, "stock": 15, "rating": 4.96, "review_count": 68, "view_count": 1500,
        "image": "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&auto=format&fit=crop&q=80"],
        "ram": "18GB", "rom": "512GB SSD", "chipset": "Apple M3 Pro (11 CPU, 14 GPU)", "screen": "14.2 inch Liquid Retina XDR 120Hz ProMotion",
        "battery": "70Wh, Thời lượng pin 18 giờ", "camera": "1080p FaceTime HD",
        "description": "MacBook Pro 14 M3 Pro hiệu năng đồ họa ấn tượng với Dynamic Caching, tản nhiệt tối ưu và màu Space Black cuốn hút.",
        "featured": 1, "created_at": "2026-07-10 00:00:00"
    },
    {
        "id": 12, "name": "MacBook Air 13 M3 8GB/256GB Midnight", "slug": "macbook-air-13-m3",
        "category_id": 2, "category_name": "Laptop", "brand_id": 1, "brand_name": "Apple",
        "price": 26990000, "original_price": 27990000, "stock": 28, "rating": 4.88, "review_count": 92, "view_count": 1350,
        "image": "https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "256GB SSD", "chipset": "Apple M3 (8 CPU, 8 GPU)", "screen": "13.6 inch Liquid Retina True Tone",
        "battery": "52.6Wh, Pin lên tới 18 giờ", "camera": "1080p FaceTime HD",
        "description": "MacBook Air M3 siêu nhẹ chỉ 1.24kg, hỗ trợ xuất 2 màn hình ngoài cùng thời lượng pin bền bỉ cả ngày làm việc.",
        "featured": 1, "created_at": "2026-05-20 00:00:00"
    },
    {
        "id": 13, "name": "MacBook Pro 16 M3 Max 36GB/1TB Silver", "slug": "macbook-pro-16-m3-max",
        "category_id": 2, "category_name": "Laptop", "brand_id": 1, "brand_name": "Apple",
        "price": 89990000, "original_price": 92990000, "stock": 6, "rating": 4.98, "review_count": 31, "view_count": 720,
        "image": "https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=800&auto=format&fit=crop&q=80"],
        "ram": "36GB", "rom": "1TB SSD", "chipset": "Apple M3 Max (14 CPU, 30 GPU)", "screen": "16.2 inch Liquid Retina XDR 1600 nits 120Hz",
        "battery": "100Wh, Pin 22 giờ", "camera": "1080p FaceTime HD",
        "description": "Đỉnh cao máy trạm di động render 3D chuyên sâu, dựng phim 8K ProRes và huấn luyện mô hình AI không nghẽn cổ chai.",
        "featured": 0, "created_at": "2026-04-10 00:00:00"
    },
    {
        "id": 14, "name": "Laptop ASUS ROG Zephyrus G16 OLED RTX 4070", "slug": "asus-rog-zephyrus-g16-oled",
        "category_id": 2, "category_name": "Laptop", "brand_id": 4, "brand_name": "ASUS",
        "price": 59990000, "original_price": 64990000, "stock": 8, "rating": 4.91, "review_count": 45, "view_count": 1100,
        "image": "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB LPDDR5X", "rom": "1TB PCIe 4.0 SSD", "chipset": "Intel Core Ultra 9 185H", "screen": "16.0 inch 2.5K OLED 240Hz 0.2ms Nebula",
        "battery": "90Wh, Sạc 100W Type-C", "camera": "1080p FHD IR Windows Hello",
        "description": "Laptop Gaming siêu mỏng nhẹ khung nhôm CNC, màn hình OLED 240Hz sắc nét đỉnh cao cùng card đồ họa RTX 4070.",
        "featured": 1, "created_at": "2026-06-01 00:00:00"
    },
    {
        "id": 15, "name": "Laptop ASUS Zenbook 14 OLED UX3405 Core Ultra 7", "slug": "asus-zenbook-14-oled-ux3405",
        "category_id": 2, "category_name": "Laptop", "brand_id": 4, "brand_name": "ASUS",
        "price": 29490000, "original_price": 31990000, "stock": 20, "rating": 4.86, "review_count": 56, "view_count": 890,
        "image": "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&auto=format&fit=crop&q=80"],
        "ram": "16GB LPDDR5X", "rom": "1TB NVMe SSD", "chipset": "Intel Core Ultra 7 155H tích hợp NPU AI", "screen": "14.0 inch 3K OLED 120Hz 100% DCI-P3",
        "battery": "75Wh, Pin 15 giờ", "camera": "FHD IR Camera",
        "description": "Zenbook 14 OLED chuẩn Intel Evo mỏng 14.9mm nặng 1.2kg, màn hình 3K OLED tuyệt mỹ và xử lý AI tăng tốc.",
        "featured": 1, "created_at": "2026-05-18 00:00:00"
    },
    {
        "id": 16, "name": "Laptop Gaming ASUS TUF Gaming A15 Ryzen 7 RTX 4060", "slug": "asus-tuf-gaming-a15",
        "category_id": 2, "category_name": "Laptop", "brand_id": 4, "brand_name": "ASUS",
        "price": 25990000, "original_price": 28990000, "stock": 25, "rating": 4.79, "review_count": 74, "view_count": 980,
        "image": "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=80"],
        "ram": "16GB DDR5", "rom": "512GB PCIe SSD", "chipset": "AMD Ryzen 7 7735HS", "screen": "15.6 inch FHD IPS 144Hz 100% sRGB",
        "battery": "90Wh, Sạc nhanh", "camera": "720p HD Camera",
        "description": "Laptop gaming độ bền chuẩn quân đội MIL-STD-810H, đồ họa RTX 4060 chiến mượt mọi tựa game AAA đỉnh cao.",
        "featured": 0, "created_at": "2026-06-12 00:00:00"
    },
    {
        "id": 17, "name": "Dell XPS 14 9440 Core Ultra 7 OLED RTX 4050", "slug": "dell-xps-14-9440",
        "category_id": 2, "category_name": "Laptop", "brand_id": 6, "brand_name": "Dell",
        "price": 54990000, "original_price": 58990000, "stock": 10, "rating": 4.93, "review_count": 38, "view_count": 790,
        "image": "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB LPDDR5X", "rom": "1TB SSD", "chipset": "Intel Core Ultra 7 155H", "screen": "14.5 inch 3.2K OLED Touch 120Hz",
        "battery": "69.5Wh, Sạc 100W", "camera": "1080p FHD IR",
        "description": "Dell XPS 14 ngôn ngữ tương lai kính nguyên khối, trackpad tàng hình cảm ứng lực và bàn phím Zero-lattice sang trọng.",
        "featured": 1, "created_at": "2026-07-25 00:00:00"
    },
    {
        "id": 18, "name": "Dell XPS 16 9640 Core Ultra 9 RTX 4070", "slug": "dell-xps-16-9640",
        "category_id": 2, "category_name": "Laptop", "brand_id": 6, "brand_name": "Dell",
        "price": 76990000, "original_price": 81990000, "stock": 5, "rating": 4.95, "review_count": 27, "view_count": 650,
        "image": "https://images.unsplash.com/photo-1587614382346-4ec70e388b28?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1587614382346-4ec70e388b28?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB LPDDR5X", "rom": "1TB PCIe SSD", "chipset": "Intel Core Ultra 9 185H", "screen": "16.3 inch 4K+ OLED Touch InfinityEdge",
        "battery": "99.5Wh, Sạc 130W", "camera": "1080p FHD IR",
        "description": "Kiệt tác công nghệ Dell XPS 16 màn hình 4K OLED tràn viền vô cực cùng sức mạnh xử lý đồ họa chuyên sâu.",
        "featured": 0, "created_at": "2026-05-30 00:00:00"
    },
    {
        "id": 19, "name": "Dell Inspiron 14 5440 Core i5-1334U", "slug": "dell-inspiron-14-5440",
        "category_id": 2, "category_name": "Laptop", "brand_id": 6, "brand_name": "Dell",
        "price": 16490000, "original_price": 18490000, "stock": 30, "rating": 4.76, "review_count": 85, "view_count": 1120,
        "image": "https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=800&auto=format&fit=crop&q=80"],
        "ram": "16GB DDR5", "rom": "512GB SSD", "chipset": "Intel Core i5-1334U", "screen": "14.0 inch FHD+ 16:10 Anti-Glare",
        "battery": "54Wh", "camera": "1080p FHD Camera",
        "description": "Dell Inspiron 14 bền bỉ, bàn phím gõ êm ái, cấu hình 16GB RAM mượt mà phục vụ học tập và công việc văn phòng.",
        "featured": 0, "created_at": "2026-04-18 00:00:00"
    },
    {
        "id": 20, "name": "Xiaomi RedmiBook Pro 15 Ryzen 7 8845HS", "slug": "xiaomi-redmibook-pro-15",
        "category_id": 2, "category_name": "Laptop", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 18990000, "original_price": 20990000, "stock": 20, "rating": 4.82, "review_count": 49, "view_count": 710,
        "image": "https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB LPDDR5X", "rom": "1TB PCIe 4.0 SSD", "chipset": "AMD Ryzen 7 8845HS tích hợp Ryzen AI", "screen": "15.6 inch 3.2K 165Hz 500 nits HDR",
        "battery": "80Wh, Sạc 100W GaN", "camera": "1080p Full HD",
        "description": "RedmiBook Pro 15 vỏ nhôm unibody cao cấp, màn hình 3.2K 165Hz và dung lượng pin khủng 80Wh.",
        "featured": 0, "created_at": "2026-06-25 00:00:00"
    },

    # 21-26: Tablet (Category 3)
    {
        "id": 21, "name": "iPad Pro 11 M4 OLED 256GB Wi-Fi Space Black", "slug": "ipad-pro-11-m4-oled",
        "category_id": 3, "category_name": "Tablet", "brand_id": 1, "brand_name": "Apple",
        "price": 27990000, "original_price": 28990000, "stock": 20, "rating": 4.97, "review_count": 72, "view_count": 1300,
        "image": "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "256GB", "chipset": "Apple M4 9-core CPU, 10-core GPU", "screen": "11.0 inch Ultra Retina XDR Tandem OLED 120Hz",
        "battery": "31.29 Wh, Pin 10 giờ", "camera": "12MP Wide LiDAR + 12MP Landscape Center Stage",
        "description": "iPad Pro M4 thiết kế mỏng không tưởng chỉ 5.3mm, màn hình Tandem OLED đột phá tái định nghĩa công nghệ hiển thị.",
        "featured": 1, "created_at": "2026-05-25 00:00:00"
    },
    {
        "id": 22, "name": "iPad Air 6 M2 11 inch 128GB Wi-Fi", "slug": "ipad-air-6-m2-11-inch",
        "category_id": 3, "category_name": "Tablet", "brand_id": 1, "brand_name": "Apple",
        "price": 16490000, "original_price": 17990000, "stock": 30, "rating": 4.88, "review_count": 88, "view_count": 1420,
        "image": "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1561154464-82e9adf32764?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "128GB", "chipset": "Apple M2 8-core CPU", "screen": "11.0 inch Liquid Retina True Tone",
        "battery": "28.93 Wh, Pin 10 giờ", "camera": "12MP Wide trước & sau",
        "description": "iPad Air 6 trang bị vi xử lý Apple M2 mạnh mẽ vượt trội, hỗ trợ bút Apple Pencil Pro thế hệ mới.",
        "featured": 1, "created_at": "2026-05-22 00:00:00"
    },
    {
        "id": 23, "name": "iPad Gen 10 10.9 inch 64GB Wi-Fi Pink", "slug": "ipad-gen-10-64gb",
        "category_id": 3, "category_name": "Tablet", "brand_id": 1, "brand_name": "Apple",
        "price": 9490000, "original_price": 10990000, "stock": 45, "rating": 4.81, "review_count": 130, "view_count": 1600,
        "image": "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=800&auto=format&fit=crop&q=80"],
        "ram": "4GB", "rom": "64GB", "chipset": "Apple A14 Bionic", "screen": "10.9 inch Liquid Retina IPS",
        "battery": "28.6 Wh, Pin 10 giờ", "camera": "12MP Wide trước & sau",
        "description": "iPad Gen 10 thiết kế vuông vức tràn viền, 4 sắc màu cá tính và cổng kết nối Type-C tiện dụng.",
        "featured": 0, "created_at": "2026-04-15 00:00:00"
    },
    {
        "id": 24, "name": "Samsung Galaxy Tab S9 Ultra 12GB/256GB S-Pen", "slug": "samsung-galaxy-tab-s9-ultra",
        "category_id": 3, "category_name": "Tablet", "brand_id": 2, "brand_name": "Samsung",
        "price": 23990000, "original_price": 27990000, "stock": 12, "rating": 4.92, "review_count": 53, "view_count": 850,
        "image": "https://images.unsplash.com/photo-1585792180666-f7547c6a28c5?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1585792180666-f7547c6a28c5?w=800&auto=format&fit=crop&q=80"],
        "ram": "12GB", "rom": "256GB", "chipset": "Snapdragon 8 Gen 2 for Galaxy", "screen": "14.6 inch Dynamic AMOLED 2X 120Hz",
        "battery": "11200 mAh, Sạc 45W", "camera": "13MP + 8MP sau, Kép 12MP trước",
        "description": "Galaxy Tab S9 Ultra màn hình khổng lồ 14.6 inch, chuẩn kháng nước IP68 đầu tiên và bút S-Pen sáng tạo.",
        "featured": 1, "created_at": "2026-06-18 00:00:00"
    },
    {
        "id": 25, "name": "Samsung Galaxy Tab S9 FE 6GB/128GB S-Pen", "slug": "samsung-galaxy-tab-s9-fe",
        "category_id": 3, "category_name": "Tablet", "brand_id": 2, "brand_name": "Samsung",
        "price": 8990000, "original_price": 9990000, "stock": 25, "rating": 4.79, "review_count": 67, "view_count": 920,
        "image": "https://images.unsplash.com/photo-1527698266440-12104e498b76?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1527698266440-12104e498b76?w=800&auto=format&fit=crop&q=80"],
        "ram": "6GB", "rom": "128GB", "chipset": "Exynos 1380", "screen": "10.9 inch IPS LCD 90Hz",
        "battery": "8000 mAh, Sạc 45W", "camera": "8MP sau, 12MP trước",
        "description": "Tab S9 FE kèm sẵn bút S-Pen trong hộp, thời lượng pin cả ngày dài và loa kép AKG sống động.",
        "featured": 0, "created_at": "2026-05-02 00:00:00"
    },
    {
        "id": 26, "name": "Xiaomi Pad 6S Pro 12.4 inch 8GB/256GB", "slug": "xiaomi-pad-6s-pro",
        "category_id": 3, "category_name": "Tablet", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 13990000, "original_price": 14990000, "stock": 20, "rating": 4.84, "review_count": 42, "view_count": 680,
        "image": "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1561154464-82e9adf32764?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "256GB", "chipset": "Snapdragon 8 Gen 2", "screen": "12.4 inch 3K 144Hz 3:2 tỉ lệ vàng",
        "battery": "10000 mAh, Sạc 120W HyperCharge", "camera": "50MP sau, 32MP trước",
        "description": "Xiaomi Pad 6S Pro màn hình 3K 144Hz tỉ lệ 3:2 làm việc tối ưu, sạc đầy 100% pin chỉ trong 35 phút.",
        "featured": 0, "created_at": "2026-06-08 00:00:00"
    },

    # 27-34: Âm thanh (Category 4)
    {
        "id": 27, "name": "Tai nghe Sony WH-1000XM5 Chống Ồn Flagship", "slug": "sony-wh-1000xm5",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 5, "brand_name": "Sony",
        "price": 7490000, "original_price": 8990000, "stock": 30, "rating": 4.93, "review_count": 168, "view_count": 1950,
        "image": "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Sony Integrated Processor V1 + HD QN1", "screen": "Driver 30mm sợi carbon cao cấp",
        "battery": "Pin 30 giờ (bật ANC), Sạc 3 phút nghe 3 giờ", "camera": "8 micro AI lọc gió",
        "description": "Công nghệ chống ồn số 1 thị trường, âm thanh chuẩn Hi-Res Audio Wireless LDAC đem lại trải nghiệm thư thái tuyệt đối.",
        "featured": 1, "created_at": "2026-05-18 00:00:00"
    },
    {
        "id": 28, "name": "Tai nghe Sony WF-1000XM5 True Wireless", "slug": "sony-wf-1000xm5",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 5, "brand_name": "Sony",
        "price": 5990000, "original_price": 6990000, "stock": 25, "rating": 4.87, "review_count": 94, "view_count": 1250,
        "image": "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Sony V2 + QN2e Dual Processor", "screen": "Driver Dynamic X 8.4mm tái tạo âm trầm sâu",
        "battery": "Pin 8 giờ + 16 giờ hộp sạc, IPX4", "camera": "3 micro mỗi bên + Cảm biến xương Bone conduction",
        "description": "Tai nghe in-ear chống ồn đỉnh cao, kích thước thu gọn 25% đeo vừa vặn êm ái suốt cả ngày dài.",
        "featured": 1, "created_at": "2026-04-20 00:00:00"
    },
    {
        "id": 29, "name": "Loa Bluetooth Sony SRS-XE300 Kháng nước IP67", "slug": "sony-srs-xe300",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 5, "brand_name": "Sony",
        "price": 3490000, "original_price": 4290000, "stock": 20, "rating": 4.80, "review_count": 51, "view_count": 620,
        "image": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Bộ khuếch tán âm Line-Shape Diffuser", "screen": "Màng loa X-Balanced Speaker Unit",
        "battery": "Pin 24 giờ, Chống sốc, chống nước IP67", "camera": "Micro đàm thoại khử vọng",
        "description": "Loa di động âm thanh lan tỏa rộng khắp không gian, chống nước chống bụi chuẩn biển dã ngoại.",
        "featured": 0, "created_at": "2026-03-15 00:00:00"
    },
    {
        "id": 30, "name": "Tai nghe Apple AirPods Pro 2 USB-C MagSafe", "slug": "apple-airpods-pro-2-usb-c",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 1, "brand_name": "Apple",
        "price": 5490000, "original_price": 6190000, "stock": 40, "rating": 4.94, "review_count": 210, "view_count": 2300,
        "image": "https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Apple H2 chip + U1 chip hộp sạc", "screen": "Driver Apple độ méo thấp tùy chỉnh",
        "battery": "Pin 6 giờ (30 giờ kèm hộp sạc), Sạc MagSafe/USB-C", "camera": "Chống ồn chủ động gấp 2 lần + Adaptive Audio",
        "description": "AirPods Pro 2 âm thanh không gian cá nhân hóa, chống ồn chủ động vượt trội và hộp sạc tìm kiếm Precision Finding.",
        "featured": 1, "created_at": "2026-06-20 00:00:00"
    },
    {
        "id": 31, "name": "Tai nghe Apple AirPods Max USB-C 2024", "slug": "apple-airpods-max-usb-c",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 1, "brand_name": "Apple",
        "price": 13290000, "original_price": 13990000, "stock": 10, "rating": 4.85, "review_count": 48, "view_count": 850,
        "image": "https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Apple H1 chip mỗi bên tai nghe", "screen": "Driver 40mm động học do Apple thiết kế",
        "battery": "Pin 20 giờ bật ANC và Âm thanh không gian", "camera": "9 micro lọc âm đàm thoại trong trẻo",
        "description": "Thiết kế trùm tai sang trọng với khung thép không gỉ, đệm tai dạng lưới thoáng khí và âm thanh độ trung thực cao.",
        "featured": 0, "created_at": "2026-09-21 00:00:00"
    },
    {
        "id": 32, "name": "Tai nghe Samsung Galaxy Buds3 Pro Galaxy AI", "slug": "samsung-galaxy-buds3-pro",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 2, "brand_name": "Samsung",
        "price": 4990000, "original_price": 5490000, "stock": 25, "rating": 4.82, "review_count": 60, "view_count": 780,
        "image": "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Dual Amp + Samsung Seamless Codec 24bit/96kHz", "screen": "Loa 2 chiều Woofer 10.5mm + Tweeter phẳng 6.1mm",
        "battery": "Pin 7 giờ + 30 giờ hộp sạc", "camera": "Dải đèn Blade Lights + Khử ồn thông minh AI",
        "description": "Thiết kế dạng thân mới tích hợp đèn LED Blade Lights, âm thanh Hi-Fi 24-bit và phiên dịch trực tiếp qua Galaxy AI.",
        "featured": 0, "created_at": "2026-07-28 00:00:00"
    },
    {
        "id": 33, "name": "Tai nghe Xiaomi Redmi Buds 5 Pro Chống Ồn 52dB", "slug": "xiaomi-redmi-buds-5-pro",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 1490000, "original_price": 1790000, "stock": 50, "rating": 4.77, "review_count": 82, "view_count": 950,
        "image": "https://images.unsplash.com/photo-1572536147248-ac59a8abfa4b?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1572536147248-ac59a8abfa4b?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Codec LHDC 5.0 chuẩn Hi-Res Audio Wireless", "screen": "Driver kép đồng trục loa trầm titan 11mm + gốm 10mm",
        "battery": "Pin 10 giờ (38 giờ hộp sạc), Sạc nhanh 5 phút", "camera": "Chống ồn sâu 52dB tần số siêu rộng 4kHz",
        "description": "Tai nghe chống ồn tốt nhất phân khúc dưới 2 triệu đồng, màng loa gốm cao cấp và âm thanh Hi-Res chi tiết.",
        "featured": 0, "created_at": "2026-05-12 00:00:00"
    },
    {
        "id": 34, "name": "Loa Bluetooth Anker Soundcore Motion Boom Plus 80W", "slug": "anker-soundcore-motion-boom-plus",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 7, "brand_name": "Anker",
        "price": 3290000, "original_price": 3990000, "stock": 20, "rating": 4.88, "review_count": 64, "view_count": 710,
        "image": "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Công nghệ tăng cường âm trầm BassUp", "screen": "Công suất 80W (2 Woofer 30W + 2 Tweeter 10W)",
        "battery": "Pin 13400 mAh, phát nhạc 20 giờ kiêm sạc dự phòng", "camera": "Chống nước và nổi trên mặt nước chuẩn IP67",
        "description": "Loa tiệc ngoài trời công suất bùng nổ 80W, chống nước hoàn hảo nổi được trên mặt nước cho các chuyến dã ngoại.",
        "featured": 0, "created_at": "2026-04-18 00:00:00"
    },

    # 35-42: Smartwatch & Phụ kiện (Category 5)
    {
        "id": 35, "name": "Apple Watch Ultra 2 GPS + Cellular 49mm Titanium", "slug": "apple-watch-ultra-2",
        "category_id": 5, "category_name": "Smartwatch & Phụ kiện", "brand_id": 1, "brand_name": "Apple",
        "price": 20990000, "original_price": 22990000, "stock": 15, "rating": 4.95, "review_count": 76, "view_count": 1200,
        "image": "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": "64GB", "chipset": "Apple S9 SiP 64-bit + Chip UWB thế hệ 2", "screen": "OLED LTPO Always-On 3000 nits đỉnh sáng",
        "battery": "Pin 36 giờ (72 giờ chế độ nguồn điện thấp)", "camera": "Còi báo động 86dB + Kháng nước 100m chuẩn lặn EN13319",
        "description": "Đồng hồ thể thao dã ngoại chuyên nghiệp, vỏ Titan tái chế bền bỉ và độ sáng màn hình 3000 nits thách thức ánh mặt trời.",
        "featured": 1, "created_at": "2026-05-05 00:00:00"
    },
    {
        "id": 36, "name": "Apple Watch Series 9 GPS 45mm Nhôm Midnight", "slug": "apple-watch-series-9-45mm",
        "category_id": 5, "category_name": "Smartwatch & Phụ kiện", "brand_id": 1, "brand_name": "Apple",
        "price": 10490000, "original_price": 11290000, "stock": 25, "rating": 4.86, "review_count": 92, "view_count": 1150,
        "image": "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": "64GB", "chipset": "Apple S9 SiP lõi tứ Neural Engine", "screen": "OLED LTPO Always-On 2000 nits",
        "battery": "Pin 18 giờ, Sạc nhanh 0-80% trong 45 phút", "camera": "Cảm biến ECG, SpO2, đo nhiệt độ cổ tay",
        "description": "Apple Watch Series 9 thao tác chạm 2 ngón Double Tap kỳ diệu, màn hình sáng gấp đôi và theo dõi sức khỏe chuyên sâu.",
        "featured": 1, "created_at": "2026-04-25 00:00:00"
    },
    {
        "id": 37, "name": "Samsung Galaxy Watch Ultra 47mm LTE Titanium", "slug": "samsung-galaxy-watch-ultra",
        "category_id": 5, "category_name": "Smartwatch & Phụ kiện", "brand_id": 2, "brand_name": "Samsung",
        "price": 16490000, "original_price": 17990000, "stock": 12, "rating": 4.90, "review_count": 48, "view_count": 870,
        "image": "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=800&auto=format&fit=crop&q=80"],
        "ram": "2GB", "rom": "32GB", "chipset": "Exynos W1000 3nm 5 nhân", "screen": "Super AMOLED 1.5 inch 3000 nits kính Sapphire",
        "battery": "590 mAh, Pin lên tới 100 giờ ở chế độ tiết kiệm", "camera": "Cảm biến BioActive + Đo chỉ số AGEs chống lão hóa",
        "description": "Galaxy Watch Ultra thiết kế vỏ đệm Cushion độc đáo, GPS tần số kép L1+L5 chính xác và năng lực AI chăm sóc sức khỏe.",
        "featured": 1, "created_at": "2026-07-22 00:00:00"
    },
    {
        "id": 38, "name": "Samsung Galaxy Watch6 Classic 43mm Bluetooth", "slug": "samsung-galaxy-watch-6-classic",
        "category_id": 5, "category_name": "Smartwatch & Phụ kiện", "brand_id": 2, "brand_name": "Samsung",
        "price": 6490000, "original_price": 7990000, "stock": 18, "rating": 4.81, "review_count": 65, "view_count": 760,
        "image": "https://images.unsplash.com/photo-1510017803434-a899398421b3?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1510017803434-a899398421b3?w=800&auto=format&fit=crop&q=80"],
        "ram": "2GB", "rom": "16GB", "chipset": "Exynos W930 1.4GHz", "screen": "Super AMOLED 1.3 inch Sapphire",
        "battery": "300 mAh, Sạc nhanh không dây", "camera": "Cảm biến huyết áp, ECG, BIA phân tích cơ thể",
        "description": "Vòng xoay bezel vật lý trứ danh, màn hình lớn hơn 20% và huấn luyện viên giấc ngủ cá nhân hóa mỗi ngày.",
        "featured": 0, "created_at": "2026-03-28 00:00:00"
    },
    {
        "id": 39, "name": "Củ sạc nhanh Anker 65W GaNPrime 3 cổng A2668", "slug": "cu-sac-anker-65w-ganprime",
        "category_id": 5, "category_name": "Smartwatch & Phụ kiện", "brand_id": 7, "brand_name": "Anker",
        "price": 890000, "original_price": 1150000, "stock": 60, "rating": 4.90, "review_count": 184, "view_count": 1890,
        "image": "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Công nghệ GaNPrime thế hệ mới nhất", "screen": "2 cổng USB-C (Max 65W) + 1 cổng USB-A (22.5W)",
        "battery": "Bảo vệ nhiệt ActiveShield 2.0 kiểm soát nhiệt", "camera": "Chuẩn Power Delivery 3.0 và PPS Samsung",
        "description": "Củ sạc siêu nhỏ gọn nhẹ hơn 38% củ sạc hãng, phân bổ công suất thông minh PowerIQ 4.0 sạc cùng lúc MacBook, iPhone và iPad.",
        "featured": 1, "created_at": "2026-04-12 00:00:00"
    },
    {
        "id": 40, "name": "Củ sạc nhanh Anker 100W GaNPrime A2343", "slug": "cu-sac-anker-100w-ganprime",
        "category_id": 5, "category_name": "Smartwatch & Phụ kiện", "brand_id": 7, "brand_name": "Anker",
        "price": 1290000, "original_price": 1590000, "stock": 40, "rating": 4.92, "review_count": 98, "view_count": 1200,
        "image": "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Công nghệ GaN thế hệ mới", "screen": "2 cổng USB-C + 1 cổng USB-A (Công suất đơn max 100W)",
        "battery": "ActiveShield 2.0 bảo vệ an toàn cháy nổ", "camera": "Tương thích laptop Dell XPS, MacBook Pro 16",
        "description": "Củ sạc 100W mạnh mẽ sạc đầy 50% pin MacBook Pro 16 inch chỉ trong 30 phút, đồng thời sạc nhanh điện thoại và phụ kiện.",
        "featured": 0, "created_at": "2026-05-15 00:00:00"
    },
    {
        "id": 41, "name": "Chuột không dây Logitech MX Master 3S Quiet Clicks", "slug": "logitech-mx-master-3s",
        "category_id": 5, "category_name": "Smartwatch & Phụ kiện", "brand_id": 8, "brand_name": "Logitech",
        "price": 2190000, "original_price": 2490000, "stock": 35, "rating": 4.96, "review_count": 175, "view_count": 1750,
        "image": "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Cảm biến Darkfield 8000 DPI trên mọi bề mặt kính", "screen": "Cuộn điện từ MagSpeed cuộn 1000 dòng/giây",
        "battery": "Pin sạc 500mAh dùng 70 ngày, Sạc nhanh USB-C", "camera": "Nút click êm ái giảm 90% tiếng ồn + Kết nối 3 thiết bị",
        "description": "Chuột công thái học cao cấp tốt nhất cho lập trình viên và nhà sáng tạo, con lăn điện từ MagSpeed và cảm biến 8000 DPI lướt mượt trên kính.",
        "featured": 1, "created_at": "2026-04-08 00:00:00"
    },
    {
        "id": 42, "name": "Bàn phím cơ không dây Logitech MX Mechanical Tactile", "slug": "logitech-mx-mechanical",
        "category_id": 5, "category_name": "Smartwatch & Phụ kiện", "brand_id": 8, "brand_name": "Logitech",
        "price": 3390000, "original_price": 3790000, "stock": 25, "rating": 4.91, "review_count": 88, "view_count": 1100,
        "image": "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Switch cơ học Low Profile độ nảy cao", "screen": "Đèn nền thông minh tự động bật sáng khi tay lại gần",
        "battery": "Pin sạc USB-C dùng 15 ngày có đèn (10 tháng tắt đèn)", "camera": "Kết nối 3 thiết bị chuyển đổi tức thì",
        "description": "Bàn phím cơ low-profile gõ cực kỳ êm ái, plate nhôm mờ cao cấp và đèn nền tự cảm biến khoảng cách tay thông minh.",
        "featured": 1, "created_at": "2026-04-10 00:00:00"
    }
]

# 5. Coupons
coupons = [
    {"id": 1, "code": "TECH10", "discount_type": "percent", "value": 10.00, "min_order": 1000000, "max_discount": 500000, "usage_limit": 1000, "used_count": 28, "description": "Giảm 10% tối đa 500k cho đơn từ 1 triệu", "status": "active"},
    {"id": 2, "code": "VIETQR50", "discount_type": "amount", "value": 50000.00, "min_order": 500000, "max_discount": 50000, "usage_limit": 500, "used_count": 42, "description": "Giảm 50.000đ khi thanh toán VietQR quét mã tức thì", "status": "active"},
    {"id": 3, "code": "GIAM100K", "discount_type": "amount", "value": 100000.00, "min_order": 2000000, "max_discount": 100000, "usage_limit": 300, "used_count": 19, "description": "Giảm ngay 100k cho đơn hàng công nghệ từ 2 triệu", "status": "active"},
    {"id": 4, "code": "APPLENEW", "discount_type": "percent", "value": 5.00, "min_order": 15000000, "max_discount": 1000000, "usage_limit": 200, "used_count": 15, "description": "Ưu đãi 5% tối đa 1 triệu đồng cho thiết bị Apple chính hãng", "status": "active"},
    {"id": 5, "code": "FREESHIP", "discount_type": "amount", "value": 30000.00, "min_order": 300000, "max_discount": 30000, "usage_limit": 2000, "used_count": 87, "description": "Miễn phí vận chuyển toàn quốc cho đơn từ 300.000đ", "status": "active"}
]

# 6. Orders (30 đơn mẫu với giỏ hàng liên kết phục vụ Apriori & Collaborative Filtering)
# Baskets:
# - Apple Eco: iPhone 16 Pro Max (1) + AirPods Pro 2 (30) + Anker 65W (39)
# - MacBook Eco: MacBook Pro 14 (11) + MX Master 3S (41) + Anker 100W (40)
# - Samsung Eco: S24 Ultra (4) + Buds3 Pro (32) + Anker 65W (39)
# - Gaming Eco: ASUS Zephyrus (14) + Sony WH-1000XM5 (27) + MX Master 3S (41)
# - iPad Eco: iPad Pro 11 (21) + AirPods Pro 2 (30)
# - Studio Eco: MX Mechanical (42) + MX Master 3S (41)
orders_plan = [
    # (order_code, user_id, cust_name, phone, email, address, method, pay_stat, status, coupon, items[(prod_id, qty, variant)], created_at)
    ("TS-749201", 2, "Tuấn Kiệt", "0987654321", "user@techstore.local", "123 Cách Mạng Tháng 8, Q10, TP.HCM", "vietqr", "paid", "completed", "TECH10", [(1, 1, "256GB - Titan Sa Mạc"), (30, 1, "Trắng USB-C"), (39, 1, "Đen GaN 65W")], "2026-08-05 09:30:00"),
    ("TS-839102", 4, "Nguyễn Hương Lan", "0934567890", "lan.nguyen@gmail.com", "88 Điện Biên Phủ, Q1, TP.HCM", "vietqr", "paid", "completed", "VIETQR50", [(11, 1, "Space Black 18GB/512GB"), (41, 1, "Đen Graphite"), (40, 1, "Đen 100W")], "2026-08-10 14:15:00"),
    ("TS-910234", 5, "Trần Minh Hoàng", "0978112233", "hoang.tran@gmail.com", "15 Lê Duẩn, Q1, TP.HCM", "cod", "paid", "completed", None, [(4, 1, "Xám Titan 12GB/256GB"), (32, 1, "Bạc"), (39, 1, "Trắng GaN 65W")], "2026-08-14 11:00:00"),
    ("TS-920345", 6, "Lê Nhật Quang", "0918776655", "quang.le@gmail.com", "42 Nguyễn Huệ, Q1, TP.HCM", "vietqr", "paid", "completed", "TECH10", [(14, 1, "Eclipse Gray OLED RTX 4070"), (27, 1, "Đen"), (41, 1, "Đen Graphite")], "2026-08-18 16:20:00"),
    ("TS-930456", 7, "Phạm Phương Mai", "0909887766", "mai.pham@gmail.com", "254 Hoàng Văn Thụ, Tân Bình, TP.HCM", "vietqr", "paid", "completed", "APPLENEW", [(21, 1, "Space Black 256GB"), (30, 1, "Trắng USB-C")], "2026-08-22 10:45:00"),
    ("TS-940567", 2, "Tuấn Kiệt", "0987654321", "user@techstore.local", "123 Cách Mạng Tháng 8, Q10, TP.HCM", "bank_transfer", "paid", "completed", "GIAM100K", [(41, 1, "Xám Pale Grey"), (42, 1, "Tactile Quiet Fullsize")], "2026-08-28 15:10:00"),
    ("TS-950678", 5, "Trần Minh Hoàng", "0978112233", "hoang.tran@gmail.com", "15 Lê Duẩn, Q1, TP.HCM", "vietqr", "paid", "completed", "VIETQR50", [(1, 1, "256GB - Titan Tự Nhiên"), (39, 1, "Đen GaN 65W")], "2026-09-02 09:00:00"),
    ("TS-960789", 4, "Nguyễn Hương Lan", "0934567890", "lan.nguyen@gmail.com", "88 Điện Biên Phủ, Q1, TP.HCM", "vietqr", "paid", "completed", "TECH10", [(12, 1, "Midnight 8GB/256GB"), (41, 1, "Đen Graphite")], "2026-09-05 13:40:00"),
    ("TS-970890", None, "Võ Thành Đạt", "0944556677", "dat.vo@gmail.com", "364 Cộng Hòa, Tân Bình, TP.HCM", "cod", "paid", "completed", None, [(27, 1, "Bạc"), (29, 1, "Xanh Dương IP67")], "2026-09-09 11:20:00"),
    ("TS-980901", 6, "Lê Nhật Quang", "0918776655", "quang.le@gmail.com", "42 Nguyễn Huệ, Q1, TP.HCM", "vietqr", "paid", "completed", "APPLENEW", [(35, 1, "49mm Dây Alpine Cam"), (30, 1, "Trắng USB-C")], "2026-09-12 17:30:00"),
    ("TS-991012", 2, "Tuấn Kiệt", "0987654321", "user@techstore.local", "123 Cách Mạng Tháng 8, Q10, TP.HCM", "vietqr", "paid", "completed", "VIETQR50", [(2, 1, "128GB - Ultramarine"), (30, 1, "Trắng USB-C"), (39, 1, "Trắng GaN 65W")], "2026-09-15 08:30:00"),
    ("TS-102123", 7, "Phạm Phương Mai", "0909887766", "mai.pham@gmail.com", "254 Hoàng Văn Thụ, Tân Bình, TP.HCM", "cod", "paid", "completed", None, [(22, 1, "Starlight 128GB"), (30, 1, "Trắng USB-C")], "2026-09-18 14:00:00"),
    ("TS-113234", 4, "Nguyễn Hương Lan", "0934567890", "lan.nguyen@gmail.com", "88 Điện Biên Phủ, Q1, TP.HCM", "vietqr", "paid", "completed", "TECH10", [(17, 1, "Platinum Core Ultra 7"), (41, 1, "Đen Graphite"), (40, 1, "Đen 100W")], "2026-09-21 16:50:00"),
    ("TS-124345", 5, "Trần Minh Hoàng", "0978112233", "hoang.tran@gmail.com", "15 Lê Duẩn, Q1, TP.HCM", "vietqr", "paid", "completed", "GIAM100K", [(4, 1, "Đen Titan"), (37, 1, "47mm Titan Xám")], "2026-09-24 10:15:00"),
    ("TS-135456", None, "Đặng Bảo Châu", "0966778899", "chau.dang@gmail.com", "180 Cao Lỗ, Quận 8, TP.HCM", "cod", "paid", "completed", None, [(7, 1, "Đen Da Leica 512GB"), (26, 1, "Xám 256GB"), (33, 1, "Trắng")], "2026-09-27 12:45:00"),
    ("TS-146567", 6, "Lê Nhật Quang", "0918776655", "quang.le@gmail.com", "42 Nguyễn Huệ, Q1, TP.HCM", "vietqr", "paid", "completed", "TECH10", [(15, 1, "Xanh Ponder Blue 1TB"), (41, 1, "Đen Graphite")], "2026-09-30 15:20:00"),
    ("TS-157678", 2, "Tuấn Kiệt", "0987654321", "user@techstore.local", "123 Cách Mạng Tháng 8, Q10, TP.HCM", "vietqr", "paid", "completed", "VIETQR50", [(1, 1, "256GB - Titan Đen"), (36, 1, "Nhôm Midnight 45mm")], "2026-10-02 09:10:00"),
    ("TS-168789", 7, "Phạm Phương Mai", "0909887766", "mai.pham@gmail.com", "254 Hoàng Văn Thụ, Tân Bình, TP.HCM", "vietqr", "paid", "completed", "GIAM100K", [(11, 1, "Space Black 18GB/512GB"), (42, 1, "Tactile Mini"), (41, 1, "Xám Pale Grey")], "2026-10-05 11:35:00"),
    ("TS-179890", 4, "Nguyễn Hương Lan", "0934567890", "lan.nguyen@gmail.com", "88 Điện Biên Phủ, Q1, TP.HCM", "bank_transfer", "paid", "completed", None, [(30, 1, "Trắng USB-C"), (39, 1, "Trắng GaN 65W")], "2026-10-07 14:25:00"),
    ("TS-190901", None, "Hoàng Văn Tuấn", "0912998877", "tuan.hoang@gmail.com", "77 Nguyễn Đình Chiểu, Q3, TP.HCM", "cod", "paid", "completed", "FREESHIP", [(16, 1, "Mecha Gray RTX 4060"), (27, 1, "Đen")], "2026-10-09 16:00:00"),
    ("TS-201012", 5, "Trần Minh Hoàng", "0978112233", "hoang.tran@gmail.com", "15 Lê Duẩn, Q1, TP.HCM", "vietqr", "paid", "completed", "VIETQR50", [(24, 1, "Xám Graphite 256GB"), (32, 1, "Bạc")], "2026-10-11 10:50:00"),
    ("TS-212123", 2, "Tuấn Kiệt", "0987654321", "user@techstore.local", "123 Cách Mạng Tháng 8, Q10, TP.HCM", "vietqr", "paid", "completed", "TECH10", [(1, 1, "256GB - Titan Trắng"), (31, 1, "Space Gray USB-C")], "2026-10-13 13:15:00"),
    ("TS-223234", 6, "Lê Nhật Quang", "0918776655", "quang.le@gmail.com", "42 Nguyễn Huệ, Q1, TP.HCM", "vietqr", "paid", "shipping", "GIAM100K", [(9, 1, "Phantom Black 512GB"), (28, 1, "Đen")], "2026-10-15 08:45:00"),
    ("TS-234345", 7, "Phạm Phương Mai", "0909887766", "mai.pham@gmail.com", "254 Hoàng Văn Thụ, Tân Bình, TP.HCM", "cod", "pending", "shipping", None, [(19, 1, "Bạc Platinum i5"), (39, 1, "Đen GaN 65W")], "2026-10-16 11:20:00"),
    ("TS-245456", 4, "Nguyễn Hương Lan", "0934567890", "lan.nguyen@gmail.com", "88 Điện Biên Phủ, Q1, TP.HCM", "vietqr", "paid", "shipping", "VIETQR50", [(2, 1, "128GB - Hồng"), (30, 1, "Trắng USB-C")], "2026-10-17 15:30:00"),
    ("TS-256567", None, "Lý Gia Hào", "0933112244", "hao.ly@gmail.com", "55 Phan Đăng Lưu, Phú Nhuận, TP.HCM", "cod", "pending", "shipping", None, [(20, 1, "Bạc Unibody R7"), (41, 1, "Đen Graphite")], "2026-10-18 10:00:00"),
    ("TS-267678", 5, "Trần Minh Hoàng", "0978112233", "hoang.tran@gmail.com", "15 Lê Duẩn, Q1, TP.HCM", "vietqr", "paid", "confirmed", "TECH10", [(6, 1, "Xám Phantom 256GB"), (37, 1, "47mm Titan Trắng")], "2026-10-19 14:10:00"),
    ("TS-278789", 2, "Tuấn Kiệt", "0987654321", "user@techstore.local", "123 Cách Mạng Tháng 8, Q10, TP.HCM", "vietqr", "paid", "confirmed", "APPLENEW", [(13, 1, "Silver M3 Max 1TB"), (40, 1, "Đen 100W")], "2026-10-20 09:30:00"),
    ("TS-289890", 7, "Phạm Phương Mai", "0909887766", "mai.pham@gmail.com", "254 Hoàng Văn Thụ, Tân Bình, TP.HCM", "vietqr", "pending", "pending", "VIETQR50", [(3, 1, "128GB Titan Tự Nhiên"), (30, 1, "Trắng USB-C")], "2026-10-21 08:20:00"),
    ("TS-290901", None, "Trương Minh Tuấn", "0988001122", "tuan.truong@gmail.com", "102 Lê Văn Sỹ, Q3, TP.HCM", "cod", "pending", "cancelled", None, [(18, 1, "Core Ultra 9 RTX 4070")], "2026-10-21 11:45:00")
]

# Calculate full orders data
orders = []
order_items = []
payments = []
prod_map = {p["id"]: p for p in products}

order_id_counter = 1001
item_id_counter = 1
payment_id_counter = 1

for item in orders_plan:
    code, uid, name, phone, email, addr, method, pay_stat, status, coup, raw_items, dt = item
    
    subtotal = 0
    curr_items = []
    for pid, qty, var in raw_items:
        p = prod_map[pid]
        item_price = p["price"]
        subtotal += item_price * qty
        curr_items.append({
            "id": item_id_counter,
            "order_id": order_id_counter,
            "product_id": pid,
            "product_name": p["name"],
            "price": item_price,
            "quantity": qty,
            "variant": var,
            "image": p["image"],
            "created_at": dt
        })
        item_id_counter += 1
        
    discount = 0
    if coup == "TECH10":
        discount = min(subtotal * 0.1, 500000)
    elif coup == "VIETQR50":
        discount = 50000
    elif coup == "GIAM100K":
        discount = 100000
    elif coup == "APPLENEW":
        discount = min(subtotal * 0.05, 1000000)
    elif coup == "FREESHIP":
        discount = 30000
        
    total = max(0, subtotal - discount)
    
    order_record = {
        "id": order_id_counter,
        "order_code": code,
        "user_id": uid,
        "customer_name": name,
        "customer_phone": phone,
        "customer_email": email,
        "shipping_address": addr,
        "payment_method": method,
        "payment_status": pay_stat,
        "status": status,
        "subtotal": subtotal,
        "discount_amount": discount,
        "shipping_fee": 0,
        "total_amount": total,
        "coupon_code": coup,
        "note": f"Giao giờ hành chính, gọi trước khi giao cho khách ({name})" if status != "cancelled" else "Khách yêu cầu hủy đơn do đổi ý",
        "created_at": dt,
        "updated_at": dt
    }
    orders.append(order_record)
    order_items.extend(curr_items)
    
    pay_record = {
        "id": payment_id_counter,
        "order_id": order_id_counter,
        "payment_code": f"PAY-{code}",
        "payment_method": method,
        "amount": total,
        "status": pay_stat,
        "transaction_id": f"TXN-{order_id_counter}894" if pay_stat == "paid" else None,
        "qr_code_url": f"https://api.vietqr.io/image/970422-0123456789-compact2.png?amount={int(total)}&addInfo={code}&accountName=TECHSTORE" if method == "vietqr" else None,
        "payment_note": f"Thanh toan don hang {code}",
        "paid_at": dt if pay_stat == "paid" else None,
        "created_at": dt,
        "updated_at": dt
    }
    payments.append(pay_record)
    payment_id_counter += 1
    order_id_counter += 1

# 7. Reviews (25 đánh giá đa dạng và chân thực)
sample_reviews = [
    (1, 1, 2, "Tuấn Kiệt", 5, "iPhone 16 Pro Max máy cầm đầm tay, viền titan sa mạc cực kỳ sang trọng. Camera Control zoom mượt mà!"),
    (2, 1, 4, "Nguyễn Hương Lan", 5, "Đã mua bản 256GB tại TechStore, giao siêu nhanh trong 2h, đóng gói niêm phong chính hãng 100%."),
    (3, 4, 5, "Trần Minh Hoàng", 5, "Galaxy S24 Ultra màn hình phẳng nhìn rất thích, Galaxy AI khoanh tròn tìm kiếm cực kỳ tiện cho công việc."),
    (4, 11, 4, "Nguyễn Hương Lan", 5, "MacBook Pro 14 M3 Pro màu Space Black xuất sắc, bàn phím gõ êm, pin dùng 2 ngày không cần sạc."),
    (5, 14, 6, "Lê Nhật Quang", 5, "Zephyrus G16 OLED 240Hz hiển thị đỉnh chóp! Chơi Cyberpunk 2077 max setting mượt mà với RTX 4070."),
    (6, 21, 7, "Phạm Phương Mai", 5, "iPad Pro M4 mỏng nhẹ đến kinh ngạc, màn OLED đen sâu tuyệt đối, vẽ vời cực sướng."),
    (7, 27, 2, "Tuấn Kiệt", 5, "Sony WH-1000XM5 đeo êm ái cách ly hoàn toàn tiếng ồn văn phòng. Âm bass trầm ấm chuẩn gu."),
    (8, 30, 4, "Nguyễn Hương Lan", 5, "AirPods Pro 2 cổng Type-C tiện dụng, chống ồn tốt hơn bản cũ rất nhiều, âm thanh vòm sống động."),
    (9, 41, 5, "Trần Minh Hoàng", 5, "Logitech MX Master 3S con lăn MagSpeed lướt vèo vèo qua nghìn dòng code Excel, click chuột êm ru không tiếng động."),
    (10, 42, 6, "Lê Nhật Quang", 5, "Bàn phím cơ low profile gõ tactile rất đầm tay, kết nối Bluetooth với cả Mac và PC chuyển qua lại 1 chạm."),
    (11, 39, 2, "Tuấn Kiệt", 5, "Củ sạc Anker GaN 65W nhỏ xíu mà sạc cùng lúc được cả laptop và iPhone, không hề bị nóng máy."),
    (12, 35, 6, "Lê Nhật Quang", 5, "Apple Watch Ultra 2 màn hình 3000 nits ra nắng gắt xem rõ nét, pin dùng thoải mái 3 ngày leo núi."),
    (13, 2, 7, "Phạm Phương Mai", 5, "iPhone 16 màu xanh Ultramarine ở ngoài đẹp hơn trên ảnh, máy gọn nhẹ chụp chân dung rất nét."),
    (14, 7, 5, "Trần Minh Hoàng", 5, "Xiaomi 14 Ultra camera Leica tone màu vintage mê mẩn, xóa phông quang học chân thật không bị giả."),
    (15, 15, 4, "Nguyễn Hương Lan", 4, "Zenbook 14 OLED mỏng nhẹ mang đi công tác rất tiện, màn hình đẹp nhưng hơi bám vân tay chút."),
    (16, 22, 7, "Phạm Phương Mai", 5, "iPad Air 6 chip M2 chạy các app chỉnh video LumaFusion siêu nhanh, giá hợp lý hơn Pro nhiều."),
    (17, 37, 5, "Trần Minh Hoàng", 5, "Galaxy Watch Ultra GPS bắt sóng rất nhạy, đo chỉ số sức khỏe chi tiết, pin trâu hơn hẳn các đời trước."),
    (18, 9, 6, "Lê Nhật Quang", 5, "ROG Phone 8 Pro chiến Genshin Impact 60fps ổn định không drop, nút trigger bấm nhạy như tay cầm."),
    (19, 40, 2, "Tuấn Kiệt", 5, "Sạc 100W Anker dùng cho MacBook 16 inch ngon lành, chân cắm chắc chắn không bị lỏng lẻo."),
    (20, 28, 5, "Trần Minh Hoàng", 4, "WF-1000XM5 chống ồn tốt, âm thanh chi tiết nhưng nút đệm tai bọt biển cần đeo quen mới thấy êm."),
    (21, 17, 4, "Nguyễn Hương Lan", 5, "Dell XPS 14 thiết kế kính tương lai quá đẹp, bàn phím và trackpad tàng hình độc lạ ấn tượng."),
    (22, 33, 7, "Phạm Phương Mai", 5, "Redmi Buds 5 Pro giá hơn 1 triệu mà chống ồn và pin quá tốt, sinh viên dùng rất kinh tế."),
    (23, 10, 5, "Trần Minh Hoàng", 4, "Galaxy A55 viền kim loại chắc chắn, màn hình 120Hz mượt, pin trâu dùng cả ngày."),
    (24, 24, 6, "Lê Nhật Quang", 5, "Tab S9 Ultra màn 14.6 inch to như laptop, bút S-Pen viết ghi chú không có độ trễ."),
    (25, 4, 2, "Tuấn Kiệt", 5, "Mua hàng bên TechStore nhân viên tư vấn nhiệt tình, quét VietQR thanh toán 3s là xong!")
]
reviews = []
for r_id, p_id, u_id, u_name, rat, comm in sample_reviews:
    reviews.append({
        "id": r_id,
        "product_id": p_id,
        "user_id": u_id,
        "user_name": u_name,
        "rating": rat,
        "comment": comm,
        "status": "approved",
        "created_at": "2026-09-15 10:00:00"
    })

# 8. Wishlists
wishlists = [
    {"id": 1, "user_id": 2, "product_id": 1, "created_at": "2026-09-20 08:00:00"},
    {"id": 2, "user_id": 2, "product_id": 27, "created_at": "2026-09-21 08:30:00"},
    {"id": 3, "user_id": 2, "product_id": 41, "created_at": "2026-09-22 09:00:00"},
    {"id": 4, "user_id": 4, "product_id": 11, "created_at": "2026-09-10 10:15:00"},
    {"id": 5, "user_id": 4, "product_id": 30, "created_at": "2026-09-12 11:00:00"},
    {"id": 6, "user_id": 5, "product_id": 4, "created_at": "2026-09-01 14:20:00"},
    {"id": 7, "user_id": 5, "product_id": 37, "created_at": "2026-09-05 15:30:00"},
    {"id": 8, "user_id": 6, "product_id": 14, "created_at": "2026-08-25 16:00:00"},
    {"id": 9, "user_id": 6, "product_id": 35, "created_at": "2026-08-28 17:10:00"},
    {"id": 10, "user_id": 7, "product_id": 21, "created_at": "2026-09-15 12:00:00"},
    {"id": 11, "user_id": 7, "product_id": 42, "created_at": "2026-09-18 13:40:00"}
]

# 9. Product Similarity (Cosine & Apriori Baskets)
similarity_pairs = [
    # Cặp điện thoại tương đương (Cosine Content-based)
    (1, 4, 0.9450, "cosine"), # iPhone 16 Pro Max <-> Galaxy S24 Ultra
    (1, 7, 0.8920, "cosine"), # iPhone 16 Pro Max <-> Xiaomi 14 Ultra
    (1, 2, 0.9100, "cosine"), # iPhone 16 Pro Max <-> iPhone 16
    (4, 5, 0.9200, "cosine"), # Galaxy S24 Ultra <-> Galaxy S24+
    (4, 6, 0.8850, "cosine"), # Galaxy S24 Ultra <-> Galaxy Z Fold6
    (7, 8, 0.9150, "cosine"), # Xiaomi 14 Ultra <-> Xiaomi 14T Pro
    # Cặp laptop tương đương (Cosine)
    (11, 17, 0.9280, "cosine"), # MacBook Pro 14 <-> Dell XPS 14
    (11, 14, 0.8750, "cosine"), # MacBook Pro 14 <-> ROG Zephyrus G16
    (11, 12, 0.9350, "cosine"), # MacBook Pro 14 <-> MacBook Air 13
    (14, 16, 0.8950, "cosine"), # ROG Zephyrus G16 <-> TUF Gaming A15
    (17, 18, 0.9400, "cosine"), # Dell XPS 14 <-> Dell XPS 16
    (15, 12, 0.9050, "cosine"), # Zenbook 14 <-> MacBook Air 13
    # Cặp tai nghe tương đương (Cosine)
    (27, 31, 0.9120, "cosine"), # Sony WH-1000XM5 <-> AirPods Max
    (28, 30, 0.9380, "cosine"), # Sony WF-1000XM5 <-> AirPods Pro 2
    (30, 32, 0.9050, "cosine"), # AirPods Pro 2 <-> Galaxy Buds3 Pro
    # Cặp thường được mua cùng (Apriori Co-purchase Rule / Confidence)
    (1, 30, 0.8750, "apriori"), # iPhone 16 Pro Max + AirPods Pro 2
    (1, 39, 0.9200, "apriori"), # iPhone 16 Pro Max + Củ sạc Anker 65W
    (4, 32, 0.8600, "apriori"), # Galaxy S24 Ultra + Galaxy Buds3 Pro
    (4, 39, 0.8950, "apriori"), # Galaxy S24 Ultra + Củ sạc Anker 65W
    (11, 41, 0.9400, "apriori"), # MacBook Pro 14 + Logitech MX Master 3S
    (11, 40, 0.8800, "apriori"), # MacBook Pro 14 + Củ sạc Anker 100W
    (14, 27, 0.8500, "apriori"), # ROG Zephyrus G16 + Sony WH-1000XM5
    (14, 41, 0.8900, "apriori"), # ROG Zephyrus G16 + Logitech MX Master 3S
    (21, 30, 0.8300, "apriori"), # iPad Pro 11 + AirPods Pro 2
    (41, 42, 0.9600, "apriori"), # Logitech MX Master 3S + Logitech MX Mechanical
]

product_similarity = []
sim_id = 1
for p1, p2, sc, algo in similarity_pairs:
    product_similarity.append({
        "id": sim_id,
        "product_id": p1,
        "similar_product_id": p2,
        "score": sc,
        "algorithm": algo,
        "created_at": "2026-10-21 00:00:00"
    })
    sim_id += 1

# 10. Product Views (Lịch sử xem sản phẩm)
product_views = []
view_id = 1
for uid in [2, 4, 5, 6, 7]:
    for pid in [1, 4, 11, 14, 21, 27, 30, 35, 39, 41]:
        product_views.append({
            "id": view_id,
            "product_id": pid,
            "user_id": uid,
            "session_id": f"sess_{uid}_{view_id}",
            "ip_address": f"192.168.1.{10 + uid}",
            "viewed_at": "2026-10-20 10:00:00"
        })
        view_id += 1

# 11. Refresh Tokens
refresh_tokens = [
    {"id": 1, "user_id": 1, "token_hash": "a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3", "expires_at": "2026-11-20 00:00:00", "revoked": 0, "created_at": "2026-10-21 08:00:00"},
    {"id": 2, "user_id": 2, "token_hash": "b556b34819311e8c306d3756deeb3ea79f390e2eee0e9f6d887d75e6e6916bd2", "expires_at": "2026-11-20 00:00:00", "revoked": 0, "created_at": "2026-10-21 08:30:00"},
    {"id": 3, "user_id": 4, "token_hash": "c445a23708200d7b295c2645cdda2d968e280d1ddd9d8e5c776c64d5d5805ac1", "expires_at": "2026-11-20 00:00:00", "revoked": 0, "created_at": "2026-10-21 09:00:00"}
]

# 12. Audit Logs
audit_logs = [
    {"id": 1, "user_id": 1, "user_name": "admin@techstore.local", "action": "INITIALIZE_DATABASE", "description": "Khởi tạo hệ thống CSDL và bộ dữ liệu hạt giống 42 sản phẩm 8 thương hiệu", "ip_address": "127.0.0.1", "created_at": "2026-08-01 08:00:00"},
    {"id": 2, "user_id": 1, "user_name": "admin@techstore.local", "action": "CREATE_PRODUCT", "description": "Đăng tải sản phẩm mới iPhone 16 Pro Max 256GB lên hệ thống", "ip_address": "127.0.0.1", "created_at": "2026-09-20 10:00:00"},
    {"id": 3, "user_id": 3, "user_name": "trung@techstore.local", "action": "UPDATE_ORDER_STATUS", "description": "Chuyển đơn TS-749201 sang completed và xác nhận tiền VietQR về tài khoản", "ip_address": "127.0.0.1", "created_at": "2026-08-05 10:00:00"},
    {"id": 4, "user_id": 1, "user_name": "admin@techstore.local", "action": "CREATE_COUPON", "description": "Tạo chiến dịch mã giảm giá TECH10 giảm 10% tối đa 500k", "ip_address": "127.0.0.1", "created_at": "2026-08-01 09:00:00"},
    {"id": 5, "user_id": 3, "user_name": "trung@techstore.local", "action": "BUILD_SIMILARITY", "description": "Chạy thuật toán Cosine Similarity và Apriori Baskets cập nhật bảng gợi ý", "ip_address": "127.0.0.1", "created_at": "2026-10-21 00:00:00"}
]

# Write to database/data.json
full_data_json = {
    "users": users,
    "categories": categories,
    "brands": brands,
    "products": products,
    "coupons": coupons,
    "orders": orders,
    "order_items": order_items,
    "payments": payments,
    "reviews": reviews,
    "wishlists": wishlists,
    "cart": [
        {"id": 1, "user_id": 2, "product_id": 1, "quantity": 1, "variant": "256GB - Titan Sa Mạc"},
        {"id": 2, "user_id": 2, "product_id": 39, "quantity": 1, "variant": "Đen GaN 65W"}
    ],
    "product_similarity": product_similarity,
    "product_views": product_views,
    "refresh_tokens": refresh_tokens,
    "audit_logs": audit_logs
}

data_json_path = os.path.join(os.path.dirname(__file__), "data.json")
with open(data_json_path, "w", encoding="utf-8") as f:
    json.dump(full_data_json, f, ensure_ascii=False, indent=2)
print(f"Successfully saved data.json: {len(products)} products, {len(orders)} orders, {len(brands)} brands, {len(categories)} categories")

# Now generate database/seed.sql
def escape_sql(val):
    if val is None:
        return "NULL"
    if isinstance(val, (int, float)):
        return str(val)
    if isinstance(val, (dict, list)):
        val = json.dumps(val, ensure_ascii=False)
    s = str(val).replace("\\", "\\\\").replace("'", "''")
    return f"'{s}'"

seed_sql_lines = []
seed_sql_lines.append("-- ==============================================================================")
seed_sql_lines.append("-- TechStore - Complete Production Seed Data (DML)")
seed_sql_lines.append("-- Topic 1: RESTful API · PHP, MySQL, HTML/CSS/JS")
seed_sql_lines.append("-- Nhiệm vụ T05: Viết seed.sql (≥40 sản phẩm, 8 hãng, 5 danh mục, user mẫu, ~30 đơn mẫu)")
seed_sql_lines.append("-- ==============================================================================")
seed_sql_lines.append("\nUSE `techstore`;\n")
seed_sql_lines.append("SET FOREIGN_KEY_CHECKS = 0;\n")

# Clear tables before inserting seed
tables_to_truncate = [
    "audit_logs", "product_similarity", "product_views", "refresh_tokens",
    "coupons", "wishlists", "reviews", "payments", "order_items", "orders",
    "carts", "product_specs", "product_images", "products", "brands", "categories", "users"
]
for tbl in tables_to_truncate:
    seed_sql_lines.append(f"TRUNCATE TABLE `{tbl}`;")
seed_sql_lines.append("")

# 1. Users
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 1. DỮ LIỆU SEED CHO BẢNG users (Admin & Khách hàng mẫu)")
seed_sql_lines.append("-- Mật khẩu mặc định Admin@123 và User@123 (Bcrypt băm an toàn)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
user_cols = ["id", "name", "email", "password_hash", "role", "phone", "address", "avatar", "status", "created_at"]
seed_sql_lines.append(f"INSERT INTO `users` (`{'`, `'.join(user_cols)}`) VALUES")
user_vals = []
for u in users:
    val_str = "(" + ", ".join(escape_sql(u[c]) for c in user_cols) + ")"
    user_vals.append(val_str)
seed_sql_lines.append(",\n".join(user_vals) + ";\n")

# 2. Categories
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 2. DỮ LIỆU SEED CHO BẢNG categories (5 danh mục ngành hàng)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
cat_cols = ["id", "name", "slug", "icon", "description", "product_count"]
seed_sql_lines.append(f"INSERT INTO `categories` (`{'`, `'.join(cat_cols)}`) VALUES")
cat_vals = []
for c in categories:
    val_str = "(" + ", ".join(escape_sql(c[col]) for col in cat_cols) + ")"
    cat_vals.append(val_str)
seed_sql_lines.append(",\n".join(cat_vals) + ";\n")

# 3. Brands
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 3. DỮ LIỆU SEED CHO BẢNG brands (8 thương hiệu công nghệ lớn)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
brand_cols = ["id", "name", "slug", "logo", "description"]
seed_sql_lines.append(f"INSERT INTO `brands` (`{'`, `'.join(brand_cols)}`) VALUES")
brand_vals = []
for b in brands:
    val_str = "(" + ", ".join(escape_sql(b[col]) for col in brand_cols) + ")"
    brand_vals.append(val_str)
seed_sql_lines.append(",\n".join(brand_vals) + ";\n")

# 4. Products
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 4. DỮ LIỆU SEED CHO BẢNG products (42 sản phẩm chi tiết cấu hình)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
prod_cols = ["id", "name", "slug", "category_id", "category_name", "brand_id", "brand_name",
             "price", "original_price", "stock", "rating", "review_count", "view_count",
             "image", "images", "ram", "rom", "chipset", "screen", "battery", "camera",
             "description", "featured", "created_at"]
seed_sql_lines.append(f"INSERT INTO `products` (`{'`, `'.join(prod_cols)}`) VALUES")
prod_vals = []
for p in products:
    val_str = "(" + ", ".join(escape_sql(p[c]) for c in prod_cols) + ")"
    prod_vals.append(val_str)
seed_sql_lines.append(",\n".join(prod_vals) + ";\n")

# 5. Product Images
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 5. DỮ LIỆU SEED CHO BẢNG product_images (Thư viện ảnh sản phẩm)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
pimg_cols = ["product_id", "image_url", "is_primary", "display_order"]
seed_sql_lines.append(f"INSERT INTO `product_images` (`{'`, `'.join(pimg_cols)}`) VALUES")
pimg_vals = []
for p in products:
    pimg_vals.append(f"({p['id']}, {escape_sql(p['image'])}, 1, 0)")
    for idx, extra_img in enumerate(p.get("images", []), start=1):
        if extra_img != p["image"]:
            pimg_vals.append(f"({p['id']}, {escape_sql(extra_img)}, 0, {idx})")
seed_sql_lines.append(",\n".join(pimg_vals) + ";\n")

# 6. Product Specs
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 6. DỮ LIỆU SEED CHO BẢNG product_specs (Thông số kỹ thuật chi tiết)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
spec_cols = ["product_id", "spec_group", "spec_key", "spec_value", "display_order"]
seed_sql_lines.append(f"INSERT INTO `product_specs` (`{'`, `'.join(spec_cols)}`) VALUES")
spec_vals = []
for p in products:
    d_order = 1
    if p.get("chipset"):
        spec_vals.append(f"({p['id']}, 'Hiệu năng', 'Vi xử lý (CPU/Chip)', {escape_sql(p['chipset'])}, {d_order})")
        d_order += 1
    if p.get("ram"):
        spec_vals.append(f"({p['id']}, 'Bộ nhớ', 'Dung lượng RAM', {escape_sql(p['ram'])}, {d_order})")
        d_order += 1
    if p.get("rom"):
        spec_vals.append(f"({p['id']}, 'Bộ nhớ', 'Bộ nhớ trong (ROM/SSD)', {escape_sql(p['rom'])}, {d_order})")
        d_order += 1
    if p.get("screen"):
        spec_vals.append(f"({p['id']}, 'Màn hình', 'Thông số hiển thị', {escape_sql(p['screen'])}, {d_order})")
        d_order += 1
    if p.get("battery"):
        spec_vals.append(f"({p['id']}, 'Pin & Sạc', 'Pin và công nghệ sạc', {escape_sql(p['battery'])}, {d_order})")
        d_order += 1
    if p.get("camera"):
        spec_vals.append(f"({p['id']}, 'Camera', 'Cụm camera', {escape_sql(p['camera'])}, {d_order})")
        d_order += 1
seed_sql_lines.append(",\n".join(spec_vals) + ";\n")

# 7. Coupons
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 7. DỮ LIỆU SEED CHO BẢNG coupons (Mã giảm giá ưu đãi)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
coup_cols = ["id", "code", "discount_type", "value", "min_order", "max_discount", "usage_limit", "used_count", "description", "status"]
seed_sql_lines.append(f"INSERT INTO `coupons` (`{'`, `'.join(coup_cols)}`) VALUES")
coup_vals = []
for cp in coupons:
    val_str = "(" + ", ".join(escape_sql(cp[c]) for c in coup_cols) + ")"
    coup_vals.append(val_str)
seed_sql_lines.append(",\n".join(coup_vals) + ";\n")

# 8. Orders
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 8. DỮ LIỆU SEED CHO BẢNG orders (30 đơn mẫu phục vụ gợi ý và phân tích)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
ord_cols = ["id", "order_code", "user_id", "customer_name", "customer_phone", "customer_email",
            "shipping_address", "payment_method", "payment_status", "status", "subtotal",
            "discount_amount", "shipping_fee", "total_amount", "coupon_code", "note", "created_at"]
seed_sql_lines.append(f"INSERT INTO `orders` (`{'`, `'.join(ord_cols)}`) VALUES")
ord_vals = []
for od in orders:
    val_str = "(" + ", ".join(escape_sql(od[c]) for c in ord_cols) + ")"
    ord_vals.append(val_str)
seed_sql_lines.append(",\n".join(ord_vals) + ";\n")

# 9. Order Items
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 9. DỮ LIỆU SEED CHO BẢNG order_items (Mặt hàng chi tiết trong đơn)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
item_cols = ["id", "order_id", "product_id", "product_name", "price", "quantity", "variant", "image", "created_at"]
seed_sql_lines.append(f"INSERT INTO `order_items` (`{'`, `'.join(item_cols)}`) VALUES")
item_vals = []
for it in order_items:
    val_str = "(" + ", ".join(escape_sql(it[c]) for c in item_cols) + ")"
    item_vals.append(val_str)
seed_sql_lines.append(",\n".join(item_vals) + ";\n")

# 10. Payments
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 10. DỮ LIỆU SEED CHO BẢNG payments (Giao dịch thanh toán VietQR / COD)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
pay_cols = ["id", "order_id", "payment_code", "payment_method", "amount", "status", "transaction_id", "qr_code_url", "payment_note", "paid_at", "created_at"]
seed_sql_lines.append(f"INSERT INTO `payments` (`{'`, `'.join(pay_cols)}`) VALUES")
pay_vals = []
for py in payments:
    val_str = "(" + ", ".join(escape_sql(py[c]) for c in pay_cols) + ")"
    pay_vals.append(val_str)
seed_sql_lines.append(",\n".join(pay_vals) + ";\n")

# 11. Carts
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 11. DỮ LIỆU SEED CHO BẢNG carts (Giỏ hàng người dùng mẫu)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
cart_cols = ["id", "user_id", "product_id", "quantity", "variant"]
seed_sql_lines.append(f"INSERT INTO `carts` (`{'`, `'.join(cart_cols)}`) VALUES")
seed_sql_lines.append("(1, 2, 1, 1, '256GB - Titan Sa Mạc'),\n(2, 2, 39, 1, 'Đen GaN 65W');\n")

# 12. Reviews
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 12. DỮ LIỆU SEED CHO BẢNG reviews (25 đánh giá chân thực kèm số sao)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
rev_cols = ["id", "product_id", "user_id", "user_name", "rating", "comment", "status", "created_at"]
seed_sql_lines.append(f"INSERT INTO `reviews` (`{'`, `'.join(rev_cols)}`) VALUES")
rev_vals = []
for rv in reviews:
    val_str = "(" + ", ".join(escape_sql(rv[c]) for c in rev_cols) + ")"
    rev_vals.append(val_str)
seed_sql_lines.append(",\n".join(rev_vals) + ";\n")

# 13. Wishlists
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 13. DỮ LIỆU SEED CHO BẢNG wishlists (Sản phẩm yêu thích)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
wl_cols = ["id", "user_id", "product_id", "created_at"]
seed_sql_lines.append(f"INSERT INTO `wishlists` (`{'`, `'.join(wl_cols)}`) VALUES")
wl_vals = []
for wl in wishlists:
    val_str = "(" + ", ".join(escape_sql(wl[c]) for c in wl_cols) + ")"
    wl_vals.append(val_str)
seed_sql_lines.append(",\n".join(wl_vals) + ";\n")

# 14. Product Similarity
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 14. DỮ LIỆU SEED CHO BẢNG product_similarity (Điểm tương đồng Cosine & Apriori)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
sim_cols = ["id", "product_id", "similar_product_id", "score", "algorithm", "created_at"]
seed_sql_lines.append(f"INSERT INTO `product_similarity` (`{'`, `'.join(sim_cols)}`) VALUES")
sim_vals = []
for sm in product_similarity:
    val_str = "(" + ", ".join(escape_sql(sm[c]) for c in sim_cols) + ")"
    sim_vals.append(val_str)
seed_sql_lines.append(",\n".join(sim_vals) + ";\n")

# 15. Product Views
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 15. DỮ LIỆU SEED CHO BẢNG product_views (Lịch sử xem sản phẩm)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
pv_cols = ["id", "product_id", "user_id", "session_id", "ip_address", "viewed_at"]
seed_sql_lines.append(f"INSERT INTO `product_views` (`{'`, `'.join(pv_cols)}`) VALUES")
pv_vals = []
for pv in product_views:
    val_str = "(" + ", ".join(escape_sql(pv[c]) for c in pv_cols) + ")"
    pv_vals.append(val_str)
seed_sql_lines.append(",\n".join(pv_vals) + ";\n")

# 16. Refresh Tokens
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 16. DỮ LIỆU SEED CHO BẢNG refresh_tokens (Phiên đăng nhập người dùng)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
rf_cols = ["id", "user_id", "token_hash", "expires_at", "revoked", "created_at"]
seed_sql_lines.append(f"INSERT INTO `refresh_tokens` (`{'`, `'.join(rf_cols)}`) VALUES")
rf_vals = []
for rf in refresh_tokens:
    val_str = "(" + ", ".join(escape_sql(rf[c]) for c in rf_cols) + ")"
    rf_vals.append(val_str)
seed_sql_lines.append(",\n".join(rf_vals) + ";\n")

# 17. Audit Logs
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
seed_sql_lines.append("-- 17. DỮ LIỆU SEED CHO BẢNG audit_logs (Nhật ký kiểm toán hoạt động)")
seed_sql_lines.append("-- -----------------------------------------------------------------------------")
aud_cols = ["id", "user_id", "user_name", "action", "description", "ip_address", "created_at"]
seed_sql_lines.append(f"INSERT INTO `audit_logs` (`{'`, `'.join(aud_cols)}`) VALUES")
aud_vals = []
for au in audit_logs:
    val_str = "(" + ", ".join(escape_sql(au[c]) for c in aud_cols) + ")"
    aud_vals.append(val_str)
seed_sql_lines.append(",\n".join(aud_vals) + ";\n")

seed_sql_lines.append("SET FOREIGN_KEY_CHECKS = 1;\n")

seed_sql_path = os.path.join(os.path.dirname(__file__), "seed.sql")
with open(seed_sql_path, "w", encoding="utf-8") as f:
    f.write("\n".join(seed_sql_lines))
print(f"Successfully generated database/seed.sql ({len(seed_sql_lines)} lines)")
