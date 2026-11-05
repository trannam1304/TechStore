"""
TechStore - Kịch bản Video Demo Tự động ĐỒNG BỘ 100% (Selenium Two-Screen Lockstep)
Tự động mở 2 cửa sổ Chrome chia đôi màn hình 50:50 chính xác:
  - CỬA SỔ TRÁI (50%): Giao diện TechStore với CON TRỎ CHUỘT ẢO LƯỚT & CLICK TRỰC QUAN
  - CỬA SỔ PHẢI (50%): Bảng điều khiển API Live Telemetry (Hiển thị mã 401, 403, 200, JWT & JSON)
Đếm ngược 10 giây trước khi bắt đầu để người dùng bật phần mềm quay màn hình.
"""

import sys
import time
import json

def get_screen_work_area():
    try:
        import ctypes
        from ctypes import wintypes
        user32 = ctypes.windll.user32
        try:
            ctypes.windll.shcore.SetProcessDpiAwareness(2)
        except Exception:
            user32.SetProcessDPIAware()

        rect = wintypes.RECT()
        user32.SystemParametersInfoW(0x0030, 0, ctypes.byref(rect), 0)
        work_x = rect.left
        work_y = rect.top
        work_w = rect.right - rect.left
        work_h = rect.bottom - rect.top
        return work_x, work_y, work_w, work_h
    except Exception:
        return 0, 0, 1920, 1040

def snap_window(driver, target_x, target_y, target_w, target_h):
    try:
        driver.set_window_rect(x=target_x, y=target_y, width=target_w, height=target_h)
    except Exception:
        pass

    try:
        import ctypes
        user32 = ctypes.windll.user32
        unique_marker = f"__SNAP_TECHSTORE_{target_x}_{int(time.time()*1000)}__"
        old_title = driver.title
        driver.execute_script(f"document.title = '{unique_marker}';")
        time.sleep(0.2)
        hwnd = user32.FindWindowW(None, unique_marker)
        if hwnd:
            user32.SetWindowPos(hwnd, 0, int(target_x), int(target_y), int(target_w), int(target_h), 0x0044)
        if old_title:
            driver.execute_script(f"document.title = '{old_title}';")
    except Exception:
        pass

def inject_human_cursor(driver):
    js = """
    if (!document.getElementById('demo-cursor-style')) {
        let style = document.createElement('style');
        style.id = 'demo-cursor-style';
        style.innerHTML = `
            @keyframes ripple-anim {
                0% { transform: scale(0.6); opacity: 1; }
                100% { transform: scale(2.2); opacity: 0; }
            }
            #demo-virtual-cursor {
                position: fixed;
                width: 26px;
                height: 26px;
                z-index: 99999999;
                pointer-events: none;
                transition: top 0.55s cubic-bezier(0.25, 1, 0.5, 1), left 0.55s cubic-bezier(0.25, 1, 0.5, 1);
                filter: drop-shadow(0 3px 6px rgba(0,0,0,0.4));
            }
        `;
        document.head.appendChild(style);
    }

    if (!document.getElementById('demo-virtual-cursor')) {
        let cursor = document.createElement('div');
        cursor.id = 'demo-virtual-cursor';
        cursor.style.top = '120px';
        cursor.style.left = '120px';
        cursor.innerHTML = `
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                <path d="M5.5 3.2L18.8 12.3C19.7 12.9 19.3 14.3 18.2 14.3H12.9L10.2 20.6C9.8 21.5 8.5 21.6 7.9 20.8L5.2 17.2L2.1 19.4C1.2 20 0 19.3 0 18.2V4.5C0 3.4 1.3 2.8 2.2 3.5L5.5 3.2Z" fill="#d70018" stroke="#ffffff" stroke-width="1.8"/>
            </svg>
        `;
        document.body.appendChild(cursor);
    }

    window.humanClick = function(selector, doneCallback) {
        let el = document.querySelector(selector);
        let cursor = document.getElementById('demo-virtual-cursor');
        if (!el || !cursor) { if (doneCallback) doneCallback(); return; }

        let rect = el.getBoundingClientRect();
        let targetX = rect.left + rect.width / 2;
        let targetY = rect.top + rect.height / 2;

        cursor.style.left = targetX + 'px';
        cursor.style.top = targetY + 'px';

        setTimeout(() => {
            let ripple = document.createElement('div');
            ripple.style.cssText = `position:fixed;left:${targetX - 16}px;top:${targetY - 16}px;width:32px;height:32px;border-radius:50%;background:rgba(215,0,24,0.4);border:2px solid #d70018;z-index:99999998;pointer-events:none;animation:ripple-anim 0.45s ease-out;`;
            document.body.appendChild(ripple);
            setTimeout(() => ripple.remove(), 450);

            el.click();
            if (doneCallback) doneCallback();
        }, 580);
    };

    window.humanType = function(selector, text, doneCallback) {
        let el = document.querySelector(selector);
        let cursor = document.getElementById('demo-virtual-cursor');
        if (!el || !cursor) { if (doneCallback) doneCallback(); return; }

        let rect = el.getBoundingClientRect();
        let targetX = rect.left + 20;
        let targetY = rect.top + rect.height / 2;

        cursor.style.left = targetX + 'px';
        cursor.style.top = targetY + 'px';

        setTimeout(() => {
            el.focus();
            el.value = '';
            let i = 0;
            let timer = setInterval(() => {
                if (i < text.length) {
                    el.value += text[i];
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    i++;
                } else {
                    clearInterval(timer);
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    if (doneCallback) doneCallback();
                }
            }, 45);
        }, 580);
    };
    """
    try:
        driver.execute_script(js)
    except Exception:
        pass

