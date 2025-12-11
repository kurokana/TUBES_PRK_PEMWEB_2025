<?php
/**
 * SiPaMaLi - Main Entry Point
 * Sistem Pelaporan & Pemantauan Masalah Lingkungan
 * Kelompok 22 - Praktikum Pemrograman Web 2025
 */

// Start session
session_start();

// Define base paths
define('ROOT_PATH', __DIR__);
define('SRC_PATH', ROOT_PATH . '/src');
define('BACKEND_PATH', SRC_PATH . '/backend');
define('FRONTEND_PATH', SRC_PATH . '/frontend');

// Auto redirect to appropriate page based on login status
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $role = $_SESSION['role'] ?? 'warga';
    
    switch ($role) {
        case 'super_admin':
            header('Location: /super_admin.php');
            break;
        case 'admin':
            header('Location: /admin.php');
            break;
        case 'petugas':
            header('Location: /petugas.php');
            break;
        default:
            header('Location: /pelapor.php');
            break;
    }
    exit;
} else {
    // Not logged in, show landing page
    require_once SRC_PATH . '/frontend/pages/index.php';
}
