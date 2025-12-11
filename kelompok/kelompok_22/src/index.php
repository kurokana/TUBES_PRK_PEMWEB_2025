<?php
/**
 * SiPaMaLi - Front Controller / Router
 * Sistem Pelaporan & Pemantauan Masalah Lingkungan
 * Kelompok 22 - Praktikum Pemrograman Web 2025
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the request URI and remove query string
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request_uri = rtrim($request_uri, '/');

// Handle empty path - check if user is logged in
if (empty($request_uri) || $request_uri === '/') {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $role = $_SESSION['role'] ?? 'pelapor';
        
        switch ($role) {
            case 'super_admin':
                header('Location: /super_admin.php');
                exit;
            case 'admin':
                header('Location: /admin.php');
                exit;
            case 'petugas':
                header('Location: /petugas.php');
                exit;
            default:
                header('Location: /pelapor.php');
                exit;
        }
    } else {
        // Show landing page for visitors
        require __DIR__ . '/frontend/pages/home.php';
        exit;
    }
}

// Define routes mapping
$routes = [
    '/home.php' => '/frontend/pages/home.php',
    '/login.php' => '/frontend/pages/login.php',
    '/registrasi.php' => '/frontend/pages/registrasi.php',
    '/lupa_password.php' => '/frontend/pages/lupa_password.php',
    '/pelapor.php' => '/frontend/pages/pelapor.php',
    '/riwayat.php' => '/frontend/pages/riwayat.php',
    '/profile.php' => '/frontend/pages/profile.php',
    '/admin.php' => '/backend/controllers/admin.php',
    '/petugas.php' => '/backend/controllers/petugas.php',
    '/detail_tugas.php' => '/backend/controllers/detail_tugas.php',
    '/super_admin.php' => '/backend/controllers/super_admin.php',
    '/logout.php' => '/backend/controllers/logout.php',
    '/api.php' => '/backend/controllers/api.php',
    '/test_upload.html' => '/test_upload.html',
    '/test_upload_warga.php' => '/test_upload_warga.php',
];

// Check if the route exists
if (isset($routes[$request_uri])) {
    $file = __DIR__ . $routes[$request_uri];
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Serve static files from /frontend/assets or /src/frontend/assets
if (strpos($request_uri, '/frontend/assets/') === 0 || strpos($request_uri, '/src/frontend/assets/') === 0) {
    // Handle both /frontend/assets and /src/frontend/assets
    if (strpos($request_uri, '/src/frontend/assets/') === 0) {
        $relative_path = substr($request_uri, 4); // Remove '/src'
    } else {
        $relative_path = $request_uri;
    }
    
    $file = __DIR__ . $relative_path;
    if (file_exists($file)) {
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
        ];
        
        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension]);
            readfile($file);
            exit;
        }
    }
}

// Serve uploaded files from /uploads directory
if (strpos($request_uri, '/uploads/') === 0) {
    // Old path for compatibility
    $file = __DIR__ . $request_uri;
    if (file_exists($file)) {
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

// Serve uploaded files from /src/uploads directory
if (strpos($request_uri, '/src/uploads/') === 0) {
    $relative_path = substr($request_uri, 4); // Remove '/src'
    $file = __DIR__ . $relative_path;
    if (file_exists($file)) {
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

// 404 - Not Found
http_response_code(404);
echo '<!DOCTYPE html>
<html>
<head>
    <title>404 - Halaman Tidak Ditemukan</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #e11d48; }
    </style>
</head>
<body>
    <h1>404 - Halaman Tidak Ditemukan</h1>
    <p>Maaf, halaman yang Anda cari tidak ditemukan.</p>
    <a href="/">Kembali ke Beranda</a>
</body>
</html>';
exit;
