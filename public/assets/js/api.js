/**
 * TechStore - Central REST API Client & Helper Utilities
 * Supports real backend /api/v1 + seamless mock fallback for standalone demo
 * Implements 100% of the 37 APIs from Project Plan Sheet 8
 * Author: Tuan Kiet (Front-end & Documentation)
 */

const API_CONFIG = {
  baseUrl: '/api/v1',
  timeout: 8000,
  storageKeys: {
    accessToken: 'techstore_access_token',
    refreshToken: 'techstore_refresh_token',
    user: 'techstore_user',
    cart: 'techstore_cart',
    wishlist: 'techstore_wishlist',
    orders: 'techstore_orders',
    products: 'techstore_products_db',
    reviews: 'techstore_reviews_db',
    categories: 'techstore_categories_db',
    brands: 'techstore_brands_db',
    coupons: 'techstore_coupons_db',
    auditLogs: 'techstore_audit_logs'
  }
};

// ==========================================
// TOAST NOTIFICATIONS & UI HELPERS
// ==========================================
function showToast(message, type = 'info', duration = 3500) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  
  let icon = 'ℹ️';
  if (type === 'success') icon = '✅';
  if (type === 'danger' || type === 'error') icon = '⚠️';
  if (type === 'warning') icon = '🔔';

  toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

function formatVND(amount) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount || 0);
}

function debounce(func, delay = 300) {
  let timer;
  return function (...args) {
    clearTimeout(timer);
    timer = setTimeout(() => func.apply(this, args), delay);
  };
}

