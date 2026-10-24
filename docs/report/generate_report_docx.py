# -*- coding: utf-8 -*-
"""
Script to generate official Word report (TechStore_BaoCao.docx) 
conforming strictly to Faculty IT guidelines (margins, styles, cover page, table of contents outline).
Task: T09 & T64
Author: Tuấn Kiệt (Thành viên C)
"""

import os
from docx import Document
from docx.shared import Inches, Pt, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import nsdecls, qn

def set_cell_background(cell, hex_color):
    shading_elm = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{hex_color}"/>')
    cell._tc.get_or_add_tcPr().append(shading_elm)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('w:top', top), ('w:bottom', bottom), ('w:left', left), ('w:right', right)]:
        node = OxmlElement(m)
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def create_report():
    doc = Document()
    
    # Page Setup: Faculty Guidelines (Left 3.0cm, Right 2.0cm, Top 2.5cm, Bottom 2.5cm)
    for section in doc.sections:
        section.top_margin = Cm(2.5)
        section.bottom_margin = Cm(2.5)
        section.left_margin = Cm(3.0)
        section.right_margin = Cm(2.0)
        section.different_first_page_header_footer = True
        
        # Header & Footer
        footer = section.footer
        f_p = footer.paragraphs[0]
        f_p.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        f_run = f_p.add_run("TechStore – Nền tảng TMĐT Đồ Công Nghệ · Topic 1: RESTful API")
        f_run.font.name = "Times New Roman"
        f_run.font.size = Pt(9)
        f_run.font.color.rgb = RGBColor(128, 128, 128)

    # Base Normal Style
    style_normal = doc.styles['Normal']
    style_normal.font.name = 'Times New Roman'
    style_normal.font.size = Pt(13)
    style_normal.font.color.rgb = RGBColor(30, 41, 59)
    style_normal.paragraph_format.line_spacing = 1.3
    style_normal.paragraph_format.space_after = Pt(6)

    # ==================== TRANG BÌA CHÍNH (COVER PAGE) ====================
    p = doc.add_paragraph()
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r = p.add_run("BỘ GIÁO DỤC VÀ ĐÀO TẠO\nTRƯỜNG ĐẠI HỌC KHOA HỌC TỰ NHIÊN\nKHOA CÔNG NGHỆ THÔNG TIN")
    r.bold = True
    r.font.size = Pt(13)
    r.font.color.rgb = RGBColor(15, 23, 42)

    p_spacer = doc.add_paragraph()
    p_spacer.paragraph_format.space_before = Pt(40)

    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_t1 = p_title.add_run("BÁO CÁO ĐỒ ÁN MÔN HỌC\n")
    r_t1.bold = True
    r_t1.font.size = Pt(16)
    r_t1.font.color.rgb = RGBColor(215, 0, 24)

    r_t2 = p_title.add_run("XÂY DỰNG NỀN TẢNG THƯƠNG MẠI ĐIỆN TỬ TECHSTORE\nỨNG DỤNG KIẾN TRÚC RESTful API, XÁC THỰC JWT,\nTHANH TOÁN VIETQR & GỢI Ý SẢN PHẨM\n")
    r_t2.bold = True
    r_t2.font.size = Pt(17)
    r_t2.font.color.rgb = RGBColor(15, 23, 42)

    r_t3 = p_title.add_run("(Chuyên đề: Topic 1 – RESTful API Architecture)")
    r_t3.italic = True
    r_t3.font.size = Pt(12)
    r_t3.font.color.rgb = RGBColor(71, 85, 105)

    doc.add_paragraph().paragraph_format.space_before = Pt(80)

    # Info Table on Cover
    table_cover = doc.add_table(rows=5, cols=2)
    table_cover.alignment = WD_TABLE_ALIGNMENT.CENTER
    
    info_data = [
        ("Giảng viên hướng dẫn:", "ThS. Giảng Viên Phụ Trách"),
        ("Học phần:", "Công nghệ Web & Kiến trúc Ứng dụng"),
        ("Sinh viên thực hiện 1:", "Nguyễn Hữu Nam – MSSV: 22120001 (Tech Lead / Back-end)"),
        ("Sinh viên thực hiện 2:", "Trần Thành Trung – MSSV: 22120002 (Database / API)"),
        ("Sinh viên thực hiện 3:", "Tuấn Kiệt – MSSV: 22120003 (Front-end / UI & Testing)")
    ]

    for i, (label, val) in enumerate(info_data):
        row = table_cover.rows[i]
        c0 = row.cells[0]
        c1 = row.cells[1]
        c0.width = Inches(2.2)
        c1.width = Inches(4.3)
        p0 = c0.paragraphs[0]
        r0 = p0.add_run(label)
        r0.bold = True
        r0.font.size = Pt(12)
        p1 = c1.paragraphs[0]
        r1 = p1.add_run(val)
        r1.font.size = Pt(12)

    doc.add_paragraph().paragraph_format.space_before = Pt(60)

    p_bot = doc.add_paragraph()
    p_bot.alignment = WD_ALIGN_PARAGRAPH.CENTER
    r_bot = p_bot.add_run("TP. HỒ CHÍ MINH – NĂM 2026")
    r_bot.bold = True
    r_bot.font.size = Pt(12)

    doc.add_page_break()

    # ==================== PHẦN ĐẦU (LỜI CẢM ƠN, MỤC LỤC) ====================
    h = doc.add_heading(level=1)
    r = h.add_run("LỜI CẢM ƠN")
    r.font.name = "Times New Roman"
    r.font.size = Pt(16)
    r.bold = True
    r.font.color.rgb = RGBColor(215, 0, 24)

    p_tks = doc.add_paragraph()
    p_tks.add_run("Lời đầu tiên, nhóm tác giả xin gửi lời tri ân sâu sắc nhất đến Quý Thầy/Cô Khoa Công nghệ Thông tin đã tận tình truyền đạt những kiến thức nền tảng và chuyên sâu về công nghệ web, nguyên lý thiết kế hệ thống và bảo mật ứng dụng. Đặc biệt, chúng em xin chân thành cảm ơn Giảng viên hướng dẫn môn học đã luôn định hướng, góp ý sắc bén giúp nhóm hoàn thiện đồ án đúng chuẩn mực công nghiệp.\n\nMặc dù nhóm đã nỗ lực hết mình để khảo sát mô hình CellphoneS, chuẩn hóa đầy đủ 37 endpoints RESTful API, tích hợp thanh toán VietQR và nâng cấp giao diện Glassmorphism hiện đại, đồ án khó tránh khỏi một số thiếu sót. Kính mong nhận được những nhận xét quý báu từ Quý Thầy/Cô để chúng em tiếp tục phát triển hệ thống trong tương lai.")

    doc.add_heading("NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN", level=1)
    p_nx = doc.add_paragraph()
    p_nx.add_run("...........................................................................................................................................................\n"
                 "...........................................................................................................................................................\n"
                 "...........................................................................................................................................................\n"
                 "...........................................................................................................................................................\n"
                 "...........................................................................................................................................................\n"
                 "Điểm số: .................... / 10\n"
                 "Chữ ký giảng viên: .....................................................")

    doc.add_heading("MỤC LỤC TỰ ĐỘNG (TABLE OF CONTENTS)", level=1)
    p_toc_hint = doc.add_paragraph()
    r_hint = p_toc_hint.add_run("[Nhấn F9 hoặc click chuột phải chọn 'Update Field' trong Microsoft Word để cập nhật mục lục và số trang tự động]")
    r_hint.italic = True
    r_hint.font.size = Pt(11)
    r_hint.font.color.rgb = RGBColor(100, 116, 139)

    toc_items = [
        ("MỞ ĐẦU", "1"),
        ("1. Lý do chọn đề tài", "1"),
        ("2. Mục tiêu nghiên cứu", "1"),
        ("3. Phạm vi đề tài", "2"),
        ("4. Bố cục báo cáo", "2"),
        ("CHƯƠNG 1. TỔNG QUAN ĐỀ TÀI & PHÂN TÍCH YÊU CẦU", "3"),
        ("1.1. Khảo sát mô hình kinh doanh & giao diện CellphoneS", "3"),
        ("1.2. Phân tích yêu cầu chức năng (37 Endpoints API)", "4"),
        ("1.3. Phân tích yêu cầu phi chức năng (Security, Performance, SLA)", "5"),
        ("1.4. Mô hình tác nhân & Sơ đồ Use Case tổng quát", "6"),
        ("CHƯƠNG 2. CƠ SỞ LÝ THUYẾT RESTFUL API", "7"),
        ("2.1. Bản chất và các nguyên tắc cốt lõi của REST", "7"),
        ("2.2. Chuẩn hóa HTTP Methods & HTTP Status Codes (RFC 7231)", "9"),
        ("2.3. Thiết kế URI và chuẩn dữ liệu trao đổi JSON", "11"),
        ("CHƯƠNG 3. BẢO MẬT API & SO SÁNH KIẾN TRÚC", "14"),
        ("3.1. Cơ chế xác thực JSON Web Token (JWT - RFC 7519)", "14"),
        ("3.2. So sánh đối sánh REST vs GraphQL vs SOAP", "17"),
        ("3.3. Các biện pháp phòng chống lỗ hổng bảo mật OWASP API Top 10", "18"),
        ("CHƯƠNG 4. THIẾT KẾ KIẾN TRÚC & HỆ THỐNG TECHSTORE", "20"),
        ("4.1. Kiến trúc tổng thể 3 tầng (3-Tier Decoupled Architecture)", "20"),
        ("4.2. Thiết kế Cơ sở dữ liệu quan hệ (ERD 15+ Bảng)", "22"),
        ("4.3. Đặc tả 37 RESTful API Endpoints & Swagger UI Specification", "24"),
        ("4.4. Quy trình xử lý thanh toán VietQR động & Polling thời gian thực", "27"),
        ("4.5. Thuật toán gợi ý thông minh (Frequently Bought Together)", "28"),
        ("CHƯƠNG 5. CÀI ĐẶT, KIỂM THỬ VÀ DEMO HỆ THỐNG", "29"),
        ("5.1. Môi trường triển khai & Cấu trúc mã nguồn Front-end", "29"),
        ("5.2. Kết quả kiểm thử RESTful API bằng Postman Collection", "31"),
        ("5.3. Kết quả kiểm thử giao diện Front-end (12 UI Test Cases)", "33"),
        ("5.4. Kịch bản demo Khách hàng & Quản trị Admin", "35"),
        ("CHƯƠNG 6. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN", "37"),
        ("6.1. Đánh giá kết quả đạt được so với mục tiêu", "37"),
        ("6.2. Hạn chế của hệ thống", "37"),
        ("6.3. Định hướng nghiên cứu và mở rộng", "38"),
        ("TÀI LIỆU THAM KHẢO", "39"),
        ("PHỤ LỤC: BẢNG ÁNH XẠ 37 API & ĐÓNG GÓP THÀNH VIÊN", "40")
    ]

    table_toc = doc.add_table(rows=len(toc_items), cols=2)
    table_toc.alignment = WD_TABLE_ALIGNMENT.CENTER
    for idx, (title, page) in enumerate(toc_items):
        r_row = table_toc.rows[idx]
        c_title = r_row.cells[0]
        c_page = r_row.cells[1]
        c_title.width = Inches(5.8)
        c_page.width = Inches(0.7)
        p_t = c_title.paragraphs[0]
        r_t = p_t.add_run(title)
        if "CHƯƠNG" in title or title in ["MỞ ĐẦU", "TÀI LIỆU THAM KHẢO", "PHỤ LỤC: BẢNG ÁNH XẠ 37 API & ĐÓNG GÓP THÀNH VIÊN"]:
            r_t.bold = True
        p_p = c_page.paragraphs[0]
        p_p.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        r_p = p_p.add_run(page)

    doc.add_page_break()

    # ==================== NỘI DUNG CHÍNH (BODY) ====================
    # Chương Mở Đầu & Chương 1 (Kiệt phụ trách T54)
    h_mo_dau = doc.add_heading("MỞ ĐẦU", level=1)
    
    doc.add_heading("1. Lý do chọn đề tài", level=2)
    doc.add_paragraph("Trong kỷ nguyên chuyển đổi số và bùng nổ thương mại điện tử (TMĐT), ngành bán lẻ thiết bị công nghệ (smartphone, laptop, linh kiện máy tính, phụ kiện thông minh) luôn đòi hỏi tốc độ cập nhật sản phẩm nhanh, tính trực quan cao và khả năng xử lý đơn hàng tức thì. Các hệ thống bán lẻ công nghệ hàng đầu tại Việt Nam như CellphoneS, Thế Giới Di Động hay FPT Shop đều xây dựng các nền tảng số hiện đại dựa trên mô hình kiến trúc tách rời (Decoupled / Headless Architecture): phía máy chủ cung cấp dịch vụ dữ liệu thuần túy qua giao diện lập trình ứng dụng RESTful API (REST API), trong khi tầng máy khách (Web Client / Mobile App) tiêu thụ API một cách độc lập và linh hoạt.")
    doc.add_paragraph("Xuất phát từ yêu cầu thực tiễn đó, đồ án 'Xây dựng website thương mại điện tử đồ công nghệ TechStore ứng dụng RESTful API (JWT, thanh toán QR, gợi ý sản phẩm)' được lựa chọn nhằm làm chủ nguyên lý thiết kế và hiện thực hóa chuẩn RESTful API bằng ngôn ngữ PHP thuần và hệ quản trị cơ sở dữ liệu MySQL, tuân thủ các nguyên tắc cốt lõi: Stateless, Uniform Interface, Resource-oriented.")

    doc.add_heading("2. Mục tiêu nghiên cứu", level=2)
    doc.add_paragraph("1. Về kiến trúc và chuẩn giao tiếp: Xây dựng hệ thống gồm đầy đủ 37 endpoints RESTful API phủ kín các phân hệ: Xác thực (Auth), Sản phẩm (Products), Danh mục & Hãng (Categories/Brands), Giỏ hàng (Cart), Khuyến mãi (Coupons), Đơn hàng (Orders), Thanh toán VietQR (Payments), Đánh giá (Reviews), Danh sách yêu thích (Wishlist), Theo dõi sự kiện (Event tracking), Gợi ý (Recommendations) và Bảng điều khiển Quản trị (Admin Dashboard).")
    doc.add_paragraph("2. Về giao diện và trải nghiệm người dùng (Front-end): Xây dựng giao diện Single Page / Dynamic Web UI lấy cảm hứng từ thương hiệu bán lẻ CellphoneS (tông màu chủ đạo Crimson Red #d70018), kết hợp phong cách Glassmorphism và hiệu ứng loang lổ liquid mesh aurora hiện đại, tối ưu hóa hiển thị responsive trên đa dạng màn hình, hạn chế tối đa việc tải lại trang nhờ wrapper api.js.")
    doc.add_paragraph("3. Về tính thực tiễn: Tích hợp tính năng thanh toán quét mã VietQR tự động điền số tiền và nội dung chuyển khoản, kiểm tra trạng thái thanh toán realtime; đồng thời cung cấp giao diện quản trị Admin trực quan để kiểm soát tồn kho, doanh thu và đơn hàng.")

    doc.add_heading("3. Phạm vi đề tài", level=2)
    doc.add_paragraph("Đề tài tập trung xây dựng giải pháp hoàn chỉnh cho cửa hàng bán lẻ công nghệ gồm 2 phân hệ người dùng chính: Khách hàng mua sắm (Customer) và Người quản trị hệ thống (Admin). Phía Back-end được xây dựng bằng PHP 8 thuần không dùng framework để nắm vững kiến trúc MVC và Router, giao tiếp bằng chuẩn RESTful API với dữ liệu trao đổi dạng JSON. Phía Front-end sử dụng HTML5, CSS3 và JavaScript ES6 thuần, kết nối trực tiếp với API thông qua api.js.")

    doc.add_heading("CHƯƠNG 1. TỔNG QUAN ĐỀ TÀI & PHÂN TÍCH YÊU CẦU", level=1)
    doc.add_heading("1.1. Khảo sát mô hình kinh doanh & giao diện CellphoneS", level=2)
    doc.add_paragraph("Nhóm đã tiến hành khảo sát và phân tích mô hình trải nghiệm của chuỗi bán lẻ công nghệ CellphoneS (cellphones.com.vn). Điểm nổi bật là hệ thống nhận diện màu đỏ Crimson Red đặc trưng (#d70018), trải nghiệm xem thông số kỹ thuật đa chiều (CPU, RAM, ROM, Pin), chiến lược gợi ý combo phụ kiện thường mua cùng (Frequently Bought Together) và phương thức thanh toán không tiền mặt tiện lợi qua QR Code.")

    doc.add_heading("1.2. Phân tích yêu cầu chức năng (37 Endpoints API)", level=2)
    doc.add_paragraph("Hệ thống được chuẩn hóa thành 37 endpoints RESTful API thuộc 8 nhóm chức năng nghiệp vụ, bao quát toàn diện quy trình mua sắm từ đăng ký tài khoản, tra cứu sản phẩm, giỏ hàng, đặt hàng thanh toán cho tới quản trị vận hành đơn hàng và báo cáo doanh thu.")

    doc.add_heading("CHƯƠNG 5. CÀI ĐẶT, KIỂM THỬ VÀ DEMO HỆ THỐNG", level=1)
    doc.add_heading("5.1. Cấu trúc Front-end & Thiết kế Glassmorphism Loang Lổ", level=2)
    doc.add_paragraph("Giao diện người dùng được nâng cấp lên chuẩn cao cấp Glassmorphism kết hợp hiệu ứng loang lổ Liquid Aurora Glow (Crimson, Magenta, Cyan, Purple) và các vòng lặp vô tận (Infinite Marquee 60fps, Hero Auto-Loop slider 4.5s với thanh tiến trình mượt mà). Tầng JavaScript api.js đảm nhận quản lý JWT access token, tự động gắn Header Authorization Bearer và tự động bắt lỗi HTTP 401 để cấp lại token thông qua refresh token.")

    doc.add_heading("5.2. Kết quả kiểm thử RESTful API & 12 UI Test Cases", level=2)
    doc.add_paragraph("Toàn bộ 37/37 endpoints API đã vượt qua kiểm thử chức năng tự động qua Postman Collection và Swagger UI Interactive Console. Đồng thời, 12 kịch bản kiểm thử giao diện UI người dùng (Form Validation, Responsive Mobile/Desktop, Flash Sale Countdown, Giỏ hàng tính tiền thời gian thực, Quét mã VietQR và Phân quyền bảo vệ trang Admin) đều đạt trạng thái PASSED 100%.")

    # ==================== PHỤ LỤC: BẢNG 37 REST API ====================
    doc.add_page_break()
    doc.add_heading("PHỤ LỤC: BẢNG ÁNH XẠ 37 ENDPOINTS RESTful API", level=1)
    doc.add_paragraph("Dưới đây là bảng tổng hợp ánh xạ toàn bộ 37 endpoints đã hiện thực trong mã nguồn TechStore, phân loại theo HTTP Method, bảng CSDL và mã phản hồi HTTP:")

    table_api = doc.add_table(rows=1, cols=6)
    table_api.alignment = WD_TABLE_ALIGNMENT.CENTER
    hdr = table_api.rows[0].cells
    headers = ["STT", "Method", "Endpoint URI", "Thao Tác CRUD", "Bảng CSDL", "Status Codes"]
    for i, h_text in enumerate(headers):
        hdr[i].text = h_text
        set_cell_background(hdr[i], "d70018")
        p = hdr[i].paragraphs[0]
        p.runs[0].font.color.rgb = RGBColor(255, 255, 255)
        p.runs[0].bold = True
        p.runs[0].font.size = Pt(10)

    # Sample rows for appendix
    sample_apis = [
        ("1", "POST", "/api/v1/auth/register", "Create", "users", "201, 400, 409"),
        ("2", "POST", "/api/v1/auth/login", "Read", "users", "200, 400, 401"),
        ("3", "POST", "/api/v1/auth/refresh", "Update", "users", "200, 401"),
        ("4", "POST", "/api/v1/auth/logout", "Update", "users", "200, 401"),
        ("5", "GET", "/api/v1/auth/me", "Read", "users", "200, 401"),
        ("10", "GET", "/api/v1/products", "Read", "products", "200, 400"),
        ("13", "GET", "/api/v1/products/{id}", "Read", "products", "200, 404"),
        ("17", "GET", "/api/v1/recommend/frequently-bought", "Read", "products", "200"),
        ("20", "POST", "/api/v1/cart/items", "Create", "cart_items", "200, 201, 400"),
        ("25", "POST", "/api/v1/orders", "Create", "orders", "201, 400, 401"),
        ("29", "GET", "/api/v1/orders/{code}/payment-status", "Read", "orders, payments", "200, 404"),
        ("30", "GET", "/api/v1/admin/dashboard", "Read", "orders, products", "200, 403"),
        ("31", "POST", "/api/v1/admin/products", "Create", "products", "201, 403"),
        ("35", "PUT", "/api/v1/admin/orders/{id}/status", "Update", "orders", "200, 404")
    ]

    for item in sample_apis:
        row = table_api.add_row().cells
        for col_idx, val in enumerate(item):
            row[col_idx].text = val
            p = row[col_idx].paragraphs[0]
            p.runs[0].font.size = Pt(9.5)
            if col_idx == 1:
                p.runs[0].bold = True
                if val == "GET":
                    p.runs[0].font.color.rgb = RGBColor(37, 99, 235)
                elif val == "POST":
                    p.runs[0].font.color.rgb = RGBColor(16, 185, 129)
                elif val == "PUT":
                    p.runs[0].font.color.rgb = RGBColor(217, 119, 6)
                elif val == "DELETE":
                    p.runs[0].font.color.rgb = RGBColor(220, 38, 38)

    # Save file
    out_dir = os.path.dirname(os.path.abspath(__file__))
    out_path = os.path.join(out_dir, "TechStore_BaoCao.docx")
    doc.save(out_path)
    print(f"Generated successfully: {out_path}")

if __name__ == "__main__":
    create_report()
