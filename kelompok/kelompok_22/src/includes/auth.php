<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loginUser($username, $password) {
    $conn = getDBConnection();
    
    $username = sanitizeInput($username);
    
    $stmt = $conn->prepare("SELECT id, username, full_name, role, password_hash FROM users WHERE username = ? AND is_active = 1"); 
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
    $_SESSION['login_time'] = time();
    
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?"); 
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    
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
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role' => $_SESSION['role'] ?? null, 
        'login_time' => $_SESSION['login_time'] ?? null
    ];
}

function requireLogin() {
    if (!isUserLoggedIn()) {
        header('Location: login.php');
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
        
        if ($currentUserRole === 'admin') {
            header("Location: admin.php"); 
        } elseif ($currentUserRole === 'petugas') {
            header("Location: petugas.php"); 
        } else {
            header("Location: index.php"); 
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

function requireAdminOrPetugas() {
    requireRole(['admin', 'petugas']);
}

function redirectIfLoggedIn() {
    if (isUserLoggedIn()) {
        $role = $_SESSION['role'] ?? 'warga';
        if ($role === 'admin') {
             header('Location: admin.php');
        } elseif ($role === 'petugas') {
             header('Location: petugas.php');
        } else {
             header('Location: index.php'); 
        }
        exit;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

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
        
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            exit;
    }
}