# -*- coding: utf-8 -*-
"""
Script to generate high-resolution professional architecture diagrams in Vietnamese
for TechStore Academic Midterm Report
Separates the 3-tier architecture into 3 dedicated high-detail figures:
- fig_4_1_presentation_tier.png: Kiến trúc Tầng Trình diễn (Presentation Tier / Client SPA)
- fig_4_2_application_tier.png: Kiến trúc Tầng Dịch vụ & Nghiệp vụ (Application / RESTful API Tier)
- fig_4_3_data_tier.png: Kiến trúc Tầng Dữ liệu & Lưu trữ (Data Tier / MySQL 8.0 3NF)
Author: TechStore Team
"""

import os
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.patches as patches

# Set font family that supports Vietnamese characters cleanly
plt.rcParams['font.sans-serif'] = ['Segoe UI', 'Arial', 'Tahoma', 'DejaVu Sans']
plt.rcParams['axes.unicode_minus'] = False

output_dir = os.path.abspath('docs/report')
os.makedirs(output_dir, exist_ok=True)

# -----------------------------------------------------------------------------
# 1. FIG 2.1: REST ARCHITECTURE DIAGRAM (VIETNAMESE)
# -----------------------------------------------------------------------------
def draw_rest_architecture():
    fig, ax = plt.subplots(figsize=(10.5, 5.8), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 60)
    ax.axis('off')
    fig.patch.set_facecolor('#ffffff')

    # Title
    ax.text(50, 56.5, 'Tổng quan Kiến trúc Dịch vụ Web RESTful (RESTful Web Services)', 
            ha='center', va='center', fontsize=13, fontweight='bold', color='#1e3a8a')
    ax.text(50, 53.5, 'Mô hình giao tiếp Khách - Chủ phi trạng thái qua giao thức HTTP tiêu chuẩn', 
            ha='center', va='center', fontsize=9.5, style='italic', color='#64748b')

    # Box 1: Clients (Left)
    client_box = patches.FancyBboxPatch((4, 10), 23, 39, boxstyle="round,pad=0.8", 
                                        ec="#2563eb", fc="#eff6ff", lw=2)
    ax.add_patch(client_box)
    ax.text(15.5, 45, 'TẦNG MÁY KHÁCH\n(CLIENT TIER)', ha='center', va='center', fontsize=10, fontweight='bold', color='#1e40af')
    
    sub_c1 = patches.FancyBboxPatch((6, 33), 19, 8, boxstyle="round,pad=0.4", ec="#93c5fd", fc="#ffffff", lw=1.2)
    ax.add_patch(sub_c1)
    ax.text(15.5, 37, 'Ứng dụng Web SPA\n(HTML5 / CSS / JS)', ha='center', va='center', fontsize=8.5, color='#1e293b')

    sub_c2 = patches.FancyBboxPatch((6, 22), 19, 8, boxstyle="round,pad=0.4", ec="#93c5fd", fc="#ffffff", lw=1.2)
    ax.add_patch(sub_c2)
    ax.text(15.5, 26, 'Ứng dụng Di động\n(Mobile App iOS/Android)', ha='center', va='center', fontsize=8.5, color='#1e293b')

    sub_c3 = patches.FancyBboxPatch((6, 12), 19, 7, boxstyle="round,pad=0.4", ec="#93c5fd", fc="#ffffff", lw=1.2)
    ax.add_patch(sub_c3)
    ax.text(15.5, 15.5, 'Công cụ API / Đối tác\n(Postman / Swagger)', ha='center', va='center', fontsize=8.5, color='#1e293b')

    # Middle: Network & HTTP Communication Channel
    net_box = patches.FancyBboxPatch((32, 10), 33, 39, boxstyle="round,pad=0.8", 
                                     ec="#059669", fc="#ecfdf5", lw=2)
    ax.add_patch(net_box)
    ax.text(48.5, 45.5, 'GIAO THỨC HTTP/1.1 TIÊU CHUẨN', ha='center', va='center', fontsize=10, fontweight='bold', color='#065f46')
    ax.text(48.5, 42, 'Giao tiếp Phi trạng thái (Stateless)', ha='center', va='center', fontsize=8.5, color='#047857')

    methods_box = patches.FancyBboxPatch((34.5, 24), 28, 15, boxstyle="round,pad=0.5", ec="#6ee7b7", fc="#ffffff", lw=1.2)
    ax.add_patch(methods_box)
    ax.text(48.5, 35, 'CÁC ĐỘNG TỪ HTTP (CRUD)', ha='center', va='center', fontsize=9, fontweight='bold', color='#0f172a')
    ax.text(48.5, 29, 'GET (Đọc) | POST (Tạo mới)\nPUT (Thay thế) | PATCH (Sửa)\nDELETE (Xóa) | OPTIONS', 
            ha='center', va='center', fontsize=8, color='#334155')

    hp_box = patches.FancyBboxPatch((34.5, 12), 28, 9, boxstyle="round,pad=0.5", ec="#6ee7b7", fc="#ffffff", lw=1.2)
    ax.add_patch(hp_box)
    ax.text(48.5, 16.5, 'Định dạng dữ liệu: JSON (UTF-8)\nXác thực: Bearer JWT Token\nBộ đệm: ETag / Cache-Control', 
            ha='center', va='center', fontsize=7.8, color='#334155')

    # Box 3: REST Server & Data (Right)
    server_box = patches.FancyBboxPatch((70, 10), 26, 39, boxstyle="round,pad=0.8", 
                                        ec="#7c3aed", fc="#f5f3ff", lw=2)
    ax.add_patch(server_box)
    ax.text(83, 45, 'MÁY CHỦ REST API\n(SERVER TIER)', ha='center', va='center', fontsize=10, fontweight='bold', color='#5b21b6')

    sub_s1 = patches.FancyBboxPatch((72, 33), 22, 7.5, boxstyle="round,pad=0.4", ec="#c4b5fd", fc="#ffffff", lw=1.2)
    ax.add_patch(sub_s1)
    ax.text(83, 36.5, 'Bộ định tuyến tập trung\n(Router & Controller)', ha='center', va='center', fontsize=8.2, color='#1e293b')

    sub_s2 = patches.FancyBboxPatch((72, 23), 22, 7.5, boxstyle="round,pad=0.4", ec="#c4b5fd", fc="#ffffff", lw=1.2)
    ax.add_patch(sub_s2)
    ax.text(83, 26.5, 'Lớp Middleware bảo vệ\n(JWT, Role, CORS, RTR)', ha='center', va='center', fontsize=8.2, color='#1e293b')

    sub_s3 = patches.FancyBboxPatch((72, 12), 22, 8.5, boxstyle="round,pad=0.4", ec="#c4b5fd", fc="#ffffff", lw=1.2)
    ax.add_patch(sub_s3)
    ax.text(83, 16, 'Cơ sở dữ liệu (MySQL 8.0)\n17 Bảng quan hệ 3NF', ha='center', va='center', fontsize=8.2, fontweight='bold', color='#0f172a')

    # Arrows
    ax.annotate('', xy=(32, 34), xytext=(27, 34), 
                arrowprops=dict(facecolor='#2563eb', edgecolor='#2563eb', shrink=0.05, width=1.5, headwidth=6))
    ax.text(29.5, 36, 'Yêu cầu', ha='center', va='bottom', fontsize=7.5, fontweight='bold', color='#1d4ed8')

    ax.annotate('', xy=(27, 22), xytext=(32, 22), 
                arrowprops=dict(facecolor='#059669', edgecolor='#059669', shrink=0.05, width=1.5, headwidth=6))
    ax.text(29.5, 19, 'Phản hồi', ha='center', va='top', fontsize=7.5, fontweight='bold', color='#047857')

    ax.annotate('', xy=(70, 34), xytext=(65, 34), 
                arrowprops=dict(facecolor='#2563eb', edgecolor='#2563eb', shrink=0.05, width=1.5, headwidth=6))
    ax.text(67.5, 36, 'Xử lý', ha='center', va='bottom', fontsize=7.5, fontweight='bold', color='#1d4ed8')

    ax.annotate('', xy=(65, 22), xytext=(70, 22), 
                arrowprops=dict(facecolor='#059669', edgecolor='#059669', shrink=0.05, width=1.5, headwidth=6))
    ax.text(67.5, 19, 'Dữ liệu', ha='center', va='top', fontsize=7.5, fontweight='bold', color='#047857')

    plt.tight_layout()
    out_file = os.path.join(output_dir, 'fig_2_1_rest_architecture.png')
    plt.savefig(out_file, bbox_inches='tight')
    plt.close()
    print("Generated:", out_file)

