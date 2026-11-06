# TECHSTORE – KỊCH BẢN THUYẾT TRÌNH VIDEO CHI TIẾT (VIDEO PRESENTATION SCRIPT)
> **Môn học:** Công nghệ Web & Kiến trúc Ứng dụng  
> **Chủ đề:** Topic 1: RESTful API (REST Principles, Richardson Maturity Model, HTTP Methods, JWT/OAuth, So sánh GraphQL/SOAP, OWASP API Security)  
> **Dự án:** TechStore – Nền tảng TMĐT Đồ Công Nghệ (Tham khảo mô hình CellphoneS)  
> **Yêu cầu:** Thời lượng tối đa 20 phút. Đầy đủ: Lý thuyết, Kiến trúc hệ thống, Giải thích Code, Demo ứng dụng thực tế. Cả 3 thành viên đều tham gia.

---

## 1. BẢNG PHÂN BỔ THỜI LƯỢNG TỔNG THỂ (TỔNG THỜI GIAN: ~17 PHÚT 30 GIÂY)

| Phân đoạn | Thời lượng | Người thực hiện | Nội dung trọng tâm | Công cụ / Màn hình hiển thị |
|---|---|---|---|---|
| **Phần 1: Khai mạc & Tổng quan Đề tài** | **2.0 phút** (00:00 - 02:00) | **Hữu Nam** (Leader) | Giới thiệu thành viên, mục tiêu đề tài, bài toán nghiệp vụ TMĐT công nghệ & phạm vi chức năng | Slide 1, 2 (Slide PowerPoint `TechStore_Slides.pptx`) |
| **Phần 2: Cơ sở Lý thuyết RESTful API & So sánh** | **3.0 phút** (02:00 - 05:00) | **Thành Trung** (Backend) | 6 Nguyên tắc REST, Richardson Level 1-3 (HATEOAS), HTTP Methods, Status Codes, So sánh REST vs GraphQL vs SOAP | Slide 3, 4, 6 |
| **Phần 3: Thiết kế CSDL & 37 Endpoints API** | **3.0 phút** (05:00 - 08:00) | **Thành Trung** (Backend) | Sơ đồ CSDL ERD 17 bảng chuẩn hóa 3NF, Cổng Swagger UI / Redoc, Cấu trúc Controller/Model trong PHP thuần | Sơ đồ ERD, Cổng Swagger UI (`/docs/`), IDE Code `ProductController.php` |
| **Phần 4: Kiến trúc Hệ thống, Bảo mật JWT & VietQR** | **3.5 phút** (08:00 - 11:30) | **Hữu Nam** (Leader) | Kiến trúc Decoupled 3 tầng, Token JWT (Access/Refresh RTR), Phân quyền RBAC, Chặn OWASP Top 10 (401, 403, IDOR), Luồng VietQR NAPAS247 | Slide Kiến trúc, Code `Router.php`, `AuthMiddleware.php`, Màn hình API Live Telemetry chia đôi (`api-live.html`) |
| **Phần 5: Kiến trúc Front-end & api.js Wrapper** | **1.5 phút** (11:30 - 13:00) | **Tuấn Kiệt** (Front-end) | Design Tokens CSS CellphoneS, Glassmorphism, Lớp `ApiClient` Fetch wrapper, JWT Interceptor tự động làm mới token ngầm khi gặp 401 | Slide Front-end, IDE Code `public/assets/js/api.js` |
| **Phần 6: Demo Thực tế Phân hệ Khách hàng (Client)** | **2.5 phút** (13:00 - 15:30) | **Tuấn Kiệt** (Front-end) | Luồng mua hàng thực tế: Live Search 250ms, Trang chi tiết + Gợi ý combo phụ kiện, Giỏ hàng realtime, Áp mã coupon, Quét VietQR + Polling 3s, Lịch sử đơn | Trình duyệt Chrome thao tác trực tiếp trên giao diện Client (`/pages/`) |
| **Phần 7: Demo Quản trị Admin & Kiểm thử Postman** | **2.0 phút** (15:30 - 17:30) | **Tuấn Kiệt** (Front-end) | Dashboard KPI doanh thu 7 ngày, CRUD sản phẩm preview ảnh, Duyệt tiền VietQR & In hóa đơn A4, Bộ test Postman 37 API & 12 UI Test Cases, Kết luận | Phân hệ Admin (`/admin/`), Postman runner kết quả PASSED 100%, Slide Kết luận |

