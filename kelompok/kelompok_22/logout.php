<?php
/**
 * Logout Handler
 * File untuk menangani proses logout admin
 * Tanggal: 9 Desember 2025
 */

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hapus semua session data
session_unset();

// Destroy session
session_destroy();

// Hapus session cookie jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirect ke halaman login
header("Location: login.php?logout=success");
exit();
?>
