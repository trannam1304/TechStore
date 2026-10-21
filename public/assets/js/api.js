/**
 * TechStore - Central REST API Client & Helper Utilities
 * Supports real backend /api/v1 + seamless mock fallback for standalone demo
 * Author: Tuan Kiet (Front-end & Documentation)
 */

const API_CONFIG = {
  baseUrl: window.location.origin.includes('localhost') || window.location.origin.includes('127.0.0.1')
    ? '/api/v1'
    : '/api/v1',
  timeout: 8000,
  storageKeys: {
    accessToken: 'techstore_access_token',
    refreshToken: 'techstore_refresh_token',
    user: 'techstore_user',
    cart: 'techstore_cart',
    wishlist: 'techstore_wishlist',
    orders: 'techstore_orders',
    products: 'techstore_products_db'
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
    id: 1,
    name: 'iPhone 16 Pro Max 256GB',
    category_id: 1,
    category_name: 'Điện thoại',
    brand_id: 1,
    brand_name: 'Apple',
    price: 34990000,
    original_price: 37990000,
    stock: 25,
    rating: 4.9,
    review_count: 142,
    image: 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600&auto=format&fit=crop&q=80',
    images: [
      'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800&auto=format&fit=crop&q=80',
      'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800&auto=format&fit=crop&q=80'
    ],
    ram: '8GB',
    rom: '256GB',
    chipset: 'Apple A18 Pro 3nm',
    screen: '6.9 inch Super Retina XDR OLED 120Hz',
    battery: '4685 mAh, Sạc nhanh 30W',
    camera: 'Chính 48MP + Góc rộng 48MP + Tele 5x 12MP',
    description: 'iPhone 16 Pro Max thiết kế Titan sa mạc đẳng cấp, chip A18 Pro mạnh mẽ và nút Camera Control hoàn toàn mới.',
    featured: true,
    created_at: '2026-09-20'
  },
  {
    id: 2,
    name: 'Samsung Galaxy S24 Ultra 5G 12GB/256GB',
    category_id: 1,
    category_name: 'Điện thoại',
    brand_id: 2,
    brand_name: 'Samsung',
    price: 28990000,
    original_price: 33990000,
    stock: 18,
    rating: 4.8,
    review_count: 98,
    image: 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600&auto=format&fit=crop&q=80',
    images: [
      'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=800&auto=format&fit=crop&q=80'
    ],
    ram: '12GB',
    rom: '256GB',
    chipset: 'Snapdragon 8 Gen 3 for Galaxy',
    screen: '6.8 inch Dynamic AMOLED 2X 120Hz QHD+',
    battery: '5000 mAh, Sạc 45W',
    camera: '200MP + 50MP + 12MP + 10MP, Zoom AI 100x',
    description: 'Khung viền Titan cao cấp, bút S-Pen tích hợp và quyền năng Galaxy AI mở ra kỷ nguyên công nghệ di động mới.',
    featured: true,
    created_at: '2026-08-15'
  },
  {
    id: 3,
    name: 'MacBook Pro 14 inch M3 Pro 18GB/512GB',
    category_id: 2,
    category_name: 'Laptop',
    brand_id: 1,
    brand_name: 'Apple',
    price: 48990000,
    original_price: 52990000,
    stock: 12,
    rating: 5.0,
    review_count: 64,
    image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&auto=format&fit=crop&q=80',
    images: [
      'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&auto=format&fit=crop&q=80'
    ],
    ram: '18GB',
    rom: '512GB',
    chipset: 'Apple M3 Pro 11-core CPU, 14-core GPU',
    screen: '14.2 inch Liquid Retina XDR 120Hz',
    battery: '70Wh, Thời lượng pin 18 giờ',
    camera: 'FaceTime HD 1080p',
    description: 'Hiệu năng đồ họa đỉnh cao với chip M3 Pro kiến trúc mới, màn hình Liquid Retina XDR độ sáng 1600 nits đỉnh sáng.',
    featured: true,
    created_at: '2026-07-10'
  },
  {
    id: 4,
    name: 'Laptop Gaming ASUS ROG Zephyrus G16',
    category_id: 2,
    category_name: 'Laptop',
    brand_id: 4,
    brand_name: 'ASUS',
    price: 54990000,
    original_price: 59990000,
    stock: 8,
    rating: 4.8,
    review_count: 36,
    image: 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600&auto=format&fit=crop&q=80',
    images: [
      'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=800&auto=format&fit=crop&q=80'
    ],
    ram: '32GB',
    rom: '1TB',
    chipset: 'Intel Core Ultra 9 185H / RTX 4080 12GB',
    screen: '16 inch ROG Nebula OLED 2.5K 240Hz',
    battery: '90Wh, Pin trâu, sạc nhanh Type-C',
    camera: 'FHD IR Camera',
    description: 'Tuyệt tác laptop gaming siêu mỏng nhẹ với màn hình OLED 240Hz chuẩn màu 100% DCI-P3 cùng card đồ họa RTX 4080 mạnh mẽ.',
    featured: true,
    created_at: '2026-06-25'
  },
  {
    id: 5,
    name: 'iPad Pro 11 inch M4 Wi-Fi 256GB',
    category_id: 3,
    category_name: 'Tablet',
    brand_id: 1,
    brand_name: 'Apple',
    price: 27990000,
    original_price: 29990000,
    stock: 20,
    rating: 4.9,
    review_count: 53,
    image: 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format&fit=crop&q=80',
    images: [
      'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800&auto=format&fit=crop&q=80'
    ],
    ram: '8GB',
    rom: '256GB',
    chipset: 'Apple M4 chip thế hệ mới',
    screen: '11 inch Ultra Retina XDR Tandem OLED',
    battery: '31.29 Wh, 10 giờ lướt web',
    camera: '12MP Wide + Cảm biến LiDAR',
    description: 'Thiết kế siêu mỏng nhẹ chỉ 5.3mm, đột phá công nghệ Ultra Retina XDR OLED hai lớp hiển thị rực rỡ.',
    featured: true,
    created_at: '2026-09-01'
  },
  {
    id: 6,
    name: 'Tai nghe Sony WH-1000XM5 Chống Ồn',
    category_id: 4,
    category_name: 'Âm thanh',
    brand_id: 5,
    brand_name: 'Sony',
    price: 7490000,
    original_price: 8990000,
    stock: 35,
    rating: 4.9,
    review_count: 180,
    image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=80',
    images: [
      'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800&auto=format&fit=crop&q=80'
    ],
    ram: '-',
    rom: '-',
    chipset: 'Sony V1 + QN1 Processor',
    screen: '-',
    battery: '30 giờ (bật ANC), Sạc nhanh 3 phút được 3 giờ',
    camera: '-',
    description: 'Tai nghe chống ồn số 1 thị trường với 8 micro kép và 2 bộ vi xử lý riêng biệt mang lại âm thanh chuẩn Hi-Res Audio.',
    featured: true,
    created_at: '2026-05-10'
  },
  {
    id: 7,
    name: 'Apple Watch Ultra 2 GPS + Cellular 49mm',
    category_id: 5,
    category_name: 'Smartwatch',
    brand_id: 1,
    brand_name: 'Apple',
    price: 20990000,
    original_price: 22990000,
    stock: 14,
    rating: 4.9,
    review_count: 42,
    image: 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600&auto=format&fit=crop&q=80',
    images: [
      'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=800&auto=format&fit=crop&q=80'
    ],
    ram: '-',
    rom: '64GB',
    chipset: 'Apple S9 SiP',
    screen: '49mm Retina LTPO OLED 3000 nits',
    battery: 'Pin 36 giờ, 72 giờ chế độ nguồn điện thấp',
    camera: '-',
    description: 'Khung Titan siêu bền bỉ chuẩn quân đội, GPS băng tần kép chính xác, chống nước 100m dành cho vận động viên thể thao khắc nghiệt.',
    featured: false,
    created_at: '2026-08-01'
  },
  {
    id: 8,
    name: 'Củ sạc nhanh Anker 65W GaNPrime 3 cổng',
    category_id: 6,
    category_name: 'Phụ kiện',
    brand_id: 6,
    brand_name: 'Anker',
    price: 890000,
    original_price: 1200000,
    stock: 50,
    rating: 4.8,
    review_count: 215,
    image: 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=600&auto=format&fit=crop&q=80',
    images: [
      'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=800&auto=format&fit=crop&q=80'
    ],
    ram: '-',
    rom: '-',
    chipset: 'GaNPrime Technology',
    screen: '-',
    battery: '-',
    camera: '-',
    description: 'Công nghệ GaNPrime tối ưu hóa dòng sạc an toàn, sạc cùng lúc 3 thiết bị MacBook, iPhone, iPad với công suất tối đa 65W.',
    featured: false,
    created_at: '2026-04-12'
  }
];

