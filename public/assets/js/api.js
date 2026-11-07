/**
 * TechStore - Central REST API Client & Helper Utilities
 * Direct Cloud Database RESTful API (/api/v1 - TiDB Cloud MySQL)
 * Implements 100% of the 37 APIs from Project Plan Sheet 8
 * Author: Tuan Kiet (Front-end & Documentation)
 */

const API_CONFIG = {
  baseUrl: '/api/v1',
  timeout: 10000,
  storageKeys: {
    accessToken: 'techstore_access_token',
    refreshToken: 'techstore_refresh_token',
    user: 'techstore_user',
    cart: 'techstore_cart'
  }
};

// Dọn dẹp toàn bộ dữ liệu mock fake cũ trong LocalStorage để đồng bộ 100% dữ liệu Cloud Database thật
try {
  [
    'techstore_products_db',
    'techstore_reviews_db',
    'techstore_categories_db',
    'techstore_brands_db',
    'techstore_coupons_db',
    'techstore_audit_logs',
    'techstore_orders',
    'techstore_wishlist'
  ].forEach(k => localStorage.removeItem(k));
} catch (e) {}

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
  
  let iconSvg = '';
  if (typeof TechIcons !== 'undefined') {
    if (type === 'success') iconSvg = TechIcons.render('check-circle', { size: 20, className: 'tech-icon-green' });
    else if (type === 'danger' || type === 'error') iconSvg = TechIcons.render('alert-triangle', { size: 20, className: 'tech-icon-primary' });
    else if (type === 'warning') iconSvg = TechIcons.render('bolt', { size: 20, className: 'tech-icon-gold' });
    else iconSvg = TechIcons.render('info-circle', { size: 20, className: 'tech-icon-secondary' });
  } else {
    if (type === 'success') {
      iconSvg = '<svg class="tech-icon tech-icon-green" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M8.5 12.5l2.5 2.5 5-5" stroke-width="2.2"/></svg>';
    } else if (type === 'danger' || type === 'error') {
      iconSvg = '<svg class="tech-icon tech-icon-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><path d="M12 9v4M12 17h.01"/></svg>';
    } else if (type === 'warning') {
      iconSvg = '<svg class="tech-icon tech-icon-gold" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z"/></svg>';
    } else {
      iconSvg = '<svg class="tech-icon tech-icon-secondary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8h.01M12 11v5"/></svg>';
    }
  }

  toast.innerHTML = `<span class="toast-icon-wrap">${iconSvg}</span> <span class="toast-msg">${message}</span>`;
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
// CORE API CLIENT (REAL CLOUD DATABASE)
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
      const user = JSON.parse(raw);
      if (user) {
        let changed = false;
        ['name', 'address'].forEach(key => {
          if (typeof user[key] === 'string' && user[key].includes('?')) {
            const healed = user[key]
              .replace(/Tu\?+n\s*Ki\?+t/gi, 'Tuấn Kiệt')
              .replace(/H\?+u\s*Nam/gi, 'Hữu Nam')
              .replace(/Th\?+nh\s*Trung/gi, 'Thành Trung')
              .replace(/Kh\?+ch\s*h\?+ng/gi, 'Khách hàng')
              .replace(/Nguy\?+n\s*V\?+n\s*C\?+/gi, 'Nguyễn Văn Cừ')
              .replace(/Qu\?+n\s*5/gi, 'Quận 5')
              .replace(/C\?+ch\s*M\?+ng\s*Th\?+ng\s*8/gi, 'Cách Mạng Tháng 8')
              .replace(/Ph\?+ng\s*7/gi, 'Phường 7')
              .replace(/Qu\?+n\s*10/gi, 'Quận 10');
            if (healed !== user[key]) {
              user[key] = healed;
              changed = true;
            }
          }
        });
        if (changed) {
          localStorage.setItem(API_CONFIG.storageKeys.user, JSON.stringify(user));
        }
      }
      return user;
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

  // Core Request Wrapper (Direct Cloud Database Backend)
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
      let json = null;
      try {
        json = text ? JSON.parse(text) : {};
      } catch (parseErr) {
        return {
          success: false,
          error: {
            code: 'INVALID_JSON_RESPONSE',
            message: text || 'Phản hồi từ máy chủ không hợp lệ'
          }
        };
      }

      return json;
    } catch (err) {
      console.error(`[API Network Error] ${endpoint}:`, err);
      return {
        success: false,
        error: {
          code: 'NETWORK_ERROR',
          message: err.message || 'Không thể kết nối đến máy chủ Cloud Database API'
        }
      };
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
    google: async (payload) => {
      const body = typeof payload === 'string' ? { token: payload } : payload;
      const res = await this.request('/auth/google', { method: 'POST', body: JSON.stringify(body) });
      if (res && res.success && res.data) {
        const { access_token, refresh_token, user } = res.data;
        if (access_token || user) {
          this.setAuthTokens(access_token, refresh_token, user);
        }
      }
      return res;
    },
    getGoogleClientId: async () => {
      try {
        const res = await this.request('/auth/google/client-id', { method: 'GET' });
        return res?.data?.client_id || '';
      } catch (e) {
        return '';
      }
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
    addItem: async (productId, quantity = 1, variant = '', customPrice = null) => {
      productId = parseInt(productId, 10);
      quantity = Math.max(1, parseInt(quantity, 10) || 1);
      
      const cart = this.getCart();
      let prod = (window.allProducts || []).find(p => p.id === productId);
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
        const finalPrice = (customPrice && Number(customPrice) > 0) ? Number(customPrice) : prod.price;
        const existing = cart.items.find(i => i.product_id === prod.id && (i.variant || 'Mặc định') === targetVariant);
        if (existing) {
          existing.quantity += quantity;
          existing.price = finalPrice;
        } else {
          cart.items.push({
            id: Date.now() + Math.floor(Math.random() * 100),
            product_id: prod.id,
            name: prod.name,
            price: finalPrice,
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
          const finalPrice = (customPrice && Number(customPrice) > 0) ? Number(customPrice) : (prod ? prod.price : null);
          const res = await this.request('/cart/items', { 
            method: 'POST', 
            body: JSON.stringify({ product_id: productId, quantity, variant, price: finalPrice }) 
          });
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
    add: (productId) => this.request('/wishlist', { method: 'POST', body: JSON.stringify({ product_id: parseInt(productId, 10) }) }),
    remove: (productId) => this.request(`/wishlist/${parseInt(productId, 10)}`, { method: 'DELETE' }),
    toggle: async (productId) => {
      productId = parseInt(productId, 10);
      if (!this.isLoggedIn()) {
        showToast('Vui lòng đăng nhập để lưu sản phẩm yêu thích', 'warning');
        return { active: false };
      }
      try {
        const res = await this.wishlist.get();
        const list = (res && res.data && (Array.isArray(res.data) ? res.data : res.data.items)) || [];
        const exists = list.some(item => (item.product_id || item.id) === productId);
        if (exists) {
          await this.wishlist.remove(productId);
          return { active: false };
        } else {
          await this.wishlist.add(productId);
          return { active: true };
        }
      } catch (err) {
        console.error('Wishlist toggle error:', err);
        return { active: false };
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
      if (typeof TechIcons !== 'undefined') {
        btn.innerHTML = TechIcons.render(res.active ? 'heart-filled' : 'heart', { size: 18 });
      } else {
        btn.style.color = res.active ? '#d70018' : '#94a3b8';
      }
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

  const updateAdminElements = (role) => {
    const isAdmin = role === 'admin';
    const adminLink = document.getElementById('dropdown-admin-link');
    if (adminLink) {
      adminLink.style.display = isAdmin ? 'flex' : 'none';
    }
    document.querySelectorAll('.topbar-admin-link, #topbar-admin-link').forEach(el => {
      el.style.display = isAdmin ? 'inline-flex' : 'none';
    });
    document.querySelectorAll('.drawer-admin-item, #drawer-admin-link').forEach(el => {
      el.style.display = isAdmin ? 'flex' : 'none';
    });
  };

  const user = api.getCurrentUser();
  if (user) {
    const nameEl = document.getElementById('dropdown-name');
    const roleEl = document.getElementById('dropdown-role');
    if (nameEl) nameEl.textContent = user.name;
    if (roleEl) roleEl.textContent = user.role === 'admin' ? 'Quản trị viên' : 'Khách hàng';

    // Ẩn/hiện theo vai trò thực tế
    updateAdminElements(user.role);

    // Asynchronously refresh user profile from backend to ensure latest DB synchronization
    if (api.isLoggedIn()) {
      api.auth.me().then(res => {
        if (res && res.success && res.data) {
          const freshUser = res.data;
          api.setAuthTokens(null, null, freshUser);
          if (nameEl) nameEl.textContent = freshUser.name;
          if (roleEl) roleEl.textContent = freshUser.role === 'admin' ? 'Quản trị viên' : 'Khách hàng';
          updateAdminElements(freshUser.role);
        }
      }).catch(() => {});
    }

    const loginLinks = document.querySelectorAll('.guest-only');
    loginLinks.forEach(el => el.style.display = 'none');
    const userLinks = document.querySelectorAll('.auth-only');
    userLinks.forEach(el => el.style.display = 'flex');

    // Chắc chắn phần quản trị hệ thống bị ẩn hoàn toàn đối với tài khoản khách hàng
    updateAdminElements(user.role);
  } else {
    updateAdminElements(null);

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

// ===================================================
// HAMBURGER MENU & MOBILE NAVIGATION DRAWER
// ===================================================
function toggleMobileDrawer() {
  const drawer = document.getElementById('mobile-drawer');
  const overlay = document.getElementById('mobile-drawer-overlay');
  if (drawer && overlay) {
    const isActive = drawer.classList.contains('active');
    if (isActive) {
      drawer.classList.remove('active');
      overlay.classList.remove('active');
    } else {
      drawer.classList.add('active');
      overlay.classList.add('active');
    }
  }
}
window.toggleMobileDrawer = toggleMobileDrawer;

function initMobileDrawer() {
  const headerContainer = document.querySelector('.header-container');
  if (headerContainer && !document.getElementById('btn-hamburger-menu')) {
    const btn = document.createElement('button');
    btn.id = 'btn-hamburger-menu';
    btn.className = 'btn-hamburger';
    btn.setAttribute('aria-label', 'Mở menu điều hướng');
    btn.onclick = toggleMobileDrawer;
    btn.innerHTML = `
      <svg class="tech-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
        <line x1="3" y1="6" x2="21" y2="6"/>
        <line x1="3" y1="12" x2="21" y2="12"/>
        <line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    `;
    headerContainer.prepend(btn);
  }

  if (!document.getElementById('mobile-drawer')) {
    const overlay = document.createElement('div');
    overlay.id = 'mobile-drawer-overlay';
    overlay.className = 'mobile-drawer-overlay';
    overlay.onclick = toggleMobileDrawer;

    const drawer = document.createElement('aside');
    drawer.id = 'mobile-drawer';
    drawer.className = 'mobile-drawer';
    drawer.innerHTML = `
      <div class="drawer-header">
        <div style="display: flex; align-items: center; gap: 8px;">
          <span style="font-size: 18px; font-weight: 900; letter-spacing: -0.5px;">TechStore</span>
          <span class="logo-badge" style="font-size: 11px; padding: 2px 6px;">REST</span>
        </div>
        <button class="drawer-close-btn" onclick="toggleMobileDrawer()">&times;</button>
      </div>
      <nav class="drawer-nav">
        <a href="/pages/index.html" class="drawer-link">
          <svg class="tech-icon tech-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <span>Trang chủ</span>
        </a>
        <a href="/pages/products.html" class="drawer-link">
          <svg class="tech-icon tech-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          <span>Tất cả sản phẩm</span>
        </a>
        <a href="/pages/cart.html" class="drawer-link">
          <svg class="tech-icon tech-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 4h2l2.2 10.3a2 2 0 002 1.7h8.8a2 2 0 002-1.6L21.5 7H6"/><circle cx="9.5" cy="19.5" r="1.5"/><circle cx="17.5" cy="19.5" r="1.5"/></svg>
          <span>Giỏ hàng</span>
        </a>
        <a href="/pages/account.html#orders" class="drawer-link" id="drawer-orders-link">
          <svg class="tech-icon tech-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l9 4.8v8.4l-9 4.8-9-4.8V7.8L12 3z"/><path d="M12 3v9.6M21 7.8l-9 4.8M3 7.8l9 4.8"/></svg>
          <span>Lịch sử đơn hàng</span>
        </a>
        <a href="/pages/account.html" class="drawer-link">
          <svg class="tech-icon tech-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M5 19.5a7 7 0 0114 0"/></svg>
          <span>Hồ sơ tài khoản</span>
        </a>
      </nav>
    `;
    document.body.appendChild(overlay);
    document.body.appendChild(drawer);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initAmbientAurora();
    initMobileDrawer();
  });
} else {
  initAmbientAurora();
  initMobileDrawer();
}

// Tự động nạp bộ thử nghiệm kiểm thử bảo mật T73 khi phát hiện cờ ?demo=1 hoặc sessionStorage
(function() {
  try {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('demo') || urlParams.has('test') || sessionStorage.getItem('techstore_demo_mode') === '1') {
      sessionStorage.setItem('techstore_demo_mode', '1');
      if (!document.getElementById('techstore-demo-suite-script')) {
        const script = document.createElement('script');
        script.id = 'techstore-demo-suite-script';
        script.src = '/assets/js/demo-test-suite.js';
        document.head.appendChild(script);
      }
    }
  } catch (e) {}
})();

// Cơ chế tự phục hồi đường dẫn ảnh (Image Path Auto-Healing)
// Đảm bảo tương thích tuyệt đối cho cả Web Server (/assets/images/) lẫn mở file cục bộ (../assets/images/)
window.addEventListener('error', function (e) {
  if (e.target && e.target.tagName === 'IMG') {
    const img = e.target;
    if (img.dataset.autoHealed) return;
    img.dataset.autoHealed = '1';

    const rawSrc = img.getAttribute('src') || '';
    if (rawSrc.startsWith('/assets/images/')) {
      img.src = '../assets/images/' + rawSrc.replace('/assets/images/', '');
    } else if (rawSrc.startsWith('../assets/images/')) {
      img.src = '/assets/images/' + rawSrc.replace('../assets/images/', '');
    } else if (rawSrc.startsWith('assets/images/')) {
      img.src = '/assets/images/' + rawSrc.replace('assets/images/', '');
    } else {
      img.src = '/assets/images/iphone-16-128gb.png';
    }
  }
}, true);

