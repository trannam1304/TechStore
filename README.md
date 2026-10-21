# TechStore – Website thương mại điện tử đồ công nghệ

> Topic 1: **RESTful API** (REST principles, HTTP methods, JWT/OAuth, so sánh với GraphQL/SOAP).
> Website bán điện thoại, laptop, phụ kiện – tham khảo mô hình CellphoneS.

---

## 1. Mục tiêu dự án

- Xây dựng website bán hàng công nghệ hoàn chỉnh: khách xem – tìm – mua – thanh toán; quản trị viên quản lý hệ thống.
- **Toàn bộ nghiệp vụ được cung cấp qua REST API** (PHP thuần), front-end (HTML/CSS/JS) chỉ tiêu thụ API → đáp ứng yêu cầu demo của topic.
- Bảo mật API bằng **JWT** (access token + refresh token), phân quyền `customer` / `admin`.
- Có tài liệu bài luận (essay) về REST và bộ test Postman.

## 2. Phạm vi chức năng

### 2.1 Phía khách hàng
| Nhóm | Chức năng |
|---|---|
| Tài khoản | Đăng ký, đăng nhập, đăng xuất, quên mật khẩu, cập nhật hồ sơ, sổ địa chỉ |
| Sản phẩm | Danh sách, lọc (hãng, giá, RAM, bộ nhớ…), sắp xếp, tìm kiếm (gợi ý khi gõ), phân trang, chi tiết sản phẩm, thư viện ảnh, thông số kỹ thuật |
| Giỏ hàng | Thêm/sửa/xóa, tính tổng, áp dụng mã giảm giá |
| Đặt hàng | Checkout, chọn địa chỉ, phương thức thanh toán (COD / **QR chuyển khoản**) |
| Thanh toán QR | Sinh mã VietQR theo số tiền + nội dung chuyển khoản (mã đơn), trang theo dõi trạng thái thanh toán |
| Đơn hàng | Lịch sử, chi tiết, hủy đơn khi chưa xử lý |
| Tương tác | Đánh giá & bình luận, danh sách yêu thích, so sánh sản phẩm |
| **Gợi ý sản phẩm** | "Sản phẩm tương tự", "Thường được mua cùng", "Dành cho bạn" (xem mục 5) |

### 2.2 Phía quản trị (Admin)
- Dashboard: doanh thu theo ngày/tháng, đơn mới, top sản phẩm bán chạy, tồn kho thấp (biểu đồ).
- CRUD: sản phẩm (kèm upload nhiều ảnh), danh mục, thương hiệu, mã giảm giá, banner.
- Quản lý đơn hàng: đổi trạng thái (Chờ xác nhận → Đã xác nhận → Đang giao → Hoàn tất / Hủy), xác nhận thanh toán QR, in hóa đơn.
- Quản lý người dùng & phân quyền, khóa/mở tài khoản, duyệt/ẩn đánh giá.
- Nhật ký hoạt động (audit log).

### 2.3 Đề xuất bổ sung (để nâng điểm – chọn theo thời gian)
- Rate limiting đơn giản theo IP/token, CORS cấu hình rõ ràng.
- API versioning (`/api/v1`), chuẩn response thống nhất, mã lỗi HTTP đúng chuẩn.
- Swagger/OpenAPI file mô tả API (`docs/openapi.yaml`).
- Xác thực bước 2 hoặc đăng nhập Google (OAuth 2.0) làm demo cho phần OAuth.
- Gửi email xác nhận đơn (PHPMailer).
- Responsive mobile, SEO cơ bản (slug thân thiện).

## 3. Công nghệ

| Lớp | Công nghệ |
|---|---|
| Back-end | PHP 8.x thuần (không framework), PDO, kiến trúc MVC tự xây + Router riêng |
| CSDL | MySQL 8 / MariaDB |
| Front-end | HTML5, CSS3 (Flexbox/Grid), JavaScript ES6 (fetch API) |
| Xác thực | JWT (HS256) tự cài đặt hoặc thư viện `firebase/php-jwt` (nếu giảng viên cho phép Composer) |
| Công cụ | Git/GitHub, Postman, XAMPP/Laragon, VS Code, draw.io (ERD, kiến trúc) |

## 4. Kiến trúc & cấu trúc thư mục

```
techstore/
├── public/                 # Document root (chỉ thư mục này public)
│   ├── index.php           # Front controller
│   ├── assets/             # css, js, images
│   ├── pages/              # trang HTML phía khách
│   └── admin/              # trang quản trị
├── api/                    # REST API
│   ├── routes.php          # Khai báo route
│   ├── controllers/
│   ├── models/
│   ├── middleware/         # AuthMiddleware, RoleMiddleware, RateLimit, Cors
│   ├── services/           # RecommendService, QrService, JwtService
│   └── helpers/            # Response, Validator
├── config/                 # config.php, database.php (không commit file .env thật)
├── database/
│   ├── schema.sql
│   └── seed.sql
├── docs/                   # essay, ERD, openapi.yaml, postman collection, ảnh demo
├── tests/                  # Postman / script test
├── .gitignore
├── .env.example
└── README.md
```