// ==========================================
// INITIAL MOCK DATABASE SEED
// ==========================================
const DEFAULT_PRODUCTS = [
  {
    "id": 1,
    "name": "iPhone 16 Pro Max 256GB",
    "slug": "iphone-16-pro-max-256gb",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 34990000,
    "original_price": 37990000,
    "stock": 25,
    "rating": 4.95,
    "review_count": 142,
    "view_count": 1850,
    "image": "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&auto=format&fit=crop&q=80",
      "https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "256GB",
    "chipset": "Apple A18 Pro 3nm",
    "screen": "6.9 inch Super Retina XDR OLED 120Hz",
    "battery": "4685 mAh, Sạc nhanh 30W",
    "camera": "Chính 48MP + Góc rộng 48MP + Tele 5x 12MP",
    "description": "iPhone 16 Pro Max thiết kế Titan sa mạc đẳng cấp, vi xử lý A18 Pro mạnh mẽ và nút Camera Control hoàn toàn mới mang đến trải nghiệm quay chụp điện ảnh đỉnh cao.",
    "featured": 1,
    "created_at": "2026-09-20 00:00:00"
  },
  {
    "id": 2,
    "name": "iPhone 16 128GB",
    "slug": "iphone-16-128gb",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 21990000,
    "original_price": 23990000,
    "stock": 35,
    "rating": 4.88,
    "review_count": 89,
    "view_count": 1200,
    "image": "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "128GB",
    "chipset": "Apple A18 3nm",
    "screen": "6.1 inch Super Retina XDR OLED",
    "battery": "3561 mAh, Sạc nhanh 25W",
    "camera": "48MP Fusion + 12MP Ultra Wide",
    "description": "iPhone 16 với màu sắc Ultramarine hiện đại, nút chụp Camera Control và hiệu năng bứt phá phục vụ Apple Intelligence.",
    "featured": 1,
    "created_at": "2026-09-22 00:00:00"
  },
  {
    "id": 3,
    "name": "iPhone 15 Pro 128GB",
    "slug": "iphone-15-pro-128gb",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 24990000,
    "original_price": 28990000,
    "stock": 18,
    "rating": 4.85,
    "review_count": 110,
    "view_count": 950,
    "image": "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "128GB",
    "chipset": "Apple A17 Pro",
    "screen": "6.1 inch Super Retina XDR OLED 120Hz ProMotion",
    "battery": "3274 mAh, Sạc nhanh 20W",
    "camera": "48MP + 12MP + 12MP 3x",
    "description": "iPhone 15 Pro khung viền Titan cấp hàng không vũ trụ nhẹ và bền, cổng USB-C tốc độ cao và nút Action tùy biến.",
    "featured": 0,
    "created_at": "2026-08-10 00:00:00"
  },
  {
    "id": 4,
    "name": "Samsung Galaxy S24 Ultra 5G 12GB/256GB",
    "slug": "samsung-galaxy-s24-ultra",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 29990000,
    "original_price": 33990000,
    "stock": 20,
    "rating": 4.9,
    "review_count": 105,
    "view_count": 1600,
    "image": "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "12GB",
    "rom": "256GB",
    "chipset": "Snapdragon 8 Gen 3 for Galaxy",
    "screen": "6.8 inch Dynamic AMOLED 2X 120Hz 2600 nits",
    "battery": "5000 mAh, Sạc nhanh 45W",
    "camera": "200MP + 50MP + 12MP + 10MP, Zoom 100x",
    "description": "Galaxy S24 Ultra khai mở kỷ nguyên Galaxy AI, khung viền Titan sang trọng và bút S-Pen quyền năng.",
    "featured": 1,
    "created_at": "2026-08-15 00:00:00"
  },
  {
    "id": 5,
    "name": "Samsung Galaxy S24+ 12GB/256GB",
    "slug": "samsung-galaxy-s24-plus",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 21490000,
    "original_price": 26990000,
    "stock": 22,
    "rating": 4.82,
    "review_count": 64,
    "view_count": 820,
    "image": "https://images.unsplash.com/photo-1580910051074-3eb694886505?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "12GB",
    "rom": "256GB",
    "chipset": "Exynos 2400 Deca-Core",
    "screen": "6.7 inch QHD+ Dynamic AMOLED 2X 120Hz",
    "battery": "4900 mAh, Sạc 45W",
    "camera": "50MP + 12MP + 10MP 3x",
    "description": "Galaxy S24+ sở hữu màn hình QHD+ sắc nét, dung lượng pin tăng cường và thiết kế phẳng thanh lịch.",
    "featured": 0,
    "created_at": "2026-07-20 00:00:00"
  },
  {
    "id": 6,
    "name": "Samsung Galaxy Z Fold6 5G 256GB",
    "slug": "samsung-galaxy-z-fold6",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 41990000,
    "original_price": 43990000,
    "stock": 12,
    "rating": 4.87,
    "review_count": 45,
    "view_count": 980,
    "image": "https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "12GB",
    "rom": "256GB",
    "chipset": "Snapdragon 8 Gen 3 for Galaxy",
    "screen": "7.6 inch Foldable Dynamic AMOLED 2X 120Hz",
    "battery": "4400 mAh, Sạc 25W",
    "camera": "50MP + 12MP + 10MP",
    "description": "Galaxy Z Fold6 thiết kế phẳng mỏng nhẹ hơn bao giờ hết, bản lề Flex Hinge kép mở ra không gian đa nhiệm đỉnh cao.",
    "featured": 1,
    "created_at": "2026-08-01 00:00:00"
  },
  {
    "id": 7,
    "name": "Xiaomi 14 Ultra 5G 16GB/512GB Leica",
    "slug": "xiaomi-14-ultra",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 28990000,
    "original_price": 32990000,
    "stock": 15,
    "rating": 4.89,
    "review_count": 78,
    "view_count": 1100,
    "image": "https://images.unsplash.com/photo-1567581935884-3349723552ca?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1567581935884-3349723552ca?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "16GB",
    "rom": "512GB",
    "chipset": "Snapdragon 8 Gen 3",
    "screen": "6.73 inch LTPO AMOLED 120Hz 3000 nits",
    "battery": "5000 mAh, Sạc nhanh 90W",
    "camera": "4 camera Leica 50MP cảm biến 1 inch",
    "description": "Xiaomi 14 Ultra hợp tác cùng Leica mang lại hệ thống 4 ống kính quang học Summilux, khẩu độ vô cấp cho ảnh chụp xuất sắc.",
    "featured": 1,
    "created_at": "2026-06-15 00:00:00"
  },
  {
    "id": 8,
    "name": "Xiaomi 14T Pro 5G 12GB/512GB",
    "slug": "xiaomi-14t-pro",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 16990000,
    "original_price": 17990000,
    "stock": 30,
    "rating": 4.8,
    "review_count": 52,
    "view_count": 750,
    "image": "https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "12GB",
    "rom": "512GB",
    "chipset": "MediaTek Dimensity 9300+",
    "screen": "6.67 inch AMOLED 144Hz 4000 nits",
    "battery": "5000 mAh, Sạc 120W HyperCharge",
    "camera": "50MP Leica + 50MP Tele + 12MP Ultra Wide",
    "description": "Xiaomi 14T Pro camera Leica thế hệ mới, sạc siêu tốc 120W và hiệu năng Dimensity 9300+ cực khủng.",
    "featured": 0,
    "created_at": "2026-09-28 00:00:00"
  },
  {
    "id": 9,
    "name": "ASUS ROG Phone 8 Pro 16GB/512GB",
    "slug": "asus-rog-phone-8-pro",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 4,
    "brand_name": "ASUS",
    "price": 27990000,
    "original_price": 29990000,
    "stock": 10,
    "rating": 4.92,
    "review_count": 63,
    "view_count": 890,
    "image": "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "16GB",
    "rom": "512GB",
    "chipset": "Snapdragon 8 Gen 3",
    "screen": "6.78 inch Samsung AMOLED 165Hz LTPO",
    "battery": "5500 mAh, Sạc 65W HyperCharge",
    "camera": "50MP Sony IMX890 chống rung Gimbal 6 trục",
    "description": "Quái vật gaming ROG Phone 8 Pro với màn hình LED AniMe Vision mặt lưng, tản nhiệt GameCool 8 và nút cảm ứng AirTrigger.",
    "featured": 1,
    "created_at": "2026-07-05 00:00:00"
  },
  {
    "id": 10,
    "name": "Samsung Galaxy A55 5G 8GB/128GB",
    "slug": "samsung-galaxy-a55-5g",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 9490000,
    "original_price": 9990000,
    "stock": 40,
    "rating": 4.75,
    "review_count": 115,
    "view_count": 1400,
    "image": "https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "128GB",
    "chipset": "Exynos 1480 4nm",
    "screen": "6.6 inch Super AMOLED 120Hz",
    "battery": "5000 mAh, Sạc 25W",
    "camera": "50MP OIS + 12MP + 5MP",
    "description": "Galaxy A55 viền kim loại sang trọng, bảo mật cấp phần cứng Knox Vault và kháng nước bụi chuẩn IP67.",
    "featured": 0,
    "created_at": "2026-05-10 00:00:00"
  },
  {
    "id": 11,
    "name": "MacBook Pro 14 M3 Pro 18GB/512GB Space Black",
    "slug": "macbook-pro-14-m3-pro",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 49990000,
    "original_price": 52990000,
    "stock": 15,
    "rating": 4.96,
    "review_count": 68,
    "view_count": 1500,
    "image": "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "18GB",
    "rom": "512GB SSD",
    "chipset": "Apple M3 Pro (11 CPU, 14 GPU)",
    "screen": "14.2 inch Liquid Retina XDR 120Hz ProMotion",
    "battery": "70Wh, Thời lượng pin 18 giờ",
    "camera": "1080p FaceTime HD",
    "description": "MacBook Pro 14 M3 Pro hiệu năng đồ họa ấn tượng với Dynamic Caching, tản nhiệt tối ưu và màu Space Black cuốn hút.",
    "featured": 1,
    "created_at": "2026-07-10 00:00:00"
  },
  {
    "id": 12,
    "name": "MacBook Air 13 M3 8GB/256GB Midnight",
    "slug": "macbook-air-13-m3",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 26990000,
    "original_price": 27990000,
    "stock": 28,
    "rating": 4.88,
    "review_count": 92,
    "view_count": 1350,
    "image": "https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "256GB SSD",
    "chipset": "Apple M3 (8 CPU, 8 GPU)",
    "screen": "13.6 inch Liquid Retina True Tone",
    "battery": "52.6Wh, Pin lên tới 18 giờ",
    "camera": "1080p FaceTime HD",
    "description": "MacBook Air M3 siêu nhẹ chỉ 1.24kg, hỗ trợ xuất 2 màn hình ngoài cùng thời lượng pin bền bỉ cả ngày làm việc.",
    "featured": 1,
    "created_at": "2026-05-20 00:00:00"
  },
  {
    "id": 13,
    "name": "MacBook Pro 16 M3 Max 36GB/1TB Silver",
    "slug": "macbook-pro-16-m3-max",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 89990000,
    "original_price": 92990000,
    "stock": 6,
    "rating": 4.98,
    "review_count": 31,
    "view_count": 720,
    "image": "https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "36GB",
    "rom": "1TB SSD",
    "chipset": "Apple M3 Max (14 CPU, 30 GPU)",
    "screen": "16.2 inch Liquid Retina XDR 1600 nits 120Hz",
    "battery": "100Wh, Pin 22 giờ",
    "camera": "1080p FaceTime HD",
    "description": "Đỉnh cao máy trạm di động render 3D chuyên sâu, dựng phim 8K ProRes và huấn luyện mô hình AI không nghẽn cổ chai.",
    "featured": 0,
    "created_at": "2026-04-10 00:00:00"
  },
  {
    "id": 14,
    "name": "Laptop ASUS ROG Zephyrus G16 OLED RTX 4070",
    "slug": "asus-rog-zephyrus-g16-oled",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 4,
    "brand_name": "ASUS",
    "price": 59990000,
    "original_price": 64990000,
    "stock": 8,
    "rating": 4.91,
    "review_count": 45,
    "view_count": 1100,
    "image": "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB LPDDR5X",
    "rom": "1TB PCIe 4.0 SSD",
    "chipset": "Intel Core Ultra 9 185H",
    "screen": "16.0 inch 2.5K OLED 240Hz 0.2ms Nebula",
    "battery": "90Wh, Sạc 100W Type-C",
    "camera": "1080p FHD IR Windows Hello",
    "description": "Laptop Gaming siêu mỏng nhẹ khung nhôm CNC, màn hình OLED 240Hz sắc nét đỉnh cao cùng card đồ họa RTX 4070.",
    "featured": 1,
    "created_at": "2026-06-01 00:00:00"
  },
  {
    "id": 15,
    "name": "Laptop ASUS Zenbook 14 OLED UX3405 Core Ultra 7",
    "slug": "asus-zenbook-14-oled-ux3405",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 4,
    "brand_name": "ASUS",
    "price": 29490000,
    "original_price": 31990000,
    "stock": 20,
    "rating": 4.86,
    "review_count": 56,
    "view_count": 890,
    "image": "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "16GB LPDDR5X",
    "rom": "1TB NVMe SSD",
    "chipset": "Intel Core Ultra 7 155H tích hợp NPU AI",
    "screen": "14.0 inch 3K OLED 120Hz 100% DCI-P3",
    "battery": "75Wh, Pin 15 giờ",
    "camera": "FHD IR Camera",
    "description": "Zenbook 14 OLED chuẩn Intel Evo mỏng 14.9mm nặng 1.2kg, màn hình 3K OLED tuyệt mỹ và xử lý AI tăng tốc.",
    "featured": 1,
    "created_at": "2026-05-18 00:00:00"
  },
  {
    "id": 16,
    "name": "Laptop Gaming ASUS TUF Gaming A15 Ryzen 7 RTX 4060",
    "slug": "asus-tuf-gaming-a15",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 4,
    "brand_name": "ASUS",
    "price": 25990000,
    "original_price": 28990000,
    "stock": 25,
    "rating": 4.79,
    "review_count": 74,
    "view_count": 980,
    "image": "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "16GB DDR5",
    "rom": "512GB PCIe SSD",
    "chipset": "AMD Ryzen 7 7735HS",
    "screen": "15.6 inch FHD IPS 144Hz 100% sRGB",
    "battery": "90Wh, Sạc nhanh",
    "camera": "720p HD Camera",
    "description": "Laptop gaming độ bền chuẩn quân đội MIL-STD-810H, đồ họa RTX 4060 chiến mượt mọi tựa game AAA đỉnh cao.",
    "featured": 0,
    "created_at": "2026-06-12 00:00:00"
  },
  {
    "id": 17,
    "name": "Dell XPS 14 9440 Core Ultra 7 OLED RTX 4050",
    "slug": "dell-xps-14-9440",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 6,
    "brand_name": "Dell",
    "price": 54990000,
    "original_price": 58990000,
    "stock": 10,
    "rating": 4.93,
    "review_count": 38,
    "view_count": 790,
    "image": "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB LPDDR5X",
    "rom": "1TB SSD",
    "chipset": "Intel Core Ultra 7 155H",
    "screen": "14.5 inch 3.2K OLED Touch 120Hz",
    "battery": "69.5Wh, Sạc 100W",
    "camera": "1080p FHD IR",
    "description": "Dell XPS 14 ngôn ngữ tương lai kính nguyên khối, trackpad tàng hình cảm ứng lực và bàn phím Zero-lattice sang trọng.",
    "featured": 1,
    "created_at": "2026-07-25 00:00:00"
  },
  {
    "id": 18,
    "name": "Dell XPS 16 9640 Core Ultra 9 RTX 4070",
    "slug": "dell-xps-16-9640",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 6,
    "brand_name": "Dell",
    "price": 76990000,
    "original_price": 81990000,
    "stock": 5,
    "rating": 4.95,
    "review_count": 27,
    "view_count": 650,
    "image": "https://images.unsplash.com/photo-1587614382346-4ec70e388b28?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1587614382346-4ec70e388b28?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB LPDDR5X",
    "rom": "1TB PCIe SSD",
    "chipset": "Intel Core Ultra 9 185H",
    "screen": "16.3 inch 4K+ OLED Touch InfinityEdge",
    "battery": "99.5Wh, Sạc 130W",
    "camera": "1080p FHD IR",
    "description": "Kiệt tác công nghệ Dell XPS 16 màn hình 4K OLED tràn viền vô cực cùng sức mạnh xử lý đồ họa chuyên sâu.",
    "featured": 0,
    "created_at": "2026-05-30 00:00:00"
  },
  {
    "id": 19,
    "name": "Dell Inspiron 14 5440 Core i5-1334U",
    "slug": "dell-inspiron-14-5440",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 6,
    "brand_name": "Dell",
    "price": 16490000,
    "original_price": 18490000,
    "stock": 30,
    "rating": 4.76,
    "review_count": 85,
    "view_count": 1120,
    "image": "https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "16GB DDR5",
    "rom": "512GB SSD",
    "chipset": "Intel Core i5-1334U",
    "screen": "14.0 inch FHD+ 16:10 Anti-Glare",
    "battery": "54Wh",
    "camera": "1080p FHD Camera",
    "description": "Dell Inspiron 14 bền bỉ, bàn phím gõ êm ái, cấu hình 16GB RAM mượt mà phục vụ học tập và công việc văn phòng.",
    "featured": 0,
    "created_at": "2026-04-18 00:00:00"
  },
  {
    "id": 20,
    "name": "Xiaomi RedmiBook Pro 15 Ryzen 7 8845HS",
    "slug": "xiaomi-redmibook-pro-15",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 18990000,
    "original_price": 20990000,
    "stock": 20,
    "rating": 4.82,
    "review_count": 49,
    "view_count": 710,
    "image": "https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB LPDDR5X",
    "rom": "1TB PCIe 4.0 SSD",
    "chipset": "AMD Ryzen 7 8845HS tích hợp Ryzen AI",
    "screen": "15.6 inch 3.2K 165Hz 500 nits HDR",
    "battery": "80Wh, Sạc 100W GaN",
    "camera": "1080p Full HD",
    "description": "RedmiBook Pro 15 vỏ nhôm unibody cao cấp, màn hình 3.2K 165Hz và dung lượng pin khủng 80Wh.",
    "featured": 0,
    "created_at": "2026-06-25 00:00:00"
  },
  {
    "id": 21,
    "name": "iPad Pro 11 M4 OLED 256GB Wi-Fi Space Black",
    "slug": "ipad-pro-11-m4-oled",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 27990000,
    "original_price": 28990000,
    "stock": 20,
    "rating": 4.97,
    "review_count": 72,
    "view_count": 1300,
    "image": "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "256GB",
    "chipset": "Apple M4 9-core CPU, 10-core GPU",
    "screen": "11.0 inch Ultra Retina XDR Tandem OLED 120Hz",
    "battery": "31.29 Wh, Pin 10 giờ",
    "camera": "12MP Wide LiDAR + 12MP Landscape Center Stage",
    "description": "iPad Pro M4 thiết kế mỏng không tưởng chỉ 5.3mm, màn hình Tandem OLED đột phá tái định nghĩa công nghệ hiển thị.",
    "featured": 1,
    "created_at": "2026-05-25 00:00:00"
  },
  {
    "id": 22,
    "name": "iPad Air 6 M2 11 inch 128GB Wi-Fi",
    "slug": "ipad-air-6-m2-11-inch",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 16490000,
    "original_price": 17990000,
    "stock": 30,
    "rating": 4.88,
    "review_count": 88,
    "view_count": 1420,
    "image": "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "128GB",
    "chipset": "Apple M2 8-core CPU",
    "screen": "11.0 inch Liquid Retina True Tone",
    "battery": "28.93 Wh, Pin 10 giờ",
    "camera": "12MP Wide trước & sau",
    "description": "iPad Air 6 trang bị vi xử lý Apple M2 mạnh mẽ vượt trội, hỗ trợ bút Apple Pencil Pro thế hệ mới.",
    "featured": 1,
    "created_at": "2026-05-22 00:00:00"
  },
  {
    "id": 23,
    "name": "iPad Gen 10 10.9 inch 64GB Wi-Fi Pink",
    "slug": "ipad-gen-10-64gb",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 9490000,
    "original_price": 10990000,
    "stock": 45,
    "rating": 4.81,
    "review_count": 130,
    "view_count": 1600,
    "image": "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "4GB",
    "rom": "64GB",
    "chipset": "Apple A14 Bionic",
    "screen": "10.9 inch Liquid Retina IPS",
    "battery": "28.6 Wh, Pin 10 giờ",
    "camera": "12MP Wide trước & sau",
    "description": "iPad Gen 10 thiết kế vuông vức tràn viền, 4 sắc màu cá tính và cổng kết nối Type-C tiện dụng.",
    "featured": 0,
    "created_at": "2026-04-15 00:00:00"
  },
  {
    "id": 24,
    "name": "Samsung Galaxy Tab S9 Ultra 12GB/256GB S-Pen",
    "slug": "samsung-galaxy-tab-s9-ultra",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 23990000,
    "original_price": 27990000,
    "stock": 12,
    "rating": 4.92,
    "review_count": 53,
    "view_count": 850,
    "image": "https://images.unsplash.com/photo-1585792180666-f7547c6a28c5?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1585792180666-f7547c6a28c5?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "12GB",
    "rom": "256GB",
    "chipset": "Snapdragon 8 Gen 2 for Galaxy",
    "screen": "14.6 inch Dynamic AMOLED 2X 120Hz",
    "battery": "11200 mAh, Sạc 45W",
    "camera": "13MP + 8MP sau, Kép 12MP trước",
    "description": "Galaxy Tab S9 Ultra màn hình khổng lồ 14.6 inch, chuẩn kháng nước IP68 đầu tiên và bút S-Pen sáng tạo.",
    "featured": 1,
    "created_at": "2026-06-18 00:00:00"
  },
  {
    "id": 25,
    "name": "Samsung Galaxy Tab S9 FE 6GB/128GB S-Pen",
    "slug": "samsung-galaxy-tab-s9-fe",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 8990000,
    "original_price": 9990000,
    "stock": 25,
    "rating": 4.79,
    "review_count": 67,
    "view_count": 920,
    "image": "https://images.unsplash.com/photo-1527698266440-12104e498b76?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1527698266440-12104e498b76?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "6GB",
    "rom": "128GB",
    "chipset": "Exynos 1380",
    "screen": "10.9 inch IPS LCD 90Hz",
    "battery": "8000 mAh, Sạc 45W",
    "camera": "8MP sau, 12MP trước",
    "description": "Tab S9 FE kèm sẵn bút S-Pen trong hộp, thời lượng pin cả ngày dài và loa kép AKG sống động.",
    "featured": 0,
    "created_at": "2026-05-02 00:00:00"
  },
  {
    "id": 26,
    "name": "Xiaomi Pad 6S Pro 12.4 inch 8GB/256GB",
    "slug": "xiaomi-pad-6s-pro",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 13990000,
    "original_price": 14990000,
    "stock": 20,
    "rating": 4.84,
    "review_count": 42,
    "view_count": 680,
    "image": "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "256GB",
    "chipset": "Snapdragon 8 Gen 2",
    "screen": "12.4 inch 3K 144Hz 3:2 tỉ lệ vàng",
    "battery": "10000 mAh, Sạc 120W HyperCharge",
    "camera": "50MP sau, 32MP trước",
    "description": "Xiaomi Pad 6S Pro màn hình 3K 144Hz tỉ lệ 3:2 làm việc tối ưu, sạc đầy 100% pin chỉ trong 35 phút.",
    "featured": 0,
    "created_at": "2026-06-08 00:00:00"
  },
  {
    "id": 27,
    "name": "Tai nghe Sony WH-1000XM5 Chống Ồn Flagship",
    "slug": "sony-wh-1000xm5",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 5,
    "brand_name": "Sony",
    "price": 7490000,
    "original_price": 8990000,
    "stock": 30,
    "rating": 4.93,
    "review_count": 168,
    "view_count": 1950,
    "image": "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Sony Integrated Processor V1 + HD QN1",
    "screen": "Driver 30mm sợi carbon cao cấp",
    "battery": "Pin 30 giờ (bật ANC), Sạc 3 phút nghe 3 giờ",
    "camera": "8 micro AI lọc gió",
    "description": "Công nghệ chống ồn số 1 thị trường, âm thanh chuẩn Hi-Res Audio Wireless LDAC đem lại trải nghiệm thư thái tuyệt đối.",
    "featured": 1,
    "created_at": "2026-05-18 00:00:00"
  },
  {
    "id": 28,
    "name": "Tai nghe Sony WF-1000XM5 True Wireless",
    "slug": "sony-wf-1000xm5",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 5,
    "brand_name": "Sony",
    "price": 5990000,
    "original_price": 6990000,
    "stock": 25,
    "rating": 4.87,
    "review_count": 94,
    "view_count": 1250,
    "image": "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Sony V2 + QN2e Dual Processor",
    "screen": "Driver Dynamic X 8.4mm tái tạo âm trầm sâu",
    "battery": "Pin 8 giờ + 16 giờ hộp sạc, IPX4",
    "camera": "3 micro mỗi bên + Cảm biến xương Bone conduction",
    "description": "Tai nghe in-ear chống ồn đỉnh cao, kích thước thu gọn 25% đeo vừa vặn êm ái suốt cả ngày dài.",
    "featured": 1,
    "created_at": "2026-04-20 00:00:00"
  },
  {
    "id": 29,
    "name": "Loa Bluetooth Sony SRS-XE300 Kháng nước IP67",
    "slug": "sony-srs-xe300",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 5,
    "brand_name": "Sony",
    "price": 3490000,
    "original_price": 4290000,
    "stock": 20,
    "rating": 4.8,
    "review_count": 51,
    "view_count": 620,
    "image": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Bộ khuếch tán âm Line-Shape Diffuser",
    "screen": "Màng loa X-Balanced Speaker Unit",
    "battery": "Pin 24 giờ, Chống sốc, chống nước IP67",
    "camera": "Micro đàm thoại khử vọng",
    "description": "Loa di động âm thanh lan tỏa rộng khắp không gian, chống nước chống bụi chuẩn biển dã ngoại.",
    "featured": 0,
    "created_at": "2026-03-15 00:00:00"
  },
  {
    "id": 30,
    "name": "Tai nghe Apple AirPods Pro 2 USB-C MagSafe",
    "slug": "apple-airpods-pro-2-usb-c",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 5490000,
    "original_price": 6190000,
    "stock": 40,
    "rating": 4.94,
    "review_count": 210,
    "view_count": 2300,
    "image": "https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Apple H2 chip + U1 chip hộp sạc",
    "screen": "Driver Apple độ méo thấp tùy chỉnh",
    "battery": "Pin 6 giờ (30 giờ kèm hộp sạc), Sạc MagSafe/USB-C",
    "camera": "Chống ồn chủ động gấp 2 lần + Adaptive Audio",
    "description": "AirPods Pro 2 âm thanh không gian cá nhân hóa, chống ồn chủ động vượt trội và hộp sạc tìm kiếm Precision Finding.",
    "featured": 1,
    "created_at": "2026-06-20 00:00:00"
  },
  {
    "id": 31,
    "name": "Tai nghe Apple AirPods Max USB-C 2024",
    "slug": "apple-airpods-max-usb-c",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 13290000,
    "original_price": 13990000,
    "stock": 10,
    "rating": 4.85,
    "review_count": 48,
    "view_count": 850,
    "image": "https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Apple H1 chip mỗi bên tai nghe",
    "screen": "Driver 40mm động học do Apple thiết kế",
    "battery": "Pin 20 giờ bật ANC và Âm thanh không gian",
    "camera": "9 micro lọc âm đàm thoại trong trẻo",
    "description": "Thiết kế trùm tai sang trọng với khung thép không gỉ, đệm tai dạng lưới thoáng khí và âm thanh độ trung thực cao.",
    "featured": 0,
    "created_at": "2026-09-21 00:00:00"
  },
  {
    "id": 32,
    "name": "Tai nghe Samsung Galaxy Buds3 Pro Galaxy AI",
    "slug": "samsung-galaxy-buds3-pro",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 4990000,
    "original_price": 5490000,
    "stock": 25,
    "rating": 4.82,
    "review_count": 60,
    "view_count": 780,
    "image": "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Dual Amp + Samsung Seamless Codec 24bit/96kHz",
    "screen": "Loa 2 chiều Woofer 10.5mm + Tweeter phẳng 6.1mm",
    "battery": "Pin 7 giờ + 30 giờ hộp sạc",
    "camera": "Dải đèn Blade Lights + Khử ồn thông minh AI",
    "description": "Thiết kế dạng thân mới tích hợp đèn LED Blade Lights, âm thanh Hi-Fi 24-bit và phiên dịch trực tiếp qua Galaxy AI.",
    "featured": 0,
    "created_at": "2026-07-28 00:00:00"
  },
  {
    "id": 33,
    "name": "Tai nghe Xiaomi Redmi Buds 5 Pro Chống Ồn 52dB",
    "slug": "xiaomi-redmi-buds-5-pro",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 1490000,
    "original_price": 1790000,
    "stock": 50,
    "rating": 4.77,
    "review_count": 82,
    "view_count": 950,
    "image": "https://images.unsplash.com/photo-1572536147248-ac59a8abfa4b?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1572536147248-ac59a8abfa4b?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Codec LHDC 5.0 chuẩn Hi-Res Audio Wireless",
    "screen": "Driver kép đồng trục loa trầm titan 11mm + gốm 10mm",
    "battery": "Pin 10 giờ (38 giờ hộp sạc), Sạc nhanh 5 phút",
    "camera": "Chống ồn sâu 52dB tần số siêu rộng 4kHz",
    "description": "Tai nghe chống ồn tốt nhất phân khúc dưới 2 triệu đồng, màng loa gốm cao cấp và âm thanh Hi-Res chi tiết.",
    "featured": 0,
    "created_at": "2026-05-12 00:00:00"
  },
  {
    "id": 34,
    "name": "Loa Bluetooth Anker Soundcore Motion Boom Plus 80W",
    "slug": "anker-soundcore-motion-boom-plus",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 7,
    "brand_name": "Anker",
    "price": 3290000,
    "original_price": 3990000,
    "stock": 20,
    "rating": 4.88,
    "review_count": 64,
    "view_count": 710,
    "image": "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Công nghệ tăng cường âm trầm BassUp",
    "screen": "Công suất 80W (2 Woofer 30W + 2 Tweeter 10W)",
    "battery": "Pin 13400 mAh, phát nhạc 20 giờ kiêm sạc dự phòng",
    "camera": "Chống nước và nổi trên mặt nước chuẩn IP67",
    "description": "Loa tiệc ngoài trời công suất bùng nổ 80W, chống nước hoàn hảo nổi được trên mặt nước cho các chuyến dã ngoại.",
    "featured": 0,
    "created_at": "2026-04-18 00:00:00"
  },
  {
    "id": 35,
    "name": "Apple Watch Ultra 2 GPS + Cellular 49mm Titanium",
    "slug": "apple-watch-ultra-2",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 20990000,
    "original_price": 22990000,
    "stock": 15,
    "rating": 4.95,
    "review_count": 76,
    "view_count": 1200,
    "image": "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": "64GB",
    "chipset": "Apple S9 SiP 64-bit + Chip UWB thế hệ 2",
    "screen": "OLED LTPO Always-On 3000 nits đỉnh sáng",
    "battery": "Pin 36 giờ (72 giờ chế độ nguồn điện thấp)",
    "camera": "Còi báo động 86dB + Kháng nước 100m chuẩn lặn EN13319",
    "description": "Đồng hồ thể thao dã ngoại chuyên nghiệp, vỏ Titan tái chế bền bỉ và độ sáng màn hình 3000 nits thách thức ánh mặt trời.",
    "featured": 1,
    "created_at": "2026-05-05 00:00:00"
  },
  {
    "id": 36,
    "name": "Apple Watch Series 9 GPS 45mm Nhôm Midnight",
    "slug": "apple-watch-series-9-45mm",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 10490000,
    "original_price": 11290000,
    "stock": 25,
    "rating": 4.86,
    "review_count": 92,
    "view_count": 1150,
    "image": "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": "64GB",
    "chipset": "Apple S9 SiP lõi tứ Neural Engine",
    "screen": "OLED LTPO Always-On 2000 nits",
    "battery": "Pin 18 giờ, Sạc nhanh 0-80% trong 45 phút",
    "camera": "Cảm biến ECG, SpO2, đo nhiệt độ cổ tay",
    "description": "Apple Watch Series 9 thao tác chạm 2 ngón Double Tap kỳ diệu, màn hình sáng gấp đôi và theo dõi sức khỏe chuyên sâu.",
    "featured": 1,
    "created_at": "2026-04-25 00:00:00"
  },
  {
    "id": 37,
    "name": "Samsung Galaxy Watch Ultra 47mm LTE Titanium",
    "slug": "samsung-galaxy-watch-ultra",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 16490000,
    "original_price": 17990000,
    "stock": 12,
    "rating": 4.9,
    "review_count": 48,
    "view_count": 870,
    "image": "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "2GB",
    "rom": "32GB",
    "chipset": "Exynos W1000 3nm 5 nhân",
    "screen": "Super AMOLED 1.5 inch 3000 nits kính Sapphire",
    "battery": "590 mAh, Pin lên tới 100 giờ ở chế độ tiết kiệm",
    "camera": "Cảm biến BioActive + Đo chỉ số AGEs chống lão hóa",
    "description": "Galaxy Watch Ultra thiết kế vỏ đệm Cushion độc đáo, GPS tần số kép L1+L5 chính xác và năng lực AI chăm sóc sức khỏe.",
    "featured": 1,
    "created_at": "2026-07-22 00:00:00"
  },
  {
    "id": 38,
    "name": "Samsung Galaxy Watch6 Classic 43mm Bluetooth",
    "slug": "samsung-galaxy-watch-6-classic",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 6490000,
    "original_price": 7990000,
    "stock": 18,
    "rating": 4.81,
    "review_count": 65,
    "view_count": 760,
    "image": "https://images.unsplash.com/photo-1510017803434-a899398421b3?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1510017803434-a899398421b3?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "2GB",
    "rom": "16GB",
    "chipset": "Exynos W930 1.4GHz",
    "screen": "Super AMOLED 1.3 inch Sapphire",
    "battery": "300 mAh, Sạc nhanh không dây",
    "camera": "Cảm biến huyết áp, ECG, BIA phân tích cơ thể",
    "description": "Vòng xoay bezel vật lý trứ danh, màn hình lớn hơn 20% và huấn luyện viên giấc ngủ cá nhân hóa mỗi ngày.",
    "featured": 0,
    "created_at": "2026-03-28 00:00:00"
  },
  {
    "id": 39,
    "name": "Củ sạc nhanh Anker 65W GaNPrime 3 cổng A2668",
    "slug": "cu-sac-anker-65w-ganprime",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 7,
    "brand_name": "Anker",
    "price": 890000,
    "original_price": 1150000,
    "stock": 60,
    "rating": 4.9,
    "review_count": 184,
    "view_count": 1890,
    "image": "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Công nghệ GaNPrime thế hệ mới nhất",
    "screen": "2 cổng USB-C (Max 65W) + 1 cổng USB-A (22.5W)",
    "battery": "Bảo vệ nhiệt ActiveShield 2.0 kiểm soát nhiệt",
    "camera": "Chuẩn Power Delivery 3.0 và PPS Samsung",
    "description": "Củ sạc siêu nhỏ gọn nhẹ hơn 38% củ sạc hãng, phân bổ công suất thông minh PowerIQ 4.0 sạc cùng lúc MacBook, iPhone và iPad.",
    "featured": 1,
    "created_at": "2026-04-12 00:00:00"
  },
  {
    "id": 40,
    "name": "Củ sạc nhanh Anker 100W GaNPrime A2343",
    "slug": "cu-sac-anker-100w-ganprime",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 7,
    "brand_name": "Anker",
    "price": 1290000,
    "original_price": 1590000,
    "stock": 40,
    "rating": 4.92,
    "review_count": 98,
    "view_count": 1200,
    "image": "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Công nghệ GaN thế hệ mới",
    "screen": "2 cổng USB-C + 1 cổng USB-A (Công suất đơn max 100W)",
    "battery": "ActiveShield 2.0 bảo vệ an toàn cháy nổ",
    "camera": "Tương thích laptop Dell XPS, MacBook Pro 16",
    "description": "Củ sạc 100W mạnh mẽ sạc đầy 50% pin MacBook Pro 16 inch chỉ trong 30 phút, đồng thời sạc nhanh điện thoại và phụ kiện.",
    "featured": 0,
    "created_at": "2026-05-15 00:00:00"
  },
  {
    "id": 41,
    "name": "Chuột không dây Logitech MX Master 3S Quiet Clicks",
    "slug": "logitech-mx-master-3s",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 8,
    "brand_name": "Logitech",
    "price": 2190000,
    "original_price": 2490000,
    "stock": 35,
    "rating": 4.96,
    "review_count": 175,
    "view_count": 1750,
    "image": "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Cảm biến Darkfield 8000 DPI trên mọi bề mặt kính",
    "screen": "Cuộn điện từ MagSpeed cuộn 1000 dòng/giây",
    "battery": "Pin sạc 500mAh dùng 70 ngày, Sạc nhanh USB-C",
    "camera": "Nút click êm ái giảm 90% tiếng ồn + Kết nối 3 thiết bị",
    "description": "Chuột công thái học cao cấp tốt nhất cho lập trình viên và nhà sáng tạo, con lăn điện từ MagSpeed và cảm biến 8000 DPI lướt mượt trên kính.",
    "featured": 1,
    "created_at": "2026-04-08 00:00:00"
  },
  {
    "id": 42,
    "name": "Bàn phím cơ không dây Logitech MX Mechanical Tactile",
    "slug": "logitech-mx-mechanical",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 8,
    "brand_name": "Logitech",
    "price": 3390000,
    "original_price": 3790000,
    "stock": 25,
    "rating": 4.91,
    "review_count": 88,
    "view_count": 1100,
    "image": "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Switch cơ học Low Profile độ nảy cao",
    "screen": "Đèn nền thông minh tự động bật sáng khi tay lại gần",
    "battery": "Pin sạc USB-C dùng 15 ngày có đèn (10 tháng tắt đèn)",
    "camera": "Kết nối 3 thiết bị chuyển đổi tức thì",
    "description": "Bàn phím cơ low-profile gõ cực kỳ êm ái, plate nhôm mờ cao cấp và đèn nền tự cảm biến khoảng cách tay thông minh.",
    "featured": 1,
    "created_at": "2026-04-10 00:00:00"
  },
  {
    "id": 43,
    "name": "iPhone 16 Plus 128GB",
    "slug": "iphone-16-plus-128gb",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 25490000,
    "original_price": 26990000,
    "stock": 25,
    "rating": 4.88,
    "review_count": 54,
    "view_count": 980,
    "image": "https://images.unsplash.com/photo-1591337676887-a217a6970a8a?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1591337676887-a217a6970a8a?w=800&auto=format&fit=crop&q=80",
      "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "128GB",
    "chipset": "Apple A18 3nm",
    "screen": "6.7 inch Super Retina XDR OLED",
    "battery": "4674 mAh, Sạc nhanh 25W",
    "camera": "48MP Fusion + 12MP Ultra Wide",
    "description": "iPhone 16 Plus màn hình lớn 6.7 inch, thời lượng pin trâu nhất lịch sử iPhone và nút điều khiển Camera Control đột phá.",
    "featured": 1,
    "created_at": "2026-09-25 00:00:00"
  },
  {
    "id": 44,
    "name": "iPhone 15 128GB Pink",
    "slug": "iphone-15-128gb-pink",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 18990000,
    "original_price": 21990000,
    "stock": 30,
    "rating": 4.85,
    "review_count": 110,
    "view_count": 1450,
    "image": "https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "6GB",
    "rom": "128GB",
    "chipset": "Apple A16 Bionic",
    "screen": "6.1 inch Super Retina XDR OLED Dynamic Island",
    "battery": "3349 mAh, Sạc nhanh 20W",
    "camera": "48MP + 12MP Ultra Wide",
    "description": "iPhone 15 mặt lưng kính pha màu sang trọng, Dynamic Island tiện ích và cổng kết nối USB-C tương thích linh hoạt.",
    "featured": 0,
    "created_at": "2026-07-15 00:00:00"
  },
  {
    "id": 45,
    "name": "Samsung Galaxy Z Flip6 5G 256GB",
    "slug": "samsung-galaxy-z-flip6",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 24990000,
    "original_price": 28990000,
    "stock": 16,
    "rating": 4.89,
    "review_count": 67,
    "view_count": 1120,
    "image": "https://images.unsplash.com/photo-1580910051074-3eb694886505?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "12GB",
    "rom": "256GB",
    "chipset": "Snapdragon 8 Gen 3 for Galaxy",
    "screen": "6.7 inch Dynamic AMOLED 2X 120Hz + Phụ 3.4 inch Super AMOLED",
    "battery": "4000 mAh, Sạc nhanh 25W",
    "camera": "50MP OIS + 12MP Ultra Wide",
    "description": "Galaxy Z Flip6 gập mở phong cách, camera 50MP nâng cấp vượt bậc, buồng tản nhiệt hơi Vapor Chamber đầu tiên trên dòng Flip.",
    "featured": 1,
    "created_at": "2026-08-08 00:00:00"
  },
  {
    "id": 46,
    "name": "Samsung Galaxy S23 FE 5G 8GB/128GB",
    "slug": "samsung-galaxy-s23-fe",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 11990000,
    "original_price": 14890000,
    "stock": 28,
    "rating": 4.78,
    "review_count": 85,
    "view_count": 930,
    "image": "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "128GB",
    "chipset": "Exynos 2200 4nm",
    "screen": "6.4 inch Dynamic AMOLED 2X 120Hz",
    "battery": "4500 mAh, Sạc nhanh 25W",
    "camera": "50MP OIS + 12MP + 8MP 3x",
    "description": "Galaxy S23 FE hội tụ các tính năng Flagship cao cấp, Galaxy AI thông minh và thiết kế biểu tượng thời thượng.",
    "featured": 0,
    "created_at": "2026-06-20 00:00:00"
  },
  {
    "id": 47,
    "name": "Xiaomi 14 5G 12GB/256GB Leica",
    "slug": "xiaomi-14-12gb-256gb",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 19990000,
    "original_price": 22990000,
    "stock": 22,
    "rating": 4.87,
    "review_count": 94,
    "view_count": 1280,
    "image": "https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "12GB",
    "rom": "256GB",
    "chipset": "Snapdragon 8 Gen 3",
    "screen": "6.36 inch OLED 1.5K LTPO 120Hz 3000 nits",
    "battery": "4610 mAh, Sạc nhanh 90W",
    "camera": "3 camera Leica Summilux 50MP",
    "description": "Xiaomi 14 thiết kế nhỏ gọn cầm nắm hoàn hảo, ống kính quang học Leica Summilux thu sáng đỉnh cao và sạc nhanh 90W.",
    "featured": 1,
    "created_at": "2026-07-01 00:00:00"
  },
  {
    "id": 48,
    "name": "Xiaomi Redmi Note 13 Pro+ 5G 8GB/256GB",
    "slug": "xiaomi-redmi-note-13-pro-plus",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 8990000,
    "original_price": 10990000,
    "stock": 45,
    "rating": 4.82,
    "review_count": 135,
    "view_count": 1600,
    "image": "https://images.unsplash.com/photo-1567581935884-3349723552ca?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1567581935884-3349723552ca?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "256GB",
    "chipset": "MediaTek Dimensity 7200-Ultra 4nm",
    "screen": "6.67 inch AMOLED 1.5K 120Hz cong",
    "battery": "5000 mAh, Sạc siêu tốc 120W",
    "camera": "200MP OIS Siêu nét + 8MP + 2MP",
    "description": "Redmi Note 13 Pro+ bứt phá phân khúc với camera 200MP, kháng nước bụi IP68 và sạc đầy 100% pin chỉ 19 phút.",
    "featured": 0,
    "created_at": "2026-05-18 00:00:00"
  },
  {
    "id": 49,
    "name": "ASUS ROG Phone 8 12GB/256GB Phantom Black",
    "slug": "asus-rog-phone-8",
    "category_id": 1,
    "category_name": "Điện thoại",
    "brand_id": 4,
    "brand_name": "ASUS",
    "price": 21990000,
    "original_price": 24990000,
    "stock": 15,
    "rating": 4.9,
    "review_count": 48,
    "view_count": 820,
    "image": "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "12GB",
    "rom": "256GB",
    "chipset": "Snapdragon 8 Gen 3",
    "screen": "6.78 inch FHD+ AMOLED 165Hz LTPO",
    "battery": "5500 mAh, Sạc 65W HyperCharge",
    "camera": "50MP Sony IMX890 chống rung Gimbal",
    "description": "ASUS ROG Phone 8 mỏng nhẹ đa dụng, chip Snapdragon 8 Gen 3 cân mọi game mobile nặng nhất cùng hệ thống tản nhiệt tiên tiến.",
    "featured": 0,
    "created_at": "2026-06-10 00:00:00"
  },
  {
    "id": 50,
    "name": "MacBook Air 15 M3 16GB/512GB Starlight",
    "slug": "macbook-air-15-m3",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 37990000,
    "original_price": 40990000,
    "stock": 18,
    "rating": 4.92,
    "review_count": 53,
    "view_count": 1100,
    "image": "https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "16GB Unified",
    "rom": "512GB SSD",
    "chipset": "Apple M3 (8 CPU, 10 GPU)",
    "screen": "15.3 inch Liquid Retina 500 nits",
    "battery": "66.5Wh, Pin 18 giờ",
    "camera": "1080p FaceTime HD",
    "description": "MacBook Air 15 inch M3 không gian hiển thị rộng rãi, vỏ nhôm nguyên khối siêu mỏng chỉ 11.5mm và âm thanh 6 loa ấn tượng.",
    "featured": 1,
    "created_at": "2026-06-15 00:00:00"
  },
  {
    "id": 51,
    "name": "Laptop ASUS Zenbook Duo OLED UX8406 2 Màn Hình",
    "slug": "asus-zenbook-duo-oled-ux8406",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 4,
    "brand_name": "ASUS",
    "price": 47990000,
    "original_price": 52990000,
    "stock": 10,
    "rating": 4.94,
    "review_count": 36,
    "view_count": 950,
    "image": "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB LPDDR5X",
    "rom": "1TB PCIe 4.0 SSD",
    "chipset": "Intel Core Ultra 9 185H NPU AI",
    "screen": "Kép 14.0 inch 3K OLED 120Hz cảm ứng",
    "battery": "75Wh, Sạc nhanh Type-C",
    "camera": "FHD IR Windows Hello",
    "description": "Zenbook Duo trang bị 2 màn hình cảm ứng OLED 3K 120Hz mở rộng tầm nhìn, bàn phím Bluetooth tháo rời tiện lợi.",
    "featured": 1,
    "created_at": "2026-07-12 00:00:00"
  },
  {
    "id": 52,
    "name": "Laptop Gaming ASUS ROG Strix SCAR 16 RTX 4080",
    "slug": "asus-rog-strix-scar-16",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 4,
    "brand_name": "ASUS",
    "price": 79990000,
    "original_price": 84990000,
    "stock": 6,
    "rating": 4.97,
    "review_count": 29,
    "view_count": 780,
    "image": "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB DDR5 5600MHz",
    "rom": "2TB PCIe 4.0 SSD",
    "chipset": "Intel Core i9-14900HX",
    "screen": "16.0 inch 2.5K Mini LED 240Hz ROG Nebula HDR",
    "battery": "90Wh, Sạc 330W",
    "camera": "720p HD Camera",
    "description": "Quái vật chiến game ROG Strix SCAR 16 với màn hình Mini LED 240Hz, card RTX 4080 175W max công suất và tản nhiệt 3 quạt kim loại lỏng.",
    "featured": 0,
    "created_at": "2026-08-01 00:00:00"
  },
  {
    "id": 53,
    "name": "Dell Latitude 7440 Core i7-1365U Doanh Nghiệp",
    "slug": "dell-latitude-7440",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 6,
    "brand_name": "Dell",
    "price": 28990000,
    "original_price": 31990000,
    "stock": 15,
    "rating": 4.8,
    "review_count": 41,
    "view_count": 670,
    "image": "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "16GB LPDDR5",
    "rom": "512GB NVMe SSD",
    "chipset": "Intel Core i7-1365U vPro",
    "screen": "14.0 inch FHD+ IPS 16:10 400 nits ComfortView+",
    "battery": "57Wh ExpressCharge",
    "camera": "FHD IR Camera che cơ học",
    "description": "Dell Latitude 7440 chuẩn văn phòng cao cấp, độ bền hợp kim magie siêu nhẹ và bảo mật phần cứng Intel vPro an toàn.",
    "featured": 0,
    "created_at": "2026-05-25 00:00:00"
  },
  {
    "id": 54,
    "name": "Lenovo ThinkPad X1 Carbon Gen 12 Core Ultra 7",
    "slug": "lenovo-thinkpad-x1-carbon-gen-12",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 9,
    "brand_name": "Lenovo",
    "price": 49990000,
    "original_price": 54990000,
    "stock": 12,
    "rating": 4.96,
    "review_count": 38,
    "view_count": 890,
    "image": "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB LPDDR5X",
    "rom": "1TB PCIe Gen4 SSD",
    "chipset": "Intel Core Ultra 7 155H NPU",
    "screen": "14.0 inch 2.8K OLED 120Hz 100% DCI-P3",
    "battery": "57Wh, Pin 16 giờ",
    "camera": "8MP FHD IR Communications Bar",
    "description": "Biểu tượng doanh nhân ThinkPad X1 Carbon Gen 12 sợi carbon siêu nhẹ 1.09kg, bàn phím gõ đỉnh cao và màn hình OLED 120Hz.",
    "featured": 1,
    "created_at": "2026-06-18 00:00:00"
  },
  {
    "id": 55,
    "name": "Lenovo Legion Pro 5 16IRX9 i7-14700HX RTX 4060",
    "slug": "lenovo-legion-pro-5",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 9,
    "brand_name": "Lenovo",
    "price": 38990000,
    "original_price": 42990000,
    "stock": 20,
    "rating": 4.88,
    "review_count": 57,
    "view_count": 1040,
    "image": "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB DDR5 5600MHz",
    "rom": "1TB SSD M.2 NVMe",
    "chipset": "Intel Core i7-14700HX 20 nhân",
    "screen": "16.0 inch WQXGA IPS 240Hz 500 nits 100% sRGB",
    "battery": "80Wh, Sạc nhanh 300W",
    "camera": "1080p FHD",
    "description": "Laptop Gaming Legion Pro 5 hệ thống tản nhiệt Legion Coldfront 5.0, chip AI Lenovo LA1 tối ưu FPS tự động khi chơi game.",
    "featured": 0,
    "created_at": "2026-07-20 00:00:00"
  },
  {
    "id": 56,
    "name": "Acer Swift Go 14 OLED Core Ultra 5 125H",
    "slug": "acer-swift-go-14-oled",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 10,
    "brand_name": "Acer",
    "price": 21490000,
    "original_price": 23990000,
    "stock": 25,
    "rating": 4.81,
    "review_count": 44,
    "view_count": 730,
    "image": "https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "16GB LPDDR5X",
    "rom": "512GB PCIe Gen4 SSD",
    "chipset": "Intel Core Ultra 5 125H tích hợp Intel Arc",
    "screen": "14.0 inch 2.8K OLED 90Hz 100% DCI-P3 500 nits",
    "battery": "65Wh, Sạc 100W Type-C",
    "camera": "1440p QHD Webcam AI khử nhiễu",
    "description": "Acer Swift Go 14 chuẩn Intel Evo màn hình 2.8K OLED rực rỡ, webcam QHD sắc nét cùng trọng lượng chỉ 1.3kg.",
    "featured": 0,
    "created_at": "2026-05-30 00:00:00"
  },
  {
    "id": 57,
    "name": "Acer Predator Helios 16 i9-14900HX RTX 4070",
    "slug": "acer-predator-helios-16",
    "category_id": 2,
    "category_name": "Laptop",
    "brand_id": 10,
    "brand_name": "Acer",
    "price": 56990000,
    "original_price": 61990000,
    "stock": 8,
    "rating": 4.93,
    "review_count": 31,
    "view_count": 650,
    "image": "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "32GB DDR5 5600MHz",
    "rom": "1TB PCIe Gen4 SSD",
    "chipset": "Intel Core i9-14900HX",
    "screen": "16.0 inch WQXGA IPS 240Hz 500 nits G-Sync",
    "battery": "90Wh, Sạc 330W",
    "camera": "1080p FHD",
    "description": "Predator Helios 16 thiết kế đậm chất phi thuyền vũ trụ, quạt kim loại 3D AeroBlade thế hệ 5 tản nhiệt cực êm và RTX 4070 uy lực.",
    "featured": 0,
    "created_at": "2026-08-14 00:00:00"
  },
  {
    "id": 58,
    "name": "iPad Pro 13 inch M4 OLED 256GB Wi-Fi",
    "slug": "ipad-pro-13-m4-oled",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 36990000,
    "original_price": 38990000,
    "stock": 15,
    "rating": 4.98,
    "review_count": 65,
    "view_count": 1200,
    "image": "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "256GB",
    "chipset": "Apple M4 chip 9-core CPU",
    "screen": "13.0 inch Ultra Retina XDR Tandem OLED 120Hz",
    "battery": "38.99 Wh, Pin 10 giờ",
    "camera": "12MP Wide LiDAR + 12MP Landscape Center Stage",
    "description": "iPad Pro 13 inch siêu mỏng 5.1mm mỏng nhất lịch sử Apple, màn hình Tandem OLED rực rỡ và sức mạnh chip M4 tối tân.",
    "featured": 1,
    "created_at": "2026-06-25 00:00:00"
  },
  {
    "id": 59,
    "name": "iPad mini 7 128GB Wi-Fi Apple Intelligence",
    "slug": "ipad-mini-7-128gb",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 13990000,
    "original_price": 14990000,
    "stock": 25,
    "rating": 4.86,
    "review_count": 72,
    "view_count": 980,
    "image": "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "128GB",
    "chipset": "Apple A17 Pro 3nm",
    "screen": "8.3 inch Liquid Retina True Tone",
    "battery": "19.3 Wh, Pin 10 giờ",
    "camera": "12MP Wide trước & sau",
    "description": "iPad mini 7 nhỏ gọn trong lòng bàn tay, chip A17 Pro mạnh mẽ hỗ trợ Apple Intelligence và bút Apple Pencil Pro.",
    "featured": 0,
    "created_at": "2026-09-10 00:00:00"
  },
  {
    "id": 60,
    "name": "Samsung Galaxy Tab S9 11 inch 8GB/128GB S-Pen",
    "slug": "samsung-galaxy-tab-s9",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 16990000,
    "original_price": 19990000,
    "stock": 20,
    "rating": 4.88,
    "review_count": 59,
    "view_count": 850,
    "image": "https://images.unsplash.com/photo-1585792180666-f7547c6a28c5?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1585792180666-f7547c6a28c5?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "128GB",
    "chipset": "Snapdragon 8 Gen 2 for Galaxy",
    "screen": "11.0 inch Dynamic AMOLED 2X 120Hz",
    "battery": "8400 mAh, Sạc nhanh 45W",
    "camera": "13MP sau, 12MP trước siêu rộng",
    "description": "Galaxy Tab S9 chuẩn chống nước kháng bụi IP68, màn hình Dynamic AMOLED 2X rực rỡ và bút S-Pen đi kèm thỏa sức sáng tạo.",
    "featured": 0,
    "created_at": "2026-07-05 00:00:00"
  },
  {
    "id": 61,
    "name": "Samsung Galaxy Tab A9+ 5G 4GB/64GB",
    "slug": "samsung-galaxy-tab-a9-plus",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 5490000,
    "original_price": 6490000,
    "stock": 35,
    "rating": 4.75,
    "review_count": 98,
    "view_count": 1100,
    "image": "https://images.unsplash.com/photo-1527698266440-12104e498b76?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1527698266440-12104e498b76?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "4GB",
    "rom": "64GB",
    "chipset": "Snapdragon 695 5G",
    "screen": "11.0 inch TFT LCD 90Hz",
    "battery": "7040 mAh, Sạc 15W",
    "camera": "8MP sau, 5MP trước",
    "description": "Galaxy Tab A9+ kết nối 5G tốc độ cao, hệ thống 4 loa Dolby Atmos sống động và màn hình 90Hz giải trí xem phim tuyệt vời.",
    "featured": 0,
    "created_at": "2026-05-10 00:00:00"
  },
  {
    "id": 62,
    "name": "Xiaomi Pad 6 8GB/256GB Gravity Gray",
    "slug": "xiaomi-pad-6-8gb-256gb",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 7990000,
    "original_price": 8990000,
    "stock": 30,
    "rating": 4.83,
    "review_count": 112,
    "view_count": 1350,
    "image": "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1561154464-82e9adf32764?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB",
    "rom": "256GB",
    "chipset": "Snapdragon 870",
    "screen": "11.0 inch 2.8K 144Hz WQHD+ 10-bit",
    "battery": "8840 mAh, Sạc 33W",
    "camera": "13MP sau, 8MP trước",
    "description": "Xiaomi Pad 6 thiết kế nhôm nguyên khối siêu mỏng 6.51mm, màn hình 2.8K 144Hz siêu mượt và pin bền bỉ gần 2 ngày sử dụng.",
    "featured": 0,
    "created_at": "2026-06-12 00:00:00"
  },
  {
    "id": 63,
    "name": "Lenovo Tab P12 Pro 12.6 inch AMOLED 120Hz",
    "slug": "lenovo-tab-p12-pro",
    "category_id": 3,
    "category_name": "Tablet",
    "brand_id": 9,
    "brand_name": "Lenovo",
    "price": 14490000,
    "original_price": 16990000,
    "stock": 14,
    "rating": 4.82,
    "review_count": 35,
    "view_count": 620,
    "image": "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "8GB LPDDR5",
    "rom": "256GB UFS 3.1",
    "chipset": "Snapdragon 870",
    "screen": "12.6 inch 2K AMOLED 120Hz HDR10+ Dolby Vision",
    "battery": "10200 mAh, Sạc nhanh 45W",
    "camera": "Kép 13MP + 5MP góc rộng, Kép 8MP trước",
    "description": "Lenovo Tab P12 Pro màn hình AMOLED 12.6 inch rực rỡ, 4 loa JBL chuyên nghiệp và bút cảm ứng Lenovo Precision Pen 3.",
    "featured": 0,
    "created_at": "2026-07-28 00:00:00"
  },
  {
    "id": 64,
    "name": "Tai nghe Apple AirPods 4 Chống Ồn Chủ Động ANC",
    "slug": "apple-airpods-4-anc",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 4490000,
    "original_price": 4790000,
    "stock": 35,
    "rating": 4.89,
    "review_count": 88,
    "view_count": 1400,
    "image": "https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Apple H2 chip",
    "screen": "Driver Apple độ méo tiếng siêu thấp",
    "battery": "Pin 5 giờ (30 giờ kèm hộp), Sạc không dây/USB-C",
    "camera": "Chống ồn chủ động ANC trên thiết kế tai mở",
    "description": "AirPods 4 lần đầu tiên mang chống ồn chủ động lên thiết kế mở open-ear, chip H2 lọc giọng nói trong trẻo và âm thanh không gian cá nhân hóa.",
    "featured": 1,
    "created_at": "2026-09-22 00:00:00"
  },
  {
    "id": 65,
    "name": "Loa Bluetooth Marshall Stanmore III 80W Chính Hãng",
    "slug": "marshall-stanmore-iii",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 11,
    "brand_name": "Marshall",
    "price": 8990000,
    "original_price": 9990000,
    "stock": 15,
    "rating": 4.95,
    "review_count": 76,
    "view_count": 1250,
    "image": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Bộ khuếch tán âm Class D 80W (Woofer 50W + 2 Tweeter 15W)",
    "screen": "Thiết kế da sần Vintage & Núm kim loại đồng thau",
    "battery": "Nguồn điện trực tiếp 100-240V",
    "camera": "Bluetooth 5.2 LE Audio + Cổng AUX 3.5mm/RCA",
    "description": "Biểu tượng loa để bàn Marshall Stanmore III không gian âm trường mở rộng, bass nội lực uy lực và ngoại hình cổ điển đậm chất rock.",
    "featured": 1,
    "created_at": "2026-06-30 00:00:00"
  },
  {
    "id": 66,
    "name": "Loa Bluetooth Marshall Emberton II Kháng Nước IP67",
    "slug": "marshall-emberton-ii",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 11,
    "brand_name": "Marshall",
    "price": 3990000,
    "original_price": 4590000,
    "stock": 25,
    "rating": 4.91,
    "review_count": 64,
    "view_count": 890,
    "image": "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Âm thanh đa hướng True Stereophonic 360 độ",
    "screen": "2 củ loa toàn dải 2 inch 10W + 2 màng cộng hưởng thụ động",
    "battery": "Pin khủng 30 giờ, Sạc nhanh 20 phút nghe 4 giờ",
    "camera": "Chuẩn chống bụi nước IP67 siêu bền",
    "description": "Loa di động Marshall Emberton II âm thanh 360 độ True Stereophonic đặc trưng, pin 30 giờ và tính năng Stack Mode kết nối nhiều loa cùng lúc.",
    "featured": 0,
    "created_at": "2026-05-15 00:00:00"
  },
  {
    "id": 67,
    "name": "Loa Bluetooth JBL Charge 5 Công Suất 40W Pin 20h",
    "slug": "jbl-charge-5",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 12,
    "brand_name": "JBL",
    "price": 3490000,
    "original_price": 3990000,
    "stock": 30,
    "rating": 4.88,
    "review_count": 105,
    "view_count": 1320,
    "image": "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "JBL Original Pro Sound công nghệ Bass Radiator kép",
    "screen": "Racetrack driver 52x90mm + Tweeter 20mm riêng biệt",
    "battery": "Pin 7500 mAh chơi nhạc 20 giờ kiêm sạc dự phòng điện thoại",
    "camera": "Kháng nước chống bụi IP67 chuẩn dã ngoại",
    "description": "Loa di động tiệc tùng JBL Charge 5 chất âm JBL Pro Sound bùng nổ, thời lượng pin 20 giờ và khả năng sạc ngược cho smartphone.",
    "featured": 1,
    "created_at": "2026-07-18 00:00:00"
  },
  {
    "id": 68,
    "name": "Loa Bluetooth JBL Flip 6 Nhỏ Gọn Kháng Nước",
    "slug": "jbl-flip-6",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 12,
    "brand_name": "JBL",
    "price": 2490000,
    "original_price": 2990000,
    "stock": 35,
    "rating": 4.85,
    "review_count": 82,
    "view_count": 1020,
    "image": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Hệ thống loa 2 chiều 30W (Woofer 20W + Tweeter 10W)",
    "screen": "Màng loa hình đường đua + 2 màng thụ động tối ưu bass",
    "battery": "Pin 12 giờ, Sạc Type-C bảo vệ an toàn",
    "camera": "Kháng nước và chống bụi hoàn hảo IP67",
    "description": "JBL Flip 6 thiết kế hình ống năng động cầm vừa vặn, âm thanh 2 chiều trong trẻo và công nghệ PartyBoost ghép nối nhiều loa.",
    "featured": 0,
    "created_at": "2026-06-05 00:00:00"
  },
  {
    "id": 69,
    "name": "Tai nghe Sony ULT WEAR Chống Ồn Bass Siêu Trầm",
    "slug": "sony-ult-wear",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 5,
    "brand_name": "Sony",
    "price": 3990000,
    "original_price": 4490000,
    "stock": 25,
    "rating": 4.84,
    "review_count": 47,
    "view_count": 760,
    "image": "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Sony Integrated Processor V1 tối ưu chống ồn",
    "screen": "Củ loa 40mm chuyên biệt dòng ULT Power Sound",
    "battery": "Pin 30 giờ (bật ANC), Sạc nhanh 3 phút nghe 90 phút",
    "camera": "Nút bấm ULT kích hoạt 2 chế độ Extra Bass uy lực",
    "description": "Sony ULT WEAR sinh ra cho tín đồ nhạc sôi động EDM/Hip-hop với nút bấm ULT tăng cường âm trầm rung chuyển màng nhĩ và chống ồn V1 xuất sắc.",
    "featured": 0,
    "created_at": "2026-05-22 00:00:00"
  },
  {
    "id": 70,
    "name": "Tai nghe Soundcore Liberty 4 NC Chống Ồn 98.5%",
    "slug": "soundcore-liberty-4-nc",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 7,
    "brand_name": "Anker",
    "price": 1890000,
    "original_price": 2390000,
    "stock": 45,
    "rating": 4.87,
    "review_count": 118,
    "view_count": 1420,
    "image": "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Chuẩn Hi-Res Wireless Audio với codec LDAC",
    "screen": "Driver 11mm tùy chỉnh âm học độ nhạy cao",
    "battery": "Pin 10 giờ (50 giờ kèm hộp), Sạc không dây Qi",
    "camera": "Chống ồn thích ứng Adaptive ANC 2.0 giảm 98.5% tạp âm",
    "description": "Tai nghe Soundcore Liberty 4 NC chống ồn thích ứng chuẩn SGS giảm đến 98.5% tiếng ồn, chứng nhận âm thanh Hi-Res Wireless LDAC đỉnh cao.",
    "featured": 0,
    "created_at": "2026-06-18 00:00:00"
  },
  {
    "id": 71,
    "name": "Loa Bluetooth Soundcore Motion X600 Spatial Audio 50W",
    "slug": "soundcore-motion-x600",
    "category_id": 4,
    "category_name": "Âm thanh",
    "brand_id": 7,
    "brand_name": "Anker",
    "price": 3890000,
    "original_price": 4590000,
    "stock": 20,
    "rating": 4.9,
    "review_count": 52,
    "view_count": 810,
    "image": "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1545454675-3531b543be5d?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Thuật toán âm thanh không gian Spatial Audio 5 kênh",
    "screen": "5 củ loa và 3 bộ khuếch đại độc lập công suất 50W",
    "battery": "Pin 6400 mAh nghe nhạc 12 giờ",
    "camera": "Vỏ nhôm phay xước cao cấp chuẩn kháng nước IPX7",
    "description": "Loa di động Anker Soundcore Motion X600 mang lại trải nghiệm âm thanh rạp hát tại gia với công nghệ Spatial Audio 5 driver và chuẩn Hi-Res LDAC.",
    "featured": 0,
    "created_at": "2026-07-25 00:00:00"
  },
  {
    "id": 72,
    "name": "Apple Watch Series 10 GPS 46mm Nhôm Jet Black",
    "slug": "apple-watch-series-10-46mm",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 11490000,
    "original_price": 12290000,
    "stock": 25,
    "rating": 4.93,
    "review_count": 62,
    "view_count": 1350,
    "image": "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": "64GB",
    "chipset": "Apple S10 SiP 64-bit lõi tứ Neural Engine",
    "screen": "OLED góc nhìn rộng sáng hơn 40% khi nhìn nghiêng",
    "battery": "Pin 18 giờ, Sạc siêu nhanh 80% chỉ trong 30 phút",
    "camera": "Cảm biến độ sâu, nhiệt độ nước, phát hiện ngưng thở khi ngủ",
    "description": "Apple Watch Series 10 thiết kế mỏng nhất từ trước đến nay (9.7mm), màn hình hiển thị lớn hơn cả bản Ultra và sạc cực nhanh.",
    "featured": 1,
    "created_at": "2026-09-28 00:00:00"
  },
  {
    "id": 73,
    "name": "Apple Watch SE 2024 GPS 44mm Viền Nhôm",
    "slug": "apple-watch-se-2024-44mm",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 6590000,
    "original_price": 7490000,
    "stock": 35,
    "rating": 4.82,
    "review_count": 95,
    "view_count": 1280,
    "image": "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": "32GB",
    "chipset": "Apple S8 SiP 64-bit",
    "screen": "Retina LTPO OLED 1000 nits kính Ion-X",
    "battery": "Pin 18 giờ chế độ sử dụng tiêu chuẩn",
    "camera": "Cảm biến nhịp tim quang học thế hệ 2 + Phát hiện té ngã",
    "description": "Apple Watch SE lựa chọn thông minh giá tốt nhất hệ sinh thái Apple, đầy đủ tính năng theo dõi vận động, nhịp tim và cứu hộ SOS.",
    "featured": 0,
    "created_at": "2026-06-15 00:00:00"
  },
  {
    "id": 74,
    "name": "Samsung Galaxy Watch7 44mm Bluetooth Kính Sapphire",
    "slug": "samsung-galaxy-watch7-44mm",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 7490000,
    "original_price": 8490000,
    "stock": 22,
    "rating": 4.87,
    "review_count": 51,
    "view_count": 920,
    "image": "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "2GB",
    "rom": "32GB",
    "chipset": "Exynos W1000 tiến trình 3nm 5 nhân",
    "screen": "1.5 inch Super AMOLED 2000 nits Sapphire Glass",
    "battery": "425 mAh, Sạc nhanh không dây WPC",
    "camera": "Cảm biến BioActive thế hệ mới + Huấn luyện viên giấc ngủ Galaxy AI",
    "description": "Galaxy Watch7 trang bị chip 3nm mạnh mẽ gấp 3 lần, định vị GPS tần số kép L1+L5 chuẩn xác từng mét và trợ lý sức khỏe Galaxy AI.",
    "featured": 1,
    "created_at": "2026-08-05 00:00:00"
  },
  {
    "id": 75,
    "name": "Samsung Galaxy Watch FE 40mm Dây Thể Thao",
    "slug": "samsung-galaxy-watch-fe",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 2,
    "brand_name": "Samsung",
    "price": 4490000,
    "original_price": 4990000,
    "stock": 30,
    "rating": 4.79,
    "review_count": 42,
    "view_count": 780,
    "image": "https://images.unsplash.com/photo-1510017803434-a899398421b3?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1510017803434-a899398421b3?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "1.5GB",
    "rom": "16GB",
    "chipset": "Exynos W920 Dual-Core 1.18GHz",
    "screen": "1.2 inch Super AMOLED kính Sapphire chống trầy",
    "battery": "247 mAh, Sạc không dây từ tính",
    "camera": "Cảm biến nhịp tim, đo nồng độ oxy trong máu SpO2, ECG",
    "description": "Galaxy Watch FE phiên bản Fan Edition tinh gọn, kính Sapphire cao cấp và hơn 100 chế độ tập luyện thể dục thể thao chuyên biệt.",
    "featured": 0,
    "created_at": "2026-06-28 00:00:00"
  },
  {
    "id": 76,
    "name": "Xiaomi Watch 2 Pro 4G LTE Snapdragon W5+ Gen 1",
    "slug": "xiaomi-watch-2-pro",
    "category_id": 5,
    "category_name": "Smartwatch",
    "brand_id": 3,
    "brand_name": "Xiaomi",
    "price": 5990000,
    "original_price": 6990000,
    "stock": 18,
    "rating": 4.83,
    "review_count": 39,
    "view_count": 690,
    "image": "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": "2GB",
    "rom": "32GB",
    "chipset": "Snapdragon W5+ Gen 1 4nm tiết kiệm điện",
    "screen": "1.43 inch AMOLED 466x466 600 nits khung thép không gỉ",
    "battery": "495 mAh, Thời lượng pin 55 giờ ở chế độ LTE",
    "camera": "Hệ điều hành Google Wear OS đầy đủ kho ứng dụng Play Store",
    "description": "Xiaomi Watch 2 Pro kết nối eSIM 4G độc lập nghe gọi không cần điện thoại, Wear OS mượt mà và khung vỏ thép không gỉ sang trọng.",
    "featured": 0,
    "created_at": "2026-07-10 00:00:00"
  },
  {
    "id": 77,
    "name": "Củ sạc nhanh Anker 30W GaN 511 Nano 3 A2147",
    "slug": "cu-sac-anker-30w-gan-nano-3",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 7,
    "brand_name": "Anker",
    "price": 390000,
    "original_price": 520000,
    "stock": 80,
    "rating": 4.94,
    "review_count": 260,
    "view_count": 2450,
    "image": "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Chíp bán dẫn GaN III nhỏ hơn 70% củ sạc 30W thường",
    "screen": "1 cổng USB-C Power Delivery 3.0 & PPS 30W",
    "battery": "ActiveShield 2.0 đo nhiệt độ 3 triệu lần/ngày",
    "camera": "Sạc siêu tốc iPhone 16 từ 0-50% trong 25 phút",
    "description": "Củ sạc siêu tí hon Anker Nano 3 công suất 30W hỗ trợ sạc nhanh tối đa cho iPhone 16 Pro Max, iPad Air và MacBook Air.",
    "featured": 1,
    "created_at": "2026-08-12 00:00:00"
  },
  {
    "id": 78,
    "name": "Pin dự phòng Anker MagGo 10.000mAh Qi2 15W Màn Hình LED",
    "slug": "pin-du-phong-anker-maggo-10000mah",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 7,
    "brand_name": "Anker",
    "price": 1490000,
    "original_price": 1890000,
    "stock": 40,
    "rating": 4.91,
    "review_count": 145,
    "view_count": 1680,
    "image": "https://images.unsplash.com/photo-1609592424109-dd9892f1b177?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1609592424109-dd9892f1b177?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Chuẩn sạc không dây thế hệ mới Qi2 Certified 15W",
    "screen": "Màn hình thông minh hiển thị chính xác % pin và thời gian sạc",
    "battery": "Dung lượng 10.000 mAh, Cổng Type-C vào/ra công suất 27W",
    "camera": "Hút nam châm từ tính MagSafe cực mạnh kiêm chân dựng máy gập",
    "description": "Pin sạc từ tính Anker MagGo chuẩn Qi2 15W sạc nhanh không dây gấp đôi MagSafe thường, màn hình thông minh hiển thị thời gian pin còn lại.",
    "featured": 1,
    "created_at": "2026-08-20 00:00:00"
  },
  {
    "id": 79,
    "name": "Cáp sạc Anker Bio-Braided Type-C to Type-C 240W 1.8m",
    "slug": "cap-sac-anker-type-c-240w",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 7,
    "brand_name": "Anker",
    "price": 290000,
    "original_price": 390000,
    "stock": 90,
    "rating": 4.89,
    "review_count": 198,
    "view_count": 1900,
    "image": "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "E-Marker chip tích hợp kiểm soát dòng điện an toàn",
    "screen": "Dây bọc dù sinh học uốn gập 20.000 lần không đứt gãy",
    "battery": "Hỗ trợ công suất truyền tải điện Power Delivery tối đa 240W",
    "camera": "Tương thích mọi thiết bị từ iPhone 16 đến MacBook Pro 16 inch",
    "description": "Dây cáp bọc dù thân thiện môi trường Anker Bio-Braided chịu tải đến 240W, sạc tốc độ tối đa cho mọi laptop và smartphone hiện đại.",
    "featured": 0,
    "created_at": "2026-07-15 00:00:00"
  },
  {
    "id": 80,
    "name": "Bàn phím cơ Logitech G PRO X TKL LIGHTSPEED Wireless",
    "slug": "logitech-g-pro-x-tkl",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 8,
    "brand_name": "Logitech",
    "price": 4190000,
    "original_price": 4690000,
    "stock": 20,
    "rating": 4.95,
    "review_count": 78,
    "view_count": 940,
    "image": "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Công nghệ không dây Lightspeed độ trễ cực thấp 1ms",
    "screen": "Đèn LED RGB LIGHTSYNC 16.8 triệu màu từng phím",
    "battery": "Pin sạc USB-C dùng liên tục 50 giờ chơi game",
    "camera": "Switch cơ học GX Brown Tactile độ phản hồi xúc giác hoàn hảo",
    "description": "Bàn phím cơ thi đấu Esports chuyên nghiệp Logitech G PRO X TKL thiết kế Tenkeyless nhỏ gọn, keycap PBT doubleshot bền bỉ và kết nối Lightspeed 1ms.",
    "featured": 0,
    "created_at": "2026-08-01 00:00:00"
  },
  {
    "id": 81,
    "name": "Chuột Gaming không dây Logitech G Pro X Superlight 2",
    "slug": "logitech-g-pro-x-superlight-2",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 8,
    "brand_name": "Logitech",
    "price": 3490000,
    "original_price": 3990000,
    "stock": 25,
    "rating": 4.97,
    "review_count": 130,
    "view_count": 1550,
    "image": "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Cảm biến HERO 2 độ phân giải 32.000 DPI, theo dõi 500+ IPS",
    "screen": "Switch quang học - cơ học lai LIGHTFORCE phản xạ tức thì",
    "battery": "Pin khủng 95 giờ sử dụng, cổng sạc USB-C hiện đại",
    "camera": "Trọng lượng siêu nhẹ chỉ 60g không cần đục lỗ tổ ong",
    "description": "Huyền thoại chuột gaming FPS thế giới Superlight 2 với trọng lượng siêu nhẹ 60g, switch hybrid LIGHTFORCE và tần số lấy mẫu Polling Rate 4000Hz.",
    "featured": 1,
    "created_at": "2026-08-18 00:00:00"
  },
  {
    "id": 82,
    "name": "Bút cảm ứng Apple Pencil Pro Dành Cho iPad M4 / M2",
    "slug": "apple-pencil-pro",
    "category_id": 6,
    "category_name": "Phụ kiện chính hãng",
    "brand_id": 1,
    "brand_name": "Apple",
    "price": 3290000,
    "original_price": 3490000,
    "stock": 40,
    "rating": 4.96,
    "review_count": 115,
    "view_count": 1380,
    "image": "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=600&auto=format&fit=crop&q=80",
    "images": [
      "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=800&auto=format&fit=crop&q=80"
    ],
    "ram": null,
    "rom": null,
    "chipset": "Cảm biến con quay hồi chuyển Barrel Roll xoay thân bút",
    "screen": "Cảm ứng lực bóp Squeeze mở bảng công cụ tức thì",
    "battery": "Sạc không dây từ tính gắn cạnh iPad, hỗ trợ Find My",
    "camera": "Phản hồi xúc giác Haptic Feedback rung phản hồi khi thao tác",
    "description": "Apple Pencil Pro đỉnh cao sáng tạo với thao tác bóp Squeeze, xoay thân bút đổi nét cọ Barrel Roll và tìm kiếm vị trí Find My tiện lợi.",
    "featured": 0,
    "created_at": "2026-06-25 00:00:00"
  }
];

