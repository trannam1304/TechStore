<?php
/**
 * TechStore - Verification Test Suite for Priority 1 & 2
 * 1. CouponController::getCoupons() and discount_amount in validateCoupon
 * 2. Anti-IDOR in OrderController::cancelOrder (BR08 & NFR02.04)
 * 3. scripts/build_similarity.php (SYS02) & Hybrid Recommendation forMe() (FR09.03 & BR12)
 * 4. Review purchase verification & anti-duplicate (BR09 & TC-REC-01)
 * 5. VietQR 15-minute expiration (SYS01 & BR05) & Webhook HMAC authentication (BR06 & NFR02.05)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/services/JwtService.php';

use Config\Database;
use Api\Services\JwtService;

$baseUrl = 'http://localhost:8000/api/v1';

function callApi(string $method, string $path, $body = null, ?string $token = null, array $extraHeaders = []): array {
    global $baseUrl;
    $url = $baseUrl . $path;
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }
    foreach ($extraHeaders as $k => $v) {
        $headers[] = "{$k}: {$v}";
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if ($body !== null) {
        $payload = is_string($body) ? $body : json_encode($body);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    }

    $res = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $code,
        'json' => json_decode((string)$res, true),
        'raw'  => $res
    ];
}

$pdo = Database::getConnection();

echo "====================================================================\n";
echo " TECHSTORE - KIỂM THỬ XÁC MINH CÁC TÍNH NĂNG ƯU TIÊN 1 & 2\n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $title, bool $condition, string $detail = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$title}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$title}" . ($detail ? " -> {$detail}" : "") . "\n";
        $failCount++;
    }
}

// Chuẩn bị 2 user để test phân quyền & IDOR (Lưu vào bảng users để thỏa mãn FK)
$pdo->exec("INSERT INTO users (id, name, email, password_hash, role, status) VALUES 
    (101, 'User A', 'user_a@test.local', 'hash', 'customer', 'active'),
    (102, 'User B', 'user_b@test.local', 'hash', 'customer', 'active')
    ON DUPLICATE KEY UPDATE name = VALUES(name), role = VALUES(role), status = VALUES(status)
");

$userA = [
    'id'    => 101,
    'name'  => 'User A (Owner)',
    'email' => 'user_a@test.local',
    'role'  => 'customer'
];
$userB = [
    'id'    => 102,
    'name'  => 'User B (Attacker)',
    'email' => 'user_b@test.local',
    'role'  => 'customer'
];

$tokenA = JwtService::generateAccessToken($userA);
$tokenB = JwtService::generateAccessToken($userB);

// -----------------------------------------------------------------------------
// NHÓM 1: COUPONS (getCoupons & discount_amount)
// -----------------------------------------------------------------------------
echo "[1] KIỂM THỬ COUPONS:\n";

// 1.1 GET /coupons
$resCoupons = callApi('GET', '/coupons');
assertTest(
    'GET /coupons - Lấy danh sách coupon hoạt động thành công (HTTP 200)',
    $resCoupons['code'] === 200 && is_array($resCoupons['json']['data']),
    "HTTP {$resCoupons['code']}"
);

// 1.2 POST /coupons/validate trả về discount_amount
$resVal = callApi('POST', '/coupons/validate', ['code' => 'TECH10', 'total' => 20000000]);
assertTest(
    'POST /coupons/validate - Phản hồi chứa key discount_amount khớp với Cart/Checkout UI',
    $resVal['code'] === 200 && isset($resVal['json']['data']['discount_amount']) && $resVal['json']['data']['discount_amount'] > 0,
    "HTTP {$resVal['code']}, discount_amount: " . ($resVal['json']['data']['discount_amount'] ?? 'null')
);

// -----------------------------------------------------------------------------
// NHÓM 2: CHỐNG IDOR TRONG HỦY ĐƠN HÀNG (OrderController::cancelOrder)
// -----------------------------------------------------------------------------
echo "\n[2] KIỂM THỬ CHỐNG LỖ HỔNG IDOR KHI HỦY ĐƠN HÀNG (BR08 & NFR02.04):\n";

// Tạo đơn hàng cho User A
$stmtOrderA = $pdo->prepare("
    INSERT INTO orders (order_code, user_id, customer_name, customer_phone, shipping_address, payment_method, payment_status, status, total_amount, created_at)
    VALUES (?, ?, 'User A', '0901111111', 'HCM', 'cod', 'pending', 'pending', 15000000, NOW())
");
$orderCodeA = 'ORD-A-' . time();
$stmtOrderA->execute([$orderCodeA, $userA['id']]);
$orderIdA = (int)$pdo->lastInsertId();

// Thêm item
$stmtItemA = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, 1, 'Test Item', 15000000, 1)");
$stmtItemA->execute([$orderIdA]);

// User B cố tình gửi request hủy đơn của User A
$resIdor = callApi('POST', "/orders/{$orderIdA}/cancel", null, $tokenB);
assertTest(
    'Chặn IDOR: User B không thể hủy đơn hàng của User A (HTTP 403 Forbidden)',
    $resIdor['code'] === 403,
    "HTTP {$resIdor['code']} (Kỳ vọng 403)"
);

// User A tự hủy đơn hàng của chính mình
$resOwnerCancel = callApi('POST', "/orders/{$orderIdA}/cancel", null, $tokenA);
assertTest(
    'Chính chủ: User A hủy đơn hàng của chính mình thành công (HTTP 200 OK)',
    $resOwnerCancel['code'] === 200 && ($resOwnerCancel['json']['data']['status'] ?? '') === 'cancelled',
    "HTTP {$resOwnerCancel['code']}"
);

// -----------------------------------------------------------------------------
// NHÓM 3: GỢI Ý THÔNG MINH (build_similarity & Recommendation forMe)
// -----------------------------------------------------------------------------
echo "\n[3] KIỂM THỬ GỢI Ý THÔNG MINH & HYBRID SCORING (FR09.03 & BR12):\n";

// Chạy script build_similarity.php
exec('php scripts/build_similarity.php', $cliOutput, $exitCode);
assertTest(
    'SYS02 CLI: Chạy scripts/build_similarity.php thành công với exit code 0',
    $exitCode === 0,
    "Exit code: {$exitCode}"
);

// Kiểm tra bảng product_similarity có dữ liệu
$simCount = (int)$pdo->query("SELECT COUNT(*) FROM product_similarity")->fetchColumn();
assertTest(
    'Bảng product_similarity đã được lưu trữ ma trận tương đồng (Cosine & Apriori)',
    $simCount > 0,
    "Số bản ghi: {$simCount}"
);

// Gọi API GET /recommendations/me
$resRecMe = callApi('GET', '/recommendations/me', null, $tokenA);
assertTest(
    'GET /recommendations/me - Gợi ý cá nhân hóa Hybrid Scoring hoạt động (HTTP 200)',
    $resRecMe['code'] === 200 && !empty($resRecMe['json']['data']['for_you']),
    "HTTP {$resRecMe['code']}"
);

// -----------------------------------------------------------------------------
// NHÓM 4: RÀNG BUỘC ĐÁNH GIÁ SẢN PHẨM (BR09 & TC-REC-01)
// -----------------------------------------------------------------------------
echo "\n[4] KIỂM THỬ ĐIỀU KIỆN ĐÁNH GIÁ SẢN PHẨM (BR09):\n";

// User B chưa từng mua sản phẩm ID 2 -> Thử gửi đánh giá
$resNoBuyReview = callApi('POST', '/products/2/reviews', [
    'rating'  => 5,
    'comment' => 'Khách chưa mua hàng thử đánh giá'
], $tokenB);
assertTest(
    'BR09 Negative: Khách chưa mua sản phẩm bị chặn gửi đánh giá (HTTP 403 Forbidden)',
    $resNoBuyReview['code'] === 403,
    "HTTP {$resNoBuyReview['code']} (Kỳ vọng 403)"
);

// Tạo đơn hàng COMPLETED mua sản phẩm 2 cho User B
$stmtOrderB = $pdo->prepare("
    INSERT INTO orders (order_code, user_id, customer_name, customer_phone, shipping_address, payment_method, payment_status, status, total_amount, created_at)
    VALUES (?, ?, 'User B', '0902222222', 'HCM', 'cod', 'paid', 'completed', 25000000, NOW())
");
$orderCodeB = 'ORD-B-' . time();
$stmtOrderB->execute([$orderCodeB, $userB['id']]);
$orderIdB = (int)$pdo->lastInsertId();

$stmtItemB = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, 2, 'MacBook Air M3', 25000000, 1)");
$stmtItemB->execute([$orderIdB]);

// User B gửi đánh giá sau khi đã mua và hoàn tất đơn
$resBoughtReview = callApi('POST', '/products/2/reviews', [
    'rating'  => 5,
    'comment' => 'Máy dùng rất mượt mà, giao nhanh!'
], $tokenB);
assertTest(
    'BR09 Positive: Khách đã mua đơn COMPLETED gửi đánh giá thành công (HTTP 201 Created)',
    $resBoughtReview['code'] === 201,
    "HTTP {$resBoughtReview['code']}"
);

// User B gửi đánh giá lần 2 cho cùng sản phẩm -> Phải bị chặn 409 Conflict
$resDuplicateReview = callApi('POST', '/products/2/reviews', [
    'rating'  => 4,
    'comment' => 'Đánh giá lần 2 bị chặn'
], $tokenB);
assertTest(
    'BR09 Anti-Duplicate: Mỗi khách hàng chỉ được gửi tối đa 1 đánh giá (HTTP 409 Conflict)',
    $resDuplicateReview['code'] === 409,
    "HTTP {$resDuplicateReview['code']} (Kỳ vọng 409)"
);

// -----------------------------------------------------------------------------
// NHÓM 5: THANH TOÁN VIETQR 15 PHÚT (SYS01) & WEBHOOK HMAC (BR06)
// -----------------------------------------------------------------------------
echo "\n[5] KIỂM THỬ THANH TOÁN VIETQR & WEBHOOK HMAC (SYS01 & BR06 & NFR02.05):\n";

// 5.1 Webhook thiếu chữ ký số
$resNoSigWebhook = callApi('POST', '/payments/webhook', ['order_code' => $orderCodeB]);
assertTest(
    'BR06 / NFR02.05: Webhook thiếu chữ ký HMAC bị từ chối ngay lập tức (HTTP 401)',
    $resNoSigWebhook['code'] === 401,
    "HTTP {$resNoSigWebhook['code']} (Kỳ vọng 401)"
);

// 5.2 Webhook với chữ ký số giả mạo / sai
$resFakeSigWebhook = callApi('POST', '/payments/webhook', ['order_code' => $orderCodeB], null, [
    'X-Webhook-Signature' => 'fake_hmac_signature_1234567890abcdef'
]);
assertTest(
    'BR06 / NFR02.05: Webhook sai chữ ký HMAC bị từ chối (HTTP 401)',
    $resFakeSigWebhook['code'] === 401,
    "HTTP {$resFakeSigWebhook['code']} (Kỳ vọng 401)"
);

// 5.3 Webhook với chữ ký số HMAC-SHA256 chuẩn
$webhookSecret = 'techstore_webhook_secret_key_2026';
$webhookPayload = json_encode(['order_code' => $orderCodeB, 'transaction_id' => 'TXN-MB-' . time()]);
$validHmac = hash_hmac('sha256', $webhookPayload, $webhookSecret);

$resValidWebhook = callApi('POST', '/payments/webhook', $webhookPayload, null, [
    'X-Webhook-Signature' => $validHmac
]);
assertTest(
    'BR06 / NFR02.05: Webhook có chữ ký HMAC hợp lệ cập nhật PAID thành công (HTTP 200)',
    $resValidWebhook['code'] === 200 && ($resValidWebhook['json']['data']['status'] ?? '') === 'paid',
    "HTTP {$resValidWebhook['code']}"
);

// 5.4 VietQR 15-phút hết hạn tự động (SYS01 & BR05)
// Tạo đơn hàng VietQR có thời điểm tạo hơn 16 phút trước
$oldCreatedAt = date('Y-m-d H:i:s', time() - 1000); // 16.6 phút trước
$orderCodeStale = 'ORD-STALE-' . time();
$stmtStale = $pdo->prepare("
    INSERT INTO orders (order_code, user_id, customer_name, customer_phone, shipping_address, payment_method, payment_status, status, total_amount, created_at)
    VALUES (?, ?, 'User Stale', '0903333333', 'HCM', 'vietqr', 'pending', 'pending', 12000000, ?)
");
$stmtStale->execute([$orderCodeStale, $userA['id'], $oldCreatedAt]);
$staleOrderId = (int)$pdo->lastInsertId();

// Gắn order_item với product ID 1
$initStock = (int)$pdo->query("SELECT stock FROM products WHERE id = 1")->fetchColumn();
$stmtStaleItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity) VALUES (?, 1, 'iPhone 16', 12000000, 2)");
$stmtStaleItem->execute([$staleOrderId]);

// Giả lập stock đã bị trừ khi tạo đơn
$pdo->exec("UPDATE products SET stock = stock - 2 WHERE id = 1");

// Gọi Polling GET /payments/{orderId}/status
$resPollStatus = callApi('GET', "/payments/{$staleOrderId}/status");

// Kiểm tra đơn đã tự động chuyển sang EXPIRED và hoàn lại stock
$postStock = (int)$pdo->query("SELECT stock FROM products WHERE id = 1")->fetchColumn();
$pStatus = strtoupper($resPollStatus['json']['data']['payment_status'] ?? '');

assertTest(
    'SYS01 & BR05: Đơn VietQR quá 15 phút tự động chuyển EXPIRED và hoàn trả tồn kho',
    $resPollStatus['code'] === 200 && $pStatus === 'EXPIRED' && $postStock === $initStock,
    "Payment Status: {$pStatus}, Stock khôi phục: " . ($postStock === $initStock ? 'ĐÚNG' : 'SAI')
);

// Dọn dẹp dữ liệu test
$pdo->exec("DELETE FROM reviews WHERE comment LIKE '%Khách chưa mua hàng%' OR comment LIKE '%Máy dùng rất mượt mà%'");
$pdo->exec("DELETE FROM orders WHERE user_id IN (101, 102)");
$pdo->exec("DELETE FROM users WHERE id IN (101, 102)");

echo "\n====================================================================\n";
echo " KẾT QUẢ TỔNG HỢP KIỂM THỬ ƯU TIÊN 1 & 2:\n";
echo "  Tổng kiểm thử: " . ($passCount + $failCount) . "\n";
echo "  PASS:          {$passCount}\n";
echo "  FAIL:          {$failCount}\n";
echo "  Tỷ lệ đạt:     " . round(($passCount / ($passCount + $failCount)) * 100, 1) . "%\n";
echo "====================================================================\n";
