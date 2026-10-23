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
│   └── models/                     # Thao tác trực tiếp CSDL MySQL (Domain Aggregate Root Models)
│       ├── BaseModel.php           # PDO wrapper & Query helper: query, execute, lastInsertId, transaction
│       ├── ProductModel.php        # Thực thể Sản phẩm (products, product_images, product_specs)
│       ├── CategoryModel.php       # Thực thể Danh mục (categories)
│       ├── BrandModel.php          # Thực thể Thương hiệu (brands)
│       ├── UserModel.php           # Thực thể Người dùng & Token (users, refresh_tokens)
│       ├── CartModel.php           # Thực thể Giỏ hàng phía server (carts)
│       ├── OrderModel.php          # Thực thể Đơn hàng & Chi tiết (orders, order_items)
│       ├── CouponModel.php         # Thực thể Mã giảm giá (coupons)
│       ├── PaymentModel.php        # Thực thể Giao dịch & Cổng thanh toán (payments)
│       ├── ReviewWishlistModel.php # Thực thể Đánh giá & Yêu thích (reviews, wishlists)
│       └── AnalyticsModel.php      # Tổng hợp thống kê, lượt xem & Kiểm toán (audit_logs, product_views, product_similarity)
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

---

### 2.4. Tầng Mô hình Thực thể (Domain Aggregate Models) & Nguyên tắc Phân tách Trách nhiệm (Clean MVC Architecture)

#### 2.4.1. Nguyên tắc Phân tách Trách nhiệm (Separation of Concerns)
Nhằm giải quyết triệt để tình trạng *Fat Controller* (Controller phình to, vừa đọc request vừa trực tiếp thực thi câu lệnh SQL, vi phạm nguyên lý Single Responsibility Principle), hệ thống chuẩn hóa mô hình phân tầng chặt chẽ:

```
[Client / Browser]
       │
       ▼ (HTTP Request: JSON, Query Params, Headers)
[Front Controller: index.php] ──▶ [Router & Middleware Stack (CORS, JWT Auth, Role)]
                                              │
                                              ▼
                                   [Controller Layer (HTTP Orchestrator)]
                                   • Tiếp nhận input, Validate dữ liệu (Validator)
                                   • Điều phối luồng nghiệp vụ (Orchestration)
                                   • Tuyệt đối KHÔNG chứa câu lệnh SQL thô
                                              │
                     ┌────────────────────────┴────────────────────────┐
                     ▼                                                 ▼
     [Domain Model Layer (Aggregate Roots)]            [Business Service Layer]
     • Kế thừa BaseModel (PDO Prepared)                • Thuật toán phức tạp / Crypto
     • Đóng gói toàn bộ thao tác SQL                   • JwtService, QrService, RecommendService
     • Query scopes, Data Mapping, Transactions
                     │                                                 │
                     └────────────────────────┬────────────────────────┘
                                              ▼
                                   [MySQL Database (17 Tables)]
```

- **Controller (HTTP Orchestrator):**
  - Đóng vai trò lớp tiếp nhận và nhạc trưởng điều phối.
  - **Nhiệm vụ:** Giải mã HTTP Request → Kiểm tra hợp lệ đầu vào qua [Validator.php](../../api/helpers/Validator.php) → Ủy quyền thực thi nghiệp vụ và truy vấn CSDL cho các `Model` (kèm các `Service` nếu có thuật toán) → Kiểm tra kết quả trả về → Đóng gói HTTP Response chuẩn mực qua [Response.php](../../api/helpers/Response.php) (`jsonRes`, `jsonErr`, `paginated`).
  - **Quy tắc bất biến:** Controller **tuyệt đối không** nhúng câu lệnh SQL thô (`SELECT`, `INSERT`, `UPDATE`, `DELETE`), không trực tiếp lấy kết nối PDO từ `Database`, và không suy diễn cấu trúc cột CSDL vật lý.
- **Model (Domain Aggregate Root & Data Access):**
  - Kế thừa trực tiếp từ [BaseModel.php](../../api/models/BaseModel.php).
  - Đóng vai trò là Aggregate Root trong Domain-Driven Design (DDD), gom nhóm và quản lý tập trung các bảng CSDL thuộc cùng một phạm vi ngữ cảnh nghiệp vụ (Bounded Context).
  - Sử dụng 100% Native PDO Prepared Statements với Parameter Binding (loại trừ hoàn toàn nguy cơ SQL Injection).
  - Đảm nhiệm: Data Mapping, Query Scopes (lọc, sắp xếp, phân trang), tính toán tính toàn vẹn dữ liệu, và điều khiển Transaction cục bộ.
- **Service (Complex Business & Algorithmic Engine):**
  - Đảm nhận các nghiệp vụ thuật toán vượt ra ngoài phạm vi CRUD CSDL đơn thuần: Mã hóa và kiểm tra JWT HS256 ([JwtService.php](../../api/services/JwtService.php)), sinh mã và ảnh VietQR NAPAS247 ([QrService.php](../../api/services/QrService.php)), tính toán độ tương đồng Cosine Similarity và ma trận Co-occurrence ([RecommendService.php](../../api/services/RecommendService.php)).

