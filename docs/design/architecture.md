# THIẾT KẾ KIẾN TRÚC TECHSTORE RESTFUL API

**Dự án:** TechStore – Nền tảng TMĐT Đồ Công Nghệ (Topic 1: RESTful API)  
**Tác giả:** Nguyễn Thành Trung (Back-end & Thuật toán)  
**Trạng thái:** Chuẩn hóa hoàn tất  

---

## 1. Thiết kế tổ chức thư mục (Target Directory Structure)

```
techstore/
├── config/
│   ├── config.php                  # Đọc biến môi trường từ .env
│   └── database.php                # Quản lý kết nối PDO MySQL (Singleton)
│
├── api/
│   ├── routes.php                  # Router trung tâm: Đăng ký endpoint và map sang Controller
│   │
│   ├── core/                       # Nhân xử lý API framework nhẹ
│   │   ├── Autoloader.php          # Tự động nạp class theo namespace (PSR-4 compliant)
│   │   ├── Router.php              # Phân tích URL, method, route parameters {id}
│   │   └── Request.php             # Đóng gói dữ liệu đầu vào (JSON body, query params, headers)
│   │
│   ├── helpers/                    # Các hàm tiện ích dùng chung
│   │   ├── Response.php            # Chuẩn hóa JSON: jsonRes(), jsonErr(), paginated()
│   │   └── Validator.php           # Kiểm tra dữ liệu (required, email, numeric, min, max...)
│   │
│   ├── middleware/                 # Bộ lọc tiền xử lý HTTP Request
│   │   ├── CorsMiddleware.php      # Xử lý CORS headers & HTTP OPTIONS Preflight
│   │   ├── AuthMiddleware.php      # Kiểm tra và giải mã JWT Access Token
│   │   └── RoleMiddleware.php      # Phân quyền truy cập (Admin / Customer)
│   │
│   ├── controllers/                # Tiếp nhận request & điều phối nghiệp vụ
│   │   ├── BaseController.php      # Controller cơ sở (hàm helper dùng chung)
│   │   ├── AuthController.php      # 5 APIs: register, login, refresh, logout, me
│   │   ├── ProductController.php   # 6 APIs: danh sách, chi tiết, CRUD sản phẩm admin
│   │   ├── CategoryBrandController.php # 4 APIs: categories, brands (khách & admin)
│   │   ├── CartController.php      # 4 APIs: xem giỏ, thêm, sửa số lượng, xóa giỏ
│   │   ├── CouponController.php    # 1 API: validate mã giảm giá
│   │   ├── OrderController.php     # 5 APIs: tạo đơn (transaction), lịch sử, chi tiết, hủy, đổi trạng thái
│   │   ├── PaymentController.php   # 3 APIs: sinh VietQR, polling trạng thái, webhook xác nhận
│   │   ├── ReviewWishlistController.php # 4 APIs: đánh giá sản phẩm, yêu thích
│   │   ├── RecommendationController.php # 2 APIs: gợi ý tương tự / mua cùng, gợi ý cho tôi
│   │   └── AdminController.php     # 3 APIs: thống kê dashboard, audit logs, quản lý user
│   │
│   ├── services/                   # Logic nghiệp vụ phức tạp & Thuật toán
│   │   ├── JwtService.php          # Ký & giải mã JWT HS256 (RFC 7519)
│   │   ├── QrService.php           # Sinh payload & ảnh VietQR NAPAS247
│   │   └── RecommendService.php    # Thuật toán Cosine Similarity + Co-occurrence + Hybrid
│   │
│   └── models/                     # Thao tác trực tiếp CSDL MySQL (PDO Prepared Statements)
│       └── BaseModel.php           # PDO wrapper: query, execute, lastInsertId, transaction
│
├── database/
│   ├── schema.sql                  # DDL cấu trúc CSDL hoàn chỉnh (≥15 bảng)
│   ├── seed.sql                    # DML dữ liệu mẫu (≥40 sản phẩm, 8 hãng, ~30 đơn mẫu)
│   └── data.json.bak               # Sao lưu file data.json cũ và vô hiệu hóa runtime
│
├── docs/
│   ├── design/                     # Tài liệu thiết kế kiến trúc & CSDL
│   │   └── architecture.md
│   └── openapi.yaml                # Đặc tả 37 endpoints chuẩn OpenAPI 3.0
│
├── public/
│   ├── index.php                   # Front controller (Document Root)
│   └── ... (toàn bộ pages, admin, assets)
```

---

## 2. Đặc tả chi tiết các tầng (Layer Specification)

