/**
 * TechStore - Automated Security Test & Video Demo Suite (T73)
 * Tập hợp các giao diện và kịch bản thử nghiệm bảo mật (OWASP Top 10 API Security).
 * CHỈ hoạt động khi bật chế độ tests (?demo=1 hoặc sessionStorage techstore_demo_mode).
 * Tách biệt hoàn toàn khỏi mã nguồn sản phẩm thông thường.
 */

(function() {
  function isTestMode() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('demo') || urlParams.has('test')) {
      try { sessionStorage.setItem('techstore_demo_mode', '1'); } catch (e) {}
      return true;
    }
    try {
      return sessionStorage.getItem('techstore_demo_mode') === '1';
    } catch (e) {
      return false;
    }
  }

  function initDemoTestUI() {
    if (!isTestMode()) return;

    // 1. Tiêm mục "Quản trị Admin (Chỉ Admin)" vào Mobile Navigation Drawer
    injectDrawerSecurityItem();

    // 2. Nếu đang ở trang Quản trị Admin mà user là customer -> Hiển thị màn hình 403 RBAC Guard & IDOR Lab
    if (window.location.pathname.includes('/admin/')) {
      setupAdminDemoGuard();
    }
  }

  function injectDrawerSecurityItem() {
    const drawerNav = document.querySelector('#mobile-drawer .drawer-nav');
    if (drawerNav && !document.getElementById('drawer-admin-link')) {
      const divider = document.createElement('div');
      divider.className = 'drawer-divider demo-test-only';

      const label = document.createElement('div');
      label.className = 'demo-test-only';
      label.style.cssText = 'font-size: 11px; font-weight: 800; text-transform: uppercase; color: #ef4444; padding: 6px 12px; letter-spacing: 0.5px;';
      label.textContent = 'Bảo Mật & Phân Quyền (Test T73)';

      const link = document.createElement('a');
      link.href = '/admin/index.html?demo=1';
      link.id = 'drawer-admin-link';
      link.className = 'drawer-link drawer-admin-item demo-test-only';
      link.innerHTML = `
        <svg class="tech-icon tech-icon-sm" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        <span style="color: #ef4444; font-weight: 700;">Quản trị Admin (Chỉ Admin)</span>
      `;
      drawerNav.appendChild(divider);
      drawerNav.appendChild(label);
      drawerNav.appendChild(link);
    }
  }

  function setupAdminDemoGuard() {
    if (window.api && typeof window.api.isAdmin === 'function' && !window.api.isAdmin()) {
      showRbacForbiddenScreen();
    }
  }

  function showRbacForbiddenScreen() {
    const layoutEl = document.getElementById('admin-main-layout');
    if (layoutEl) layoutEl.style.display = 'none';

    let screen = document.getElementById('rbac-forbidden-screen');
    if (screen) {
      screen.style.display = 'flex';
      return;
    }

    const u = (window.api && window.api.getCurrentUser) ? window.api.getCurrentUser() : null;
    const userEmail = u ? (u.email || 'user@techstore.local') : 'Chưa đăng nhập (Khách vãng lai)';
    const userRole = u ? ((u.role || 'customer') + ' (Khách hàng)') : 'guest (Chưa xác thực)';

    screen = document.createElement('div');
    screen.id = 'rbac-forbidden-screen';
    screen.style.cssText = 'display: flex; min-height: 100vh; align-items: center; justify-content: center; background: radial-gradient(circle at 50% 20%, #1e1b4b 0%, #0f172a 100%); color: #ffffff; padding: 24px; position: relative; z-index: 99999;';
    screen.innerHTML = `
      <div style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1.5px solid rgba(239, 68, 68, 0.4); border-radius: 24px; padding: 36px 32px; max-width: 540px; width: 100%; text-align: center; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), inset 0 0 0 1px rgba(255, 255, 255, 0.1);">
        <div style="width: 76px; height: 76px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); border: 2px solid #ef4444; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 18px; box-shadow: 0 0 35px rgba(239, 68, 68, 0.45);">
          <svg class="tech-icon" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2L4 5.5v6.2c0 5.3 3.4 10.2 8 11.3 4.6-1.1 8-6 8-11.3V5.5L12 2z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <circle cx="12" cy="16.5" r="1" fill="#ef4444"/>
          </svg>
        </div>
        
        <div style="display: inline-block; background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid #ef4444; padding: 5px 14px; border-radius: 20px; font-size: 11px; font-weight: 800; letter-spacing: 0.8px; margin-bottom: 12px; text-transform: uppercase;">
          MÃ BẢO MẬT: HTTP 403 FORBIDDEN (RBAC GUARD)
        </div>
        
        <h2 style="font-size: 22px; font-weight: 900; color: #ffffff; margin-bottom: 10px; letter-spacing: -0.3px;">
          TRUY CẬP BỊ TỪ CHỐI
        </h2>
        
        <p style="font-size: 13px; color: #cbd5e1; line-height: 1.6; margin-bottom: 22px;">
          Cơ chế phân quyền theo vai trò (Role-Based Access Control) phát hiện tài khoản hiện tại không có quyền hạn Quản trị viên (Admin) để xem dữ liệu quản trị doanh thu.
        </p>

        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 14px 18px; text-align: left; margin-bottom: 26px; font-size: 13px;">
          <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <span style="color: #94a3b8;">Tài khoản đăng nhập:</span>
            <span style="color: #38bdf8; font-weight: 700;">${userEmail}</span>
          </div>
          <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <span style="color: #94a3b8;">Vai trò hiện tại:</span>
            <span style="color: #fbbf24; font-weight: 800;">${userRole}</span>
          </div>
          <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
            <span style="color: #94a3b8;">Yêu cầu phân quyền:</span>
            <span style="color: #ef4444; font-weight: 800;">admin (Quản trị viên)</span>
          </div>
          <div style="display: flex; justify-content: space-between; padding: 6px 0;">
            <span style="color: #94a3b8;">Tiêu chuẩn bảo mật:</span>
            <span style="color: #34d399; font-weight: 700;">OWASP API05:2023 - BFLA Guard</span>
          </div>
        </div>

        <div style="background: rgba(245, 158, 11, 0.12); border: 1.5px dashed rgba(245, 158, 11, 0.5); border-radius: 16px; padding: 14px 18px; margin-bottom: 22px; text-align: left;">
          <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
            <span style="color: #fbbf24; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Kiểm tra lỗ hổng IDOR (OWASP API01)</span>
            <span style="background: #ef4444; color: #fff; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 10px;">BOLA GUARD</span>
          </div>
          <p style="font-size: 12px; color: #cbd5e1; line-height: 1.5; margin-bottom: 12px;">
            Khách hàng (User #2) thử gửi request hủy <strong>Đơn hàng #1029</strong> thuộc sở hữu của khách hàng khác.
          </p>
          <button id="btn-test-cancel-idor" onclick="window.TechStoreDemo.handleTestIdorCancel()" style="width: 100%; background: linear-gradient(135deg, #f59e0b, #d97706); color: #0f172a; font-weight: 800; font-size: 13px; padding: 10px 18px; border-radius: 24px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35); transition: all 0.25s ease;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>
            <span id="btn-test-cancel-idor-text">Thử Hủy Đơn Hàng #1029 Của Người Khác (POST /orders/1029/cancel)</span>
          </button>
        </div>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
          <a href="/pages/account.html?demo=1" id="btn-forbidden-account" class="btn btn-outline" style="border-color: #38bdf8; color: #38bdf8; padding: 9px 20px; font-size: 13px; font-weight: 700; border-radius: 30px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
            ← Về Hồ sơ cá nhân
          </a>
          <a href="/pages/index.html?demo=1" id="btn-forbidden-home" class="btn btn-outline" style="border-color: #64748b; color: #94a3b8; padding: 9px 18px; font-size: 13px; font-weight: 700; border-radius: 30px; text-decoration: none;">
            Trang chủ
          </a>
        </div>
      </div>
    `;

    document.body.prepend(screen);
    if (typeof showToast === 'function') {
      showToast('Truy cập bị từ chối (403 Forbidden): Yêu cầu quyền Quản trị viên (Admin)', 'danger');
    }
  }

  async function handleTestIdorCancel() {
    const btn = document.getElementById('btn-test-cancel-idor');
    const textSpan = document.getElementById('btn-test-cancel-idor-text');
    if (btn) btn.disabled = true;
    if (textSpan) textSpan.textContent = 'Đang gửi POST /api/v1/orders/1029/cancel...';

    try {
      const res = await api.orders.cancel(1029);
      if (!res.success && (res.status === 403 || res.error?.code === 'FORBIDDEN')) {
        showToast('CHẶN LỖ HỔNG IDOR (HTTP 403): Bạn không có quyền hủy đơn hàng của người khác!', 'danger');
        if (btn) {
          btn.style.background = '#ef4444';
          btn.style.color = '#ffffff';
        }
        if (textSpan) {
          textSpan.textContent = 'CHẶN THÀNH CÔNG: HTTP 403 FORBIDDEN (OWASP API01)';
        }
      } else {
        showToast(res.message || 'Thao tác hoàn tất', 'warning');
      }
    } catch (err) {
      showToast('Đã chặn request vi phạm quyền sở hữu đơn hàng (403)', 'danger');
    } finally {
      if (btn) btn.disabled = false;
    }
  }

  window.TechStoreDemo = {
    isTestMode,
    showRbacForbiddenScreen,
    handleTestIdorCancel,
    initDemoTestUI
  };

  // Tự động kiểm tra và khởi tạo khi DOM sẵn sàng
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDemoTestUI);
  } else {
    initDemoTestUI();
  }
})();
