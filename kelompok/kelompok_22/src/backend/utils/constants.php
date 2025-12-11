<?php
/**
 * SiPaMaLi - Path Constants
 * Centralized path management untuk memudahkan maintenance
 */

// Define base directory
if (!defined('BASE_DIR')) {
    define('BASE_DIR', dirname(dirname(__FILE__)));
}

// Frontend Paths
define('FRONTEND_DIR', BASE_DIR . '/frontend');
define('FRONTEND_PAGES', FRONTEND_DIR . '/pages');
define('FRONTEND_ASSETS', FRONTEND_DIR . '/assets');
define('FRONTEND_UPLOADS', FRONTEND_DIR . '/uploads');

// Backend Paths
define('BACKEND_DIR', BASE_DIR . '/backend');
define('BACKEND_CONTROLLERS', BACKEND_DIR . '/controllers');
define('BACKEND_MIDDLEWARE', BACKEND_DIR . '/middleware');
define('BACKEND_UTILS', BACKEND_DIR . '/utils');
define('BACKEND_MODELS', BACKEND_DIR . '/models');

// URL Paths (relative to src/)
define('URL_BASE', '/kelompok_22/src');
define('URL_FRONTEND', URL_BASE . '/frontend/pages');
define('URL_BACKEND', URL_BASE . '/backend/controllers');

// Page URLs
define('URL_LOGIN', URL_FRONTEND . '/login.php');
define('URL_REGISTER', URL_FRONTEND . '/registrasi.php');
define('URL_INDEX', URL_FRONTEND . '/index.html');
define('URL_PELAPOR', URL_FRONTEND . '/pelapor.php');
define('URL_RIWAYAT', URL_FRONTEND . '/riwayat.php');

// Dashboard URLs
define('URL_ADMIN', URL_BACKEND . '/admin.php');
define('URL_PETUGAS', URL_BACKEND . '/petugas.php');
define('URL_SUPER_ADMIN', URL_BACKEND . '/super_admin.php');
define('URL_API', URL_BACKEND . '/api.php');
define('URL_LOGOUT', URL_BACKEND . '/logout.php');

/**
 * Get redirect URL based on user role
 */
function getRedirectUrlByRole($role) {
    switch ($role) {
        case 'super_admin':
            return URL_SUPER_ADMIN;
        case 'admin':
            return URL_ADMIN;
        case 'petugas':
            return URL_PETUGAS;
        case 'warga':
        default:
            return URL_INDEX;
    }
}

/**
 * Get relative path from current file to target
 */
function getRelativePath($from, $to) {
    $from = explode('/', $from);
    $to = explode('/', $to);
    
    $relPath = $to;
    
    foreach($from as $depth => $dir) {
        if($dir === $to[$depth]) {
            array_shift($relPath);
        } else {
            $remaining = count($from) - $depth;
            if($remaining > 1) {
                $padLength = (count($relPath) + $remaining - 1) * -1;
                $relPath = array_pad($relPath, $padLength, '..');
                break;
            }
        }
    }
    return implode('/', $relPath);
}