const DEFAULT_CATEGORIES = [
  { id: 1, name: 'Điện thoại', icon: '📱', slug: 'dien-thoai', product_count: 17 },
  { id: 2, name: 'Laptop', icon: '💻', slug: 'laptop', product_count: 18 },
  { id: 3, name: 'Tablet', icon: '📋', slug: 'tablet', product_count: 12 },
  { id: 4, name: 'Âm thanh', icon: '🎧', slug: 'am-thanh', product_count: 16 },
  { id: 5, name: 'Smartwatch', icon: '⌚', slug: 'smartwatch', product_count: 9 },
  { id: 6, name: 'Phụ kiện chính hãng', icon: '🔌', slug: 'phu-kien', product_count: 10 }
];

const DEFAULT_BRANDS = [
  { id: 1, name: 'Apple', logo: '' },
  { id: 2, name: 'Samsung', logo: 'Samsung' },
  { id: 3, name: 'Xiaomi', logo: 'Xiaomi' },
  { id: 4, name: 'ASUS', logo: 'ASUS' },
  { id: 5, name: 'Sony', logo: 'Sony' },
  { id: 6, name: 'Dell', logo: 'Dell' },
  { id: 7, name: 'Anker', logo: 'Anker' },
  { id: 8, name: 'Logitech', logo: 'Logitech' },
  { id: 9, name: 'Lenovo', logo: 'Lenovo' },
  { id: 10, name: 'Acer', logo: 'Acer' },
  { id: 11, name: 'Marshall', logo: 'Marshall' },
  { id: 12, name: 'JBL', logo: 'JBL' }
];

