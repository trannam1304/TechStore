# TechStore – Tài Liệu Wireframe & UI Kit (Design Tokens)

> Phụ trách: **Tuấn Kiệt (Front-end & Documentation)**  
> Mã nhiệm vụ: **T07**  
> Dự án: TechStore – Nền tảng thương mại điện tử công nghệ (Tham khảo CellphoneS)

---

## 1. Hệ Thống Design Tokens (`public/assets/css/tokens.css`)

### 1.1 Bảng màu (Color Palette)
- **Primary Brand Red**: `#d70018` (Màu đỏ đặc trưng CellphoneS, kích thích chuyển đổi)
- **Primary Hover**: `#b30014`
- **Primary Light / Background Tint**: `#fff0f1`
- **Secondary Blue**: `#0066cc` (Nút phụ, điểm nhấn công nghệ)
- **Neutrals**:
  - Dark Slate: `#1e293b` (Màu chữ tiêu đề, icon)
  - Text Body: `#475569`
  - Subtle Gray: `#64748b`
  - Border: `#e2e8f0`
  - Background Gray: `#f8fafc` / `#f1f5f9`
  - Pure White: `#ffffff`
- **Semantic Feedback**:
  - Success Green: `#10b981` (Đã thanh toán, còn hàng, áp coupon thành công)
  - Warning Amber: `#f59e0b` (Trả góp 0%, sắp hết hàng, rating)
  - Danger Red: `#ef4444` (Lỗi, hủy đơn, xóa sản phẩm)

### 1.2 Typography & Spacing
- **Font Family**: `'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif`
- **Font Sizes**: 12px (xs), 14px (sm), 16px (base), 18px (md), 20px (lg), 24px (xl), 30px (2xl), 36px (3xl)
- **Spacing**: 4px, 8px, 12px, 16px, 20px, 24px, 32px, 40px, 48px
- **Border Radius**: 4px (xs), 8px (md), 12px (lg), 16px (xl), 9999px (full)
- **Shadows**: Elevation shadow card, shadow hover, elevation modal backdrop

---

## 2. Kiến Trúc Giao Diện Phía Khách Hàng (Customer UI)

### 2.1 Luồng mua hàng & Thanh toán QR (User Journey)
```
[Trang chủ index.html]
       │
       ├──> [Danh sách sản phẩm products.html] (Lọc giá/hãng, tìm kiếm debounce)
       │              │
       │              ▼
       └───> [Chi tiết sản phẩm product-detail.html] (Gallery, thông số, combo mua cùng)
                      │
                      ▼
               [Thêm vào giỏ]
                      │
                      ▼
              [Giỏ hàng cart.html] (Đổi số lượng, áp mã giảm giá)
                      │
                      ▼
             [Thanh toán checkout.html] (Điền thông tin, chọn VietQR)
                      │
                      ▼
           [Quét mã QR payment-qr.html] (VietQR, đếm ngược 15:00, polling tự động)
                      │
                      ▼
           [Đã xác nhận & Xem đơn account.html#orders]
```

---

## 3. Wireframes Các Trang Chính

### 3.1 Trang Chủ (`pages/index.html`)
```
+-----------------------------------------------------------------------------------+
| Topbar: Hotline: 1800.6018 | Tra cứu đơn hàng | Trang Quản Trị                    |
+-----------------------------------------------------------------------------------+
| Logo: TechStore | [🔍 Ô tìm kiếm live auto-suggest........] | ❤️ Yêu thích | 🛒 Giỏ | 👤 User|
+-----------------------------------------------------------------------------------+
| [⚡ Tất cả] [📱 Điện thoại] [💻 Laptop] [📋 Tablet] [🎧 Âm thanh] [🔌 Phụ kiện]   |
+-----------------------------------------------------------------------------------+
| [ Hero Banner Slider: iPhone 16 Pro Max ] | [ Banner nhỏ: Galaxy S24 Ultra ]     |
| [ Nút "Khám phá ngay"                   ] | [ Banner nhỏ: MacBook Pro M3   ]     |
+-----------------------------------------------------------------------------------+
| 🛡️ 100% Chính hãng | 🚀 Giao 2 Giờ | 🔄 Đổi trả 30 ngày | 💳 VietQR Tự động        |
+-----------------------------------------------------------------------------------+
| ⚡ FLASH SALE GIÁ SỐC ⏳ Kết thúc sau: [ 03 : 45 : 20 ]                            |
| [Card SP 1]  [Card SP 2]  [Card SP 3]  [Card SP 4]  [Card SP 5]                   |
+-----------------------------------------------------------------------------------+
| 🛍️ GỢI Ý DÀNH RIÊNG CHO BẠN (Component recommend.js) ◀ ▶                          |
| [Card Gợi ý 1]  [Card Gợi ý 2]  [Card Gợi ý 3]  [Card Gợi ý 4]                    |
+-----------------------------------------------------------------------------------+
| Footer: Về TechStore | Chính sách | Danh mục | Cổng thanh toán VietQR             |
+-----------------------------------------------------------------------------------+
```