---

## 2. KỊCH BẢN CHI TIẾT & LỜI THOẠI MẪU CHO TỪNG THÀNH VIÊN

### THÀNH VIÊN 1: NGUYỄN HỮU NAM (LEADER / TECH LEAD & SECURITY)
**Tổng thời lượng phụ trách:** ~5.5 phút (chia 2 lượt: Mở đầu 2.0 phút và Kiến trúc/Bảo mật 3.5 phút)

#### LƯỢT 1: Khai mạc & Giới thiệu Tổng quan Đề tài (00:00 - 02:00, 2 phút)
- **Hành động:** Bật webcam góc trên phải, trình chiếu Slide 1 (Trang bìa) và Slide 2 (Mục tiêu đề tài) trong `TechStore_Slides.pptx`.
- **Lời thoại phát biểu:**
  > *"Kính chào thầy và các bạn! Em là Nguyễn Hữu Nam, đại diện cho nhóm sinh viên thực hiện đồ án môn học Công nghệ Web và Kiến trúc Ứng dụng. Nhóm em gồm 3 thành viên:*
  > *1. Em – Nguyễn Hữu Nam, nhóm trưởng phụ trách kiến trúc back-end, bảo mật xác thực JWT, thanh toán VietQR và tích hợp hệ thống.*
  > *2. Bạn Trần Thành Trung, phụ trách cơ sở lý thuyết REST, thiết kế cơ sở dữ liệu MySQL, xây dựng các RESTful Controllers và thuật toán gợi ý.*
  > *3. Bạn Tuấn Kiệt, phụ trách toàn bộ giao diện Front-end người dùng và quản trị viên, xây dựng thư viện kết nối API, cùng bộ kiểm thử tự động.*
  >
  > *Đề tài của nhóm chúng em thuộc **Topic 1: RESTful API**. Nhóm đã xây dựng nền tảng thương mại điện tử bán thiết bị công nghệ mang tên **TechStore**, lấy cảm hứng từ chuỗi bán lẻ CellphoneS.*
  > *Mục tiêu cốt lõi của đề tài là:*
  > *Thứ nhất: Hiện thực hóa 100% chuẩn RESTful API bằng ngôn ngữ PHP 8 thuần không dựa vào framework có sẵn.*
  > *Thứ hai: Kiến trúc hệ thống theo mô hình Decoupled – phân tách độc lập hoàn toàn giữa Back-end và Front-end.*
  > *Thứ ba: Bảo mật toàn diện với chuẩn JSON Web Token (RFC 7519), phân quyền RBAC và kiểm soát các lỗ hổng OWASP API Top 10.*
  > *Và Thứ tư: Tích hợp phương thức thanh toán số thế hệ mới qua chuẩn mã VietQR NAPAS247 và cơ chế Polling thời gian thực.*
  >
  > *Sau đây, em xin kính mời bạn Thành Trung tiếp tục phần trình bày về Cơ sở lý thuyết REST và Thiết kế Cơ sở dữ liệu của hệ thống."*

---

#### LƯỢT 2: Kiến trúc Hệ thống, Cơ chế Bảo mật JWT & Luồng VietQR (08:00 - 11:30, 3.5 phút)
- **Hành động:** 
  1. Chiếu Slide Kiến trúc 3 tầng (Slide 3) & Slide Bảo mật JWT (Slide 4).
  2. Mở Visual Studio Code, hiển thị file `api/core/Router.php`, `api/middleware/AuthMiddleware.php`, và `api/services/JwtService.php`.
  3. Mở song song màn hình Chrome hoặc trang API Live Telemetry (`/pages/api-live.html`) để chứng minh cơ chế bắt lỗi 401, 403 RBAC và 403 IDOR.
