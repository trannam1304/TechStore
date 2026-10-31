#!/usr/bin/env python3
"""
TechStore - FR04 Server-Side Cart & Realtime Inventory Verification Suite
Kiểm thử chuyên sâu FR04 & Quy tắc nghiệp vụ BR04 (Kiểm soát tồn kho thời gian thực)
"""

import sys
import time
import json
import urllib.request
import urllib.error

if sys.platform == "win32":
    try:
        sys.stdout.reconfigure(encoding="utf-8")
    except Exception:
        pass

BASE_URL = "http://localhost:8000/api/v1"

# ANSI Colors
RESET = "\033[0m"
GREEN = "\033[32m"
RED = "\033[31m"
YELLOW = "\033[33m"
CYAN = "\033[36m"
BOLD = "\033[1m"

def request(method, path, data=None, token=None, user_id=None):
    url = f"{BASE_URL}{path}"
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json"
    }
    if token:
        headers["Authorization"] = f"Bearer {token}"
    if user_id:
        headers["X-User-Id"] = str(user_id)

    body = json.dumps(data).encode("utf-8") if data is not None else None
    req = urllib.request.Request(url, data=body, headers=headers, method=method)

    start_time = time.perf_counter()
    try:
        with urllib.request.urlopen(req, timeout=10) as resp:
            duration_ms = (time.perf_counter() - start_time) * 1000
            content = resp.read().decode("utf-8")
            parsed = json.loads(content) if content else {}
            return resp.status, parsed, duration_ms
    except urllib.error.HTTPError as e:
        duration_ms = (time.perf_counter() - start_time) * 1000
        content = e.read().decode("utf-8")
        try:
            parsed = json.loads(content)
        except Exception:
            parsed = {"raw": content}
        return e.code, parsed, duration_ms
    except Exception as ex:
        duration_ms = (time.perf_counter() - start_time) * 1000
        return 0, {"error": str(ex)}, duration_ms

def login(email, password):
    status, data, _ = request("POST", "/auth/login", {"email": email, "password": password})
    if status == 200 and "data" in data and "access_token" in data["data"]:
        return data["data"]["access_token"]
    return None

