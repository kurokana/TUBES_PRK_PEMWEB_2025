<?php
/**
 * Test script untuk simulasi upload laporan dari warga
 */

// Start session
session_start();

// Simulasi login sebagai warga
$_SESSION['logged_in'] = true;
$_SESSION['user_id'] = 3; // ID user warga dari database
$_SESSION['role'] = 'warga';
$_SESSION['username'] = 'warga1';
$_SESSION['full_name'] = 'Warga Test';

echo "=== TEST UPLOAD LAPORAN WARGA ===\n";
echo "Session Info:\n";
echo "- Logged In: " . ($_SESSION['logged_in'] ? 'Yes' : 'No') . "\n";
echo "- User ID: " . $_SESSION['user_id'] . "\n";
echo "- Role: " . $_SESSION['role'] . "\n";
echo "- Username: " . $_SESSION['username'] . "\n";
echo "\n";

// Include required files
require_once __DIR__ . '/src/backend/utils/config.php';
require_once __DIR__ . '/src/backend/middleware/auth.php';

echo "=== CONFIG CHECK ===\n";
echo "- ROOT_PATH: " . ROOT_PATH . "\n";
echo "- UPLOAD_DIR: " . UPLOAD_DIR . "\n";
echo "- Upload dir exists: " . (is_dir(UPLOAD_DIR) ? 'Yes' : 'No') . "\n";
echo "- Upload dir writable: " . (is_writable(UPLOAD_DIR) ? 'Yes' : 'No') . "\n";
echo "\n";

echo "=== AUTH CHECK ===\n";
echo "- isLoggedIn(): " . (isLoggedIn() ? 'Yes' : 'No') . "\n";
echo "- getCurrentUser():\n";
$currentUser = getCurrentUser();
if ($currentUser) {
    echo "  - ID: " . $currentUser['id'] . "\n";
    echo "  - Username: " . $currentUser['username'] . "\n";
    echo "  - Role: " . $currentUser['role'] . "\n";
} else {
    echo "  - NULL\n";
}
echo "\n";

echo "=== DATABASE CHECK ===\n";
$conn = getDBConnection();
if ($conn) {
    echo "- Database connection: OK\n";
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, full_name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        echo "- User found in DB:\n";
        echo "  - ID: " . $user['id'] . "\n";
        echo "  - Username: " . $user['username'] . "\n";
        echo "  - Full Name: " . $user['full_name'] . "\n";
        echo "  - Role: " . $user['role'] . "\n";
    } else {
        echo "- User NOT found in DB!\n";
    }
    
    $stmt->close();
} else {
    echo "- Database connection: FAILED\n";
}
echo "\n";

echo "=== PERMISSION CHECK ===\n";
// Check if warga can create report
$can_report = in_array($_SESSION['role'], ['warga', 'pelapor']);
echo "- Can create report: " . ($can_report ? 'Yes' : 'No') . "\n";
echo "\n";

echo "=== FILE UPLOAD SIMULATION ===\n";
// Create a test image file
$test_image_path = UPLOAD_DIR . 'test_' . time() . '.jpg';
$test_image_content = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlbaWmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9/KKKKAP/2Q==');
file_put_contents($test_image_path, $test_image_content);

if (file_exists($test_image_path)) {
    echo "- Test image created: Yes\n";
    echo "- Test image path: " . $test_image_path . "\n";
    echo "- Test image size: " . filesize($test_image_path) . " bytes\n";
    
    // Test if image is accessible via web path
    $web_path = '/uploads/' . basename($test_image_path);
    echo "- Web path: " . $web_path . "\n";
    
    // Clean up
    unlink($test_image_path);
    echo "- Test image cleaned up\n";
} else {
    echo "- Failed to create test image\n";
}

echo "\n=== TEST COMPLETE ===\n";