# -----------------------------------------------------------------------------
# 2. FIG 2.2: RICHARDSON MATURITY MODEL (VIETNAMESE)
# -----------------------------------------------------------------------------
def draw_rmm_levels():
    fig, ax = plt.subplots(figsize=(10.5, 5.2), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 50)
    ax.axis('off')
    fig.patch.set_facecolor('#ffffff')

    ax.text(50, 46.5, 'Mô hình Trưởng thành Richardson (Richardson Maturity Model - RMM)', 
            ha='center', va='center', fontsize=13, fontweight='bold', color='#1e3a8a')
    ax.text(50, 43.5, 'Thang đo 4 cấp độ đánh giá mức độ tuân thủ phong cách kiến trúc RESTful', 
            ha='center', va='center', fontsize=9.5, style='italic', color='#64748b')

    levels = [
        ("CẤP ĐỘ 0: VŨNG LẦY POX", "The Swamp of POX", 
         "• Duy nhất 1 Endpoint URI (/api/endpoint)\n• Duy nhất phương thức POST\n• HTTP chỉ đóng vai trò đường hầm truyền tin (Tunnel)\n• Tiêu biểu: XML-RPC, SOAP Web Services", 
         "#94a3b8", "#f1f5f9", "#475569", 2),
        
        ("CẤP ĐỘ 1: TÀI NGUYÊN ĐỊNH DANH", "Resources Identification", 
         "• Phân chia thành nhiều URI tài nguyên riêng biệt\n• Ví dụ: /products, /users, /orders/102\n• Vẫn sử dụng phương thức POST cho mọi tác vụ\n• Chưa khai thác hệ thống động từ HTTP", 
         "#3b82f6", "#eff6ff", "#1d4ed8", 12),
        
        ("CẤP ĐỘ 2: ĐỘNG TỪ HTTP & TRẠNG THÁI", "HTTP Verbs & Status Codes", 
         "• Áp dụng đúng ngữ nghĩa động từ: GET, POST, PUT, DELETE\n• Phản hồi chuẩn mã trạng thái: 200, 201, 400, 401, 404, 500\n• Đảm bảo tính an toàn (Safety) và khả hoàn (Idempotency)\n• [ĐỒ ÁN TECHSTORE HOÀN TẤT ĐẠT CHUẨN CẤP ĐỘ NÀY]", 
         "#059669", "#ecfdf5", "#047857", 22),
        
        ("CẤP ĐỘ 3: ĐIỀU KHIỂN SIÊU PHƯƠNG TIỆN", "Hypermedia Controls (HATEOAS)", 
         "• Phản hồi chứa các liên kết điều hướng (Hypermedia Links)\n• Khách hàng tự khám phá trạng thái tiếp theo mà không cần hardcode URI\n• Tiêu chuẩn nâng cao trong các hệ sinh thái quy mô toàn cầu", 
         "#7c3aed", "#f5f3ff", "#6d28d9", 32)
    ]

    for title, en_title, desc, border_col, bg_col, text_col, y_bottom in levels:
        box = patches.FancyBboxPatch((5, y_bottom), 90, 8.5, boxstyle="round,pad=0.5", 
                                     ec=border_col, fc=bg_col, lw=1.8)
        ax.add_patch(box)
        
        tag_box = patches.FancyBboxPatch((7, y_bottom + 1.2), 26, 6.1, boxstyle="round,pad=0.3",
                                         ec=border_col, fc="#ffffff", lw=1)
        ax.add_patch(tag_box)
        ax.text(20, y_bottom + 4.8, title, ha='center', va='center', fontsize=8.2, fontweight='bold', color=text_col)
        ax.text(20, y_bottom + 2.5, f"({en_title})", ha='center', va='center', fontsize=7.2, style='italic', color='#64748b')

        ax.text(35, y_bottom + 4.25, desc, ha='left', va='center', fontsize=7.5, color='#1e293b', linespacing=1.2)

        if "TECHSTORE" in desc:
            badge = patches.FancyBboxPatch((81, y_bottom + 2), 12, 4.5, boxstyle="round,pad=0.3",
                                           ec="#047857", fc="#059669", lw=1)
            ax.add_patch(badge)
            ax.text(87, y_bottom + 4.25, 'TECHSTORE\nLEVEL 2', ha='center', va='center', 
                    fontsize=7.2, fontweight='bold', color='#ffffff')

    plt.tight_layout()
    out_file = os.path.join(output_dir, 'fig_2_2_rmm_levels.png')
    plt.savefig(out_file, bbox_inches='tight')
    plt.close()
    print("Generated:", out_file)