const DEFAULT_COUPONS = [
  { id: 1, code: 'TECH10', discount_type: 'percent', value: 10, min_order: 1000000, max_discount: 500000, description: 'Giảm 10% tối đa 500k cho đơn từ 1 triệu' },
  { id: 2, code: 'VIETQR50', discount_type: 'amount', value: 50000, min_order: 500000, max_discount: 50000, description: 'Giảm 50.000đ khi thanh toán VietQR' },
  { id: 3, code: 'GIAM100K', discount_type: 'amount', value: 100000, min_order: 2000000, max_discount: 100000, description: 'Giảm ngay 100k cho đơn từ 2 triệu' }
];

const DEFAULT_REVIEWS = [
  { id: 1, product_id: 1, user_name: 'Nguyễn Văn Nam', rating: 5, comment: 'Hàng chuẩn chính hãng CellphoneS, giao nhanh trong 2h tại TP.HCM, đóng gói cẩn thận 10/10!', created_at: '2026-10-18' },
  { id: 2, product_id: 1, user_name: 'Trần Thị Mai', rating: 5, comment: 'Máy dùng rất mượt mà, camera nét đỉnh chóp. Nhân viên tư vấn nhiệt tình.', created_at: '2026-10-15' },
  { id: 3, product_id: 2, user_name: 'Lê Hoàng Minh', rating: 4, comment: 'Sản phẩm tốt trong tầm giá, quét VietQR thanh toán 5s là xong luôn.', created_at: '2026-10-12' }
];

