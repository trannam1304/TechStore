# -*- coding: utf-8 -*-
"""
Script to expand database/seed.sql by updating database/build_seed.py
Adds 40 new products (IDs 43 to 82), updates Category 5 and 6, and adds 4 new brands.
"""

new_categories_code = '''# 2. Categories (6 danh mục phân tách rõ Smartwatch & Phụ kiện chính hãng)
categories = [
    {"id": 1, "name": "Điện thoại", "icon": "📱", "slug": "dien-thoai", "description": "Smartphone cao cấp từ Apple, Samsung, Xiaomi, ROG Phone", "product_count": 17},
    {"id": 2, "name": "Laptop", "icon": "💻", "slug": "laptop", "description": "Laptop doanh nhân, văn phòng mỏng nhẹ và Laptop Gaming hiệu năng cao", "product_count": 18},
    {"id": 3, "name": "Tablet", "icon": "📋", "slug": "tablet", "description": "Máy tính bảng phục vụ học tập, sáng tạo đồ họa và giải trí", "product_count": 12},
    {"id": 4, "name": "Âm thanh", "icon": "🎧", "slug": "am-thanh", "description": "Tai nghe chống ồn chủ động, tai nghe True Wireless và loa bluetooth", "product_count": 16},
    {"id": 5, "name": "Smartwatch", "icon": "⌚", "slug": "smartwatch", "description": "Đồng hồ thông minh theo dõi sức khỏe và thiết bị đeo thể thao chuyên nghiệp", "product_count": 9},
    {"id": 6, "name": "Phụ kiện chính hãng", "icon": "🔌", "slug": "phu-kien", "description": "Củ cáp sạc nhanh GaN, pin sạc dự phòng, chuột và bàn phím công nghệ cao cấp", "product_count": 10}
]'''

new_brands_code = '''# 3. Brands (12 hãng công nghệ lớn)
brands = [
    {"id": 1, "name": "Apple", "slug": "apple", "logo": "", "description": "Thương hiệu công nghệ hàng đầu thế giới với hệ sinh thái iPhone, Mac, iPad cao cấp."},
    {"id": 2, "name": "Samsung", "slug": "samsung", "logo": "Samsung", "description": "Gã khổng lồ công nghệ Hàn Quốc tiên phong màn hình AMOLED và trí tuệ nhân tạo Galaxy AI."},
    {"id": 3, "name": "Xiaomi", "slug": "xiaomi", "logo": "Xiaomi", "description": "Tập đoàn công nghệ với định vị cấu hình vượt trội trên giá thành và camera hợp tác cùng Leica."},
    {"id": 4, "name": "ASUS", "slug": "asus", "logo": "ASUS", "description": "Nhà sản xuất máy tính và thiết bị gaming hàng đầu với dòng Zenbook tinh tế và ROG huyền thoại."},
    {"id": 5, "name": "Sony", "slug": "sony", "logo": "Sony", "description": "Biểu tượng âm thanh và hình ảnh Nhật Bản với chuẩn âm thanh đỉnh cao Hi-Res và chống ồn 1000X."},
    {"id": 6, "name": "Dell", "slug": "dell", "logo": "Dell", "description": "Thương hiệu máy tính xách tay và máy trạm bền bỉ trứ danh cùng dòng cao cấp Dell XPS đột phá."},
    {"id": 7, "name": "Anker", "slug": "anker", "logo": "Anker", "description": "Hãng phụ kiện sạc nhanh và âm thanh cá nhân số 1 toàn cầu ứng dụng vật liệu bán dẫn GaN."},
    {"id": 8, "name": "Logitech", "slug": "logitech", "logo": "Logitech", "description": "Thương hiệu Thụy Sĩ đứng đầu thế giới về thiết bị ngoại vi văn phòng công thái học và gaming cao cấp."},
    {"id": 9, "name": "Lenovo", "slug": "lenovo", "logo": "Lenovo", "description": "Tập đoàn công nghệ hàng đầu thế giới với dòng laptop ThinkPad doanh nhân và Legion gaming đỉnh cao."},
    {"id": 10, "name": "Acer", "slug": "acer", "logo": "Acer", "description": "Hãng máy tính và phần cứng công nghệ tiên phong cùng dòng Predator và Swift mỏng nhẹ đột phá."},
    {"id": 11, "name": "Marshall", "slug": "marshall", "logo": "Marshall", "description": "Biểu tượng âm thanh rock huyền thoại từ Anh Quốc với chất âm đặc trưng và thiết kế retro cổ điển sang trọng."},
    {"id": 12, "name": "JBL", "slug": "jbl", "logo": "JBL", "description": "Thương hiệu âm thanh Mỹ trứ danh toàn cầu với công nghệ JBL Pro Sound và âm bass sống động."}
]'''