#### 2.4.2. Danh mục 10 Aggregate Root Models & Ánh xạ 17 Bảng CSDL
Toàn bộ 17 bảng trong [database/schema.sql](../../database/schema.sql) được quản lý tập trung bởi 10 Aggregate Root Models kế thừa từ [BaseModel.php](../../api/models/BaseModel.php):

| STT | Model Class | File nguồn | Bảng CSDL quản lý (17 Tables) | Trách nhiệm dữ liệu cốt lõi |
|:---:|---|---|---|---|
| 01 | **BaseModel** | [api/models/BaseModel.php](../../api/models/BaseModel.php) | Toàn bộ | Abstract base class: Đóng gói kết nối PDO Singleton, cung cấp helper `query()`, `fetchAll()`, `fetchOne()`, `insert()`, `update()`, `delete()`, `beginTransaction()`, `commit()`, `rollBack()`. |
| 02 | **ProductModel** | [api/models/ProductModel.php](../../api/models/ProductModel.php) | `products`<br>`product_images`<br>`product_specs` | Tìm kiếm full-text/debounce, bộ lọc đa tiêu chí (danh mục, hãng, khoảng giá, rating, thông số RAM/ROM/Chipset), phân trang, chi tiết sản phẩm kèm specs/images, CRUD sản phẩm Admin, kiểm tra và cập nhật tồn kho `stock`. |
| 03 | **CategoryModel** | [api/models/CategoryModel.php](../../api/models/CategoryModel.php) | `categories` | Danh sách danh mục kèm đếm sản phẩm (`product_count`), chi tiết danh mục theo slug/ID, CRUD danh mục cho Quản trị viên. |
| 04 | **BrandModel** | [api/models/BrandModel.php](../../api/models/BrandModel.php) | `brands` | Danh sách hãng sản xuất, chi tiết hãng, CRUD thương hiệu cho Quản trị viên. |
| 05 | **UserModel** | [api/models/UserModel.php](../../api/models/UserModel.php) | `users`<br>`refresh_tokens` | Đăng ký người dùng, tìm kiếm theo email/id, xác thực mật khẩu bcrypt, lưu trữ / thu hồi / kiểm tra Refresh Token, cập nhật hồ sơ, quản lý trạng thái tài khoản (active/inactive/banned). |
| 06 | **CartModel** | [api/models/CartModel.php](../../api/models/CartModel.php) | `carts` (view `cart`) | Lấy giỏ hàng theo `user_id` kèm JOIN giá/tồn kho thời gian thực từ `products`, thêm sản phẩm, cập nhật số lượng, xóa sản phẩm, dọn sạch giỏ hàng. |
| 07 | **OrderModel** | [api/models/OrderModel.php](../../api/models/OrderModel.php) | `orders`<br>`order_items` | Tạo đơn hàng trong ACID Transaction, lưu chi tiết `order_items`, tra cứu lịch sử đơn hàng của user, tra cứu chi tiết đơn, cập nhật trạng thái đơn hàng (`pending`, `confirmed`, `shipping`, `completed`, `cancelled`), hủy đơn hàng. |
| 08 | **CouponModel** | [api/models/CouponModel.php](../../api/models/CouponModel.php) | `coupons` | Thẩm định mã giảm giá, kiểm tra thời hạn (`end_date`), kiểm tra giới hạn lượt dùng (`usage_limit` vs `used_count`), tính giá trị chiết khấu theo % hoặc số tiền cố định, ghi nhận số lần dùng, CRUD voucher Admin. |
| 09 | **PaymentModel** | [api/models/PaymentModel.php](../../api/models/PaymentModel.php) | `payments` | Lưu thông tin giao dịch VietQR / COD, tra cứu trạng thái thanh toán theo `order_id` hoặc `payment_code`, xác nhận thanh toán thành công (webhook), đối soát mã ngân hàng `transaction_id`. |
| 10 | **ReviewWishlistModel** | [api/models/ReviewWishlistModel.php](../../api/models/ReviewWishlistModel.php) | `reviews`<br>`wishlists` (view `wishlist`) | Lấy danh sách đánh giá đã duyệt, kiểm tra điều kiện người dùng đã mua sản phẩm trước khi review, lưu đánh giá mới và kích hoạt tính lại `rating`/`review_count` của sản phẩm; quản lý danh sách yêu thích cá nhân. |
| 11 | **AnalyticsModel** | [api/models/AnalyticsModel.php](../../api/models/AnalyticsModel.php) | `audit_logs`<br>`product_views`<br>`product_similarity` | Ghi nhật ký kiểm toán hành vi Admin (`audit_logs`), ghi nhận sự kiện xem sản phẩm (`product_views`), truy vấn ma trận độ tương đồng phục vụ gợi ý (`product_similarity`), tổng hợp số liệu báo cáo Dashboard (doanh thu 7 ngày, tổng đơn, top bán chạy). |