- **Lời thoại phát biểu:**
  > *"Em xin tiếp tục phần trình bày về Kiến trúc Hệ thống và Cơ chế Bảo mật của TechStore.*
  >
  > *Về mặt kiến trúc, TechStore áp dụng mô hình 3 tầng phân tách (Decoupled 3-Tier Architecture):*
  > *- Tầng 1: Presentation Layer gồm HTML5, CSS3 Tokens và JavaScript ES6 thuần. Toàn bộ giao tiếp đều qua Fetch API, hoàn toàn không render mã HTML từ server.*
  > *- Tầng 2: Application Gateway viết bằng PHP 8 thuần theo mô hình MVC, sử dụng Front Controller tại public/index.php kết hợp lớp Router do nhóm tự viết. Router hỗ trợ đầy đủ các phương thức GET, POST, PUT, DELETE, tự động phân giải Route Parameters dạng {id} và chuỗi Middleware tiền xử lý.*
  > *- Tầng 3: Data Persistence sử dụng MySQL 8 với 100% PDO Native Prepared Statements chống tuyệt đối SQL Injection.*
  >
  > *Về cơ chế xác thực và bảo mật:*
  > *Hệ thống tuân thủ nghiêm ngặt nguyên lý Stateless của REST – tuyệt đối không dùng Session hay Cookie máy chủ, mà sử dụng cặp Token JWT theo chuẩn RFC 7519:*
  > *1. Access Token có thời hạn sống ngắn (15 đến 60 phút), được mã hóa bằng thuật toán HMAC-SHA256, mang thông tin user_id và role.*
  > *2. Refresh Token có thời hạn 7 ngày, được mã hóa và lưu vào bảng refresh_tokens trong MySQL. Nhóm cài đặt cơ chế Refresh Token Rotation (RTR): mỗi khi client yêu cầu cấp lại Access Token qua POST /auth/refresh, một Refresh Token mới sẽ được sinh ra và thu hồi mã cũ, ngăn chặn triệt để tấn công Replay Attack.*
  >
  > *Đặc biệt, hệ thống kiểm soát chặt chẽ các lỗ hổng OWASP API Security Top 10:*
  > *- Lỗ hổng API02 (Broken Authentication): Khi client gửi request không có Bearer Token, AuthMiddleware lập tức trả về mã lỗi HTTP 401 Unauthorized theo chuẩn RFC 7807.*
  > *- Lỗ hổng API05 (Broken Function Level Authorization): Khi tài khoản khách hàng cố truy cập API thống kê doanh thu /admin/stats, RoleMiddleware chặn lại với mã HTTP 403 Forbidden.*
  > *- Lỗ hổng API01 (Broken Object Level Authorization - IDOR): Tại endpoint POST /orders/{id}/cancel, hệ thống đối chiếu user_id trong token với user_id của đơn hàng trong MySQL. Nếu khách hàng cố tình truyền ID đơn của người khác, server sẽ từ chối với mã 403 Forbidden.*
  >
  > *Về phương thức thanh toán VietQR:*
  > *Khi khách đặt hàng chọn hình thức chuyển khoản, QrService tự động sinh payload và ảnh mã QR theo chuẩn NAPAS247 ngân hàng MBBank, đính kèm số tiền chính xác và mã đơn duy nhất. Phía client sử dụng cơ chế Polling 3 giây gọi API /orders/{code}/payment-status để kiểm tra trạng thái thanh toán.*
  >
  > *Tiếp theo đây, em xin nhường lời cho bạn Tuấn Kiệt để trình bày về Tầng Front-end và Demo trực tiếp toàn bộ hệ thống."*

---

### THÀNH VIÊN 2: TRẦN THÀNH TRUNG (BACK-END & DATABASE & THUẬT TOÁN)
**Tổng thời lượng phụ trách:** ~6.0 phút (Từ 02:00 đến 08:00)