# -----------------------------------------------------------------------------
# 3A. FIG 4.1: PRESENTATION TIER ARCHITECTURE (VIETNAMESE)
# -----------------------------------------------------------------------------
def draw_presentation_tier():
    fig, ax = plt.subplots(figsize=(11.5, 6.6), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 70)
    ax.axis('off')
    fig.patch.set_facecolor('#ffffff')

    # Main Title
    ax.text(50, 66.5, 'Kiến trúc Tầng 1: Tầng Trình diễn Giao diện (Presentation Tier - Client SPA)', 
            ha='center', va='center', fontsize=13, fontweight='bold', color='#1e3a8a')
    ax.text(50, 63.5, 'Ứng dụng Trang đơn (Single Page Application) - Phân tách độc lập, giao tiếp REST JSON phi trạng thái', 
            ha='center', va='center', fontsize=9.2, style='italic', color='#64748b')

    # Main Container
    main_box = patches.FancyBboxPatch((2, 9), 96, 52, boxstyle="round,pad=0.8", ec="#2563eb", fc="#eff6ff", lw=2)
    ax.add_patch(main_box)
    ax.text(4, 58.5, 'MÔ ĐUN MÁY KHÁCH TRANG ĐƠN (CLIENT-SIDE SPA) - THUẦN HTML5 / MODERN CSS3 / VANILLA JAVASCRIPT', 
            fontsize=9.2, fontweight='bold', color='#1e40af')

    # 4 Module Columns
    # Col 1: Storefront Client
    col1 = patches.FancyBboxPatch((3.5, 11), 22.5, 45, boxstyle="round,pad=0.5", ec="#93c5fd", fc="#ffffff", lw=1.5)
    ax.add_patch(col1)
    ax.text(14.75, 53.5, 'GIAO DIỆN KHÁCH HÀNG\n(STOREFRONT CLIENT)', ha='center', va='center', fontsize=8.5, fontweight='bold', color='#1e3a8a')

    c1_items = [
        ("Trang chủ & Danh mục", "index.html / products.html\n• Banner Hero & Flash Sale\n• Bộ lọc Faceted (Hãng, Giá, RAM)\n• Phân trang & Instant Search", 42, 9),
        ("Chi tiết Sản phẩm", "product-detail.html\n• Thư viện ảnh đa góc nhìn (85 ảnh)\n• 226 thông số kỹ thuật chi tiết\n• Gợi ý tương đồng & mua kèm\n• Đánh giá 1-5 sao có kiểm chứng", 30.5, 10),
        ("Giỏ hàng & Thanh toán", "cart.html / checkout.html\n• Giỏ hàng thời gian thực\n• Áp mã giảm giá Coupon\n• Modal quét VietQR MB Bank 15p", 20.5, 8.5),
        ("Hồ sơ & Lịch sử", "account.html\n• Thông tin cá nhân & Đổi pass\n• Danh sách đơn hàng & Trạng thái\n• Danh sách yêu thích Wishlist", 12, 7)
    ]
    for title, desc, y_pos, h in c1_items:
        card = patches.FancyBboxPatch((4.5, y_pos), 20.5, h, boxstyle="round,pad=0.3", ec="#bfdbfe", fc="#f8fafc", lw=1)
        ax.add_patch(card)
        ax.text(14.75, y_pos + h - 1.5, title, ha='center', va='center', fontsize=7.8, fontweight='bold', color='#0f172a')
        ax.text(5.5, y_pos + (h-1.5)/2, desc, ha='left', va='center', fontsize=6.7, color='#334155', linespacing=1.2)

    # Col 2: Admin Portal
    col2 = patches.FancyBboxPatch((27.5, 11), 22.5, 45, boxstyle="round,pad=0.5", ec="#a5b4fc", fc="#ffffff", lw=1.5)
    ax.add_patch(col2)
    ax.text(38.75, 53.5, 'CỔNG QUẢN TRỊ ADMIN\n(ADMIN PORTAL)', ha='center', va='center', fontsize=8.5, fontweight='bold', color='#3730a3')

    c2_items = [
        ("Bảng điều khiển KPI", "admin/index.html\n• Tổng doanh thu thời gian thực\n• Thống kê đơn hàng theo trạng thái\n• Top sản phẩm bán chạy nhất\n• Biểu đồ doanh thu trực quan", 42, 9),
        ("Quản lý Hàng hóa & Kho", "admin/products.html\n• CRUD 42 sản phẩm công nghệ\n• Upload và quản lý thư viện ảnh\n• Cấu hình thông số kỹ thuật specs\n• Cảnh báo tồn kho tối thiểu", 30.5, 10),
        ("Xử lý Đơn hàng", "admin/orders.html\n• Duyệt đơn & Tra cứu mã đơn\n• Đổi trạng thái (Paid -> Shipped)\n• Tra cứu lịch sử thanh toán VietQR", 20.5, 8.5),
        ("Người dùng & Khuyến mãi", "admin/users.html, coupons.html\n• Quản lý tài khoản & Quyền (RBAC)\n• Cấp mã Voucher giảm giá\n• Nhật ký kiểm toán audit_logs", 12, 7)
    ]
    for title, desc, y_pos, h in c2_items:
        card = patches.FancyBboxPatch((28.5, y_pos), 20.5, h, boxstyle="round,pad=0.3", ec="#c7d2fe", fc="#f8fafc", lw=1)
        ax.add_patch(card)
        ax.text(38.75, y_pos + h - 1.5, title, ha='center', va='center', fontsize=7.8, fontweight='bold', color='#0f172a')
        ax.text(29.5, y_pos + (h-1.5)/2, desc, ha='left', va='center', fontsize=6.7, color='#334155', linespacing=1.2)

    # Col 3: Design System
    col3 = patches.FancyBboxPatch((51.5, 11), 21.5, 45, boxstyle="round,pad=0.5", ec="#6ee7b7", fc="#ffffff", lw=1.5)
    ax.add_patch(col3)
    ax.text(62.25, 53.5, 'HỆ THỐNG THIẾT KẾ\n(DESIGN SYSTEM & CSS)', ha='center', va='center', fontsize=8.5, fontweight='bold', color='#065f46')

    c3_items = [
        ("Design Tokens Chuẩn mực", "assets/css/tokens.css\n• Bảng màu chuẩn CellphoneS\n  Màu đỏ chủ đạo: #d70018\n  Slate Dark: #0f172a, Gray 50-900\n• Thang đo Spacing, Radius, Shadow\n• Kiểu chữ Segoe UI / SF Pro", 40.5, 10.5),
        ("Hiệu ứng Glassmorphism", "assets/css/base.css\n• Nền mờ Backdrop blur (12px)\n• Hiệu ứng Card nổi khối tinh tế\n• Chuyển động mượt mà (Transitions)\n• Hoàn toàn không dùng framework nặng", 26.5, 12.5),
        ("Thích ứng Responsive", "Tối ưu hiển thị đa thiết bị:\n• Desktop màn rộng (1920x1080)\n• Laptop tiêu chuẩn (1366x768)\n• Máy tính bảng iPad (820px)\n• Điện thoại Mobile (iPhone 390px)", 12, 13)
    ]
    for title, desc, y_pos, h in c3_items:
        card = patches.FancyBboxPatch((52.5, y_pos), 19.5, h, boxstyle="round,pad=0.3", ec="#a7f3d0", fc="#f8fafc", lw=1)
        ax.add_patch(card)
        ax.text(62.25, y_pos + h - 1.5, title, ha='center', va='center', fontsize=7.8, fontweight='bold', color='#0f172a')
        ax.text(53.5, y_pos + (h-1.5)/2, desc, ha='left', va='center', fontsize=6.7, color='#334155', linespacing=1.2)

    # Col 4: API Library & State Management
    col4 = patches.FancyBboxPatch((74.5, 11), 22, 45, boxstyle="round,pad=0.5", ec="#fcd34d", fc="#ffffff", lw=1.5)
    ax.add_patch(col4)
    ax.text(85.5, 53.5, 'THƯ VIỆN API TRUNG TÂM\n(ASSETS/JS/API.JS)', ha='center', va='center', fontsize=8.5, fontweight='bold', color='#92400e')

    c4_items = [
        ("Fetch API Wrapper", "Lớp bọc HTTP chuẩn hóa:\n• Tự động gắn Bearer Token\n• Đặt Content-Type: application/json\n• Chuẩn hóa mã phản hồi HTTP\n  (200, 201, 400, 401, 403, 404, 500)", 40.5, 10.5),
        ("Refresh Token Interceptor", "Cơ chế tự phục hồi phiên ngầm:\n• Bắt mã lỗi HTTP 401 Unauthorized\n• Tự động gọi POST /auth/refresh\n• Lưu token mới & retry request gốc\n• Khách hàng không bị ngắt quãng phiên", 26.5, 12.5),
        ("Quản lý Trạng thái & Cache", "Tối ưu hóa trải nghiệm khách:\n• Instant Search Debounce 300ms\n• Lưu trữ an toàn Access Token\n• Đồng bộ giỏ hàng và LocalStorage\n• Bảo vệ chống tấn công CSRF / XSS", 12, 13)
    ]
    for title, desc, y_pos, h in c4_items:
        card = patches.FancyBboxPatch((75.5, y_pos), 20, h, boxstyle="round,pad=0.3", ec="#fde68a", fc="#f8fafc", lw=1)
        ax.add_patch(card)
        ax.text(85.5, y_pos + h - 1.5, title, ha='center', va='center', fontsize=7.8, fontweight='bold', color='#0f172a')
        ax.text(76.5, y_pos + (h-1.5)/2, desc, ha='left', va='center', fontsize=6.7, color='#334155', linespacing=1.2)

    # Bottom Communication Bar
    bot_box = patches.FancyBboxPatch((2, 1.5), 96, 6, boxstyle="round,pad=0.4", ec="#2563eb", fc="#1d4ed8", lw=1.5)
    ax.add_patch(bot_box)
    ax.text(50, 4.5, 'HỢP ĐỒNG GIAO TIẾP VỚI TẦNG 2: 100% YÊU CẦU / PHẢN HỒI QUA HTTP REST JSON (KHÔNG SỬ DỤNG SERVER-SIDE RENDERING)', 
            ha='center', va='center', fontsize=8.2, fontweight='bold', color='#ffffff')

    # Output arrow
    ax.annotate('', xy=(50, 0.5), xytext=(50, 1.5), 
                arrowprops=dict(facecolor='#1d4ed8', edgecolor='#1d4ed8', shrink=0.05, width=1.8, headwidth=6))

    plt.tight_layout()
    out_file = os.path.join(output_dir, 'fig_4_1_presentation_tier.png')
    plt.savefig(out_file, bbox_inches='tight')
    plt.close()
    print("Generated:", out_file)