// Helper Functions for Local Storage DB
function getStoredProducts() {
  const p = localStorage.getItem(API_CONFIG.storageKeys.products);
  if (!p) {
    localStorage.setItem(API_CONFIG.storageKeys.products, JSON.stringify(DEFAULT_PRODUCTS));
    return DEFAULT_PRODUCTS;
  }
  try {
    const list = JSON.parse(p);
    if (!Array.isArray(list) || list.length < DEFAULT_PRODUCTS.length) {
      localStorage.setItem(API_CONFIG.storageKeys.products, JSON.stringify(DEFAULT_PRODUCTS));
      return DEFAULT_PRODUCTS;
    }
    return list;
  } catch (e) {
    return DEFAULT_PRODUCTS;
  }
}

function saveStoredProducts(products) {
  localStorage.setItem(API_CONFIG.storageKeys.products, JSON.stringify(products));
}

function getStoredReviews() {
  const r = localStorage.getItem(API_CONFIG.storageKeys.reviews);
  if (!r) {
    localStorage.setItem(API_CONFIG.storageKeys.reviews, JSON.stringify(DEFAULT_REVIEWS));
    return DEFAULT_REVIEWS;
  }
  try {
    return JSON.parse(r);
  } catch (e) {
    return DEFAULT_REVIEWS;
  }
}