#### Phần 2: Cơ sở Lý thuyết RESTful API & So sánh Công nghệ (02:00 - 05:00, 3 phút)
- **Hành động:** Trình chiếu Slide 3, Slide 4 và Slide so sánh REST vs GraphQL vs SOAP (`TechStore_Slides.pptx`).
- **Lời thoại phát biểu:**
  > *"Xin chào thầy và các bạn, em là Trần Thành Trung. Sau đây em xin phép trình bày về Cơ sở lý thuyết của Topic 1: RESTful API.*
  >
  > *REST (Representational State Transfer) là phong cách kiến trúc phần mềm do Roy Fielding đề xuất năm 2000. Hệ thống TechStore được xây dựng tuân thủ đầy đủ 6 nguyên tắc cốt lõi của REST:*
  > *1. Client-Server Architecture: Tách rời giao diện người dùng và xử lý nghiệp vụ máy chủ.*
  > *2. Statelessness (Vô trạng thái): Mọi HTTP request từ client đều phải chứa đầy đủ thông tin định danh và ngữ cảnh cần thiết để máy chủ xử lý, máy chủ không lưu session người dùng.*
  > *3. Cacheability: Sử dụng các HTTP header Cache-Control, max-age và ETag để tối ưu hiệu năng và giảm tải băng thông.*
  > *4. Layered System: Khách hàng chỉ tương tác với cổng API mà không cần biết cấu trúc CSDL phía sau.*
  > *5. Code on Demand (Tùy chọn).*
  > *Và 6. Uniform Interface: Giao diện thống nhất qua định danh tài nguyên URI dạng danh từ (ví dụ: /api/v1/products, /api/v1/orders) và thao tác thông qua các phương thức HTTP chuẩn.*
  >
  > *Về cấp độ trưởng thành, hệ thống của nhóm đạt Cấp độ 3 trong Mô hình Richardson (Richardson Maturity Model Level 3) nhờ tích hợp HATEOAS (Hypermedia As The Engine Of Application State). Trong phản hồi JSON của danh sách sản phẩm, API đính kèm khối _links trỏ đến liên kết trang trước, trang sau và chi tiết sản phẩm, cho phép client tự động điều hướng mà không cần hardcode URI.*
  >
  > *Nhóm cũng thực hiện nghiên cứu so sánh chuyên sâu giữa REST, GraphQL và SOAP:*
  > *- So với SOAP: REST nhẹ hơn rất nhiều, sử dụng định dạng JSON thân thiện thay vì phong bì XML cồng kềnh, dễ dàng phát triển và tiêu thụ trên web và mobile.*
  > *- So với GraphQL: Mặc dù GraphQL linh hoạt trong việc chống over-fetching, nhưng REST vượt trội hơn về khả năng tận dụng bộ nhớ đệm HTTP ở tầng mạng (CDN, Browser Caching), chuẩn hóa mã lỗi HTTP tự nhiên, đơn giản trong việc bảo mật và kiểm soát quyền truy cập endpoint."*

---

#### Phần 3: Thiết kế Cơ sở Dữ liệu Quan hệ & 37 Endpoints API (05:00 - 08:00, 3 phút)
- **Hành động:** 
  1. Trình chiếu sơ đồ ERD trực quan (Slide 5 hoặc file `docs/erd.png`).
  2. Mở trình duyệt truy cập Cổng tài liệu API tương tác tại địa chỉ `http://localhost:8000/docs/index.html` (Swagger UI & Redoc).
  3. Mở mã nguồn trong VS Code: `api/controllers/ProductController.php` và `api/services/RecommendService.php`.
