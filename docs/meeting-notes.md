# BIÊN BẢN HỌP KHỞI ĐỘNG DỰ ÁN (KICKOFF MEETING NOTES)

> **Dự án:** TechStore - Hệ sinh thái Website Thương mại Điện tử Bán Thiết bị Công nghệ  
> **Nhiệm vụ T02:** Họp kickoff, chốt phạm vi, quy ước code & API, lịch stand-up, thông tin trang bìa báo cáo.

---

## 1. Thông Tin Cuộc Họp

* **Thời gian:** 08:30 - 10:30, Ngày 21/10/2026
* **Địa điểm:** Phòng Lab Công nghệ Thông tin / Google Meet
* **Chủ trì:** Hữu Nam (Tech Lead & Backend)
* **Thành viên tham dự đầy đủ:**
  1. **Hữu Nam** - Nhóm trưởng, Thiết kế Kiến trúc Backend, Xác thực JWT & Phân quyền
  2. **Thành Trung** - Cơ sở dữ liệu MySQL, DML Seeding, RESTful APIs Sản phẩm & Postman Test
  3. **Tuấn Kiệt** - Thiết kế UI/UX Kit, Front-end Client & Phụ trách Báo cáo Đồ án

---

## 2. Thông Tin Trang Bìa Báo Cáo (Đã xác nhận với Giảng viên)

* **Trường:** Đại học Khoa học Tự nhiên - ĐHQG TP.HCM / Khoa Công nghệ Thông tin
* **Tên Đề Tài:** XÂY DỰNG HỆ THỐNG WEBSITE THƯƠNG MẠI ĐIỆN TỬ BÁN THIẾT BỊ CÔNG NGHỆ TECHSTORE
* **Chủ đề:** Topic 1: RESTful API · PHP Thuần, MySQL, HTML/CSS/JavaScript
* **Giảng viên hướng dẫn:** ThS. Giảng viên phụ trách môn học
* **Danh sách sinh viên thực hiện:**
  1. Hữu Nam - MSSV: 22120001
  2. Thành Trung - MSSV: 22120002
  3. Tuấn Kiệt - MSSV: 22120003
* **Năm học:** 2026 - 2027

---

## 3. Chốt Phạm Vi Đồ Án (Scope of Work)

Nhóm thống nhất triển khai theo kế hoạch 8 ngày với 3 trụ cột kỹ thuật:

1. **Kiến trúc Backend & RESTful API:**
   * Sử dụng **PHP 8 thuần** không framework, chuẩn Front Controller (`public/index.php`).
   * Xác thực phân quyền không trạng thái bằng **JWT (HS256)** với cặp Access Token (15 phút) và Refresh Token (7 ngày) lưu trong Database.
   * Định dạng phản hồi API JSON đồng bộ 100%:
     * Thành công: `{ "success": true, "data": ..., "message": "...", "meta": ... }`
     * Thất bại: `{ "success": false, "error": { "code": "...", "message": "...", "details": [...] } }`
2. **Cơ sở dữ liệu & Tối ưu hóa:**
   * Hệ thống CSDL quan hệ chuẩn hóa 3NF gồm 17 bảng.
   * Dữ liệu mẫu (Seed): ≥ 42 sản phẩm, 8 thương hiệu, 5 ngành hàng, 30 đơn hàng thực tế.
   * Hỗ trợ cơ chế Fallback sang file `database/data.json` khi máy chấm không có MySQL cục bộ.
3. **Giao diện Người dùng (Front-end):**
   * Sử dụng HTML5, Vanilla CSS (Design Tokens hiện đại lấy cảm hứng từ CellphoneS), Vanilla JavaScript (`api.js`).
   * Responsive hoàn toàn trên Mobile, Tablet, Desktop.

---

## 4. Quy Ước Lập Trình & Quy Trình Làm Việc

* **Lịch Daily Stand-up:** 21:00 mỗi tối qua Discord/Google Meet (15 phút cập nhật tiến độ, vướng mắc).
* **Môi trường phát triển:** XAMPP / PHP 8.x / MySQL 8.x / Postman v11.
* **Tiêu chuẩn hoàn thành (Definition of Done):**
  * Mã nguồn không có lỗi cú pháp (`php -l` sạch 100%).
  * Có script kiểm thử tự động xác nhận tính đúng đắn.
  * Được review và merge vào nhánh `develop`.