# -----------------------------------------------------------------------------
# 3B. FIG 4.2: APPLICATION / REST API TIER ARCHITECTURE (VIETNAMESE)
# -----------------------------------------------------------------------------
def draw_application_tier():
    fig, ax = plt.subplots(figsize=(11.5, 6.8), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 72)
    ax.axis('off')
    fig.patch.set_facecolor('#ffffff')

    # Title
    ax.text(50, 68.5, 'Kiến trúc Tầng 2: Tầng Dịch vụ & Nghiệp vụ (Application / RESTful API Tier)', 
            ha='center', va='center', fontsize=13, fontweight='bold', color='#065f46')
    ax.text(50, 65.5, 'Máy chủ RESTful API PHP 8.2 thuần MVC - Phân tầng bảo mật, 11 Controllers & Tích hợp dịch vụ bên ngoài', 
            ha='center', va='center', fontsize=9.2, style='italic', color='#64748b')

    # Main API Box (Left & Center)
    api_main = patches.FancyBboxPatch((2, 2), 69, 61, boxstyle="round,pad=0.8", ec="#059669", fc="#ecfdf5", lw=2)
    ax.add_patch(api_main)
    ax.text(4, 60.5, 'MÁY CHỦ REST API TECHSTORE (PHP THUẦN TÚY - KIẾN TRÚC MVC KHÔNG DÙNG FRAMEWORK NẶNG)', 
            fontsize=8.8, fontweight='bold', color='#065f46')

    # 1. Router Box (Top-Left of API)
    r_box = patches.FancyBboxPatch((3.5, 44), 22, 14.5, boxstyle="round,pad=0.4", ec="#6ee7b7", fc="#ffffff", lw=1.3)
    ax.add_patch(r_box)
    ax.text(14.5, 56, 'BỘ ĐỊNH TUYẾN (ROUTER)', ha='center', va='center', fontsize=8.2, fontweight='bold', color='#065f46')
    ax.text(4.5, 49.5, '• public/index.php: Single Entry Point\n• api/core/Router.php:\n  Phân giải Method & Regex URL\n• api/routes/api.php:\n  Hợp đồng 37 REST Endpoints', 
            fontsize=6.8, color='#1e293b', linespacing=1.2)

    # 2. Middleware Pipeline (Top-Center of API)
    m_box = patches.FancyBboxPatch((27.5, 44), 42, 14.5, boxstyle="round,pad=0.4", ec="#6ee7b7", fc="#ffffff", lw=1.3)
    ax.add_patch(m_box)
    ax.text(48.5, 56, 'ĐƯỜNG ỐNG MIDDLEWARE BẢO MẬT (SECURITY PIPELINE)', ha='center', va='center', fontsize=8.2, fontweight='bold', color='#065f46')

    m_items = [
        ("CorsMiddleware", "Kiểm soát CORS Origin,\nxử lý Preflight OPTIONS", 28.5),
        ("RateLimitMiddleware", "Giới hạn 100 req/phút,\nchặn tấn công Brute-force", 38.5),
        ("AuthMiddleware", "Thẩm định Bearer JWT,\nkiểm tra chữ ký số HS256", 48.5),
        ("RoleMiddleware", "Kiểm tra đặc quyền RBAC\n(Customer vs Admin)", 58.5)
    ]
    for title, desc, x_pos in m_items:
        m_card = patches.FancyBboxPatch((x_pos, 45.2), 9.5, 8.8, boxstyle="round,pad=0.3", ec="#a7f3d0", fc="#f0fdf4", lw=1)
        ax.add_patch(m_card)
        ax.text(x_pos + 4.75, 52, title, ha='center', va='center', fontsize=6.8, fontweight='bold', color='#047857')
        ax.text(x_pos + 4.75, 48, desc, ha='center', va='center', fontsize=5.8, color='#334155', linespacing=1.1)

    # Arrow from Router to Middleware
    ax.annotate('', xy=(27.5, 51.5), xytext=(25.5, 51.5), 
                arrowprops=dict(facecolor='#059669', edgecolor='#059669', shrink=0.05, width=1.5, headwidth=5))

    # 3. Controllers Layer (Middle of API)
    c_box = patches.FancyBboxPatch((3.5, 23), 66, 19, boxstyle="round,pad=0.5", ec="#3b82f6", fc="#ffffff", lw=1.4)
    ax.add_patch(c_box)
    ax.text(36.5, 39.5, 'LỚP ĐIỀU KHIỂN NGHIỆP VỤ (11 RESTFUL CONTROLLERS)', ha='center', va='center', fontsize=8.5, fontweight='bold', color='#1d4ed8')

    ctrl_groups = [
        ("Định danh & Phiên", "AuthController\n• Đăng nhập, Đăng ký\n• Google OAuth 2.0\n• Đổi mật khẩu, RTR", 4.5, 12),
        ("Danh mục & Sản phẩm", "ProductController\nCategoryController\nBrandController\n• Tìm kiếm, lọc 42 SP", 17.5, 12),
        ("Giỏ hàng & Đơn hàng", "CartController\nOrderController\nCouponController\n• Quản lý giỏ máy chủ\n• Giao dịch ACID", 30.5, 13),
        ("Tương tác & Đánh giá", "ReviewController\nWishlistController\n• Đánh giá 1-5 sao\n• Lưu SP yêu thích", 44.5, 11),
        ("Thanh toán & Gợi ý", "PaymentController\nRecommendationController\n• Sinh mã VietQR động\n• Phục vụ gợi ý Top 6", 56.5, 12)
    ]
    for grp_title, grp_desc, x_pos, w in ctrl_groups:
        card = patches.FancyBboxPatch((x_pos, 24.5), w, 12.8, boxstyle="round,pad=0.3", ec="#bfdbfe", fc="#eff6ff", lw=1)
        ax.add_patch(card)
        ax.text(x_pos + w/2, 35.5, grp_title, ha='center', va='center', fontsize=7.2, fontweight='bold', color='#1e40af')
        ax.text(x_pos + 0.8, 29.5, grp_desc, ha='left', va='center', fontsize=6.2, color='#1e293b', linespacing=1.15)

    # Arrow from Middleware to Controllers
    ax.annotate('', xy=(36.5, 42), xytext=(36.5, 44), 
                arrowprops=dict(facecolor='#059669', edgecolor='#059669', shrink=0.05, width=1.5, headwidth=5))

    # 4. Services & Data Models Layer (Bottom of API)
    s_box = patches.FancyBboxPatch((3.5, 3.5), 66, 17.5, boxstyle="round,pad=0.5", ec="#8b5cf6", fc="#ffffff", lw=1.4)
    ax.add_patch(s_box)
    ax.text(36.5, 18.5, 'LỚP DỊCH VỤ CHUYÊN SÂU & MÔ HÌNH DỮ LIỆU (SERVICES & DATA MODELS)', ha='center', va='center', fontsize=8.5, fontweight='bold', color='#6d28d9')

    # Sub-service 1: Business Services
    srv_card = patches.FancyBboxPatch((4.5, 4.8), 31.5, 11.5, boxstyle="round,pad=0.3", ec="#ddd6fe", fc="#f5f3ff", lw=1)
    ax.add_patch(srv_card)
    ax.text(20.25, 14.5, 'Dịch vụ Nghiệp vụ (Business Services)', ha='center', va='center', fontsize=7.5, fontweight='bold', color='#5b21b6')
    ax.text(5.5, 9.2, '• RecommendService: Pipeline lai Cosine 5D + Apriori giỏ hàng\n• JwtService: Ký/giải mã JWT token HMAC-SHA256\n• QrService: Định dạng chuỗi EMVCo Napas247 VietQR MB Bank\n• Response & ErrorHandler: Chuẩn hóa cấu trúc JSON', 
            fontsize=6.2, color='#334155', linespacing=1.2)

    # Sub-service 2: Models & PDO
    mdl_card = patches.FancyBboxPatch((37.5, 4.8), 31, 11.5, boxstyle="round,pad=0.3", ec="#ddd6fe", fc="#f5f3ff", lw=1)
    ax.add_patch(mdl_card)
    ax.text(53, 14.5, 'Lớp Mô hình Thực thể & PDO Singleton', ha='center', va='center', fontsize=7.5, fontweight='bold', color='#5b21b6')
    ax.text(38.5, 9.2, '• Các Model: Product, Order, User, Cart, Payment, Review, Coupon\n• Giao dịch ACID: beginTransaction(), commit(), rollBack()\n• Database.php: PDO Singleton, EMULATE_PREPARES = false\n• 100% Prepared Statements chống triệt để SQL Injection', 
            fontsize=6.2, color='#334155', linespacing=1.2)

    # 5. External Integrations (Right Side Column)
    ext_main = patches.FancyBboxPatch((73, 2), 25, 61, boxstyle="round,pad=0.8", ec="#d97706", fc="#fffbeb", lw=2)
    ax.add_patch(ext_main)
    ax.text(85.5, 60.5, 'TÍCH HỢP DỊCH VỤ NGOÀI\n(EXTERNAL INTEGRATIONS)', ha='center', va='center', fontsize=8.8, fontweight='bold', color='#92400e')

    # External 1: VietQR MB Bank
    ext1 = patches.FancyBboxPatch((74.5, 32), 22, 24, boxstyle="round,pad=0.4", ec="#fcd34d", fc="#ffffff", lw=1.2)
    ax.add_patch(ext1)
    ax.text(85.5, 53.5, 'CỔNG VIETQR MB BANK', ha='center', va='center', fontsize=8.2, fontweight='bold', color='#b45309')
    ax.text(75.5, 42.5, '• Chuẩn Napas247 Quick Response\n• Sinh mã QR thanh toán động\n  đính kèm mã đơn & số tiền chính xác\n• Thời hạn hiệu lực: Đếm ngược 15 phút\n• Nhận Webhook tự động qua:\n  POST /api/v1/payments/webhook\n• Thẩm định chữ ký số HMAC-SHA256\n• Tự động chuyển đơn thành PAID', 
            fontsize=6.3, color='#451a03', linespacing=1.2)

    # External 2: Google OAuth 2.0
    ext2 = patches.FancyBboxPatch((74.5, 5), 22, 24, boxstyle="round,pad=0.4", ec="#fcd34d", fc="#ffffff", lw=1.2)
    ax.add_patch(ext2)
    ax.text(85.5, 26.5, 'GOOGLE OAUTH 2.0 IDENTITY', ha='center', va='center', fontsize=8.2, fontweight='bold', color='#b45309')
    ax.text(75.5, 15.5, '• Xác thực một chạm (Google One Tap)\n• Khách hàng cấp quyền trên Google Server\n• Client gửi Google ID Token về API\n• Máy chủ TechStore thẩm định chữ ký\n  với Google Certificate công khai\n• Tự động trích xuất email/họ tên,\n  khởi tạo tài khoản & cấp JWT nội bộ', 
            fontsize=6.3, color='#451a03', linespacing=1.2)

    # Connection arrow between API and External
    ax.annotate('', xy=(74.5, 44), xytext=(69.5, 44), 
                arrowprops=dict(facecolor='#d97706', edgecolor='#d97706', shrink=0.05, width=1.5, headwidth=5))
    ax.annotate('', xy=(69.5, 38), xytext=(74.5, 38), 
                arrowprops=dict(facecolor='#d97706', edgecolor='#d97706', shrink=0.05, width=1.5, headwidth=5))

    plt.tight_layout()
    out_file = os.path.join(output_dir, 'fig_4_2_application_tier.png')
    plt.savefig(out_file, bbox_inches='tight')
    plt.close()
    print("Generated:", out_file)