- **Lời thoại phát biểu:**
  > *"Tiếp theo, em xin trình bày về Thiết kế Cơ sở Dữ liệu và Danh mục 37 RESTful API của TechStore.*
  >
  > *Hệ thống CSDL được thiết kế theo mô hình quan hệ MySQL 8 chuẩn hóa ở mức 3NF gồm 17 bảng vật lý, phân bố trên 6 phân hệ cốt lõi:*
  > *- Phân hệ Người dùng: gồm bảng users và user_addresses lưu sổ địa chỉ nhận hàng.*
  > *- Phân hệ Danh mục & Hãng: categories và brands có quan hệ 1-N với sản phẩm.*
  > *- Phân hệ Sản phẩm: products, product_images, product_variants và product_attributes lưu trữ chi tiết thông số kỹ thuật (RAM, ROM, Chip, Màn hình).*
  > *- Phân hệ Bán hàng & Khuyến mãi: carts, cart_items, coupons với đầy đủ logic giảm giá theo % hoặc tiền cố định.*
  > *- Phân hệ Đơn hàng: orders, order_items và payments.*
  > *- Phân hệ Tương tác & Gợi ý: reviews, wishlists, event_logs và product_similarity.*
  >
  > *Toàn bộ hệ thống cung cấp chính xác 37 endpoints RESTful API hoàn chỉnh, được tài liệu hóa tương tác tại đường dẫn /docs/ bằng Swagger UI và Redoc dựa trên tệp chuẩn OpenAPI 3.0.*
  > *Tất cả các API đều phản hồi theo cấu trúc chuẩn ISO:*
  > *Thành công trả về mã 200/201 kèm object { success: true, data: ..., message: ... }.*
  > *Thất bại trả về đúng mã lỗi 400, 401, 403, 404, 422 kèm mã lỗi cụ thể.*
  >
  > *Một điểm nhấn kỹ thuật quan trọng của nhóm là **Thuật toán Gợi ý Sản phẩm Thông minh** được cài đặt trong RecommendService.php:*
  > *1. Thuật toán Content-based (Sản phẩm tương tự): Nhóm tiến hành vector hóa các đặc trưng danh mục, hãng và mức giá, tính toán độ tương đồng Cosine (Cosine Similarity) để đề xuất các thiết bị cùng phân khúc.*
  > *2. Thuật toán Item-based Collaborative Filtering (Frequently Bought Together - Thường được mua cùng): Phân tích lịch sử các đơn hàng trước đó để gợi ý combo phụ kiện (ví dụ: mua iPhone thì gợi ý củ sạc nhanh 20W và ốp lưng) kèm chính sách giảm giá 8% combo.*
  >
  > *Sau đây, em xin kính mời bạn nhóm trưởng Hữu Nam tiếp tục trình bày về Kiến trúc Hệ thống và Cơ chế Bảo mật."*

---

### THÀNH VIÊN 3: TUẤN KIỆT (FRONT-END LEAD, CLIENT INTEGRATION & QA)
**Tổng thời lượng phụ trách:** ~6.0 phút (Từ 11:30 đến 17:30)

#### Phần 5: Kiến trúc Front-end & Lớp ApiClient Wrapper (11:30 - 13:00, 1.5 phút)
- **Hành động:** 
  1. Trình chiếu Slide Front-end & Design Tokens (Slide 8, 9).
  2. Mở Visual Studio Code, phóng to file `public/assets/js/api.js`.
- **Lời thoại phát biểu:**
  > *"Kính chào thầy và các bạn! Em là Tuấn Kiệt, thành viên phụ trách toàn bộ giao diện người dùng và tích hợp API của TechStore.*
  >
  > *Về mặt giao diện, hệ thống được xây dựng hoàn toàn bằng HTML5, CSS3 Tokens và JavaScript ES6 thuần, lấy cảm hứng từ CellphoneS với màu đỏ nhận diện Crimson Red #d70018 kết hợp phong cách Glassmorphism và Liquid Aurora Mesh hiện đại chuẩn xu hướng 2026.*
  >
  > *Toàn bộ kết nối giữa Front-end và Back-end được trừu tượng hóa và quản lý tập trung thông qua lớp ApiClient trong tệp api.js:*
  > *- Đây là một Fetch Wrapper tự động bắt cấu hình Base URL và gắn tiêu đề Content-Type.*
  > *- Cài đặt cơ chế **JWT Interceptor**: Mỗi request gửi đi sẽ tự động trích xuất Access Token từ bộ nhớ và đính kèm vào Header Authorization: Bearer.*
  > *- Cơ chế **Silent Refresh Token**: Nếu Access Token hết hạn và server trả về mã lỗi 401 Unauthorized, interceptor sẽ tự động chặn request, âm thầm gọi API /auth/refresh để lấy token mới, cập nhật lại bộ nhớ và tự động gửi lại request ban đầu mà người dùng không hề hay biết.*
  > *- Ngoài ra, api.js còn tích hợp cơ chế Smart Fallback trên localStorage, đảm bảo khi chấm bài trên máy sạch nếu chưa cấu hình CSDL, giao diện vẫn hoạt động trơn tru mà không bao giờ gặp lỗi màn hình trắng."*

---