function saveStoredReviews(reviews) {
  localStorage.setItem(API_CONFIG.storageKeys.reviews, JSON.stringify(reviews));
}

function getStoredWishlistIds() {
  const w = localStorage.getItem(API_CONFIG.storageKeys.wishlist);
  if (!w) {
    const initial = [1, 6];
    localStorage.setItem(API_CONFIG.storageKeys.wishlist, JSON.stringify(initial));
    return initial;
  }
  try {
    return JSON.parse(w);
  } catch (e) {
    return [1, 6];
  }
}

function saveStoredWishlistIds(ids) {
  localStorage.setItem(API_CONFIG.storageKeys.wishlist, JSON.stringify(ids));
}

// ==========================================
// CORE API CLIENT
// ==========================================
class ApiClient {
  constructor() {
    this.baseUrl = API_CONFIG.baseUrl;
  }

  // Token Management
  getAccessToken() {
    return localStorage.getItem(API_CONFIG.storageKeys.accessToken);
  }

  getRefreshToken() {
    return localStorage.getItem(API_CONFIG.storageKeys.refreshToken);
  }

  getCurrentUser() {
    const raw = localStorage.getItem(API_CONFIG.storageKeys.user);
    if (!raw) return null;
    try {
      return JSON.parse(raw);
    } catch (e) {
      return null;
    }
  }

  setAuthTokens(accessToken, refreshToken, user) {
    if (accessToken) localStorage.setItem(API_CONFIG.storageKeys.accessToken, accessToken);
    if (refreshToken) localStorage.setItem(API_CONFIG.storageKeys.refreshToken, refreshToken);
    if (user) localStorage.setItem(API_CONFIG.storageKeys.user, JSON.stringify(user));
  }

  clearAuth() {
    localStorage.removeItem(API_CONFIG.storageKeys.accessToken);
    localStorage.removeItem(API_CONFIG.storageKeys.refreshToken);
    localStorage.removeItem(API_CONFIG.storageKeys.user);
    this.updateHeaderCartBadge();
  }

  getCartKey() {
    const user = this.getCurrentUser();
    return (user && user.id) ? `techstore_cart_user_${user.id}` : 'techstore_cart_guest';
  }

  isLoggedIn() {
    return !!this.getAccessToken();
  }

  isAdmin() {
    const user = this.getCurrentUser();
    return user && (user.role === 'admin' || user.email?.includes('admin'));
  }