# -----------------------------------------------------------------------------
# 3C. FIG 4.3: DATA TIER ARCHITECTURE (VIETNAMESE)
# -----------------------------------------------------------------------------
def draw_data_tier():
    fig, ax = plt.subplots(figsize=(11.5, 6.8), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 72)
    ax.axis('off')
    fig.patch.set_facecolor('#ffffff')

    # Main Title
    ax.text(50, 68.5, 'Kiến trúc Tầng 3: Tầng Dữ liệu và Lưu trữ (Data Tier - MySQL 8.0 3NF)', 
            ha='center', va='center', fontsize=13, fontweight='bold', color='#5b21b6')
    ax.text(50, 65.5, 'Hệ quản trị CSDL Quan hệ MySQL 8.0 - 17 Bảng dữ liệu chuẩn hóa 3NF phân theo 5 phân nhóm nghiệp vụ', 
            ha='center', va='center', fontsize=9.2, style='italic', color='#64748b')

    # Main Container
    d_main = patches.FancyBboxPatch((2, 16), 96, 47, boxstyle="round,pad=0.8", ec="#7c3aed", fc="#f5f3ff", lw=2)
    ax.add_patch(d_main)
    ax.text(4, 60.5, 'HỆ CƠ SỞ DỮ LIỆU TECHSTORE (17 BẢNG QUAN HỆ - PHÂN THEO 5 PHÂN NHÓM CHỨC NĂNG)', 
            fontsize=8.8, fontweight='bold', color='#5b21b6')

    # 5 Table Groups Columns
    # Grp 1: Core Identity & Session
    g1 = patches.FancyBboxPatch((3.5, 18), 18, 40, boxstyle="round,pad=0.4", ec="#c4b5fd", fc="#ffffff", lw=1.3)
    ax.add_patch(g1)
    ax.text(12.5, 55.5, '1. ĐỊNH DANH & PHIÊN\n(2 BẢNG)', ha='center', va='center', fontsize=7.8, fontweight='bold', color='#4338ca')
    
    card_u = patches.FancyBboxPatch((4.5, 36), 16, 17, boxstyle="round,pad=0.3", ec="#a5b4fc", fc="#f8fafc", lw=1)
    ax.add_patch(card_u)
    ax.text(12.5, 51, 'Bảng: users', ha='center', va='center', fontsize=7.5, fontweight='bold', color='#1e1b4b')
    ax.text(5.5, 43, '• id (PK, Auto Inc)\n• email (Unique, Varchar)\n• password_hash (Bcrypt)\n• role (customer/admin)\n• full_name, phone\n• address, status (1/0)\n• created_at, updated_at', 
            fontsize=6.2, color='#334155', linespacing=1.2)

    card_rt = patches.FancyBboxPatch((4.5, 19.5), 16, 15, boxstyle="round,pad=0.3", ec="#a5b4fc", fc="#f8fafc", lw=1)
    ax.add_patch(card_rt)
    ax.text(12.5, 32.5, 'Bảng: refresh_tokens', ha='center', va='center', fontsize=7.5, fontweight='bold', color='#1e1b4b')
    ax.text(5.5, 25.5, '• id (PK, Auto Inc)\n• user_id (FK -> users)\n• token_hash (SHA-256)\n• expires_at (Datetime)\n• revoked (Tinyint 0/1)\n• created_at (Timestamp)', 
            fontsize=6.2, color='#334155', linespacing=1.2)

    # Grp 2: Catalog & Products
    g2 = patches.FancyBboxPatch((22.5, 18), 19.5, 40, boxstyle="round,pad=0.4", ec="#93c5fd", fc="#ffffff", lw=1.3)
    ax.add_patch(g2)
    ax.text(32.25, 55.5, '2. HÀNG HÓA & DANH MỤC\n(5 BẢNG)', ha='center', va='center', fontsize=7.8, fontweight='bold', color='#1d4ed8')

    g2_cards = [
        ("categories (5 DM)", "id (PK), name, slug, image_url", 47, 5.5),
        ("brands (8 Hãng)", "id (PK), name, slug, logo_url", 40.5, 5.5),
        ("products (42 SP)", "id (PK), name, slug, category_id (FK),\nbrand_id (FK), price, original_price,\nstock, rating, view_count, is_active", 28, 11.5),
        ("product_images (85)", "id (PK), product_id (FK), image_url,\nis_primary, display_order", 20, 7)
    ]
    for title, desc, y_pos, h in g2_cards:
        card = patches.FancyBboxPatch((23.5, y_pos), 17.5, h, boxstyle="round,pad=0.3", ec="#bfdbfe", fc="#f8fafc", lw=1)
        ax.add_patch(card)
        ax.text(32.25, y_pos + h - 1.3, title, ha='center', va='center', fontsize=7, fontweight='bold', color='#0f172a')
        ax.text(24.5, y_pos + (h-1.3)/2, desc, ha='left', va='center', fontsize=5.8, color='#334155', linespacing=1.1)

    # Specs table in g2
    card_sp = patches.FancyBboxPatch((23.5, 19.5), 17.5, 0.1, boxstyle="round,pad=0.1", ec="#ffffff", fc="#ffffff", lw=0)

    # Grp 3: Carts, Orders & Payments
    g3 = patches.FancyBboxPatch((43, 18), 20, 40, boxstyle="round,pad=0.4", ec="#6ee7b7", fc="#ffffff", lw=1.3)
    ax.add_patch(g3)
    ax.text(53, 55.5, '3. GIỎ HÀNG & ĐƠN HÀNG\n(5 BẢNG)', ha='center', va='center', fontsize=7.8, fontweight='bold', color='#047857')

    g3_cards = [
        ("carts (Giỏ hàng)", "id (PK), user_id (FK), product_id (FK),\nquantity, created_at", 47, 5.5),
        ("coupons (Voucher)", "id (PK), code (Unique), discount_type,\ndiscount_value, min_order_value, usage_limit", 40, 6),
        ("orders (Đơn hàng)", "id (PK), order_code (Unique), user_id (FK),\ntotal_amount, discount_amount, final_amount,\nstatus, shipping_address, created_at", 28, 11),
        ("order_items (Món)", "id (PK), order_id (FK), product_id (FK),\nquantity, unit_price, subtotal", 20.5, 6.5),
        ("payments (Thanh toán)", "id (PK), order_id (FK), method, amount,\ntransaction_code, status, qr_url, paid_at", 13.5, 6.2)
    ]
    for title, desc, y_pos, h in g3_cards:
        card = patches.FancyBboxPatch((44, y_pos), 18, h, boxstyle="round,pad=0.3", ec="#a7f3d0", fc="#f8fafc", lw=1)
        ax.add_patch(card)
        ax.text(53, y_pos + h - 1.3, title, ha='center', va='center', fontsize=7, fontweight='bold', color='#065f46')
        ax.text(45, y_pos + (h-1.3)/2, desc, ha='left', va='center', fontsize=5.8, color='#334155', linespacing=1.1)

    # Grp 4: Customer Engagement
    g4 = patches.FancyBboxPatch((64, 18), 17, 40, boxstyle="round,pad=0.4", ec="#fcd34d", fc="#ffffff", lw=1.3)
    ax.add_patch(g4)
    ax.text(72.5, 55.5, '4. TƯƠNG TÁC KHÁCH\n(3 BẢNG)', ha='center', va='center', fontsize=7.8, fontweight='bold', color='#b45309')

    g4_cards = [
        ("reviews (Đánh giá)", "id (PK), user_id (FK),\nproduct_id (FK), rating (1-5),\ncomment, is_verified_purchase,\ncreated_at", 39.5, 13),
        ("wishlists (Yêu thích)", "id (PK), user_id (FK),\nproduct_id (FK), created_at", 28.5, 9.5),
        ("product_views (Lượt xem)", "id (PK), product_id (FK),\nuser_id, ip_address, viewed_at\n(Phục vụ thuật toán gợi ý)", 16, 11)
    ]
    for title, desc, y_pos, h in g4_cards:
        card = patches.FancyBboxPatch((65, y_pos), 15, h, boxstyle="round,pad=0.3", ec="#fde68a", fc="#f8fafc", lw=1)
        ax.add_patch(card)
        ax.text(72.5, y_pos + h - 1.3, title, ha='center', va='center', fontsize=7, fontweight='bold', color='#92400e')
        ax.text(66, y_pos + (h-1.3)/2, desc, ha='left', va='center', fontsize=5.8, color='#334155', linespacing=1.1)

    # Grp 5: System Intelligence & Logs
    g5 = patches.FancyBboxPatch((82, 18), 15, 40, boxstyle="round,pad=0.4", ec="#f43f5e", fc="#ffffff", lw=1.3)
    ax.add_patch(g5)
    ax.text(89.5, 55.5, '5. TRÍ TUỆ & NHẬT KÝ\n(2 BẢNG)', ha='center', va='center', fontsize=7.8, fontweight='bold', color='#be123c')

    card_sim = patches.FancyBboxPatch((83, 36), 13, 17, boxstyle="round,pad=0.3", ec="#fecdd3", fc="#f8fafc", lw=1)
    ax.add_patch(card_sim)
    ax.text(89.5, 51, 'product_similarity', ha='center', va='center', fontsize=7.2, fontweight='bold', color='#9f1239')
    ax.text(84, 43, '• id (PK, Auto Inc)\n• product_id (FK)\n• target_id (FK)\n• similarity_score\n  (Điểm tương đồng)\n• algorithm_type\n  (cosine / apriori)', 
            fontsize=5.8, color='#334155', linespacing=1.15)

    card_log = patches.FancyBboxPatch((83, 19.5), 13, 15, boxstyle="round,pad=0.3", ec="#fecdd3", fc="#f8fafc", lw=1)
    ax.add_patch(card_log)
    ax.text(89.5, 32.5, 'audit_logs', ha='center', va='center', fontsize=7.2, fontweight='bold', color='#9f1239')
    ax.text(84, 25.5, '• id (PK, Auto Inc)\n• user_id (FK)\n• action (Thêm/Sửa/Xóa)\n• entity_type, entity_id\n• payload_diff (JSON)\n• ip_address, created_at', 
            fontsize=5.8, color='#334155', linespacing=1.15)

    # Bottom Database Technical Standards Box
    bot_spec = patches.FancyBboxPatch((2, 1.5), 96, 13, boxstyle="round,pad=0.5", ec="#7c3aed", fc="#ffffff", lw=1.5)
    ax.add_patch(bot_spec)
    ax.text(50, 12.8, 'TIÊU CHUẨN KỸ THUẬT VÀ TỐI ƯU HÓA CƠ SỞ DỮ LIỆU MYSQL 8.0 TECHSTORE', 
            ha='center', va='center', fontsize=8.2, fontweight='bold', color='#5b21b6')

    sub_specs = [
        ("Toàn vẹn Ràng buộc (Referential Integrity)", 
         "100% Khóa ngoại (FK) liên kết bảng.\nON DELETE CASCADE cho bảng phụ thuộc (order_items, specs).\nRESTRICT / SET NULL cho bảng danh mục đảm bảo an toàn.", 3),
        ("Đánh chỉ mục B-Tree (Indexing Optimization)", 
         "Index trên các trường tìm kiếm thường xuyên:\nproducts(slug), products(category_id, brand_id),\norders(order_code), orders(user_id, status), refresh_tokens(user_id).", 35),
        ("Giao dịch ACID & Kết nối an toàn (Security)", 
         "Động cơ InnoDB hỗ trợ giao dịch ACID toàn vẹn.\nBảng mã ký tự utf8mb4_unicode_ci hỗ trợ tiếng Việt trọn vẹn.\nKết nối qua PDO Singleton tắt Emulate Prepares chống SQLi.", 67)
    ]
    for s_title, s_desc, x_pos in sub_specs:
        s_box = patches.FancyBboxPatch((x_pos, 2.5), 30, 8.8, boxstyle="round,pad=0.3", ec="#ddd6fe", fc="#f5f3ff", lw=1)
        ax.add_patch(s_box)
        ax.text(x_pos + 15, 9.5, s_title, ha='center', va='center', fontsize=6.8, fontweight='bold', color='#4c1d95')
        ax.text(x_pos + 1, 5.5, s_desc, ha='left', va='center', fontsize=5.8, color='#334155', linespacing=1.15)

    plt.tight_layout()
    out_file = os.path.join(output_dir, 'fig_4_3_data_tier.png')
    plt.savefig(out_file, bbox_inches='tight')
    plt.close()
    print("Generated:", out_file)

