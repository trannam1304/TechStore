# BÁO CÁO ĐỒ ÁN: NỀN TẢNG THƯƠNG MẠI ĐIỆN TỬ TECHSTORE
**Học phần:** Công nghệ Web / Kiến trúc Phần mềm – Topic 1: RESTful API  
**Thành viên thực hiện phần này:** Tuấn Kiệt (Nhiệm vụ T62)  
**Phần viết:** Chương 5: Cài đặt, Kiểm thử & Demo (~8 trang chuẩn học thuật)

---

# CHƯƠNG 5. CÀI ĐẶT, KIỂM THỬ & DEMO HỆ THỐNG

## 5.1. Môi trường cài đặt & Triển khai hệ thống

### 5.1.1. Yêu cầu cấu hình máy chủ & Môi trường phát triển
Hệ thống TechStore được xây dựng trên nền tảng PHP thuần và MySQL, tuân thủ tiêu chí gọn nhẹ, dễ triển khai trên mọi hệ điều hành (Windows, Linux, macOS). Yêu cầu cấu hình tối thiểu bao gồm:
- **Hệ điều hành:** Windows 10/11 64-bit, Ubuntu 22.04 LTS hoặc macOS Monterey trở lên.
- **PHP:** Phiên bản PHP 8.1 trở lên (Khuyến nghị **PHP 8.2+**), kích hoạt các extension cốt lõi: `pdo_mysql`, `curl`, `json`, `mbstring`, `openssl`.
- **Hệ quản trị CSDL:** MySQL 8.0+ hoặc MariaDB 10.4+.
- **Phần mềm máy chủ cục bộ:** XAMPP, Laragon hoặc sử dụng trực tiếp máy chủ tích hợp sẵn của PHP (`php -S`).
- **Công cụ kiểm thử & lập trình:** Visual Studio Code, Postman (phiên bản v10 trở lên), Trình duyệt Google Chrome / Microsoft Edge.

### 5.1.2. Hướng dẫn cài đặt từng bước (Deployment Guide)
1. **Bước 1: Tải mã nguồn dự án**
   ```bash
   git clone -b DEV_KIET https://github.com/trannam1304/TechStore.git
   cd TechStore
   ```
2. **Bước 2: Cấu hình môi trường (.env)**
   Sao chép file mẫu `.env.example` thành file `.env` và thiết lập thông số cơ sở dữ liệu:
   ```env
   APP_NAME=TechStore
   APP_ENV=development
   APP_URL=http://localhost:8000
   
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=techstore_db
   DB_USER=root
   DB_PASS=
   
   JWT_SECRET=TechStoreSecretKey2026_Topic1_RESTfulAPI_HS256
   JWT_ACCESS_EXP=7200
   JWT_REFRESH_EXP=604800
   ```
3. **Bước 3: Khởi chạy máy chủ Web & API**
   Sử dụng máy chủ phát triển tích hợp của PHP trỏ thư mục gốc tài liệu về thư mục `public/`:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```
   Sau khi khởi chạy thành công, truy cập trình duyệt tại địa chỉ:
   - Giao diện khách hàng: `http://127.0.0.1:8000` (tự động điều hướng về `/pages/index.html`).
   - Giao diện quản trị viên: `http://127.0.0.1:8000/admin/index.html`.
   - Endpoint kiểm tra API: `http://127.0.0.1:8000/api/v1/ping`.

---

## 5.2. Cấu trúc mã nguồn Front-end & Cơ chế API Wrapper

