# QUY TRÌNH PHÁT TRIỂN & QUẢN LÝ GIT (GIT WORKFLOW)

> **Dự án:** TechStore - Hệ thống Website Bán Thiết Bị Công Nghệ (RESTful API & Web Application)  
> **Nhiệm vụ T01:** Tạo repo GitHub, thiết lập branch rules, GitHub Projects Kanban và nhãn (labels).

---

## 1. Mô Hình Nhánh (Git Branching Strategy)

Dự án TechStore áp dụng mô hình chuẩn **Git Flow** nhằm đảm bảo code trên nhánh chính luôn ổn định và sẵn sàng bàn giao:

```
[main]          ●─────────────────────────────● (v1.0.0 Release)
                 \                           /
[develop]         ●────────●────────●───────● (Integration)
                   \      /          \     /
[feature/...]       ●────●            ●───● (T04, T11, T15, ...)
```

### Chi tiết các nhánh:

* **`main`**: Nhánh bảo vệ (Protected Branch). Chỉ chứa mã nguồn production đã được kiểm thử 100%. Không push trực tiếp lên `main`.
* **`develop`**: Nhánh tích hợp mã nguồn chính của nhóm. Tất cả tính năng sau khi review xong sẽ merge vào đây.
* **`feature/<task-id>-<short-name>`**: Nhánh phát triển tính năng riêng của từng thành viên (VD: `feature/T10-jwt-auth`, `feature/T13-product-filter`, `feature/T15-postman-tests`).
* **`bugfix/<issue-id>`**: Nhánh sửa lỗi phát sinh trong quá trình kiểm thử.
* **`hotfix/<issue-id>`**: Sửa lỗi khẩn cấp trực tiếp từ `main`.

---

## 2. Quy Định Đặt Tên Commit (Conventional Commits)

Cú pháp commit bắt buộc:
```
<type>(<scope>): <mô tả ngắn bằng tiếng Việt hoặc tiếng Anh>

[Nhiệm vụ: Txx]
```

### Các tiền tố (Types):
* **`feat`**: Tính năng mới (VD: `feat(auth): thêm JwtService và RoleMiddleware [T10]`)
* **`fix`**: Sửa lỗi (VD: `fix(products): sửa lỗi phân trang khi truyền limit vượt quá 100 [T13]`)
* **`docs`**: Tài liệu (VD: `docs(api): cập nhật openapi.yaml và ERD.md [T06]`)
* **`test`**: Kiểm thử (VD: `test(postman): bổ sung kịch bản test tự động 19 requests [T15]`)
* **`refactor`**: Tái cấu trúc mã nguồn không làm thay đổi tính năng
* **`chore`**: Cấu hình môi trường, script phụ trợ (`start.bat`, `.env`)

---

## 3. Quy Trình Pull Request (PR) & Review Code

1. Tạo nhánh mới từ `develop`:
   ```bash
   git checkout develop
   git pull origin develop
   git checkout -b feature/T15-postman-tests
   ```
2. Thực hiện code và commit theo quy chuẩn.
3. Chạy kiểm thử tự động trước khi tạo PR:
   ```bash
   php tests/test_api_t10_t14.php
   npx newman run tests/postman_collection.json -e tests/postman_environment.json
   ```
4. Push nhánh lên GitHub và tạo Pull Request vào `develop`.
5. Ít nhất **1 thành viên khác (Peer Review)** hoặc **Tech Lead (Hữu Nam)** phê duyệt (Approve) trước khi merge.
6. Áp dụng cơ chế **Squash and Merge** để giữ lịch sử commit trên `develop` gọn gàng.

---

## 4. Quản Lý Công Việc với GitHub Projects (Kanban Board)

Bảng công việc được chia thành các cột trạng thái:
1. **Backlog / To Do**: Các nhiệm vụ được phân công trong 8 ngày (T01 - T17...).
2. **In Progress**: Nhiệm vụ đang được thực hiện.
3. **In Review / Testing**: Đã hoàn thành code, đang chạy test và tạo Pull Request.
4. **Done**: Đã vượt qua kiểm thử tự động, đã merge vào `develop`.

### Hệ thống Nhãn (Labels):
* `priority: high` (Đỏ) - Nhiệm vụ cốt lõi, chặn tiến độ nhóm
* `priority: medium` (Vàng) - Nhiệm vụ phụ trợ
* `backend` (Xanh dương) - PHP, Database, JWT, Router
* `frontend` (Xanh lá) - HTML, CSS, JS, UI Kit
* `testing` (Tím) - Unit test, Postman automation
* `docs` (Cam) - Báo cáo, OpenAPI, ERD
