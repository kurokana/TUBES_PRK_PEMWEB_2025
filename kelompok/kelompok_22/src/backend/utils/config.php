<?php
/**
 * Database Configuration untuk SiPaMaLi
 * Tugas Besar Praktikum Pemrograman Web 2025
 * Kelompok 22
 */

// Path Configuration
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(dirname(dirname(__DIR__)))); // kelompok_22/
if (!defined('SRC_PATH')) define('SRC_PATH', ROOT_PATH . '/src');
if (!defined('BACKEND_PATH')) define('BACKEND_PATH', SRC_PATH . '/backend');
if (!defined('FRONTEND_PATH')) define('FRONTEND_PATH', SRC_PATH . '/frontend');

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'dev');
define('DB_PASS', 'DevPass123!');
define('DB_NAME', 'pamali2');

// Konfigurasi Upload - Gunakan satu lokasi upload terpusat
define('UPLOAD_DIR', SRC_PATH . '/uploads/');
define('UPLOAD_URL', '/src/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Konfigurasi Aplikasi
define('BASE_URL', 'http://localhost:8000/');
define('SITE_NAME', 'SiPaMaLi');

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
        $connection = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($connection->connect_error) {
            error_log('Database connection failed: ' . $connection->connect_error);
            
            // Display user-friendly error
            die('
            <!DOCTYPE html>
            <html>
            <head><title>Database Error</title></head>
            <body>
                <h1>Database Connection Error</h1>
                <p>Cannot connect to database. Please check:</p>
                <ul>
                    <li>MySQL/MariaDB is running</li>
                    <li>Database credentials in config.php are correct</li>
                    <li>Database "' . DB_NAME . '" exists</li>
                    <li>User "' . DB_USER . '" has access</li>
                </ul>
                <p><strong>Error:</strong> ' . htmlspecialchars($connection->connect_error) . '</p>
            </body>
            </html>
            ');
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
    // Clear any accidental output (errors, warnings, whitespace) before sending JSON
    if (ob_get_level()) {
        ob_end_clean();
    }
    
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

// config.php (Revisi Fungsi getAllReports)

function getAllReports($statusFilter = null) {
    $db = getDBConnection(); 
    
    $sql = "SELECT 
                r.id, r.report_id, r.user_id, r.category, r.location, r.description,
                r.status, r.priority, r.image_path, r.assigned_to, r.created_at, 
                u.full_name as reported_by
            FROM reports r
            JOIN users u ON r.user_id = u.id";
            
    // Logika Filtering: HANYA tambahkan WHERE jika filter tidak kosong
    if (!empty($statusFilter)) {
        // Penting: Escape string untuk mencegah SQL Injection
        $cleanStatus = $db->real_escape_string($statusFilter);
        $sql .= " WHERE r.status = '$cleanStatus'";
    }
    
    $sql .= " ORDER BY r.created_at DESC"; // Tambahkan ORDER BY setelah WHERE

    $result = $db->query($sql);
    $reports = [];
    
    if ($result === false) {
        // Jika terjadi error pada SQL
        error_log("SQL Error in getAllReports: " . $db->error);
        return [];
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
    }
    
    return $reports; 
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