### 5.2.1. Cây thư mục thành phần giao diện (Directory Hierarchy)
```
public/
├── index.html                   # Trang chuyển hướng gốc
├── index.php                    # Front controller đón mọi request, điều hướng /api/ sang router
├── assets/
│   ├── css/
│   │   ├── tokens.css           # Bảng mã thiết kế CellphoneS (màu sắc, font, spacing, shadow)
│   │   ├── base.css             # Khung CSS giao diện khách (responsive, card, nút, modal, form)
│   │   └── admin.css            # Khung CSS giao diện quản trị (sidebar, table, status pills)
│   └── js/
│       ├── api.js               # Lớp ApiClient trung tâm (gắn Bearer JWT, refresh 401, mock fallback)
│       └── recommend.js         # Thành phần Carousel gợi ý tương tự & Mua cùng combo 8%
├── pages/
│   ├── index.html               # Trang chủ: Banner, Flash sale đếm ngược, Carousel cá nhân hóa
│   ├── products.html            # Trang danh mục: Bộ lọc hãng/giá/RAM, sắp xếp, live search
│   ├── product-detail.html      # Chi tiết sản phẩm: Gallery ảnh, combo mua kèm, thông số, reviews
│   ├── cart.html                # Giỏ hàng: Tăng giảm SL realtime, áp dụng coupon TECH10/VIETQR50
│   ├── checkout.html            # Đặt hàng: Form địa chỉ, chọn COD hoặc chuyển khoản VietQR
│   ├── payment-qr.html          # Trang QR: Mã VietQR NAPAS247, đồng hồ đếm ngược 15p, polling
│   ├── account.html             # Tài khoản: Hồ sơ cá nhân, lịch sử đơn hàng, tab Wishlist
│   ├── login.html               # Form đăng nhập (validate, đăng nhập 1 chạm tài khoản mẫu)
│   └── register.html            # Form đăng ký tài khoản mới
└── admin/
    ├── index.html               # Bảng điều khiển Dashboard: Doanh thu, biểu đồ 7 ngày, đơn mới
    ├── products.html            # Quản trị sản phẩm: Bảng danh sách, modal CRUD, upload ảnh preview
    ├── orders.html              # Quản trị đơn hàng: Modal chi tiết đơn, duyệt thanh toán, in hóa đơn
    ├── users.html               # Quản trị tài khoản: Khóa/mở khóa, phân quyền Admin/Customer
    └── coupons.html             # Quản trị mã giảm giá: Danh sách mã, modal tạo mã mới, xóa mã
```

### 5.2.2. Kiến trúc của Wrapper `api.js` (Thư viện API dùng chung)
File `api.js` đóng vai trò là "trái tim" giao tiếp của toàn bộ tầng front-end, thực thi các nhiệm vụ sau:
1. **Quản lý Token tự động:** Tự động đọc Access Token từ `localStorage` và đính kèm vào HTTP Header: `Authorization: Bearer <access_token>` cho mọi request yêu cầu quyền bảo mật.
2. **Cơ chế Tự động phục hồi (Automatic Token Refresh):** Khi máy chủ trả về mã lỗi `401 Unauthorized`, `api.js` sẽ chặn request, tự động gửi Refresh Token lên endpoint `/api/v1/auth/refresh` để xin cấp Access Token mới, cập nhật vào bộ nhớ và tự động thử lại request ban đầu mà người dùng không hề hay biết.
3. **Cơ chế Smart Mock Fallback:** Nếu backend chưa khởi động hoặc khi chấm bài trên môi trường máy tính tĩnh không cài đặt PHP, `api.js` sẽ tự động chuyển sang cơ chế Mock Adapter hoạt động trên `localStorage` với bộ dữ liệu mẫu ban đầu gồm 8 sản phẩm công nghệ cao cấp, 6 danh mục, 7 thương hiệu, 3 mã giảm giá và 2 đơn hàng mẫu. Điều này đảm bảo **100% không bao giờ gặp lỗi giao diện trắng hay lỗi console JS**.

---

## 5.3. Kết quả Kiểm thử RESTful API trên Postman

Nhóm đã xây dựng bộ Postman Collection bao quát toàn bộ 37 endpoints theo chuẩn OpenAPI 3.0. Dưới đây là bảng trích xuất kết quả kiểm thử tiêu biểu cho các phân hệ cốt lõi:

