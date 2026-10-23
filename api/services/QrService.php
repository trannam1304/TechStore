<?php
/**
 * TechStore - VietQR Payment Service
 * Phụ trách: Hữu Nam (Day 2: T26)
 * Tích hợp VietQR chuẩn NAPAS247
 */

namespace Api\Services;

class QrService {
    /**
     * Sinh mã VietQR động theo chuẩn Napas247
     */
    public static function generate(string $bank, string $account, string $accountName, int $amount, string $orderCode): string {
        $encodedDesc = urlencode($orderCode);
        $encodedName = urlencode($accountName);
        return "https://img.vietqr.io/image/{$bank}-{$account}-compact2.png?amount={$amount}&addInfo={$encodedDesc}&accountName={$encodedName}";
    }
}
