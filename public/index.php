<?php
/**
 * TechStore - Front Controller
 * Document root: /public
 * Routes requests to API router or serves front-end pages
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// If request is root, redirect to customer homepage
if ($uri === '/' || $uri === '/index.php') {
    header('Location: /pages/index.html');
    exit;
}

// Route API requests to API router
if (str_starts_with($uri, '/api/')) {
    $routesFile = __DIR__ . '/../api/routes.php';
    if (file_exists($routesFile)) {
        require_once $routesFile;
        exit;
    }
}

// For PHP built-in web server: return false if static file exists
$filePath = __DIR__ . $uri;
if (file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// Fallback 404
http_response_code(404);
echo json_encode([
    'success' => false,
    'error' => [
        'code' => 'NOT_FOUND',
        'message' => 'Resource not found'
    ]
]);