#### Phần 6: Demo Trải nghiệm Mua sắm Khách hàng (13:00 - 15:30, 2.5 phút)
- **Hành động:** Mở trình duyệt Chrome ở chế độ toàn màn hình, thao tác mượt mà theo đúng luồng:
  1. Trang chủ `pages/index.html`: Gõ thanh tìm kiếm "iPhone", xem Live Search gợi ý sau 250ms debounce.
  2. Trang sản phẩm `pages/product-detail.html`: Xem thông số kỹ thuật, khối mua kèm combo giảm giá 8%, carousel sản phẩm tương tự.
  3. Trang giỏ hàng `pages/cart.html`: Cập nhật số lượng realtime, nhập coupon `TECH10` giảm ngay 500.000đ.
  4. Trang thanh toán `pages/checkout.html`: Điền địa chỉ nhận hàng, chọn "Chuyển khoản VietQR".
  5. Trang QR `pages/payment-qr.html`: Hiển thị mã QR ngân hàng MBBank, đồng hồ đếm ngược 15 phút, giải thích cơ chế Polling 3s. Bấm nút giả lập chuyển khoản thành công -> Chuyển sang màn hình chúc mừng.
  6. Trang tài khoản `pages/account.html`: Xem đơn hàng vừa đặt ở trạng thái "Đã thanh toán".
- **Lời thoại phát biểu:**
  > *"Bây giờ, em xin phép demo trực tiếp luồng mua hàng thực tế từ góc nhìn khách hàng:*
  >
  > *Từ trang chủ, khi em nhập từ khóa 'iPhone' vào ô tìm kiếm, cơ chế Debounce 250ms lập tức gọi API /products?search=... và hiển thị tức thì danh sách gợi ý.*
  > *Bấm vào iPhone 16 Pro Max, trang chi tiết hiển thị đầy đủ thư viện ảnh sắc nét, bảng thông số kỹ thuật của chip A18 Pro và màn hình 120Hz.*
  > *Ngay phía dưới là khối Gợi ý Combo mua kèm củ sạc 20W với ưu đãi giảm 8%, cùng khối Sản phẩm tương tự được tính toán từ Cosine Similarity.*
  >
  > *Em tiến hành thêm sản phẩm vào giỏ hàng. Tại trang cart.html, giá tạm tính và tổng tiền được tính toán realtime phía client và đồng bộ lên server. Em nhập mã giảm giá TECH10 và bấm Áp dụng, hệ thống gọi API /coupons/validate và trừ ngay 500.000đ theo đúng thể lệ.*
  >
  > *Bước sang trang Thanh toán, em chọn hình thức 'Chuyển khoản VietQR'. Ngay khi bấm Đặt hàng, API POST /payments/qr khởi tạo giao dịch và chuyển hướng sang trang payment-qr.html.*
  > *Tại đây, mã QR chuẩn VietQR ngân hàng MBBank hiển thị với chính xác số tiền và nội dung chuyển khoản là mã đơn hàng. Đồng hồ đếm ngược 15 phút hoạt động, song song với cơ chế Polling gửi request kiểm tra mỗi 3 giây một lần.*
  > *Để tiện cho việc chấm bài của giảng viên, trang có tích hợp nút Giả lập chuyển khoản thành công. Em xin bấm nút này: ngay lập tức cơ chế Polling nhận diện trạng thái thanh toán thành công, đóng đồng hồ và chuyển hướng đến trang xác nhận đơn hàng. Vào trang cá nhân, đơn hàng lập tức xuất hiện trong lịch sử mua sắm với trạng thái Đã thanh toán."*

---

#### Phần 7: Demo Phân hệ Quản trị Admin & Báo cáo Kiểm thử (15:30 - 17:30, 2.0 phút)
- **Hành động:** 
  1. Đăng nhập tài khoản Admin `admin@techstore.local`.
  2. Mở Dashboard `admin/index.html`: Giới thiệu 4 thẻ KPI doanh thu, số đơn, biểu đồ cột 7 ngày.
  3. Mở Quản lý Đơn hàng `admin/orders.html`: Thao tác nút "Duyệt TT" và mở modal in hóa đơn A4.
  4. Mở Quản lý Sản phẩm `admin/products.html`: Mở form thêm sản phẩm, tải ảnh lên xem preview ảnh trực tiếp.
  5. Mở Postman hoặc bảng kiểm thử: Chiếu Postman Test Collection với 37/37 API PASSED và 12 UI Test Cases đạt 100%.
  6. Kết luận và chào kết thúc video.