const DEFAULT_CATEGORIES = [
  { id: 1, name: 'Điện thoại', icon: '📱', slug: 'dien-thoai', product_count: 28 },
  { id: 2, name: 'Laptop', icon: '💻', slug: 'laptop', product_count: 20 },
  { id: 3, name: 'Tablet', icon: '📋', slug: 'tablet', product_count: 14 },
  { id: 4, name: 'Âm thanh', icon: '🎧', slug: 'am-thanh', product_count: 16 },
  { id: 5, name: 'Smartwatch', icon: '⌚', slug: 'smartwatch', product_count: 12 },
  { id: 6, name: 'Phụ kiện', icon: '🔌', slug: 'phu-kien', product_count: 35 }
];

const DEFAULT_BRANDS = [
  { id: 1, name: 'Apple', logo: '' },
  { id: 2, name: 'Samsung', logo: 'Samsung' },
  { id: 3, name: 'Xiaomi', logo: 'Xiaomi' },
  { id: 4, name: 'ASUS', logo: 'ASUS' },
  { id: 5, name: 'Sony', logo: 'Sony' },
  { id: 6, name: 'Anker', logo: 'Anker' },
  { id: 7, name: 'Dell', logo: 'Dell' }
];

const DEFAULT_COUPONS = [
  { code: 'TECH10', discount_type: 'percent', value: 10, min_order: 1000000, max_discount: 500000, description: 'Giảm 10% tối đa 500k cho đơn từ 1 triệu' },
  { code: 'VIETQR50', discount_type: 'amount', value: 50000, min_order: 500000, max_discount: 50000, description: 'Giảm 50.000đ khi thanh toán VietQR' },
  { code: 'GIAM100K', discount_type: 'amount', value: 100000, min_order: 2000000, max_discount: 100000, description: 'Giảm ngay 100k cho đơn từ 2 triệu' }
];

