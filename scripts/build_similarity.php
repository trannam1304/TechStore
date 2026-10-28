<?php
/**
 * TechStore - Scheduled Background Task (SYS02)
 * Script CLI: Tính toán ma trận độ tương đồng sản phẩm (Cosine Similarity & Apriori)
 * Quy tắc nghiệp vụ: BR10 & BR11 & FR09.04
 * Chạy dòng lệnh: php scripts/build_similarity.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/models/ProductModel.php';

use Config\Database;

echo "====================================================================\n";
echo " TECHSTORE RECOMMENDATION ENGINE - MATRIX BUILDER (SYS02)\n";
echo " Task ID: SYS02 | BR10 (Cosine 5D) & BR11 (Co-occurrence Apriori)\n";
echo " Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "====================================================================\n\n";

try {
    $pdo = Database::getConnection();
} catch (\Throwable $e) {
    fwrite(STDERR, "[ERROR] Không thể kết nối cơ sở dữ liệu: " . $e->getMessage() . "\n");
    exit(1);
}

// 1. Lấy toàn bộ sản phẩm active
$stmt = $pdo->query("SELECT id, name, category_id, brand_id, price, ram, rom, rating, view_count, status FROM products WHERE status = 'active' ORDER BY id ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalProducts = count($products);

echo "[1/4] Đã nạp {$totalProducts} sản phẩm đang kinh doanh từ CSDL.\n";

if ($totalProducts < 2) {
    echo "[WARN] Số lượng sản phẩm < 2, không thể tính toán ma trận tương đồng.\n";
    exit(0);
}

// Hàm trích xuất dung lượng số nguyên (GB)
function parseStorageGb(?string $val): int {
    if (!$val) return 0;
    $v = strtoupper(trim($val));
    if (str_contains($v, 'TB')) {
        $num = (float)preg_replace('/[^0-9.]/', '', $v);
        return (int)($num * 1024);
    }
    return (int)preg_replace('/[^0-9]/', '', $v);
}

// 2. Xây dựng vector đặc trưng 5 chiều (BR10)
// Dim 1: Category, Dim 2: Brand, Dim 3: Price, Dim 4: RAM, Dim 5: ROM
$vectors = [];
foreach ($products as $p) {
    $id = (int)$p['id'];
    $vectors[$id] = [
        'category_id' => (int)$p['category_id'],
        'brand_id'    => (int)$p['brand_id'],
        'price'       => (float)$p['price'],
        'ram_gb'      => parseStorageGb($p['ram'] ?? ''),
        'rom_gb'      => parseStorageGb($p['rom'] ?? ''),
    ];
}

// 3. Tính ma trận Cosine Similarity theo chuẩn 5 thuộc tính (BR10)
echo "[2/4] Đang tính toán ma trận tương đồng Content-based Cosine (5D)...\n";

$cosinePairs = [];
$upsertSimStmt = $pdo->prepare("
    INSERT INTO product_similarity (product_id, similar_product_id, score, algorithm, created_at)
    VALUES (?, ?, ?, 'cosine', NOW())
    ON DUPLICATE KEY UPDATE score = VALUES(score), created_at = NOW()
");

$cosineCount = 0;
foreach ($products as $p1) {
    $id1 = (int)$p1['id'];
    $v1 = $vectors[$id1];

    $candidates = [];
    foreach ($products as $p2) {
        $id2 = (int)$p2['id'];
        if ($id1 === $id2) continue;
        $v2 = $vectors[$id2];

        // Thành phần 1: Danh mục cùng loại (trọng số 0.35)
        $simCat = ($v1['category_id'] === $v2['category_id']) ? 1.0 : 0.0;

        // Thành phần 2: Thương hiệu cùng hãng (trọng số 0.20)
        $simBrand = ($v1['brand_id'] === $v2['brand_id']) ? 1.0 : 0.0;

        // Thành phần 3: Khoảng giá tương đồng (trọng số 0.20)
        $maxPrice = max($v1['price'], $v2['price'], 1.0);
        $simPrice = max(0.0, 1.0 - (abs($v1['price'] - $v2['price']) / $maxPrice));

        // Thành phần 4: RAM tương đồng (trọng số 0.15)
        $maxRam = max($v1['ram_gb'], $v2['ram_gb'], 1);
        $simRam = max(0.0, 1.0 - (abs($v1['ram_gb'] - $v2['ram_gb']) / (float)$maxRam));

        // Thành phần 5: ROM tương đồng (trọng số 0.10)
        $maxRom = max($v1['rom_gb'], $v2['rom_gb'], 1);
        $simRom = max(0.0, 1.0 - (abs($v1['rom_gb'] - $v2['rom_gb']) / (float)$maxRom));

        // Tổng điểm Cosine hỗn hợp 5 chiều
        $cosineScore = (0.35 * $simCat) + (0.20 * $simBrand) + (0.20 * $simPrice) + (0.15 * $simRam) + (0.10 * $simRom);

        // Chuẩn hóa điểm trong khoảng [0.0000, 1.0000]
        $cosineScore = round(max(0.0, min(1.0, $cosineScore)), 4);

        if ($cosineScore >= 0.30) {
            $candidates[] = [
                'target_id' => $id2,
                'score'     => $cosineScore
            ];
        }
    }

    // Sắp xếp lấy tối đa 8 sản phẩm có độ tương đồng cao nhất cho mỗi sản phẩm
    usort($candidates, fn($a, $b) => $b['score'] <=> $a['score']);
    $topCandidates = array_slice($candidates, 0, 8);

    foreach ($topCandidates as $cand) {
        $upsertSimStmt->execute([$id1, $cand['target_id'], $cand['score']]);
        $cosineCount++;
    }
}

echo "   -> Đã lưu {$cosineCount} cặp tương đồng Cosine vào bảng product_similarity.\n";

// 4. Khai phá luật kết hợp giỏ hàng Apriori / Co-occurrence (BR11)
echo "[3/4] Đang khai phá dữ liệu giỏ hàng lịch sử Co-occurrence (FBT)...\n";

$upsertAprioriStmt = $pdo->prepare("
    INSERT INTO product_similarity (product_id, similar_product_id, score, algorithm, created_at)
    VALUES (?, ?, ?, 'apriori', NOW())
    ON DUPLICATE KEY UPDATE score = VALUES(score), created_at = NOW()
");

// Lấy danh sách sản phẩm theo từng đơn hàng đã đặt
$orderItemsStmt = $pdo->query("
    SELECT oi.order_id, oi.product_id 
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status != 'cancelled'
    ORDER BY oi.order_id ASC
");

$ordersBaskets = [];
$productFrequency = [];

while ($row = $orderItemsStmt->fetch(PDO::FETCH_ASSOC)) {
    $oid = (int)$row['order_id'];
    $pid = (int)$row['product_id'];
    $ordersBaskets[$oid][] = $pid;
    $productFrequency[$pid] = ($productFrequency[$pid] ?? 0) + 1;
}

$coOccurrenceCounts = [];
foreach ($ordersBaskets as $basket) {
    $uniqueBasket = array_unique($basket);
    $n = count($uniqueBasket);
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            if ($i === $j) continue;
            $pA = $uniqueBasket[$i];
            $pB = $uniqueBasket[$j];
            $coOccurrenceCounts[$pA][$pB] = ($coOccurrenceCounts[$pA][$pB] ?? 0) + 1;
        }
    }
}

$aprioriCount = 0;
foreach ($coOccurrenceCounts as $pA => $targets) {
    $freqA = $productFrequency[$pA] ?? 1;
    foreach ($targets as $pB => $countAB) {
        // Confidence(A -> B) = Count(A ∩ B) / Count(A)
        $confidence = round($countAB / (float)$freqA, 4);
        $confidence = max(0.0001, min(1.0, $confidence));
        $upsertAprioriStmt->execute([$pA, $pB, $confidence]);
        $aprioriCount++;
    }
}

echo "   -> Đã trích xuất {$aprioriCount} luật kết hợp Apriori từ lịch sử đơn hàng.\n";

// 5. Tổng kết
echo "[4/4] Hoàn tất quá trình xây dựng ma trận gợi ý!\n";
echo "====================================================================\n";
echo " KẾT QUẢ: Cập nhật thành công bảng product_similarity.\n";
echo "  - Cặp Cosine Similarity: {$cosineCount}\n";
echo "  - Cặp Apriori Baskets:   {$aprioriCount}\n";
echo "  - Trạng thái hoàn thành: EXIT CODE 0 (SUCCESS)\n";
echo "====================================================================\n";

exit(0);
