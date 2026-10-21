# TECHSTORE – KỊCH BẢN THUYẾT TRÌNH VIDEO & DÀN Ý SLIDE
**Nhiệm vụ:** T60 (Slide thuyết trình) & T68 (Kịch bản quay phần của Kiệt)  
**Người trình bày:** Tuấn Kiệt (Thành viên C – Phụ trách Front-end & Giao diện)  
**Thời lượng phân bổ:** **5.0 phút** (trên tổng số 18 phút video toàn nhóm – Khớp Sheet 6 'Video')

---

## 1. Bảng Phân Đoạn & Bấm Giờ Chi Tiết (Phần của Kiệt: Phút 11:00 → 16:00)

| Phân đoạn | Thời lượng | Nội dung thuyết trình | Màn hình hiển thị & Slide tương ứng |
|---|---|---|---|
| **Phần 7: Giải thích Code Front-end** | **1.5 phút** (90 giây) | - Giới thiệu cấu trúc tầng Front-end & Design Tokens CSS lấy cảm hứng từ CellphoneS.<br>- Giải thích chi tiết lớp `ApiClient` trong `api.js`: Fetch wrapper, tự động gắn Header `Authorization: Bearer <token>`.<br>- Giải thích cơ chế tự động bắt mã lỗi `401 Unauthorized` và tự động gửi refresh token để tái cấp quyền trong nền.<br>- Cơ chế Smart Mock Fallback giúp ứng dụng chạy độc lập không phụ thuộc môi trường.<br>- Cơ chế Polling thời gian thực tại trang thanh toán VietQR. | - Slide 1: Kiến trúc Tầng Front-end & Design Tokens.<br>- Slide 2: Sơ đồ luồng xử lý JWT Interceptor & Refresh Token trong `api.js`.<br>- Màn hình IDE Visual Studio Code (Cỡ chữ ≥ 16pt) trỏ trực tiếp vào file `public/assets/js/api.js`. |
| **Phần 8: Demo Khách Hàng (Customer)** | **2.0 phút** (120 giây) | - Đăng ký tài khoản mới & Đăng nhập 1-chạm.<br>- Tìm kiếm sản phẩm thông minh bằng Live Search (debounce 250ms), thử lọc sản phẩm theo mức giá và thương hiệu.<br>- Mở chi tiết iPhone 16 Pro Max: xem thông số kỹ thuật, khối mua kèm phụ kiện combo giảm 8%, các sản phẩm tương tự.<br>- Thêm giỏ hàng, cập nhật số lượng realtime, nhập mã giảm giá `TECH10` giảm 500.000đ.<br>- Đặt hàng chọn "Chuyển khoản VietQR" → Chuyển sang trang QR.<br>- Trình diễn mã QR MBBank, đồng hồ đếm ngược 15 phút và giả lập thanh toán thành công qua cơ chế Polling 3s.<br>- Vào trang cá nhân kiểm tra lịch sử đơn hàng và thử gửi đánh giá 5 sao. | - Trình duyệt Google Chrome (phóng to 110%):<br>1. `pages/index.html`<br>2. `pages/products.html`<br>3. `pages/product-detail.html`<br>4. `pages/cart.html`<br>5. `pages/checkout.html`<br>6. `pages/payment-qr.html`<br>7. `pages/account.html` |
| **Phần 9: Demo Quản Trị (Admin Panel)** | **1.5 phút** (90 giây) | - Đăng nhập tài khoản Admin `admin@techstore.local`.<br>- Trực quan hóa Dashboard: Chỉ số KPI doanh thu, tăng trưởng, đơn mới và biểu đồ cột doanh thu 7 ngày.<br>- Quản lý sản phẩm (`admin/products.html`): Thêm mới sản phẩm có preview ảnh tải lên từ máy tính; sửa giá bán; xóa sản phẩm.<br>- Quản lý đơn hàng (`admin/orders.html`): Bấm "Duyệt TT" để xác nhận tiền về, mở modal đổi trạng thái đơn sang Đang giao/Hoàn tất; trình diễn tính năng in hóa đơn bán hàng A4.<br>- Quản lý người dùng (`admin/users.html`) & Mã giảm giá (`admin/coupons.html`). | - Trình duyệt mở phân hệ Quản trị viên:<br>1. `admin/index.html`<br>2. `admin/products.html`<br>3. `admin/orders.html`<br>4. `admin/users.html`<br>5. `admin/coupons.html` |

---

## 2. Lời Thoại Thuyết Trình Mẫu (Speaking Script từng giây)

