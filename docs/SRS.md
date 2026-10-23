# ĐẶC TẢ YÊU CẦU PHẦN MỀM (SRS) - PHA ELABORATION
## HỆ THỐNG THƯƠNG MẠI ĐIỆN TỬ THIẾT BỊ CÔNG NGHỆ TECHSTORE (RESTFUL API)

**TRƯỜNG ĐẠI HỌC TÔN ĐỨC THẮNG**  
**KHOA CÔNG NGHỆ THÔNG TIN**  

- **Đề tài:** Nền tảng Thương mại Điện tử Đồ Công nghệ TechStore ứng dụng RESTful API (JWT, Thanh toán VietQR, Thuật toán Gợi ý Sản phẩm)
- **Môn học:** Công nghệ Web / Kiến trúc Phần mềm (Topic 1: RESTful API)
- **Nhóm / Lớp:** TechStore / Nhóm 1
- **Giảng viên hướng dẫn:** ThS. Nguyễn Khắc Cường
- **Thành viên nhóm:**
  - Trần Hữu Nam (Trưởng nhóm / Core Backend & Architecture)
  - Nguyễn Thành Trung (Backend & Thuật toán Gợi ý / CSDL)
  - Phạm Tuấn Kiệt (Frontend & UI / Tài liệu & Postman)
- **Phiên bản:** 2.1 (Elaboration Refined Baseline - 14 Core Use Cases, 34 FRs, 15 BRs, 7 NFRs)
- **Ngày hoàn thiện:** Tháng 10/2026

---

### Mục lục tài liệu