- **Lời thoại phát biểu:**
  > *"Tiếp theo là Phân hệ Quản trị viên Admin tại địa chỉ /admin:*
  >
  > *Sau khi đăng nhập với quyền Admin, Dashboard hiển thị bảng thống kê KPI tổng quan về doanh thu tháng, tăng trưởng, số đơn hàng mới và biểu đồ cột doanh thu 7 ngày được vẽ tự động từ API /admin/stats.*
  > *Tại trang Quản lý đơn hàng, Admin có thể tra cứu nhanh, bấm nút 'Duyệt TT' để xác nhận giao dịch ngân hàng hoặc cập nhật trạng thái sang Đang giao và Hoàn tất. Hệ thống cũng cung cấp mẫu in hóa đơn bán hàng chuẩn khổ A4 chuyên nghiệp để đóng gói cùng kiện hàng.*
  > *Tại trang Quản lý sản phẩm, Admin có thể thêm mới hoặc cập nhật sản phẩm với tính năng xem trước hình ảnh tải lên từ máy tính trước khi lưu vào cơ sở dữ liệu.*
  >
  > *Về công tác kiểm thử (Quality Assurance):*
  > *Nhóm đã xây dựng bộ Postman Collection bao quát toàn bộ 37 endpoints RESTful API, kiểm tra đầy đủ các kịch bản Success, Validation Error, Token Expiration và Phân quyền RBAC. Toàn bộ 37/37 kiểm thử tự động đều vượt qua (100% Passed).*
  > *Bên cạnh đó, 12 kịch bản kiểm thử giao diện người dùng (UI Test Cases) từ form validation, debounce search, giỏ hàng đến responsive trên thiết bị di động đều đạt kết quả hoàn hảo.*
  >
  > *Tổng kết lại: Đồ án TechStore của nhóm đã hoàn thành xuất sắc toàn bộ mục tiêu của Topic 1: RESTful API. Hệ thống vừa đảm bảo tính chuẩn mực về lý thuyết kiến trúc, vừa mang lại trải nghiệm thực tế mượt mà, sẵn sàng triển khai thực tiễn.*
  > *Chúng em xin chân thành cảm ơn thầy và các bạn đã dành thời gian theo dõi phần trình bày của nhóm!"*

---

## 3. CHECKLIST KỸ THUẬT QUAY VIDEO & MẸO GHI ĐIỂM CAO

1. **Chuẩn bị trước khi bấm máy:**
   - Đảm bảo MySQL đang chạy và đã nạp CSDL bằng lệnh `import_db.bat`.
   - Khởi động web server bằng `start.bat` (chạy tại cổng `http://localhost:8000`).
   - Mở sẵn 3 màn hình/tab:
     - Tab 1: Slide thuyết trình `TechStore_Slides.pptx`.
     - Tab 2: Trình duyệt mở TechStore Client & Admin.
     - Tab 3: Visual Studio Code với các file trọng tâm (`Router.php`, `AuthMiddleware.php`, `api.js`, `schema.sql`).
   - Đăng nhập sẵn tài khoản test trên trình duyệt để demo mượt mà, không bị chậm trễ lúc gõ phím.

2. **Cách quay với Google Meet / Zoom:**
   - Tạo phòng Google Meet/Zoom có cả 3 thành viên tham gia.
   - Bật camera của cả 3 thành viên (yêu cầu của đề tài: *Each team member must participate*).
   - Ghim camera của người đang nói hoặc để layout dạng "Tiled" (Lưới) ở góc màn hình.
   - Người phụ trách chia sẻ màn hình theo từng phân đoạn được phân công.
   - Sử dụng micro rõ ràng, phòng yên tĩnh, kiểm tra âm lượng trước khi ghi hình chính thức.

3. **Mẹo kiểm soát thời gian dưới 20 phút:**
   - Tập dượt (Rehearsal) 1 lần trước khi bấm quay thật.
   - Đặt đồng hồ bấm giờ đếm ngược 18 phút trên điện thoại để kiểm soát nhịp độ nói.
   - Không dừng lại quá lâu ở những phần gõ chữ; nên copy-paste hoặc dùng tính năng autofill nếu cần.
