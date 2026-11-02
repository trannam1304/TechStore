import os
import yaml

spec_yaml = """openapi: 3.0.3
info:
  title: TechStore RESTful API Specification
  description: >-
    Hợp đồng API (API Contract) chính thức giữa Front-End (HTML/CSS/JS) và Back-End (PHP thuần & MySQL).
    Thuộc Đồ án Topic 1: RESTful API · Đại học Tôn Đức Thắng.
    Bao quát toàn diện 10 phân hệ nghiệp vụ: Auth, Products, Categories, Brands, Cart, Coupons, Orders, Payments, Reviews & Wishlist, Recommendations, Admin & Audit Logs.
    Tuân thủ nghiêm ngặt nguyên lý REST, chuẩn mã trạng thái HTTP và cấu trúc response thống nhất.
  version: 1.0.0
  contact:
    name: TechStore Engineering Team
    email: admin@techstore.local

servers:
  - url: http://localhost:8000/api/v1
    description: Máy chủ cục bộ (Local Development Server)
  - url: /api/v1
    description: Production API Gateway

tags:
  - name: Auth
    description: Xác thực, đăng nhập JWT, đăng ký và quản lý phiên
  - name: Products
    description: Danh mục sản phẩm, bộ lọc, tìm kiếm, thông số và quản lý CRUD
  - name: Categories
    description: Ngành hàng sản phẩm
  - name: Brands
    description: Thương hiệu công nghệ
  - name: Cart
    description: Quản lý giỏ hàng mua sắm và kiểm soát tồn kho realtime
  - name: Coupons
    description: Mã voucher ưu đãi giảm giá
  - name: Orders
    description: Quy trình đặt hàng ACID, lịch sử đơn và cập nhật trạng thái
  - name: Payments
    description: Thanh toán chuyển khoản tự động VietQR (Napas247) và Webhook
  - name: Reviews
    description: Đánh giá và nhận xét sản phẩm sau mua (BR09)
  - name: Wishlist
    description: Danh sách sản phẩm yêu thích của khách hàng
  - name: Recommendations
    description: Động cơ gợi ý thông minh Cosine 5D, Apriori FBT và Hybrid Score
  - name: Admin
    description: Quản trị hệ thống, KPI thống kê 7 ngày và Audit Logs

paths:
  # ============================================================================
  # 1. AUTHENTICATION & USERS
  # ============================================================================
  /auth/register:
    post:
      tags: [Auth]
      summary: Đăng ký tài khoản khách hàng mới
      description: Tạo tài khoản mới, băm mật khẩu Bcrypt và cấp cặp Access Token + Refresh Token
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/RegisterRequest'
      responses:
        '201':
          description: Đăng ký thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/AuthResponse'
        '400':
          description: Dữ liệu gửi lên không hợp lệ
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'
        '409':
          description: Email đã được sử dụng
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /auth/login:
    post:
      tags: [Auth]
      summary: Đăng nhập hệ thống, cấp Access & Refresh Token
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/LoginRequest'
      responses:
        '200':
          description: Đăng nhập thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/AuthResponse'
        '401':
          description: Sai email hoặc mật khẩu
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /auth/refresh:
    post:
      tags: [Auth]
      summary: Cấp mới Access Token bằng Refresh Token (Silent Refresh)
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/TokenRefreshRequest'
      responses:
        '200':
          description: Cấp token mới thành công
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: object
                    properties:
                      access_token: { type: string, example: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." }
                      token_type: { type: string, example: "Bearer" }
                      expires_in: { type: integer, example: 7200 }
        '401':
          description: Refresh token không hợp lệ hoặc đã hết hạn
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /auth/logout:
    post:
      tags: [Auth]
      summary: Đăng xuất an toàn và thu hồi Refresh Token
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Đăng xuất thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  /auth/me:
    get:
      tags: [Auth]
      summary: Lấy hồ sơ tài khoản đang đăng nhập
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Lấy thông tin cá nhân thành công
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    $ref: '#/components/schemas/User'
        '401':
          description: Chưa đăng nhập hoặc token không hợp lệ
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /auth/google:
    post:
      tags: [Auth]
      summary: Đăng nhập bằng Google OAuth 2.0 Credential Token
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: [credential]
              properties:
                credential: { type: string, example: "eyJhbGciOiJSUzI1NiIs..." }
      responses:
        '200':
          description: Đăng nhập Google thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/AuthResponse'

  # ============================================================================
  # 2. PRODUCTS & CATALOG
  # ============================================================================
  /products:
    get:
      tags: [Products]
      summary: Lấy danh sách sản phẩm với bộ lọc đa tiêu chí, tìm kiếm và phân trang
      parameters:
        - name: page
          in: query
          schema: { type: integer, default: 1 }
        - name: limit
          in: query
          schema: { type: integer, default: 12, maximum: 100 }
        - name: search
          in: query
          schema: { type: string }
        - name: category_id
          in: query
          schema: { type: integer }
        - name: brand_id
          in: query
          schema: { type: integer }
        - name: min_price
          in: query
          schema: { type: number }
        - name: max_price
          in: query
          schema: { type: number }
        - name: ram
          in: query
          schema: { type: string }
        - name: rom
          in: query
          schema: { type: string }
        - name: sort
          in: query
          schema: { type: string, enum: [price_asc, price_desc, rating_desc, newest], default: newest }
      responses:
        '200':
          description: Danh sách sản phẩm kèm metadata phân trang
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Product'
                  meta:
                    $ref: '#/components/schemas/PaginationMeta'

    post:
      tags: [Products]
      summary: Thêm sản phẩm mới (Quyền Admin)
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/ProductCreateRequest'
      responses:
        '201':
          description: Tạo sản phẩm mới thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'
        '403':
          description: Yêu cầu quyền Quản trị viên
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /products/{id}:
    get:
      tags: [Products]
      summary: Xem chi tiết sản phẩm, thông số kỹ thuật và thư viện ảnh
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Chi tiết sản phẩm
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    $ref: '#/components/schemas/ProductDetail'
        '404':
          description: Không tìm thấy sản phẩm
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

    put:
      tags: [Products]
      summary: Cập nhật toàn diện sản phẩm (Quyền Admin)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/ProductCreateRequest'
      responses:
        '200':
          description: Cập nhật sản phẩm thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

    patch:
      tags: [Products]
      summary: Cập nhật một phần thông tin sản phẩm (Quyền Admin)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/ProductUpdateRequest'
      responses:
        '200':
          description: Cập nhật sản phẩm thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

    delete:
      tags: [Products]
      summary: Xóa sản phẩm (Quyền Admin)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Xóa sản phẩm thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  # ============================================================================
  # 3. CATEGORIES & BRANDS
  # ============================================================================
  /categories:
    get:
      tags: [Categories]
      summary: Lấy danh sách ngành hàng kèm số lượng sản phẩm liên kết
      responses:
        '200':
          description: Danh sách danh mục
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Category'

    post:
      tags: [Categories]
      summary: Tạo mới danh mục (Quyền Admin)
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CategoryCreateRequest'
      responses:
        '201':
          description: Tạo danh mục thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  /categories/{id}:
    put:
      tags: [Categories]
      summary: Cập nhật thông tin danh mục (Quyền Admin)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CategoryCreateRequest'
      responses:
        '200':
          description: Cập nhật danh mục thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

    delete:
      tags: [Categories]
      summary: Xóa danh mục (Quyền Admin)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Xóa danh mục thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  /brands:
    get:
      tags: [Brands]
      summary: Lấy danh sách thương hiệu công nghệ
      responses:
        '200':
          description: Danh sách thương hiệu
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Brand'

    post:
      tags: [Brands]
      summary: Tạo mới thương hiệu (Quyền Admin)
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/BrandCreateRequest'
      responses:
        '201':
          description: Tạo thương hiệu thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  /brands/{id}:
    put:
      tags: [Brands]
      summary: Cập nhật thương hiệu (Quyền Admin)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/BrandCreateRequest'
      responses:
        '200':
          description: Cập nhật thương hiệu thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

    delete:
      tags: [Brands]
      summary: Xóa thương hiệu (Quyền Admin)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Xóa thương hiệu thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  # ============================================================================
  # 4. CART & INVENTORY CONTROL
  # ============================================================================
  /cart:
    get:
      tags: [Cart]
      summary: Lấy giỏ hàng của người dùng hiện tại kèm tính toán tổng tiền
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Thông tin giỏ hàng
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CartResponse'

    delete:
      tags: [Cart]
      summary: Làm trống toàn bộ giỏ hàng
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Đã dọn sạch giỏ hàng
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  /cart/items:
    post:
      tags: [Cart]
      summary: Thêm sản phẩm vào giỏ hàng (Kiểm tra tồn kho realtime BR04)
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/AddToCartRequest'
      responses:
        '201':
          description: Thêm vào giỏ thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CartResponse'
        '400':
          description: Vượt quá số lượng tồn kho khả dụng
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /cart/items/{id}:
    put:
      tags: [Cart]
      summary: Cập nhật số lượng món đồ trong giỏ (Tự động xóa nếu quantity = 0)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/UpdateCartItemRequest'
      responses:
        '200':
          description: Cập nhật số lượng thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CartResponse'

    delete:
      tags: [Cart]
      summary: Xóa một món đồ cụ thể khỏi giỏ
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Xóa món đồ khỏi giỏ thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CartResponse'

  # ============================================================================
  # 5. COUPONS ENGINE
  # ============================================================================
  /coupons:
    get:
      tags: [Coupons]
      summary: Lấy danh sách các mã giảm giá công khai đang có hiệu lực
      responses:
        '200':
          description: Danh sách mã giảm giá
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Coupon'

  /coupons/validate:
    post:
      tags: [Coupons]
      summary: Thẩm định mã giảm giá theo điều kiện đơn hàng tối thiểu và hạn dùng (BR07)
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CouponValidateRequest'
      responses:
        '200':
          description: Mã giảm giá hợp lệ
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CouponValidateResponse'
        '400':
          description: Mã hết hạn, hết lượt hoặc chưa đạt đơn tối thiểu
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  # ============================================================================
  # 6. ORDERS & ACID TRANSACTION
  # ============================================================================
  /orders:
    get:
      tags: [Orders]
      summary: Lấy danh sách lịch sử đơn hàng của tôi
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Lịch sử đơn hàng
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Order'

    post:
      tags: [Orders]
      summary: Tạo đơn hàng mới trong Database Transaction ACID (Trừ kho, tạo đơn, xóa giỏ)
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateOrderRequest'
      responses:
        '201':
          description: Đặt hàng thành công
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    $ref: '#/components/schemas/Order'
        '400':
          description: Sản phẩm trong giỏ hết hàng hoặc giỏ hàng rỗng
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /orders/{id}:
    get:
      tags: [Orders]
      summary: Xem chi tiết đơn hàng (Chống IDOR BR03)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Chi tiết đơn hàng
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    $ref: '#/components/schemas/Order'
        '403':
          description: Không có quyền truy cập đơn hàng của người khác
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /orders/{id}/cancel:
    post:
      tags: [Orders]
      summary: Khách hàng hủy đơn hàng khi trạng thái PENDING và tự động hoàn kho (BR08)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Hủy đơn hàng và hoàn kho thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'
        '400':
          description: Đơn hàng đã được duyệt hoặc đang vận chuyển, không thể hủy
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /orders/{id}/status:
    patch:
      tags: [Orders]
      summary: Cập nhật trạng thái xử lý và thanh toán của đơn hàng (Quyền Admin)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/UpdateOrderStatusRequest'
      responses:
        '200':
          description: Cập nhật trạng thái đơn thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  # ============================================================================
  # 7. PAYMENTS (VIETQR & WEBHOOK)
  # ============================================================================
  /payments/qr:
    post:
      tags: [Payments]
      summary: Sinh ảnh mã VietQR NAPAS247 động theo số tiền và mã đơn (BR05)
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/VietQrInitRequest'
      responses:
        '201':
          description: Khởi tạo mã VietQR thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/VietQrResponse'

  /payments/{orderId}/status:
    get:
      tags: [Payments]
      summary: Polling kiểm tra trạng thái thanh toán đơn hàng mỗi 3 giây (BR06)
      security:
        - BearerAuth: []
      parameters:
        - name: orderId
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Trạng thái thanh toán của đơn
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: object
                    properties:
                      order_id: { type: integer, example: 1001 }
                      payment_status: { type: string, enum: [PENDING, PAID, EXPIRED], example: "PAID" }
                      order_status: { type: string, example: "CONFIRMED" }
                      is_paid: { type: boolean, example: true }

  /payments/webhook:
    post:
      tags: [Payments]
      summary: Webhook ngân hàng tự động duyệt tiền về kèm chữ ký HMAC-SHA256 (BR06)
      parameters:
        - name: X-Webhook-Signature
          in: header
          required: true
          schema: { type: string }
          description: Chữ ký HMAC-SHA256 tính từ body và WEBHOOK_SECRET
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/WebhookPaymentRequest'
      responses:
        '200':
          description: Cập nhật thanh toán thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'
        '401':
          description: Chữ ký HMAC không hợp lệ hoặc thiếu header
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  # ============================================================================
  # 8. REVIEWS & WISHLIST
  # ============================================================================
  /products/{id}/reviews:
    get:
      tags: [Reviews]
      summary: Lấy danh sách đánh giá của sản phẩm
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Danh sách đánh giá
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Review'

    post:
      tags: [Reviews]
      summary: Gửi đánh giá sao 1-5 kèm bình luận sau khi mua hàng COMPLETED (BR09)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateReviewRequest'
      responses:
        '201':
          description: Gửi đánh giá thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'
        '403':
          description: Bạn chỉ có thể đánh giá sau khi đã mua hàng và đơn đã hoàn tất
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /wishlist:
    get:
      tags: [Wishlist]
      summary: Lấy danh sách sản phẩm yêu thích cá nhân
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Danh sách sản phẩm yêu thích
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/WishlistItem'

    post:
      tags: [Wishlist]
      summary: Thêm sản phẩm vào danh sách yêu thích
      security:
        - BearerAuth: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/AddToWishlistRequest'
      responses:
        '200':
          description: Đã thêm vào danh sách yêu thích
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  /wishlist/{id}:
    delete:
      tags: [Wishlist]
      summary: Xóa sản phẩm khỏi danh sách yêu thích
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Đã xóa khỏi yêu thích
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  # ============================================================================
  # 9. RECOMMENDATIONS & AI EVENTS
  # ============================================================================
  /events/view:
    post:
      tags: [Recommendations]
      summary: Ghi nhận sự kiện xem sản phẩm vào MySQL (HTTP 202 Accepted)
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/ViewEventRequest'
      responses:
        '202':
          description: Ghi nhận lượt xem thành công
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  message: { type: string, example: "Ghi nhận lượt xem" }

  /products/{id}/recommendations:
    get:
      tags: [Recommendations]
      summary: Gợi ý sản phẩm tương tự (Cosine 5D) và combo thường mua cùng (Apriori FBT)
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      responses:
        '200':
          description: Danh sách gợi ý thông minh
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: object
                    properties:
                      similar:
                        type: array
                        items:
                          $ref: '#/components/schemas/Product'
                      bought_together:
                        type: array
                        items:
                          $ref: '#/components/schemas/Product'

  /recommendations/me:
    get:
      tags: [Recommendations]
      summary: Gợi ý sản phẩm cá nhân hóa theo công thức lai Hybrid Score (BR12)
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Danh sách sản phẩm được AI đề xuất riêng
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: object
                    properties:
                      for_you:
                        type: array
                        items:
                          $ref: '#/components/schemas/Product'
                      is_personalized: { type: boolean, example: true }
                      algorithm: { type: string, example: "hybrid_score (0.4*Content + 0.4*CoOccur + 0.2*Popularity)" }

  # ============================================================================
  # 10. ADMIN & AUDIT LOGS
  # ============================================================================
  /admin/stats:
    get:
      tags: [Admin]
      summary: Báo cáo tổng quan KPI doanh thu, đơn hàng và biểu đồ 7 ngày (Quyền Admin)
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Thống kê Dashboard
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    $ref: '#/components/schemas/AdminStatsResponse'
        '403':
          description: Yêu cầu quyền Quản trị viên
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ErrorResponse'

  /admin/users:
    get:
      tags: [Admin]
      summary: Danh sách người dùng hệ thống (Quyền Admin)
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Danh sách người dùng
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/User'

  /admin/users/{id}:
    patch:
      tags: [Admin]
      summary: Khóa/mở khóa hoặc cập nhật phân quyền người dùng (Quyền Admin, cấm tự khóa BR15)
      security:
        - BearerAuth: []
      parameters:
        - name: id
          in: path
          required: true
          schema: { type: integer }
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/AdminUserPatchRequest'
      responses:
        '200':
          description: Cập nhật người dùng thành công
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/SuccessResponse'

  /admin/audit-logs:
    get:
      tags: [Admin]
      summary: Tra cứu nhật ký kiểm toán bất biến của Quản trị viên (BR14)
      security:
        - BearerAuth: []
      responses:
        '200':
          description: Danh sách nhật ký kiểm toán
          content:
            application/json:
              schema:
                type: object
                properties:
                  success: { type: boolean, example: true }
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/AuditLog'

# ==============================================================================
# COMPONENTS SCHEMAS (ĐỊNH NGHĨA KIỂU DỮ LIỆU CHUẨN)
# ==============================================================================
components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
      description: Nhập JWT Access Token vào định dạng "Bearer {token}"

  schemas:
    # 1. Base Responses
    SuccessResponse:
      type: object
      required: [success]
      properties:
        success: { type: boolean, example: true }
        message: { type: string, example: "Thao tác thành công" }
        data: { type: object }

    ErrorResponse:
      type: object
      required: [success, error]
      properties:
        success: { type: boolean, example: false }
        error:
          type: object
          required: [code, message]
          properties:
            code: { type: string, example: "VALIDATION_ERROR" }
            message: { type: string, example: "Dữ liệu gửi lên không hợp lệ" }
            details:
              type: array
              items: { type: string }

    PaginationMeta:
      type: object
      properties:
        current_page: { type: integer, example: 1 }
        per_page: { type: integer, example: 12 }
        total_items: { type: integer, example: 83 }
        total_pages: { type: integer, example: 7 }

    # 2. Auth Schemas
    RegisterRequest:
      type: object
      required: [email, password, name]
      properties:
        name: { type: string, example: "Nguyễn Văn A" }
        email: { type: string, format: email, example: "khachhang@gmail.com" }
        password: { type: string, format: password, example: "Password@123" }
        phone: { type: string, example: "0987654321" }
        address: { type: string, example: "227 Nguyễn Văn Cừ, Quận 5, TP.HCM" }

    LoginRequest:
      type: object
      required: [email, password]
      properties:
        email: { type: string, format: email, example: "user@techstore.local" }
        password: { type: string, format: password, example: "User@123" }

    TokenRefreshRequest:
      type: object
      required: [refresh_token]
      properties:
        refresh_token: { type: string, example: "b556b34819311e8c306d3756deeb3ea7..." }

    User:
      type: object
      properties:
        id: { type: integer, example: 2 }
        name: { type: string, example: "Tuấn Kiệt" }
        email: { type: string, format: email, example: "user@techstore.local" }
        role: { type: string, enum: [admin, customer], example: "customer" }
        phone: { type: string, example: "0987654321" }
        address: { type: string, example: "123 Cách Mạng Tháng 8, Quận 10, TP.HCM" }
        avatar: { type: string, example: "https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=200" }
        status: { type: string, enum: [active, inactive, banned], example: "active" }
        created_at: { type: string, format: date-time, example: "2026-02-15 09:30:00" }

    AuthResponse:
      type: object
      properties:
        success: { type: boolean, example: true }
        data:
          type: object
          properties:
            user:
              $ref: '#/components/schemas/User'
            access_token: { type: string, example: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." }
            refresh_token: { type: string, example: "b556b34819311e8c306d3756deeb3ea7..." }
            token_type: { type: string, example: "Bearer" }
            expires_in: { type: integer, example: 7200 }

    # 3. Product Schemas
    Product:
      type: object
      properties:
        id: { type: integer, example: 1 }
        name: { type: string, example: "iPhone 16 Pro Max 256GB" }
        slug: { type: string, example: "iphone-16-pro-max-256gb" }
        category_id: { type: integer, example: 1 }
        category_name: { type: string, example: "Điện thoại" }
        brand_id: { type: integer, example: 1 }
        brand_name: { type: string, example: "Apple" }
        price: { type: number, example: 34990000 }
        original_price: { type: number, example: 37990000 }
        stock: { type: integer, example: 25 }
        rating: { type: number, example: 4.95 }
        review_count: { type: integer, example: 142 }
        view_count: { type: integer, example: 1850 }
        image: { type: string, example: "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600" }
        ram: { type: string, example: "8GB" }
        rom: { type: string, example: "256GB" }
        chipset: { type: string, example: "Apple A18 Pro 3nm" }
        featured: { type: integer, enum: [0, 1], example: 1 }

    ProductDetail:
      allOf:
        - $ref: '#/components/schemas/Product'
        - type: object
          properties:
            description: { type: string, example: "iPhone 16 Pro Max thiết kế Titan sa mạc..." }
            images:
              type: array
              items: { type: string }
            screen: { type: string, example: "6.9 inch Super Retina XDR OLED 120Hz" }
            battery: { type: string, example: "4685 mAh, Sạc nhanh 30W" }
            camera: { type: string, example: "Chính 48MP + Góc rộng 48MP + Tele 5x 12MP" }
            specs:
              type: array
              items:
                type: object
                properties:
                  spec_group: { type: string, example: "Hiệu năng" }
                  spec_key: { type: string, example: "Vi xử lý (CPU/Chip)" }
                  spec_value: { type: string, example: "Apple A18 Pro 3nm" }

    ProductCreateRequest:
      type: object
      required: [name, category_id, brand_id, price, stock]
      properties:
        name: { type: string, example: "iPhone 16 Pro Max 256GB" }
        category_id: { type: integer, example: 1 }
        brand_id: { type: integer, example: 1 }
        price: { type: number, example: 34990000 }
        original_price: { type: number, example: 37990000 }
        stock: { type: integer, example: 25 }
        image: { type: string, example: "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600" }
        images:
          type: array
          items: { type: string }
        ram: { type: string, example: "8GB" }
        rom: { type: string, example: "256GB" }
        chipset: { type: string, example: "Apple A18 Pro" }
        screen: { type: string, example: "6.9 inch Super Retina XDR" }
        battery: { type: string, example: "4685 mAh" }
        camera: { type: string, example: "48MP + 48MP + 12MP" }
        description: { type: string, example: "Mô tả sản phẩm" }
        featured: { type: integer, enum: [0, 1], example: 1 }

    ProductUpdateRequest:
      type: object
      properties:
        price: { type: number, example: 33990000 }
        stock: { type: integer, example: 30 }
        featured: { type: integer, enum: [0, 1], example: 1 }
        status: { type: string, enum: [active, inactive, out_of_stock], example: "active" }

    Category:
      type: object
      properties:
        id: { type: integer, example: 1 }
        name: { type: string, example: "Điện thoại" }
        slug: { type: string, example: "dien-thoai" }
        icon: { type: string, example: "📱" }
        product_count: { type: integer, example: 10 }

    CategoryCreateRequest:
      type: object
      required: [name]
      properties:
        name: { type: string, example: "Phụ kiện Gaming" }
        icon: { type: string, example: "🎮" }

    Brand:
      type: object
      properties:
        id: { type: integer, example: 1 }
        name: { type: string, example: "Apple" }
        slug: { type: string, example: "apple" }
        logo: { type: string, example: "" }

    BrandCreateRequest:
      type: object
      required: [name]
      properties:
        name: { type: string, example: "Razer" }
        logo: { type: string, example: "razer-logo.png" }

    # 4. Cart Schemas
    CartItem:
      type: object
      properties:
        id: { type: integer, example: 1 }
        user_id: { type: integer, example: 2 }
        product_id: { type: integer, example: 1 }
        product_name: { type: string, example: "iPhone 16 Pro Max 256GB" }
        price: { type: number, example: 34990000 }
        quantity: { type: integer, example: 1 }
        variant: { type: string, example: "256GB - Titan Sa Mạc" }
        image: { type: string, example: "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600" }

    CartResponse:
      type: object
      properties:
        success: { type: boolean, example: true }
        data:
          type: object
          properties:
            items:
              type: array
              items:
                $ref: '#/components/schemas/CartItem'
            total_items: { type: integer, example: 2 }
            subtotal: { type: number, example: 35880000 }

    AddToCartRequest:
      type: object
      required: [product_id, quantity]
      properties:
        product_id: { type: integer, example: 1 }
        quantity: { type: integer, default: 1, example: 1 }
        variant: { type: string, example: "256GB - Titan Sa Mạc" }

    UpdateCartItemRequest:
      type: object
      required: [quantity]
      properties:
        quantity: { type: integer, example: 2 }

    # 5. Order Schemas
    OrderItem:
      type: object
      properties:
        id: { type: integer, example: 1 }
        order_id: { type: integer, example: 1001 }
        product_id: { type: integer, example: 1 }
        product_name: { type: string, example: "iPhone 16 Pro Max 256GB" }
        price: { type: number, example: 34990000 }
        quantity: { type: integer, example: 1 }
        variant: { type: string, example: "256GB - Titan Sa Mạc" }
        image: { type: string, example: "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600" }

    Order:
      type: object
      properties:
        id: { type: integer, example: 1001 }
        order_code: { type: string, example: "TS-749201" }
        user_id: { type: integer, example: 2 }
        customer_name: { type: string, example: "Tuấn Kiệt" }
        customer_phone: { type: string, example: "0987654321" }
        customer_email: { type: string, example: "user@techstore.local" }
        shipping_address: { type: string, example: "123 Cách Mạng Tháng 8, Q10, TP.HCM" }
        payment_method: { type: string, enum: [vietqr, cod, bank_transfer], example: "vietqr" }
        payment_status: { type: string, enum: [pending, paid, failed], example: "paid" }
        status: { type: string, enum: [pending, confirmed, shipping, completed, cancelled], example: "completed" }
        subtotal: { type: number, example: 41370000 }
        discount_amount: { type: number, example: 500000 }
        shipping_fee: { type: number, example: 0 }
        total_amount: { type: number, example: 40870000 }
        coupon_code: { type: string, example: "TECH10" }
        note: { type: string, example: "Giao giờ hành chính" }
        items:
          type: array
          items:
            $ref: '#/components/schemas/OrderItem'
        created_at: { type: string, format: date-time, example: "2026-08-05 09:30:00" }

    CreateOrderRequest:
      type: object
      required: [customer_name, customer_phone, shipping_address, payment_method]
      properties:
        customer_name: { type: string, example: "Tuấn Kiệt" }
        customer_phone: { type: string, example: "0987654321" }
        customer_email: { type: string, format: email, example: "user@techstore.local" }
        shipping_address: { type: string, example: "123 Cách Mạng Tháng 8, Quận 10, TP.HCM" }
        payment_method: { type: string, enum: [vietqr, cod, bank_transfer], example: "vietqr" }
        coupon_code: { type: string, example: "TECH10" }
        note: { type: string, example: "Gọi trước khi giao hàng" }

    UpdateOrderStatusRequest:
      type: object
      properties:
        status:
          type: string
          enum: [pending, confirmed, shipping, completed, cancelled]
          example: "shipping"
        payment_status:
          type: string
          enum: [pending, paid, failed]
          example: "paid"

    # 6. Payment & VietQR Schemas
    VietQrInitRequest:
      type: object
      required: [order_id]
      properties:
        order_id: { type: integer, example: 1001 }
        amount: { type: number, example: 40870000 }
        order_code: { type: string, example: "TS-749201" }

    VietQrResponse:
      type: object
      properties:
        success: { type: boolean, example: true }
        data:
          type: object
          properties:
            qr_url: { type: string, example: "https://img.vietqr.io/image/MB-0123456789-compact2.png?amount=40870000&addInfo=TS-749201" }
            bank_name: { type: string, example: "Ngân hàng Quân Đội (MB Bank)" }
            account_number: { type: string, example: "0123456789" }
            account_name: { type: string, example: "TECHSTORE VIETNAM" }
            amount: { type: number, example: 40870000 }
            order_code: { type: string, example: "TS-749201" }
            expires_in: { type: integer, example: 900 }

    WebhookPaymentRequest:
      type: object
      required: [order_code, amount]
      properties:
        order_code: { type: string, example: "TS-749201" }
        amount: { type: number, example: 40870000 }
        transaction_id: { type: string, example: "TXN-987654321" }

    # 7. Coupon Schemas
    Coupon:
      type: object
      properties:
        id: { type: integer, example: 1 }
        code: { type: string, example: "TECH10" }
        discount_type: { type: string, enum: [percent, fixed], example: "percent" }
        value: { type: number, example: 10 }
        min_order: { type: number, example: 20000000 }
        max_discount: { type: number, example: 500000 }
        usage_limit: { type: integer, example: 100 }
        used_count: { type: integer, example: 12 }
        status: { type: string, example: "active" }

    CouponValidateRequest:
      type: object
      required: [code, total]
      properties:
        code: { type: string, example: "TECH10" }
        total: { type: number, example: 34990000 }

    CouponValidateResponse:
      type: object
      properties:
        success: { type: boolean, example: true }
        data:
          type: object
          properties:
            code: { type: string, example: "TECH10" }
            discount_type: { type: string, example: "percent" }
            discount: { type: number, example: 500000 }
            final_total: { type: number, example: 34490000 }

    # 8. Reviews & Wishlist Schemas
    Review:
      type: object
      properties:
        id: { type: integer, example: 1 }
        product_id: { type: integer, example: 1 }
        user_id: { type: integer, example: 2 }
        user_name: { type: string, example: "Tuấn Kiệt" }
        rating: { type: integer, minimum: 1, maximum: 5, example: 5 }
        comment: { type: string, example: "Máy dùng mượt mà, camera xuất sắc!" }
        created_at: { type: string, format: date-time, example: "2026-08-10 14:20:00" }

    CreateReviewRequest:
      type: object
      required: [rating]
      properties:
        rating: { type: integer, minimum: 1, maximum: 5, example: 5 }
        comment: { type: string, example: "Sản phẩm chính hãng đóng gói cẩn thận" }

    WishlistItem:
      type: object
      properties:
        id: { type: integer, example: 1 }
        product_id: { type: integer, example: 1 }
        name: { type: string, example: "iPhone 16 Pro Max 256GB" }
        price: { type: number, example: 34990000 }
        image: { type: string, example: "https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=600" }

    AddToWishlistRequest:
      type: object
      required: [product_id]
      properties:
        product_id: { type: integer, example: 1 }

    ViewEventRequest:
      type: object
      required: [product_id]
      properties:
        product_id: { type: integer, example: 1 }

    # 9. Admin Schemas
    AdminStatsResponse:
      type: object
      properties:
        revenue:
          type: object
          properties:
            total: { type: number, example: 1001450000 }
            this_month: { type: number, example: 154000000 }
        orders:
          type: object
          properties:
            total: { type: integer, example: 32 }
            pending: { type: integer, example: 3 }
        products:
          type: object
          properties:
            total: { type: integer, example: 83 }
            low_stock: { type: integer, example: 4 }
        users:
          type: object
          properties:
            total: { type: integer, example: 15 }

    AdminUserPatchRequest:
      type: object
      properties:
        status: { type: string, enum: [active, banned], example: "active" }
        role: { type: string, enum: [customer, admin], example: "customer" }

    AuditLog:
      type: object
      properties:
        id: { type: integer, example: 1 }
        admin_id: { type: integer, example: 1 }
        admin_email: { type: string, example: "admin@techstore.local" }
        action: { type: string, example: "UPDATE_ORDER_STATUS" }
        details: { type: string, example: "Chuyển đơn hàng TS-749201 sang completed" }
        ip_address: { type: string, example: "127.0.0.1" }
        created_at: { type: string, format: date-time, example: "2026-08-05 10:00:00" }
"""

import sys
sys.stdout.reconfigure(encoding='utf-8')

# 1. Thẩm định cú pháp YAML
try:
    data = yaml.safe_load(spec_yaml)
    print(f"[OK] YAML Syntax Valid! Total {len(data['paths'])} endpoints specified.")
except Exception as e:
    print(f"[ERROR] YAML Syntax Error: {e}")
    exit(1)

# 2. Ghi ra docs/openapi.yaml
target1 = os.path.join(os.path.dirname(__file__), "..", "docs", "openapi.yaml")
with open(target1, "w", encoding="utf-8") as f:
    f.write(spec_yaml.strip() + "\n")
print(f"[OK] Successfully updated: {target1}")

# 3. Đồng bộ ra public/docs/openapi.yaml cho Swagger UI
target2 = os.path.join(os.path.dirname(__file__), "..", "public", "docs", "openapi.yaml")
with open(target2, "w", encoding="utf-8") as f:
    f.write(spec_yaml.strip() + "\n")
print(f"[OK] Successfully synced to Swagger UI: {target2}")
