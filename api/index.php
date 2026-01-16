<?php
/**
 * API Router - REST API for Mobile App
 * File: api/index.php
 */

// Headers
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load config
require_once dirname(__DIR__) . '/config/config.php';
require_once APPROOT . '/app/core/Database.php';

// Load API helpers
require_once __DIR__ . '/helpers/Response.php';
require_once __DIR__ . '/helpers/Auth.php';

// Get request info
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Parse URI - remove base path and query string
$basePath = '/api';
$uri = parse_url($requestUri, PHP_URL_PATH);
$uri = str_replace(BASEURL . $basePath, '', $uri);
$uri = trim($uri, '/');
$segments = explode('/', $uri);

// Get endpoint and action
$endpoint = $segments[0] ?? '';
$action = $segments[1] ?? '';
$param = $segments[2] ?? null;

// Route to appropriate controller
try {
    switch ($endpoint) {
        case 'auth':
            require_once __DIR__ . '/controllers/AuthController.php';
            $controller = new Api\AuthController();
            break;
            
        case 'guru':
            require_once __DIR__ . '/controllers/GuruController.php';
            $controller = new Api\GuruController();
            break;
            
        case 'siswa':
            require_once __DIR__ . '/controllers/SiswaController.php';
            $controller = new Api\SiswaController();
            break;
            
        case 'waliKelas':
            require_once __DIR__ . '/controllers/WaliKelasController.php';
            $controller = new Api\WaliKelasController();
            break;
            
        case 'notifications':
            require_once __DIR__ . '/controllers/NotificationController.php';
            $controller = new Api\NotificationController();
            break;
            
        default:
            Response::json([
                'status' => false,
                'message' => 'Endpoint not found',
                'endpoints' => [
                    'POST /api/auth/login',
                    'GET /api/guru/dashboard',
                    'GET /api/siswa/dashboard',
                    'GET /api/waliKelas/dashboard'
                ]
            ], 404);
            exit;
    }
    
    // Call controller method
    $controller->handleRequest($requestMethod, $action, $param);
    
} catch (Exception $e) {
    Response::error('Server error: ' . $e->getMessage(), 500);
}