  // Core Request Wrapper
  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    const token = this.getAccessToken();

    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      ...(options.headers || {})
    };

    if (options.body && options.body instanceof FormData) {
      delete headers['Content-Type']; // Let browser set multipart boundary
    }

    try {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), API_CONFIG.timeout);

      const response = await fetch(url, {
        ...options,
        headers,
        credentials: 'same-origin',
        signal: controller.signal
      });
      clearTimeout(timeoutId);

      // Handle 401 Unauthorized -> Attempt token refresh
      if (response.status === 401 && !options._retry && this.getRefreshToken()) {
        options._retry = true;
        const refreshed = await this.refreshToken();
        if (refreshed) {
          headers['Authorization'] = `Bearer ${this.getAccessToken()}`;
          return fetch(url, { ...options, headers }).then(r => r.json());
        }
      }

      if (response.status === 204) {
        return { success: true, message: 'Thành công (204 No Content)' };
      }

      const text = await response.text();
      if (!text || text.trim() === '') {
        return this.handleMockFallback(endpoint, options);
      }

      const json = JSON.parse(text);
      return json;
    } catch (err) {
      // If server is not responding, fallback to client-side smart mock
      return this.handleMockFallback(endpoint, options);
    }
  }

  async refreshToken() {
    const rf = this.getRefreshToken();
    try {
      const res = await fetch(`${this.baseUrl}/auth/refresh`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ refresh_token: rf || '' })
      });
      if (res.ok) {
        const data = await res.json();
        const payload = data.data || data;
        if (payload.access_token) {
          this.setAuthTokens(payload.access_token, payload.refresh_token || rf, payload.user || this.getCurrentUser());
          return true;
        }
      }
    } catch (e) {}
    this.clearAuth();
    return false;
  }

  // ==========================================
  // SMART MOCK FALLBACK (STANDALONE CAPABLE)
  // Covers 100% of the 37 APIs from Sheet 8
  // ==========================================
  handleMockFallback(endpoint, options) {
    const method = (options.method || 'GET').toUpperCase();
    const body = options.body && typeof options.body === 'string' ? JSON.parse(options.body) : (options.body || {});

    // 1-5 & 37. AUTH GROUP
    if (endpoint === '/auth/login' && method === 'POST') {
      const { email, password } = body;
      const isAdminUser = email?.includes('admin') || password === 'Admin@123';
      const user = {
        id: isAdminUser ? 1 : 2,
        name: isAdminUser ? 'Hữu Nam (Admin)' : (body.name || 'Tuấn Kiệt (Customer)'),
        email: email || 'user@techstore.local',
        role: isAdminUser ? 'admin' : 'customer',
        phone: '0987654321',
        address: '227 Nguyễn Văn Cừ, Quận 5, TP.HCM'
      };
      const accessToken = `jwt_mock_${user.role}_${Date.now()}`;
      const refreshToken = `jwt_rf_mock_${user.role}_${Date.now()}`;
      this.setAuthTokens(accessToken, refreshToken, user);
      return { success: true, data: { user, access_token: accessToken, refresh_token: refreshToken } };
    }

    if (endpoint === '/auth/register' && method === 'POST') {
      const user = {
        id: Date.now(),
        name: body.name || 'Khách hàng mới',
        email: body.email,
        role: 'customer',
        phone: body.phone || '',
        address: body.address || ''
      };
      const accessToken = `jwt_mock_customer_${Date.now()}`;
      const refreshToken = `jwt_rf_mock_customer_${Date.now()}`;
      this.setAuthTokens(accessToken, refreshToken, user);
      return { success: true, message: 'Đăng ký thành công', data: { user, access_token: accessToken, refresh_token: refreshToken } };
    }

    if (endpoint === '/auth/me' && method === 'GET') {
      const user = this.getCurrentUser() || {
        id: 2,
        name: 'Tuấn Kiệt (Demo)',
        email: 'user@techstore.local',
        role: 'customer',
        phone: '0987654321',
        address: '227 Nguyễn Văn Cừ, Quận 5, TP.HCM'
      };
      return { success: true, data: user };
    }

    if (endpoint === '/auth/logout') {
      this.clearAuth();
      return { success: true, message: 'Đã đăng xuất' };
    }

    if (endpoint === '/auth/google' && method === 'POST') {
      const user = {
        id: 88,
        name: 'Google User',
        email: 'google_user@gmail.com',
        role: 'customer',
        avatar: 'https://lh3.googleusercontent.com/a/default-user'
      };
      this.setAuthTokens(`jwt_google_${Date.now()}`, `jwt_rf_google_${Date.now()}`, user);
      return { success: true, data: { user, access_token: `jwt_google_${Date.now()}` } };
    }

    // 6-11. PRODUCTS GROUP
    if (endpoint.startsWith('/products') && method === 'GET') {
      const matchId = endpoint.match(/\/products\/(\d+)$/);
      const products = getStoredProducts();

      if (matchId) {
        const id = parseInt(matchId[1], 10);
        const p = products.find(item => item.id === id);
        return p ? { success: true, data: p } : { success: false, message: 'Không tìm thấy sản phẩm' };
      }

      // Filter query params
      const urlObj = new URL('http://dummy.com' + endpoint);
      const search = (urlObj.searchParams.get('search') || urlObj.searchParams.get('q') || '').toLowerCase();
      const cat = urlObj.searchParams.get('category_id') || urlObj.searchParams.get('category');
      const brand = urlObj.searchParams.get('brand_id') || urlObj.searchParams.get('brand');

      let filtered = [...products];
      if (search) {
        filtered = filtered.filter(p => p.name.toLowerCase().includes(search) || (p.category_name && p.category_name.toLowerCase().includes(search)));
      }
      if (cat && cat !== 'all') {
        filtered = filtered.filter(p => p.category_id == cat || (p.category_name && p.category_name.toLowerCase() === cat.toLowerCase()));
      }
      if (brand) {
        filtered = filtered.filter(p => p.brand_id == brand);
      }

      return {
        success: true,
        data: filtered,
        pagination: { total: filtered.length, page: 1, limit: 50, total_pages: 1 }
      };
    }

    if (endpoint === '/products' && method === 'POST') {
      const products = getStoredProducts();
      const newId = products.length ? Math.max(...products.map(p => p.id)) + 1 : 1;
      const newProd = {
        id: newId,
        name: body.name || 'Sản phẩm mới',
        category_id: parseInt(body.category_id, 10) || 1,
        category_name: body.category_name || 'Điện thoại',
        brand_id: parseInt(body.brand_id, 10) || 1,
        brand_name: body.brand_name || 'Apple',
        price: parseInt(body.price, 10) || 1000000,
        original_price: parseInt(body.original_price, 10) || parseInt(body.price, 10) || 1200000,
        stock: parseInt(body.stock, 10) || 10,
        rating: 5.0,
        review_count: 0,
        image: body.image || 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format&fit=crop&q=80',
        images: body.images || [body.image || 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&auto=format&fit=crop&q=80'],
        ram: body.ram || '8GB',
        rom: body.rom || '128GB',
        chipset: body.chipset || '',
        screen: body.screen || '',
        battery: body.battery || '',
        camera: body.camera || '',
        description: body.description || '',
        featured: !!body.featured,
        created_at: new Date().toISOString().split('T')[0]
      };
      products.push(newProd);
      saveStoredProducts(products);
      return { success: true, message: 'Thêm sản phẩm thành công', data: newProd };
    }

    if (endpoint.match(/\/products\/(\d+)$/) && (method === 'PUT' || method === 'PATCH')) {
      const id = parseInt(endpoint.match(/\/products\/(\d+)$/)[1], 10);
      const products = getStoredProducts();
      const idx = products.findIndex(p => p.id === id);
      if (idx !== -1) {
        products[idx] = { ...products[idx], ...body, id };
        saveStoredProducts(products);
        return { success: true, message: 'Cập nhật thành công', data: products[idx] };
      }
      return { success: false, message: 'Không tìm thấy sản phẩm' };
    }

    if (endpoint.match(/\/products\/(\d+)$/) && method === 'DELETE') {
      const id = parseInt(endpoint.match(/\/products\/(\d+)$/)[1], 10);
      let products = getStoredProducts();
      products = products.filter(p => p.id !== id);
      saveStoredProducts(products);
      return { success: true, message: 'Đã xóa sản phẩm' };
    }

    // 12-14. CATEGORIES & BRANDS
    if (endpoint === '/categories') return { success: true, data: DEFAULT_CATEGORIES };
    if (endpoint === '/brands') return { success: true, data: DEFAULT_BRANDS };

    // 15-18. CART GROUP
    if (endpoint === '/cart' && method === 'GET') {
      return { success: true, data: this.getCart() };
    }

    if (endpoint === '/cart/items' && method === 'POST') {
      const cart = this.getCart();
      const products = getStoredProducts();
      const prod = products.find(p => p.id === body.product_id);

      if (prod) {
        const existing = cart.items.find(i => i.product_id === prod.id && i.variant === (body.variant || 'Mặc định'));
        if (existing) {
          existing.quantity += (body.quantity || 1);
        } else {
          cart.items.push({
            id: Date.now() + Math.floor(Math.random() * 100),
            product_id: prod.id,
            name: prod.name,
            price: prod.price,
            quantity: body.quantity || 1,
            variant: body.variant || 'Mặc định',
            image: prod.image
          });
        }
        this.saveCart(cart);
      }
      return { success: true, message: 'Đã thêm vào giỏ', data: cart };
    }

    if (endpoint.match(/\/cart\/items\/(\d+)$/) && method === 'PUT') {
      const itemId = parseInt(endpoint.match(/\/cart\/items\/(\d+)$/)[1], 10);
      const cart = this.getCart();
      const item = cart.items.find(i => i.id === itemId || i.product_id === itemId);
      if (item) {
        item.quantity = Math.max(1, body.quantity || 1);
        this.saveCart(cart);
      }
      return { success: true, data: cart };
    }

    if (endpoint.match(/\/cart\/items\/(\d+)$/) && method === 'DELETE') {
      const itemId = parseInt(endpoint.match(/\/cart\/items\/(\d+)$/)[1], 10);
      const cart = this.getCart();
      cart.items = cart.items.filter(i => i.id !== itemId && i.product_id !== itemId);
      this.saveCart(cart);
      return { success: true, message: 'Đã xóa', data: cart };
    }

    if (endpoint === '/cart' && method === 'DELETE') {
      this.clearCart();
      return { success: true, message: 'Đã xóa toàn bộ giỏ hàng', data: this.getCart() };
    }

    // 19. COUPONS
    if (endpoint === '/coupons/validate' && method === 'POST') {
      const code = (body.code || '').toUpperCase().trim();
      const total = body.total || 0;
      const c = DEFAULT_COUPONS.find(item => item.code === code);

      if (!c) {
        return { success: false, message: 'Mã giảm giá không tồn tại' };
      }
      if (total < c.min_order) {
        return { success: false, message: `Đơn hàng tối thiểu ${formatVND(c.min_order)} để áp dụng mã này` };
      }

      const discount = c.discount_type === 'percent'
        ? Math.min(c.max_discount, Math.round((total * c.value) / 100))
        : c.value;

      return {
        success: true,
        message: 'Áp dụng thành công',
        data: {
          code: c.code,
          discount_amount: discount,
          final_total: Math.max(0, total - discount),
          description: c.description
        }
      };
    }

    if (endpoint === '/coupons' && method === 'GET') {
      return { success: true, data: DEFAULT_COUPONS };
    }

    // 20-23, 27. ORDERS GROUP
    if (endpoint === '/orders' && method === 'POST') {
      const orders = this.getStoredOrders();
      const newOrder = {
        id: Date.now(),
        order_code: `TS-${Math.floor(100000 + Math.random() * 900000)}`,
        customer_name: body.customer_name || 'Khách hàng',
        customer_phone: body.customer_phone || '',
        customer_email: body.customer_email || '',
        shipping_address: body.shipping_address || 'TP.HCM',
        payment_method: body.payment_method || 'vietqr',
        payment_status: 'pending',
        status: 'pending',
        total_amount: body.total_amount || 0,
        discount_amount: body.discount_amount || 0,
        note: body.note || '',
        created_at: new Date().toISOString(),
        items: body.items || []
      };
      orders.unshift(newOrder);
      localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(orders));
      this.clearCart();
      return { success: true, message: 'Đặt hàng thành công', data: newOrder };
    }

    if (endpoint === '/orders' && method === 'GET') {
      return { success: true, data: this.getStoredOrders() };
    }

    if (endpoint.match(/\/orders\/(\d+)$/) && method === 'GET') {
      const id = parseInt(endpoint.match(/\/orders\/(\d+)$/)[1], 10);
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.id === id) || orders[0];
      return { success: true, data: ord };
    }

    if (endpoint.match(/\/orders\/(\d+)\/cancel$/) && method === 'POST') {
      const id = parseInt(endpoint.match(/\/orders\/(\d+)\/cancel$/)[1], 10);
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.id === id);
      if (ord) {
        ord.status = 'cancelled';
        localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(orders));
        return { success: true, message: 'Đã hủy đơn hàng' };
      }
      return { success: false, message: 'Không tìm thấy đơn hàng' };
    }

    if (endpoint.match(/\/orders\/(\d+)\/status$/) && method === 'PATCH') {
      const id = parseInt(endpoint.match(/\/orders\/(\d+)\/status$/)[1], 10);
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.id === id);
      if (ord) {
        if (body.status) ord.status = body.status;
        if (body.payment_status) ord.payment_status = body.payment_status;
        localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(orders));
        return { success: true, message: 'Đã cập nhật trạng thái đơn hàng', data: ord };
      }
      return { success: false, message: 'Không tìm thấy đơn hàng' };
    }

    // 24-26. VIETQR PAYMENTS & WEBHOOK
    if (endpoint === '/payments/qr' && method === 'POST') {
      const orderId = body.order_id || 1001;
      const amount = body.amount || 1000000;
      const orderCode = body.order_code || `TS-${orderId}`;
      const bank = 'MB';
      const account = '0987654321';
      const qrUrl = `https://img.vietqr.io/image/${bank}-${account}-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(orderCode)}&accountName=TECHSTORE%20VIETNAM`;

      return {
        success: true,
        data: {
          qr_url: qrUrl,
          bank_name: 'Ngân hàng Quân Đội (MB Bank)',
          account_number: account,
          account_name: 'TECHSTORE VIETNAM',
          amount: amount,
          order_code: orderCode,
          expires_in: 900
        }
      };
    }

    if (endpoint.match(/\/payments\/(\d+)\/status$/) && method === 'GET') {
      const orderId = parseInt(endpoint.match(/\/payments\/(\d+)\/status$/)[1], 10);
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.id === orderId);
      const isPaid = ord ? ord.payment_status === 'paid' : false;
      return {
        success: true,
        data: {
          order_id: orderId,
          payment_status: isPaid ? 'paid' : 'pending',
          is_paid: isPaid
        }
      };
    }

    if (endpoint === '/payments/webhook' && method === 'POST') {
      const orderCode = body.order_code || body.content || '';
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.order_code === orderCode || String(o.id) === String(orderCode));
      if (ord) {
        ord.payment_status = 'paid';
        ord.status = 'confirmed';
        localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(orders));
      }
      return { success: true, message: 'Webhook đã nhận và xử lý' };
    }

    // 28. ADMIN USERS
    if (endpoint === '/admin/users' && method === 'GET') {
      return {
        success: true,
        data: [
          { id: 1, name: 'Hữu Nam (Admin)', email: 'admin@techstore.local', role: 'admin', status: 'active', created_at: '2026-01-01' },
          { id: 2, name: 'Tuấn Kiệt (Customer)', email: 'user@techstore.local', role: 'customer', status: 'active', created_at: '2026-02-15' },
          { id: 3, name: 'Thành Trung (Admin)', email: 'trung@techstore.local', role: 'admin', status: 'active', created_at: '2026-03-01' },
          { id: 4, name: 'Vũ Minh Anh', email: 'minhanh@gmail.com', role: 'customer', status: 'active', created_at: '2026-05-12' },
          { id: 5, name: 'Đỗ Quốc Bảo', email: 'baodo@yahoo.com', role: 'customer', status: 'locked', created_at: '2026-06-20' }
        ]
      };
    }

    if (endpoint.match(/\/admin\/users\/(\d+)$/) && method === 'PATCH') {
      return { success: true, message: 'Cập nhật tài khoản người dùng thành công' };
    }

    // 29-30. REVIEWS GROUP
    if (endpoint.match(/\/products\/(\d+)\/reviews$/) && method === 'GET') {
      const prodId = parseInt(endpoint.match(/\/products\/(\d+)\/reviews$/)[1], 10);
      const reviews = getStoredReviews();
      const filtered = reviews.filter(r => r.product_id === prodId);
      return { success: true, data: filtered.length ? filtered : reviews.slice(0, 3) };
    }

    if (endpoint.match(/\/products\/(\d+)\/reviews$/) && method === 'POST') {
      const prodId = parseInt(endpoint.match(/\/products\/(\d+)\/reviews$/)[1], 10);
      const reviews = getStoredReviews();
      const newRev = {
        id: Date.now(),
        product_id: prodId,
        user_name: body.user_name || this.getCurrentUser()?.name || 'Khách hàng',
        rating: body.rating || 5,
        comment: body.comment || '',
        created_at: new Date().toISOString().split('T')[0]
      };
      reviews.unshift(newRev);
      saveStoredReviews(reviews);
      return { success: true, message: 'Gửi đánh giá thành công', data: newRev };
    }

    // 31. WISHLIST GROUP
    if (endpoint === '/wishlist' && method === 'GET') {
      const ids = getStoredWishlistIds();
      const products = getStoredProducts();
      const wishlist = products.filter(p => ids.includes(p.id));
      return { success: true, data: wishlist };
    }

    if (endpoint === '/wishlist' && method === 'POST') {
      const prodId = parseInt(body.product_id, 10);
      const ids = getStoredWishlistIds();
      if (!ids.includes(prodId)) {
        ids.push(prodId);
        saveStoredWishlistIds(ids);
      }
      return { success: true, message: 'Đã thêm vào danh sách yêu thích', active: true };
    }

    if (endpoint.match(/\/wishlist\/(\d+)$/) && method === 'DELETE') {
      const prodId = parseInt(endpoint.match(/\/wishlist\/(\d+)$/)[1], 10);
      let ids = getStoredWishlistIds();
      ids = ids.filter(id => id !== prodId);
      saveStoredWishlistIds(ids);
      return { success: true, message: 'Đã bỏ khỏi danh sách yêu thích', active: false };
    }

    // 32. EVENTS TRACKING
    if (endpoint === '/events/view' && method === 'POST') {
      return { success: true, tracked: true };
    }

    // 33-34. RECOMMENDATIONS
    if (endpoint.includes('/recommendations')) {
      const products = getStoredProducts();
      return {
        success: true,
        data: {
          similar: products.slice(0, 4),
          bought_together: products.slice(1, 3),
          for_you: products.slice(2, 6)
        }
      };
    }

    // 35. ADMIN STATS
    if (endpoint === '/admin/stats') {
      const products = getStoredProducts();
      const orders = this.getStoredOrders();
      const totalRevenue = orders.reduce((sum, o) => sum + (o.payment_status === 'paid' ? o.total_amount : 0), 184500000);
      return {
        success: true,
        data: {
          total_revenue: totalRevenue,
          revenue_growth: '+14.8%',
          new_orders_count: orders.length || 28,
          total_products: products.length,
          total_users: 142,
          recent_orders: orders.slice(0, 5),
          revenue_chart: [
            { day: 'T2', amount: 18500000 },
            { day: 'T3', amount: 22400000 },
            { day: 'T4', amount: 19800000 },
            { day: 'T5', amount: 31200000 },
            { day: 'T6', amount: 28900000 },
            { day: 'T7', amount: 45000000 },
            { day: 'CN', amount: 38700000 }
          ]
        }
      };
    }

    // 36. AUDIT LOGS
    if (endpoint === '/admin/audit-logs') {
      return {
        success: true,
        data: [
          { id: 1, user_name: 'admin@techstore.local', action: 'UPDATE_ORDER_STATUS', description: 'Cập nhật đơn hàng TS-749201 sang completed', created_at: '2026-10-19 10:00:00' },
          { id: 2, user_name: 'admin@techstore.local', action: 'CREATE_PRODUCT', description: 'Thêm sản phẩm mới iPhone 16 Pro Max', created_at: '2026-09-20 14:20:00' }
        ]
      };
    }

    return { success: true, message: 'Mock response OK', data: {} };
  }

  // Cart Storage Helpers
  getCart() {
    const key = this.getCartKey();
    const raw = localStorage.getItem(key);
    if (!raw) return { items: [], total: 0 };
    try {
      const cart = JSON.parse(raw);
      cart.total = (cart.items || []).reduce((sum, item) => sum + (item.price * item.quantity), 0);
      return cart;
    } catch (e) {
      return { items: [], total: 0 };
    }
  }

  saveCart(cart) {
    const key = this.getCartKey();
    cart.total = (cart.items || []).reduce((sum, item) => sum + (item.price * item.quantity), 0);
    localStorage.setItem(key, JSON.stringify(cart));
    this.updateHeaderCartBadge();
  }

  clearCart() {
    const key = this.getCartKey();
    localStorage.removeItem(key);
    this.updateHeaderCartBadge();
  }

  updateHeaderCartBadge() {
    const cart = this.getCart();
    const count = (cart.items || []).reduce((sum, i) => sum + i.quantity, 0);
    const badges = document.querySelectorAll('.cart-badge');
    badges.forEach(b => {
      b.textContent = count;
      b.style.display = count > 0 ? 'inline-block' : 'none';
    });
  }

  getStoredOrders() {
    const raw = localStorage.getItem(API_CONFIG.storageKeys.orders);
    if (!raw) {
      const initial = [
        {
          id: 1001,
          order_code: 'TS-749201',
          customer_name: 'Tuấn Kiệt',
          customer_phone: '0987654321',
          shipping_address: '227 Nguyễn Văn Cừ, P4, Quận 5, TP.HCM',
          payment_method: 'vietqr',
          payment_status: 'paid',
          status: 'completed',
          total_amount: 34990000,
          discount_amount: 500000,
          created_at: '2026-10-19T09:30:00Z',
          items: [{ name: 'iPhone 16 Pro Max 256GB', quantity: 1, price: 34990000 }]
        },
        {
          id: 1002,
          order_code: 'TS-839102',
          customer_name: 'Nguyễn Văn Minh',
          customer_phone: '0912345678',
          shipping_address: '15 Lê Duẩn, Bến Nghé, Quận 1, TP.HCM',
          payment_method: 'vietqr',
          payment_status: 'pending',
          status: 'pending',
          total_amount: 7490000,
          discount_amount: 50000,
          created_at: '2026-10-21T08:15:00Z',
          items: [{ name: 'Tai nghe Sony WH-1000XM5 Chống Ồn', quantity: 1, price: 7490000 }]
        }
      ];
      localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(initial));
      return initial;
    }
    try {
      return JSON.parse(raw);
    } catch (e) {
      return [];
    }
  }

  // ==========================================
  // HIGH LEVEL API METHODS (FULL 37 API CONTRACT)
  // ==========================================
  auth = {
    login: async (email, password) => {
      const res = await this.request('/auth/login', { method: 'POST', body: JSON.stringify({ email, password }) });
      if (res && res.success && res.data) {
        const { access_token, refresh_token, user } = res.data;
        if (access_token || user) {
          this.setAuthTokens(access_token, refresh_token, user);
          // Chuyển các món đồ khách vãng lai đã chọn vào giỏ hàng tài khoản
          try {
            const guestCartRaw = localStorage.getItem('techstore_cart_guest');
            if (guestCartRaw) {
              const guestCart = JSON.parse(guestCartRaw);
              if (guestCart && Array.isArray(guestCart.items) && guestCart.items.length > 0) {
                for (const item of guestCart.items) {
                  await this.cart.addItem(item.product_id, item.quantity, item.variant);
                }
              }
              localStorage.removeItem('techstore_cart_guest');
            }
          } catch (e) {}

          try {
            await this.cart.get();
          } catch (e) {}
        }
      }
      return res;
    },
    register: async (userData) => {
      const res = await this.request('/auth/register', { method: 'POST', body: JSON.stringify(userData) });
      if (res && res.success && res.data) {
        const { access_token, refresh_token, user } = res.data;
        if (access_token || user) {
          this.setAuthTokens(access_token, refresh_token, user);
          // Chuyển các món đồ khách vãng lai đã chọn vào giỏ hàng tài khoản mới
          try {
            const guestCartRaw = localStorage.getItem('techstore_cart_guest');
            if (guestCartRaw) {
              const guestCart = JSON.parse(guestCartRaw);
              if (guestCart && Array.isArray(guestCart.items) && guestCart.items.length > 0) {
                for (const item of guestCart.items) {
                  await this.cart.addItem(item.product_id, item.quantity, item.variant);
                }
              }
              localStorage.removeItem('techstore_cart_guest');
            }
          } catch (e) {}

          try {
            await this.cart.get();
          } catch (e) {}
        }
      }
      return res;
    },
    refresh: (refreshToken) => this.request('/auth/refresh', { method: 'POST', body: JSON.stringify({ refresh_token: refreshToken }) }),
    logout: async () => {
      try {
        await this.request('/auth/logout', { method: 'POST' });
      } catch (e) {}
      this.clearAuth();
      return { success: true, message: 'Đã đăng xuất' };
    },
    me: () => this.request('/auth/me', { method: 'GET' }),
    google: async (token) => {
      const res = await this.request('/auth/google', { method: 'POST', body: JSON.stringify({ token }) });
      if (res && res.success && res.data) {
        const { access_token, refresh_token, user } = res.data;
        if (access_token || user) {
          this.setAuthTokens(access_token, refresh_token, user);
        }
      }
      return res;
    }
  };

  products = {
    getAll: (params = {}) => {
      const qs = new URLSearchParams(params).toString();
      return this.request(`/products${qs ? '?' + qs : ''}`, { method: 'GET' });
    },
    getById: (id) => this.request(`/products/${id}`, { method: 'GET' }),
    create: (data) => this.request('/products', { method: 'POST', body: JSON.stringify(data) }),
    update: (id, data) => this.request(`/products/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
    patch: (id, data) => this.request(`/products/${id}`, { method: 'PATCH', body: JSON.stringify(data) }),
    delete: (id) => this.request(`/products/${id}`, { method: 'DELETE' })
  };

  categories = {
    getAll: () => this.request('/categories', { method: 'GET' }),
    create: (data) => this.request('/categories', { method: 'POST', body: JSON.stringify(data) }),
    update: (id, data) => this.request(`/categories/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
    delete: (id) => this.request(`/categories/${id}`, { method: 'DELETE' })
  };

  brands = {
    getAll: () => this.request('/brands', { method: 'GET' }),
    create: (data) => this.request('/brands', { method: 'POST', body: JSON.stringify(data) }),
    update: (id, data) => this.request(`/brands/${id}`, { method: 'PUT', body: JSON.stringify(data) }),
    delete: (id) => this.request(`/brands/${id}`, { method: 'DELETE' })
  };

  cart = {
    get: async () => {
      if (this.isLoggedIn()) {
        try {
          const res = await this.request('/cart', { method: 'GET' });
          if (res && res.success && res.data) {
            this.saveCart(res.data);
            return res;
          }
        } catch (e) {}
      }
      return { success: true, data: this.getCart() };
    },
    addItem: async (productId, quantity = 1, variant = '') => {
      productId = parseInt(productId, 10);
      quantity = Math.max(1, parseInt(quantity, 10) || 1);
      
      const cart = this.getCart();
      const products = getStoredProducts();
      let prod = products.find(p => p.id === productId);
      if (!prod && window.allProducts) {
        prod = window.allProducts.find(p => p.id === productId);
      }
      if (!prod) {
        try {
          const pRes = await this.products.getById(productId);
          if (pRes && pRes.data) prod = pRes.data;
        } catch (e) {}
      }

      if (prod) {
        const targetVariant = variant || 'Mặc định';
        const existing = cart.items.find(i => i.product_id === prod.id && (i.variant || 'Mặc định') === targetVariant);
        if (existing) {
          existing.quantity += quantity;
        } else {
          cart.items.push({
            id: Date.now() + Math.floor(Math.random() * 100),
            product_id: prod.id,
            name: prod.name,
            price: prod.price,
            quantity: quantity,
            variant: targetVariant,
            image: prod.image
          });
        }
        this.saveCart(cart);
      }

      // Chỉ gửi request lên server backend PHP nếu người dùng ĐÃ ĐĂNG NHẬP
      if (this.isLoggedIn()) {
        try {
          const res = await this.request('/cart/items', { method: 'POST', body: JSON.stringify({ product_id: productId, quantity, variant }) });
          if (res && res.success && res.data) {
            this.saveCart(res.data);
            return res;
          }
          return res || { success: true, data: cart };
        } catch (e) {
          return { success: true, data: cart };
        }
      }

      return { success: true, data: cart };
    },
    updateItem: async (itemId, quantity) => {
      itemId = parseInt(itemId, 10);
      quantity = Math.max(0, parseInt(quantity, 10));
      const cart = this.getCart();
      if (quantity <= 0) {
        cart.items = cart.items.filter(i => i.id !== itemId && i.product_id !== itemId);
      } else {
        const item = cart.items.find(i => i.id === itemId || i.product_id === itemId);
        if (item) item.quantity = quantity;
      }
      this.saveCart(cart);

      if (this.isLoggedIn()) {
        try {
          const res = await this.request(`/cart/items/${itemId}`, { method: 'PUT', body: JSON.stringify({ quantity }) });
          if (res && res.success && res.data) {
            this.saveCart(res.data);
            return res;
          }
        } catch (e) {}
      }
      return { success: true, data: cart };
    },
    removeItem: async (itemId) => {
      itemId = parseInt(itemId, 10);
      const cart = this.getCart();
      cart.items = cart.items.filter(i => i.id !== itemId && i.product_id !== itemId);
      this.saveCart(cart);

      if (this.isLoggedIn()) {
        try {
          const res = await this.request(`/cart/items/${itemId}`, { method: 'DELETE' });
          if (res && res.success && res.data) {
            this.saveCart(res.data);
            return res;
          }
        } catch (e) {}
      }
      return { success: true, message: 'Đã xóa', data: cart };
    },
    clear: async () => {
      this.clearCart();
      if (this.isLoggedIn()) {
        try {
          await this.request('/cart', { method: 'DELETE' });
        } catch (e) {}
      }
      return { success: true };
    }
  };

  coupons = {
    validate: (code, total) => this.request('/coupons/validate', { method: 'POST', body: JSON.stringify({ code, total }) }),
    getAll: () => this.request('/coupons', { method: 'GET' })
  };

  orders = {
    create: async (orderData) => {
      const res = await this.request('/orders', { method: 'POST', body: JSON.stringify(orderData) });
      if (res && res.success) {
        this.clearCart();
      }
      return res;
    },
    getAll: () => this.request('/orders', { method: 'GET' }),
    getById: (id) => this.request(`/orders/${id}`, { method: 'GET' }),
    cancel: (id) => this.request(`/orders/${id}/cancel`, { method: 'POST' }),
    updateStatus: (id, status, paymentStatus) => this.request(`/orders/${id}/status`, { method: 'PATCH', body: JSON.stringify({ status, payment_status: paymentStatus }) })
  };

  payments = {
    generateQr: (orderId, amount, orderCode) => this.request('/payments/qr', { method: 'POST', body: JSON.stringify({ order_id: orderId, amount, order_code: orderCode }) }),
    getStatus: (orderId) => this.request(`/payments/${orderId}/status`, { method: 'GET' }),
    webhook: (payload, signature) => this.request('/payments/webhook', { method: 'POST', body: JSON.stringify(payload), headers: { 'X-Signature': signature } }),
    simulateSuccess: (orderId) => {
      return this.orders.updateStatus(orderId, 'confirmed', 'paid');
    }
  };

  recommendations = {
    getSimilar: (productId) => this.request(`/products/${productId}/recommendations`, { method: 'GET' }),
    getBoughtTogether: (productId) => this.request(`/products/${productId}/recommendations`, { method: 'GET' }),
    getForMe: () => this.request('/recommendations/me', { method: 'GET' })
  };

  reviews = {
    getByProduct: (productId) => this.request(`/products/${productId}/reviews`, { method: 'GET' }),
    create: (productId, rating, comment) => this.request(`/products/${productId}/reviews`, { method: 'POST', body: JSON.stringify({ rating, comment }) })
  };

  wishlist = {
    get: () => this.request('/wishlist', { method: 'GET' }),
    add: (productId) => this.request('/wishlist', { method: 'POST', body: JSON.stringify({ product_id: productId }) }),
    remove: (productId) => this.request(`/wishlist/${productId}`, { method: 'DELETE' }),
    toggle: async (productId) => {
      const ids = getStoredWishlistIds();
      if (ids.includes(productId)) {
        await this.request(`/wishlist/${productId}`, { method: 'DELETE' });
        return { active: false };
      } else {
        await this.request('/wishlist', { method: 'POST', body: JSON.stringify({ product_id: productId }) });
        return { active: true };
      }
    }
  };

  events = {
    trackView: (productId) => this.request('/events/view', { method: 'POST', body: JSON.stringify({ product_id: productId }) })
  };

  admin = {
    getStats: () => this.request('/admin/stats', { method: 'GET' }),
    getUsers: () => this.request('/admin/users', { method: 'GET' }),
    updateUser: (id, data) => this.request(`/admin/users/${id}`, { method: 'PATCH', body: JSON.stringify(data) }),
    getAuditLogs: () => this.request('/admin/audit-logs', { method: 'GET' })
  };
}