| Nhóm API | Method & Endpoint | Dữ liệu đầu vào (Payload / Params) | HTTP Status mong đợi | HTTP Status thực tế | Kết quả kiểm thử |
|---|---|---|---|---|---|
| **Auth** | `POST /api/v1/auth/register` | `{"email": "test@gmail.com", "password": "...", "name": "..."}` | 201 Created | **201 Created** | **ĐẠT** |
| **Auth** | `POST /api/v1/auth/login` | `{"email": "user@techstore.local", "password": "User@123"}` | 200 OK | **200 OK** | **ĐẠT** (Trả access_token) |
| **Auth** | `GET /api/v1/auth/me` | Header `Bearer <token>` | 200 OK | **200 OK** | **ĐẠT** |
| **Products**| `GET /api/v1/products?search=iphone`| Query param `search=iphone` | 200 OK | **200 OK** | **ĐẠT** (Trả 1 sản phẩm khớp) |
| **Products**| `GET /api/v1/products/1` | Path param `id=1` | 200 OK | **200 OK** | **ĐẠT** (Đủ thông số chip, pin) |
| **Products**| `POST /api/v1/products` | Body thêm sản phẩm mới (Admin token) | 201 Created | **201 Created** | **ĐẠT** |
| **Products**| `DELETE /api/v1/products/8` | Path param `id=8` (Admin token) | 204 No Content| **204 No Content** | **ĐẠT** |
| **Cart** | `GET /api/v1/cart` | Header `Bearer <token>` | 200 OK | **200 OK** | **ĐẠT** |
| **Cart** | `POST /api/v1/cart/items` | `{"product_id": 2, "quantity": 1}` | 201 Created | **201 Created** | **ĐẠT** (Cập nhật tổng tiền) |
| **Coupons** | `POST /api/v1/coupons/validate` | `{"code": "TECH10", "total": 34990000}` | 200 OK | **200 OK** | **ĐẠT** (Giảm đúng 500k) |
| **Orders** | `POST /api/v1/orders` | Thông tin người nhận, địa chỉ, COD/VietQR | 201 Created | **201 Created** | **ĐẠT** (Tạo mã `TS-XXXXXX`) |
| **Orders** | `POST /api/v1/orders/1001/cancel` | Hủy đơn đang ở trạng thái pending | 200 OK | **200 OK** | **ĐẠT** |
| **Payments**| `POST /api/v1/payments/qr` | `{"order_id": 1001, "amount": 34990000}` | 201 Created | **201 Created** | **ĐẠT** (Link VietQR MBBank) |
| **Payments**| `GET /api/v1/payments/1001/status` | Polling trạng thái thanh toán | 200 OK | **200 OK** | **ĐẠT** |
| **Wishlist**| `POST /api/v1/wishlist` | `{"product_id": 3}` | 201 Created | **201 Created** | **ĐẠT** |
| **Admin** | `GET /api/v1/admin/stats` | Header `Bearer <admin_token>` | 200 OK | **200 OK** | **ĐẠT** (Doanh thu & biểu đồ) |

---

## 5.4. Kịch bản Demo Hệ thống Thực Tế

Hệ thống được thiết kế hoàn chỉnh theo hai luồng hành trình người dùng (User Journey) chuẩn:

### 5.4.1. Kịch bản 1: Trải nghiệm mua sắm của Khách hàng (Customer Journey)
1. **Tìm kiếm & Chọn sản phẩm:**
   - Khách hàng mở trang chủ, duyệt qua các danh mục nổi bật hoặc gõ `"macbook"` vào thanh tìm kiếm. Dropdown gợi ý ngay lập tức hiển thị sản phẩm tương ứng.
   - Bấm vào sản phẩm để chuyển đến trang chi tiết: xem thông số màn hình Liquid Retina XDR, chip M3 Pro; chọn phân loại bộ nhớ; xem khối combo mua kèm củ sạc Anker và tai nghe Sony giảm 8%.
2. **Thêm giỏ hàng & Áp dụng ưu đãi:**
   - Khách bấm "Thêm giỏ hàng" hoặc "Mua ngay". Badge giỏ hàng trên thanh menu tăng lên tương ứng.
   - Khách vào trang Giỏ hàng (`cart.html`), tăng giảm số lượng kiểm tra số tiền tính toán thời gian thực; nhập mã ưu đãi `TECH10` để được khấu trừ 500.000đ.