new_products_code = '''    # 35-38: Smartwatch (Category 5)
    {
        "id": 35, "name": "Apple Watch Ultra 2 GPS + Cellular 49mm Titanium", "slug": "apple-watch-ultra-2",
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 1, "brand_name": "Apple",
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
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 1, "brand_name": "Apple",
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
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 2, "brand_name": "Samsung",
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
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 2, "brand_name": "Samsung",
        "price": 6490000, "original_price": 7990000, "stock": 18, "rating": 4.81, "review_count": 65, "view_count": 760,
        "image": "https://images.unsplash.com/photo-1510017803434-a899398421b3?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1510017803434-a899398421b3?w=800&auto=format&fit=crop&q=80"],
        "ram": "2GB", "rom": "16GB", "chipset": "Exynos W930 1.4GHz", "screen": "Super AMOLED 1.3 inch Sapphire",
        "battery": "300 mAh, Sạc nhanh không dây", "camera": "Cảm biến huyết áp, ECG, BIA phân tích cơ thể",
        "description": "Vòng xoay bezel vật lý trứ danh, màn hình lớn hơn 20% và huấn luyện viên giấc ngủ cá nhân hóa mỗi ngày.",
        "featured": 0, "created_at": "2026-03-28 00:00:00"
    },

    # 39-42: Phụ kiện chính hãng (Category 6)
    {
        "id": 39, "name": "Củ sạc nhanh Anker 65W GaNPrime 3 cổng A2668", "slug": "cu-sac-anker-65w-ganprime",
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 7, "brand_name": "Anker",
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
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 7, "brand_name": "Anker",
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
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 8, "brand_name": "Logitech",
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
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 8, "brand_name": "Logitech",
        "price": 3390000, "original_price": 3790000, "stock": 25, "rating": 4.91, "review_count": 88, "view_count": 1100,
        "image": "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Switch cơ học Low Profile độ nảy cao", "screen": "Đèn nền thông minh tự động bật sáng khi tay lại gần",
        "battery": "Pin sạc USB-C dùng 15 ngày có đèn (10 tháng tắt đèn)", "camera": "Kết nối 3 thiết bị chuyển đổi tức thì",
        "description": "Bàn phím cơ low-profile gõ cực kỳ êm ái, plate nhôm mờ cao cấp và đèn nền tự cảm biến khoảng cách tay thông minh.",
        "featured": 1, "created_at": "2026-04-10 00:00:00"
    },

    # 43-49: Điện thoại mở rộng (Category 1)
    {
        "id": 43, "name": "iPhone 16 Plus 128GB", "slug": "iphone-16-plus-128gb",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 1, "brand_name": "Apple",
        "price": 25490000, "original_price": 26990000, "stock": 25, "rating": 4.88, "review_count": 54, "view_count": 980,
        "image": "https://images.unsplash.com/photo-1591337676887-a217a6970a8a?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1591337676887-a217a6970a8a?w=800&auto=format&fit=crop&q=80", "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "128GB", "chipset": "Apple A18 3nm", "screen": "6.7 inch Super Retina XDR OLED",
        "battery": "4674 mAh, Sạc nhanh 25W", "camera": "48MP Fusion + 12MP Ultra Wide",
        "description": "iPhone 16 Plus màn hình lớn 6.7 inch, thời lượng pin trâu nhất lịch sử iPhone và nút điều khiển Camera Control đột phá.",
        "featured": 1, "created_at": "2026-09-25 00:00:00"
    },
    {
        "id": 44, "name": "iPhone 15 128GB Pink", "slug": "iphone-15-128gb-pink",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 1, "brand_name": "Apple",
        "price": 18990000, "original_price": 21990000, "stock": 30, "rating": 4.85, "review_count": 110, "view_count": 1450,
        "image": "https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=800&auto=format&fit=crop&q=80"],
        "ram": "6GB", "rom": "128GB", "chipset": "Apple A16 Bionic", "screen": "6.1 inch Super Retina XDR OLED Dynamic Island",
        "battery": "3349 mAh, Sạc nhanh 20W", "camera": "48MP + 12MP Ultra Wide",
        "description": "iPhone 15 mặt lưng kính pha màu sang trọng, Dynamic Island tiện ích và cổng kết nối USB-C tương thích linh hoạt.",
        "featured": 0, "created_at": "2026-07-15 00:00:00"
    },
    {
        "id": 45, "name": "Samsung Galaxy Z Flip6 5G 256GB", "slug": "samsung-galaxy-z-flip6",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 2, "brand_name": "Samsung",
        "price": 24990000, "original_price": 28990000, "stock": 16, "rating": 4.89, "review_count": 67, "view_count": 1120,
        "image": "https://images.unsplash.com/photo-1580910051074-3eb694886505?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800&auto=format&fit=crop&q=80"],
        "ram": "12GB", "rom": "256GB", "chipset": "Snapdragon 8 Gen 3 for Galaxy", "screen": "6.7 inch Dynamic AMOLED 2X 120Hz + Phụ 3.4 inch Super AMOLED",
        "battery": "4000 mAh, Sạc nhanh 25W", "camera": "50MP OIS + 12MP Ultra Wide",
        "description": "Galaxy Z Flip6 gập mở phong cách, camera 50MP nâng cấp vượt bậc, buồng tản nhiệt hơi Vapor Chamber đầu tiên trên dòng Flip.",
        "featured": 1, "created_at": "2026-08-08 00:00:00"
    },
    {
        "id": 46, "name": "Samsung Galaxy S23 FE 5G 8GB/128GB", "slug": "samsung-galaxy-s23-fe",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 2, "brand_name": "Samsung",
        "price": 11990000, "original_price": 14890000, "stock": 28, "rating": 4.78, "review_count": 85, "view_count": 930,
        "image": "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "128GB", "chipset": "Exynos 2200 4nm", "screen": "6.4 inch Dynamic AMOLED 2X 120Hz",
        "battery": "4500 mAh, Sạc nhanh 25W", "camera": "50MP OIS + 12MP + 8MP 3x",
        "description": "Galaxy S23 FE hội tụ các tính năng Flagship cao cấp, Galaxy AI thông minh và thiết kế biểu tượng thời thượng.",
        "featured": 0, "created_at": "2026-06-20 00:00:00"
    },
    {
        "id": 47, "name": "Xiaomi 14 5G 12GB/256GB Leica", "slug": "xiaomi-14-12gb-256gb",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 19990000, "original_price": 22990000, "stock": 22, "rating": 4.87, "review_count": 94, "view_count": 1280,
        "image": "https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&auto=format&fit=crop&q=80"],
        "ram": "12GB", "rom": "256GB", "chipset": "Snapdragon 8 Gen 3", "screen": "6.36 inch OLED 1.5K LTPO 120Hz 3000 nits",
        "battery": "4610 mAh, Sạc nhanh 90W", "camera": "3 camera Leica Summilux 50MP",
        "description": "Xiaomi 14 thiết kế nhỏ gọn cầm nắm hoàn hảo, ống kính quang học Leica Summilux thu sáng đỉnh cao và sạc nhanh 90W.",
        "featured": 1, "created_at": "2026-07-01 00:00:00"
    },
    {
        "id": 48, "name": "Xiaomi Redmi Note 13 Pro+ 5G 8GB/256GB", "slug": "xiaomi-redmi-note-13-pro-plus",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 8990000, "original_price": 10990000, "stock": 45, "rating": 4.82, "review_count": 135, "view_count": 1600,
        "image": "https://images.unsplash.com/photo-1567581935884-3349723552ca?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1567581935884-3349723552ca?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "256GB", "chipset": "MediaTek Dimensity 7200-Ultra 4nm", "screen": "6.67 inch AMOLED 1.5K 120Hz cong",
        "battery": "5000 mAh, Sạc siêu tốc 120W", "camera": "200MP OIS Siêu nét + 8MP + 2MP",
        "description": "Redmi Note 13 Pro+ bứt phá phân khúc với camera 200MP, kháng nước bụi IP68 và sạc đầy 100% pin chỉ 19 phút.",
        "featured": 0, "created_at": "2026-05-18 00:00:00"
    },
    {
        "id": 49, "name": "ASUS ROG Phone 8 12GB/256GB Phantom Black", "slug": "asus-rog-phone-8",
        "category_id": 1, "category_name": "Điện thoại", "brand_id": 4, "brand_name": "ASUS",
        "price": 21990000, "original_price": 24990000, "stock": 15, "rating": 4.90, "review_count": 48, "view_count": 820,
        "image": "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=800&auto=format&fit=crop&q=80"],
        "ram": "12GB", "rom": "256GB", "chipset": "Snapdragon 8 Gen 3", "screen": "6.78 inch FHD+ AMOLED 165Hz LTPO",
        "battery": "5500 mAh, Sạc 65W HyperCharge", "camera": "50MP Sony IMX890 chống rung Gimbal",
        "description": "ASUS ROG Phone 8 mỏng nhẹ đa dụng, chip Snapdragon 8 Gen 3 cân mọi game mobile nặng nhất cùng hệ thống tản nhiệt tiên tiến.",
        "featured": 0, "created_at": "2026-06-10 00:00:00"
    },

    # 50-57: Laptop mở rộng (Category 2)
    {
        "id": 50, "name": "MacBook Air 15 M3 16GB/512GB Starlight", "slug": "macbook-air-15-m3",
        "category_id": 2, "category_name": "Laptop", "brand_id": 1, "brand_name": "Apple",
        "price": 37990000, "original_price": 40990000, "stock": 18, "rating": 4.92, "review_count": 53, "view_count": 1100,
        "image": "https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=800&auto=format&fit=crop&q=80"],
        "ram": "16GB Unified", "rom": "512GB SSD", "chipset": "Apple M3 (8 CPU, 10 GPU)", "screen": "15.3 inch Liquid Retina 500 nits",
        "battery": "66.5Wh, Pin 18 giờ", "camera": "1080p FaceTime HD",
        "description": "MacBook Air 15 inch M3 không gian hiển thị rộng rãi, vỏ nhôm nguyên khối siêu mỏng chỉ 11.5mm và âm thanh 6 loa ấn tượng.",
        "featured": 1, "created_at": "2026-06-15 00:00:00"
    },
    {
        "id": 51, "name": "Laptop ASUS Zenbook Duo OLED UX8406 2 Màn Hình", "slug": "asus-zenbook-duo-oled-ux8406",
        "category_id": 2, "category_name": "Laptop", "brand_id": 4, "brand_name": "ASUS",
        "price": 47990000, "original_price": 52990000, "stock": 10, "rating": 4.94, "review_count": 36, "view_count": 950,
        "image": "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB LPDDR5X", "rom": "1TB PCIe 4.0 SSD", "chipset": "Intel Core Ultra 9 185H NPU AI", "screen": "Kép 14.0 inch 3K OLED 120Hz cảm ứng",
        "battery": "75Wh, Sạc nhanh Type-C", "camera": "FHD IR Windows Hello",
        "description": "Zenbook Duo trang bị 2 màn hình cảm ứng OLED 3K 120Hz mở rộng tầm nhìn, bàn phím Bluetooth tháo rời tiện lợi.",
        "featured": 1, "created_at": "2026-07-12 00:00:00"
    },
    {
        "id": 52, "name": "Laptop Gaming ASUS ROG Strix SCAR 16 RTX 4080", "slug": "asus-rog-strix-scar-16",
        "category_id": 2, "category_name": "Laptop", "brand_id": 4, "brand_name": "ASUS",
        "price": 79990000, "original_price": 84990000, "stock": 6, "rating": 4.97, "review_count": 29, "view_count": 780,
        "image": "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB DDR5 5600MHz", "rom": "2TB PCIe 4.0 SSD", "chipset": "Intel Core i9-14900HX", "screen": "16.0 inch 2.5K Mini LED 240Hz ROG Nebula HDR",
        "battery": "90Wh, Sạc 330W", "camera": "720p HD Camera",
        "description": "Quái vật chiến game ROG Strix SCAR 16 với màn hình Mini LED 240Hz, card RTX 4080 175W max công suất và tản nhiệt 3 quạt kim loại lỏng.",
        "featured": 0, "created_at": "2026-08-01 00:00:00"
    },
    {
        "id": 53, "name": "Dell Latitude 7440 Core i7-1365U Doanh Nghiệp", "slug": "dell-latitude-7440",
        "category_id": 2, "category_name": "Laptop", "brand_id": 6, "brand_name": "Dell",
        "price": 28990000, "original_price": 31990000, "stock": 15, "rating": 4.80, "review_count": 41, "view_count": 670,
        "image": "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=800&auto=format&fit=crop&q=80"],
        "ram": "16GB LPDDR5", "rom": "512GB NVMe SSD", "chipset": "Intel Core i7-1365U vPro", "screen": "14.0 inch FHD+ IPS 16:10 400 nits ComfortView+",
        "battery": "57Wh ExpressCharge", "camera": "FHD IR Camera che cơ học",
        "description": "Dell Latitude 7440 chuẩn văn phòng cao cấp, độ bền hợp kim magie siêu nhẹ và bảo mật phần cứng Intel vPro an toàn.",
        "featured": 0, "created_at": "2026-05-25 00:00:00"
    },
    {
        "id": 54, "name": "Lenovo ThinkPad X1 Carbon Gen 12 Core Ultra 7", "slug": "lenovo-thinkpad-x1-carbon-gen-12",
        "category_id": 2, "category_name": "Laptop", "brand_id": 9, "brand_name": "Lenovo",
        "price": 49990000, "original_price": 54990000, "stock": 12, "rating": 4.96, "review_count": 38, "view_count": 890,
        "image": "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB LPDDR5X", "rom": "1TB PCIe Gen4 SSD", "chipset": "Intel Core Ultra 7 155H NPU", "screen": "14.0 inch 2.8K OLED 120Hz 100% DCI-P3",
        "battery": "57Wh, Pin 16 giờ", "camera": "8MP FHD IR Communications Bar",
        "description": "Biểu tượng doanh nhân ThinkPad X1 Carbon Gen 12 sợi carbon siêu nhẹ 1.09kg, bàn phím gõ đỉnh cao và màn hình OLED 120Hz.",
        "featured": 1, "created_at": "2026-06-18 00:00:00"
    },
    {
        "id": 55, "name": "Lenovo Legion Pro 5 16IRX9 i7-14700HX RTX 4060", "slug": "lenovo-legion-pro-5",
        "category_id": 2, "category_name": "Laptop", "brand_id": 9, "brand_name": "Lenovo",
        "price": 38990000, "original_price": 42990000, "stock": 20, "rating": 4.88, "review_count": 57, "view_count": 1040,
        "image": "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB DDR5 5600MHz", "rom": "1TB SSD M.2 NVMe", "chipset": "Intel Core i7-14700HX 20 nhân", "screen": "16.0 inch WQXGA IPS 240Hz 500 nits 100% sRGB",
        "battery": "80Wh, Sạc nhanh 300W", "camera": "1080p FHD",
        "description": "Laptop Gaming Legion Pro 5 hệ thống tản nhiệt Legion Coldfront 5.0, chip AI Lenovo LA1 tối ưu FPS tự động khi chơi game.",
        "featured": 0, "created_at": "2026-07-20 00:00:00"
    },
    {
        "id": 56, "name": "Acer Swift Go 14 OLED Core Ultra 5 125H", "slug": "acer-swift-go-14-oled",
        "category_id": 2, "category_name": "Laptop", "brand_id": 10, "brand_name": "Acer",
        "price": 21490000, "original_price": 23990000, "stock": 25, "rating": 4.81, "review_count": 44, "view_count": 730,
        "image": "https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=800&auto=format&fit=crop&q=80"],
        "ram": "16GB LPDDR5X", "rom": "512GB PCIe Gen4 SSD", "chipset": "Intel Core Ultra 5 125H tích hợp Intel Arc", "screen": "14.0 inch 2.8K OLED 90Hz 100% DCI-P3 500 nits",
        "battery": "65Wh, Sạc 100W Type-C", "camera": "1440p QHD Webcam AI khử nhiễu",
        "description": "Acer Swift Go 14 chuẩn Intel Evo màn hình 2.8K OLED rực rỡ, webcam QHD sắc nét cùng trọng lượng chỉ 1.3kg.",
        "featured": 0, "created_at": "2026-05-30 00:00:00"
    },
    {
        "id": 57, "name": "Acer Predator Helios 16 i9-14900HX RTX 4070", "slug": "acer-predator-helios-16",
        "category_id": 2, "category_name": "Laptop", "brand_id": 10, "brand_name": "Acer",
        "price": 56990000, "original_price": 61990000, "stock": 8, "rating": 4.93, "review_count": 31, "view_count": 650,
        "image": "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=80"],
        "ram": "32GB DDR5 5600MHz", "rom": "1TB PCIe Gen4 SSD", "chipset": "Intel Core i9-14900HX", "screen": "16.0 inch WQXGA IPS 240Hz 500 nits G-Sync",
        "battery": "90Wh, Sạc 330W", "camera": "1080p FHD",
        "description": "Predator Helios 16 thiết kế đậm chất phi thuyền vũ trụ, quạt kim loại 3D AeroBlade thế hệ 5 tản nhiệt cực êm và RTX 4070 uy lực.",
        "featured": 0, "created_at": "2026-08-14 00:00:00"
    },

    # 58-63: Tablet mở rộng (Category 3)
    {
        "id": 58, "name": "iPad Pro 13 inch M4 OLED 256GB Wi-Fi", "slug": "ipad-pro-13-m4-oled",
        "category_id": 3, "category_name": "Tablet", "brand_id": 1, "brand_name": "Apple",
        "price": 36990000, "original_price": 38990000, "stock": 15, "rating": 4.98, "review_count": 65, "view_count": 1200,
        "image": "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "256GB", "chipset": "Apple M4 chip 9-core CPU", "screen": "13.0 inch Ultra Retina XDR Tandem OLED 120Hz",
        "battery": "38.99 Wh, Pin 10 giờ", "camera": "12MP Wide LiDAR + 12MP Landscape Center Stage",
        "description": "iPad Pro 13 inch siêu mỏng 5.1mm mỏng nhất lịch sử Apple, màn hình Tandem OLED rực rỡ và sức mạnh chip M4 tối tân.",
        "featured": 1, "created_at": "2026-06-25 00:00:00"
    },
    {
        "id": 59, "name": "iPad mini 7 128GB Wi-Fi Apple Intelligence", "slug": "ipad-mini-7-128gb",
        "category_id": 3, "category_name": "Tablet", "brand_id": 1, "brand_name": "Apple",
        "price": 13990000, "original_price": 14990000, "stock": 25, "rating": 4.86, "review_count": 72, "view_count": 980,
        "image": "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "128GB", "chipset": "Apple A17 Pro 3nm", "screen": "8.3 inch Liquid Retina True Tone",
        "battery": "19.3 Wh, Pin 10 giờ", "camera": "12MP Wide trước & sau",
        "description": "iPad mini 7 nhỏ gọn trong lòng bàn tay, chip A17 Pro mạnh mẽ hỗ trợ Apple Intelligence và bút Apple Pencil Pro.",
        "featured": 0, "created_at": "2026-09-10 00:00:00"
    },
    {
        "id": 60, "name": "Samsung Galaxy Tab S9 11 inch 8GB/128GB S-Pen", "slug": "samsung-galaxy-tab-s9",
        "category_id": 3, "category_name": "Tablet", "brand_id": 2, "brand_name": "Samsung",
        "price": 16990000, "original_price": 19990000, "stock": 20, "rating": 4.88, "review_count": 59, "view_count": 850,
        "image": "https://images.unsplash.com/photo-1585792180666-f7547c6a28c5?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1585792180666-f7547c6a28c5?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "128GB", "chipset": "Snapdragon 8 Gen 2 for Galaxy", "screen": "11.0 inch Dynamic AMOLED 2X 120Hz",
        "battery": "8400 mAh, Sạc nhanh 45W", "camera": "13MP sau, 12MP trước siêu rộng",
        "description": "Galaxy Tab S9 chuẩn chống nước kháng bụi IP68, màn hình Dynamic AMOLED 2X rực rỡ và bút S-Pen đi kèm thỏa sức sáng tạo.",
        "featured": 0, "created_at": "2026-07-05 00:00:00"
    },
    {
        "id": 61, "name": "Samsung Galaxy Tab A9+ 5G 4GB/64GB", "slug": "samsung-galaxy-tab-a9-plus",
        "category_id": 3, "category_name": "Tablet", "brand_id": 2, "brand_name": "Samsung",
        "price": 5490000, "original_price": 6490000, "stock": 35, "rating": 4.75, "review_count": 98, "view_count": 1100,
        "image": "https://images.unsplash.com/photo-1527698266440-12104e498b76?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1527698266440-12104e498b76?w=800&auto=format&fit=crop&q=80"],
        "ram": "4GB", "rom": "64GB", "chipset": "Snapdragon 695 5G", "screen": "11.0 inch TFT LCD 90Hz",
        "battery": "7040 mAh, Sạc 15W", "camera": "8MP sau, 5MP trước",
        "description": "Galaxy Tab A9+ kết nối 5G tốc độ cao, hệ thống 4 loa Dolby Atmos sống động và màn hình 90Hz giải trí xem phim tuyệt vời.",
        "featured": 0, "created_at": "2026-05-10 00:00:00"
    },
    {
        "id": 62, "name": "Xiaomi Pad 6 8GB/256GB Gravity Gray", "slug": "xiaomi-pad-6-8gb-256gb",
        "category_id": 3, "category_name": "Tablet", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 7990000, "original_price": 8990000, "stock": 30, "rating": 4.83, "review_count": 112, "view_count": 1350,
        "image": "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1561154464-82e9adf32764?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB", "rom": "256GB", "chipset": "Snapdragon 870", "screen": "11.0 inch 2.8K 144Hz WQHD+ 10-bit",
        "battery": "8840 mAh, Sạc 33W", "camera": "13MP sau, 8MP trước",
        "description": "Xiaomi Pad 6 thiết kế nhôm nguyên khối siêu mỏng 6.51mm, màn hình 2.8K 144Hz siêu mượt và pin bền bỉ gần 2 ngày sử dụng.",
        "featured": 0, "created_at": "2026-06-12 00:00:00"
    },
    {
        "id": 63, "name": "Lenovo Tab P12 Pro 12.6 inch AMOLED 120Hz", "slug": "lenovo-tab-p12-pro",
        "category_id": 3, "category_name": "Tablet", "brand_id": 9, "brand_name": "Lenovo",
        "price": 14490000, "original_price": 16990000, "stock": 14, "rating": 4.82, "review_count": 35, "view_count": 620,
        "image": "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80"],
        "ram": "8GB LPDDR5", "rom": "256GB UFS 3.1", "chipset": "Snapdragon 870", "screen": "12.6 inch 2K AMOLED 120Hz HDR10+ Dolby Vision",
        "battery": "10200 mAh, Sạc nhanh 45W", "camera": "Kép 13MP + 5MP góc rộng, Kép 8MP trước",
        "description": "Lenovo Tab P12 Pro màn hình AMOLED 12.6 inch rực rỡ, 4 loa JBL chuyên nghiệp và bút cảm ứng Lenovo Precision Pen 3.",
        "featured": 0, "created_at": "2026-07-28 00:00:00"
    },

    # 64-71: Âm thanh mở rộng (Category 4)
    {
        "id": 64, "name": "Tai nghe Apple AirPods 4 Chống Ồn Chủ Động ANC", "slug": "apple-airpods-4-anc",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 1, "brand_name": "Apple",
        "price": 4490000, "original_price": 4790000, "stock": 35, "rating": 4.89, "review_count": 88, "view_count": 1400,
        "image": "https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Apple H2 chip", "screen": "Driver Apple độ méo tiếng siêu thấp",
        "battery": "Pin 5 giờ (30 giờ kèm hộp), Sạc không dây/USB-C", "camera": "Chống ồn chủ động ANC trên thiết kế tai mở",
        "description": "AirPods 4 lần đầu tiên mang chống ồn chủ động lên thiết kế mở open-ear, chip H2 lọc giọng nói trong trẻo và âm thanh không gian cá nhân hóa.",
        "featured": 1, "created_at": "2026-09-22 00:00:00"
    },
    {
        "id": 65, "name": "Loa Bluetooth Marshall Stanmore III 80W Chính Hãng", "slug": "marshall-stanmore-iii",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 11, "brand_name": "Marshall",
        "price": 8990000, "original_price": 9990000, "stock": 15, "rating": 4.95, "review_count": 76, "view_count": 1250,
        "image": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Bộ khuếch tán âm Class D 80W (Woofer 50W + 2 Tweeter 15W)", "screen": "Thiết kế da sần Vintage & Núm kim loại đồng thau",
        "battery": "Nguồn điện trực tiếp 100-240V", "camera": "Bluetooth 5.2 LE Audio + Cổng AUX 3.5mm/RCA",
        "description": "Biểu tượng loa để bàn Marshall Stanmore III không gian âm trường mở rộng, bass nội lực uy lực và ngoại hình cổ điển đậm chất rock.",
        "featured": 1, "created_at": "2026-06-30 00:00:00"
    },
    {
        "id": 66, "name": "Loa Bluetooth Marshall Emberton II Kháng Nước IP67", "slug": "marshall-emberton-ii",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 11, "brand_name": "Marshall",
        "price": 3990000, "original_price": 4590000, "stock": 25, "rating": 4.91, "review_count": 64, "view_count": 890,
        "image": "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Âm thanh đa hướng True Stereophonic 360 độ", "screen": "2 củ loa toàn dải 2 inch 10W + 2 màng cộng hưởng thụ động",
        "battery": "Pin khủng 30 giờ, Sạc nhanh 20 phút nghe 4 giờ", "camera": "Chuẩn chống bụi nước IP67 siêu bền",
        "description": "Loa di động Marshall Emberton II âm thanh 360 độ True Stereophonic đặc trưng, pin 30 giờ và tính năng Stack Mode kết nối nhiều loa cùng lúc.",
        "featured": 0, "created_at": "2026-05-15 00:00:00"
    },
    {
        "id": 67, "name": "Loa Bluetooth JBL Charge 5 Công Suất 40W Pin 20h", "slug": "jbl-charge-5",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 12, "brand_name": "JBL",
        "price": 3490000, "original_price": 3990000, "stock": 30, "rating": 4.88, "review_count": 105, "view_count": 1320,
        "image": "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "JBL Original Pro Sound công nghệ Bass Radiator kép", "screen": "Racetrack driver 52x90mm + Tweeter 20mm riêng biệt",
        "battery": "Pin 7500 mAh chơi nhạc 20 giờ kiêm sạc dự phòng điện thoại", "camera": "Kháng nước chống bụi IP67 chuẩn dã ngoại",
        "description": "Loa di động tiệc tùng JBL Charge 5 chất âm JBL Pro Sound bùng nổ, thời lượng pin 20 giờ và khả năng sạc ngược cho smartphone.",
        "featured": 1, "created_at": "2026-07-18 00:00:00"
    },
    {
        "id": 68, "name": "Loa Bluetooth JBL Flip 6 Nhỏ Gọn Kháng Nước", "slug": "jbl-flip-6",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 12, "brand_name": "JBL",
        "price": 2490000, "original_price": 2990000, "stock": 35, "rating": 4.85, "review_count": 82, "view_count": 1020,
        "image": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Hệ thống loa 2 chiều 30W (Woofer 20W + Tweeter 10W)", "screen": "Màng loa hình đường đua + 2 màng thụ động tối ưu bass",
        "battery": "Pin 12 giờ, Sạc Type-C bảo vệ an toàn", "camera": "Kháng nước và chống bụi hoàn hảo IP67",
        "description": "JBL Flip 6 thiết kế hình ống năng động cầm vừa vặn, âm thanh 2 chiều trong trẻo và công nghệ PartyBoost ghép nối nhiều loa.",
        "featured": 0, "created_at": "2026-06-05 00:00:00"
    },
    {
        "id": 69, "name": "Tai nghe Sony ULT WEAR Chống Ồn Bass Siêu Trầm", "slug": "sony-ult-wear",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 5, "brand_name": "Sony",
        "price": 3990000, "original_price": 4490000, "stock": 25, "rating": 4.84, "review_count": 47, "view_count": 760,
        "image": "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Sony Integrated Processor V1 tối ưu chống ồn", "screen": "Củ loa 40mm chuyên biệt dòng ULT Power Sound",
        "battery": "Pin 30 giờ (bật ANC), Sạc nhanh 3 phút nghe 90 phút", "camera": "Nút bấm ULT kích hoạt 2 chế độ Extra Bass uy lực",
        "description": "Sony ULT WEAR sinh ra cho tín đồ nhạc sôi động EDM/Hip-hop với nút bấm ULT tăng cường âm trầm rung chuyển màng nhĩ và chống ồn V1 xuất sắc.",
        "featured": 0, "created_at": "2026-05-22 00:00:00"
    },
    {
        "id": 70, "name": "Tai nghe Soundcore Liberty 4 NC Chống Ồn 98.5%", "slug": "soundcore-liberty-4-nc",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 7, "brand_name": "Anker",
        "price": 1890000, "original_price": 2390000, "stock": 45, "rating": 4.87, "review_count": 118, "view_count": 1420,
        "image": "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Chuẩn Hi-Res Wireless Audio với codec LDAC", "screen": "Driver 11mm tùy chỉnh âm học độ nhạy cao",
        "battery": "Pin 10 giờ (50 giờ kèm hộp), Sạc không dây Qi", "camera": "Chống ồn thích ứng Adaptive ANC 2.0 giảm 98.5% tạp âm",
        "description": "Tai nghe Soundcore Liberty 4 NC chống ồn thích ứng chuẩn SGS giảm đến 98.5% tiếng ồn, chứng nhận âm thanh Hi-Res Wireless LDAC đỉnh cao.",
        "featured": 0, "created_at": "2026-06-18 00:00:00"
    },
    {
        "id": 71, "name": "Loa Bluetooth Soundcore Motion X600 Spatial Audio 50W", "slug": "soundcore-motion-x600",
        "category_id": 4, "category_name": "Âm thanh", "brand_id": 7, "brand_name": "Anker",
        "price": 3890000, "original_price": 4590000, "stock": 20, "rating": 4.90, "review_count": 52, "view_count": 810,
        "image": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Thuật toán âm thanh không gian Spatial Audio 5 kênh", "screen": "5 củ loa và 3 bộ khuếch đại độc lập công suất 50W",
        "battery": "Pin 6400 mAh nghe nhạc 12 giờ", "camera": "Vỏ nhôm phay xước cao cấp chuẩn kháng nước IPX7",
        "description": "Loa di động Anker Soundcore Motion X600 mang lại trải nghiệm âm thanh rạp hát tại gia với công nghệ Spatial Audio 5 driver và chuẩn Hi-Res LDAC.",
        "featured": 0, "created_at": "2026-07-25 00:00:00"
    },

    # 72-76: Smartwatch mở rộng (Category 5)
    {
        "id": 72, "name": "Apple Watch Series 10 GPS 46mm Nhôm Jet Black", "slug": "apple-watch-series-10-46mm",
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 1, "brand_name": "Apple",
        "price": 11490000, "original_price": 12290000, "stock": 25, "rating": 4.93, "review_count": 62, "view_count": 1350,
        "image": "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": "64GB", "chipset": "Apple S10 SiP 64-bit lõi tứ Neural Engine", "screen": "OLED góc nhìn rộng sáng hơn 40% khi nhìn nghiêng",
        "battery": "Pin 18 giờ, Sạc siêu nhanh 80% chỉ trong 30 phút", "camera": "Cảm biến độ sâu, nhiệt độ nước, phát hiện ngưng thở khi ngủ",
        "description": "Apple Watch Series 10 thiết kế mỏng nhất từ trước đến nay (9.7mm), màn hình hiển thị lớn hơn cả bản Ultra và sạc cực nhanh.",
        "featured": 1, "created_at": "2026-09-28 00:00:00"
    },
    {
        "id": 73, "name": "Apple Watch SE 2024 GPS 44mm Viền Nhôm", "slug": "apple-watch-se-2024-44mm",
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 1, "brand_name": "Apple",
        "price": 6590000, "original_price": 7490000, "stock": 35, "rating": 4.82, "review_count": 95, "view_count": 1280,
        "image": "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": "32GB", "chipset": "Apple S8 SiP 64-bit", "screen": "Retina LTPO OLED 1000 nits kính Ion-X",
        "battery": "Pin 18 giờ chế độ sử dụng tiêu chuẩn", "camera": "Cảm biến nhịp tim quang học thế hệ 2 + Phát hiện té ngã",
        "description": "Apple Watch SE lựa chọn thông minh giá tốt nhất hệ sinh thái Apple, đầy đủ tính năng theo dõi vận động, nhịp tim và cứu hộ SOS.",
        "featured": 0, "created_at": "2026-06-15 00:00:00"
    },
    {
        "id": 74, "name": "Samsung Galaxy Watch7 44mm Bluetooth Kính Sapphire", "slug": "samsung-galaxy-watch7-44mm",
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 2, "brand_name": "Samsung",
        "price": 7490000, "original_price": 8490000, "stock": 22, "rating": 4.87, "review_count": 51, "view_count": 920,
        "image": "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=800&auto=format&fit=crop&q=80"],
        "ram": "2GB", "rom": "32GB", "chipset": "Exynos W1000 tiến trình 3nm 5 nhân", "screen": "1.5 inch Super AMOLED 2000 nits Sapphire Glass",
        "battery": "425 mAh, Sạc nhanh không dây WPC", "camera": "Cảm biến BioActive thế hệ mới + Huấn luyện viên giấc ngủ Galaxy AI",
        "description": "Galaxy Watch7 trang bị chip 3nm mạnh mẽ gấp 3 lần, định vị GPS tần số kép L1+L5 chuẩn xác từng mét và trợ lý sức khỏe Galaxy AI.",
        "featured": 1, "created_at": "2026-08-05 00:00:00"
    },
    {
        "id": 75, "name": "Samsung Galaxy Watch FE 40mm Dây Thể Thao", "slug": "samsung-galaxy-watch-fe",
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 2, "brand_name": "Samsung",
        "price": 4490000, "original_price": 4990000, "stock": 30, "rating": 4.79, "review_count": 42, "view_count": 780,
        "image": "https://images.unsplash.com/photo-1510017803434-a899398421b3?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1510017803434-a899398421b3?w=800&auto=format&fit=crop&q=80"],
        "ram": "1.5GB", "rom": "16GB", "chipset": "Exynos W920 Dual-Core 1.18GHz", "screen": "1.2 inch Super AMOLED kính Sapphire chống trầy",
        "battery": "247 mAh, Sạc không dây từ tính", "camera": "Cảm biến nhịp tim, đo nồng độ oxy trong máu SpO2, ECG",
        "description": "Galaxy Watch FE phiên bản Fan Edition tinh gọn, kính Sapphire cao cấp và hơn 100 chế độ tập luyện thể dục thể thao chuyên biệt.",
        "featured": 0, "created_at": "2026-06-28 00:00:00"
    },
    {
        "id": 76, "name": "Xiaomi Watch 2 Pro 4G LTE Snapdragon W5+ Gen 1", "slug": "xiaomi-watch-2-pro",
        "category_id": 5, "category_name": "Smartwatch", "brand_id": 3, "brand_name": "Xiaomi",
        "price": 5990000, "original_price": 6990000, "stock": 18, "rating": 4.83, "review_count": 39, "view_count": 690,
        "image": "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=80"],
        "ram": "2GB", "rom": "32GB", "chipset": "Snapdragon W5+ Gen 1 4nm tiết kiệm điện", "screen": "1.43 inch AMOLED 466x466 600 nits khung thép không gỉ",
        "battery": "495 mAh, Thời lượng pin 55 giờ ở chế độ LTE", "camera": "Hệ điều hành Google Wear OS đầy đủ kho ứng dụng Play Store",
        "description": "Xiaomi Watch 2 Pro kết nối eSIM 4G độc lập nghe gọi không cần điện thoại, Wear OS mượt mà và khung vỏ thép không gỉ sang trọng.",
        "featured": 0, "created_at": "2026-07-10 00:00:00"
    },

    # 77-82: Phụ kiện chính hãng mở rộng (Category 6)
    {
        "id": 77, "name": "Củ sạc nhanh Anker 30W GaN 511 Nano 3 A2147", "slug": "cu-sac-anker-30w-gan-nano-3",
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 7, "brand_name": "Anker",
        "price": 390000, "original_price": 520000, "stock": 80, "rating": 4.94, "review_count": 260, "view_count": 2450,
        "image": "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Chíp bán dẫn GaN III nhỏ hơn 70% củ sạc 30W thường", "screen": "1 cổng USB-C Power Delivery 3.0 & PPS 30W",
        "battery": "ActiveShield 2.0 đo nhiệt độ 3 triệu lần/ngày", "camera": "Sạc siêu tốc iPhone 16 từ 0-50% trong 25 phút",
        "description": "Củ sạc siêu tí hon Anker Nano 3 công suất 30W hỗ trợ sạc nhanh tối đa cho iPhone 16 Pro Max, iPad Air và MacBook Air.",
        "featured": 1, "created_at": "2026-08-12 00:00:00"
    },
    {
        "id": 78, "name": "Pin dự phòng Anker MagGo 10.000mAh Qi2 15W Màn Hình LED", "slug": "pin-du-phong-anker-maggo-10000mah",
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 7, "brand_name": "Anker",
        "price": 1490000, "original_price": 1890000, "stock": 40, "rating": 4.91, "review_count": 145, "view_count": 1680,
        "image": "https://images.unsplash.com/photo-1609592424109-dd9892f1b177?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1609592424109-dd9892f1b177?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Chuẩn sạc không dây thế hệ mới Qi2 Certified 15W", "screen": "Màn hình thông minh hiển thị chính xác % pin và thời gian sạc",
        "battery": "Dung lượng 10.000 mAh, Cổng Type-C vào/ra công suất 27W", "camera": "Hút nam châm từ tính MagSafe cực mạnh kiêm chân dựng máy gập",
        "description": "Pin sạc từ tính Anker MagGo chuẩn Qi2 15W sạc nhanh không dây gấp đôi MagSafe thường, màn hình thông minh hiển thị thời gian pin còn lại.",
        "featured": 1, "created_at": "2026-08-20 00:00:00"
    },
    {
        "id": 79, "name": "Cáp sạc Anker Bio-Braided Type-C to Type-C 240W 1.8m", "slug": "cap-sac-anker-type-c-240w",
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 7, "brand_name": "Anker",
        "price": 290000, "original_price": 390000, "stock": 90, "rating": 4.89, "review_count": 198, "view_count": 1900,
        "image": "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "E-Marker chip tích hợp kiểm soát dòng điện an toàn", "screen": "Dây bọc dù sinh học uốn gập 20.000 lần không đứt gãy",
        "battery": "Hỗ trợ công suất truyền tải điện Power Delivery tối đa 240W", "camera": "Tương thích mọi thiết bị từ iPhone 16 đến MacBook Pro 16 inch",
        "description": "Dây cáp bọc dù thân thiện môi trường Anker Bio-Braided chịu tải đến 240W, sạc tốc độ tối đa cho mọi laptop và smartphone hiện đại.",
        "featured": 0, "created_at": "2026-07-15 00:00:00"
    },
    {
        "id": 80, "name": "Bàn phím cơ Logitech G PRO X TKL LIGHTSPEED Wireless", "slug": "logitech-g-pro-x-tkl",
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 8, "brand_name": "Logitech",
        "price": 4190000, "original_price": 4690000, "stock": 20, "rating": 4.95, "review_count": 78, "view_count": 940,
        "image": "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Công nghệ không dây Lightspeed độ trễ cực thấp 1ms", "screen": "Đèn LED RGB LIGHTSYNC 16.8 triệu màu từng phím",
        "battery": "Pin sạc USB-C dùng liên tục 50 giờ chơi game", "camera": "Switch cơ học GX Brown Tactile độ phản hồi xúc giác hoàn hảo",
        "description": "Bàn phím cơ thi đấu Esports chuyên nghiệp Logitech G PRO X TKL thiết kế Tenkeyless nhỏ gọn, keycap PBT doubleshot bền bỉ và kết nối Lightspeed 1ms.",
        "featured": 0, "created_at": "2026-08-01 00:00:00"
    },
    {
        "id": 81, "name": "Chuột Gaming không dây Logitech G Pro X Superlight 2", "slug": "logitech-g-pro-x-superlight-2",
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 8, "brand_name": "Logitech",
        "price": 3490000, "original_price": 3990000, "stock": 25, "rating": 4.97, "review_count": 130, "view_count": 1550,
        "image": "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Cảm biến HERO 2 độ phân giải 32.000 DPI, theo dõi 500+ IPS", "screen": "Switch quang học - cơ học lai LIGHTFORCE phản xạ tức thì",
        "battery": "Pin khủng 95 giờ sử dụng, cổng sạc USB-C hiện đại", "camera": "Trọng lượng siêu nhẹ chỉ 60g không cần đục lỗ tổ ong",
        "description": "Huyền thoại chuột gaming FPS thế giới Superlight 2 với trọng lượng siêu nhẹ 60g, switch hybrid LIGHTFORCE và tần số lấy mẫu Polling Rate 4000Hz.",
        "featured": 1, "created_at": "2026-08-18 00:00:00"
    },
    {
        "id": 82, "name": "Bút cảm ứng Apple Pencil Pro Dành Cho iPad M4 / M2", "slug": "apple-pencil-pro",
        "category_id": 6, "category_name": "Phụ kiện chính hãng", "brand_id": 1, "brand_name": "Apple",
        "price": 3290000, "original_price": 3490000, "stock": 40, "rating": 4.96, "review_count": 115, "view_count": 1380,
        "image": "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=600&auto=format&fit=crop&q=80",
        "images": ["https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=800&auto=format&fit=crop&q=80"],
        "ram": None, "rom": None, "chipset": "Cảm biến con quay hồi chuyển Barrel Roll xoay thân bút", "screen": "Cảm ứng lực bóp Squeeze mở bảng công cụ tức thì",
        "battery": "Sạc không dây từ tính gắn cạnh iPad, hỗ trợ Find My", "camera": "Phản hồi xúc giác Haptic Feedback rung phản hồi khi thao tác",
        "description": "Apple Pencil Pro đỉnh cao sáng tạo với thao tác bóp Squeeze, xoay thân bút đổi nét cọ Barrel Roll và tìm kiếm vị trí Find My tiện lợi.",
        "featured": 0, "created_at": "2026-06-25 00:00:00"
    }'''

