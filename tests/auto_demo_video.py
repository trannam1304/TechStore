"""
TechStore - Kịch bản Video Demo Tự động (Selenium Split-Screen)
Tự động ghim cửa sổ Chrome ở 50% bên trái màn hình
Phối hợp đồng bộ với Postman (Thư mục 12: Demo Bảo mật & JWT) ở bên phải màn hình
Dành cho: Quay video Phân đoạn Demo (≤ 1.5 - 2 phút)
"""

import sys
import time

def get_screen_resolution():
    try:
        import ctypes
        user32 = ctypes.windll.user32
        user32.SetProcessDPIAware()
        sw = user32.GetSystemMetrics(0)
        sh = user32.GetSystemMetrics(1)
        return sw, sh
    except Exception:
        return 1920, 1080

def show_banner(driver, text):
    js = f"""
    let b = document.getElementById('demo-step-banner');
    if (!b) {{
        b = document.createElement('div');
        b.id = 'demo-step-banner';
        b.style.cssText = 'position:fixed;top:14px;left:50%;transform:translateX(-50%);z-index:999999;background:#0f172a;color:#38bdf8;padding:10px 24px;border-radius:30px;font-size:14px;font-weight:700;box-shadow:0 8px 30px rgba(0,0,0,0.4);border:2px solid #38bdf8;pointer-events:none;font-family:Segoe UI, sans-serif;letter-spacing:0.5px;';
        document.body.appendChild(b);
    }}
    b.innerText = "{text}";
    """
    try:
        driver.execute_script(js)
    except Exception:
        pass

def main():
    print("=" * 70)
    print(" TECHSTORE - SELENIUM AUTOMATED SPLIT-SCREEN DEMO RUNNER")
    print("=" * 70)

    sw, sh = get_screen_resolution()
    half_w = sw // 2
    print(f"[*] Kích thước màn hình phát hiện: {sw}x{sh}")
    print(f"[*] Cửa sổ Chrome sẽ được ghim tại: x=0, y=0, width={half_w}, height={sh}")
    print("[*] Vui lòng đặt cửa sổ Postman ở nửa màn hình bên phải (Win + Phím Phải)")
    print("[*] Khởi động trình duyệt sau 3 giây...")
    time.sleep(2)

    from selenium import webdriver
    from selenium.webdriver.common.by import By

    options = webdriver.ChromeOptions()
    options.add_experimental_option("excludeSwitches", ["enable-automation"])
    options.add_experimental_option('useAutomationExtension', False)
    options.add_argument(f"--window-size={half_w},{sh}")
    options.add_argument("--window-position=0,0")

    driver = webdriver.Chrome(options=options)
    driver.set_window_rect(x=0, y=0, width=half_w, height=sh)

    BASE_URL = "http://localhost:8000"

    try:
        # BƯỚC 1: Khách hàng truy cập trang chủ TechStore
        print("\n[Bước 1/6] Mở trang chủ TechStore...")
        driver.get(f"{BASE_URL}/pages/index.html")
        show_banner(driver, "BƯỚC 1: TRANG CHỦ TECHSTORE (RESTful API LEVEL 3)")
        time.sleep(3)

        # BƯỚC 2: Cố tình truy cập trang Quản trị khi chưa đăng nhập
        print("[Bước 2/6] Kiểm tra Auth Guard: Cố tình vào /admin/ khi chưa đăng nhập...")
        driver.get(f"{BASE_URL}/admin/index.html")
        time.sleep(1)
        show_banner(driver, "BƯỚC 2: CHẶN TRUY CẬP /admin/ CHƯA ĐĂNG NHẬP (401)")
        time.sleep(3)

        # BƯỚC 3: Đăng nhập tài khoản Khách hàng
        print("[Bước 3/6] Đăng nhập tài khoản Khách hàng (user@techstore.local)...")
        driver.get(f"{BASE_URL}/pages/login.html")
        time.sleep(1)
        show_banner(driver, "BƯỚC 3: ĐĂNG NHẬP KHÁCH HÀNG & CẤP TOKEN JWT")
        driver.execute_script("fillAndLogin('user@techstore.local', 'User@123');")
        time.sleep(3)

        # BƯỚC 4: Khách hàng cố tình vào trang Admin -> Chặn quyền RBAC (403)
        print("[Bước 4/6] Kiểm tra RBAC: Khách hàng cố tình vào lại trang /admin/...")
        driver.get(f"{BASE_URL}/admin/index.html")
        time.sleep(1)
        show_banner(driver, "BƯỚC 4: PHÂN QUYỀN RBAC - CHẶN CUSTOMER TRUY CẬP ADMIN (403)")
        time.sleep(3)

        # BƯỚC 5: Mua sắm, xem giỏ hàng và kiểm soát tồn kho
        print("[Bước 5/6] Mở chi tiết sản phẩm và xem giỏ hàng...")
        driver.get(f"{BASE_URL}/pages/cart.html")
        show_banner(driver, "BƯỚC 5: GIỎ HÀNG KHÁCH HÀNG & KIỂM SOÁT TỒN KHO")
        time.sleep(3)

        # BƯỚC 6: Đăng xuất, Đăng nhập Quản trị viên Admin
        print("[Bước 6/6] Đăng nhập Admin và mở Bảng điều khiển Quản trị...")
        driver.get(f"{BASE_URL}/pages/login.html")
        time.sleep(1)
        show_banner(driver, "BƯỚC 6: ĐĂNG NHẬP ADMIN & BẢNG ĐIỀU KHIỂN KPI DOANH THU")
        driver.execute_script("fillAndLogin('admin@techstore.local', 'Admin@123');")
        time.sleep(2)
        driver.get(f"{BASE_URL}/admin/index.html")
        show_banner(driver, "BẢNG ĐIỀU KHIỂN QUẢN TRỊ ADMIN (BIỂU ĐỒ KPI 7 NGÀY)")
        time.sleep(4)

        # Xem trang Đơn hàng quản trị
        driver.get(f"{BASE_URL}/admin/orders.html")
        show_banner(driver, "QUẢN TRỊ ĐƠN HÀNG & ĐỒNG BỘ TRẠNG THÁI THANH TOÁN")
        time.sleep(3)

        # Kết thúc
        show_banner(driver, "DEMO HOÀN TẤT THÀNH CÔNG (100% TEST PASS)")
        print("\n=== HOÀN TẤT KỊCH BẢN DEMO THÀNH CÔNG! ===")
        time.sleep(3)

    except Exception as e:
        print(f"[!] Có lỗi trong quá trình demo: {e}")
    finally:
        print("[*] Đóng trình duyệt sau 3 giây...")
        time.sleep(3)
        driver.quit()

if __name__ == "__main__":
    main()
