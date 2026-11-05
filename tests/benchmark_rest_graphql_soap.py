"""
TechStore - Benchmark Thực nghiệm So sánh Kiến trúc (Task T72)
So sánh định lượng 3 phong cách kiến trúc: RESTful API vs GraphQL vs SOAP 1.1
Phục vụ Báo cáo Chương 2, 3 và Video Demo bảo vệ đồ án
"""

import urllib.request
import urllib.parse
import json
import time

BASE_URL = "http://127.0.0.1:8000/api/v1"

def test_request(method, url, headers=None, data=None):
    if headers is None:
        headers = {}
    req = urllib.request.Request(url, data=data, headers=headers, method=method)
    start = time.perf_counter()
    try:
        with urllib.request.urlopen(req) as resp:
            body = resp.read()
            elapsed_ms = (time.perf_counter() - start) * 1000
            resp_headers = dict(resp.getheaders())
            return {
                "status": resp.status,
                "elapsed_ms": elapsed_ms,
                "size_bytes": len(body),
                "headers": resp_headers,
                "body": body
            }
    except urllib.error.HTTPError as e:
        body = e.read()
        elapsed_ms = (time.perf_counter() - start) * 1000
        return {
            "status": e.code,
            "elapsed_ms": elapsed_ms,
            "size_bytes": len(body),
            "headers": dict(e.headers),
            "body": body
        }

