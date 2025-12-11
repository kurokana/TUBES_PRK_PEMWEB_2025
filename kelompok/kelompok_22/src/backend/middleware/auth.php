<?php
require_once __DIR__ . '/../utils/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loginUser($username, $password) {
    $conn = getDBConnection();
    
    $username = sanitizeInput($username);
    
    $stmt = $conn->prepare("SELECT id, username, full_name, role, email, phone, password_hash FROM users WHERE username = ? AND is_active = 1"); 
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return [
            'success' => false,
            'message' => 'Username tidak terdaftar atau akun tidak aktif.'
        ];
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($password, $user['password_hash'])) {
        return [
            'success' => false,
            'message' => 'Username atau password salah.'
        ];
    }
    
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['phone'] = $user['phone'];
    $_SESSION['login_time'] = time();
    
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?"); 
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    
    // Audit log
    logAudit($user['id'], 'login', NULL, NULL, 'User logged in: ' . $username);
    
    return [
        'success' => true,
        'message' => 'Login berhasil',
        'role' => $user['role'], 
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ]
    ];
}

function registerUser($data) {
    $conn = getDBConnection();
    
    $username = sanitizeInput($data['username']);
    $email = sanitizeInput($data['email']);
    $full_name = sanitizeInput($data['full_name']);
    $phone = sanitizeInput($data['phone'] ?? '');
    $password = $data['password'];
    
    // Validasi
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        return ['success' => false, 'message' => 'Semua field wajib diisi'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password minimal 6 karakter'];
    }
    
    // Cek username/email sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param('ss', $username, $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Username atau email sudah terdaftar'];
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user dengan default role 'warga'
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, phone, role, is_active, email_verified) VALUES (?, ?, ?, ?, ?, 'warga', 1, 0)");
    $stmt->bind_param('sssss', $username, $email, $password_hash, $full_name, $phone);
    
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login.'];
    }
    
    return ['success' => false, 'message' => 'Registrasi gagal: ' . $conn->error];
}

function logAudit($user_id, $action_type, $target_type, $target_id, $description, $old_value = null, $new_value = null) {
    $conn = getDBConnection();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action_type, target_type, target_id, description, old_value, new_value, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issssssss', $user_id, $action_type, $target_type, $target_id, $description, $old_value, $new_value, $ip_address, $user_agent);
    $stmt->execute();
    $stmt->close();
}

function logoutUser() {
    session_destroy();
    return [
        'success' => true,
        'message' => 'Logout berhasil'
    ];
}

function isUserLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function getUserInfo() {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT id, username, full_name, email, phone, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}

function requireLogin() {
    if (!isUserLoggedIn()) {
        header('Location: /login.php');
        exit;
    }
}

function requireRole($requiredRoles) {
    requireLogin();

    $currentUserRole = $_SESSION['role'] ?? 'guest';
    
    if (is_string($requiredRoles)) {
        $requiredRoles = [$requiredRoles];
    }
    
    if (!in_array($currentUserRole, $requiredRoles)) {
        if ($currentUserRole === 'super_admin') {
            header("Location: super_admin.php"); 
        } elseif ($currentUserRole === 'admin') {
            header("Location: admin.php"); 
        } elseif ($currentUserRole === 'petugas') {
            header("Location: petugas.php"); 
        } else {
            header("Location: index.html"); 
        }
        exit;
    }
}

function requireAdmin() {
    requireRole('admin');
}

function requirePetugas() {
    requireRole('petugas');
}

function requireSuperAdmin() {
    requireRole('super_admin');
}

function requireAdminOrPetugas() {
    requireRole(['admin', 'petugas']);
}

function requireAdminOrSuperAdmin() {
    requireRole(['admin', 'super_admin']);
}

function redirectIfLoggedIn() {
    if (isUserLoggedIn()) {
        $role = $_SESSION['role'] ?? 'warga';
        if ($role === 'super_admin') {
            header('Location: /super_admin.php');
        } elseif ($role === 'admin') {
            header('Location: /admin.php');
        } elseif ($role === 'petugas') {
            header('Location: /petugas.php');
        } else {
            header('Location: /pelapor.php');
        }
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /login.php');
    exit;
}

// Only handle auth-related POST actions when accessed directly (not when included)
// Check if this file is being accessed directly as the main script
$isDirectAccess = (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__));

if ($isDirectAccess && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
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
            
            $result = loginUser($username, $password); 
            echo json_encode($result);
            exit;
        
        case 'logout':
            $result = logoutUser();
            echo json_encode($result);
            exit;
        
        case 'check':
            echo json_encode([
                'success' => true,
                'logged_in' => isUserLoggedIn(), 
                'user' => getUserInfo() 
            ]);
            exit;
        
        case 'register':
            $result = registerUser($_POST);
            echo json_encode($result);
            exit;
        
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            exit;
    }
}

// Alias functions for backward compatibility
function isLoggedIn() {
    return isUserLoggedIn();
}

function getCurrentUser() {
    return getUserInfo();
}