### 2.1. Phần 7: Giải thích kiến trúc Code Front-end (1.5 phút)
> *"Xin chào thầy và các bạn, em là Tuấn Kiệt, thành viên phụ trách toàn bộ giao diện người dùng và tích hợp API của dự án TechStore. Sau đây em xin phép trình bày về cấu trúc mã nguồn front-end.*  
> *Về mặt giao diện, hệ thống được xây dựng hoàn toàn bằng HTML5, CSS3 và JavaScript thuần, lấy cảm hứng thiết kế từ hệ thống bán lẻ CellphoneS với bộ mã thiết kế tokens.css chuẩn hóa màu đỏ Crimson Red `#d70018`. Toàn bộ giao tiếp giữa máy khách và máy chủ được điều phối tập trung thông qua lớp ApiClient trong file api.js.*  
> *Đặc biệt, api.js cài đặt cơ chế JWT Interceptor: mỗi khi gửi request, token được tự động đính kèm vào HTTP header. Nếu access token hết hạn dẫn đến mã phản hồi 401 Unauthorized, client sẽ tự động chặn request, kích hoạt hàm refreshToken() để xin cấp access token mới và thử lại request ban đầu một cách hoàn toàn trong suốt với người dùng.*  
> *Bên cạnh đó, thư viện còn tích hợp sẵn cơ chế Smart Mock Fallback trên localStorage, đảm bảo hệ thống có thể chạy demo độc lập trên mọi máy sạch mà không bao giờ gặp lỗi giao diện trắng hay lỗi console."*

### 2.2. Phần 8: Demo trải nghiệm mua sắm Khách hàng (2.0 phút)
> *"Tiếp theo, em xin phép demo trực tiếp luồng mua hàng thực tế của khách hàng:*  
> *Từ trang chủ, khi em gõ từ khóa 'iPhone' vào thanh tìm kiếm, cơ chế debounce sau 250ms sẽ gọi API và hiển thị ngay danh sách gợi ý. Bấm vào sản phẩm, trang chi tiết hiển thị đầy đủ thư viện ảnh, bảng thông số kỹ thuật chi tiết của chip A18 Pro, màn hình 120Hz, cùng khối gợi ý mua kèm phụ kiện được giảm 8% combo.*  
> *Em thêm sản phẩm vào giỏ hàng. Tại trang cart.html, số lượng và tổng tiền được tính toán realtime. Em nhập mã giảm giá TECH10 và hệ thống lập tức trừ 500.000đ theo đúng quy tắc.*  
> *Khi tiến hành thanh toán, em chọn hình thức 'Chuyển khoản VietQR'. Ngay khi nhấn Đặt hàng, API /payments/qr sẽ sinh ra mã QR chuẩn NAPAS247 của ngân hàng Quân Đội MBBank đi kèm chính xác số tiền và mã đơn hàng. Đồng hồ đếm ngược 15 phút bắt đầu chạy, đồng thời cơ chế Polling gửi yêu cầu kiểm tra mỗi 3 giây. Em nhấn nút giả lập thanh toán thành công — ngay lập tức hệ thống nhận diện tiền về, đóng đồng hồ và chuyển sang màn hình chúc mừng. Đơn hàng lập tức được lưu vào lịch sử tài khoản với trạng thái Đã thanh toán."*

### 2.3. Phần 9: Demo phân hệ Quản trị viên Admin (1.5 phút)
> *"Cuối cùng là phân hệ Quản trị viên tại địa chỉ /admin:*  
> *Tại bảng điều khiển Dashboard, Admin có thể theo dõi ngay các chỉ số tổng doanh thu, tỷ lệ tăng trưởng cùng biểu đồ cột doanh thu 7 ngày trong tuần được vẽ trực quan.*  
> *Tại trang quản lý sản phẩm, Admin có thể tìm kiếm, lọc theo hãng và thêm mới sản phẩm với tính năng xem trước ảnh upload trực tiếp từ máy tính. Thông tin sản phẩm sau khi lưu sẽ đồng bộ ngay lập tức với cơ sở dữ liệu.*  
> *Đặc biệt tại trang quản lý đơn hàng, Admin có thể bấm nút 'Duyệt TT' để xác nhận nhanh tiền chuyển khoản ngân hàng, hoặc mở modal để đổi trạng thái đơn hàng sang Đang giao hoặc Hoàn tất. Hệ thống cũng tích hợp sẵn mẫu in hóa đơn bán hàng chuyên nghiệp để in kèm đơn bưu phẩm.*  
> *Nhờ kiến trúc tách rời và tiêu thụ chuẩn xác 37 REST API, tầng front-end của TechStore mang lại trải nghiệm mượt mà, phản hồi tức thì và đáp ứng hoàn hảo các yêu cầu của đồ án. Em xin chân thành cảm ơn thầy và các bạn đã lắng nghe!"*