### 2.1. Tầng Kết Nối CSDL ([config/database.php](../../config/database.php))
- Sử dụng mô hình **Singleton Pattern** để chỉ duy nhất một kết nối PDO được khởi tạo trong suốt vòng đời của HTTP request.
- Thiết lập các thuộc tính PDO an toàn:
  ```php
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false, // Sử dụng Native Prepared Statements
  ```

### 2.2. Tầng Router & Dispatcher ([api/core/Router.php](../../api/core/Router.php))
- Hỗ trợ đầy đủ các phương thức HTTP: `GET`, `POST`, `PUT`, `PATCH`, `DELETE`.
- Hỗ trợ tham số URL động (Route Parameters), ví dụ: `/products/{id}`, `/orders/{orderId}/status`.
- Hỗ trợ gắn chuỗi Middleware trực tiếp trên route:
  ```php
  $router->get('/products/{id}', [ProductController::class, 'show']);
  $router->post('/products', [ProductController::class, 'store'], [AuthMiddleware::class, RoleMiddleware::admin()]);
  ```

### 2.3. Tầng Phân rã Controller (37 API endpoints)

| Controller | File tương đối | Phương thức & Endpoint | Chức năng nghiệp vụ |
|---|---|---|---|
| **AuthController** | [api/controllers/AuthController.php](../../api/controllers/AuthController.php) | `POST /auth/register`<br>`POST /auth/login`<br>`POST /auth/refresh`<br>`POST /auth/logout`<br>`GET /auth/me`<br>`POST /auth/google` | Đăng ký tài khoản, xác thực mật khẩu bcrypt, phát hành/thu hồi cặp token. |
| **ProductController** | [api/controllers/ProductController.php](../../api/controllers/ProductController.php) | `GET /products`<br>`GET /products/{id}`<br>`POST /products`<br>`PUT /products/{id}`<br>`PATCH /products/{id}`<br>`DELETE /products/{id}` | Lọc tìm kiếm phân trang sản phẩm cho khách; CRUD sản phẩm cho Admin. |
| **CategoryBrandController** | [api/controllers/CategoryBrandController.php](../../api/controllers/CategoryBrandController.php) | `GET /categories`, `GET /brands`<br>`POST/PUT/DELETE /categories`<br>`POST/PUT/DELETE /brands` | Lấy danh mục và hãng cho navbar/bộ lọc; quản trị thêm sửa xóa danh mục và thương hiệu. |
| **CartController** | [api/controllers/CartController.php](../../api/controllers/CartController.php) | `GET /cart`<br>`POST /cart/items`<br>`PUT /cart/items/{id}`<br>`DELETE /cart/items/{id}` | Quản lý giỏ hàng phía máy chủ của người dùng đăng nhập. |
| **CouponController** | [api/controllers/CouponController.php](../../api/controllers/CouponController.php) | `POST /coupons/validate` | Kiểm tra điều kiện đơn hàng tối thiểu và tính mức chiết khấu. |
| **OrderController** | [api/controllers/OrderController.php](../../api/controllers/OrderController.php) | `POST /orders`<br>`GET /orders`<br>`GET /orders/{id}`<br>`POST /orders/{id}/cancel`<br>`PATCH /orders/{id}/status` | Đặt hàng sử dụng PDO Transaction (trừ tồn kho), tra cứu đơn cá nhân, duyệt đơn Admin. |
| **PaymentController** | [api/controllers/PaymentController.php](../../api/controllers/PaymentController.php) | `POST /payments/qr`<br>`GET /payments/{orderId}/status`<br>`POST /payments/webhook` | Sinh mã VietQR ảnh động, kiểm tra polling thanh toán, webhook xác nhận tiền về. |
| **ReviewWishlistController** | [api/controllers/ReviewWishlistController.php](../../api/controllers/ReviewWishlistController.php) | `GET/POST /products/{id}/reviews`<br>`GET/POST/DELETE /wishlist` | Đánh giá sao kèm bình luận (chỉ cho người đã mua); lưu sản phẩm yêu thích. |
| **RecommendationController** | [api/controllers/RecommendationController.php](../../api/controllers/RecommendationController.php) | `GET /products/{id}/recommendations`<br>`GET /recommendations/me` | Gợi ý sản phẩm tương tự (Content-based), mua kèm (Co-occurrence) và cá nhân hóa. |
| **AdminController** | [api/controllers/AdminController.php](../../api/controllers/AdminController.php) | `GET /admin/stats`<br>`GET /admin/audit-logs`<br>`GET/PATCH /admin/users`<br>`CRUD /admin/coupons` | Thống kê doanh thu 7 ngày, nhật ký kiểm toán, quản lý khóa/mở và phân quyền user. |