# -----------------------------------------------------------------------------
# 4. FIG 4.5: RECOMMENDATION PIPELINE (VIETNAMESE)
# -----------------------------------------------------------------------------
def draw_recommendation_pipeline():
    fig, ax = plt.subplots(figsize=(10.5, 5.2), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 50)
    ax.axis('off')
    fig.patch.set_facecolor('#ffffff')

    ax.text(50, 47, 'Kiến trúc Luồng Thuật toán Gợi ý Sản phẩm Lai (Hybrid Recommendation)', 
            ha='center', va='center', fontsize=13, fontweight='bold', color='#1e3a8a')

    # Branch 1: Content-Based
    b1 = patches.FancyBboxPatch((4, 26), 29, 16, boxstyle="round,pad=0.6", ec="#3b82f6", fc="#eff6ff", lw=1.8)
    ax.add_patch(b1)
    ax.text(18.5, 39, 'Nhánh 1: Gợi ý theo Cấu hình', ha='center', fontsize=9, fontweight='bold', color='#1e40af')
    ax.text(18.5, 32, 'Cosine Similarity 5 Chiều\n- Danh mục (0.35) | Hãng (0.20)\n- Khoảng giá (0.20) | RAM (0.15)\n- Bộ nhớ ROM (0.10)', 
            ha='center', fontsize=7.5, color='#1e293b')

    # Branch 2: Apriori Co-occurrence
    b2 = patches.FancyBboxPatch((4, 6), 29, 16, boxstyle="round,pad=0.6", ec="#10b981", fc="#ecfdf5", lw=1.8)
    ax.add_patch(b2)
    ax.text(18.5, 19, 'Nhánh 2: Khai phá Giỏ hàng', ha='center', fontsize=9, fontweight='bold', color='#065f46')
    ax.text(18.5, 12, 'Apriori Co-occurrence\n- Quét dữ liệu order_items\n- Độ tin cậy Confidence P(B|A)\n- Hệ số nâng Lift > 1.0 (Mua cùng)', 
            ha='center', fontsize=7.5, color='#1e293b')

    # Center: Hybrid Merger
    merger = patches.FancyBboxPatch((40, 14), 27, 20, boxstyle="round,pad=0.8", ec="#8b5cf6", fc="#f5f3ff", lw=2)
    ax.add_patch(merger)
    ax.text(53.5, 30.5, 'BỘ TRỘN ĐIỂM LAI (HYBRID)', ha='center', fontsize=9, fontweight='bold', color='#5b21b6')
    ax.text(53.5, 23, 'Điểm lai (Hybrid Score) =\n0.4 * Điểm Cấu hình +\n0.4 * Mua cùng nhau +\n0.2 * Điểm Nổi bật', 
            ha='center', fontsize=7.8, fontweight='bold', color='#334155')
    ax.text(53.5, 17, 'Nổi bật = 0.5*Lượt xem + 0.5*Rating', ha='center', fontsize=6.8, color='#6d28d9')

    # Arrows from Branch 1 & 2 to Merger
    ax.annotate('', xy=(40, 30), xytext=(33, 34), 
                arrowprops=dict(facecolor='#3b82f6', edgecolor='#3b82f6', shrink=0.05, width=1.5, headwidth=6))
    ax.annotate('', xy=(40, 20), xytext=(33, 14), 
                arrowprops=dict(facecolor='#10b981', edgecolor='#10b981', shrink=0.05, width=1.5, headwidth=6))

    # Right: Output Carousel & Fallback
    out_box = patches.FancyBboxPatch((74, 24), 23, 16, boxstyle="round,pad=0.6", ec="#059669", fc="#f0fdf4", lw=1.8)
    ax.add_patch(out_box)
    ax.text(85.5, 36.5, 'Đầu ra Cá nhân hóa', ha='center', fontsize=9, fontweight='bold', color='#065f46')
    ax.text(85.5, 29.5, 'Top 6 Sản phẩm phù hợp nhất\nPhục vụ qua API:\nGET /recommendations/me', 
            ha='center', fontsize=7.5, color='#1e293b')

    cs_box = patches.FancyBboxPatch((74, 6), 23, 14, boxstyle="round,pad=0.6", ec="#d97706", fc="#fffbeb", lw=1.8)
    ax.add_patch(cs_box)
    ax.text(85.5, 16.5, 'Dự phòng Khởi động lạnh', ha='center', fontsize=8.5, fontweight='bold', color='#92400e')
    ax.text(85.5, 10.5, 'Cho khách mới (Cold-Start):\nTop lượt xem nhiều nhất &\nĐánh giá trung bình >= 4.5 sao', 
            ha='center', fontsize=7.2, color='#78350f')

    ax.annotate('', xy=(74, 30), xytext=(67, 26), 
                arrowprops=dict(facecolor='#059669', edgecolor='#059669', shrink=0.05, width=1.5, headwidth=6))
    ax.annotate('', xy=(74, 15), xytext=(67, 20), 
                arrowprops=dict(facecolor='#d97706', edgecolor='#d97706', shrink=0.05, width=1.5, headwidth=6))

    plt.tight_layout()
    out_file = os.path.join(output_dir, 'fig_4_5_recommendation_pipeline.png')
    plt.savefig(out_file, bbox_inches='tight')
    # Also save backwards compatible fig_4_3
    plt.savefig(os.path.join(output_dir, 'fig_4_3_recommendation_pipeline.png'), bbox_inches='tight')
    plt.close()
    print("Generated:", out_file)