// Initialize Mock DB in LocalStorage
function getStoredProducts() {
  const p = localStorage.getItem(API_CONFIG.storageKeys.products);
  if (!p) {
    localStorage.setItem(API_CONFIG.storageKeys.products, JSON.stringify(DEFAULT_PRODUCTS));
    return DEFAULT_PRODUCTS;
  }
  try {
    return JSON.parse(p);
  } catch (e) {
    return DEFAULT_PRODUCTS;
  }
}

function saveStoredProducts(products) {
  localStorage.setItem(API_CONFIG.storageKeys.products, JSON.stringify(products));
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
  }

  isLoggedIn() {
    return !!this.getAccessToken();
  }

  isAdmin() {
    const user = this.getCurrentUser();
    return user && (user.role === 'admin' || user.email === 'admin@techstore.local');
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

      const json = await response.json();
      return json;
    } catch (err) {
      // If server is not responding, fallback to client-side mock
      console.warn(`[TechStore API] Live endpoint ${endpoint} unavailable, activating smart mock fallback.`, err.message);
      return this.handleMockFallback(endpoint, options);
    }
  }

  async refreshToken() {
    const rf = this.getRefreshToken();
    if (!rf) return false;
    try {
      const res = await fetch(`${this.baseUrl}/auth/refresh`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: rf })
      });
      if (res.ok) {
        const data = await res.json();
        this.setAuthTokens(data.data.access_token, data.data.refresh_token, data.data.user);
        return true;
      }
    } catch (e) {}
    this.clearAuth();
    return false;
  }

  // ==========================================
  // SMART MOCK FALLBACK (STANDALONE CAPABLE)
  // ==========================================
  handleMockFallback(endpoint, options) {
    const method = (options.method || 'GET').toUpperCase();
    const body = options.body && typeof options.body === 'string' ? JSON.parse(options.body) : (options.body || {});

    // AUTH ENDPOINTS
    if (endpoint === '/auth/login' && method === 'POST') {
      const { email, password } = body;
      const isAdminUser = email.includes('admin') || password === 'Admin@123';
      const user = {
        id: isAdminUser ? 1 : 2,
        name: isAdminUser ? 'Quản trị viên (Hữu Nam / Kiệt)' : 'Khách hàng Tuấn Kiệt',
        email: email || 'user@techstore.local',
        role: isAdminUser ? 'admin' : 'customer',
        phone: '0987654321',
        address: '227 Nguyễn Văn Cừ, Quận 5, TP.HCM'
      };
      this.setAuthTokens('mock_jwt_access_token_' + Date.now(), 'mock_jwt_refresh_token', user);
      return { success: true, message: 'Đăng nhập thành công', data: { token: 'mock_jwt_access_token', user } };
    }

    if (endpoint === '/auth/register' && method === 'POST') {
      const user = {
        id: Math.floor(Math.random() * 1000) + 10,
        name: body.name || 'Khách hàng mới',
        email: body.email,
        role: 'customer',
        phone: body.phone || '',
        address: body.address || ''
      };
      this.setAuthTokens('mock_jwt_access_token_' + Date.now(), 'mock_jwt_refresh_token', user);
      return { success: true, message: 'Đăng ký tài khoản thành công', data: { user } };
    }

    if (endpoint === '/auth/me' && method === 'GET') {
      const user = this.getCurrentUser() || {
        id: 2,
        name: 'Khách hàng Demo',
        email: 'user@techstore.local',
        role: 'customer',
        phone: '0987654321',
        address: '227 Nguyễn Văn Cừ, Quận 5, TP.HCM'
      };
      return { success: true, data: user };
    }

    if (endpoint === '/auth/logout') {
      this.clearAuth();
      return { success: true, message: 'Đăng xuất thành công' };
    }

    // PRODUCTS ENDPOINTS
    if (endpoint.startsWith('/products') && method === 'GET') {
      let products = getStoredProducts();
      
      // Detail by ID: /products/{id}
      const matchId = endpoint.match(/\/products\/(\d+)/);
      if (matchId) {
        const id = parseInt(matchId[1], 10);
        const item = products.find(p => p.id === id) || products[0];
        return { success: true, data: item };
      }

      // Query params parsing
      const urlParams = new URLSearchParams(endpoint.split('?')[1] || '');
      const search = (urlParams.get('search') || '').toLowerCase();
      const catId = urlParams.get('category_id');
      const brandId = urlParams.get('brand_id');
      const sort = urlParams.get('sort');

      if (search) {
        products = products.filter(p => p.name.toLowerCase().includes(search) || p.category_name.toLowerCase().includes(search));
      }
      if (catId) {
        products = products.filter(p => p.category_id == catId);
      }
      if (brandId) {
        products = products.filter(p => p.brand_id == brandId);
      }
      if (sort === 'price_asc') products.sort((a, b) => a.price - b.price);
      if (sort === 'price_desc') products.sort((a, b) => b.price - a.price);

      return {
        success: true,
        data: products,
        meta: { total: products.length, page: 1, limit: 20 }
      };
    }

    if (endpoint === '/products' && method === 'POST') {
      const products = getStoredProducts();
      const newProduct = {
        id: products.length + 1,
        name: body.name || 'Sản phẩm mới',
        category_id: body.category_id || 1,
        category_name: 'Thiết bị công nghệ',
        brand_id: body.brand_id || 1,
        brand_name: 'TechStore',
        price: parseInt(body.price, 10) || 10000000,
        original_price: parseInt(body.original_price, 10) || 12000000,
        stock: parseInt(body.stock, 10) || 10,
        rating: 5.0,
        review_count: 0,
        image: body.image || 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=600&auto=format&fit=crop&q=80',
        images: [body.image || 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=800&auto=format&fit=crop&q=80'],
        ram: body.ram || '-',
        rom: body.rom || '-',
        chipset: body.chipset || '-',
        screen: body.screen || '-',
        battery: body.battery || '-',
        camera: body.camera || '-',
        description: body.description || '',
        created_at: new Date().toISOString().split('T')[0]
      };
      products.unshift(newProduct);
      saveStoredProducts(products);
      return { success: true, message: 'Thêm sản phẩm thành công', data: newProduct };
    }

    if (endpoint.startsWith('/products/') && (method === 'PUT' || method === 'PATCH')) {
      const id = parseInt(endpoint.split('/')[2], 10);
      const products = getStoredProducts();
      const idx = products.findIndex(p => p.id === id);
      if (idx !== -1) {
        products[idx] = { ...products[idx], ...body };
        saveStoredProducts(products);
        return { success: true, message: 'Cập nhật thành công', data: products[idx] };
      }
      return { success: false, error: 'Không tìm thấy sản phẩm' };
    }

    if (endpoint.startsWith('/products/') && method === 'DELETE') {
      const id = parseInt(endpoint.split('/')[2], 10);
      let products = getStoredProducts();
      products = products.filter(p => p.id !== id);
      saveStoredProducts(products);
      return { success: true, message: 'Đã xóa sản phẩm' };
    }

    // CATEGORIES & BRANDS
    if (endpoint === '/categories') return { success: true, data: DEFAULT_CATEGORIES };
    if (endpoint === '/brands') return { success: true, data: DEFAULT_BRANDS };

    // CART
    if (endpoint === '/cart' && method === 'GET') {
      const cart = this.getCart();
      return { success: true, data: cart };
    }

    if (endpoint === '/cart/items' && method === 'POST') {
      const cart = this.getCart();
      const products = getStoredProducts();
      const product = products.find(p => p.id == body.product_id) || products[0];

      const existingIndex = cart.items.findIndex(i => i.product_id == body.product_id && i.variant == (body.variant || ''));
      const qty = parseInt(body.quantity, 10) || 1;

      if (existingIndex > -1) {
        cart.items[existingIndex].quantity += qty;
      } else {
        cart.items.push({
          id: Date.now(),
          product_id: product.id,
          name: product.name,
          image: product.image,
          price: product.price,
          original_price: product.original_price,
          quantity: qty,
          variant: body.variant || 'Mặc định'
        });
      }
      this.saveCart(cart);
      return { success: true, message: 'Đã thêm vào giỏ hàng', data: cart };
    }

    // COUPONS
    if (endpoint === '/coupons/validate' && method === 'POST') {
      const code = (body.code || '').trim().toUpperCase();
      const total = body.total || 0;
      const coupon = DEFAULT_COUPONS.find(c => c.code === code);

      if (!coupon) {
        return { success: false, message: 'Mã giảm giá không hợp lệ hoặc đã hết hạn' };
      }
      if (total < coupon.min_order) {
        return { success: false, message: `Mã chỉ áp dụng cho đơn từ ${formatVND(coupon.min_order)}` };
      }

      let discountAmount = coupon.discount_type === 'percent'
        ? Math.min((total * coupon.value) / 100, coupon.max_discount || 99999999)
        : coupon.value;

      return {
        success: true,
        message: 'Áp dụng mã thành công!',
        data: {
          code: coupon.code,
          discount_amount: discountAmount,
          description: coupon.description
        }
      };
    }

    // ORDERS & CHECKOUT
    if (endpoint === '/orders' && method === 'POST') {
      const orders = this.getStoredOrders();
      const newOrder = {
        id: orders.length + 1001,
        order_code: 'TS-' + Math.floor(100000 + Math.random() * 900000),
        customer_name: body.customer_name || 'Khách hàng',
        customer_phone: body.customer_phone || '0987654321',
        customer_email: body.customer_email || 'khach@techstore.vn',
        shipping_address: body.shipping_address || 'Địa chỉ giao hàng',
        payment_method: body.payment_method || 'vietqr', // 'vietqr' | 'cod'
        payment_status: 'pending', // 'pending' | 'paid'
        status: 'pending', // 'pending' | 'confirmed' | 'shipping' | 'completed' | 'cancelled'
        total_amount: body.total_amount || 0,
        discount_amount: body.discount_amount || 0,
        items: body.items || [],
        note: body.note || '',
        created_at: new Date().toISOString()
      };
      orders.unshift(newOrder);
      localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(orders));
      this.clearCart();
      return { success: true, message: 'Đặt hàng thành công', data: newOrder };
    }

    if (endpoint === '/orders' && method === 'GET') {
      const orders = this.getStoredOrders();
      return { success: true, data: orders };
    }

    if (endpoint.match(/\/orders\/(\d+)$/) && method === 'GET') {
      const id = parseInt(endpoint.match(/\/orders\/(\d+)$/)[1], 10);
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.id === id) || orders[0];
      return { success: true, data: ord };
    }

    if (endpoint.match(/\/orders\/(\d+)\/cancel/) && method === 'POST') {
      const id = parseInt(endpoint.match(/\/orders\/(\d+)\/cancel/)[1], 10);
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.id === id);
      if (ord) {
        ord.status = 'cancelled';
        localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(orders));
        return { success: true, message: 'Đã hủy đơn hàng' };
      }
      return { success: false, message: 'Không tìm thấy đơn hàng' };
    }

    // VIETQR PAYMENT
    if (endpoint === '/payments/qr' && method === 'POST') {
      const orderId = body.order_id;
      const amount = body.amount || 1000000;
      const orderCode = body.order_code || `TS-${orderId}`;
      const bank = 'MB'; // MBBank or Vietcombank
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
          expires_in: 900 // 15 mins
        }
      };
    }

    if (endpoint.match(/\/payments\/(\d+)\/status/) && method === 'GET') {
      const orderId = parseInt(endpoint.match(/\/payments\/(\d+)\/status/)[1], 10);
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

    // RECOMMENDATIONS
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

    // REVIEWS
    if (endpoint.includes('/reviews')) {
      return {
        success: true,
        data: [
          { id: 1, user_name: 'Nguyễn Văn Nam', rating: 5, comment: 'Hàng chuẩn chính hãng CellphoneS, giao nhanh trong 2h tại TP.HCM, đóng gói cẩn thận 10/10!', created_at: '2026-10-18' },
          { id: 2, user_name: 'Trần Thị Mai', rating: 5, comment: 'Máy dùng rất mượt mà, camera nét đỉnh chóp. Nhân viên tư vấn nhiệt tình.', created_at: '2026-10-15' },
          { id: 3, user_name: 'Lê Hoàng Minh', rating: 4, comment: 'Sản phẩm tốt trong tầm giá, quét VietQR thanh toán 5s là xong luôn.', created_at: '2026-10-12' }
        ]
      };
    }

    // ADMIN STATS
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

    if (endpoint === '/admin/users') {
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

    return { success: true, message: 'Mock response OK', data: {} };
  }

  // Cart Storage Helpers
  getCart() {
    const raw = localStorage.getItem(API_CONFIG.storageKeys.cart);
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
    cart.total = (cart.items || []).reduce((sum, item) => sum + (item.price * item.quantity), 0);
    localStorage.setItem(API_CONFIG.storageKeys.cart, JSON.stringify(cart));
    this.updateHeaderCartBadge();
  }

  clearCart() {
    localStorage.removeItem(API_CONFIG.storageKeys.cart);
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
  // HIGH LEVEL API METHODS (FULL API CONTRACT)
  // ==========================================
  auth = {
    login: (email, password) => this.request('/auth/login', { method: 'POST', body: JSON.stringify({ email, password }) }),
    register: (userData) => this.request('/auth/register', { method: 'POST', body: JSON.stringify(userData) }),
    logout: () => this.request('/auth/logout', { method: 'POST' }),
    me: () => this.request('/auth/me', { method: 'GET' })
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
    getAll: () => this.request('/categories', { method: 'GET' })
  };

  brands = {
    getAll: () => this.request('/brands', { method: 'GET' })
  };

  cart = {
    get: () => this.request('/cart', { method: 'GET' }),
    addItem: (productId, quantity = 1, variant = '') => this.request('/cart/items', { method: 'POST', body: JSON.stringify({ product_id: productId, quantity, variant }) }),
    updateItem: (itemId, quantity) => {
      const cart = this.getCart();
      const item = cart.items.find(i => i.id == itemId);
      if (item) {
        item.quantity = Math.max(1, quantity);
        this.saveCart(cart);
      }
      return Promise.resolve({ success: true, data: cart });
    },
    removeItem: (itemId) => {
      const cart = this.getCart();
      cart.items = cart.items.filter(i => i.id != itemId);
      this.saveCart(cart);
      return Promise.resolve({ success: true, data: cart });
    },
    clear: () => {
      this.clearCart();
      return Promise.resolve({ success: true });
    }
  };

  coupons = {
    validate: (code, total) => this.request('/coupons/validate', { method: 'POST', body: JSON.stringify({ code, total }) })
  };

  orders = {
    create: (orderData) => this.request('/orders', { method: 'POST', body: JSON.stringify(orderData) }),
    getAll: () => this.request('/orders', { method: 'GET' }),
    getById: (id) => this.request(`/orders/${id}`, { method: 'GET' }),
    cancel: (id) => this.request(`/orders/${id}/cancel`, { method: 'POST' }),
    updateStatus: (id, status, paymentStatus) => {
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.id == id);
      if (ord) {
        if (status) ord.status = status;
        if (paymentStatus) ord.payment_status = paymentStatus;
        localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(orders));
      }
      return Promise.resolve({ success: true, data: ord });
    }
  };

  payments = {
    generateQr: (orderId, amount, orderCode) => this.request('/payments/qr', { method: 'POST', body: JSON.stringify({ order_id: orderId, amount, order_code: orderCode }) }),
    getStatus: (orderId) => this.request(`/payments/${orderId}/status`, { method: 'GET' }),
    simulateSuccess: (orderId) => {
      const orders = this.getStoredOrders();
      const ord = orders.find(o => o.id == orderId);
      if (ord) {
        ord.payment_status = 'paid';
        ord.status = 'confirmed';
        localStorage.setItem(API_CONFIG.storageKeys.orders, JSON.stringify(orders));
      }
      return Promise.resolve({ success: true, message: 'Thanh toán thành công' });
    }
  };

  recommendations = {
    getSimilar: (productId) => this.request(`/products/${productId}/recommendations`, { method: 'GET' }),
    getBoughtTogether: (productId) => this.request(`/products/${productId}/recommendations`, { method: 'GET' }),
    getForMe: () => this.request('/recommendations/me', { method: 'GET' })
  };

  reviews = {
    getByProduct: (productId) => this.request(`/products/${productId}/reviews`, { method: 'GET' }),
    create: (productId, rating, comment) => {
      return Promise.resolve({
        success: true,
        message: 'Đã gửi đánh giá thành công!',
        data: { id: Date.now(), user_name: 'Bạn', rating, comment, created_at: new Date().toISOString().split('T')[0] }
      });
    }
  };

  events = {
    trackView: (productId) => this.request('/events/view', { method: 'POST', body: JSON.stringify({ product_id: productId }) })
  };

  admin = {
    getStats: () => this.request('/admin/stats', { method: 'GET' }),
    getUsers: () => this.request('/admin/users', { method: 'GET' }),
    updateUser: (id, data) => Promise.resolve({ success: true, message: 'Cập nhật người dùng thành công' })
  };
}

// Global API Instance
const api = new ApiClient();

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
    const items = (res.data || []).slice(0, 5);

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