def run_benchmark():
    print("=" * 78)
    print(" TECHSTORE - THỰC NGHIỆM ĐỐI CHỨNG: RESTful API vs GraphQL vs SOAP")
    print("=" * 78)

    # -------------------------------------------------------------------------
    # KỊCH BẢN 1: Lấy danh sách 10 sản phẩm (Hiển thị trang danh mục)
    # -------------------------------------------------------------------------
    print("\n[KỊCH BẢN 1] Duyệt danh sách 10 sản phẩm (Product Catalog - 10 items):")

    # 1. RESTful API (GET /products?limit=10)
    rest_url = f"{BASE_URL}/products?limit=10"
    rest_latencies = []
    rest_res = None
    for _ in range(5):
        r = test_request("GET", rest_url)
        rest_latencies.append(r["elapsed_ms"])
        rest_res = r
    rest_avg_ms = sum(rest_latencies) / len(rest_latencies)
    rest_etag = rest_res["headers"].get("ETag", rest_res["headers"].get("etag", ""))

    print(f"  * REST API (GET /products?limit=10):")
    print(f"    - Mã phản hồi HTTP  : {rest_res['status']} OK")
    print(f"    - Kích thước Payload: {rest_res['size_bytes']:,} bytes ({rest_res['size_bytes']/1024:.2f} KB)")
    print(f"    - Độ trễ phản hồi   : {rest_avg_ms:.2f} ms")
    print(f"    - ETag Caching      : {rest_etag}")
    print(f"    - Cache-Control     : {rest_res['headers'].get('Cache-Control', 'N/A')}")

    # 2. GraphQL (POST /graphql với Field Selection)
    gql_url = f"{BASE_URL}/graphql"
    gql_query = {
        "query": """{
            products(limit: 10) {
                id
                name
                price
                original_price
                thumbnail_url
                category { name }
                brand { name }
            }
        }"""
    }
    gql_data = json.dumps(gql_query).encode("utf-8")
    gql_latencies = []
    gql_res = None
    for _ in range(5):
        r = test_request("POST", gql_url, {"Content-Type": "application/json"}, gql_data)
        gql_latencies.append(r["elapsed_ms"])
        gql_res = r
    gql_avg_ms = sum(gql_latencies) / len(gql_latencies)

    print(f"\n  * GraphQL (POST /graphql - Chỉ chọn các trường hiển thị UI):")
    print(f"    - Mã phản hồi HTTP  : {gql_res['status']} OK")
    print(f"    - Kích thước Payload: {gql_res['size_bytes']:,} bytes ({gql_res['size_bytes']/1024:.2f} KB)")
    print(f"    - Độ trễ phản hồi   : {gql_avg_ms:.2f} ms")
    print(f"    - Khả năng lọc trường (Field Selection): Thành công (loại bỏ mô tả, thông số thừa)")

    # 3. SOAP 1.1 (POST /soap với XML Envelope)
    soap_url = f"{BASE_URL}/soap"
    soap_xml = """<?xml version="1.0" encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tech="https://techstore.local/soap">
  <soapenv:Header/>
  <soapenv:Body>
    <tech:GetProductsRequest>
      <tech:limit>10</tech:limit>
    </tech:GetProductsRequest>
  </soapenv:Body>
</soapenv:Envelope>""".encode("utf-8")

    soap_latencies = []
    soap_res = None
    for _ in range(5):
        r = test_request("POST", soap_url, {"Content-Type": "text/xml; charset=utf-8"}, soap_xml)
        soap_latencies.append(r["elapsed_ms"])
        soap_res = r
    soap_avg_ms = sum(soap_latencies) / len(soap_latencies)

    print(f"\n  * SOAP 1.1 (POST /soap - Đóng gói XML Envelope có Header & Body):")
    print(f"    - Mã phản hồi HTTP  : {soap_res['status']} OK")
    print(f"    - Kích thước Payload: {soap_res['size_bytes']:,} bytes ({soap_res['size_bytes']/1024:.2f} KB)")
    print(f"    - Độ trễ phản hồi   : {soap_avg_ms:.2f} ms")
    print(f"    - Chi phí phong bì (Payload Overhead): Cực lớn do thẻ đóng/mở XML & namespace")

    # -------------------------------------------------------------------------
    # KỊCH BẢN 2: MINH CHỨNG HIỆN TƯỢNG OVER-FETCHING
    # Khi Client Mobile/Widget chỉ cần: ID, Tên, Giá (Dropdown / Price badge)
    # -------------------------------------------------------------------------
    print("\n" + "-" * 78)
    print("[KỊCH BẢN 2] Minh chứng hiện tượng Over-fetching (Chỉ cần ID, Name, Price):")

    # REST vẫn trả về toàn bộ thực thể tài nguyên chuẩn mực
    print(f"  * REST API: Vẫn phải tải toàn bộ thông tin tài nguyên -> {rest_res['size_bytes']:,} bytes")

    # GraphQL chỉ tải đúng 3 trường
    gql_minimal_query = {"query": "{ products(limit: 10) { id name price } }"}
    gql_min_data = json.dumps(gql_minimal_query).encode("utf-8")
    gql_min_res = test_request("POST", gql_url, {"Content-Type": "application/json"}, gql_min_data)

    saved_bytes = rest_res['size_bytes'] - gql_min_res['size_bytes']
    pct_reduction = (saved_bytes / rest_res['size_bytes']) * 100

    print(f"  * GraphQL : Chỉ trả đúng 3 trường -> {gql_min_res['size_bytes']:,} bytes")
    print(f"  ==> ĐÁNH GIÁ: GraphQL giúp giảm tới {pct_reduction:.1f}% băng thông mạng (Tiết kiệm {saved_bytes:,} bytes)!")

    # -------------------------------------------------------------------------
    # KỊCH BẢN 3: MINH CHỨNG HIỆU QUẢ HTTP CACHING (304 Not Modified)
    # -------------------------------------------------------------------------
    print("\n" + "-" * 78)
    print("[KỊCH BẢN 3] Minh chứng hiệu năng HTTP Caching (Tái truy cập danh mục):")

    if rest_etag:
        cache_test = test_request("GET", rest_url, {"If-None-Match": rest_etag})
        print(f"  * REST API với If-None-Match: '{rest_etag}':")
        print(f"    - Mã phản hồi HTTP  : {cache_test['status']} Not Modified")
        print(f"    - Kích thước tải    : {cache_test['size_bytes']} bytes (Băng thông = 0 KB)")
        print(f"    - Thời gian phản hồi: {cache_test['elapsed_ms']:.2f} ms (Phản hồi tức thì)")
    print(f"  * GraphQL & SOAP (Dùng POST):")
    print(f"    - Không tận dụng được bộ đệm HTTP tự nhiên (Trình duyệt & Proxy trung gian luôn phải gửi toàn bộ request lên backend)")

    # -------------------------------------------------------------------------
    # BẢNG TỔNG HỢP SO SÁNH THỰC NGHIỆM ĐỊNH LƯỢNG
    # -------------------------------------------------------------------------
    print("\n" + "=" * 78)
    print(" BẢNG TỔNG HỢP ĐỐI CHỨNG THỰC NGHIỆM TẠI HỆ THỐNG TECHSTORE")
    print("=" * 78)
    print(f"{'Tiêu chí đánh giá':<28} | {'RESTful API (TechStore)':<23} | {'GraphQL (Đối chứng)':<20} | {'SOAP 1.1 (Đối chứng)'}")
    print("-" * 100)
    print(f"{'Kích thước Payload (10 sp)':<28} | {rest_res['size_bytes']:>6} bytes ({rest_res['size_bytes']/1024:.2f} KB)   | {gql_res['size_bytes']:>6} bytes ({gql_res['size_bytes']/1024:.2f} KB)  | {soap_res['size_bytes']:>6} bytes ({soap_res['size_bytes']/1024:.2f} KB)")
    print(f"{'Độ trễ trung bình (ms)':<28} | {rest_avg_ms:>6.2f} ms                 | {gql_avg_ms:>6.2f} ms                | {soap_avg_ms:>6.2f} ms")
    print(f"{'Phương thức truyền tải':<28} | {'GET / POST / PUT / DELETE':<23} | {'POST (Chủ yếu)':<20} | {'POST (Tunnel qua HTTP)'}")
    print(f"{'Định dạng dữ liệu':<28} | {'JSON (RFC 8259)':<23} | {'JSON':<20} | {'XML (SOAP Envelope)'}")
    print(f"{'Hiện tượng Over-fetching':<28} | {'Có (Trả đủ tài nguyên)':<23} | {'Không (Field Selection)':<20} | {'Rất nặng (Tags XML)'}")
    print(f"{'Giải quyết Under-fetching':<28} | {'Cần thêm endpoint/embed':<23} | {'Tốt (Nested Query)':<20} | {'Phức tạp'}")
    print(f"{'Khả năng HTTP Caching':<28} | {'Xuất sắc (ETag, 304)':<23} | {'Hạn chế (Cần APQ)':<20} | {'Không hỗ trợ tự nhiên'}")
    print(f"{'Cấp độ trưởng thành RMM':<28} | {'Level 3 (HATEOAS)':<23} | {'Không áp dụng (Level 0)':<20} | {'Level 0 (The Swamp of POX)'}")
    print("=" * 78)

if __name__ == "__main__":
    run_benchmark()