### Quy ước REST API
- Base URL: `/api/v1`
- Định dạng: JSON, `Content-Type: application/json`
- Response chuẩn: `{ "success": true, "data": {...}, "message": "", "meta": {...} }`
- Lỗi: `{ "success": false, "error": { "code": "VALIDATION_ERROR", "details": [...] } }`
- Mã HTTP: 200, 201, 204, 400, 401, 403, 404, 409, 422, 429, 500.

### Danh sách endpoint chính (tóm tắt)
| Method | Endpoint | Mô tả | Quyền |
|---|---|---|---|
| POST | /auth/register | Đăng ký | Public |
| POST | /auth/login | Đăng nhập → JWT | Public |
| POST | /auth/refresh | Cấp lại access token | Public |
| GET | /products | Danh sách + lọc/tìm/phân trang | Public |
| GET | /products/{id} | Chi tiết | Public |
| POST/PUT/DELETE | /products, /products/{id} | CRUD sản phẩm | Admin |
| GET | /products/{id}/recommendations | Gợi ý tương tự / mua cùng | Public |
| GET | /recommendations/me | Gợi ý cá nhân hóa | Customer |
| GET/POST/PUT/DELETE | /cart, /cart/items/{id} | Giỏ hàng | Customer |
| POST | /orders | Tạo đơn | Customer |
| GET | /orders, /orders/{id} | Xem đơn | Customer/Admin |
| PATCH | /orders/{id}/status | Đổi trạng thái | Admin |
| POST | /payments/qr | Sinh mã QR cho đơn | Customer |
| POST | /payments/confirm | Xác nhận đã nhận tiền | Admin |
| GET | /admin/stats | Thống kê dashboard | Admin |

(Danh sách đầy đủ trong `docs/openapi.yaml` và sheet "API List" của bảng phân công.)

## 5. Thuật toán gợi ý sản phẩm

1. **Content-based (tương tự)**: vector hóa sản phẩm theo danh mục, hãng, khoảng giá, thông số (RAM, ROM, màn hình…) → tính **cosine similarity** → top N.
2. **Item-based Collaborative / "Mua cùng nhau"**: thống kê đồng xuất hiện trong đơn hàng (co-occurrence matrix, độ hỗ trợ/độ tin cậy – Apriori đơn giản).
3. **Cá nhân hóa**: kết hợp lịch sử xem, yêu thích, đã mua (có trọng số) → điểm gợi ý = `α·content + β·co-occurrence + γ·popularity`.
4. **Cold-start**: người dùng mới → hiển thị bán chạy / mới nhất.
5. Cài đặt bằng PHP + SQL, cache kết quả (bảng `product_similarity`, cập nhật bằng script cron/CLI).

## 6. Thanh toán QR

- Dùng chuẩn **VietQR**: URL ảnh QR `https://img.vietqr.io/image/{BANK}-{ACCOUNT}-compact2.png?amount={AMOUNT}&addInfo={ORDER_CODE}&accountName={NAME}` (kiểm tra lại tài liệu VietQR trước khi dùng).
- Luồng: tạo đơn → hiển thị QR + đếm ngược → khách chuyển khoản → admin xác nhận thủ công (hoặc webhook giả lập để demo) → đơn chuyển "Đã thanh toán".
- Lưu ý: không lưu thông tin thẻ; số tài khoản để trong `.env`.

## 7. Bảo mật

- Mật khẩu: `password_hash()` (bcrypt/argon2).
- Chống SQL Injection: PDO prepared statements 100%.
- Chống XSS: escape đầu ra, kiểm soát upload (kiểm tra mime, đổi tên file).
- JWT: hạn ngắn (15–60 phút) + refresh token lưu DB; kiểm tra role ở middleware.
- CORS, rate limit, validate input phía server.

## 8. Cài đặt & chạy

```bash
git clone https://github.com/trannam1304/TechStore 
cp .env.example .env            # chỉnh DB, JWT_SECRET, thông tin ngân hàng
mysql -u root -p < database/schema.sql
mysql -u root -p techstore < database/seed.sql
# Đặt document root vào /public (XAMPP/Laragon) hoặc:
php -S localhost:8000 -t public
# Hoặc khởi chạy nhanh bằng file .bat trên Windows:
start.bat
```

Tài khoản demo (seed): `admin@techstore.local / Admin@123`, `user@techstore.local / User@123`.

## 9. Nhóm thực hiện

| Vai trò | Thành viên | Phụ trách chính |
|---|---|---|
| Leader / Tech Lead / Back-end Core | (Hữu Nam) | Kiến trúc, Auth/JWT, Order & QR, tích hợp, quản lý repo |
| Back-end & Thuật toán | (Thành Trung | Sản phẩm, tìm kiếm, gợi ý, admin API, CSDL |
| Front-end & Tài liệu | (Tuấn Kiệt) | UI khách + admin, tiêu thụ API, essay, Postman |

## 10. Tài liệu liên quan
- [Quy trình Git](GIT_WORKFLOW.md)
- Bảng phân công: `TechStore_PhanCong_8Ngay.xlsx`
- Essay REST: `docs/essay_rest.docx`
- Postman collection: `docs/postman_collection.json`
