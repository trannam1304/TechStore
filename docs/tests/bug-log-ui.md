# TECHSTORE – BẢNG THEO DÕI KIỂM THỬ GIAO DIỆN & BUG LOG UI
**Nhiệm vụ:** T50 (Kiểm thử giao diện) & T53 (Sửa lỗi UI theo bug log)  
**Người thực hiện:** Tuấn Kiệt (Front-end Developer)  
**Dự án:** TechStore – Website TMĐT Đồ Công Nghệ (Topic 1: RESTful API)  
**Tiêu chuẩn chất lượng (DoD):** ≥ 10 test case, kiểm tra responsive, đa trình duyệt, validate form, xử lý lỗi API và trải nghiệm người dùng.

---

## 1. Danh sách Ma trận Kiểm thử Giao diện (UI Test Cases)

| ID | Chức năng kiểm tra | Kịch bản / Thao tác | Kết quả mong đợi | Trình duyệt & Thiết bị | Trạng thái |
|---|---|---|---|---|---|
| **TC-01** | Header & Navbar Responsive | Thu nhỏ màn hình xuống kích thước Mobile (< 768px) và mở menu điều hướng | Header tự co giãn gọn gàng, thanh tìm kiếm hiển thị dạng mobile, nút mở menu danh mục hoạt động mượt mà | Chrome, Safari (iPhone 14 / 390px) | **PASS** |
| **TC-02** | Live Search Debounce | Gõ từ khóa `"iphone"` vào ô tìm kiếm toàn trang | Sau 250ms ngừng gõ, hiển thị dropdown 5 gợi ý nhanh kèm hình ảnh, tên và giá; nhấn vào chuyển đến chi tiết sản phẩm | Chrome 120 (Desktop) | **PASS** |
| **TC-03** | Bộ lọc Catalog (Products) | Chọn danh mục "Laptop", chọn hãng "Apple", lọc giá "15tr - 30tr" | Danh sách sản phẩm lập tức cập nhật theo đúng tiêu chí lọc kết hợp; đếm số lượng hiển thị chính xác | Edge, Firefox (Desktop) | **PASS** |
| **TC-04** | Form Đăng ký tài khoản | Nhập thiếu email hoặc mật khẩu dưới 6 ký tự rồi nhấn "Đăng ký" | Hiển thị thông báo toast lỗi màu đỏ, viền input đổi màu cảnh báo, không gửi request lỗi | Chrome, Safari | **PASS** |
| **TC-05** | Form Đăng nhập & Lưu token | Đăng nhập tài khoản khách hàng `user@techstore.local` / `User@123` | Nhận JWT access token & refresh token, lưu vào `localStorage`, navbar cập nhật tên người dùng | Chrome, Edge | **PASS** |
| **TC-06** | Auth Guard Admin Panel | Người dùng khách hàng truy cập trực tiếp URL `/admin/index.html` | Hệ thống kiểm tra quyền `role !== 'admin'`, hiển thị thông báo toast lỗi và tự động điều hướng về `/pages/login.html` | Chrome 120 | **PASS** |
| **TC-07** | Thêm nhanh giỏ hàng & Badge | Nhấn nút "🛒 Thêm giỏ" trên thẻ sản phẩm ở Trang chủ / Catalog | Hiển thị toast thành công, số đếm trên badge giỏ hàng góc trên navbar tự động tăng tức thì | Chrome, Firefox, Safari | **PASS** |
| **TC-08** | Cập nhật số lượng giỏ hàng | Tăng/giảm số lượng bằng nút `+` / `-` trong trang `cart.html` | Tổng tiền từng sản phẩm và tổng tiền thanh toán toàn bộ giỏ hàng cập nhật realtime không cần tải lại trang | Chrome 120 | **PASS** |
| **TC-09** | Áp dụng Mã giảm giá (Coupon) | Nhập mã `TECH10` cho đơn hàng > 1.000.000đ | Giảm 10% (tối đa 500k), hiển thị dòng giảm giá màu xanh và cập nhật số tiền phải thanh toán | Chrome 120 | **PASS** |
| **TC-10** | Luồng Thanh toán VietQR | Đặt hàng chọn phương thức "Chuyển khoản VietQR" | Chuyển đến trang `payment-qr.html`, hiển thị mã VietQR chuẩn NAPAS247 ngân hàng MB, bộ đếm ngược 15 phút kích hoạt | Chrome, Safari Mobile | **PASS** |
| **TC-11** | Polling Trạng thái Thanh toán | Ở trang QR, giả lập tiền về (hoặc nhấn nút "Demo Giả lập thanh toán") | Cơ chế polling phát hiện trạng thái `paid`, dừng đếm ngược, chuyển giao diện sang màn hình "Thanh toán thành công" | Chrome 120 | **PASS** |
| **TC-12** | Đánh giá & Yêu thích (Wishlist) | Nhấn nút icon trái tim ❤️ trên sản phẩm và gửi đánh giá 5 sao kèm bình luận | Thêm sản phẩm vào tab Yêu thích trong trang tài khoản; đánh giá hiển thị ngay lập tức trong tab Đánh giá | Chrome, Edge | **PASS** |

---

## 2. Nhật ký Sửa lỗi Giao diện (Bug Log UI - T53)

| Mã lỗi | Mô tả lỗi phát hiện | Nguyên nhân | Giải pháp khắc phục | Trạng thái |
|---|---|---|---|---|
| **BUG-01** | Nhấn "Thêm giỏ hàng" tại `products.html` bị lỗi `quickAddToCart is not defined` | `quickAddToCart` ban đầu chỉ nằm trong file `recommend.js`, trang `products.html` chưa import file này | Chuyển hàm `quickAddToCart` và `toggleWishlist` sang gắn trực tiếp vào `window` trong `api.js` (file dùng chung cho 100% trang) | **ĐÃ ĐÓNG (FIXED)** |
| **BUG-02** | Tab "Danh sách yêu thích" trong `account.html` hiển thị sản phẩm cố định, không xóa được | Chưa gọi endpoint `/wishlist` thực tế | Tích hợp hàm `api.wishlist.get()` và bổ sung nút `removeFromWishlist(id)` gọi `DELETE /wishlist/{id}` | **ĐÃ ĐÓNG (FIXED)** |
| **BUG-03** | Bảng sản phẩm Admin hiển thị lỗi khi API trả về định dạng phân trang `{ items: [...] }` | Code Admin chỉ đọc `res.data` dạng mảng | Viết lại logic: `Array.isArray(res.data) ? res.data : (res.data?.items || [])` hỗ trợ cả 2 định dạng | **ĐÃ ĐÓNG (FIXED)** |
| **BUG-04** | Bộ đếm thời gian 15 phút tại trang QR tiếp tục chạy ngay cả khi đã thanh toán thành công | Chưa `clearInterval` khi chuyển trạng thái | Thêm `clearInterval(countdownTimer)` và `clearInterval(pollInterval)` trong hàm `showSuccessState()` | **ĐÃ ĐÓNG (FIXED)** |
| **BUG-05** | Form thêm sản phẩm Admin không xem trước được ảnh khi upload file từ máy tính | Chưa có FileReader chuyển đổi sang DataURL | Bổ sung hàm `handleFileUpload(event)` dùng `FileReader` để preview ảnh tức thì và lưu base64/URL | **ĐÃ ĐÓNG (FIXED)** |

---
**Xác nhận hoàn thành:** Tuấn Kiệt đã kiểm thử toàn diện trên Google Chrome, Microsoft Edge, Firefox và giả lập Mobile Safari. Mọi lỗi giao diện đã được đóng và đạt chuẩn nghiệm thu (DoD).
