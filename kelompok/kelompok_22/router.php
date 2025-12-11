<?php
/**
 * SiPaMaLi - Router
 * Handles routing for API and other endpoints
 */

// Debug: Log every request
file_put_contents('/tmp/router_debug.log', "\n\n=== ROUTER.PHP ===\n", FILE_APPEND);
file_put_contents('/tmp/router_debug.log', "Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents('/tmp/router_debug.log', "URI: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
file_put_contents('/tmp/router_debug.log', "Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);

// Get the request URI and remove query string
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = rtrim($request_uri, '/');

// Define routes mapping
$routes = [
    '/api.php' => '/src/backend/controllers/api.php',
    '/login.php' => '/src/frontend/pages/login.php',
    '/registrasi.php' => '/src/frontend/pages/registrasi.php',
    '/pelapor.php' => '/src/frontend/pages/pelapor.php',
    '/riwayat.php' => '/src/frontend/pages/riwayat.php',
    '/admin.php' => '/src/backend/controllers/admin.php',
    '/petugas.php' => '/src/backend/controllers/petugas.php',
    '/super_admin.php' => '/src/backend/controllers/super_admin.php',
    '/logout.php' => '/src/backend/controllers/logout.php',
    '/test_upload.html' => '/test_upload.html',
];

// Check if the route exists
if (isset($routes[$request_uri])) {
    $file = __DIR__ . $routes[$request_uri];
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Serve uploaded files from /uploads directory
if (strpos($request_uri, '/uploads/') === 0) {
    $file = __DIR__ . $request_uri;
    if (file_exists($file)) {
        // Set appropriate content type
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime_types = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension]);
            readfile($file);
            exit;
        }
    }
}

// If no route matched and not root, return false to let PHP built-in server handle it
return false;