# -----------------------------------------------------------------------------
# 5. FIG 4.6: VIETQR PAYMENT WORKFLOW (VIETNAMESE)
# -----------------------------------------------------------------------------
def draw_vietqr_flow():
    fig, ax = plt.subplots(figsize=(10.5, 5.2), dpi=300)
    ax.set_xlim(0, 100)
    ax.set_ylim(0, 50)
    ax.axis('off')
    fig.patch.set_facecolor('#ffffff')

    ax.text(50, 47, 'Quy trình Xử lý Thanh toán Tự động Quét mã VietQR MB Bank', 
            ha='center', va='center', fontsize=13, fontweight='bold', color='#1e3a8a')

    steps = [
        ("1. Khởi tạo đơn", "Khách hàng Checkout\nGiao dịch ACID\nTrừ kho tức thì", 5, "#3b82f6", "#eff6ff"),
        ("2. Sinh mã VietQR", "QrService tạo ảnh QR\nMB Bank chính xác tiền\nĐếm ngược 15 phút", 24, "#0ea5e9", "#f0f9ff"),
        ("3. Quét chuyển khoản", "Khách quét bằng App\nNgân hàng tự điền STK,\nsố tiền và mã đơn", 43, "#10b981", "#ecfdf5"),
        ("4. Nhận Webhook", "Ngân hàng báo tiền vào\nPOST /payments/webhook\nXác thực HMAC-SHA256", 62, "#f59e0b", "#fffbeb"),
        ("5. Khóa đơn hàng", "Thẩm định chữ ký khớp\nĐơn chuyển 'PAID'\nKho hàng bảo lưu an toàn", 81, "#8b5cf6", "#f5f3ff")
    ]

    for title, desc, x_pos, b_col, bg_col in steps:
        box = patches.FancyBboxPatch((x_pos, 16), 15, 23, boxstyle="round,pad=0.5", ec=b_col, fc=bg_col, lw=1.8)
        ax.add_patch(box)
        ax.text(x_pos + 7.5, 34, title, ha='center', va='center', fontsize=8.2, fontweight='bold', color='#0f172a')
        ax.text(x_pos + 7.5, 24, desc, ha='center', va='center', fontsize=7, color='#334155')

        if x_pos < 81:
            ax.annotate('', xy=(x_pos + 19, 27.5), xytext=(x_pos + 15, 27.5), 
                        arrowprops=dict(facecolor='#64748b', edgecolor='#64748b', shrink=0.05, width=1.5, headwidth=5))

    cancel_box = patches.FancyBboxPatch((20, 4), 60, 8, boxstyle="round,pad=0.5", ec="#ef4444", fc="#fef2f2", lw=1.5)
    ax.add_patch(cancel_box)
    ax.text(50, 8, 'Hàng rào Tự hủy Bảo vệ Kho: Nếu sau 15 phút không thanh toán -> Tự hủy đơn & Hoàn trả 100% tồn kho', 
            ha='center', va='center', fontsize=7.6, fontweight='bold', color='#b91c1c')

    plt.tight_layout()
    out_file = os.path.join(output_dir, 'fig_4_6_vietqr_flow.png')
    plt.savefig(out_file, bbox_inches='tight')
    # Also save backwards compatible fig_4_4
    plt.savefig(os.path.join(output_dir, 'fig_4_4_vietqr_flow.png'), bbox_inches='tight')
    plt.close()
    print("Generated:", out_file)

if __name__ == '__main__':
    print("Re-generating all diagrams with 100% Vietnamese content and 3 dedicated tier figures...")
    draw_rest_architecture()
    draw_rmm_levels()
    draw_presentation_tier()
    draw_application_tier()
    draw_data_tier()
    draw_recommendation_pipeline()
    draw_vietqr_flow()
    print("All diagrams generated successfully!")
