<?php
/**
 * SiPaMaLi - Main Entry Point
 * Sistem Pelaporan & Pemantauan Masalah Lingkungan
 * Kelompok 22 - Praktikum Pemrograman Web 2025
 */

// Start session
session_start();

// Define base paths
define('BASE_PATH', __DIR__);
define('BACKEND_PATH', BASE_PATH . '/backend');
define('FRONTEND_PATH', BASE_PATH . '/frontend');

// Auto redirect to appropriate page
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $role = $_SESSION['role'] ?? 'warga';
    
    switch ($role) {
        case 'super_admin':
            header('Location: backend/controllers/super_admin.php');
            break;
        case 'admin':
            header('Location: backend/controllers/admin.php');
            break;
        case 'petugas':
            header('Location: backend/controllers/petugas.php');
            break;
        default:
            header('Location: frontend/pages/index.html');
            break;
    }
} else {
    // Not logged in, show landing page or redirect to login
    header('Location: frontend/pages/index.html');
}
exit;
