<?php
/**
 * TechStore - Benchmark Controller (REST vs GraphQL vs SOAP)
 * Phục vụ đối chứng thực nghiệm kiến trúc API (Task T72 & Báo cáo Chương 2, 3)
 * Hiện thực hóa 2 endpoint đối chứng chuẩn:
 *   1. GraphQL: POST /api/v1/graphql (Field Selection, Nested Resolvers, minh chứng chống Over-fetching / Under-fetching)
 *   2. SOAP:    POST /api/v1/soap    (SOAP 1.1 XML Envelope, minh chứng Payload Overhead)
 */

namespace Api\Controllers;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/BrandModel.php';
require_once __DIR__ . '/../models/ReviewModel.php';
require_once __DIR__ . '/../helpers/Response.php';

use Api\Models\ProductModel;
use Api\Models\CategoryModel;
use Api\Models\BrandModel;
use Api\Models\ReviewModel;
use Api\Helpers\Response;
use Exception;

class BenchmarkController extends BaseController {

    // =========================================================================
    // 1. GRAPHQL ENDPOINT (POST /api/v1/graphql)
    // =========================================================================

    /**
     * Tiếp nhận và phân giải truy vấn GraphQL
     */
    public static function graphql(?array $body = null): void {
        header('Content-Type: application/json; charset=utf-8');
        header('X-Benchmark-Engine: TechStore-GraphQL-Mini');

        // Đọc query từ JSON body hoặc POST payload
        if ($body === null) {
            $rawInput = file_get_contents('php://input');
            $body = json_decode($rawInput, true) ?: [];
        }

        // Hỗ trợ cả trường query từ body hoặc $_GET['query']
        $query = $body['query'] ?? ($_GET['query'] ?? null);

        if (empty($query) || !is_string($query)) {
            http_response_code(400);
            echo json_encode([
                'errors' => [
                    ['message' => 'GraphQL query không được để trống. Hãy cung cấp trường "query" trong JSON body.']
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        try {
            $parsedQuery = self::parseGraphQL($query);
            $result = self::resolveGraphQL($parsedQuery);

            http_response_code(200);
            echo json_encode([
                'data' => $result
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'errors' => [
                    ['message' => $e->getMessage()]
                ]
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }

    /**
     * Parser cú pháp GraphQL tối giản (AST Tokenizer)
     */
    private static function parseGraphQL(string $source): array {
        // Loại bỏ ghi chú kiểu # comment
        $source = preg_replace('/#.*$/m', '', $source);

        // Tách các token cơ bản
        preg_match_all('/([A-Za-z_][A-Za-z0-9_]*)|([0-9]+(?:\.[0-9]+)?)|"([^"]*)"|([{}(),:])|(\s+)/', $source, $matches, PREG_SET_ORDER);

        $tokens = [];
        foreach ($matches as $m) {
            if (!empty($m[5])) continue; // Bỏ qua khoảng trắng
            if (!empty($m[1])) {
                $tokens[] = ['type' => 'NAME', 'value' => $m[1]];
            } elseif (!empty($m[2])) {
                $tokens[] = ['type' => 'NUMBER', 'value' => $m[2] + 0];
            } elseif (isset($m[3]) && $m[3] !== '') {
                $tokens[] = ['type' => 'STRING', 'value' => $m[3]];
            } elseif (!empty($m[4])) {
                $tokens[] = ['type' => 'PUNCT', 'value' => $m[4]];
            }
        }

        $pos = 0;
        // Bỏ qua từ khóa 'query' và tên operation tùy chọn
        if (isset($tokens[$pos]) && $tokens[$pos]['type'] === 'NAME' && $tokens[$pos]['value'] === 'query') {
            $pos++;
            if (isset($tokens[$pos]) && $tokens[$pos]['type'] === 'NAME') {
                $pos++; // Bỏ qua tên operation (ví dụ GetProducts)
            }
        }

        if (!isset($tokens[$pos]) || $tokens[$pos]['value'] !== '{') {
            throw new Exception("Cú pháp truy vấn GraphQL không hợp lệ: mong đợi ký tự mở '{'");
        }
        $pos++; // Bỏ qua dấu '{' đầu tiên

        return self::parseSelectionSet($tokens, $pos);
    }

    /**
     * Phân tích selection set đệ quy
     */
    private static function parseSelectionSet(array &$tokens, int &$pos): array {
        $selection = [];

        while ($pos < count($tokens)) {
            $token = $tokens[$pos];
            if ($token['value'] === '}') {
                $pos++;
                break;
            }
            if ($token['value'] === ',') {
                $pos++;
                continue;
            }

            if ($token['type'] !== 'NAME') {
                throw new Exception("Tên trường không hợp lệ tại vị trí {$pos}: " . ($token['value'] ?? 'EOF'));
            }

            $fieldName = $token['value'];
            $pos++;

            // Phân tích đối số bên trong (arg1: val1, arg2: val2)
            $args = [];
            if (isset($tokens[$pos]) && $tokens[$pos]['value'] === '(') {
                $pos++;
                while ($pos < count($tokens) && $tokens[$pos]['value'] !== ')') {
                    if ($tokens[$pos]['value'] === ',') {
                        $pos++;
                        continue;
                    }
                    $argName = $tokens[$pos]['value'];
                    $pos++;
                    if (!isset($tokens[$pos]) || $tokens[$pos]['value'] !== ':') {
                        throw new Exception("Mong đợi dấu ':' sau tham số {$argName}");
                    }
                    $pos++;
                    $argVal = $tokens[$pos]['value'] ?? null;
                    $pos++;
                    $args[$argName] = $argVal;
                }
                if (isset($tokens[$pos]) && $tokens[$pos]['value'] === ')') {
                    $pos++;
                }
            }

            // Phân tích trường con đệ quy nếu có { ... }
            $subSelection = null;
            if (isset($tokens[$pos]) && $tokens[$pos]['value'] === '{') {
                $pos++;
                $subSelection = self::parseSelectionSet($tokens, $pos);
            }

            $selection[$fieldName] = [
                'name'   => $fieldName,
                'args'   => $args,
                'fields' => $subSelection
            ];
        }

        return $selection;
    }

    /**
     * Bộ phân giải (Resolvers) thực thi dữ liệu từ MySQL
     */
    private static function resolveGraphQL(array $parsedQuery): array {
        $data = [];

        foreach ($parsedQuery as $rootName => $fieldConfig) {
            $args = $fieldConfig['args'] ?? [];
            $subFields = $fieldConfig['fields'] ?? [];

            switch ($rootName) {
                case 'products':
                    $filters = [];
                    if (isset($args['limit'])) $filters['limit'] = (int)$args['limit'];
                    if (isset($args['page'])) $filters['page'] = (int)$args['page'];
                    if (isset($args['category_id'])) $filters['category_id'] = (int)$args['category_id'];
                    if (isset($args['brand_id'])) $filters['brand_id'] = (int)$args['brand_id'];
                    if (isset($args['search'])) $filters['search'] = (string)$args['search'];
                    if (isset($args['sort'])) $filters['sort'] = (string)$args['sort'];

                    $res = ProductModel::getAll($filters);
                    $items = $res['items'] ?? [];

                    $resolvedProducts = [];
                    foreach ($items as $item) {
                        $resolvedProducts[] = self::filterFields($item, $subFields);
                    }
                    $data['products'] = $resolvedProducts;
                    break;

                case 'product':
                    $id = (int)($args['id'] ?? 0);
                    if ($id <= 0) {
                        throw new Exception("Truy vấn 'product' yêu cầu truyền đối số hợp lệ: product(id: Int)");
                    }
                    $product = ProductModel::findById($id);
                    if (!$product) {
                        $data['product'] = null;
                    } else {
                        $data['product'] = self::filterFields($product, $subFields);
                    }
                    break;

                case 'categories':
                    $cats = CategoryModel::getAll();
                    $resolvedCats = [];
                    foreach ($cats as $cat) {
                        $filtered = [];
                        foreach ($subFields as $k => $cfg) {
                            if (array_key_exists($k, $cat)) {
                                $filtered[$k] = $cat[$k];
                            }
                        }
                        $resolvedCats[] = !empty($filtered) ? $filtered : $cat;
                    }
                    $data['categories'] = $resolvedCats;
                    break;

                default:
                    throw new Exception("Không hỗ trợ truy vấn trường gốc '{$rootName}' trên schema GraphQL.");
            }
        }

        return $data;
    }

    /**
     * Lọc chỉ trả về các trường được Client chọn (Field Selection), tránh Over-fetching
     */
    private static function filterFields(array $product, array $requestedFields): array {
        if (empty($requestedFields)) {
            return $product;
        }

        $filtered = [];
        foreach ($requestedFields as $fieldKey => $fieldMeta) {
            $subFields = $fieldMeta['fields'];

            if ($fieldKey === 'category') {
                $catData = [
                    'id'   => $product['category_id'] ?? null,
                    'name' => $product['category_name'] ?? null,
                    'slug' => $product['category_slug'] ?? null
                ];
                if (!empty($subFields)) {
                    $catFiltered = [];
                    foreach ($subFields as $subK => $v) {
                        if (array_key_exists($subK, $catData)) {
                            $catFiltered[$subK] = $catData[$subK];
                        }
                    }
                    $filtered['category'] = $catFiltered;
                } else {
                    $filtered['category'] = $catData;
                }
                continue;
            }

            if ($fieldKey === 'brand') {
                $brandData = [
                    'id'   => $product['brand_id'] ?? null,
                    'name' => $product['brand_name'] ?? null,
                    'slug' => $product['brand_slug'] ?? null
                ];
                if (!empty($subFields)) {
                    $brandFiltered = [];
                    foreach ($subFields as $subK => $v) {
                        if (array_key_exists($subK, $brandData)) {
                            $brandFiltered[$subK] = $brandData[$subK];
                        }
                    }
                    $filtered['brand'] = $brandFiltered;
                } else {
                    $filtered['brand'] = $brandData;
                }
                continue;
            }

            if ($fieldKey === 'specs') {
                $specs = $product['specs'] ?? [];
                if (empty($specs) && !empty($product['id'])) {
                    $fullProd = ProductModel::findById((int)$product['id']);
                    $specs = $fullProd['specs'] ?? [];
                }
                if (!empty($subFields)) {
                    $filteredSpecs = [];
                    foreach ($specs as $spec) {
                        $specItem = [];
                        foreach ($subFields as $subK => $v) {
                            if (array_key_exists($subK, $spec)) {
                                $specItem[$subK] = $spec[$subK];
                            }
                        }
                        $filteredSpecs[] = $specItem;
                    }
                    $filtered['specs'] = $filteredSpecs;
                } else {
                    $filtered['specs'] = $specs;
                }
                continue;
            }

            if ($fieldKey === 'reviews') {
                $reviews = ReviewModel::getByProductId((int)($product['id'] ?? 0));
                if (!empty($subFields)) {
                    $filteredReviews = [];
                    foreach ($reviews as $rev) {
                        $revItem = [];
                        foreach ($subFields as $subK => $v) {
                            if (array_key_exists($subK, $rev)) {
                                $revItem[$subK] = $rev[$subK];
                            }
                        }
                        $filteredReviews[] = $revItem;
                    }
                    $filtered['reviews'] = $filteredReviews;
                } else {
                    $filtered['reviews'] = $reviews;
                }
                continue;
            }

            if (array_key_exists($fieldKey, $product)) {
                $filtered[$fieldKey] = $product[$fieldKey];
            }
        }

        return $filtered;
    }

    // =========================================================================
    // 2. SOAP 1.1 / XML ENDPOINT (POST /api/v1/soap)
    // =========================================================================

    /**
     * Tiếp nhận và đóng gói dữ liệu trong phong bì SOAP Envelope XML
     */
    public static function soap(): void {
        header('Content-Type: text/xml; charset=utf-8');
        header('X-Benchmark-Engine: TechStore-SOAP-1.1');

        $rawXml = file_get_contents('php://input');
        $operation = 'GetProductsRequest';
        $limit = 10;
        $id = null;

        // Phân tích tham số từ phong bì XML nếu được gửi lên
        if (!empty($rawXml)) {
            // Tách nhanh limit hoặc id từ XML bằng regex đơn giản
            if (preg_match('/<.*:?limit>(\d+)<\/.*:?limit>/i', $rawXml, $m)) {
                $limit = max(1, min(100, (int)$m[1]));
            }
            if (preg_match('/<.*:?id>(\d+)<\/.*:?id>/i', $rawXml, $m)) {
                $id = (int)$m[1];
                $operation = 'GetProductDetailRequest';
            }
            if (stripos($rawXml, 'GetProductDetail') !== false) {
                $operation = 'GetProductDetailRequest';
            }
        }

        $xmlOutput = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xmlOutput .= '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tech="https://techstore.local/soap">' . "\n";
        $xmlOutput .= '  <soapenv:Header>' . "\n";
        $xmlOutput .= '    <tech:ServerTimestamp>' . gmdate('Y-m-d\TH:i:s\Z') . '</tech:ServerTimestamp>' . "\n";
        $xmlOutput .= '    <tech:ServiceProtocol>SOAP 1.1 / WSDL Profile</tech:ServiceProtocol>' . "\n";
        $xmlOutput .= '  </soapenv:Header>' . "\n";
        $xmlOutput .= '  <soapenv:Body>' . "\n";

        if ($operation === 'GetProductDetailRequest' && $id !== null) {
            $product = ProductModel::findById($id);
            if (!$product) {
                $xmlOutput .= '    <soapenv:Fault>' . "\n";
                $xmlOutput .= '      <faultcode>soapenv:Client</faultcode>' . "\n";
                $xmlOutput .= '      <faultstring>Không tìm thấy sản phẩm yêu cầu</faultstring>' . "\n";
                $xmlOutput .= '    </soapenv:Fault>' . "\n";
            } else {
                $xmlOutput .= '    <tech:GetProductDetailResponse>' . "\n";
                $xmlOutput .= self::formatProductXml($product, 6);
                $xmlOutput .= '    </tech:GetProductDetailResponse>' . "\n";
            }
        } else {
            // Danh sách sản phẩm (Mặc định)
            $res = ProductModel::getAll(['limit' => $limit]);
            $items = $res['items'] ?? [];
            $total = $res['pagination']['total_items'] ?? count($items);

            $xmlOutput .= '    <tech:GetProductsResponse>' . "\n";
            $xmlOutput .= '      <tech:status>SUCCESS</tech:status>' . "\n";
            $xmlOutput .= '      <tech:totalItems>' . $total . '</tech:totalItems>' . "\n";
            $xmlOutput .= '      <tech:limit>' . $limit . '</tech:limit>' . "\n";
            $xmlOutput .= '      <tech:products>' . "\n";

            foreach ($items as $item) {
                $xmlOutput .= self::formatProductXml($item, 8);
            }

            $xmlOutput .= '      </tech:products>' . "\n";
            $xmlOutput .= '    </tech:GetProductsResponse>' . "\n";
        }

        $xmlOutput .= '  </soapenv:Body>' . "\n";
        $xmlOutput .= '</soapenv:Envelope>';

        http_response_code(200);
        echo $xmlOutput;
        exit;
    }

    /**
     * Chuyển mảng thông tin sản phẩm thành thẻ XML chuẩn có namespace
     */
    private static function formatProductXml(array $p, int $indent = 6): string {
        $sp = str_repeat(' ', $indent);
        $spChild = str_repeat(' ', $indent + 2);

        $name = htmlspecialchars($p['name'] ?? '', ENT_XML1, 'UTF-8');
        $slug = htmlspecialchars($p['slug'] ?? '', ENT_XML1, 'UTF-8');
        $catName = htmlspecialchars($p['category_name'] ?? '', ENT_XML1, 'UTF-8');
        $brandName = htmlspecialchars($p['brand_name'] ?? '', ENT_XML1, 'UTF-8');
        $thumb = htmlspecialchars($p['thumbnail_url'] ?? '', ENT_XML1, 'UTF-8');

        $xml = "{$sp}<tech:product>\n";
        $xml .= "{$spChild}<tech:id>" . (int)($p['id'] ?? 0) . "</tech:id>\n";
        $xml .= "{$spChild}<tech:name>{$name}</tech:name>\n";
        $xml .= "{$spChild}<tech:slug>{$slug}</tech:slug>\n";
        $xml .= "{$spChild}<tech:price>" . (int)($p['price'] ?? 0) . "</tech:price>\n";
        $xml .= "{$spChild}<tech:original_price>" . (int)($p['original_price'] ?? 0) . "</tech:original_price>\n";
        $xml .= "{$spChild}<tech:stock>" . (int)($p['stock'] ?? 0) . "</tech:stock>\n";
        $xml .= "{$spChild}<tech:category_id>" . (int)($p['category_id'] ?? 0) . "</tech:category_id>\n";
        $xml .= "{$spChild}<tech:category_name>{$catName}</tech:category_name>\n";
        $xml .= "{$spChild}<tech:brand_id>" . (int)($p['brand_id'] ?? 0) . "</tech:brand_id>\n";
        $xml .= "{$spChild}<tech:brand_name>{$brandName}</tech:brand_name>\n";
        $xml .= "{$spChild}<tech:thumbnail_url>{$thumb}</tech:thumbnail_url>\n";
        $xml .= "{$spChild}<tech:rating_average>" . ($p['rating_average'] ?? 0) . "</tech:rating_average>\n";
        $xml .= "{$spChild}<tech:review_count>" . (int)($p['review_count'] ?? 0) . "</tech:review_count>\n";
        $xml .= "{$spChild}<tech:featured>" . ((!empty($p['featured'])) ? 1 : 0) . "</tech:featured>\n";
        $xml .= "{$sp}</tech:product>\n";

        return $xml;
    }
}