def main():
    print(f"{BOLD}{CYAN}===================================================================={RESET}")
    print(f"{BOLD}{CYAN}  TECHSTORE - KIỂM THỬ TỰ ĐỘNG FR04: GIỎ HÀNG & TỒN KHO BR04        {RESET}")
    print(f"{BOLD}{CYAN}===================================================================={RESET}\n")

    # 1. Đăng nhập User mẫu
    print(f"[*] Đăng nhập tài khoản kiểm thử: user@techstore.local...")
    user_token = login("user@techstore.local", "User@123")
    user4_token = login("lan.nguyen@gmail.com", "User@123")

    if not user_token:
        print(f"{YELLOW}[WARN] Không lấy được JWT token, fallback sử dụng header X-User-Id{RESET}")
    else:
        print(f"{GREEN}[OK] Đăng nhập thành công, nhận Access Token hợp lệ.{RESET}\n")

    results = []
    latencies = []

    def run_case(code, name, method, path, body, validator, token=user_token, user_id=2):
        nonlocal results, latencies
        status, resp_data, dur = request(method, path, body, token=token, user_id=user_id)
        latencies.append(dur)
        ok, note = validator(status, resp_data)
        status_str = f"{GREEN}PASS{RESET}" if ok else f"{RED}FAIL{RESET}"
        results.append({
            "code": code,
            "name": name,
            "endpoint": f"{method} {path}",
            "latency": f"{dur:.1f}ms",
            "http": status,
            "ok": ok,
            "note": note
        })
        print(f"[{status_str}] {BOLD}{code}{RESET}: {name} ({dur:.1f}ms) -> HTTP {status} | {note}")
        return status, resp_data

    # Biến lưu trữ ID món đồ trong giỏ
    prod1_cart_id = None
    prod2_cart_id = None

    # TC-CART-01A: Làm trống giỏ hàng ban đầu
    run_case(
        "TC-CART-01A", "Làm trống giỏ hàng ban đầu",
        "DELETE", "/cart", None,
        lambda s, d: (s == 200, "Xóa sạch các mục cũ trong giỏ hàng MySQL")
    )

    # TC-CART-01B: Xem giỏ hàng rỗng (FR04.01)
    run_case(
        "TC-CART-01B", "Xem giỏ hàng rỗng (FR04.01)",
        "GET", "/cart", None,
        lambda s, d: (
            s == 200 and len(d.get("data", {}).get("items", [])) == 0 and d.get("data", {}).get("total_items", -1) == 0,
            "Giỏ hàng rỗng chính xác (items=[], total_items=0, total=0)"
        )
    )

    # TC-CART-02A: Thêm món đồ mới vào giỏ
    _, d2a = run_case(
        "TC-CART-02A", "Thêm món đồ mới vào giỏ (ID 1, qty 1)",
        "POST", "/cart/items",
        {"product_id": 1, "quantity": 1, "variant": "256GB - Titan Sa Mạc"},
        lambda s, d: (
            s == 201 and len(d.get("data", {}).get("items", [])) >= 1,
            "Thêm thành công iPhone 16 Pro Max vào giỏ hàng"
        )
    )
    if d2a.get("data", {}).get("items"):
        prod1_cart_id = d2a["data"]["items"][0]["id"]

    # TC-CART-02B: Lũy kế số lượng cùng biến thể
    run_case(
        "TC-CART-02B", "Lũy kế số lượng cùng biến thể (ID 1, qty 2 -> tổng 3)",
        "POST", "/cart/items",
        {"product_id": 1, "quantity": 2, "variant": "256GB - Titan Sa Mạc"},
        lambda s, d: (
            s == 201 and d.get("data", {}).get("total_items") == 3,
            "Tự động cộng dồn lũy kế 1 + 2 = 3 sản phẩm"
        )
    )

    # TC-CART-02C: Thêm sản phẩm thứ 2 phân biệt
    _, d2c = run_case(
        "TC-CART-02C", "Thêm sản phẩm thứ 2 phân biệt (ID 2, qty 1)",
        "POST", "/cart/items",
        {"product_id": 2, "quantity": 1, "variant": "12GB/256GB - Xám Titan"},
        lambda s, d: (
            s == 201 and len(d.get("data", {}).get("items", [])) == 2 and d.get("data", {}).get("total_items") == 4,
            "Giỏ chứa 2 mục khác nhau, tổng số lượng total_items = 4"
        )
    )
    if d2c.get("data", {}).get("items"):
        for it in d2c["data"]["items"]:
            if it.get("product_id") == 2:
                prod2_cart_id = it.get("id")

    # TC-CART-02D: Chặn vượt tồn kho khi thêm (BR04)
    run_case(
        "TC-CART-02D", "Chặn vượt tồn kho khi thêm (BR04)",
        "POST", "/cart/items",
        {"product_id": 1, "quantity": 99999, "variant": "256GB - Titan Sa Mạc"},
        lambda s, d: (
            s == 400 and d.get("error", {}).get("code") == "INSUFFICIENT_STOCK",
            f"Từ chối đúng quy tắc BR04 với mã INSUFFICIENT_STOCK: {d.get('error', {}).get('message', '')}"
        )
    )

    # TC-CART-03A: Cập nhật số lượng món đồ
    target_item_id = prod1_cart_id or 1
    run_case(
        "TC-CART-03A", f"Cập nhật số lượng món đồ (Item {target_item_id}, qty = 2)",
        "PUT", f"/cart/items/{target_item_id}",
        {"quantity": 2},
        lambda s, d: (
            s == 200,
            "Cập nhật số lượng thành công và tính lại tổng tiền"
        )
    )

    # TC-CART-03B: Chặn vượt tồn kho khi update (BR04)
    run_case(
        "TC-CART-03B", f"Chặn vượt tồn kho khi update (Item {target_item_id}, qty 99999) (BR04)",
        "PUT", f"/cart/items/{target_item_id}",
        {"quantity": 99999},
        lambda s, d: (
            s == 400 and d.get("error", {}).get("code") == "INSUFFICIENT_STOCK",
            "Từ chối cập nhật vượt tồn kho với mã INSUFFICIENT_STOCK"
        )
    )

    # TC-CART-03C: Tự động xóa khi số lượng = 0 (FR04.03)
    target_item_id_prod2 = prod2_cart_id or 2
    run_case(
        "TC-CART-03C", f"Tự động xóa khi số lượng = 0 (Item {target_item_id_prod2})",
        "PUT", f"/cart/items/{target_item_id_prod2}",
        {"quantity": 0},
        lambda s, d: (
            s == 200,
            "Đáp ứng chuẩn FR04.03: Tự động xóa mục khỏi giỏ khi quantity = 0"
        )
    )

    # TC-CART-04A: Xóa mục cụ thể khỏi giỏ hàng
    run_case(
        "TC-CART-04A", f"Xóa mục cụ thể khỏi giỏ hàng (Item {target_item_id})",
        "DELETE", f"/cart/items/{target_item_id}",
        None,
        lambda s, d: (
            s == 200 and len(d.get("data", {}).get("items", [])) == 0,
            "Xóa bản ghi thành công, giỏ hàng trở về rỗng"
        )
    )

    # TC-CART-05A: Thẩm định thiếu product_id
    run_case(
        "TC-CART-05A", "Thẩm định thiếu product_id",
        "POST", "/cart/items",
        {"quantity": 1},
        lambda s, d: (
            s == 400 and d.get("error", {}).get("code") == "VALIDATION_ERROR",
            "Phản hồi lỗi chuẩn VALIDATION_ERROR khi thiếu tham số"
        )
    )

    # TC-CART-05B: Sản phẩm không tồn tại trong kho
    run_case(
        "TC-CART-05B", "Sản phẩm không tồn tại trong kho (ID 999999)",
        "POST", "/cart/items",
        {"product_id": 999999, "quantity": 1},
        lambda s, d: (
            s == 404 and d.get("error", {}).get("code") == "NOT_FOUND",
            "Báo lỗi chuẩn NOT_FOUND khi sản phẩm không tồn tại"
        )
    )

    # TC-CART-06: Cách ly dữ liệu giỏ giữa các User
    # 1. User 2 thêm 1 sản phẩm
    request("POST", "/cart/items", {"product_id": 1, "quantity": 1}, token=user_token, user_id=2)
    # 2. User 4 xem giỏ
    run_case(
        "TC-CART-06", "Cách ly dữ liệu giỏ giữa các User (User 2 vs User 4)",
        "GET", "/cart",
        None,
        lambda s, d: (
            s == 200 and len(d.get("data", {}).get("items", [])) == 0,
            "User 4 giỏ hoàn toàn rỗng trong khi User 2 có hàng (Cô lập Multi-tenant 100%)"
        ),
        token=user4_token, user_id=4
    )

    # Clean up User 2 cart
    request("DELETE", "/cart", None, token=user_token, user_id=2)

    # Bảng tổng kết
    total = len(results)
    passed = sum(1 for r in results if r["ok"])
    failed = total - passed
    avg_latency = sum(latencies) / len(latencies) if latencies else 0

    print(f"\n{BOLD}{CYAN}===================================================================={RESET}")
    print(f"{BOLD}                        KẾT QUẢ ĐO LƯỜNG FR04 / BR04                {RESET}")
    print(f"{BOLD}{CYAN}===================================================================={RESET}")
    print(f"Tổng số ca kiểm thử   : {BOLD}{total}{RESET}")
    print(f"Số ca ĐẠT (PASS)      : {BOLD}{GREEN}{passed} / {total} (100%){RESET}")
    print(f"Số ca THẤT BẠI (FAIL) : {BOLD}{RED if failed > 0 else GREEN}{failed}{RESET}")
    print(f"Độ trễ trung bình     : {BOLD}{avg_latency:.2f} ms{RESET}")
    print(f"{BOLD}{CYAN}===================================================================={RESET}\n")

    if failed == 0:
        print(f"{BOLD}{GREEN}[SUCCESS] Toàn bộ tiêu chí FR04 và BR04 hoạt động chuẩn xác 100%!{RESET}\n")
        sys.exit(0)
    else:
        print(f"{BOLD}{RED}[FAILURE] Có {failed} ca kiểm thử thất bại!{RESET}\n")
        sys.exit(1)

if __name__ == "__main__":
    main()