### 3.2 Trang Chi Tiết Sản Phẩm (`pages/product-detail.html`)
```
+-----------------------------------------------------------------------------------+
| Header & Search...                                                                |
+-----------------------------------------------------------------------------------+
| Breadcrumb: Trang chủ > Điện thoại > iPhone 16 Pro Max                            |
+-----------------------------------------------------------------------------------+
| [ Gallery Ảnh ]                 | Tên sản phẩm: iPhone 16 Pro Max 256GB           |
| [ Ảnh lớn xem trước           ] | Hãng: Apple • ★ 4.9 (142 đánh giá)              |
| [ Thumb 1 ] [ Thumb 2 ] [ Th 3] | Giá: 34.990.000₫ (Gốc: 37.990.000₫ -8%)         |
|                                 | Bộ nhớ: [256GB] [512GB] [1TB]                   |
|                                 | Màu sắc: [Titan Tự Nhiên] [Titan Sa Mạc]...     |
|                                 | Số lượng: [-] [ 1 ] [+]                         |
|                                 | [⚡ MUA NGAY]  [🛒 Thêm vào giỏ hàng]           |
+-----------------------------------------------------------------------------------+
| 🛍️ THƯỜNG ĐƯỢC MUA CÙNG (Bundle Combo 8% Off):                                   |
| [iPhone 16 Pro] + [Củ sạc GaN 65W] + [Tai nghe chống ồn] = [⚡ Mua cả 3 món]     |
+-----------------------------------------------------------------------------------+
| Tabs: [Thông số kỹ thuật]  [Mô tả chi tiết]  [Đánh giá & Bình luận (142)]         |
+-----------------------------------------------------------------------------------+
```

### 3.3 Trang Thanh Toán VietQR (`pages/payment-qr.html`)
```
+-----------------------------------------------------------------------------------+
|               Quét mã VietQR để thanh toán đơn hàng #TS-1001                      |
|               [🟢 Đang chờ quét mã chuyển khoản...]                               |
|               ⏳ Mã QR hết hạn sau: [ 14 : 59 ]                                   |
|                                                                                   |
|                   +-----------------------------------+                           |
|                   |                                   |                           |
|                   |         [ ẢNH VIETQR ]            |                           |
|                   |                                   |                           |
|                   +-----------------------------------+                           |
|                                                                                   |
|   +---------------------------------------------------------------------------+   |
|   | Ngân hàng thụ hưởng: MB Bank (Ngân hàng Quân Đội)                         |   |
|   | Số tài khoản: 0987654321                         [Sao chép]               |   |
|   | Chủ tài khoản: TECHSTORE VIETNAM                                          |   |
|   | Số tiền: 34.990.000₫                             [Sao chép]               |   |
|   | Nội dung chuyển khoản: TS-1001                   [Sao chép]               |   |
|   +---------------------------------------------------------------------------+   |
|                                                                                   |
|   [✓ Tôi đã chuyển khoản (Xác nhận Demo)]       [Xem đơn hàng của tôi]            |
+-----------------------------------------------------------------------------------+
```

---

## 4. Kiến Trúc Giao Diện Quản Trị (Admin Panel)

- **Admin Layout**:
  - Sidebar cố định bên trái (Dashboard, Sản phẩm, Đơn hàng, Mã giảm giá, Người dùng).
  - Topbar hiển thị tên quản trị viên và avatar Super Admin.
  - Phân quyền: Kiểm tra `api.isAdmin()` ở client; nếu không đúng quyền, chuyển hướng về trang đăng nhập.
  - Hỗ trợ in hóa đơn bán lẻ trực tiếp từ bảng quản lý đơn hàng (`orders.html` -> `window.print()`).