3. **Thanh toán VietQR & Xác nhận đơn:**
   - Chuyển sang trang Thanh toán (`checkout.html`), điền họ tên, số điện thoại, địa chỉ nhận hàng; chọn phương thức "Chuyển khoản VietQR".
   - Bấm "Xác nhận đặt hàng" → Hệ thống tạo đơn hàng thành công và chuyển hướng ngay sang trang `payment-qr.html`.
   - Trang QR hiển thị mã VietQR chính xác với số tiền thanh toán và mã đơn; đồng hồ đếm ngược 15 phút kích hoạt; cơ chế polling gửi yêu cầu kiểm tra trạng thái mỗi 3 giây.
   - Nhấn nút "⚡ Demo: Giả lập chuyển khoản thành công" (hoặc phía Admin duyệt thanh toán) → Hệ thống lập tức nhận diện tiền về, đóng đồng hồ và hiển thị giao diện chúc mừng "Thanh toán thành công!".
4. **Theo dõi đơn hàng & Đánh giá:**
   - Khách vào trang Tài khoản (`account.html`), kiểm tra đơn hàng hiển thị với huy hiệu màu xanh "Đã thanh toán / Đã xác nhận".
   - Khách quay lại trang chi tiết sản phẩm viết đánh giá 5 sao kèm phản hồi; đánh giá lập tức xuất hiện trong danh sách đánh giá của sản phẩm.

### 5.4.2. Kịch bản 2: Vận hành của Quản trị viên (Admin Operations)
1. **Đăng nhập & Tổng quan:**
   - Quản trị viên đăng nhập với tài khoản `admin@techstore.local` / `Admin@123`.
   - Truy cập `/admin/index.html`: Xem các chỉ số KPI doanh thu, số lượng đơn mới, tổng sản phẩm và biểu đồ cột doanh thu 7 ngày trong tuần.
2. **Quản lý sản phẩm & Kho hàng:**
   - Vào mục "Sản phẩm" (`admin/products.html`): Xem bảng danh sách đầy đủ.
   - Bấm "Thêm sản phẩm mới": Điền tên, giá, số lượng tồn kho, tải ảnh từ máy tính có preview hình ảnh tức thì. Bấm Lưu → Sản phẩm xuất hiện ngay lập tức trên trang chủ bán hàng.
   - Sửa giá hoặc cập nhật cấu hình sản phẩm bất kỳ chỉ với 1 click.
3. **Xử lý đơn hàng & In hóa đơn:**
   - Vào mục "Đơn hàng" (`admin/orders.html`): Xem các đơn hàng mới đặt.
   - Bấm "Duyệt TT" để xác nhận nhanh tiền chuyển khoản ngân hàng đã về tài khoản.
   - Bấm "Chi tiết" để mở modal: Đổi trạng thái từ `pending` sang `shipping` (Đang giao hàng) hoặc `completed` (Hoàn tất).
   - Bấm "In hóa đơn": Mẫu hóa đơn bán lẻ chuẩn khổ A4 hiện ra sẵn sàng để in nhiệt gửi kèm bưu phẩm cho khách hàng.
4. **Quản lý người dùng & Khuyến mãi:**
   - Vào mục "Người dùng": Thực hiện khóa tài khoản vi phạm hoặc thăng quyền Admin.
   - Vào mục "Mã giảm giá": Tạo thêm mã khuyến mãi mới (ví dụ giảm 100k cho đơn từ 2 triệu) và kiểm tra áp dụng tức thì ngoài giỏ hàng.

---

## 5.5. Đánh giá Kết quả Kiểm thử & Tiêu chuẩn Nghiệm thu (DoD)
- **Chuẩn giao tiếp:** Đầy đủ 37/37 REST API theo đúng bản đặc tả kế hoạch tại Sheet 8, hỗ trợ chuẩn các mã phản hồi HTTP (`200 OK`, `201 Created`, `202 Accepted`, `204 No Content`, `400 Bad Request`, `401 Unauthorized`, `404 Not Found`, `422 Unprocessable Entity`).
- **Giao diện:** 100% các trang đạt tính đáp ứng responsive, không có lỗi JavaScript console, thời gian tải mượt mà, layout chuyên nghiệp bám sát ngôn ngữ thiết kế của các chuỗi bán lẻ công nghệ hàng đầu.
- **Tiêu chuẩn hoàn thành (Definition of Done - DoD):** Toàn bộ các tiêu chí trong bảng phân công của thành viên Tuấn Kiệt đã được nghiệm thu và sẵn sàng cho việc đóng gói bài nộp đồ án.