#### 2.4.3. Ma trận Điều phối Controller ↔ Model (Controller-Model Orchestration Matrix)
Trong kiến trúc hướng thực thể, một Controller có thể điều phối linh hoạt một hoặc nhiều Model nhằm hoàn thành chuỗi nghiệp vụ toàn vẹn:

| Controller | Use-Case / API Endpoint | Models được điều phối (Orchestrated Models) | Dịch vụ bổ trợ (Services) | Vai trò phối hợp nghiệp vụ |
|---|---|---|---|---|
| **AuthController** | Đăng ký, Đăng nhập, Refresh, Logout | [UserModel](../../api/models/UserModel.php) | [JwtService](../../api/services/JwtService.php) | Tra cứu user, xác thực mật khẩu, phát hành cặp token JWT; lưu và thu hồi token hash trong `refresh_tokens`. |
| **ProductController** | Lọc tìm kiếm, Xem chi tiết, CRUD sản phẩm | [ProductModel](../../api/models/ProductModel.php)<br>[CategoryModel](../../api/models/CategoryModel.php)<br>[BrandModel](../../api/models/BrandModel.php) | - | Điều phối tìm kiếm sản phẩm kèm kiểm tra tính hợp lệ của Category ID và Brand ID khi thêm/sửa sản phẩm. |
| **CategoryBrandController** | Hiển thị Navbar, CRUD Danh mục & Hãng | [CategoryModel](../../api/models/CategoryModel.php)<br>[BrandModel](../../api/models/BrandModel.php) | - | Quản lý danh mục ngành hàng và danh sách thương hiệu công nghệ. |
| **CartController** | Quản lý giỏ hàng phía Server | [CartModel](../../api/models/CartModel.php)<br>[ProductModel](../../api/models/ProductModel.php) | - | Đối soát giá bán và số lượng tồn kho thực tế từ `ProductModel` trước khi cho phép thêm hoặc cập nhật số lượng trong giỏ. |
| **CouponController** | Validate mã giảm giá | [CouponModel](../../api/models/CouponModel.php) | - | Kiểm tra thời hạn, lượt dùng và tính mức giảm cho giỏ hàng. |
| **OrderController** | Tạo đơn hàng (ACID Transaction) | [OrderModel](../../api/models/OrderModel.php)<br>[ProductModel](../../api/models/ProductModel.php)<br>[CouponModel](../../api/models/CouponModel.php)<br>[CartModel](../../api/models/CartModel.php)<br>[PaymentModel](../../api/models/PaymentModel.php) | - | **Nghiệp vụ phức hợp cao nhất:**<br>1. Kiểm tra tồn kho từng item qua `ProductModel`<br>2. Kiểm tra & áp dụng mã qua `CouponModel`<br>3. Mở Transaction: ghi `orders` & `order_items` qua `OrderModel`<br>4. Trừ tồn kho qua `ProductModel`<br>5. Tạo bản ghi giao dịch qua `PaymentModel`<br>6. Xóa giỏ hàng qua `CartModel`<br>7. Commit Transaction. |
| **PaymentController** | Sinh VietQR, Polling, Webhook ngân hàng | [PaymentModel](../../api/models/PaymentModel.php)<br>[OrderModel](../../api/models/OrderModel.php) | [QrService](../../api/services/QrService.php) | Sinh mã QR NAPAS247 qua `QrService`, lưu bản ghi qua `PaymentModel`; khi webhook xác nhận tiền về, cập nhật trạng thái đơn sang `paid` qua `OrderModel`. |
| **ReviewWishlistController** | Đánh giá sản phẩm, Quản lý yêu thích | [ReviewWishlistModel](../../api/models/ReviewWishlistModel.php)<br>[OrderModel](../../api/models/OrderModel.php)<br>[ProductModel](../../api/models/ProductModel.php) | - | `OrderModel` xác nhận khách đã mua hàng → `ReviewWishlistModel` lưu đánh giá → `ProductModel` cập nhật lại điểm sao trung bình và số lượt review. |
| **RecommendationController** | Gợi ý sản phẩm tương tự, mua kèm, cho tôi | [ProductModel](../../api/models/ProductModel.php)<br>[AnalyticsModel](../../api/models/AnalyticsModel.php)<br>[OrderModel](../../api/models/OrderModel.php) | [RecommendService](../../api/services/RecommendService.php) | Lấy dữ liệu sản phẩm và ma trận tương đồng qua `ProductModel` & `AnalyticsModel`, phối hợp thuật toán trong `RecommendService` để xếp hạng gợi ý. |
| **AdminController** | Thống kê Dashboard, Quản lý người dùng, Audit log | [AnalyticsModel](../../api/models/AnalyticsModel.php)<br>[UserModel](../../api/models/UserModel.php)<br>[OrderModel](../../api/models/OrderModel.php)<br>[ProductModel](../../api/models/ProductModel.php)<br>[CouponModel](../../api/models/CouponModel.php) | - | Tổng hợp báo cáo toàn diện hệ thống: số liệu doanh thu từ `OrderModel`, tồn kho từ `ProductModel`, người dùng từ `UserModel`, nhật ký kiểm toán từ `AnalyticsModel`. |

