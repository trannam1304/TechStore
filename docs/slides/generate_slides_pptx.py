# -*- coding: utf-8 -*-
"""
Generate official presentation slides (TechStore_Slides.pptx)
Task: T60
Author: Tuấn Kiệt (Thành viên C)
"""

import os
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.enum.shapes import MSO_SHAPE

def create_slides():
    prs = Presentation()
    # 16:9 Widescreen layout
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)

    blank_layout = prs.slide_layouts[6]

    # Colors
    c_crimson = RGBColor(215, 0, 24)
    c_dark = RGBColor(15, 23, 42)
    c_card_bg = RGBColor(248, 250, 252)
    c_gray = RGBColor(100, 116, 139)
    c_blue = RGBColor(37, 99, 235)
    c_green = RGBColor(16, 185, 129)
    c_white = RGBColor(255, 255, 255)

    slides_data = [
        # Slide 1: Cover
        {
            "type": "cover",
            "title": "TECHSTORE – NỀN TẢNG TMĐT ĐỒ CÔNG NGHỆ",
            "subtitle": "ỨNG DỤNG KIẾN TRÚC RESTful API, XÁC THỰC JWT, THANH TOÁN VIETQR & GỢI Ý THÔNG MINH",
            "meta": "Topic 1: RESTful API · Khóa 2022-2026\nNhóm thực hiện: Hữu Nam (Tech Lead) · Thành Trung (Database) · Tuấn Kiệt (Front-end & QA)"
        },
        # Slide 2: Objectives
        {
            "type": "content",
            "title": "1. MỤC TIÊU & YÊU CẦU ĐỀ TÀI",
            "bullets": [
                "Làm chủ nguyên lý thiết kế và hiện thực hóa chuẩn RESTful API bằng PHP thuần và MySQL 8.",
                "Chuẩn hóa đầy đủ 37 endpoints RESTful API bao quát 8 phân hệ nghiệp vụ TMĐT.",
                "Cài đặt cơ chế bảo mật xác thực JSON Web Token (JWT - RFC 7519) kèm Access & Refresh Token.",
                "Tích hợp phương thức thanh toán số thế hệ mới: Quét mã VietQR NAPAS247 và Polling thời gian thực.",
                "Phát triển giao diện Front-end hiện đại phong cách Glassmorphism + Liquid Aurora Mesh lấy cảm hứng từ CellphoneS.",
                "Xây dựng thuật toán gợi ý mua kèm phụ kiện (Frequently Bought Together) và sản phẩm tương tự."
            ]
        },
        # Slide 3: Architecture
        {
            "type": "content",
            "title": "2. KIẾN TRÚC TỔNG THỂ 3 TẦNG (DECOUPLED ARCHITECTURE)",
            "bullets": [
                "Tầng Trình Diễn (Presentation Layer): HTML5, CSS3 Tokens, Vanilla JavaScript ES6 (Fetch API, api.js wrapper). Hoàn toàn độc lập với phía máy chủ.",
                "Tầng Dịch Vụ Ứng Dụng (Application Gateway): PHP 8.x thuần, Front Controller, MVC Router điều hướng 37 REST endpoints.",
                "Tầng Dữ Liệu & Lưu Trữ (Data Persistence Layer): MySQL 8 quan hệ (15+ bảng) kết hợp JSON Storage Adapter chống đứt gãy môi trường.",
                "Tài Liệu Hóa Tương Tác: Cổng Swagger UI & Redoc tại đường dẫn /docs/index.html đọc trực tiếp từ openapi.yaml."
            ]
        },
        # Slide 4: JWT
        {
            "type": "content",
            "title": "3. CƠ CHẾ BẢO MẬT XÁC THỰC JWT (RFC 7519)",
            "bullets": [
                "Không sử dụng Session/Cookie truyền thống nhằm đảm bảo nguyên tắc vô trạng thái (Stateless) của RESTful API.",
                "Access Token: Thời hạn sống ngắn (15 phút), mã hóa HS256, chứa user_id, role, email.",
                "Refresh Token: Thời hạn sống dài (7 ngày), lưu an toàn, dùng để cấp phát access token mới.",
                "JWT Interceptor trong api.js: Tự động đính kèm 'Authorization: Bearer <token>', tự động bắt lỗi 401 Unauthorized để xin cấp lại token trong suốt với người dùng.",
                "Phân quyền RBAC (Role-Based Access Control): Phân định rõ ràng Customer và Admin cho từng endpoint."
            ]
        },
        # Slide 5: Database ERD
        {
            "type": "content",
            "title": "4. THIẾT KẾ CƠ SỞ DỮ LIỆU QUAN HỆ (ERD ≥ 15 BẢNG)",
            "bullets": [
                "Phân hệ Người dùng: users (id, name, email, password_hash, role, status), user_addresses.",
                "Phân hệ Danh mục & Hãng: categories (id, name, slug, icon), brands (id, name, logo).",
                "Phân hệ Sản phẩm: products, product_images, product_variants, product_attributes.",
                "Phân hệ Giỏ hàng & Khuyến mãi: carts, cart_items, coupons (code, discount_type, value, min_spend).",
                "Phân hệ Đơn hàng & Thanh toán: orders, order_items, payments (qr_code, status, trans_id).",
                "Phân hệ Tương tác: reviews (rating, comment), wishlists, event_logs (lịch sử xem để gợi ý)."
            ]
        },
        # Slide 6: 37 Endpoints & Swagger UI
        {
            "type": "content",
            "title": "5. ĐẶC TẢ 37 RESTful API & CỔNG SWAGGER UI / REDOC",
            "bullets": [
                "Tất cả 37 endpoints tuân thủ nguyên tắc Stateless, URI danh từ, chuẩn hóa HTTP Methods (GET, POST, PUT, DELETE).",
                "Chuẩn phản hồi thống nhất: { success: true/false, data: {...}, message: '...' }.",
                "Mã trạng thái chuẩn RFC 7231: 200 OK, 201 Created, 204 No Content, 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 409 Conflict.",
                "Cổng API Documentation tương tác tại /docs/index.html hỗ trợ: Swagger UI (Thử nghiệm trực tiếp), Redoc (Đọc chuẩn 3 cột), và Bảng ánh xạ CRUD Matrix."
            ]
        },
        # Slide 7: VietQR
        {
            "type": "content",
            "title": "6. THANH TOÁN VIETQR NAPAS247 & POLLING REALTIME",
            "bullets": [
                "Khách hàng bấm chọn hình thức 'Chuyển khoản VietQR' tại trang checkout.html.",
                "Máy chủ sinh mã đơn hàng duy nhất (VD: TS-20261023-XXXX) và tạo mã QR chuẩn NAPAS247 ngân hàng MBBank.",
                "Trang payment-qr.html hiển thị mã QR, hướng dẫn quét app ngân hàng và đồng hồ đếm ngược 15 phút.",
                "Cơ chế Polling 3s: Client định kỳ gọi /api/v1/orders/{code}/payment-status để kiểm tra trạng thái thanh toán.",
                "Nút giả lập chuyển khoản thành công: Giúp giảng viên và hội đồng kiểm thử tức thì tính năng chuyển đổi trạng thái đơn hàng."
            ]
        },
        # Slide 8: Recommendation
        {
            "type": "content",
            "title": "7. THUẬT TOÁN GỢI Ý THÔNG MINH (RECOMMENDATION ENGINE)",
            "bullets": [
                "Frequently Bought Together (Thường được mua cùng): Phân tích lịch sử đơn hàng để gợi ý mua kèm phụ kiện (Củ sạc GaN, tai nghe, ốp lưng) giảm 8% combo.",
                "Content-Based Similar Products (Sản phẩm tương tự): Gợi ý các thiết bị cùng danh mục và cùng phân khúc giá (±20%).",
                "Personalized For You (Dành riêng cho bạn): Dựa trên lịch sử xem sản phẩm được ghi nhận trong view_logs.",
                "Hiệu quả: Tăng giá trị trung bình đơn hàng (AOV) và nâng cao mức độ hài lòng của khách hàng."
            ]
        },
        # Slide 9: Modern UI
        {
            "type": "content",
            "title": "8. NÂNG CẤP GIAO DIỆN GLASSMORPHISM & LIQUID AURORA MESH",
            "bullets": [
                "Thiết kế hiện đại chuẩn 2026: Lấy cảm hứng từ thương hiệu CellphoneS nhưng nâng tầm với hiệu ứng kính mờ Glassmorphism.",
                "Liquid Aurora Mesh Background: 4 khối màu loang lổ hữu cơ chuyển động mượt mà (Crimson Red, Neon Magenta, Cyan, Purple).",
                "Vòng lặp vô tận (Infinite Loops): Hero Banner tự động chuyển cảnh mượt mà 4.5s kèm thanh tiến trình; Marquee thương hiệu chạy liên tục 60fps bằng phần cứng GPU.",
                "Tương tác vi mô (Micro-interactions): Hiệu ứng quét sáng Specular Sweep khi rê chuột qua thẻ sản phẩm, LED đếm ngược Flash Sale."
            ]
        },
        # Slide 10: Admin Panel
        {
            "type": "content",
            "title": "9. PHÂN HỆ QUẢN TRỊ ADMIN DASHBOARD",
            "bullets": [
                "Dashboard Trực Quan: Thống kê tổng doanh thu, tỷ lệ tăng trưởng, đơn mới và biểu đồ doanh thu 7 ngày.",
                "Quản lý CRUD Sản phẩm: Bảng dữ liệu có tìm kiếm, lọc, form thêm/sửa, tải ảnh có xem trước (preview).",
                "Quản lý Đơn hàng: Xem chi tiết sản phẩm, bấm 'Duyệt TT' duyệt nhanh chuyển khoản QR, đổi trạng thái và in hóa đơn bán hàng A4.",
                "Quản lý Người dùng & Khuyến mãi: Khóa/kích hoạt tài khoản người dùng, tạo mã giảm giá theo % hoặc số tiền cố định."
            ]
        },
        # Slide 11: Testing
        {
            "type": "content",
            "title": "10. KẾT QUẢ KIỂM THỬ POSTMAN & 12 UI TEST CASES",
            "bullets": [
                "Kiểm thử API tự động: Toàn bộ 37/37 API trong Postman Collection vượt qua các bài kiểm tra assert status code, schema dữ liệu và token JWT.",
                "Kiểm thử Giao diện Front-end (12 UI Test Cases):",
                "  - TC-UI-01 đến 03: Form Validation Đăng ký, Đăng nhập, Giỏ hàng tính tiền realtime (PASSED).",
                "  - TC-UI-04 đến 06: Áp dụng mã giảm giá, Quét mã VietQR và Polling thanh toán (PASSED).",
                "  - TC-UI-07 đến 09: Live Search debounce 250ms, Bộ lọc đa tiêu chí, Carousel gợi ý (PASSED).",
                "  - TC-UI-10 đến 12: Admin Auth Guard, CRUD Sản phẩm, Responsive Mobile 375px/Desktop (PASSED)."
            ]
        },
        # Slide 12: Presentation Script
        {
            "type": "content",
            "title": "11. PHÂN ĐOẠN VIDEO THUYẾT TRÌNH (PHẦN CỦA TUẤN KIỆT - 5 PHÚT)",
            "bullets": [
                "Phần 7: Giải thích Kiến trúc Code Front-end (1.5 phút): Trình bày tokens.css, api.js JWT interceptor, cơ chế refresh token và smart mock fallback.",
                "Phần 8: Demo Mua Sắm Khách Hàng (2.0 phút): Đăng ký → Tìm kiếm gợi ý → Chi tiết + Combo phụ kiện → Giỏ hàng & Coupon → Quét mã VietQR & Polling → Lịch sử đơn hàng.",
                "Phần 9: Demo Phân Hệ Quản Trị Admin (1.5 phút): Bảng điều khiển Dashboard → CRUD sản phẩm có ảnh preview → Duyệt thanh toán VietQR & In hóa đơn bán hàng.",
                "Kịch bản bấm giờ chi tiết được lưu trữ tại docs/slides/kich_ban_thuyet_trinh_video.md."
            ]
        },
        # Slide 13: Contributions
        {
            "type": "content",
            "title": "12. BẢNG PHÂN CÔNG & ĐÓNG GÓP THÀNH VIÊN (KẾ HOẠCH 8 NGÀY)",
            "bullets": [
                "Nguyễn Hữu Nam (33.3%): Tech Lead, Kiến trúc MVC Router, JWT Auth, Thanh toán VietQR, Báo cáo Chương 3, 6.",
                "Trần Thành Trung (33.3%): Thiết kế CSDL MySQL 15 bảng, 37 REST APIs Controllers, Báo cáo Chương 2, 4.",
                "Tuấn Kiệt (33.4%): Toàn bộ UI Front-end Khách hàng & Admin, api.js, Bộ test 12 UI Test Cases, Báo cáo Mở đầu & Chương 1, 5, Slide thuyết trình.",
                "Đánh giá chung: Các thành viên hoàn thành 100% nhiệm vụ được giao đúng hạn theo mốc 8 ngày."
            ]
        },
        # Slide 14: Conclusion
        {
            "type": "content",
            "title": "13. KẾT LUẬN & HƯỚNG PHÁT TRIỂN",
            "bullets": [
                "Kết quả đạt được: Đề tài hoàn thành xuất sắc toàn bộ yêu cầu của Topic 1: RESTful API với 37 endpoints hoàn chỉnh, bảo mật JWT chuẩn mực, thanh toán VietQR hiện đại và giao diện Glassmorphism đỉnh cao.",
                "Khả năng chạy thử độc lập: Chạy được ngay trên máy sạch chỉ theo hướng dẫn README.md hoặc script start.bat.",
                "Hướng phát triển tương lai:",
                "  - Nâng cấp xác thực OAuth 2.0 (Google, Facebook Login).",
                "  - Tích hợp gửi email xác nhận đơn hàng tự động qua PHPMailer / SendGrid.",
                "  - Ứng dụng mô hình AI Deep Learning cho hệ thống gợi ý sản phẩm."
            ]
        }
    ]

    for item in slides_data:
        slide = prs.slides.add_slide(blank_layout)

        if item["type"] == "cover":
            # Dark Modern Glassmorphism Slide Cover
            bg_shape = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
            bg_shape.fill.solid()
            bg_shape.fill.fore_color.rgb = RGBColor(15, 17, 23)
            bg_shape.line.color.rgb = RGBColor(15, 17, 23)

            # Red Accent Bar
            bar = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(1.2), Inches(1.5), Inches(0.18), Inches(4.5))
            bar.fill.solid()
            bar.fill.fore_color.rgb = c_crimson
            bar.line.color.rgb = c_crimson

            # Title Box
            tb = slide.shapes.add_textbox(Inches(1.6), Inches(1.4), Inches(10.5), Inches(4.8))
            tf = tb.text_frame
            tf.word_wrap = True

            p0 = tf.paragraphs[0]
            r0 = p0.add_run()
            r0.text = "ĐỒ ÁN MÔN HỌC: CÔNG NGHỆ WEB & KIẾN TRÚC ỨNG DỤNG\n"
            r0.font.size = Pt(14)
            r0.font.bold = True
            r0.font.color.rgb = RGBColor(255, 77, 109)

            p1 = tf.add_paragraph()
            r1 = p1.add_run()
            r1.text = item["title"] + "\n"
            r1.font.size = Pt(28)
            r1.font.bold = True
            r1.font.color.rgb = c_white

            p2 = tf.add_paragraph()
            r2 = p2.add_run()
            r2.text = item["subtitle"] + "\n\n"
            r2.font.size = Pt(14)
            r2.font.color.rgb = RGBColor(203, 213, 225)

            p3 = tf.add_paragraph()
            r3 = p3.add_run()
            r3.text = item["meta"]
            r3.font.size = Pt(13)
            r3.font.color.rgb = RGBColor(148, 163, 184)

        else:
            # White Clean Card Layout
            # Top Header Bar
            header_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.4), Inches(11.8), Inches(1.0))
            tf_h = header_box.text_frame
            tf_h.word_wrap = True
            p_h = tf_h.paragraphs[0]
            r_h = p_h.add_run()
            r_h.text = item["title"]
            r_h.font.size = Pt(22)
            r_h.font.bold = True
            r_h.font.color.rgb = c_crimson

            # Subtle underline
            line = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), Inches(1.3), Inches(11.7), Inches(0.04))
            line.fill.solid()
            line.fill.fore_color.rgb = RGBColor(226, 232, 240)
            line.line.color.rgb = RGBColor(226, 232, 240)

            # Content Card
            card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(0.8), Inches(1.6), Inches(11.7), Inches(5.2))
            card.fill.solid()
            card.fill.fore_color.rgb = c_card_bg
            card.line.color.rgb = RGBColor(203, 213, 225)

            # Bullet text
            tb_body = slide.shapes.add_textbox(Inches(1.2), Inches(1.8), Inches(11.0), Inches(4.7))
            tf_b = tb_body.text_frame
            tf_b.word_wrap = True

            for idx, bullet in enumerate(item["bullets"]):
                p_b = tf_b.paragraphs[0] if idx == 0 else tf_b.add_paragraph()
                p_b.space_before = Pt(8)
                p_b.space_after = Pt(8)
                
                # Check for bold prefixes
                if ":" in bullet:
                    parts = bullet.split(":", 1)
                    r_pre = p_b.add_run()
                    r_pre.text = "•  " + parts[0] + ":"
                    r_pre.font.bold = True
                    r_pre.font.size = Pt(14)
                    r_pre.font.color.rgb = c_dark
                    r_post = p_b.add_run()
                    r_post.text = parts[1]
                    r_post.font.size = Pt(14)
                    r_post.font.color.rgb = RGBColor(51, 65, 85)
                else:
                    r = p_b.add_run()
                    r.text = "•  " + bullet
                    r.font.size = Pt(14)
                    r.font.color.rgb = RGBColor(51, 65, 85)

    out_dir = os.path.dirname(os.path.abspath(__file__))
    out_path = os.path.join(out_dir, "TechStore_Slides.pptx")
    prs.save(out_path)

if __name__ == "__main__":
    create_slides()