// Global API Instance
const api = new ApiClient();
window.api = api;

// Global Quick Action Functions (available everywhere)
window.quickAddToCart = async function(productId) {
  try {
    productId = parseInt(productId, 10);
    await api.cart.addItem(productId, 1);
    const cart = api.getCart();
    const item = (cart.items || []).find(i => i.product_id === productId);
    const name = item ? item.name : 'Sản phẩm';
    showToast(`Đã thêm "${name}" vào giỏ hàng!`, 'success');
  } catch (err) {
    showToast('Không thể thêm sản phẩm vào giỏ', 'danger');
  }
};

window.toggleWishlist = async function(productId, btn) {
  try {
    const res = await api.wishlist.toggle(productId);
    if (btn) {
      btn.classList.toggle('active', res.active);
      btn.style.color = res.active ? '#d70018' : '#94a3b8';
    }
    showToast(res.active ? 'Đã thêm vào danh sách yêu thích!' : 'Đã bỏ khỏi danh sách yêu thích!', 'info');
  } catch (err) {
    showToast('Thao tác yêu thích thất bại', 'danger');
  }
};

// Setup UI State on Load (badges, active user dropdown)
document.addEventListener('DOMContentLoaded', () => {
  api.updateHeaderCartBadge();
  setupUserDropdown();
  setupLiveSearch();
});

function setupUserDropdown() {
  const userMenuBtn = document.getElementById('user-menu-btn');
  const userDropdown = document.getElementById('user-dropdown');
  if (!userMenuBtn || !userDropdown) return;

  const user = api.getCurrentUser();
  if (user) {
    const nameEl = document.getElementById('dropdown-name');
    const roleEl = document.getElementById('dropdown-role');
    if (nameEl) nameEl.textContent = user.name;
    if (roleEl) roleEl.textContent = user.role === 'admin' ? 'Quản trị viên' : 'Khách hàng';
    
    // Show admin link if admin
    const adminLink = document.getElementById('dropdown-admin-link');
    if (adminLink) adminLink.style.display = api.isAdmin() ? 'flex' : 'none';

    const loginLinks = document.querySelectorAll('.guest-only');
    loginLinks.forEach(el => el.style.display = 'none');
    const userLinks = document.querySelectorAll('.auth-only');
    userLinks.forEach(el => el.style.display = 'flex');
  } else {
    const loginLinks = document.querySelectorAll('.guest-only');
    loginLinks.forEach(el => el.style.display = 'flex');
    const userLinks = document.querySelectorAll('.auth-only');
    userLinks.forEach(el => el.style.display = 'none');
  }

  userMenuBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    userDropdown.classList.toggle('active');
  });

  document.addEventListener('click', () => {
    userDropdown.classList.remove('active');
  });

  const logoutBtn = document.getElementById('btn-logout');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', () => {
      api.auth.logout();
      showToast('Đã đăng xuất thành công', 'success');
      setTimeout(() => window.location.reload(), 500);
    });
  }
}

function setupLiveSearch() {
  const searchInput = document.getElementById('global-search-input');
  const suggestionsBox = document.getElementById('search-suggestions');
  if (!searchInput || !suggestionsBox) return;

  const handleSearch = debounce(async (keyword) => {
    if (!keyword || keyword.trim().length < 2) {
      suggestionsBox.classList.remove('active');
      return;
    }
    const res = await api.products.getAll({ search: keyword.trim(), limit: 5 });
    const items = (res.data?.items || res.data || []).slice(0, 5);

    if (items.length === 0) {
      suggestionsBox.innerHTML = `<div style="padding: 12px; font-size: 13px; color: #64748b; text-align: center;">Không tìm thấy sản phẩm cho "${keyword}"</div>`;
    } else {
      suggestionsBox.innerHTML = items.map(p => `
        <div class="suggestion-item" onclick="window.location.href='/pages/product-detail.html?id=${p.id}'">
          <img src="${p.image}" class="suggestion-thumb" alt="${p.name}">
          <div class="suggestion-info">
            <div class="suggestion-title">${p.name}</div>
            <div class="suggestion-price">${formatVND(p.price)}</div>
          </div>
        </div>
      `).join('');
    }
    suggestionsBox.classList.add('active');
  }, 250);

  searchInput.addEventListener('input', (e) => handleSearch(e.target.value));

  document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
      suggestionsBox.classList.remove('active');
    }
  });

  const searchForm = document.getElementById('global-search-form');
  if (searchForm) {
    searchForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const val = searchInput.value.trim();
      if (val) {
        window.location.href = `/pages/products.html?search=${encodeURIComponent(val)}`;
      }
    });
  }
}

// ===================================================
// INJECT AMBIENT AURORA MESH (Hiệu ứng gradient loang lổ)
// ===================================================
function initAmbientAurora() {
  if (document.querySelector('.ambient-aurora-bg')) return;
  const aurora = document.createElement('div');
  aurora.className = 'ambient-aurora-bg';
  aurora.innerHTML = `
    <div class="aurora-orb aurora-orb-1"></div>
    <div class="aurora-orb aurora-orb-2"></div>
    <div class="aurora-orb aurora-orb-3"></div>
    <div class="aurora-orb aurora-orb-4"></div>
  `;
  document.body.prepend(aurora);
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAmbientAurora);
} else {
  initAmbientAurora();
}