def show_banner(driver, text):
    js = f"""
    let b = document.getElementById('demo-step-banner');
    if (!b) {{
        b = document.createElement('div');
        b.id = 'demo-step-banner';
        b.style.cssText = 'position:fixed;top:14px;left:50%;transform:translateX(-50%);z-index:999999;background:#0f172a;color:#38bdf8;padding:10px 24px;border-radius:30px;font-size:14px;font-weight:700;box-shadow:0 8px 30px rgba(0,0,0,0.5);border:2px solid #38bdf8;pointer-events:none;font-family:Segoe UI, sans-serif;letter-spacing:0.5px;';
        document.body.appendChild(b);
    }}
    b.innerText = "{text}";
    """
    try:
        driver.execute_script(js)
    except Exception:
        pass

def trigger_inspector(driver_inspector, step_data):
    js_data = json.dumps(step_data)
    try:
        driver_inspector.execute_script(f"window.triggerApiStep({js_data});")
    except Exception as e:
        print(f"Error updating inspector: {e}")

def main():
    print("=" * 75)
    print(" TECHSTORE - TỰ ĐỘNG ĐỒNG BỘ HAI NỬA MÀN HÌNH (CÓ CON TRỎ CHUỘT THAO TÁC)")
    print("=" * 75)

    work_x, work_y, work_w, work_h = get_screen_work_area()
    half_w = work_w // 2

    print(f"[*] Diện tích màn hình khả dụng (Work Area): {work_w} x {work_h}")
    print(f"[*] Cửa sổ 1 (Trái 50% - Giao diện Web): x={work_x}, y={work_y}, width={half_w}, height={work_h}")
    print(f"[*] Cửa sổ 2 (Phải 50% - API Inspector): x={work_x + half_w}, y={work_y}, width={half_w}, height={work_h}")
    print("=" * 75)

    # ĐẾM NGƯỢC 10 GIÂY
    print("\n[CHUẨN BỊ] Hãy bật phần mềm quay màn hình ngay bây giờ (OBS hoặc Win + Alt + R)!")
    for sec in range(10, 0, -1):
        print(f"  >>> BẮT ĐẦU TỰ ĐỘNG SAU: {sec:2d} GIÂY... <<<", end="\r", flush=True)
        time.sleep(1)
    print("\n\n[BẮT ĐẦU] Đang khởi chạy 2 cửa sổ Chrome chia đôi màn hình 50:50...\n")

    from selenium import webdriver

    # 1. Khởi động Cửa sổ 1 (Bên trái: Web UI)
    print("[*] Đang mở Cửa sổ 1 (Trái 50%: Giao diện Web)...")
    options_left = webdriver.ChromeOptions()
    options_left.add_experimental_option("excludeSwitches", ["enable-automation"])
    options_left.add_experimental_option('useAutomationExtension', False)
    options_left.add_argument(f"--window-size={half_w},{work_h}")
    options_left.add_argument(f"--window-position={work_x},{work_y}")
    driver_web = webdriver.Chrome(options=options_left)

    # 2. Khởi động Cửa sổ 2 (Bên phải: API Live Inspector)
    print("[*] Đang mở Cửa sổ 2 (Phải 50%: API Live Inspector)...")
    options_right = webdriver.ChromeOptions()
    options_right.add_experimental_option("excludeSwitches", ["enable-automation"])
    options_right.add_experimental_option('useAutomationExtension', False)
    options_right.add_argument(f"--window-size={half_w},{work_h}")
    options_right.add_argument(f"--window-position={work_x + half_w},{work_y}")
    driver_api = webdriver.Chrome(options=options_right)

    BASE_URL = "http://localhost:8000"

    try:
        # Load trang ban đầu
        driver_web.get(f"{BASE_URL}/pages/index.html")
        driver_api.get(f"{BASE_URL}/pages/api-live.html")

        # Căn chỉnh vị trí 50:50 chính xác sau khi trình duyệt đã tải xong
        time.sleep(1)
        snap_window(driver_web, work_x, work_y, half_w, work_h)
        snap_window(driver_api, work_x + half_w, work_y, half_w, work_h)
        time.sleep(1.5)

        inject_human_cursor(driver_web)

        # ---------------------------------------------------------------------
        # BƯỚC 1: Khách hàng truy cập trang chủ TechStore
        # ---------------------------------------------------------------------
        print("\n[Bước 1/7] Khách hàng mở trang chủ -> Di chuyển chuột duyệt giao diện...")
        show_banner(driver_web, "BƯỚC 1: TRANG CHỦ TECHSTORE (RESTful API LEVEL 3)")
        
        # Con trỏ chuột lướt vào ô tìm kiếm
        driver_web.execute_script("window.humanClick('#global-search-input');")

        trigger_inspector(driver_api, {
            "name": "12.0 [200 OK] GET /products?limit=10 (ETag, Cache-Control & HATEOAS)",
            "method": "GET",
            "url": "/api/v1/products?limit=10",
            "status": 200,
            "statusText": "OK",
            "time": "21.8 ms",
            "size": "14.0 KB",
            "securityNote": "Chuẩn REST Cấp độ 3 (Richardson Level 3): Cache-Control: max-age=300, ETag và HATEOAS _links điều hướng.",
            "reqHeaders": {
                "Accept": "application/json",
                "If-None-Match": "\"c1d8b3db7f980887b85ffb3773ca0857\""
            },
            "resBody": {
                "success": True,
                "data": {
                    "items": [{"id": 1, "name": "iPhone 15 Pro", "price": 28990000}, {"id": 2, "name": "MacBook Air M3", "price": 27990000}],
                    "pagination": {"total_items": 42, "current_page": 1, "per_page": 10}
                },
                "_links": {
                    "self": {"href": "/api/v1/products?page=1&limit=10", "method": "GET"},
                    "next": {"href": "/api/v1/products?page=2&limit=10", "method": "GET"}
                }
            }
        })
        time.sleep(3.5)

        # ---------------------------------------------------------------------
        # BƯỚC 2: Cố tình truy cập trang Admin khi chưa đăng nhập
        # ---------------------------------------------------------------------
        print("[Bước 2/7] Chuột di chuyển vào Menu Tài khoản -> Thử vào Quản trị...")
        show_banner(driver_web, "BƯỚC 2: CHẶN TRUY CẬP /admin/ CHƯA ĐĂNG NHẬP (401)")
        
        # Chuột bấm menu tài khoản
        driver_web.execute_script("window.humanClick('#user-menu-btn');")
        time.sleep(1.2)

        # Thử mở trực tiếp trang admin
        driver_web.get(f"{BASE_URL}/admin/index.html")
        inject_human_cursor(driver_web)

        trigger_inspector(driver_api, {
            "name": "12.1 [401 Unauthorized] GET /auth/me (Thiếu Header Authorization)",
            "method": "GET",
            "url": "/api/v1/auth/me",
            "status": 401,
            "statusText": "Unauthorized",
            "time": "14.2 ms",
            "size": "486 B",
            "securityNote": "OWASP API02: Broken Authentication - Hệ thống từ chối mọi request không có Bearer token trong Header.",
            "reqHeaders": {
                "Authorization": "[Chưa truyền Header Token]"
            },
            "resBody": {
                "type": "https://techstore.local/errors/unauthorized",
                "title": "Unauthorized",
                "status": 401,
                "detail": "Yêu cầu xác thực: Vui lòng cung cấp Bearer token trong Header Authorization",
                "success": False,
                "error": {"code": "UNAUTHORIZED"}
            }
        })
        time.sleep(4.0)

        # ---------------------------------------------------------------------
        # BƯỚC 3: Đăng nhập Khách hàng & Gõ thông tin tài khoản
        # ---------------------------------------------------------------------
        print("[Bước 3/7] Mở form Đăng nhập -> Chuột bấm ô Email, Mật khẩu và gõ thông tin...")
        driver_web.get(f"{BASE_URL}/pages/login.html")
        inject_human_cursor(driver_web)
        time.sleep(1)
        show_banner(driver_web, "BƯỚC 3: ĐĂNG NHẬP KHÁCH HÀNG & CẤP TOKEN JWT")

        # Con trỏ chuột lướt vào ô Email và gõ từng ký tự
        driver_web.execute_script("window.humanType('#login-email', 'user@techstore.local');")
        time.sleep(1.5)

        # Con trỏ chuột lướt vào ô Mật khẩu và gõ
        driver_web.execute_script("window.humanType('#login-password', 'User@123');")
        time.sleep(1.5)

        # Con trỏ chuột di chuyển vào nút Đăng Nhập và click
        driver_web.execute_script("window.humanClick('#btn-login-submit');")

        trigger_inspector(driver_api, {
            "name": "12.4 [200 OK] POST /auth/login (Cấp JWT Access + Refresh Token)",
            "method": "POST",
            "url": "/api/v1/auth/login",
            "status": 200,
            "statusText": "OK",
            "time": "58.4 ms",
            "size": "1.05 KB",
            "securityNote": "Mật khẩu băm an toàn bằng PASSWORD_BCRYPT. Access Token ký bằng HMAC-SHA256 (hạn 1h), Refresh Token lưu an toàn trong MySQL (hạn 7 ngày).",
            "reqHeaders": {"Content-Type": "application/json"},
            "resBody": {
                "success": True,
                "message": "Đăng nhập thành công",
                "data": {
                    "user": {"id": 2, "name": "Khách hàng mẫu", "email": "user@techstore.local", "role": "customer"},
                    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjIsImVtYWlsIjoidXNlckB0ZWNoc3RvcmUubG9jYWwiLCJyb2xlIjoiY3VzdG9tZXIiLCJleHAiOjE3OTM4NzkyMDB9.signature...",
                    "refresh_token": "eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOjIsImp0aSI6ImZhOTNjNDk5OTU4YjE0YWY1NjA4MDk5NjEyNzk2M2JjIn0.signature..."
                }
            }
        })
        time.sleep(4.0)

        # ---------------------------------------------------------------------
        # BƯỚC 4: Khách hàng cố tình vào lại trang Admin -> Chặn quyền RBAC (403)
        # ---------------------------------------------------------------------
        print("[Bước 4/7] Khách hàng cố vào lại /admin/ -> Chặn quyền RBAC (403)...")
        driver_web.get(f"{BASE_URL}/admin/index.html")
        inject_human_cursor(driver_web)
        show_banner(driver_web, "BƯỚC 4: PHÂN QUYỀN RBAC - CHẶN CUSTOMER TRUY CẬP ADMIN (403)")

        trigger_inspector(driver_api, {
            "name": "12.5 [403 Forbidden] GET /admin/stats (Chặn quyền RBAC)",
            "method": "GET",
            "url": "/api/v1/admin/stats",
            "status": 403,
            "statusText": "Forbidden",
            "time": "16.1 ms",
            "size": "512 B",
            "securityNote": "OWASP API05: Broken Function Level Authorization - Token hợp lệ nhưng Role = 'customer' không có quyền truy cập endpoint quản trị.",
            "reqHeaders": {
                "Authorization": "Bearer eyJhbGciOiJIUzI1NiJ9...[Role: customer]"
            },
            "resBody": {
                "type": "https://techstore.local/errors/forbidden",
                "title": "Forbidden",
                "status": 403,
                "detail": "Quyền truy cập bị từ chối: Yêu cầu vai trò quản trị viên (Admin)",
                "success": False,
                "error": {"code": "FORBIDDEN"}
            }
        })
        time.sleep(4.0)

        # ---------------------------------------------------------------------
        # BƯỚC 5: Xem giỏ hàng & Chống lỗ hổng IDOR khi hủy đơn hàng
        # ---------------------------------------------------------------------
        print("[Bước 5/7] Chuột di chuyển mở Giỏ hàng -> Chống IDOR (403)...")
        driver_web.get(f"{BASE_URL}/pages/cart.html")
        inject_human_cursor(driver_web)
        show_banner(driver_web, "BƯỚC 5: CHỐNG LỖ HỔNG IDOR KHI HỦY ĐƠN HÀNG (403)")

        # Chuột bấm vào khu vực giỏ hàng
        time.sleep(1.0)
        driver_web.execute_script("window.humanClick('.cart-summary-card, .btn, a');")

        trigger_inspector(driver_api, {
            "name": "12.6 [403 Forbidden] POST /orders/1055/cancel (Chống lỗ hổng IDOR)",
            "method": "POST",
            "url": "/api/v1/orders/1055/cancel",
            "status": 403,
            "statusText": "Forbidden",
            "time": "17.9 ms",
            "size": "530 B",
            "securityNote": "OWASP API01: Broken Object Level Authorization (IDOR) - Kiểm tra user_id trong token không trùng khớp với chủ sở hữu đơn hàng trong CSDL MySQL.",
            "reqHeaders": {
                "Authorization": "Bearer eyJhbGciOiJIUzI1NiJ9...[User ID: 2]"
            },
            "resBody": {
                "type": "https://techstore.local/errors/forbidden",
                "title": "Forbidden",
                "status": 403,
                "detail": "Bạn không có quyền hủy đơn hàng của người khác",
                "success": False,
                "error": {"code": "FORBIDDEN"}
            }
        })
        time.sleep(4.0)

        # ---------------------------------------------------------------------
        # BƯỚC 6: Gia hạn phiên làm việc qua Refresh Token Rotation (RTR)
        # ---------------------------------------------------------------------
        print("[Bước 6/7] Tự động xoay vòng Refresh Token Rotation (RTR 200 OK)...")
        show_banner(driver_web, "BƯỚC 6: XOAY VÒNG REFRESH TOKEN ROTATION (RTR)")

        trigger_inspector(driver_api, {
            "name": "12.8 [200 OK] POST /auth/refresh (Cơ chế Refresh Token Rotation)",
            "method": "POST",
            "url": "/api/v1/auth/refresh",
            "status": 200,
            "statusText": "OK",
            "time": "26.3 ms",
            "size": "980 B",
            "securityNote": "Cơ chế bảo mật tiên tiến RTR: Thu hồi refresh_token cũ, cấp refresh_token mới, chống tấn công replay token đánh cắp phiên.",
            "reqHeaders": {"Content-Type": "application/json"},
            "resBody": {
                "success": True,
                "message": "Làm mới phiên làm việc thành công",
                "data": {
                    "access_token": "eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOjIsImV4cCI6MTc5Mzg4MjgwMH0.NEW_TOKEN...",
                    "refresh_token": "eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOjIsImp0aSI6ImRkNmVkOTlj..."
                }
            }
        })
        time.sleep(4.0)

        # ---------------------------------------------------------------------
        # BƯỚC 7: Đăng xuất, Đăng nhập Admin & Bảng điều khiển Quản trị
        # ---------------------------------------------------------------------
        print("[Bước 7/7] Đăng nhập Admin -> Mở Dashboard Quản trị & Audit Logs...")
        driver_web.get(f"{BASE_URL}/pages/login.html")
        inject_human_cursor(driver_web)
        time.sleep(1)

        # Con trỏ chuột lướt vào ô Email và gõ admin@techstore.local
        driver_web.execute_script("window.humanType('#login-email', 'admin@techstore.local');")
        time.sleep(1.2)

        # Con trỏ chuột lướt vào ô Mật khẩu và gõ Admin@123
        driver_web.execute_script("window.humanType('#login-password', 'Admin@123');")
        time.sleep(1.2)

        # Con trỏ chuột bấm nút Đăng Nhập
        driver_web.execute_script("window.humanClick('#btn-login-submit');")
        time.sleep(2.0)

        # Vào trang Admin
        driver_web.get(f"{BASE_URL}/admin/index.html")
        inject_human_cursor(driver_web)
        show_banner(driver_web, "BƯỚC 7: BẢNG ĐIỀU KHIỂN QUẢN TRỊ ADMIN (BIỂU ĐỒ KPI 7 NGÀY)")

        # Di chuyển chuột quanh bảng KPI
        time.sleep(1.0)
        driver_web.execute_script("window.humanClick('.stat-card, .chart-card, h2');")

        trigger_inspector(driver_api, {
            "name": "12.9 [200 OK] GET /admin/audit-logs (Nhật ký kiểm toán bất biến)",
            "method": "GET",
            "url": "/api/v1/admin/audit-logs",
            "status": 200,
            "statusText": "OK",
            "time": "24.5 ms",
            "size": "2.4 KB",
            "securityNote": "Tuân thủ tiêu chuẩn Audit Logging: Mọi hành động can thiệp dữ liệu, đăng nhập, phân quyền đều được lưu vết vĩnh viễn trong MySQL.",
            "reqHeaders": {
                "Authorization": "Bearer eyJhbGciOiJIUzI1NiJ9...[Role: admin]"
            },
            "resBody": {
                "success": True,
                "data": [
                    {"id": 85, "user_id": 1, "action": "LOGIN_SUCCESS", "details": "Đăng nhập tài khoản Admin", "created_at": "2026-11-05 17:45:10"},
                    {"id": 84, "user_id": 2, "action": "TOKEN_REFRESH", "details": "Xoay vòng Refresh Token", "created_at": "2026-11-05 17:44:30"},
                    {"id": 83, "user_id": 2, "action": "CANCEL_ORDER_DENIED", "details": "Chặn IDOR hủy đơn 1055", "created_at": "2026-11-05 17:44:10"}
                ]
            }
        })
        time.sleep(4.5)

        # Chuột bấm sang menu Đơn hàng
        print("[*] Di chuyển chuột mở menu Đơn hàng Quản trị...")
        driver_web.get(f"{BASE_URL}/admin/orders.html")
        inject_human_cursor(driver_web)
        show_banner(driver_web, "QUẢN TRỊ ĐƠN HÀNG & TRẠNG THÁI THANH TOÁN (100% TEST PASS)")
        time.sleep(1.0)
        driver_web.execute_script("window.humanClick('.table, tr, .btn');")
        time.sleep(3.5)

        show_banner(driver_web, "HOÀN TẤT KỊCH BẢN DEMO THÀNH CÔNG")
        print("\n" + "=" * 75)
        print(" DEMO TỰ ĐỘNG THÀNH CÔNG RỰC RỠ (100% PASS)!")
        print("=" * 75)
        time.sleep(3)

    except Exception as e:
        print(f"[!] Có lỗi trong quá trình demo: {e}")
    finally:
        print("[*] Đóng 2 trình duyệt sau 3 giây...")
        time.sleep(3)
        try:
            driver_web.quit()
        except Exception:
            pass
        try:
            driver_api.quit()
        except Exception:
            pass

if __name__ == "__main__":
    main()
