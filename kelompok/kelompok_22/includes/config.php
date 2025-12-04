<?php
/**
 * Database Configuration untuk SiPaMaLi
 * Tugas Besar Praktikum Pemrograman Web 2025
 * Kelompok 22
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Sesuaikan dengan password MySQL Anda
define('DB_NAME', 'sipamali_db');

// Konfigurasi Upload
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Konfigurasi Aplikasi
define('BASE_URL', 'http://localhost/kelompok_22/'); // Sesuaikan dengan path Anda

// Error Reporting (Development mode)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

/**
 * Fungsi untuk koneksi database
 * @return mysqli
 */
function getDBConnection() {
    static $connection = null;
    
    if ($connection === null) {
        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($connection->connect_error) {
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $connection->connect_error
            ]));
        }
        
        $connection->set_charset('utf8mb4');
    }
    
    return $connection;
}

/**
 * Fungsi untuk sanitasi input
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Fungsi untuk response JSON
 * @param bool $success
 * @param mixed $data
 * @param string $message
 * @param int $code
 */
function jsonResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Fungsi untuk generate Report ID
 * @return string
 */
function generateReportId() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(report_id, 5) AS UNSIGNED)) as max_num FROM reports");
    $row = $result->fetch_assoc();
    $nextNum = ($row['max_num'] ?? 0) + 1;
    return 'RPT-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
}

/**
 * Fungsi untuk validasi file upload
 * @param array $file
 * @return array
 */
function validateUploadedFile($file) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['valid' => false, 'message' => 'No file uploaded'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'message' => 'Upload error: ' . $file['error']];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'message' => 'File too large (max 5MB)'];
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['valid' => false, 'message' => 'Invalid file type. Allowed: jpg, jpeg, png, gif'];
    }
    
    // Validasi tipe MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mimeType, $allowedMimes)) {
        return ['valid' => false, 'message' => 'Invalid file format'];
    }
    
    return ['valid' => true];
}

// Buat folder upload jika belum ada
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// CORS Headers (jika diperlukan untuk development)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