def update_build_seed():
    with open("database/build_seed.py", "r", encoding="utf-8") as f:
        content = f.read()

    # 1. Replace categories
    old_cat_start = content.find("# 2. Categories")
    old_cat_end = content.find("# 3. Brands")
    if old_cat_start != -1 and old_cat_end != -1:
        content = content[:old_cat_start] + new_categories_code + "\n\n" + content[old_cat_end:]
    else:
        print("ERROR: Could not find categories section")
        return False

    # 2. Replace brands
    old_brand_start = content.find("# 3. Brands")
    old_brand_end = content.find("# 4. Products")
    if old_brand_start != -1 and old_brand_end != -1:
        content = content[:old_brand_start] + new_brands_code + "\n\n" + content[old_brand_end:]
    else:
        print("ERROR: Could not find brands section")
        return False

    # 3. Replace products 35 to end of products
    old_p35_start = content.find("    # 35-42: Smartwatch & Phụ kiện")
    old_prod_end = content.find("# 5. Coupons")
    if old_p35_start != -1 and old_prod_end != -1:
        # Find closing bracket of products before coupons
        bracket_pos = content.rfind("]", old_p35_start, old_prod_end)
        if bracket_pos != -1:
            content = content[:old_p35_start] + new_products_code + "\n" + content[bracket_pos:]
        else:
            print("ERROR: Could not find products closing bracket")
            return False
    else:
        print("ERROR: Could not find products 35-42 section")
        return False

    # 4. Update audit log text if present
    content = content.replace("Khởi tạo hệ thống CSDL và bộ dữ liệu hạt giống 42 sản phẩm 8 thương hiệu",
                              "Khởi tạo hệ thống CSDL và bộ dữ liệu hạt giống 82 sản phẩm 12 thương hiệu 6 danh mục")

    with open("database/build_seed.py", "w", encoding="utf-8") as f:
        f.write(content)
    print("Successfully updated database/build_seed.py")
    return True

if __name__ == "__main__":
    if update_build_seed():
        import subprocess
        print("Running database/build_seed.py...")
        res = subprocess.run(["python", "database/build_seed.py"], capture_output=True, text=True, encoding="utf-8")
        print("build_seed output:", res.stdout)
        if res.stderr:
            print("build_seed stderr:", res.stderr)

