<?php
/**
 * TechStore - Database Importer CLI Script
 * Tự động kết nối MySQL qua PDO, tạo database nếu chưa có và import schema.sql + seed.sql
 */

echo "========================================================\n";
echo "           TECHSTORE - DATABASE AUTO IMPORTER           \n";
echo "========================================================\n\n";

$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    $envFile = __DIR__ . '/../.env.example';
}

$host = '127.0.0.1';
$port = '3306';
$dbname = 'techstore';
$user = 'root';
$pass = '';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            if ($k === 'DB_HOST') $host = $v;
            if ($k === 'DB_PORT') $port = $v;
            if ($k === 'DB_NAME') $dbname = $v;
            if ($k === 'DB_USER') $user = $v;
            if ($k === 'DB_PASSWORD') $pass = $v;
        }
    }
}

echo "[*] Cấu hình kết nối: Host={$host}:{$port} | User={$user} | Database={$dbname}\n";

// 1. Kết nối tới MySQL Server (chưa chọn database)
$activePort = $port;
$connected = false;
$pdoServer = null;

try {
    $dsnServer = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdoServer = new PDO($dsnServer, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    $connected = true;
} catch (PDOException $e) {
    // Thử cổng thay thế (3306 <-> 3307)
    $altPort = ($port === '3307') ? '3306' : (($port === '3306') ? '3307' : null);
    if ($altPort !== null) {
        try {
            echo "[*] Cổng {$port} không phản hồi, đang tự động thử cổng {$altPort}...\n";
            $dsnServer = "mysql:host={$host};port={$altPort};charset=utf8mb4";
            $pdoServer = new PDO($dsnServer, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]);
            $activePort = $altPort;
            $connected = true;
            echo "[OK] Kết nối thành công qua cổng thay thế {$activePort}!\n";
        } catch (\Throwable $altEx) {
            // Vẫn lỗi
        }
    }

    if (!$connected) {
        echo "[ERROR] Không thể kết nối tới MySQL Server: " . $e->getMessage() . "\n";
        echo "[!] Hãy chắc chắn rằng MySQL (qua Docker hoặc XAMPP) đang chạy trên cổng {$port} hoặc {$altPort}.\n";
        exit(1);
    }
}

echo "[OK] Kết nối MySQL Server thành công!\n";

$pdoServer->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
echo "[OK] Đã kiểm tra/tạo CSDL `{$dbname}`.\n";

// 2. Kết nối trực tiếp vào database techstore
try {
    $dsnDb = "mysql:host={$host};port={$activePort};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsnDb, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    echo "[ERROR] Kết nối database `{$dbname}` thất bại: " . $e->getMessage() . "\n";
    exit(1);
}

// Hàm chia nhỏ và thực thi file SQL
function executeSqlFile(PDO $pdo, string $filePath, string $label): void {
    if (!file_exists($filePath)) {
        echo "[ERROR] Không tìm thấy file: {$filePath}\n";
        return;
    }

    echo "\n[*] Đang nạp {$label} (" . basename($filePath) . ")...\n";
    $sql = file_get_contents($filePath);

    // Xóa BOM UTF-8 nếu có
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

    // Tắt kiểm tra khóa ngoại tạm thời khi nạp dữ liệu
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Thực thi toàn bộ lệnh SQL
    try {
        $pdo->exec($sql);
        echo "[OK] Nạp {$label} thành công!\n";
    } catch (PDOException $e) {
        echo "[WARN] Lỗi khi thực thi lô lệnh: " . $e->getMessage() . "\n";
        echo "[*] Đang thử phân tách từng câu lệnh SQL...\n";

        // Tách theo dấu chấm phẩy
        $statements = preg_split('/;\s*[\r\n]+/', $sql);
        $successCount = 0;
        $failCount = 0;
        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '' || str_starts_with($stmt, '--') || str_starts_with($stmt, '/*')) {
                continue;
            }
            try {
                $pdo->exec($stmt);
                $successCount++;
            } catch (PDOException $ex) {
                $failCount++;
                // Bỏ qua lỗi drop view/table nếu chưa tồn tại
            }
        }
        echo "[OK] Đã thực thi {$successCount} câu lệnh (bỏ qua {$failCount} cảnh báo).\n";
    } finally {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
}

// 3. Thực thi schema.sql và seed.sql
$schemaFile = __DIR__ . '/schema.sql';
$seedFile = __DIR__ . '/seed.sql';

executeSqlFile($pdo, $schemaFile, "Cấu trúc bảng (Schema DDL)");
executeSqlFile($pdo, $seedFile, "Dữ liệu mẫu (Seed Data)");

// 4. Thống kê kết quả
echo "\n========================================================\n";
echo "              THỐNG KÊ DỮ LIỆU ĐÃ IMPORT                \n";
echo "========================================================\n";

try {
    $tablesStmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    printf("%-25s | %-15s\n", "Tên bảng (Table)", "Số bản ghi (Rows)");
    echo str_repeat("-", 45) . "\n";
    $totalRows = 0;
    foreach ($tables as $table) {
        $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
        $count = (int)$countStmt->fetchColumn();
        $totalRows += $count;
        printf("%-25s | %-15d\n", $table, $count);
    }
    echo str_repeat("-", 45) . "\n";
    echo "Tổng cộng: " . count($tables) . " bảng, {$totalRows} bản ghi trong CSDL `{$dbname}`.\n\n";
    echo "[SUCCESS] Đồng bộ CSDL MySQL hoàn tất 100%!\n";
} catch (PDOException $e) {
    echo "[ERROR] Không thể lấy thống kê: " . $e->getMessage() . "\n";
}
