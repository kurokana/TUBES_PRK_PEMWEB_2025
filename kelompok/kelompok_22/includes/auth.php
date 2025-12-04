<?php
/**
 * Authentication Handler untuk SiPaMaLi
 * Tugas Besar Praktikum Pemrograman Web 2025
 * Kelompok 22
 */

require_once __DIR__ . '/config.php';

// Start session jika belum
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Login admin
 * @param string $username
 * @param string $password
 * @return array
 */
function loginAdmin($username, $password) {
    $conn = getDBConnection();
    
    // Sanitize input
    $username = sanitizeInput($username);
    
    // Get user dari database
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return [
            'success' => false,
            'message' => 'Username atau password salah'
        ];
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        return [
            'success' => false,
            'message' => 'Username atau password salah'
        ];
    }
    
    // Set session
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_fullname'] = $user['full_name'];
    $_SESSION['login_time'] = time();
    
    // Update last login
    $stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    
    return [
        'success' => true,
        'message' => 'Login berhasil',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name']
        ]
    ];
}

/**
 * Logout admin
 */
function logoutAdmin() {
    session_destroy();
    return [
        'success' => true,
        'message' => 'Logout berhasil'
    ];
}

/**
 * Check apakah admin sudah login
 * @return bool
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Get admin info dari session
 * @return array|null
 */
function getAdminInfo() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'username' => $_SESSION['admin_username'] ?? null,
        'full_name' => $_SESSION['admin_fullname'] ?? null,
        'login_time' => $_SESSION['login_time'] ?? null
    ];
}

/**
 * Require login - redirect jika belum login
 */
function requireLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Redirect jika sudah login
 */
function redirectIfLoggedIn() {
    if (isAdminLoggedIn()) {
        header('Location: admin.php');
        exit;
    }
}

// Handle logout via GET parameter
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'login':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Username dan password harus diisi'
                ]);
                exit;
            }
            
            $result = loginAdmin($username, $password);
            echo json_encode($result);
            exit;
        
        case 'logout':
            $result = logoutAdmin();
            echo json_encode($result);
            exit;
        
        case 'check':
            echo json_encode([
                'success' => true,
                'logged_in' => isAdminLoggedIn(),
                'user' => getAdminInfo()
            ]);
            exit;
        
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            exit;
    }
}