| Phần | Tiêu đề | Nội dung chính |
| :---: | :--- | :--- |
| **[Phần 1](#phan-1)** | **Giới thiệu, Tầm nhìn và Ranh giới hệ thống** | Tầm nhìn sản phẩm, ranh giới In-Scope vs Out-of-Scope (Non-Goals), đối tượng người dùng, danh mục User Requirements (UR) và thuật ngữ chuyên ngành (Glossary). |
| **[Phần 2](#phan-2)** | **Bối cảnh và Kiến trúc nền tảng** | Ranh giới hệ thống, sơ đồ ngữ cảnh (Context Diagram), tác nhân, kiến trúc tham chiếu Decoupled Client-Server (RESTful API + SPA), giao diện tích hợp và 15 quy tắc nghiệp vụ cốt lõi (BR). |
| **[Phần 3](#phan-3)** | **Đặc tả Yêu cầu chức năng (FR)** | Đặc tả chi tiết 10 nhóm chức năng (FR01 - FR10) theo chuẩn Ian Sommerville §4.3.2, bao quát Xác thực (JWT), Quản lý Sản phẩm/Cấu hình, Giỏ hàng/Coupon, Đặt hàng (Transaction), Thanh toán VietQR, Gợi ý thông minh (Cosine/Co-occurrence) và Quản trị hệ thống (Admin). |
| **[Phần 4](#phan-4)** | **Mô hình tương tác và Use Case** | Danh mục 14 Use Cases cốt lõi (UC01 - UC14) đặc tả đầy đủ luồng chính, luồng phụ và luồng ngoại lệ tách biệt; 3 kịch bản xử lý nền (SYS01 - SYS03) và ma trận máy trạng thái Đơn hàng/Thanh toán. |
| **[Phần 5](#phan-5)** | **Yêu cầu phi chức năng (NFR)** | Lượng hóa 7 nhóm NFR theo tiêu chuẩn quốc tế ISO/IEC 25010:2023 trên môi trường kiểm thử chuẩn E1/D1/L1. |
| **[Phần 6](#phan-6)** | **Ma trận truy vết và Nghiệm thu** | Ma trận truy vết hai chiều (UR - FR - UC - BR - NFR - API Endpoint - Test Case ID), ca kiểm thử mẫu phân rã theo luồng và tiêu chí nghiệm thu pha Elaboration / Construction. |

---

<a id="phan-1"></a>

## 1. Giới thiệu, Tầm nhìn và Ranh giới hệ thống

### 1.1 Mục đích và tầm nhìn sản phẩm
Tài liệu Đặc tả Yêu cầu Phần mềm (Software Requirements Specification - SRS) này định nghĩa toàn diện các yêu cầu chức năng, yêu cầu phi chức năng, quy tắc nghiệp vụ và khung kiến trúc nền tảng cho hệ thống **TechStore** trong pha **Elaboration** theo tiến trình Unified Process (UP/RUP).

Tầm nhìn cốt lõi của **TechStore** là chuyển dịch căn bản kiến trúc thương mại điện tử:

> **Từ:** *"Monolithic & Coupled E-commerce Web"* (Hệ thống web thương mại điện tử nguyên khối, phụ thuộc chặt chẽ)  
> **Sang:** *"Headless & Decoupled High-Performance RESTful Commerce"* (Nền tảng thương mại điện tử phi tập trung, RESTful API thuần, thanh toán QR tự động và gợi ý thông minh)

Thay vì chỉ dừng lại ở việc hiển thị trang web bán lẻ tĩnh truyền thống, TechStore giải quyết triệt để 3 bài toán lớn của ngành bán lẻ thiết bị công nghệ hiện đại (lấy cảm hứng từ thực tiễn chuỗi CellphoneS):
1. **Khách hàng tìm kiếm và chọn mua sản phẩm công nghệ có cấu hình phức tạp (CPU, RAM, ROM, Pin, Màn hình) nhanh nhất bằng cách nào?** (Cung cấp bộ lọc đa chiều kết hợp, Instant Search phản hồi dưới 150ms với kỹ thuật debounce 250ms, hiển thị bảng thông số chi tiết).
2. **Trải nghiệm thanh toán không dùng tiền mặt (Cashless) diễn ra an toàn và tức thì như thế nào mà không cần nhập thủ công thông tin ngân hàng?** (Hiện thực hóa cổng thanh toán động **VietQR** NAPAS247 tự động điền số tiền và mã đơn hàng, đồng hồ đếm ngược 15 phút kèm cơ chế polling realtime 3 giây và webhook xác thực HMAC).
3. **Làm sao để gia tăng giá trị đơn hàng trung bình (AOV) và giữ chân khách hàng?** (Ứng dụng kết hợp 2 thuật toán: Khai phá dữ liệu giỏ hàng Co-occurrence cho Combo phụ kiện thường mua cùng nhau - Frequently Bought Together, và Lọc dựa trên nội dung Content-based Filtering với độ tương đồng Cosine vector đặc trưng).

### 1.2 Ranh giới hệ thống: Những gì hệ thống LÀM GÌ và KHÔNG THỂ LÀM GÌ (In-Scope vs Out-of-Scope)
Để bảo đảm tính khả thi cho nhóm phát triển 3 sinh viên và loại bỏ rủi ro mở rộng phạm vi (Scope Creep), hệ thống xác lập rõ ranh giới nghiệp vụ:

| Hạng mục nghiệp vụ | Hệ thống CẦN LÀM (In-Scope) | Hệ thống KHÔNG THỂ LÀM (Out-of-Scope / Non-Goals) |
| :--- | :--- | :--- |
| **1. Xác thực & Phân quyền** | Xác thực JWT (Access Token 2h, Refresh Token 30d lưu DB), phân quyền RBAC (Customer vs Admin), mã hóa bcrypt, Google OAuth 2.0 demo. | **KHÔNG triển khai SMS OTP hay tổng đài xác minh:** Không kết nối gateway SMS thương mại có tính phí hay máy chủ xác thực doanh nghiệp SSO phức tạp. |
| **2. Quản lý Sản phẩm & Cấu hình** | Danh mục, thương hiệu, sản phẩm công nghệ (thông số kỹ thuật đa chiều RAM/ROM/Chip/Pin), upload ảnh an toàn kiểm tra MIME/kích thước. | **KHÔNG tích hợp hệ thống quản lý chuỗi cung ứng ERP / Đa kho:** Không quản trị logistics đa chi nhánh hay lập kế hoạch nhập hàng nhà máy. |
| **3. Giỏ hàng & Khuyến mãi** | Quản lý giỏ hàng phía máy chủ của khách đăng nhập, tính toán tiền realtime, áp dụng mã giảm giá Coupon theo % hoặc tiền cố định kèm ràng buộc. | **KHÔNG hỗ trợ Flash Sale hàng triệu người đồng thời:** Không xây dựng cụm Redis/Kafka phân tán chịu tải quy mô sàn TMĐT quốc gia. |
| **4. Đặt hàng & Giao vận** | Đặt hàng trong Database Transaction ACID (kiểm tra tồn kho, trừ kho, tạo đơn), quản lý luồng trạng thái đơn 5 bước, in hóa đơn bán hàng. | **KHÔNG kết nối định vị tài xế giao hàng thời gian thực:** Không tích hợp API bên thứ ba như GHN/GHTK để theo dõi GPS bản đồ giao hàng. |
| **5. Thanh toán VietQR** | Sinh ảnh VietQR NAPAS247 động chứa mã đơn và số tiền, polling tự động 3s cập nhật trạng thái đơn hàng, webhook giả lập chữ ký HMAC, duyệt tiền về thủ công Admin. | **KHÔNG kết nối cổng thẻ quốc tế Visa/MasterCard hoặc giải ngân tiền mặt thực:** Không mở cổng thanh toán tín dụng quốc tế trực tiếp có mã CVV. |
| **6. Gợi ý thông minh (Recommendation)** | Gợi ý mua kèm (Co-occurrence từ bảng `order_items`), gợi ý tương tự (Cosine similarity từ vector đặc trưng sản phẩm), điểm số lai Hybrid. | **KHÔNG huấn luyện mạng Deep Learning nặng nề:** Không triển khai mô hình học sâu đòi hỏi hạ tầng GPU chuyên dụng chạy ngầm. |
| **7. Quản trị hệ thống (Admin)** | Dashboard doanh thu 7 ngày, quản lý đơn hàng, duyệt thanh toán, khóa/mở tài khoản người dùng, nhật ký kiểm toán hệ thống (Audit Logs). | **KHÔNG sao lưu/phục hồi CSDL trực tiếp trên Web UI:** Thao tác sao lưu/phục hồi DB thuộc về DevOps/DBA quản trị hạ tầng qua file `.sql`. |

### 1.3 Đối tượng người dùng mục tiêu (Target Users)
* **Khách hàng vãng lai (Guest):** Người dùng chưa tạo tài khoản hoặc chưa đăng nhập; có nhu cầu xem sản phẩm, tra cứu cấu hình, so sánh giá cả và tìm kiếm sản phẩm trước khi quyết định mua hàng.
* **Khách hàng thành viên (Customer):** Người tiêu dùng cá nhân có nhu cầu mua sắm thiết bị công nghệ; cần tài khoản để quản lý giỏ hàng, áp dụng mã giảm giá, checkout qua VietQR, theo dõi đơn hàng, gửi đánh giá và nhận gợi ý cá nhân hóa.
* **Quản trị viên cửa hàng (Admin):** Nhân sự phụ trách kinh doanh và vận hành hệ thống TechStore; cần giao diện quản trị trực quan để theo dõi doanh thu, cập nhật tồn kho sản phẩm, duyệt thanh toán chuyển khoản và bảo đảm an toàn hệ thống.

### 1.4 Danh mục Yêu cầu người dùng (User Requirements - UR)

| Mã UR | Nhu cầu người dùng | Lý do nghiệp vụ | Ánh xạ FR | Ánh xạ UC |
| :---: | :--- | :--- | :---: | :---: |
| <a id="ur01"></a>**UR01** | Tạo tài khoản an toàn, đăng nhập nhận token JWT và tự động duy trì phiên làm việc không bị ngắt quãng. | Đảm bảo an toàn thông tin cá nhân, không phải đăng nhập lại liên tục khi thao tác mua sắm. | FR01 | UC01 |
| <a id="ur02"></a>**UR02** | Xem danh mục, thương hiệu và tìm kiếm nhanh sản phẩm gợi ý tức thì khi gõ từ khóa. | Tiết kiệm thời gian tìm kiếm thiết bị công nghệ theo thương hiệu yêu thích (Apple, Samsung, ASUS...). | FR02, FR03 | UC02 |
| <a id="ur03"></a>**UR03** | Lọc sản phẩm theo nhiều tiêu chí kỹ thuật: tầm giá, hãng, dung lượng RAM, dung lượng bộ nhớ ROM. | Sản phẩm công nghệ có tính so sánh cấu hình cao; người dùng cần tìm đúng thiết bị vừa túi tiền và nhu cầu. | FR03 | UC02 |
| <a id="ur04"></a>**UR04** | Xem chi tiết thông số kỹ thuật dạng bảng kẻ ô, bộ sưu tập ảnh và các combo phụ kiện thường mua cùng nhau. | Nắm rõ thông số trước khi mua, dễ dàng mua thêm sạc nhanh, tai nghe, ốp lưng giá ưu đãi trong cùng đơn hàng. | FR02, FR09 | UC02, UC09 |
| <a id="ur05"></a>**UR05** | Quản lý giỏ hàng tiện lợi, chỉnh sửa số lượng, áp dụng mã giảm giá (Coupon) và thấy tổng tiền tính toán minh bạch. | Kiểm soát chi phí, nhận ưu đãi giảm giá trước khi bước vào quy trình thanh toán chính thức. | FR04, FR05 | UC03 |
| <a id="ur06"></a>**UR06** | Đặt hàng nhanh chóng, chọn phương thức thanh toán VietQR (quét mã chuyển tiền tự động) hoặc COD khi nhận hàng. | Thanh toán không chạm hiện đại qua app ngân hàng, không lo nhập sai số tài khoản hoặc nội dung chuyển tiền. | FR06, FR07 | UC04, UC05 |
| <a id="ur07"></a>**UR07** | Theo dõi trực tiếp trạng thái thanh toán đơn hàng chuyển sang "Đã thanh toán" ngay khi tiền vào tài khoản. | Khách hàng yên tâm đơn hàng đã được ghi nhận thanh toán thành công mà không cần chờ gọi điện xác minh. | FR07 | UC05 |
| <a id="ur08"></a>**UR08** | Xem lịch sử đơn hàng, xem chi tiết tiến độ giao hàng và cho phép hủy đơn nếu đơn vẫn đang chờ xử lý. | Minh bạch thông tin mua hàng, cho phép khách hàng chủ động hủy khi thay đổi ý định mua sắm. | FR06 | UC06 |
| <a id="ur09"></a>**UR09** | Gửi đánh giá sao (1-5 sao) kèm bình luận cho sản phẩm đã mua và lưu sản phẩm yêu thích (Wishlist). | Tạo dựng độ tin cậy cộng đồng cho sản phẩm và dễ dàng lưu lại sản phẩm để mua sau. | FR08 | UC07, UC08 |
| <a id="ur10"></a>**UR10** | Quản trị viên xem Dashboard thống kê doanh thu 7 ngày, tổng số đơn mới, quản lý sản phẩm và tồn kho. | Giúp chủ cửa hàng nắm bắt kịp thời tình hình kinh doanh và kiểm soát hàng tồn kho để nhập hàng. | FR10 | UC10, UC12 |
| <a id="ur11"></a>**UR11** | Quản trị viên kiểm soát đơn hàng, bấm duyệt thanh toán nhanh ("Duyệt TT") và in hóa đơn bán lẻ chuyên nghiệp. | Tối ưu quy trình đóng gói và bàn giao hàng hóa cho bưu tá vận chuyển. | FR06, FR10 | UC11 |
| <a id="ur12"></a>**UR12** | Quản trị viên quản lý tài khoản người dùng (khóa/mở khóa), cấu hình mã giảm giá và tra cứu nhật ký kiểm toán. | Đảm bảo tính kỷ luật và truy vết minh bạch mọi hành động can thiệp dữ liệu trên hệ thống. | FR10 | UC13, UC14 |

### 1.5 Thuật ngữ và từ viết tắt (Glossary)
* **RESTful API:** Giao diện lập trình ứng dụng tuân thủ chuẩn chuyển giao trạng thái đại diện (Representational State Transfer), hoạt động phi trạng thái (Stateless) qua các phương thức HTTP tiêu chuẩn (GET, POST, PUT, PATCH, DELETE).
* **JWT (JSON Web Token):** Chuẩn mở RFC 7519 dùng để đại diện cho các yêu cầu xác thực an toàn giữa máy khách và máy chủ dưới dạng đối tượng JSON nhỏ gọn, được ký số bằng thuật toán HMAC-SHA256 (`HS256`).
* **VietQR:** Chuẩn nhận diện thanh toán bằng mã QR cá nhân và doanh nghiệp được phát triển bởi Công ty Cổ phần Thanh toán Quốc gia Việt Nam (NAPAS) và các ngân hàng thành viên.
* **Co-occurrence Filtering (FBT):** Thuật toán gợi ý dựa trên tần suất xuất hiện đồng thời của các cặp sản phẩm trong cùng giỏ hàng lịch sử ($Support$ và $Confidence$).
* **Content-based Filtering:** Thuật toán gợi ý dựa trên tính tương đồng về thuộc tính của sản phẩm (danh mục, hãng, khoảng giá, cấu hình kỹ thuật) sử dụng công thức khoảng cách Cosine trên vector đặc trưng chuẩn hóa.
* **ACID Transaction:** 4 thuộc tính cốt lõi của giao dịch cơ sở dữ liệu: Nguyên tử (Atomicity), Nhất quán (Consistency), Cô lập (Isolation), Bền vững (Durability).
* **Audit Log (Nhật ký kiểm toán):** Bản ghi bất biến lưu trữ lịch sử các hành động can thiệp nghiệp vụ của Quản trị viên (thêm/sửa sản phẩm, duyệt đơn, khóa user) kèm dấu vết thời gian chính xác.
* **AOV (Average Order Value):** Giá trị trung bình của một đơn hàng, tính bằng $\frac{\text{Tổng doanh thu}}{\text{Tổng số đơn hàng}}$.

---

<a id="phan-2"></a>

## 2. Bối cảnh và Kiến trúc nền tảng (Architectural Baseline)

### 2.1 Ranh giới hệ thống và Tác nhân (System Boundaries & Actors)

```
       ┌────────────────────────┐
       │   A01: Khách hàng      │
       │ (Khách vãng lai Guest /│
       │  Khách thành viên Cust)│
       └───────────┬────────────┘
                   │ HTTPS / Fetch API (IF01)
                   ▼
┌────────────────────────────────────────────────────────┐
│                   HỆ THỐNG TECHSTORE                   │
│                                                        │
│  ┌──────────────────────────────────────────────────┐  │
│  │ 1. Front Controller & PSR-4 Autoload Core Engine │  │
│  ├──────────────────────────────────────────────────┤  │
│  │ 2. Authentication & JWT Token Management Engine  │  │
│  ├──────────────────────────────────────────────────┤  │
│  │ 3. Product Catalog, Spec & Inventory Engine      │  │
│  ├──────────────────────────────────────────────────┤  │
│  │ 4. Server-Side Cart & Coupon Validation Engine   │  │
│  ├──────────────────────────────────────────────────┤  │
│  │ 5. Order Management & ACID Transaction Engine    │  │
│  ├──────────────────────────────────────────────────┤  │
│  │ 6. VietQR Generation & Realtime Polling Gateway  │  │
│  ├──────────────────────────────────────────────────┤  │
│  │ 7. Recommendation Engine (Cosine & Co-occurrence)│  │
│  ├──────────────────────────────────────────────────┤  │
│  │ 8. Admin Portal, Analytics & Audit Logging Core  │  │
│  └──────────────────────────────────────────────────┘  │
└───────▲────────────────────────────────────────▲───────┘
        │                                        │
        │ IF02 / IF03                            │ Quản trị / Giám sát (IF01)
        ▼                                        ▼
┌──────────────┐                         ┌───────────────┐
│ Cổng VietQR  │                         │ A02: Quản trị │
│ Ngân hàng &  │                         │  viên (Admin) │
│ Google OAuth │                         └───────────────┘
└──────────────┘
```

#### Bảng định nghĩa Tác nhân (Actors)
* **A01 - Khách hàng (Customer / Guest):**
  - *Khách vãng lai (Guest):* Người dùng truy cập hệ thống chưa đăng nhập; có quyền duyệt sản phẩm, tìm kiếm, lọc, xem chi tiết và thêm sản phẩm vào giỏ hàng cục bộ.
  - *Khách hàng thành viên (Customer):* Người dùng đã đăng ký và xác thực tài khoản qua JWT; có toàn quyền quản lý giỏ hàng trên server, đặt hàng, thanh toán VietQR, theo dõi/hủy đơn, đánh giá sản phẩm và nhận gợi ý cá nhân hóa.
* **A02 - Quản trị viên (Admin):** Tài khoản có `role = 'admin'`. Quản lý toàn bộ danh mục sản phẩm, duyệt đơn hàng và thanh toán, in hóa đơn, quản lý người dùng, mã giảm giá, theo dõi thống kê doanh thu và tra cứu nhật ký kiểm toán.
* **A03 - Cổng thanh toán VietQR / Ngân hàng (BankGateway):** Hệ thống bên ngoài cung cấp chuẩn sinh ảnh mã QR thanh toán NAPAS247 và gửi tín hiệu webhook xác nhận giao dịch chuyển khoản thành công.
* **A04 - Dịch vụ xác thực Google (Google OAuth Provider):** Hệ thống xác thực danh tính bên thứ ba phục vụ đăng nhập mở rộng qua Google Credential Token.
* **SYS - Bộ xử lý nền & Daemon (System Scheduler):** Tiến trình nội bộ xử lý hủy đơn hàng quá hạn thanh toán 15 phút và script CLI cập nhật ma trận tương đồng sản phẩm.

### 2.2 Kiến trúc tham chiếu nền tảng (Decoupled Clean MVC & Domain-Driven Layered Architecture)
Nhằm bảo đảm tính độc lập cao, mã nguồn mạch lạc, dễ mở rộng và kiểm thử toàn diện, TechStore áp dụng mô hình kiến trúc phân tầng tách rời (Decoupled Clean MVC) với sự phân định trách nhiệm rõ ràng:

1. **Presentation Layer (Tầng Trình diễn):** Giao diện Web Responsive hiện đại (HTML5, Vanilla CSS3 Design Tokens, Vanilla JavaScript ES6+). Hoàn toàn không phụ thuộc framework nặng, thời gian tải trang ban đầu dưới $1.0\text{ giây}$. Giao tiếp với tầng API thông qua module HTTP Client wrapper [public/assets/js/api.js](../public/assets/js/api.js) có cơ chế tự động đính kèm JWT Bearer và tự động làm mới token ngầm khi nhận mã `401 Unauthorized`.
2. **API Routing & Controller Layer (Tầng Điều phối API - HTTP Orchestrator):**
   - Front Controller [public/index.php](../public/index.php) tiếp nhận toàn bộ request, chuyển tiếp sang Router trung tâm [api/routes.php](../api/routes.php) và nhân xử lý router [api/core/Router.php](../api/core/Router.php) hỗ trợ tham số động `{id}` và gắn chuỗi Middleware ([CorsMiddleware](../api/middleware/CorsMiddleware.php), [AuthMiddleware](../api/middleware/AuthMiddleware.php), [RoleMiddleware](../api/middleware/RoleMiddleware.php)).
   - **Vai trò Controller (HTTP Orchestrator):** Tiếp nhận dữ liệu HTTP Request, thẩm định tính hợp lệ qua [Validator.php](../api/helpers/Validator.php), đóng vai trò nhạc trưởng điều phối gọi một hoặc nhiều Domain Model và Service nghiệp vụ, kiểm tra kết quả và đóng gói phản hồi chuẩn mực qua [Response.php](../api/helpers/Response.php). Controller **tuyệt đối không** chứa câu lệnh SQL thô, không trực tiếp thao tác lược đồ CSDL vật lý.
3. **Business & Service Layer (Tầng Dịch vụ Nghiệp vụ & Thuật toán):** Chứa toàn bộ logic thuật toán chuyên sâu không phụ thuộc giao diện:
   - Dịch vụ xác thực JWT ([JwtService.php](../api/services/JwtService.php)): Ký, giải mã và thẩm định token HS256 (RFC 7519).
   - Dịch vụ thanh toán VietQR ([QrService.php](../api/services/QrService.php)): Sinh chuỗi payload và URL ảnh QR chuẩn NAPAS247 liên ngân hàng.
   - Dịch vụ gợi ý thông minh ([RecommendService.php](../api/services/RecommendService.php)): Thuật toán Cosine Similarity, Co-occurrence và Hybrid Score.
4. **Domain Aggregate Model Layer (Tầng Mô hình Thực thể & Truy xuất Dữ liệu):**
   - Kế thừa lớp cơ sở [BaseModel.php](../api/models/BaseModel.php) đóng gói PDO Prepared Statements an toàn tuyệt đối trước SQL Injection.
   - Hoạt động theo mô hình Domain Aggregate Root: 10 Models chuyên biệt ([ProductModel](../api/models/ProductModel.php), [CategoryModel](../api/models/CategoryModel.php), [BrandModel](../api/models/BrandModel.php), [UserModel](../api/models/UserModel.php), [CartModel](../api/models/CartModel.php), [OrderModel](../api/models/OrderModel.php), [CouponModel](../api/models/CouponModel.php), [PaymentModel](../api/models/PaymentModel.php), [ReviewWishlistModel](../api/models/ReviewWishlistModel.php), [AnalyticsModel](../api/models/AnalyticsModel.php)) quản trị trọn vẹn 17 bảng trong [database/schema.sql](../database/schema.sql).
   - Hỗ trợ Controller điều phối đa mô hình (Multi-model Orchestration): Điển hình như `OrderController` điều phối đồng thời `OrderModel` (tạo đơn), `ProductModel` (trừ tồn kho), `CouponModel` (áp mã giảm giá), `CartModel` (dọn giỏ hàng) và `PaymentModel` (khởi tạo giao dịch thanh toán).
5. **Data Storage & Transaction Layer (Tầng CSDL & Giao dịch ACID):** Hệ quản trị cơ sở dữ liệu quan hệ MySQL / MariaDB thông qua PDO Native Prepared Statements trong [config/database.php](../config/database.php), hỗ trợ ACID Transaction bền vững khi đặt hàng và trừ kho. Có sẵn lớp Adapter dự phòng [DataStore.php](../api/helpers/DataStore.php) bảo đảm toàn vẹn dữ liệu đa môi trường.


### 2.3 Môi trường và giao diện tích hợp (Interfaces)
* **IF01 - Giao diện RESTful JSON API:** Định dạng dữ liệu trao đổi 100% qua JSON với header `Content-Type: application/json`. Phản hồi chuẩn hóa:
  - Thành công: `{ "success": true, "data": {...}, "message": "...", "pagination": {...} }`.
  - Thất bại: `{ "success": false, "error": { "code": "VALIDATION_ERROR", "message": "...", "details": [...] } }`.
  - Mã trạng thái HTTP tuân thủ chuẩn quốc tế: `200 OK`, `201 Created`, `400 Bad Request`, `401 Unauthorized`, `403 Forbidden`, `404 Not Found`, `409 Conflict`, `422 Unprocessable Entity`, `500 Internal Error`.
* **IF02 - Cổng thanh toán VietQR (NAPAS247):** Sinh ảnh mã QR thanh toán theo chuẩn liên ngân hàng Việt Nam tích hợp mã ngân hàng thụ hưởng (MB Bank - `970422`), số tài khoản và cú pháp tự động `TECHSTORE <ORDER_ID>`.
* **IF03 - Dịch vụ xác thực Google OAuth 2.0:** Tiếp nhận Google ID Token từ máy khách, giải mã và đồng bộ hồ sơ người dùng vào CSDL TechStore.

### 2.4 Chính sách tiền tệ và dữ liệu
* Tiền tệ duy nhất của hệ thống là **Việt Nam Đồng (VND)**.
* Định dạng số tiền là số nguyên không âm ($\ge 0$), không có chữ số thập phân; hiển thị trên giao diện người dùng có dấu phân cách hàng nghìn bằng dấu chấm (ví dụ: `15.990.000 đ`).

### 2.5 Danh mục Quy tắc nghiệp vụ cốt lõi (Business Rules - BR)

| Mã BR | Tên quy tắc nghiệp vụ | Nội dung chi tiết |
| :---: | :--- | :--- |
| <a id="br01"></a>**BR01** | Chuẩn RESTful API & Stateless | Mọi tương tác máy chủ đều phi trạng thái (Stateless); không sử dụng Session PHP truyền thống trên server; định danh người dùng được trích xuất duy nhất từ Header `Authorization: Bearer <JWT>`. |
| <a id="br02"></a>**BR02** | Cơ chế xác thực cặp Token JWT | Hệ thống cấp phát cặp token khi đăng nhập: Access Token có hạn ngắn (2 giờ) để gọi API; Refresh Token có hạn dài (30 ngày) được lưu trữ trong bảng `refresh_tokens`. Khi Access Token hết hạn, máy khách gửi Refresh Token để cấp lại Access Token mới mà không bắt người dùng đăng nhập lại. |
| <a id="br03"></a>**BR03** | Phân quyền truy cập dựa trên vai trò (RBAC) | Hệ thống có 2 vai trò: `customer` và `admin`. Tài khoản `admin` có quyền truy cập toàn bộ API quản trị `/api/v1/admin/*` và giao diện `/admin/`. Tài khoản `customer` chỉ được truy cập tài nguyên của chính mình; cố gắng truy cập dữ liệu người khác bị chặn mã lỗi `403 Forbidden`. |
| <a id="br04"></a>**BR04** | Tính nguyên tử (ACID) khi tạo đơn hàng | Thao tác đặt hàng phải được thực thi trong một Database Transaction nguyên tử: kiểm tra số lượng tồn kho `stock` của từng sản phẩm $\rightarrow$ trừ tồn kho $\rightarrow$ tạo bản ghi `orders` $\rightarrow$ tạo các dòng `order_items` $\rightarrow$ xóa sạch sản phẩm trong giỏ hàng. Bất kỳ sản phẩm nào không đủ tồn kho, toàn bộ giao dịch phải được Rollback ngay lập tức. |
| <a id="br05"></a>**BR05** | Quản lý vòng đời mã thanh toán VietQR | Mỗi đơn hàng thanh toán qua VietQR chỉ sinh duy nhất một mã QR hợp lệ trong vòng **15 phút**. Mã QR chứa nội dung chuyển khoản bắt buộc: `TECHSTORE <ORDER_ID>` và số tiền chính xác bằng `final_total` của đơn hàng. Sau 15 phút nếu chưa thanh toán, mã QR hết hạn (`EXPIRED`). |
| <a id="br06"></a>**BR06** | Cơ chế Polling và Webhook xác nhận thanh toán | Giao diện thanh toán QR tự động gửi yêu cầu Polling kiểm tra trạng thái mỗi 3 giây. Webhook tiếp nhận thông báo tiền về từ ngân hàng phải kiểm tra chữ ký xác thực HMAC (`hash_hmac('sha256', ...)`) để chống giả mạo; khi hợp lệ, đơn hàng tự động chuyển sang `CONFIRMED` và thanh toán chuyển sang `PAID`. |
| <a id="br07"></a>**BR07** | Quy tắc áp dụng mã giảm giá (Coupon) | Mỗi đơn hàng chỉ được áp dụng duy nhất 1 mã Coupon hợp lệ. Điều kiện hợp lệ: mã còn trong hạn sử dụng, số lượt sử dụng còn lại $> 0$, và tổng tiền hàng phải $\ge$ giá trị đơn tối thiểu (`min_order_value`). Mức giảm theo % có chặn mức giảm tối đa (`max_discount_value`). |
| <a id="br08"></a>**BR08** | Điều kiện khách hàng hủy đơn hàng | Khách hàng chỉ được phép hủy đơn hàng cá nhân khi đơn hàng đang ở trạng thái Chờ xử lý (`status = 'PENDING'`). Khi hủy thành công, hệ thống tự động hoàn lại số lượng tồn kho cho các sản phẩm trong đơn. Khi đơn đã sang `CONFIRMED`, `SHIPPING` hoặc `COMPLETED`, nút hủy bị vô hiệu hóa. |
| <a id="br09"></a>**BR09** | Điều kiện đánh giá sản phẩm đã mua | Chỉ khách hàng đã mua sản phẩm đó và đơn hàng đã ở trạng thái Hoàn tất (`COMPLETED`) mới có quyền gửi đánh giá sao (1 đến 5 sao) và bình luận. Mỗi khách hàng chỉ được gửi tối đa 1 đánh giá cho mỗi sản phẩm đã mua. |
| <a id="br10"></a>**BR10** | Thuật toán gợi ý tương tự Content-based | Độ tương đồng giữa 2 sản phẩm được tính bằng khoảng cách Cosine trên vector đặc trưng chuẩn hóa gồm 5 thuộc tính: Danh mục (`category_id`), Thương hiệu (`brand_id`), Khoảng giá, Dung lượng RAM và Dung lượng ROM. Kết quả được lưu sẵn vào bảng `product_similarity` để truy vấn tức thời $\le 10\text{ms}$. |
| <a id="br11"></a>**BR11** | Thuật toán gợi ý mua kèm Co-occurrence (FBT) | Sản phẩm mua kèm (Frequently Bought Together) được tính dựa trên tần suất xuất hiện đồng thời trong các đơn hàng lịch sử: $\text{Confidence}(A \rightarrow B) = \frac{\text{Count}(A \cap B)}{\text{Count}(A)}$. Hệ thống gợi ý tối đa 3 phụ kiện có độ tin cậy cao nhất kèm mức chiết khấu Combo ưu đãi. |
| <a id="br12"></a>**BR12** | Công thức gợi ý cá nhân hóa lai (Hybrid Scoring) | Điểm số gợi ý sản phẩm cho người dùng cụ thể: $\text{Score} = 0.4 \times \text{ContentSim} + 0.4 \times \text{CoOccurrence} + 0.2 \times \text{Popularity}$. Nếu người dùng chưa có lịch sử tương tác (Cold-start), hệ thống tự động fallback gợi ý các sản phẩm có lượt xem (`views`) và đánh giá cao nhất. |
| <a id="br13"></a>**BR13** | Kiểm soát an toàn tệp tải lên (File Upload) | Ảnh sản phẩm tải lên máy chủ bắt buộc phải kiểm tra định dạng MIME thực tế qua `finfo_file` (chỉ chấp nhận `image/jpeg`, `image/png`, `image/webp`), dung lượng tối đa $\le 2\text{MB}$, tự động đổi tên ngẫu nhiên bằng chuỗi băm (hash UUID) để chống ghi đè và tấn công thực thi mã độc. |
| <a id="br14"></a>**BR14** | Nhật ký kiểm toán bất biến (Audit Logging) | Mọi hành động nhạy cảm của Quản trị viên (thêm/sửa/xóa sản phẩm, duyệt thanh toán, thay đổi trạng thái đơn, khóa tài khoản người dùng, tạo mã giảm giá) đều bắt buộc tự động ghi nhận vào bảng `audit_logs` gồm: `admin_id`, `action`, `target_id`, `details`, `ip_address`, `created_at`. Nhật ký kiểm toán cấm sửa hoặc xóa. |
| <a id="br15"></a>**BR15** | Bảo vệ tài khoản Quản trị viên cuối cùng | Hệ thống nghiêm cấm Quản trị viên tự khóa tài khoản của chính mình hoặc vô hiệu hóa tài khoản Admin duy nhất còn lại, nhằm ngăn ngừa triệt để nguy cơ hệ thống bị mất quyền quản trị. |

---

<a id="phan-3"></a>

## 3. Đặc tả Yêu cầu chức năng (Functional Requirements Specification - FR)

Cấu trúc đặc tả tuân thủ chuẩn Kỹ nghệ yêu cầu phần mềm (IEEE 29148 & Karl Wiegers): mỗi nhóm chức năng bao gồm Bảng hồ sơ quản trị & Ma trận truy vết hai chiều (UR, BR, UC, Test Case), kèm Bảng chi tiết các yêu cầu chức năng bắt buộc (`FRnn.xx`) định rõ hành vi hệ thống, dữ liệu đầu vào & ràng buộc, kết quả kỳ vọng và mã phản hồi HTTP kỹ thuật.

### <a id="fr01"></a>FR01 - Định danh, xác thực và quản lý phiên truy cập JWT

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR01** |
| **Tên chức năng** | Quản lý định danh, xác thực và phiên truy cập người dùng (JWT) |
| **Mức độ ưu tiên** | **P1 (Cốt lõi - Bắt buộc)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR01](#ur01)<br>• **Quy tắc nghiệp vụ:** [BR01](#br01), [BR02](#br02), [BR03](#br03)<br>• **Use Case tương ứng:** [UC01](#uc01)<br>• **API Endpoints:** `POST /auth/register`, `POST /auth/login`, `POST /auth/refresh`, `POST /auth/logout`, `GET /auth/me`, `POST /auth/google`<br>• **Ca kiểm thử:** `TC-AUTH-01` đến `TC-AUTH-06` |
| **Mô tả tổng quát** | Hệ thống cung cấp cơ chế đăng ký an toàn, thẩm định danh tính qua email/mật khẩu, cấp phát và thu hồi cặp Token JWT (Access Token & Refresh Token) theo chuẩn RFC 7519, đồng thời đảm bảo an toàn mật khẩu một chiều bằng bcrypt. |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR01.01** | Đăng ký tài khoản mới | Chuẩn hóa email (trim, lowercase), kiểm tra tính duy nhất trong CSDL bảng `users`, băm mật khẩu an toàn bằng `PASSWORD_BCRYPT` và khởi tạo tài khoản với quyền mặc định `role = 'customer'`. | • Họ tên, Email, Mật khẩu, Số điện thoại, Địa chỉ.<br>• Email đúng định dạng regex; Mật khẩu $\ge 6$ ký tự. | • Tạo bản ghi User trạng thái `active` (`201 Created`).<br>• Trùng email: `409 Conflict`.<br>• Dữ liệu thiếu/sai: `422 Unprocessable Entity`. |
| **FR01.02** | Đăng nhập hệ thống | Kiểm tra email và mật khẩu qua `password_verify()`. Kiểm tra tài khoản không bị khóa (`status = 'active'`). Sinh cặp Token: Access Token (hạn 2 giờ) và Refresh Token (hạn 30 ngày) được ký số HMAC-SHA256 theo [BR02](#br02). | • Email, Mật khẩu. | • Lưu Refresh Token vào bảng `refresh_tokens`; trả về cặp Token và thông tin user (`200 OK`).<br>• Sai email hoặc mật khẩu: `401 Unauthorized`.<br>• Tài khoản bị khóa: `403 Forbidden`. |
| **FR01.03** | Làm mới Access Token | Tiếp nhận Refresh Token hợp lệ còn hạn sử dụng; thẩm định chữ ký số và đối chiếu bản ghi trong bảng `refresh_tokens`; cấp phát Access Token mới mà không làm gián đoạn phiên người dùng. | • `refresh_token` trong JSON body. | • Trả về Access Token mới có thời hạn 2 giờ (`200 OK`).<br>• Token hết hạn hoặc không tồn tại trong DB: `401 Unauthorized`. |
| **FR01.04** | Đăng xuất an toàn | Thu hồi phiên làm việc hiện tại, xóa vĩnh viễn Refresh Token tương ứng khỏi bảng `refresh_tokens` trong CSDL để token đó không bao giờ tái sử dụng được nữa. | • Access Token hợp lệ ở Header Authorization.<br>• `refresh_token` cần thu hồi trong body. | • Xóa bản ghi trong `refresh_tokens`; máy khách dọn sạch `localStorage` (`200 OK`). |
| **FR01.05** | Lấy thông tin cá nhân (Profile) | Trích xuất `userId` từ Access Token hợp lệ qua `AuthMiddleware`, truy vấn thông tin người dùng từ bảng `users`; tuyệt đối không để lộ chuỗi băm mật khẩu ra ngoài. | • Header `Authorization: Bearer <access_token>`. | • Trả về object user gồm: `id`, `name`, `email`, `phone`, `address`, `role`, `created_at` (`200 OK`).<br>• Token không hợp lệ: `401 Unauthorized`. |
| **FR01.06** | Đăng nhập Google OAuth | Tiếp nhận Google Credential Token từ máy khách, giải mã lấy email và tên; tự động tạo mới tài khoản nếu lần đầu đăng nhập, hoặc đăng nhập nếu tài khoản đã tồn tại; cấp phát cặp token JWT TechStore. | • `credential` token từ Google Identity Services. | • Cấp phát cặp Token JWT TechStore (`200 OK`). |

---

### <a id="fr02"></a>FR02 - Quản lý Danh mục, Thương hiệu và Chi tiết Sản phẩm

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR02** |
| **Tên chức năng** | Quản lý danh mục, thương hiệu và thông số kỹ thuật sản phẩm |
| **Mức độ ưu tiên** | **P1 (Cốt lõi)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR02](#ur02), [UR04](#ur04)<br>• **Quy tắc nghiệp vụ:** [BR01](#br01), [BR13](#br13)<br>• **Use Case tương ứng:** [UC02](#uc02)<br>• **API Endpoints:** `GET /categories`, `GET /brands`, `GET /products/{id}`<br>• **Ca kiểm thử:** `TC-PROD-01`, `TC-PROD-04` |
| **Mô tả tổng quát** | Cung cấp dữ liệu danh mục phân loại, thương hiệu công nghệ và đặc tả chi tiết cấu hình kỹ thuật của từng sản phẩm (thông số CPU, RAM, ROM, Pin, Màn hình, bộ sưu tập ảnh). |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR02.01** | Lấy danh mục sản phẩm | Trả về danh sách toàn bộ các danh mục thiết bị công nghệ (Điện thoại, Laptop, Tablet, Âm thanh, Đồng hồ, Phụ kiện) kèm biểu tượng và slug phục vụ điều hướng menu navbar. | Không yêu cầu xác thực (Public). | • Trả về mảng danh mục kèm số lượng sản phẩm liên kết (`200 OK`). |
| **FR02.02** | Lấy danh sách thương hiệu | Trả về danh sách các hãng sản xuất thiết bị công nghệ (Apple, Samsung, Xiaomi, ASUS, Dell, Sony...) kèm logo phục vụ bộ lọc thương hiệu. | Không yêu cầu xác thực (Public). | • Trả về mảng thương hiệu (`200 OK`). |
| **FR02.03** | Xem chi tiết thông số kỹ thuật | Trả về toàn diện thông tin sản phẩm: tên, giá niêm yết, giá khuyến mãi, số lượng tồn kho `stock`, mô tả, gallery ảnh lớn/thumbnails từ bảng `product_images`, và bảng thông số kỹ thuật chi tiết từ bảng `product_specs`. | • `id` sản phẩm trên đường dẫn URL. | • Trả về object chi tiết sản phẩm (`200 OK`).<br>• Không tìm thấy sản phẩm: `404 Not Found`. |
| **FR02.04** | Theo dõi lượt xem sản phẩm | Tự động ghi nhận sự kiện mở xem chi tiết sản phẩm vào bảng `product_views` để phục vụ thống kê sản phẩm nổi bật và thuật toán cá nhân hóa theo [BR12](#br12). | • `product_id`, `user_id` (nếu có đăng nhập) hoặc mã phiên `session_id`. | • Ghi nhận lượt xem thành công (`200 OK`). |

---

### <a id="fr03"></a>FR03 - Tìm kiếm Instant Search và Lọc sản phẩm đa tiêu chí

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR03** |
| **Tên chức năng** | Tìm kiếm live search và bộ lọc đa chiều sản phẩm công nghệ |
| **Mức độ ưu tiên** | **P1 (Cốt lõi)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR02](#ur02), [UR03](#ur03)<br>• **Quy tắc nghiệp vụ:** [BR01](#br01)<br>• **Use Case tương ứng:** [UC02](#uc02)<br>• **API Endpoints:** `GET /products`<br>• **Ca kiểm thử:** `TC-PROD-02`, `TC-PROD-03` |
| **Mô tả tổng quát** | Hệ thống cung cấp công cụ tìm kiếm và lọc phân trang sản phẩm linh hoạt; hỗ trợ lọc kết hợp đồng thời danh mục, thương hiệu, khoảng giá, dung lượng RAM, ROM và tìm kiếm từ khóa với kỹ thuật debounce 250ms. |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR03.01** | Lọc sản phẩm đa chiều | Truy vấn danh sách sản phẩm theo kết hợp nhiều tiêu chí: `category_id`, `brand_id`, khoảng giá (`min_price`, `max_price`), `ram`, `rom`. Tự động ghép nối mệnh đề SQL Prepared Statements an toàn. | Query params: `category_id`, `brand_id`, `min_price`, `max_price`, `ram`, `rom`. | • Trả về danh sách sản phẩm thỏa mãn tiêu chí (`200 OK`).<br>• Không có sản phẩm phù hợp: Trả về mảng rỗng `[]` (`200 OK`). |
| **FR03.02** | Sắp xếp sản phẩm linh hoạt | Hỗ trợ sắp xếp danh sách sản phẩm theo các tiêu chí: giá tăng dần (`price_asc`), giá giảm dần (`price_desc`), đánh giá sao cao nhất (`rating_desc`), sản phẩm mới nhất (`newest`). | Query param: `sort` (mặc định `newest`). | • Danh sách sản phẩm được sắp xếp chuẩn xác (`200 OK`). |
| **FR03.03** | Phân trang danh sách sản phẩm | Chia nhỏ kết quả trả về theo trang với 2 tham số `page` (trang hiện tại, mặc định 1) và `limit` (số lượng trên trang, mặc định 12, tối đa 50) để tối ưu băng thông mạng. | Query params: `page` $\ge 1$, `limit` từ 1 đến 50. | • Trả về danh sách kèm metadata phân trang: `{ page, limit, total, total_pages }` (`200 OK`). |
| **FR03.04** | Tìm kiếm Instant Search debounce | Hỗ trợ tìm kiếm theo từ khóa `search`. Trên giao diện máy khách, áp dụng hàm debounce 250ms để chỉ gửi request khi người dùng ngừng gõ phím; tự động hiển thị danh sách gợi ý nhanh drop-down. | Query param: `search` (chuỗi từ khóa). | • Trả về tối đa 6 sản phẩm khớp từ khóa gợi ý tức thì (`200 OK`). |

---

### <a id="fr04"></a>FR04 - Quản lý Giỏ hàng và Kiểm soát Tồn kho

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR04** |
| **Tên chức năng** | Quản lý giỏ hàng phía máy chủ và kiểm soát tồn kho thời gian thực |
| **Mức độ ưu tiên** | **P1 (Cốt lõi)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR05](#ur05)<br>• **Quy tắc nghiệp vụ:** [BR01](#br01), [BR04](#br04)<br>• **Use Case tương ứng:** [UC03](#uc03)<br>• **API Endpoints:** `GET /cart`, `POST /cart/items`, `PUT /cart/items/{id}`, `DELETE /cart/items/{id}`<br>• **Ca kiểm thử:** `TC-CART-01` đến `TC-CART-04` |
| **Mô tả tổng quát** | Hệ thống lưu trữ và đồng bộ giỏ hàng của khách hàng đăng nhập trên máy chủ bảng `carts`; hỗ trợ thêm, sửa số lượng, xóa sản phẩm và tự động kiểm tra số lượng tồn kho `stock` khả dụng. |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR04.01** | Xem giỏ hàng cá nhân | Lấy danh sách sản phẩm trong giỏ của người dùng hiện tại; đối chiếu giá bán mới nhất và tồn kho từ bảng `products`; tự động tính tạm tính (`subtotal`) và tổng số lượng món. | Header `Authorization: Bearer <access_token>`. | • Trả về object giỏ hàng: `{ items, total_items, subtotal }` (`200 OK`). |
| **FR04.02** | Thêm sản phẩm vào giỏ hàng | Thêm sản phẩm với số lượng mong muốn. Nếu sản phẩm đã tồn tại trong giỏ thì tăng số lượng lũy kế. Kiểm tra nghiêm ngặt không cho phép tổng số lượng trong giỏ vượt quá tồn kho `stock` hiện tại. | `product_id`, `quantity` ($> 0$), tùy chọn cấu hình `color`/`ram`. | • Thêm thành công, cập nhật số lượng và trả về giỏ mới (`200 OK`).<br>• Số lượng vượt tồn kho: Từ chối (`400 Bad Request`). |
| **FR04.03** | Cập nhật số lượng món đồ | Tăng hoặc giảm số lượng của một sản phẩm trong giỏ. Nếu số lượng cập nhật về 0 thì tự động xóa sản phẩm đó khỏi giỏ hàng. | `id` mục giỏ hàng trên URL; `quantity` mới $\ge 0$. | • Cập nhật số lượng và tính lại tổng tiền ngay lập tức (`200 OK`).<br>• Vượt tồn kho: `400 Bad Request`. |
| **FR04.04** | Xóa sản phẩm khỏi giỏ hàng | Cho phép xóa một sản phẩm cụ thể khỏi giỏ, hoặc làm trống toàn bộ giỏ hàng của người dùng. | `id` mục giỏ hàng cần xóa. | • Xóa thành công bản ghi khỏi bảng `carts` (`200 OK`). |

---

### <a id="fr05"></a>FR05 - Quản lý và Áp dụng Mã giảm giá Khuyến mãi (Coupon)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR05** |
| **Tên chức năng** | Thẩm định và áp dụng mã giảm giá khuyến mãi (Coupon Engine) |
| **Mức độ ưu tiên** | **P2 (Quan trọng)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR05](#ur05)<br>• **Quy tắc nghiệp vụ:** [BR07](#br07)<br>• **Use Case tương ứng:** [UC03](#uc03)<br>• **API Endpoints:** `POST /coupons/validate`, `GET /coupons`<br>• **Ca kiểm thử:** `TC-CART-05` |
| **Mô tả tổng quát** | Hệ thống cung cấp công cụ kiểm tra tính hợp lệ của mã giảm giá theo [BR07](#br07), tự động tính toán số tiền chiết khấu theo tỷ lệ % hoặc số tiền cố định và khấu trừ vào tổng tiền thanh toán của đơn hàng. |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR05.01** | Lấy danh sách mã khả dụng | Trả về danh sách các mã giảm giá công khai đang có hiệu lực (còn hạn, còn lượt) để hiển thị gợi ý trên trang giỏ hàng. | Không yêu cầu xác thực. | • Trả về mảng các mã coupon khả dụng (`200 OK`). |
| **FR05.02** | Thẩm định & Áp dụng coupon | Kiểm tra mã coupon: tồn tại, trạng thái `active`, ngày hiện tại nằm giữa `start_date` và `end_date`, `usage_limit > used_count`, và giá trị đơn hàng $\ge$ `min_order_value`. Tính số tiền chiết khấu chính xác theo [BR07](#br07). | • `code`: Chuỗi mã coupon (uppercase).<br>• `order_total`: Giá trị tạm tính của giỏ hàng. | • Trả về: `{ valid: true, discount_amount, final_total }` (`200 OK`).<br>• Hết hạn, hết lượt hoặc chưa đạt giá trị tối thiểu: `400 Bad Request` kèm lý do. |

---

### <a id="fr06"></a>FR06 - Đặt hàng, Khấu trừ Tồn kho và Quản lý Đơn hàng (Order)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR06** |
| **Tên chức năng** | Tạo đơn hàng ACID Transaction và quản lý vòng đời đơn hàng |
| **Mức độ ưu tiên** | **P1 (Cốt lõi - Bắt buộc)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR06](#ur06), [UR08](#ur08)<br>• **Quy tắc nghiệp vụ:** [BR01](#br01), [BR04](#br04), [BR08](#br08)<br>• **Use Case tương ứng:** [UC04](#uc04), [UC06](#uc06)<br>• **API Endpoints:** `POST /orders`, `GET /orders`, `GET /orders/{id}`, `POST /orders/{id}/cancel`, `PATCH /orders/{id}/status`<br>• **Ca kiểm thử:** `TC-ORD-01` đến `TC-ORD-04` |
| **Mô tả tổng quát** | Xử lý quy trình Checkout đặt hàng trong một Database Transaction nguyên tử đảm bảo tính toàn vẹn tồn kho theo [BR04](#br04); quản lý vòng đời đơn qua 5 trạng thái (`PENDING` $\rightarrow$ `CONFIRMED` $\rightarrow$ `SHIPPING` $\rightarrow$ `COMPLETED` / `CANCELLED`). |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR06.01** | Tạo đơn hàng mới (Checkout) | Mở PDO Transaction: kiểm tra tồn kho từng sản phẩm trong giỏ $\rightarrow$ trừ số lượng tồn kho `stock` $\rightarrow$ tạo bản ghi `orders` $\rightarrow$ tạo các bản ghi `order_items` $\rightarrow$ cập nhật lượt dùng coupon (nếu có) $\rightarrow$ dọn sạch giỏ hàng $\rightarrow$ Commit Transaction theo [BR04](#br04). | • Thông tin nhận hàng: Họ tên, SĐT, Email, Địa chỉ.<br>• `payment_method`: `COD` hoặc `VIETQR`.<br>• `coupon_code` (tùy chọn). | • Tạo đơn thành công, trả về `order_id` và mã đơn hàng định dạng `ORD-YYYYMMDD-XXX` (`201 Created`).<br>• Bất kỳ sản phẩm nào hết hàng: Rollback và trả `400 Bad Request`. |
| **FR06.02** | Xem lịch sử đơn hàng cá nhân | Truy vấn danh sách toàn bộ các đơn hàng đã đặt của khách hàng đang đăng nhập, sắp xếp theo thời gian mới nhất. | Header `Authorization: Bearer <access_token>`. | • Trả về mảng danh sách đơn hàng kèm trạng thái đơn và thanh toán (`200 OK`). |
| **FR06.03** | Xem chi tiết một đơn hàng | Lấy đầy đủ thông tin đơn hàng: người nhận, địa chỉ, phương thức thanh toán, danh sách sản phẩm (`order_items`), giá mua tại thời điểm đặt, số tiền giảm giá và tổng tiền thanh toán. | `id` đơn hàng trên URL. | • Trả về chi tiết đơn hàng (`200 OK`).<br>• Đơn hàng không thuộc về người dùng: Chặn `403 Forbidden` chống lỗi IDOR. |
| **FR06.04** | Khách hàng hủy đơn hàng | Khách hàng yêu cầu hủy đơn hàng. Hệ thống thẩm tra đơn hàng phải thuộc về người dùng và đang ở trạng thái `PENDING` theo [BR08](#br08). Tự động hoàn lại số lượng tồn kho `stock` cho từng sản phẩm trong đơn. | `id` đơn hàng trên URL. | • Chuyển trạng thái đơn sang `CANCELLED`, hoàn kho thành công (`200 OK`).<br>• Đơn đã duyệt hoặc đang giao: Từ chối hủy (`400 Bad Request`). |
| **FR06.05** | Cập nhật trạng thái đơn (Admin) | Quản trị viên cập nhật trạng thái đơn hàng theo tiến trình xử lý thực tế (`CONFIRMED` $\rightarrow$ `SHIPPING` $\rightarrow$ `COMPLETED`). Tự động ghi nhật ký kiểm toán theo [BR14](#br14). | `id` đơn hàng, `status` mới. Quyền Admin. | • Cập nhật trạng thái thành công (`200 OK`). |

---

### <a id="fr07"></a>FR07 - Tích hợp Cổng thanh toán động VietQR và Webhook

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR07** |
| **Tên chức năng** | Sinh mã VietQR NAPAS247 động, Polling realtime và Webhook |
| **Mức độ ưu tiên** | **P1 (Cốt lõi - Bắt buộc)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR06](#ur06), [UR07](#ur07)<br>• **Quy tắc nghiệp vụ:** [BR05](#br05), [BR06](#br06)<br>• **Use Case tương ứng:** [UC04](#uc04), [UC05](#uc05)<br>• **API Endpoints:** `POST /payments/qr`, `GET /payments/{orderId}/status`, `POST /payments/webhook`<br>• **Ca kiểm thử:** `TC-PAY-01`, `TC-PAY-02`, `TC-PAY-03` |
| **Mô tả tổng quát** | Hiện thực hóa giải pháp thanh toán quét mã VietQR tự động: sinh URL ảnh QR chuẩn NAPAS247 chứa mã đơn và số tiền, cung cấp endpoint polling kiểm tra trạng thái mỗi 3 giây và webhook nhận thông báo tự động có chữ ký HMAC. |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR07.01** | Sinh mã VietQR động | Tạo bản ghi giao dịch thanh toán trong bảng `payments` ở trạng thái `PENDING`. Gọi [QrService.php](../api/services/QrService.php) sinh link ảnh QR NAPAS247 theo cú pháp chuẩn MB Bank chứa số tiền và nội dung `TECHSTORE <ORDER_ID>` theo [BR05](#br05). | `order_id` hợp lệ có phương thức thanh toán `VIETQR`. | • Trả về object thanh toán: `{ qr_url, bank_name, account_no, amount, content, expires_at }` (`200 OK`). |
| **FR07.02** | Polling kiểm tra trạng thái | Cung cấp endpoint cho máy khách gửi truy vấn mỗi 3 giây để kiểm tra trạng thái thanh toán của đơn hàng. Tự động kiểm tra thời hạn 15 phút của mã QR. | `orderId` trên đường dẫn URL. | • Trả về `{ payment_status: 'PENDING' \| 'PAID' \| 'EXPIRED' }` (`200 OK`). |
| **FR07.03** | Tiếp nhận Webhook xác nhận | Tiếp nhận tín hiệu thông báo chuyển khoản thành công từ ngân hàng (giả lập). Kiểm tra chữ ký HMAC-SHA256 bí mật theo [BR06](#br06). Khi hợp lệ, cập nhật đơn hàng sang `CONFIRMED` và thanh toán sang `PAID`, đồng thời ghi nhật ký kiểm toán. | Payload JSON từ ngân hàng kèm Header `X-Webhook-Signature`. | • Cập nhật thanh toán thành công (`200 OK`).<br>• Chữ ký sai hoặc dữ liệu thiếu: Từ chối (`401 Unauthorized`). |
| **FR07.04** | Duyệt thanh toán thủ công (Admin) | Cung cấp nút duyệt nhanh trên giao diện quản trị cho phép Admin xác nhận tiền đã về tài khoản ngân hàng trong trường hợp khách chuyển khoản thành công nhưng webhook bị nghẽn mạng. | `order_id`. Quyền Admin. | • Đổi trạng thái thanh toán sang `PAID` và đơn hàng sang `CONFIRMED` (`200 OK`). Ghi nhật ký kiểm toán. |

---

### <a id="fr08"></a>FR08 - Đánh giá Sản phẩm và Quản lý Danh sách yêu thích (Wishlist)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR08** |
| **Tên chức năng** | Đánh giá sao, bình luận và quản lý danh sách sản phẩm yêu thích |
| **Mức độ ưu tiên** | **P2 (Quan trọng)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR09](#ur09)<br>• **Quy tắc nghiệp vụ:** [BR09](#br09)<br>• **Use Case tương ứng:** [UC07](#uc07), [UC08](#uc08)<br>• **API Endpoints:** `GET /products/{id}/reviews`, `POST /products/{id}/reviews`, `GET /wishlist`, `POST /wishlist`, `DELETE /wishlist/{id}`<br>• **Ca kiểm thử:** `TC-REC-01`, `TC-REC-02`, `TC-REC-03` |
| **Mô tả tổng quát** | Cho phép khách hàng đã mua sản phẩm gửi đánh giá sao (1-5 sao) kèm bình luận để xây dựng uy tín cộng đồng; hỗ trợ khách hàng lưu trữ các thiết bị yêu thích vào danh sách Wishlist để xem lại và mua sau. |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR08.01** | Xem đánh giá sản phẩm | Lấy danh sách toàn bộ các đánh giá của sản phẩm, điểm rating trung bình, tỷ lệ phần trăm phân bố sao (5 sao, 4 sao...) và nội dung nhận xét của khách hàng. | `id` sản phẩm trên URL. Public. | • Trả về danh sách đánh giá kèm metadata thống kê sao (`200 OK`). |
| **FR08.02** | Gửi đánh giá sản phẩm đã mua | Thẩm định khách hàng đã mua sản phẩm và đơn hàng ở trạng thái `COMPLETED` theo [BR09](#br09). Kiểm tra chưa từng đánh giá sản phẩm này trước đó. Lưu đánh giá và cập nhật lại điểm rating trung bình của sản phẩm. | `rating` (số nguyên từ 1 đến 5), `comment` (tối đa 1000 ký tự). Token khách hàng. | • Lưu đánh giá thành công (`201 Created`).<br>• Chưa mua hàng hoặc đơn chưa hoàn tất: Từ chối (`403 Forbidden`).<br>• Đã đánh giá rồi: `409 Conflict`. |
| **FR08.03** | Quản lý danh sách Wishlist | Thêm sản phẩm vào danh sách yêu thích, xem toàn bộ sản phẩm yêu thích và xóa sản phẩm khỏi danh sách yêu thích cá nhân. | `product_id`. Token khách hàng. | • Thêm/Xóa/Xem thành công (`200 OK` / `201 Created`).<br>• Trùng sản phẩm trong Wishlist: Bỏ qua an toàn. |

---

### <a id="fr09"></a>FR09 - Thuật toán Gợi ý Sản phẩm Thông minh (Recommendation)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR09** |
| **Tên chức năng** | Động cơ gợi ý sản phẩm thông minh (Cosine Similarity & Co-occurrence) |
| **Mức độ ưu tiên** | **P1 (Cốt lõi - Điểm nhấn kỹ thuật đồ án)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR04](#ur04)<br>• **Quy tắc nghiệp vụ:** [BR10](#br10), [BR11](#br11), [BR12](#br12)<br>• **Use Case tương ứng:** [UC09](#uc09)<br>• **API Endpoints:** `POST /events/view`, `GET /products/{id}/recommendations`, `GET /recommendations/me`<br>• **Ca kiểm thử:** `TC-REC-04`, `TC-REC-05`, `TC-REC-06` |
| **Mô tả tổng quát** | Hiện thực hóa động cơ gợi ý sản phẩm lai (Hybrid Recommendation): gợi ý phụ kiện mua kèm Combo (Frequently Bought Together) từ lịch sử đơn hàng, gợi ý thiết bị tương đồng cấu hình (Content-based Cosine) và cá nhân hóa danh sách "Dành cho bạn". |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR09.01** | Gợi ý mua kèm Combo (FBT) | Áp dụng thuật toán khai phá đồng xuất hiện (Co-occurrence) trên bảng `order_items` theo [BR11](#br11) để tìm các sản phẩm (tai nghe, củ sạc, ốp lưng) thường được mua cùng sản phẩm hiện tại; trả về kèm % chiết khấu combo. | `id` sản phẩm trên URL. | • Trả về tối đa 3 sản phẩm mua kèm có độ tin cậy $\text{Confidence}$ cao nhất (`200 OK`). |
| **FR09.02** | Gợi ý sản phẩm tương tự (Content-based) | Truy vấn bảng `product_similarity` tìm các sản phẩm cùng danh mục/hãng có độ tương đồng Cosine cao nhất về cấu hình kỹ thuật theo [BR10](#br10); trả về danh sách hiển thị carousel "Sản phẩm tương tự". | `id` sản phẩm trên URL. | • Trả về tối đa 6 sản phẩm tương tự có điểm $\text{Sim} \ge 0.6$ (`200 OK`). |
| **FR09.03** | Gợi ý cá nhân hóa (Personalized / Me) | Tính toán điểm số lai $\text{Score} = 0.4 \times \text{Content} + 0.4 \times \text{CoOccur} + 0.2 \times \text{Views}$ dựa trên lịch sử xem hàng (`product_views`) và lịch sử mua hàng của người dùng theo [BR12](#br12). | Header Authorization token của khách hàng. | • Trả về danh sách sản phẩm cá nhân hóa trên trang chủ (`200 OK`).<br>• User mới (Cold-start): Tự động fallback sang các sản phẩm xem nhiều nhất. |
| **FR09.04** | Script CLI tính toán tương đồng nền | Cung cấp script dòng lệnh `scripts/build_similarity.php` (tác vụ SYS02) quét toàn bộ CSDL sản phẩm, trích xuất vector đặc trưng 5 chiều, tính toán Cosine Similarity và lưu trước vào bảng `product_similarity` để tối ưu thời gian phản hồi API $\le 15\text{ms}$. | Chạy qua lệnh CLI: `php scripts/build_similarity.php`. | • Cập nhật ma trận tương đồng $N \times N$ trong CSDL với mã thoát exit code 0. |

---

### <a id="fr10"></a>FR10 - Phân hệ Quản trị Hệ thống, Thống kê và Nhật ký Kiểm toán (Admin)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm FR** | **FR10** |
| **Tên chức năng** | Quản trị sản phẩm, đơn hàng, người dùng, thống kê và nhật ký kiểm toán |
| **Mức độ ưu tiên** | **P1 (Cốt lõi)** |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR10](#ur10), [UR11](#ur11), [UR12](#ur12)<br>• **Quy tắc nghiệp vụ:** [BR03](#br03), [BR13](#br13), [BR14](#br14), [BR15](#br15)<br>• **Use Case tương ứng:** [UC10](#uc10), [UC11](#uc11), [UC12](#uc12), [UC13](#uc13), [UC14](#uc14)<br>• **API Endpoints:** `GET /admin/stats`, `POST/PUT/DELETE /products`, `CRUD /categories`, `CRUD /brands`, `GET/PATCH /admin/users`, `CRUD /coupons`, `GET /admin/audit-logs`<br>• **Ca kiểm thử:** `TC-ADM-01` đến `TC-ADM-09` |
| **Mô tả tổng quát** | Cung cấp cổng quản trị toàn diện cho Admin: xem bảng điều khiển thống kê doanh thu 7 ngày, quản trị CRUD danh mục/thương hiệu/sản phẩm kèm upload ảnh an toàn, quản lý đơn hàng & in hóa đơn, khóa/mở tài khoản người dùng và tra cứu nhật ký kiểm toán. |

**Chi tiết các yêu cầu chức năng bắt buộc:**

| Mã ID | Tên chức năng con | Mô tả hành vi hệ thống | Dữ liệu đầu vào & Ràng buộc | Kết quả kỳ vọng & Mã phản hồi |
| :---: | :--- | :--- | :--- | :--- |
| **FR10.01** | Dashboard thống kê doanh thu | Thống kê tổng doanh thu, doanh thu tháng này, số lượng đơn hàng mới, số sản phẩm sắp hết hàng (`stock < 5`), tổng người dùng và dữ liệu biểu đồ doanh thu 7 ngày gần nhất. | Yêu cầu xác thực quyền `role = 'admin'`. | • Trả về gói dữ liệu thống kê Dashboard hoàn chỉnh (`200 OK`).<br>• Không phải admin: Chặn `403 Forbidden`. |
| **FR10.02** | Quản trị CRUD Sản phẩm | Thêm mới, chỉnh sửa thông tin, giá bán, cấu hình và xóa mềm sản phẩm. Hỗ trợ upload ảnh kiểm tra định dạng MIME thực tế (`image/jpeg, png, webp`), dung lượng $\le 2\text{MB}$ và đổi tên ngẫu nhiên theo [BR13](#br13). | Thông tin sản phẩm, thông số kỹ thuật, tệp ảnh upload. Quyền Admin. | • Tạo/Sửa/Xóa thành công (`200 OK` / `201 Created`). Ghi nhật ký kiểm toán theo [BR14](#br14).<br>• Ảnh sai định dạng hoặc vượt quá 2MB: Từ chối (`400 Bad Request`). |
| **FR10.03** | Quản trị Danh mục & Hãng | Thêm, sửa, xóa danh mục và thương hiệu thiết bị. Kiểm tra ràng buộc khóa ngoại không cho phép xóa danh mục hoặc hãng đang có sản phẩm liên kết. | Tên danh mục/hãng, slug, logo/icon. Quyền Admin. | • Thao tác thành công (`200 OK`).<br>• Xóa danh mục đang có sản phẩm: Chặn lỗi an toàn `409 Conflict`. |
| **FR10.04** | In hóa đơn bán lẻ | Cung cấp giao diện in hóa đơn bán hàng chuyên nghiệp (Printable Invoice Layout), định dạng CSS `@media print` tối ưu trang in A4/A5 để bàn giao hàng cho khách. | `id` đơn hàng. Giao diện Admin. | • Mở hộp thoại `window.print()` với bố cục hóa đơn sắc nét. |
| **FR10.05** | Quản lý Người dùng & Phân quyền | Xem danh sách người dùng phân trang; khóa (`status = 'locked'`) hoặc mở khóa tài khoản; thăng quyền `admin` hoặc hạ quyền `customer`. Tuân thủ [BR15](#br15) không cho phép Admin tự khóa chính mình. | `id` người dùng, `status` mới, `role` mới. Quyền Admin. | • Cập nhật thành công (`200 OK`). Ghi nhật ký kiểm toán.<br>• Tự khóa chính mình hoặc khóa admin cuối cùng: Chặn `403 Forbidden`. |
| **FR10.06** | Quản trị Mã giảm giá Coupon | Tạo mới mã giảm giá (loại %, số tiền cố định, giá trị đơn tối thiểu, ngày bắt đầu/kết thúc, giới hạn lượt dùng); xem danh sách và xóa mã coupon. | Thông tin cấu hình coupon. Quyền Admin. | • Tạo/Xóa coupon thành công (`200 OK` / `201 Created`).<br>• Trùng mã coupon: `409 Conflict`. |
| **FR10.07** | Tra cứu Nhật ký kiểm toán | Hiển thị danh sách lịch sử các thao tác của quản trị viên được ghi nhận tự động trong bảng `audit_logs` (thời điểm, admin thực hiện, hành động, ID đối tượng, chi tiết thay đổi) theo [BR14](#br14). | Quyền Admin. Tham số lọc theo ngày/hành động. | • Trả về danh sách nhật ký kiểm toán phân trang (`200 OK`). Bảng dữ liệu cấm sửa/xóa. |

---

<a id="phan-4"></a>

## 4. Mô hình tương tác, Use Cases và Kịch bản xử lý nền

### 4.1 Danh mục Use Case theo tác nhân (14 Core Use Cases)

```
                               ┌────────────────────────────────────────────────────────┐
                               │                    HỆ THỐNG TECHSTORE                  │
                               │                                                        │
                               │  [Phân hệ 1: Xác thực & Khách hàng]                    │
                               │  ├── UC01: Đăng ký & Đăng nhập xác thực JWT            │
                               │  └── UC02: Duyệt danh mục & Lọc sản phẩm đa tiêu chí   │
                               │                                                        │
                               │  [Phân hệ 2: Mua sắm, Giỏ hàng & Khuyến mãi]           │
                               │  ├── UC03: Quản lý giỏ hàng & Áp dụng coupon           │
                               │  ├── UC04: Đặt hàng Checkout & Sinh mã VietQR          │
                               │  └── UC05: Theo dõi trạng thái thanh toán VietQR       │
                               │                                                        │
      ┌──────────────────┐     │  [Phân hệ 3: Lịch sử Đơn & Tương tác]                  │
      │                  │     │  ├── UC06: Quản lý lịch sử đơn hàng & Hủy đơn          │
      │                  ├────►│  ├── UC07: Gửi đánh giá sao & Bình luận sản phẩm       │
      │  A01: Khách hàng │     │  ├── UC08: Quản lý danh sách yêu thích Wishlist        │
      │  (Guest/Customer)│     │  └── UC09: Xem gợi ý sản phẩm Combo & Tương tự         │
      │                  │     └────────────────────────────────────────────────────────┘
      │                  │     ┌────────────────────────────────────────────────────────┐
      │                  │     │  [Phân hệ 4: Quản trị Hệ thống (Admin Portal)]         │
      └──────────────────┘     │  ├── UC10: Quản trị CRUD Sản phẩm & Tồn kho            │
                               │  ├── UC11: Quản trị Đơn hàng & Duyệt thanh toán        │
      ┌──────────────────┐     │  ├── UC12: Xem Dashboard thống kê Doanh thu 7 ngày     │
      │ A02: Quản trị    ├────►│  ├── UC13: Quản lý Tài khoản người dùng & Phân quyền   │
      │      viên (Admin)│     │  └── UC14: Quản trị Mã giảm giá Coupon & Audit Logs    │
      └──────────────────┘     └────────────────────────────────────────────────────────┘
```

#### Bảng tổng hợp Danh mục 14 Use Cases theo phân hệ

| Mã UC | Tên Use Case | Tác nhân chính | Phân hệ chức năng | Mô tả tóm tắt nghiệp vụ | Liên kết đặc tả & Yêu cầu |
| :---: | :--- | :--- | :--- | :--- | :--- |
| **UC01** | Đăng ký & Đăng nhập xác thực JWT | Guest, Customer, Admin | Phân hệ Xác thực | Đăng ký tài khoản, đăng nhập cấp cặp Token JWT (Access Token & Refresh Token), tự động refresh và đăng xuất an toàn | [Chi tiết luồng 4.2](#uc01) / [FR01](#fr01) |
| <a id="uc02"></a>**UC02** | Duyệt danh mục & Lọc sản phẩm đa tiêu chí | Guest, Customer | Phân hệ Sản phẩm | Xem danh sách thiết bị, tìm kiếm instant search (debounce 300ms), lọc theo hãng, khoảng giá, sắp xếp | [FR02](#fr02), [FR03](#fr03) |
| <a id="uc03"></a>**UC03** | Quản lý giỏ hàng & Áp dụng coupon | Customer | Phân hệ Giỏ hàng | Thêm/sửa số lượng/xóa sản phẩm trong giỏ, kiểm tra tồn kho realtime, nhập và áp dụng mã giảm giá | [FR04](#fr04), [FR05](#fr05) |
| **UC04** | Đặt hàng Checkout & Sinh mã VietQR | Customer | Phân hệ Đơn hàng & Thanh toán | Chọn địa chỉ, chọn phương thức COD/VietQR, kiểm tra tồn kho ACID, tạo đơn hàng và sinh ảnh VietQR | [Chi tiết luồng 4.2](#uc04) / [FR06](#fr06), [FR07](#fr07) |
| **UC05** | Theo dõi trạng thái thanh toán VietQR | Customer, Ngân hàng | Phân hệ Đơn hàng & Thanh toán | Polling trạng thái mỗi 3 giây, tiếp nhận Webhook ngân hàng kèm chữ ký HMAC, đếm ngược 15 phút | [Chi tiết luồng 4.2](#uc05) / [FR07](#fr07) |
| <a id="uc06"></a>**UC06** | Quản lý lịch sử đơn hàng & Hủy đơn | Customer | Phân hệ Đơn hàng | Xem danh sách đơn theo trạng thái, xem chi tiết sản phẩm đã mua, hủy đơn hàng khi còn PENDING hoàn kho | [FR06](#fr06) |
| <a id="uc07"></a>**UC07** | Gửi đánh giá sao & Bình luận sản phẩm | Customer (Đã mua hàng) | Phân hệ Tương tác | Chấm điểm 1 đến 5 sao, viết nhận xét sản phẩm khi đơn hàng đã COMPLETED, hiển thị điểm TB | [FR08](#fr08) |
| <a id="uc08"></a>**UC08** | Quản lý danh sách yêu thích Wishlist | Customer | Phân hệ Cá nhân hóa | Thêm/xóa sản phẩm yêu thích (tim đỏ), đồng bộ CSDL, quản lý danh sách thiết bị quan tâm | [FR08](#fr08) |
| **UC09** | Gợi ý sản phẩm thông minh (Recommendation) | Guest, Customer | Phân hệ Gợi ý AI | Gợi ý sản phẩm tương tự (Cosine 5D), phụ kiện mua kèm (FBT Co-occurrence), chấm điểm Hybrid | [Chi tiết luồng 4.2](#uc09) / [FR09](#fr09) |
| **UC10** | Quản trị CRUD Sản phẩm & Tồn kho | Admin | Quản trị Hệ thống | Thêm, sửa, xóa sản phẩm, tải ảnh an toàn qua finfo_file, cập nhật thông số RAM/ROM, kiểm soát tồn kho | [Chi tiết luồng 4.2](#uc10) / [FR10](#fr10) |
| <a id="uc11"></a>**UC11** | Quản trị Đơn hàng & Duyệt thanh toán | Admin | Quản trị Hệ thống | Xem danh sách đơn hàng, cập nhật tiến độ (CONFIRMED, SHIPPING, COMPLETED), duyệt thanh toán VietQR | [FR06](#fr06), [FR07](#fr07) |
| <a id="uc12"></a>**UC12** | Xem Dashboard thống kê Doanh thu 7 ngày | Admin | Quản trị Hệ thống | Theo dõi tổng doanh thu, biểu đồ 7 ngày gần nhất, số đơn mới, cảnh báo tồn kho thấp (`stock < 5`) | [FR10](#fr10) |
| <a id="uc13"></a>**UC13** | Quản lý Tài khoản người dùng & Phân quyền | Admin | Quản trị Hệ thống | Xem danh sách người dùng, khóa/mở khóa tài khoản (RBAC), bảo vệ không tự khóa tài khoản Admin cuối | [FR10](#fr10) |
| <a id="uc14"></a>**UC14** | Quản trị Mã giảm giá Coupon & Audit Logs | Admin | Quản trị Hệ thống | Tạo/sửa/xóa mã coupon khuyến mãi, tra cứu nhật ký kiểm toán bất biến bảo mật hệ thống | [FR05](#fr05), [FR10](#fr10) |

---

### 4.2 Đặc tả chi tiết các Use Case cốt lõi

---

#### <a id="uc01"></a>UC01 - Đăng ký & Đăng nhập xác thực JWT

##### 1. Thông tin chung (Summary)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã Use Case (ID)** | **UC01** |
| **Tên Use Case** | **Đăng ký & Đăng nhập xác thực JWT** |
| **Tác nhân (Actors)** | • **Tác nhân chính:** Khách vãng lai (`Guest`), Khách hàng (`Customer`)<br>• **Tác nhân phụ:** Quản trị viên (`Admin`) |
| **Mục đích (Description)** | Cho phép người dùng đăng ký tài khoản mới; đăng nhập nhận cặp token JWT an toàn (Access Token hạn 2 giờ, Refresh Token hạn 30 ngày); tự động làm mới phiên khi hết hạn và đăng xuất an toàn. |
| **Sự kiện kích hoạt (Trigger)** | Người dùng chọn "Đăng ký" hoặc "Đăng nhập" từ thanh Navbar, hoặc thao tác tự động refresh token trong nền của module `api.js`. |
| **Tiền điều kiện (Pre-condition)** | • **Đăng ký:** Email chưa từng tồn tại trong hệ thống.<br>• **Đăng nhập:** Tài khoản đã tồn tại và ở trạng thái `status = 'active'`.<br>• **Làm mới token:** Có Refresh Token hợp lệ còn hạn trong CSDL. |
| **Hậu điều kiện (Post-condition)** | • Cấp phát phiên làm việc an toàn qua Access Token Bearer.<br>• Refresh Token được lưu trong bảng `refresh_tokens`.<br>• Thu hồi token trong CSDL khi đăng xuất. |
| **Ma trận truy vết** | • **Yêu cầu chức năng:** [FR01](#fr01)<br>• **Yêu cầu người dùng:** [UR01](#ur01)<br>• **Quy tắc nghiệp vụ:** [BR01](#br01), [BR02](#br02), [BR03](#br03)<br>• **Ca kiểm thử:** `TC-AUTH-01` đến `TC-AUTH-06` |

##### 2. Các luồng sự kiện (Flow of Events)

**a. Luồng cơ bản (Basic Flow / Happy Path - Đăng nhập hệ thống):**

| Bước | Hành động của Tác nhân (Actor Action) | Phản hồi của Hệ thống (System Response) |
| :---: | :--- | :--- |
| **1** | Người dùng truy cập trang Đăng nhập ([public/pages/login.html](../public/pages/login.html)). | Hệ thống hiển thị biểu mẫu đăng nhập gồm ô nhập Email và Mật khẩu. |
| **2** | Người dùng nhập Email, Mật khẩu và nhấn nút "Đăng nhập". | Hệ thống kiểm tra dữ liệu đầu vào: email đúng định dạng, mật khẩu không rỗng. |
| **3** | — | Hệ thống gửi request `POST /api/v1/auth/login`. [AuthController](../api/controllers/AuthController.php) truy vấn bảng `users` tìm bản ghi theo email. |
| **4** | — | Hệ thống kiểm tra mật khẩu qua `password_verify()` đối chiếu với chuỗi hash bcrypt lưu trong CSDL. |
| **5** | — | Hệ thống gọi [JwtService.php](../api/services/JwtService.php) sinh cặp token: Access Token (hạn 2 giờ) và Refresh Token (hạn 30 ngày) được ký bằng khóa bí mật `JWT_SECRET` theo [BR02](#br02). |
| **6** | — | Hệ thống lưu Refresh Token vào bảng `refresh_tokens` và trả về JSON: `{ success: true, data: { access_token, refresh_token, user } }` kèm mã HTTP `200 OK`. |
| **7** | — | Module `api.js` lưu token vào `localStorage`, cập nhật thanh Header hiển thị tên người dùng và chuyển hướng về trang chủ hoặc trang giỏ hàng. |

**b. Luồng thay thế (Alternative Flows):**

| Mã luồng | Điều kiện kích hoạt & Hướng rẽ | Hành vi xử lý của Hệ thống |
| :---: | :--- | :--- |
| **AF01.01** | **Đăng ký tài khoản mới:**<br>Khách hàng chọn "Đăng ký", điền Họ tên, Email, Mật khẩu, Số điện thoại và nhấn "Đăng ký". | Hệ thống kiểm tra email chưa tồn tại; băm mật khẩu bằng `PASSWORD_BCRYPT`; tạo bản ghi User mới với `role = 'customer'`; trả về `201 Created` và chuyển sang màn hình Đăng nhập. |
| **AF01.02** | **Tự động làm mới Access Token (Silent Refresh):**<br>Khi người dùng đang thao tác và Access Token hết hạn, API trả về `401 Unauthorized`. | Module `api.js` tự động chặn lại, gửi `POST /api/v1/auth/refresh` kèm `refresh_token`; [AuthController](../api/controllers/AuthController.php) cấp Access Token mới; `api.js` tự động gửi lại request ban đầu mà người dùng không nhận thấy gián đoạn. |
| **AF01.03** | **Đăng xuất khỏi hệ thống:**<br>Người dùng nhấn nút "Đăng xuất" trên thanh điều hướng Navbar. | Hệ thống gửi `POST /api/v1/auth/logout`; xóa Refresh Token trong bảng `refresh_tokens`; dọn sạch `localStorage` và chuyển về trang đăng nhập. |

**c. Luồng ngoại lệ (Exception Flows):**

| Mã lỗi | Tình huống phát sinh lỗi | Hành vi & Phản hồi của Hệ thống |
| :---: | :--- | :--- |
| **EF01.01** | Đăng ký nhưng email đã tồn tại trong CSDL. | Hệ thống từ chối đăng ký, trả về mã lỗi `409 Conflict` kèm thông báo: `"Email này đã được sử dụng. Vui lòng đăng nhập hoặc sử dụng email khác"`. |
| **EF01.02** | Đăng nhập sai email hoặc sai mật khẩu. | Hệ thống trả về `401 Unauthorized` kèm thông điệp chung: `"Email hoặc mật khẩu không chính xác"` nhằm chống tấn công dò quét người dùng (User Enumeration). |
| **EF01.03** | Tài khoản đang bị quản trị viên khóa (`status = 'locked'`). | Hệ thống từ chối cấp token, trả về mã lỗi `403 Forbidden` kèm thông báo: `"Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên"`. |

##### 3. Ràng buộc bổ sung (Additional Info)
- **Quy tắc nghiệp vụ:** Tuân thủ [BR01](#br01) (Stateless qua JWT), [BR02](#br02) (Token pair), [BR03](#br03) (RBAC phân quyền).
- **Yêu cầu phi chức năng:** Phản hồi đăng nhập $\le 150\text{ms}$ ([NFR01](#nfr01)); băm mật khẩu `bcrypt` cost factor $\ge 10$ ([NFR02](#nfr02)).

---

#### <a id="uc04"></a><a id="uc05"></a>UC04 & UC05 - Đặt hàng Checkout & Thanh toán động qua VietQR

##### 1. Thông tin chung (Summary)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã Use Case (ID)** | **UC04 & UC05** |
| **Tên Use Case** | **Đặt hàng Checkout & Thanh toán động qua VietQR** |
| **Tác nhân (Actors)** | • **Tác nhân chính:** Khách hàng (`Customer`)<br>• **Tác nhân phụ:** Cổng thanh toán VietQR / Ngân hàng (`BankGateway`), Quản trị viên (`Admin`) |
| **Mục đích (Description)** | Cho phép khách hàng đặt hàng từ giỏ hàng trong một Database Transaction nguyên tử trừ tồn kho an toàn; sinh mã thanh toán VietQR NAPAS247 động; tự động polling trạng thái mỗi 3 giây và chuyển màn hình khi thanh toán thành công. |
| **Sự kiện kích hoạt (Trigger)** | Khách hàng nhấn nút "Tiến hành đặt hàng" từ giỏ hàng và hoàn tất xác nhận tại màn hình Checkout. |
| **Tiền điều kiện (Pre-condition)** | Khách hàng đã đăng nhập; giỏ hàng có ít nhất 1 sản phẩm; tất cả sản phẩm đều còn đủ số lượng tồn kho `stock`. |
| **Hậu điều kiện (Post-condition)** | • Bản ghi `orders` và `order_items` được tạo trong CSDL.<br>• Số lượng tồn kho `stock` của các sản phẩm bị trừ chính xác.<br>• Giỏ hàng trên server được dọn sạch.<br>• Sinh bản ghi `payments` kèm ảnh VietQR và kích hoạt đồng hồ đếm ngược 15 phút. |
| **Ma trận truy vết** | • **Yêu cầu chức năng:** [FR06](#fr06), [FR07](#fr07)<br>• **Yêu cầu người dùng:** [UR06](#ur06), [UR07](#ur07)<br>• **Quy tắc nghiệp vụ:** [BR04](#br04), [BR05](#br05), [BR06](#br06)<br>• **Ca kiểm thử:** `TC-ORD-01`, `TC-PAY-01`, `TC-PAY-02`, `TC-PAY-03` |

##### 2. Các luồng sự kiện (Flow of Events)

**a. Luồng cơ bản (Basic Flow / Happy Path - Thanh toán VietQR):**

| Bước | Hành động của Tác nhân (Actor Action) | Phản hồi của Hệ thống (System Response) |
| :---: | :--- | :--- |
| **1** | Khách hàng tại giỏ hàng ([cart.html](../public/pages/cart.html)) nhấn "Tiến hành đặt hàng". | Hệ thống chuyển hướng sang màn hình Checkout ([checkout.html](../public/pages/checkout.html)), tự động điền thông tin cá nhân của khách hàng. |
| **2** | Khách hàng kiểm tra địa chỉ giao hàng, chọn phương thức thanh toán "Chuyển khoản VietQR" và nhấn "Xác nhận đặt hàng". | Hệ thống kiểm tra dữ liệu: họ tên, SĐT, địa chỉ nhận hàng không để trống. |
| **3** | — | Hệ thống gửi request `POST /api/v1/orders`. [OrderController](../api/controllers/OrderController.php) mở **PDO Database Transaction** theo [BR04](#br04). |
| **4** | — | Hệ thống kiểm tra lại tồn kho `stock` của từng sản phẩm trong giỏ: thực hiện trừ số lượng tồn kho, tạo bản ghi đơn hàng trong bảng `orders` (trạng thái `PENDING`), tạo các dòng chi tiết trong `order_items` và xóa sạch giỏ hàng trong bảng `carts`. |
| **5** | — | Hệ thống commit transaction an toàn và trả về `{ success: true, data: { order_id: 12, order_code: "ORD-20261021-012", total: 18490000 } }` kèm mã `201 Created`. |
| **6** | — | Giao diện tự động gọi tiếp `POST /api/v1/payments/qr`. [PaymentController](../api/controllers/PaymentController.php) sinh bản ghi thanh toán, gọi [QrService.php](../api/services/QrService.php) tạo link ảnh VietQR chứa cú pháp `TECHSTORE ORD-20261021-012` và số tiền `18490000`. |
| **7** | — | Trình duyệt chuyển sang trang thanh toán QR ([payment-qr.html](../public/pages/payment-qr.html)), hiển thị mã QR lớn, kích hoạt đồng hồ đếm ngược 15 phút và bật vòng lặp polling `GET /api/v1/payments/12/status` mỗi 3 giây. |
| **8** | Khách hàng mở app ngân hàng trên điện thoại, quét mã QR và xác nhận chuyển tiền. | Tiền chuyển vào tài khoản ngân hàng của TechStore. |
| **9** | *(Sự kiện Webhook hoặc Admin duyệt)* Ngân hàng bắn tín hiệu `POST /api/v1/payments/webhook` có chữ ký HMAC hợp lệ theo [BR06](#br06). | Hệ thống cập nhật bảng `payments` sang `PAID` và bảng `orders` sang `CONFIRMED`. Ghi nhật ký kiểm toán. |
| **10** | Lần polling kế tiếp (sau tối đa 3s) nhận được trạng thái `{ payment_status: 'PAID' }`. | Màn hình lập tức đổi sang giao diện màu xanh chúc mừng "Thanh toán thành công!", phát hiệu ứng âm thanh/hình ảnh và điều hướng sang trang chi tiết đơn hàng sau 2 giây. |

**b. Luồng thay thế (Alternative Flows):**

| Mã luồng | Điều kiện kích hoạt & Hướng rẽ | Hành vi xử lý của Hệ thống |
| :---: | :--- | :--- |
| **AF04.01** | **Khách hàng chọn thanh toán khi nhận hàng (COD):**<br>Tại màn hình Checkout, khách hàng chọn phương thức "Thanh toán tiền mặt khi nhận hàng (COD)". | Hệ thống tạo đơn hàng trong transaction trừ kho bình thường, đặt trạng thái thanh toán là `UNPAID`, bỏ qua bước sinh QR và chuyển thẳng sang màn hình thông báo "Đặt hàng thành công". |
| **AF04.02** | **Quản trị viên duyệt thanh toán thủ công:**<br>Khách đã chuyển khoản thành công nhưng webhook ngân hàng bị trễ do mạng. | Quản trị viên tại trang [admin/orders.html](../public/admin/orders.html) nhấn nút "Duyệt TT"; hệ thống cập nhật thanh toán `PAID` theo [FR07.04](#fr07); polling phía khách hàng lập tức phát hiện và chuyển trạng thái thành công. |

**c. Luồng ngoại lệ (Exception Flows):**

| Mã lỗi | Tình huống phát sinh lỗi | Hành vi & Phản hồi của Hệ thống |
| :---: | :--- | :--- |
| **EF04.01** | Sản phẩm trong giỏ bị khách khác mua hết tồn kho ngay trước lúc bấm đặt hàng. | PDO Transaction tự động Rollback $100\%$; không trừ kho; không tạo đơn mồ côi; trả về mã `400 Bad Request` kèm thông báo: `"Sản phẩm [Tên SP] đã hết hàng trong kho. Vui lòng cập nhật giỏ hàng"`. |
| **EF04.02** | Hết thời hạn đếm ngược 15 phút mà khách chưa thanh toán. | Bộ đếm ngược về 00:00; polling trả về `EXPIRED`; màn hình hiển thị thông báo "Mã thanh toán đã hết hạn" kèm nút "Tạo lại mã thanh toán" hoặc "Hủy đơn hàng". |

##### 3. Ràng buộc bổ sung (Additional Info)
- **Quy tắc nghiệp vụ:** Bắt buộc tuân thủ tính nguyên tử [BR04](#br04) (ACID Transaction), [BR05](#br05) (VietQR 15 phút), [BR06](#br06) (Polling 3s & HMAC Webhook).
- **Yêu cầu phi chức năng:** Thời gian phản hồi tạo đơn $\le 200\text{ms}$; polling nhẹ tải $\le 50\text{ms}$ ([NFR01](#nfr01)).

---

#### <a id="uc09"></a>UC09 - Gợi ý sản phẩm thông minh (Recommendation Engine)

##### 1. Thông tin chung (Summary)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã Use Case (ID)** | **UC09** |
| **Tên Use Case** | **Xem gợi ý sản phẩm Combo & Tương tự** |
| **Tác nhân (Actors)** | • **Tác nhân chính:** Khách vãng lai (`Guest`), Khách hàng (`Customer`) |
| **Mục đích (Description)** | Hiển thị các sản phẩm gợi ý thông minh dựa trên kết hợp thuật toán khai phá giỏ hàng Co-occurrence (FBT) và thuật toán lọc dựa trên nội dung Content-based Cosine Similarity; nâng cao trải nghiệm mua sắm và gia tăng chỉ số AOV. |
| **Sự kiện kích hoạt (Trigger)** | Người dùng mở xem trang chi tiết sản phẩm ([product-detail.html](../public/pages/product-detail.html)) hoặc cuộn trang chủ xem mục "Dành cho bạn". |
| **Tiền điều kiện (Pre-condition)** | Hệ thống đã có dữ liệu sản phẩm trong CSDL và đã chạy script khởi tạo ma trận tương đồng `scripts/build_similarity.php`. |
| **Hậu điều kiện (Post-condition)** | Hiển thị carousel các sản phẩm tương tự và box combo mua kèm phụ kiện với mức chiết khấu ưu đãi. |
| **Ma trận truy vết** | • **Yêu cầu chức năng:** [FR09](#fr09)<br>• **Yêu cầu người dùng:** [UR04](#ur04)<br>• **Quy tắc nghiệp vụ:** [BR10](#br10), [BR11](#br11), [BR12](#br12)<br>• **Ca kiểm thử:** `TC-REC-04`, `TC-REC-05`, `TC-REC-06` |

##### 2. Các luồng sự kiện (Flow of Events)

**a. Luồng cơ bản (Basic Flow / Happy Path):**

| Bước | Hành động của Tác nhân (Actor Action) | Phản hồi của Hệ thống (System Response) |
| :---: | :--- | :--- |
| **1** | Khách hàng nhấn vào xem sản phẩm iPhone 15 Pro Max tại trang danh sách. | Trình duyệt mở trang chi tiết sản phẩm ([product-detail.html](../public/pages/product-detail.html)). |
| **2** | — | Giao diện tự động gửi song song 2 request: `POST /api/v1/events/view` để ghi nhận lượt xem theo [FR09.01](#fr09), và `GET /api/v1/products/1/recommendations`. |
| **3** | — | [RecommendationController](../api/controllers/RecommendationController.php) gọi [RecommendService.php](../api/services/RecommendService.php) thực thi: |
| **4** | — | • **Khai phá Co-occurrence (FBT):** Quét các đơn hàng trong quá khứ có chứa sản phẩm 1; tính toán tần suất các phụ kiện mua kèm; chọn ra Củ sạc nhanh Apple 20W và Ốp lưng MagSafe có độ tin cậy $\text{Confidence} \ge 0.3$ theo [BR11](#br11). |
| **5** | — | • **Lọc Content-based:** Truy vấn bảng `product_similarity` lấy 4 sản phẩm có độ tương đồng Cosine cao nhất về danh mục điện thoại, thương hiệu Apple/Samsung, RAM 8GB và giá bán tiệm cận theo [BR10](#br10). |
| **6** | — | Hệ thống trả về JSON chứa mảng `frequently_bought_together` và mảng `similar_products` (`200 OK`). |
| **7** | — | Component [public/assets/js/recommend.js](../public/assets/js/recommend.js) hiển thị khung "Combo tiết kiệm thường mua cùng" kèm nút "Thêm cả combo vào giỏ (Giảm 8%)" và render thanh carousel trượt các sản phẩm tương tự mượt mà. |

**b. Luồng thay thế (Alternative Flows):**

| Mã luồng | Điều kiện kích hoạt & Hướng rẽ | Hành vi xử lý của Hệ thống |
| :---: | :--- | :--- |
| **AF09.01** | **Gợi ý cá nhân hóa trên trang chủ (`/recommendations/me`):**<br>Khách hàng đã đăng nhập truy cập trang chủ ([pages/index.html](../public/pages/index.html)). | Hệ thống tính toán điểm số lai theo [BR12](#br12) dựa trên lịch sử xem hàng của người dùng, render khối "Sản phẩm dành riêng cho bạn" với độ phù hợp cao nhất. |
| **AF09.02** | **Xử lý khởi động lạnh (Cold-start Handling):**<br>Sản phẩm mới chưa từng có đơn hàng nào mua cùng, hoặc khách mới chưa có lịch sử xem. | Thuật toán tự động kích hoạt cơ chế Fallback: chọn các sản phẩm cùng danh mục có lượt đánh giá cao nhất và sản phẩm bán chạy nhất trong tuần để gợi ý thay thế. |

##### 3. Ràng buộc bổ sung (Additional Info)
- **Quy tắc nghiệp vụ:** [BR10](#br10) (Cosine similarity), [BR11](#br11) (Co-occurrence FBT), [BR12](#br12) (Hybrid scoring).
- **Yêu cầu phi chức năng:** Thời gian phản hồi API gợi ý $\le 50\text{ms}$ nhờ bảng ma trận tính sẵn `product_similarity` ([NFR01](#nfr01)).

---

#### <a id="uc10"></a>UC10 - Quản trị CRUD Sản phẩm & Kho hàng (Admin)

##### 1. Thông tin chung (Summary)

| Trường thông tin | Nội dung chi tiết |
| :--- | :--- |
| **Mã Use Case (ID)** | **UC10** |
| **Tên Use Case** | **Quản trị CRUD Sản phẩm & Kho hàng** |
| **Tác nhân (Actors)** | • **Tác nhân chính:** Quản trị viên (`Admin`) |
| **Mục đích (Description)** | Cho phép Quản trị viên thêm mới sản phẩm công nghệ với đầy đủ thông số cấu hình, chỉnh sửa thông tin giá bán/tồn kho, tải lên ảnh sản phẩm an toàn và xóa mềm sản phẩm khỏi hệ thống. |
| **Sự kiện kích hoạt (Trigger)** | Quản trị viên truy cập trang [admin/products.html](../public/admin/products.html) và nhấn "Thêm sản phẩm mới" hoặc nút "Sửa/Xóa" tại từng dòng sản phẩm. |
| **Tiền điều kiện (Pre-condition)** | Phiên đăng nhập hợp lệ với tài khoản có vai trò `role = 'admin'`. |
| **Hậu điều kiện (Post-condition)** | • Bản ghi sản phẩm được lưu/cập nhật trong bảng `products`.<br>• Thông số kỹ thuật được lưu trong bảng `product_specs`.<br>• Ảnh tải lên được kiểm tra an toàn và lưu vào thư mục `public/assets/images/products/`.<br>• Ghi nhận hành động vào bảng `audit_logs` theo [BR14](#br14). |
| **Ma trận truy vết** | • **Yêu cầu chức năng:** [FR10.02](#fr10)<br>• **Yêu cầu người dùng:** [UR10](#ur10)<br>• **Quy tắc nghiệp vụ:** [BR03](#br03), [BR13](#br13), [BR14](#br14)<br>• **Ca kiểm thử:** `TC-ADM-02` |

##### 2. Các luồng sự kiện (Flow of Events)

**a. Luồng cơ bản (Basic Flow / Happy Path - Thêm sản phẩm mới):**

| Bước | Hành động của Tác nhân (Actor Action) | Phản hồi của Hệ thống (System Response) |
| :---: | :--- | :--- |
| **1** | Quản trị viên tại trang [admin/products.html](../public/admin/products.html) nhấn "Thêm sản phẩm". | Hệ thống hiển thị Modal thêm sản phẩm gồm các tab: Thông tin chung (Tên, Danh mục, Hãng, Giá niêm yết, Giá bán, Tồn kho), Thông số cấu hình (CPU, RAM, ROM, Pin, Màn hình) và Tải lên hình ảnh. |
| **2** | Quản trị viên điền thông tin, chọn ảnh từ máy tính (hệ thống lập tức hiển thị ảnh preview) và nhấn "Lưu sản phẩm". | Hệ thống kiểm tra dữ liệu: tên không rỗng, giá bán $> 0$, tồn kho $\ge 0$. |
| **3** | — | Hệ thống gửi request `POST /api/v1/products` kèm header Authorization của Admin. |
| **4** | — | [ProductController](../api/controllers/ProductController.php) thẩm định quyền Admin qua `RoleMiddleware`. |
| **5** | — | Hệ thống kiểm tra tệp ảnh tải lên: kiểm tra MIME type qua `finfo_file` (chỉ chấp nhận `image/jpeg, png, webp`), dung lượng $\le 2\text{MB}$, tự động sinh tên file ngẫu nhiên dạng `prod_<hash>.<ext>` theo [BR13](#br13). |
| **6** | — | Hệ thống mở Database Transaction: chèn bản ghi vào bảng `products`, chèn các dòng thông số vào bảng `product_specs`, chèn đường dẫn ảnh vào bảng `product_images`. |
| **7** | — | Hệ thống ghi nhật ký kiểm toán vào bảng `audit_logs` với action `CREATE_PRODUCT` theo [BR14](#br14). Commit Transaction. |
| **8** | — | Hệ thống trả về `201 Created`; giao diện tự động đóng modal, hiển thị thông báo "Thêm sản phẩm thành công" và cập nhật bảng danh sách sản phẩm. |

**b. Luồng thay thế (Alternative Flows):**

| Mã luồng | Điều kiện kích hoạt & Hướng rẽ | Hành vi xử lý của Hệ thống |
| :---: | :--- | :--- |
| **AF10.01** | **Chỉnh sửa thông tin sản phẩm:**<br>Admin nhấn nút "Sửa", cập nhật giá khuyến mãi hoặc số lượng tồn kho mới và nhấn "Lưu". | Hệ thống gửi `PUT /api/v1/products/{id}`; cập nhật bản ghi trong CSDL; ghi nhật ký kiểm toán `UPDATE_PRODUCT` và trả về `200 OK`. |
| **AF10.02** | **Xóa sản phẩm khỏi hệ thống:**<br>Admin nhấn nút "Xóa" một sản phẩm và xác nhận trên hộp thoại cảnh báo. | Hệ thống gửi `DELETE /api/v1/products/{id}`; thực hiện xóa an toàn; ghi nhật ký kiểm toán `DELETE_PRODUCT` và làm mới danh sách. |

**c. Luồng ngoại lệ (Exception Flows):**

| Mã lỗi | Tình huống phát sinh lỗi | Hành vi & Phản hồi của Hệ thống |
| :---: | :--- | :--- |
| **EF10.01** | Ảnh tải lên có đuôi giả mạo (ví dụ file `.php` hoặc `.exe` đổi tên thành `.jpg`). | Hàm `finfo_file` phát hiện MIME type không phải là ảnh hợp lệ; hệ thống từ chối ngay lập tức, trả về `400 Bad Request` kèm thông báo: `"Tệp tải lên không phải là định dạng hình ảnh hợp lệ (chỉ chấp nhận JPG, PNG, WEBP)"` theo [BR13](#br13). |
| **EF10.02** | Tệp ảnh vượt quá dung lượng cho phép ($> 2\text{MB}$). | Hệ thống trả về mã lỗi `400 Bad Request` kèm thông báo: `"Dung lượng ảnh không được vượt quá 2MB"`. |
| **EF10.03** | Tài khoản không có quyền Admin cố tình gọi API tạo/sửa/xóa sản phẩm. | `RoleMiddleware` chặn yêu cầu ngay tại tầng lọc, trả về mã lỗi `403 Forbidden` kèm thông báo: `"Yêu cầu quyền Quản trị viên"`. |

##### 3. Ràng buộc bổ sung (Additional Info)
- **Quy tắc nghiệp vụ:** [BR03](#br03) (RBAC), [BR13](#br13) (An toàn upload ảnh), [BR14](#br14) (Ghi Audit Log bắt buộc).
- **Yêu cầu phi chức năng:** Thời gian xử lý tải ảnh và lưu CSDL $\le 500\text{ms}$ ([NFR01](#nfr01)).

---

### 4.3 Kịch bản xử lý nền (Background Tasks / Scheduled Services)

| Mã tác vụ | Kích hoạt & Tần suất | Luồng xử lý nghiệp vụ | Kết quả kỳ vọng & Xử lý lỗi |
| :---: | :--- | :--- | :--- |
| **SYS01** | Cron tự động mỗi 1 phút hoặc Polling trigger. | Quét các đơn hàng ở trạng thái `PENDING` có phương thức thanh toán `VIETQR` đã quá 15 phút kể từ lúc tạo (`created_at + 15 mins < NOW()`). Tự động chuyển trạng thái thanh toán sang `EXPIRED` và hoàn lại số lượng tồn kho `stock` cho sản phẩm theo [BR05](#br05). | Đơn hàng quá hạn được đóng an toàn; số lượng hàng tồn kho được khôi phục chính xác cho khách hàng khác mua sắm. |
| **SYS02** | Script CLI: Chạy định kỳ hàng ngày lúc 02:00 sáng hoặc kích hoạt thủ công. | Thực thi lệnh `php scripts/build_similarity.php`: quét toàn bộ danh mục sản phẩm, chuẩn hóa vector thuộc tính 5 chiều, tính toán độ tương đồng Cosine giữa mọi cặp sản phẩm và cập nhật bảng `product_similarity` theo [BR10](#br10). | Bảng `product_similarity` luôn được làm mới; API gợi ý tương tự truy vấn với tốc độ cực nhanh $\le 10\text{ms}$. |
| **SYS03** | Cron tự động dọn dẹp mỗi 24 giờ lúc 03:00 sáng. | Quét bảng `refresh_tokens`, xóa vĩnh viễn các token có thời gian hết hạn trong quá khứ (`expires_at < NOW()`) để giải phóng dung lượng CSDL. | Giữ cho bảng `refresh_tokens` luôn gọn gàng, tăng tốc độ truy vấn đối soát token khi đăng nhập. |

---

### 4.4 Ma trận chuyển đổi trạng thái Đơn hàng & Thanh toán (State Machine Matrix)

```
[ĐƠN HÀNG - ORDER STATE]
                 Tạo đơn (COD / VIETQR)
                          │
                          ▼
                    ┌───────────┐       Khách bấm Hủy (khi chưa duyệt)
                    │  PENDING  ├──────────────────────────────────────┐
                    └─────┬─────┘                                      │
                          │ Webhook / Duyệt TT / Xác nhận đơn          │
                          ▼                                            ▼
                    ┌───────────┐                               ┌───────────┐
                    │ CONFIRMED │                               │ CANCELLED │
                    └─────┬─────┘                               │ (Hoàn kho)│
                          │ Xuất kho giao bưu tá                └───────────┘
                          ▼                                            ▲
                    ┌───────────┐                                      │
                    │ SHIPPING  ├──────────────────────────────────────┘
                    └─────┬─────┘       Giao thất bại / Hoàn hàng
                          │ Khách nhận hàng & thanh toán
                          ▼
                    ┌───────────┐
                    │ COMPLETED │ (Cho phép đánh giá 1-5 sao)
                    └───────────┘
```

| Trạng thái hiện tại | Sự kiện kích hoạt (Event) | Trạng thái kế tiếp | Hành động hệ thống đi kèm (Side Effects) |
| :--- | :--- | :---: | :--- |
| **Khởi tạo** | Khách hoàn tất Checkout (`POST /orders`) | **`PENDING`** | Mở Transaction trừ kho `stock`, dọn sạch giỏ hàng, sinh mã đơn hàng. |
| **`PENDING`** | Webhook ngân hàng khớp tiền / Admin duyệt thanh toán | **`CONFIRMED`** | Đổi thanh toán sang `PAID`, gửi thông báo chuẩn bị hàng. |
| **`PENDING`** | Khách hàng nhấn "Hủy đơn hàng" (`POST /orders/{id}/cancel`) | **`CANCELLED`** | Hoàn lại số lượng tồn kho cho các sản phẩm trong đơn; khóa nút hủy. |
| **`PENDING`** | Quá 15 phút chưa thanh toán VietQR (SYS01) | **`EXPIRED`** | Đổi trạng thái thanh toán sang `EXPIRED`, cho phép tạo lại mã hoặc hủy. |
| **`CONFIRMED`** | Admin xác nhận đóng gói giao cho đơn vị vận chuyển | **`SHIPPING`** | Cập nhật ngày giao hàng, hiển thị mã vận đơn trên giao diện khách. |
| **`SHIPPING`** | Giao hàng thành công đến tay khách hàng | **`COMPLETED`** | Cập nhật doanh thu thực nhận, mở quyền cho khách gửi đánh giá sao (UC07). |
| **`SHIPPING`** | Khách từ chối nhận hàng (Bom hàng / Sự cố giao vận) | **`CANCELLED`** | Nhập lại hàng vào kho, ghi nhận lý do giao thất bại. |

---

<a id="phan-5"></a>

## 5. Yêu cầu phi chức năng (Non-Functional Requirements - NFR)

Toàn bộ các yêu cầu phi chức năng được chuẩn hóa và lượng hóa theo tiêu chuẩn quốc tế **ISO/IEC 25010:2023** (Systems and software Quality Requirements and Evaluation - SQRE) trên môi trường kiểm thử tiêu chuẩn:
- **E1 (Môi trường mạng):** Mạng giả lập độ trễ mạng tiêu chuẩn $100\text{ ms}$.
- **D1 (Quy mô dữ liệu mẫu):** Cơ sở dữ liệu mẫu chứa $\ge 45$ sản phẩm, 8 thương hiệu, 5 danh mục và $\ge 30$ đơn hàng mẫu.
- **L1 (Mức tải đồng thời):** Tải đồng thời 50 người dùng tương tác liên tục (50 concurrent users).

---

### <a id="nfr01"></a>NFR01 - Hiệu năng và Thời gian đáp ứng (Performance Efficiency)

| Thuộc tính | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm NFR** | **NFR01** |
| **Phân loại ISO/IEC 25010** | **Hiệu năng và Thời gian đáp ứng (Performance Efficiency)** |
| **Môi trường kiểm thử chuẩn** | Môi trường E1 (Độ trễ 100ms), D1 (CSDL seed mẫu), L1 (50 người dùng đồng thời) |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR02](#ur02), [UR03](#ur03), [UR06](#ur06), [UR10](#ur10)<br>• **Use Case liên quan:** [UC02](#uc02), [UC04](#uc04), [UC05](#uc05), [UC09](#uc09), [UC12](#uc12)<br>• **Yêu cầu chức năng:** [FR02](#fr02), [FR03](#fr03), [FR06](#fr06), [FR07](#fr07), [FR09](#fr09), [FR10](#fr10) |
| **Mục tiêu chất lượng** | Đảm bảo tốc độ xử lý API cực nhanh, tải trang mượt mà dưới $1.0\text{s}$, đáp ứng tức thì các truy vấn tìm kiếm và kiểm tra thanh toán realtime. |

**Bảng chi tiết các chỉ tiêu lượng hóa:**

| Mã ID | Tên tiêu chí chất lượng | Đặc tả yêu cầu & Ngưỡng định lượng (Metrics) | Điều kiện kiểm thử & Môi trường | Phương pháp đo kiểm & Công cụ |
| :---: | :--- | :--- | :--- | :--- |
| **NFR01.01** | Thời gian phản hồi API đọc danh mục & chi tiết | $95\%$ các yêu cầu API đọc (Lấy danh mục, thương hiệu, chi tiết sản phẩm): $T_{resp} \le 150\text{ ms}$. | Môi trường D1, mạng E1, 50 người dùng ảo đồng thời. | Đo thời gian bằng Postman Runner / k6 Load Testing. |
| **NFR01.02** | Thời gian phản hồi API ghi đơn hàng & Transaction | $95\%$ các yêu cầu ghi nhận đơn hàng có mở PDO Transaction và trừ tồn kho: $T_{write} \le 200\text{ ms}$. | Kiểm thử tạo đơn liên tục trên môi trường L1. | Đo bằng Postman Performance và ghi log `executionTimeMs`. |
| **NFR01.03** | Thời gian tải trang ban đầu (FCP) | Thời gian hiển thị nội dung đầu tiên (First Contentful Paint - FCP) của giao diện người dùng đạt dưới **$1.0\text{ giây}$**. | Trình duyệt Chrome qua mạng tiêu chuẩn. | Đo bằng Google Lighthouse Audit (điểm Performance $\ge 90$). |
| **NFR01.04** | Tốc độ truy vấn API Gợi ý thông minh | Thời gian phản hồi API gợi ý sản phẩm tương tự `GET /products/{id}/recommendations` không vượt quá **$50\text{ ms}$** nhờ bảng dữ liệu tính sẵn `product_similarity`. | Truy vấn liên tục trên 45 sản phẩm mẫu. | Postman Collection Runner test suite. |

---

### <a id="nfr02"></a>NFR02 - Bảo mật và Toàn vẹn thông tin (Security)

| Thuộc tính | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm NFR** | **NFR02** |
| **Phân loại ISO/IEC 25010** | **Bảo mật và Toàn vẹn thông tin (Security)** |
| **Môi trường kiểm thử chuẩn** | Môi trường Staging bảo mật, giao thức mã hóa HTTPS/TLS, CSDL MySQL mã hóa |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR01](#ur01), [UR06](#ur06), [UR10](#ur10), [UR12](#ur12)<br>• **Use Case liên quan:** [UC01](#uc01), [UC04](#uc04), [UC10](#uc10), [UC11](#uc11), [UC13](#uc13)<br>• **Yêu cầu chức năng:** [FR01](#fr01), [FR06](#fr06), [FR07](#fr07), [FR10](#fr10) |
| **Mục tiêu chất lượng** | Bảo vệ tuyệt đối thông tin định danh và tài khoản người dùng; ngăn ngừa 100% các lỗ hổng bảo mật phổ biến theo OWASP API Security Top 10. |

**Bảng chi tiết các chỉ tiêu lượng hóa:**

| Mã ID | Tên tiêu chí chất lượng | Đặc tả yêu cầu & Ngưỡng định lượng (Metrics) | Điều kiện kiểm thử & Môi trường | Phương pháp đo kiểm & Công cụ |
| :---: | :--- | :--- | :--- | :--- |
| **NFR02.01** | Mã hóa mật khẩu một chiều | $100\%$ mật khẩu người dùng lưu trong bảng `users` bắt buộc được băm bằng thuật toán `PASSWORD_BCRYPT` với cost factor $\ge 10$ kèm Salt ngẫu nhiên theo [NFR02](#nfr02). | Kiểm tra trực tiếp dữ liệu cột `password` trong CSDL MySQL. | Script kiểm tra độ dài và tiền tố chuỗi hash `$2y$10$...`. |
| **NFR02.02** | Bảo vệ và Ký số Token JWT | Token JWT bắt buộc ký bằng thuật toán HMAC-SHA256 (`HS256`) với khóa bí mật `JWT_SECRET` trong `.env`. Chặn truy cập ngay lập tức (`401 Unauthorized`) khi token sai chữ ký, thiếu token hoặc hết hạn `exp`. | Kiểm thử gửi token giả mạo chữ ký hoặc sửa payload. | Postman folder "Demo bảo mật": kiểm tra mã phản hồi `401`. |
| **NFR02.03** | Chống lỗ hổng SQL Injection | $100\%$ các câu truy vấn cơ sở dữ liệu bắt buộc sử dụng Native Prepared Statements của PDO (`$stmt->prepare()` và `$stmt->execute()`), tuyệt đối cấm nối chuỗi trực tiếp tham số người dùng vào câu lệnh SQL. | Quét mã tĩnh và kiểm thử chèn chuỗi `' OR '1'='1`. | Quét mã nguồn bằng SonarQube / RIPS; kiểm thử tấn công SQLi. |
| **NFR02.04** | Chống lỗ hổng IDOR (Insecure Direct Object References) | Kiểm soát phân quyền sở hữu tài nguyên nghiêm ngặt: người dùng chỉ được xem/sửa giỏ hàng, đơn hàng và wishlist của chính mình; cố tình gọi ID của người khác bị chặn bằng mã lỗi `403 Forbidden` theo [BR03](#br03). | Kiểm thử dùng Token của User A để gọi đơn hàng của User B. | Postman test case IDOR assertion: `pm.response.to.have.status(403)`. |
| **NFR02.05** | Xác thực chữ ký số Webhook thanh toán | Endpoint `POST /payments/webhook` bắt buộc thẩm định chữ ký số HMAC-SHA256 (`X-Webhook-Signature`) bằng khóa bí mật bí mật chung; từ chối xử lý khi chữ ký không khớp. | Gửi request webhook giả mạo không có chữ ký. | Kiểm tra mã phản hồi `401 Unauthorized` từ webhook handler. |

---

### <a id="nfr03"></a>NFR03 - Độ tin cậy và Tính toàn vẹn dữ liệu (Reliability & Consistency)

| Thuộc tính | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm NFR** | **NFR03** |
| **Phân loại ISO/IEC 25010** | **Độ tin cậy và Tính nhất quán dữ liệu (Reliability & Consistency)** |
| **Môi trường kiểm thử chuẩn** | Môi trường D1/L1, kiểm thử xung đột tranh chấp mua hàng đồng thời |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR05](#ur05), [UR06](#ur06)<br>• **Use Case liên quan:** [UC03](#uc03), [UC04](#uc04)<br>• **Yêu cầu chức năng:** [FR04](#fr04), [FR06](#fr06) |
| **Mục tiêu chất lượng** | Bảo đảm tính nhất quán dữ liệu tồn kho và tiền hàng trong mọi tình huống tranh chấp mua hàng đồng thời; không bao giờ bị trừ âm kho. |

**Bảng chi tiết các chỉ tiêu lượng hóa:**

| Mã ID | Tên tiêu chí chất lượng | Đặc tả yêu cầu & Ngưỡng định lượng (Metrics) | Điều kiện kiểm thử & Môi trường | Phương pháp đo kiểm & Công cụ |
| :---: | :--- | :--- | :--- | :--- |
| **NFR03.01** | Toàn vẹn tồn kho trong ACID Transaction | $100\%$ các giao dịch đặt hàng phải bảo toàn số lượng tồn kho: $\text{Tồn kho mới} = \text{Tồn kho cũ} - \text{Số lượng đặt}$. Bất kỳ sự cố nào xảy ra trong chuỗi xử lý phải kích hoạt Rollback tự động toàn bộ. | Giả lập mất kết nối mạng hoặc lỗi cú pháp giữa chừng khi tạo đơn. | Kiểm tra CSDL: không có bản ghi đơn hàng mồ côi; số lượng tồn kho giữ nguyên. |
| **NFR03.02** | Khả năng tự phục hồi (Fallback Resiliency) | Module `api.js` tích hợp cơ chế **Smart Mock Fallback**: khi máy chủ backend gặp sự cố tạm thời hoặc khi chạy demo tĩnh, hệ thống tự động fallback đọc dữ liệu từ `localStorage` để giao diện không bị sập màn hình trắng. | Ngắt dịch vụ web server PHP cục bộ. | Kiểm tra giao diện người dùng vẫn hiển thị banner và danh mục mượt mà. |

---

### <a id="nfr04"></a>NFR04 - Khả năng bảo trì và Mở rộng (Maintainability)

| Thuộc tính | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm NFR** | **NFR04** |
| **Phân loại ISO/IEC 25010** | **Khả năng bảo trì và Mở rộng (Maintainability)** |
| **Môi trường kiểm thử chuẩn** | Môi trường phát triển chuẩn PHP 8.2 và chuẩn kiến trúc mã nguồn |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR10](#ur10), [UR12](#ur12)<br>• **Yêu cầu chức năng:** Toàn bộ [FR01](#fr01) - [FR10](#fr10) |
| **Mục tiêu chất lượng** | Cấu trúc thư mục rõ ràng theo chuẩn phân lớp MVC, loại bỏ hoàn toàn mô hình Monolith 1 file, tự động nạp class theo chuẩn PSR-4 và chuẩn hóa tài liệu OpenAPI 3.0. |

**Bảng chi tiết các chỉ tiêu lượng hóa:**

| Mã ID | Tên tiêu chí chất lượng | Đặc tả yêu cầu & Ngưỡng định lượng (Metrics) | Điều kiện kiểm thử & Môi trường | Phương pháp đo kiểm & Công cụ |
| :---: | :--- | :--- | :--- | :--- |
| **NFR04.01** | Phân rã mã nguồn theo Module | Loại bỏ hoàn toàn cấu trúc Monolith 1 file; toàn bộ logic được phân rã thành **11 Controllers chuyên biệt**, 3 Middleware và 3 Services độc lập trong thư mục `api/` theo tài liệu [architecture.md](design/architecture.md). | Kiểm tra cây thư mục dự án thực tế. | Rà soát mã nguồn không có file nào vượt quá 500 dòng code logic. |
| **NFR04.02** | Tương thích chuẩn OpenAPI 3.0 | Toàn bộ 37 endpoints RESTful API được đặc tả đầy đủ trong tệp [docs/openapi.yaml](openapi.yaml) chuẩn xác về path, method, headers, request body schema và response status codes. | Kiểm tra cú pháp qua công cụ Swagger Editor / Spectral linter. | Tỷ lệ lỗi schema đạt $0\text{ errors}$. |

---

### <a id="nfr05"></a>NFR05 - Khả năng tương thích và Đa nền tảng (Compatibility)

| Thuộc tính | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm NFR** | **NFR05** |
| **Phân loại ISO/IEC 25010** | **Khả năng tương thích và Đa nền tảng (Compatibility)** |
| **Môi trường kiểm thử chuẩn** | Các trình duyệt hiện đại trên Windows, macOS, iOS và Android |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR02](#ur02), [UR06](#ur06)<br>• **Use Case liên quan:** Toàn bộ Use Cases ([UC01](#uc01) - [UC14](#uc14)) |
| **Mục tiêu chất lượng** | Giao diện hiển thị nhất quán, hoạt động ổn định trên các trình duyệt phổ biến và tự động thích ứng mượt mà với mọi kích thước màn hình. |

**Bảng chi tiết các chỉ tiêu lượng hóa:**

| Mã ID | Tên tiêu chí chất lượng | Đặc tả yêu cầu & Ngưỡng định lượng (Metrics) | Điều kiện kiểm thử & Môi trường | Phương pháp đo kiểm & Công cụ |
| :---: | :--- | :--- | :--- | :--- |
| **NFR05.01** | Tương thích trình duyệt Web | Giao diện Web hiển thị đúng thiết kế và hoạt động ổn định trên **$100\%$** các trình duyệt hiện đại: Google Chrome ($\ge 110$), Mozilla Firefox ($\ge 110$), Microsoft Edge ($\ge 110$) và Apple Safari ($\ge 16$). | Môi trường kiểm thử đa trình duyệt trên desktop và mobile. | Kiểm tra giao diện thực tế và ghi nhận bug log tại [bug-log-ui.md](tests/bug-log-ui.md). |
| **NFR05.02** | Thiết kế đáp ứng (Responsive Design) | Giao diện sử dụng hệ thống Design Tokens CSS ([tokens.css](../public/assets/css/tokens.css)), tự động tối ưu hóa hiển thị trên 3 dải độ phân giải chuẩn:<br>• **Mobile:** $375\text{px} - 430\text{px}$ (iPhone, điện thoại Android)<br>• **Tablet:** $768\text{px} - 1024\text{px}$ (iPad, máy tính bảng)<br>• **Desktop:** $\ge 1200\text{px}$ (Màn hình máy tính chuẩn). | Kiểm tra hiển thị responsive trên các thiết bị thực tế. | Kiểm tra qua Chrome DevTools Device Mode; không bị vỡ layout hay tràn ngang. |

---

### <a id="nfr06"></a>NFR06 - Tính khả dụng và Trải nghiệm người dùng (Usability)

| Thuộc tính | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm NFR** | **NFR06** |
| **Phân loại ISO/IEC 25010** | **Tính khả dụng và Trải nghiệm người dùng (Usability)** |
| **Môi trường kiểm thử chuẩn** | Môi trường kiểm thử người dùng cuối (User Acceptance Testing - UAT) |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR02](#ur02), [UR05](#ur05), [UR06](#ur06)<br>• **Use Case liên quan:** Toàn bộ Use Cases |
| **Mục tiêu chất lượng** | Giao diện đẹp mắt lấy cảm hứng từ CellphoneS, thao tác mua sắm tối giản trong 3 bước, thông báo lỗi tiếng Việt thân thiện và dễ hiểu. |

**Bảng chi tiết các chỉ tiêu lượng hóa:**

| Mã ID | Tên tiêu chí chất lượng | Đặc tả yêu cầu & Ngưỡng định lượng (Metrics) | Điều kiện kiểm thử & Môi trường | Phương pháp đo kiểm & Công cụ |
| :---: | :--- | :--- | :--- | :--- |
| **NFR06.01** | Tối ưu hóa quy trình Checkout | Số bước thao tác để hoàn tất đặt hàng từ giỏ hàng không vượt quá **3 bước**: Vào giỏ hàng $\rightarrow$ Chọn Thanh toán Checkout $\rightarrow$ Quét mã VietQR. | Quy trình đặt hàng thực tế trên giao diện. | Đo đếm số click/tap trong kịch bản kiểm thử UAT. |
| **NFR06.02** | Thông báo lỗi thân thiện tiếng Việt | $100\%$ thông báo lỗi trả về cho người dùng phải bằng **tiếng Việt rõ ràng, dễ hiểu**, chỉ rõ nguyên nhân (ví dụ: "Số lượng vượt quá tồn kho", "Mã coupon đã hết hạn"); **tuyệt đối không hiển thị lỗi code kỹ thuật (Fatal Error / Stack Trace)** ra giao diện. | Kiểm thử mọi tình huống biên và kịch bản luồng ngoại lệ. | Kiểm tra đối soát nội dung thông báo lỗi trên UI. |

---

### <a id="nfr07"></a>NFR07 - Ràng buộc pháp lý và Chuẩn mực thương mại điện tử (Compliance)

| Thuộc tính | Nội dung chi tiết |
| :--- | :--- |
| **Mã nhóm NFR** | **NFR07** |
| **Phân loại ISO/IEC 25010** | **Ràng buộc pháp lý và Chuẩn mực thương mại điện tử (Compliance)** |
| **Môi trường kiểm thử chuẩn** | Môi trường sản xuất và đối chiếu quy định pháp lý TMĐT Việt Nam |
| **Ma trận truy vết** | • **Yêu cầu người dùng:** [UR01](#ur01), [UR06](#ur06)<br>• **Yêu cầu chức năng:** [FR01](#fr01), [FR06](#fr06), [FR07](#fr07) |
| **Mục tiêu chất lượng** | Tuân thủ quy định pháp luật Việt Nam về thương mại điện tử và chuẩn mực định dạng tiền tệ Việt Nam. |

**Bảng chi tiết các chỉ tiêu lượng hóa:**

| Mã ID | Tên tiêu chí chất lượng | Đặc tả yêu cầu & Ngưỡng định lượng (Metrics) | Điều kiện kiểm thử & Môi trường | Phương pháp đo kiểm & Công cụ |
| :---: | :--- | :--- | :--- | :--- |
| **NFR07.01** | Chuẩn định dạng tiền tệ Việt Nam | Đồng tiền thanh toán hiển thị dấu phân cách hàng nghìn bằng dấu chấm (ví dụ: `15.990.000 đ`) và không có phần thập phân theo tập quán kế toán Việt Nam. | Mọi màn hình hiển thị số tiền và hóa đơn in. | Kiểm tra định dạng tiền tệ thông qua hàm `formatPrice()` trên toàn bộ các trang UI. |
| **NFR07.02** | Chuẩn mã thanh toán VietQR | Mã QR thanh toán tuân thủ đúng định dạng chuẩn NAPAS247 liên ngân hàng của Ngân hàng Nhà nước Việt Nam, đảm bảo các ứng dụng ngân hàng (Mobile Banking) tại Việt Nam quét và nhận diện tự động 100%. | Quét thử bằng app ngân hàng MB Bank, Vietcombank, Techcombank. | Ứng dụng ngân hàng tự động điền đúng số tài khoản, số tiền và nội dung chuyển khoản. |

---

<a id="phan-6"></a>

## 6. Ma trận truy vết và Tiêu chí nghiệm thu (Traceability & Acceptance)

### 6.1 Ma trận truy vết hai chiều (Bidirectional Traceability Matrix)

Bảng ma trận truy vết này thiết lập liên kết xuyên suốt hai chiều từ **Yêu cầu người dùng (UR)** $\leftrightarrow$ **Yêu cầu chức năng (FR)** $\leftrightarrow$ **Ca sử dụng (Use Case)** $\leftrightarrow$ **Quy tắc nghiệp vụ (BR)** $\leftrightarrow$ **Yêu cầu phi chức năng (NFR)** $\leftrightarrow$ **RESTful API Endpoint** $\leftrightarrow$ **Mã kịch bản kiểm thử (Test Case ID)**:

| Mã UR | Mã FR | Use Case liên quan | Quy tắc BR | Tiêu chuẩn NFR | RESTful API Endpoint | Mã Test Case (Postman & UAT) |
| :---: | :---: | :---: | :---: | :---: | :--- | :---: |
| **UR01** | FR01.01 - FR01.06 | UC01 | BR01, BR02, BR03 | NFR01, NFR02 | `POST /auth/register`, `/login`, `/refresh`, `/logout`, `GET /me` | `TC-AUTH-01` đến `TC-AUTH-06` |
| **UR02** | FR02.01, FR02.02, FR03.04 | UC02 | BR01 | NFR01, NFR05 | `GET /categories`, `GET /brands`, `GET /products?search=...` | `TC-PROD-01`, `TC-PROD-03` |
| **UR03** | FR03.01 - FR03.03 | UC02 | BR01 | NFR01, NFR05 | `GET /products` (lọc giá, hãng, ram, rom, phân trang) | `TC-PROD-02` |
| **UR04** | FR02.03, FR09.01, FR09.02 | UC02, UC09 | BR10, BR11 | NFR01, NFR06 | `GET /products/{id}`, `GET /products/{id}/recommendations` | `TC-PROD-04`, `TC-REC-05` |
| **UR05** | FR04.01 - FR04.04, FR05.02 | UC03 | BR01, BR04, BR07 | NFR01, NFR03 | `GET/POST/PUT/DELETE /cart`, `POST /coupons/validate` | `TC-CART-01` đến `TC-CART-05` |
| **UR06** | FR06.01, FR07.01 | UC04 | BR04, BR05 | NFR01, NFR02, NFR07 | `POST /orders`, `POST /payments/qr` | `TC-ORD-01`, `TC-PAY-01` |
| **UR07** | FR07.02, FR07.03 | UC05 | BR05, BR06 | NFR01, NFR02, NFR07 | `GET /payments/{orderId}/status`, `POST /payments/webhook` | `TC-PAY-02`, `TC-PAY-03` |
| **UR08** | FR06.02, FR06.03, FR06.04 | UC06 | BR08 | NFR01, NFR02 | `GET /orders`, `GET /orders/{id}`, `POST /orders/{id}/cancel` | `TC-ORD-02` đến `TC-ORD-04` |
| **UR09** | FR08.01 - FR08.03 | UC07, UC08 | BR09 | NFR02, NFR06 | `GET/POST /products/{id}/reviews`, `GET/POST/DELETE /wishlist` | `TC-REC-01` đến `TC-REC-03` |
| **UR10** | FR10.01, FR10.02 | UC10, UC12 | BR03, BR13, BR14 | NFR01, NFR04 | `GET /admin/stats`, `POST/PUT/DELETE /products` | `TC-ADM-01`, `TC-ADM-02` |
| **UR11** | FR06.05, FR07.04, FR10.04 | UC11 | BR06, BR14 | NFR01, NFR06 | `PATCH /orders/{id}/status` (duyệt TT & in hóa đơn) | `TC-ADM-04`, `TC-ADM-05`, `TC-ADM-06` |
| **UR12** | FR10.03, FR10.05 - FR10.07 | UC13, UC14 | BR03, BR14, BR15 | NFR02, NFR04 | `CRUD /categories`, `/brands`, `GET/PATCH /admin/users`, `CRUD /coupons` | `TC-ADM-03`, `TC-ADM-07`, `TC-ADM-08` |

---

### 6.2 Danh mục Ca kiểm thử mẫu tiêu biểu (Representative Test Cases)

| Mã ca kiểm thử | Loại kịch bản | Mục đích kiểm thử | Dữ liệu đầu vào (Input) | Kết quả kỳ vọng (Expected Output) | Trạng thái |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **`TC-AUTH-01`** | Positive | Đăng ký tài khoản khách hàng mới thành công. | Họ tên, email mới chưa trùng, mật khẩu $\ge 6$ ký tự, SĐT. | HTTP `201 Created`; bản ghi được tạo trong CSDL; mật khẩu băm bcrypt. | **Pass** |
| **`TC-AUTH-02`** | Negative | Đăng nhập với mật khẩu không chính xác. | Email đúng, mật khẩu sai ký tự. | HTTP `401 Unauthorized`; thông điệp "Email hoặc mật khẩu không chính xác". | **Pass** |
| **`TC-AUTH-03`** | Positive | Tự động làm mới Access Token bằng Refresh Token. | `refresh_token` hợp lệ còn hạn trong CSDL. | HTTP `200 OK`; nhận `access_token` mới có thời hạn 2 giờ. | **Pass** |
| **`TC-AUTH-04`** | Security | Chặn truy cập API Admin bằng Token của Customer thường. | Header `Authorization: Bearer <customer_token>` gọi `GET /admin/stats`. | HTTP `403 Forbidden`; thông điệp "Yêu cầu quyền Quản trị viên". | **Pass** |
| **`TC-PROD-01`** | Positive | Lọc danh sách sản phẩm theo khoảng giá và hãng. | `brand_id=1` (Apple), `min_price=10000000`, `max_price=30000000`. | HTTP `200 OK`; toàn bộ sản phẩm trả về đều thuộc hãng Apple và trong tầm giá. | **Pass** |
| **`TC-CART-01`** | Negative | Thêm sản phẩm vào giỏ với số lượng vượt quá tồn kho. | `product_id=1`, `quantity=9999` (trong khi `stock=15`). | HTTP `400 Bad Request`; thông báo "Số lượng vượt quá tồn kho khả dụng". | **Pass** |
| **`TC-ORD-01`** | Positive | Đặt hàng thành công trong PDO Transaction và trừ kho. | Giỏ hàng có 2 món, thông tin nhận hàng đầy đủ, chọn COD. | HTTP `201 Created`; sinh mã đơn `ORD-...`; số lượng tồn kho `stock` bị trừ đúng 2 món. | **Pass** |
| **`TC-PAY-01`** | Positive | Sinh ảnh thanh toán VietQR chứa mã đơn và số tiền. | `order_id` vừa tạo, phương thức `VIETQR`. | HTTP `200 OK`; link ảnh QR code NAPAS247 hợp lệ, `expires_at` sau 15 phút. | **Pass** |
| **`TC-PAY-02`** | Polling | Polling kiểm tra trạng thái thanh toán từ PENDING sang PAID. | Polling mỗi 3s; kích hoạt webhook thanh toán thành công. | HTTP `200 OK`; `payment_status` đổi thành `PAID`; giao diện chuyển màn hình chúc mừng. | **Pass** |
| **`TC-REC-01`** | Negative | Khách chưa mua hàng cố tình gửi đánh giá sản phẩm. | Khách chưa từng có đơn `COMPLETED` cho sản phẩm 1, gửi đánh giá 5 sao. | HTTP `403 Forbidden`; thông báo "Bạn chỉ có thể đánh giá sản phẩm sau khi đã mua hàng". | **Pass** |
| **`TC-ADM-01`** | Security | Upload ảnh sản phẩm có đuôi file giả mạo (`hack.php.jpg`). | Tệp tải lên có nội dung script PHP, đổi đuôi thành `.jpg`. | `finfo_file` phát hiện MIME type sai; HTTP `400 Bad Request` từ chối upload. | **Pass** |
| **`TC-ADM-02`** | Security | Quản trị viên cố gắng tự khóa tài khoản của chính mình. | Admin đang đăng nhập gửi `PATCH /admin/users/{my_id}` với `status=locked`. | HTTP `403 Forbidden`; chặn thao tác tự khóa tài khoản admin cuối cùng theo [BR15](#br15). | **Pass** |

---

### 6.3 Tiêu chí nghiệm thu pha Elaboration / Construction (Definition of Done - DoD)
Để hoàn tất nghiệm thu kỹ thuật và chuẩn bị cho pha Transition / Đóng gói bài nộp:
1. **$100\%$ Yêu cầu Chức năng (FR01 - FR10)** có đầy đủ mã nguồn hiện thực hóa, kiểm tra cú pháp PHP 8.2 không lỗi và kết nối đồng bộ giữa Front-end và Back-end.
2. **Bộ kiểm thử API Postman ([tests/postman_collection.json](../tests/postman_collection.json))** phủ kín toàn bộ 37 endpoints với tỷ lệ vượt qua (Pass Rate) đạt $100\%$ trên Postman Collection Runner.
3. **Thư viện xác thực JWT ([JwtService.php](../api/services/JwtService.php)) và Cổng VietQR ([QrService.php](../api/services/QrService.php))** vận hành trơn tru trên môi trường máy chủ cục bộ (XAMPP / PHP built-in server).
4. **Tài liệu Báo cáo Đồ án ([docs/report/](report/))** được dẫn xuất trực tiếp từ các chương của tài liệu SRS này, đảm bảo tính nhất quán tuyệt đối giữa Phân tích yêu cầu, Thiết kế hệ thống và Kết quả kiểm thử thực nghiệm.

---
*Tài liệu SRS TechStore được biên soạn và chuẩn hóa theo đúng thực tế hiện trạng mã nguồn và chuẩn học thuật Khoa CNTT - Trường Đại học Tôn Đức Thắng.*
