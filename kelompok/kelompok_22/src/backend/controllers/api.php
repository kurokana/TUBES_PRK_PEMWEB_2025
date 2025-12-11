<?php
/**
 * REST API untuk SiPaMaLi
 * Tugas Besar Praktikum Pemrograman Web 2025
 * Kelompok 22
 * Endpoints:
 * - GET    /api.php?action=getReports       - Get all reports
 * - GET    /api.php?action=getReport&id=xxx - Get single report
 * - GET    /api.php?action=getStats         - Get statistics
 * - GET    /api.php?action=getPetugas       - GET LIST PETUGAS
 * - POST   /api.php?action=createReport     - Create new report
 * - POST   /api.php?action=change_user_role - Change user role (super admin only)
 * - POST   /api.php?action=toggle_user_status - Toggle user active status (super admin only)
 * - PUT    /api.php?action=updateStatus     - Update report status
 * - PUT    /api.php?action=assignReport     - ASSIGN PETUGAS (PRIMARY KEY ID)
 * - PUT    /api.php?action=validateReport   - VALIDASI (DITERIMA -> TUNTAS)
 * - PUT    /api.php?action=rejectReport     - TOLAK LAPORAN WARGA (dari halaman Laporan Warga)
 * - PUT    /api.php?action=rejectValidation - VALIDASI (DITOLAK -> DIPROSES)
 * - DELETE /api.php?action=deleteReport&id=xxx - Delete report
 * * CATATAN: Memerlukan fungsi helper: getDBConnection(), jsonResponse(), sanitizeInput(), generateReportId(), validateUploadedFile(), UPLOAD_DIR
 */

// DEBUG: File execution check
file_put_contents('/tmp/api_debug.log', "\n\n=== API.PHP LOADED ===\n", FILE_APPEND);
file_put_contents('/tmp/api_debug.log', "Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
file_put_contents('/tmp/api_debug.log', "Method: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);

require_once __DIR__ . '/../utils/config.php';
file_put_contents('/tmp/api_debug.log', "Config loaded\n", FILE_APPEND);

require_once __DIR__ . '/../middleware/auth.php';
file_put_contents('/tmp/api_debug.log', "Auth loaded\n", FILE_APPEND);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
file_put_contents('/tmp/api_debug.log', "Session started\n", FILE_APPEND);

function getPutData() {
    // Membaca isi body request mentah
    $input = file_get_contents('php://input'); 
    $putData = [];
    
    // Menguraikan data yang berbentuk URL-encoded (seperti form data)
    parse_str($input, $putData); 
    
    // Jika data adalah JSON (opsional, tapi bagus untuk masa depan)
    if (empty($putData)) {
        $json = json_decode($input, true);
        if ($json !== null) {
            $putData = $json;
        }
    }
    return $putData;
}

// Get action from appropriate source based on method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    // Debug: log received data
    file_put_contents('/tmp/api_debug.log', date('Y-m-d H:i:s') . " - POST Request\n", FILE_APPEND);
    file_put_contents('/tmp/api_debug.log', "Action: " . $action . "\n", FILE_APPEND);
    file_put_contents('/tmp/api_debug.log', "POST Data: " . print_r($_POST, true) . "\n", FILE_APPEND);
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $putData = getPutData();
    $action = $putData['action'] ?? $_GET['action'] ?? '';
} else {
    $action = $_GET['action'] ?? '';
}

// If action is empty, return error immediately
if (empty($action)) {
    error_log('Empty action received. Method: ' . $_SERVER['REQUEST_METHOD']);
    error_log('GET params: ' . print_r($_GET, true));
    error_log('POST params: ' . print_r($_POST, true));
    jsonResponse(false, null, 'Invalid action', 400);
    exit;
}

// Routing berdasarkan method dan action
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handleGet($action);
        break;
    
    case 'POST':
        handlePost($action);
        break;
    
    case 'PUT':
        handlePut($action);
        break;
    
    case 'DELETE':
        handleDelete($action);
        break;
    
    default:
        jsonResponse(false, null, 'Method not allowed', 405);
}

/**
 * Handle GET requests
 */
function handleGet($action) {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'checkSession':
            // Check if user is logged in
            if (isLoggedIn()) {
                $user = getCurrentUser();
                jsonResponse(true, ['user' => $user], 'Session active');
            } else {
                jsonResponse(false, null, 'No active session', 401);
            }
            break;
            
        case 'getReports':
            // Get filter parameter
            $filter = $_GET['filter'] ?? 'all';
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Build query based on filter and user role
            $sql = "SELECT 
                        r.id, r.report_id, r.user_id, r.category, r.location, r.description,
                        r.status, r.priority, r.image_path, r.assigned_to, r.created_at,
                        u.full_name as reporter_name
                    FROM reports r
                    LEFT JOIN users u ON r.user_id = u.id";
            
            $conditions = [];
            
            // If user is logged in and not admin, show only their reports
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                $role = $_SESSION['role'] ?? '';
                if (!in_array($role, ['admin', 'super_admin', 'petugas'])) {
                    $conditions[] = "r.user_id = " . intval($_SESSION['user_id']);
                }
            }
            
            // Apply status filter
            if ($filter !== 'all') {
                $filter_safe = $conn->real_escape_string($filter);
                $conditions[] = "r.status = '$filter_safe'";
            }
            
            if (count($conditions) > 0) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $sql .= " ORDER BY r.created_at DESC LIMIT 50";
            
            $result = $conn->query($sql);
            
            if ($result === false) {
                jsonResponse(false, null, 'Failed to fetch reports: ' . $conn->error, 500);
                return;
            }
            
            $reports = [];
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
            
            jsonResponse(true, $reports, 'Reports retrieved successfully');
            break;
        
        case 'getReport':
            // Get single report by ID
            $id = intval($_GET['id'] ?? 0);
            
            if ($id === 0) {
                jsonResponse(false, null, 'Report ID is required', 400);
                return;
            }
            
            $stmt = $conn->prepare("
                SELECT 
                    r.*, 
                    u.full_name as reporter_name,
                    u.email as reporter_email,
                    p.full_name as petugas_name
                FROM reports r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN users p ON r.assigned_to = p.id
                WHERE r.id = ?
            ");
            
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                jsonResponse(true, $row, 'Report retrieved successfully');
            } else {
                jsonResponse(false, null, 'Report not found', 404);
            }
            break;
        
        case 'getStats':
            // ... (Logic getStats) ...
            jsonResponse(false, null, 'Not implemented', 501);
            break;
            
        case 'getPetugas':
            // Endpoint untuk mengambil daftar semua user dengan role 'petugas'
            $query = "SELECT id, full_name, email 
                      FROM users 
                      WHERE role = 'petugas' 
                      ORDER BY full_name ASC";
            
            $result = $conn->query($query);
            
            if ($result === FALSE) {
                jsonResponse(false, null, 'Failed to fetch petugas list: ' . $conn->error, 500);
                break;
            }
            
            $petugas_list = [];
            while ($row = $result->fetch_assoc()) {
                // Konversi id menjadi string agar konsisten di JS
                $row['id'] = (string)$row['id']; 
                $petugas_list[] = $row;
            }
            
            jsonResponse(true, $petugas_list, 'Petugas list retrieved successfully');
            break;
            
        default:
            jsonResponse(false, null, 'Invalid action', 400);
    }
}

/**
 * Handle POST requests
 */
function handlePost($action) {
    error_log('handlePost called with action: "' . $action . '"');
    error_log('Action length: ' . strlen($action));
    error_log('Action comparison: ' . ($action === 'createReport' ? 'MATCH' : 'NO MATCH'));
    $conn = getDBConnection();
    
    switch ($action) {
        case 'createReport':
            // Check if user is logged in using auth function
            if (!isLoggedIn()) {
                jsonResponse(false, null, 'Anda harus login untuk membuat laporan', 401);
                return;
            }
            
            // Get user ID
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$user_id) {
                jsonResponse(false, null, 'User ID tidak ditemukan', 401);
                return;
            }
            
            // Validate input
            $category = sanitizeInput($_POST['category'] ?? '');
            $location = sanitizeInput($_POST['location'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            
            if (empty($category) || empty($location) || empty($description)) {
                jsonResponse(false, null, 'Kategori, lokasi, dan deskripsi harus diisi', 400);
                return;
            }
            
            // Generate report ID
            $report_id = generateReportId();
            
            // Handle image upload
            $image_path = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $validation = validateUploadedFile($_FILES['image']);
                if (!$validation['valid']) {
                    jsonResponse(false, null, $validation['message'], 400);
                    return;
                }
                
                // Create upload directory if not exists
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                
                // Generate unique filename
                $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $filename = $report_id . '_' . time() . '.' . $extension;
                $target_path = UPLOAD_DIR . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    // Store relative path for database (can be accessed from any role)
                    $image_path = '/uploads/' . $filename;
                }
            }
            
            // Insert into database
            $stmt = $conn->prepare("
                INSERT INTO reports (report_id, user_id, category, location, description, status, priority, image_path, created_at)
                VALUES (?, ?, ?, ?, ?, 'Menunggu', 'Sedang', ?, NOW())
            ");
            
            $stmt->bind_param('sissss', $report_id, $user_id, $category, $location, $description, $image_path);
            
            if ($stmt->execute()) {
                jsonResponse(true, [
                    'report_code' => $report_id,
                    'id' => $conn->insert_id
                ], 'Laporan berhasil dikirim');
            } else {
                jsonResponse(false, null, 'Gagal menyimpan laporan: ' . $conn->error, 500);
            }
            break;
        
        case 'change_user_role':
            // Only super admin can change roles
            if (!isLoggedIn()) {
                jsonResponse(false, null, 'Unauthorized - Login required', 401);
                return;
            }
            
            $currentUser = getCurrentUser();
            if ($currentUser['role'] !== 'super_admin') {
                jsonResponse(false, null, 'Unauthorized - Super admin only', 403);
                return;
            }
            
            require_once __DIR__ . '/../utils/admin_utils.php';
            $user_id = (int)$_POST['user_id'];
            $new_role = $_POST['new_role'];
            $result = changeUserRole($user_id, $new_role, $currentUser['id']);
            
            jsonResponse($result['success'], null, $result['message']);
            break;
            
        case 'toggle_user_status':
            // Only super admin can toggle user status
            if (!isLoggedIn()) {
                jsonResponse(false, null, 'Unauthorized - Login required', 401);
                return;
            }
            
            $currentUser = getCurrentUser();
            if ($currentUser['role'] !== 'super_admin') {
                jsonResponse(false, null, 'Unauthorized - Super admin only', 403);
                return;
            }
            
            require_once __DIR__ . '/../utils/admin_utils.php';
            $user_id = (int)$_POST['user_id'];
            $is_active = (int)$_POST['is_active'];
            $result = toggleUserStatus($user_id, $is_active, $currentUser['id']);
            
            jsonResponse($result['success'], null, $result['message']);
            break;
            
        default:
            jsonResponse(false, null, 'Invalid action', 400);
    }
}

/**
 * Handle PUT requests
 */
function handlePut($action) {
    $conn = getDBConnection();
    
    // Parse PUT data
    parse_str(file_get_contents("php://input"), $putData);
    
    // ASUMSI KRITIS: ID yang dikirim dari frontend adalah PRIMARY KEY (integer)
    $reportPkeyId = (int)($putData['id'] ?? $putData['report_id'] ?? 0); 
    
    if ($reportPkeyId === 0 && in_array($action, ['assignReport', 'validateReport', 'rejectValidation'])) {
        jsonResponse(false, null, 'Report Primary Key ID is required.', 400);
        return; 
    }
    
    switch ($action) {
        case 'updateStatus':
            // ... (Logic updateStatus) ...
            jsonResponse(false, null, 'Not implemented', 501);
            break;
            
        case 'assignReport':
            // Pastikan $putData sudah diisi
            $putData = getPutData();
            $reportPkeyId = (int)($putData['id'] ?? 0); // Primary Key ID
            $petugasId = sanitizeInput($putData['assigned_to'] ?? ''); // Bisa berupa ID Petugas (string) atau "" (Kosongkan)

            if ($reportPkeyId <= 0) {
                jsonResponse(false, null, 'Invalid Report ID.', 400);
                return;
            }

            if (!empty($petugasId)) {
                // --- KASUS 1: PENUGASAN ---
                $statusToUpdate = 'Diproses';
                $updateQuery = "UPDATE reports SET assigned_to = ?, status = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                
                if ($stmt === false) {
                    jsonResponse(false, null, 'SQL Prepare Error (Assignment): ' . $conn->error, 500);
                    return;
                }

                // --- PERBAIKAN FATAL ERROR: Buat variabel integer terpisah untuk bind_param ---
                $petugasIdInt = (int)$petugasId; // Pastikan ini adalah variabel, bukan hasil cast langsung di bind_param
                
                // Bind parameter: i (integer: petugas ID), s (string: status), i (integer: report ID)
                $stmt->bind_param('isi', $petugasIdInt, $statusToUpdate, $reportPkeyId); // Menggunakan variabel $petugasIdInt
                
            } else {
                // --- KASUS 2: PEMBATALAN PENUGASAN (SET NULL) ---
                $statusToUpdate = 'Menunggu';
                // Query disederhanakan karena assigned_to langsung diatur ke NULL
                $updateQuery = "UPDATE reports SET assigned_to = NULL, status = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                
                if ($stmt === false) {
                    jsonResponse(false, null, 'SQL Prepare Error (Unassignment): ' . $conn->error, 500);
                    return;
                }
                
                // Bind parameter: s (string: status), i (integer: report ID)
                $stmt->bind_param('si', $statusToUpdate, $reportPkeyId);
            }

            // Eksekusi statement
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                jsonResponse(true, null, 'Laporan berhasil ditugaskan atau status diubah.');
            } else {
                jsonResponse(false, null, 'Gagal menugaskan laporan. Pastikan ID laporan valid.', 500);
            }
    
    $stmt->close();
    break;
            
        case 'validateReport':
            $putData = getPutData();
            $reportPkeyId = (int)($putData['id'] ?? 0);
            $adminNotes = sanitizeInput($putData['admin_notes'] ?? '');

            if ($reportPkeyId <= 0) {
                jsonResponse(false, null, 'Invalid Report ID.', 400);
                return;
            }

            $finalStatus = 'Tuntas'; 
            
            // Simpan catatan ke admin_notes dan ubah status menjadi Tuntas
            // Terima laporan dengan status 'Selesai' atau 'Diproses'
            $stmt = $conn->prepare("UPDATE reports SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ? AND (status = 'Selesai' OR status = 'Diproses')");
            
            // --- PERBAIKAN SAFETY: CEK PREPARE ---
            if ($stmt === false) {
                jsonResponse(false, null, 'SQL Prepare Error (Validate): ' . $conn->error, 500);
                return;
            }

            $stmt->bind_param('ssi', $finalStatus, $adminNotes, $reportPkeyId);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                jsonResponse(true, null, "Laporan berhasil divalidasi dan status diubah menjadi 'Tuntas'.");
            } else {
                // Ini terjadi jika laporan tidak ditemukan atau statusnya bukan 'Selesai' atau 'Diproses'
                jsonResponse(false, null, "Gagal memvalidasi. Pastikan status laporan masih 'Selesai' atau 'Diproses'.", 500);
            }
            $stmt->close();
            break;

        case 'rejectReport':
            // Menolak laporan warga langsung (dari halaman Laporan Warga)
            $putData = getPutData();
            $reportPkeyId = (int)($putData['id'] ?? 0);
            $adminNotes = sanitizeInput($putData['admin_notes'] ?? '');

            if ($reportPkeyId <= 0) {
                jsonResponse(false, null, 'Invalid Report ID.', 400);
                return;
            }

            if (empty($adminNotes)) {
                jsonResponse(false, null, "Alasan penolakan wajib diisi.", 400);
                return;
            }
            
            $rejectStatus = 'Ditolak'; 
            
            // Tolak laporan dengan status apapun kecuali Tuntas
            $stmt = $conn->prepare("UPDATE reports SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ? AND status != 'Tuntas'");
            
            if ($stmt === false) {
                jsonResponse(false, null, 'SQL Prepare Error (Reject Report): ' . $conn->error, 500);
                return;
            }

            $stmt->bind_param('ssi', $rejectStatus, $adminNotes, $reportPkeyId);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                jsonResponse(true, null, "Laporan berhasil ditolak.");
            } else {
                jsonResponse(false, null, "Gagal menolak laporan. Pastikan laporan belum tuntas.", 500);
            }
            $stmt->close();
            break;

        case 'rejectValidation': 
            $putData = getPutData();
            $reportPkeyId = (int)($putData['id'] ?? 0);
            $adminNotes = sanitizeInput($putData['admin_notes'] ?? '');

            if ($reportPkeyId <= 0) {
                jsonResponse(false, null, 'Invalid Report ID.', 400);
                return;
            }

            if (empty($adminNotes)) {
                jsonResponse(false, null, "Catatan Admin wajib diisi untuk penolakan.", 400);
                return;
            }
            
            $rejectStatus = 'Ditolak'; 
            
            // Simpan catatan penolakan ke admin_notes dan ubah status menjadi Ditolak
            // Tolak laporan dengan status 'Selesai' atau 'Diproses'
            $stmt = $conn->prepare("UPDATE reports SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ? AND (status = 'Selesai' OR status = 'Diproses')");
            
            // --- PERBAIKAN SAFETY: CEK PREPARE ---
            if ($stmt === false) {
                jsonResponse(false, null, 'SQL Prepare Error (Reject): ' . $conn->error, 500);
                return;
            }

            $stmt->bind_param('ssi', $rejectStatus, $adminNotes, $reportPkeyId);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                jsonResponse(true, null, "Validasi ditolak. Status diubah menjadi 'Ditolak'.");
            } else {
                // Ini terjadi jika laporan tidak ditemukan atau statusnya bukan 'Selesai' atau 'Diproses'
                jsonResponse(false, null, "Gagal menolak validasi. Pastikan status laporan masih 'Selesai' atau 'Diproses'.", 500);
            }
            $stmt->close();
            break;
            
        case 'request_reset':
            // Request password reset - verify username and email match
            $username = sanitizeInput($_POST['username'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            
            if (empty($username) || empty($email)) {
                jsonResponse(false, null, 'Username dan email harus diisi', 400);
                return;
            }
            
            $conn = getDBConnection();
            
            // Check if username and email match
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND email = ? AND is_active = 1");
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // User found - in production, send email with reset token
                // For now, just return success (allow direct reset)
                jsonResponse(true, null, 'Verifikasi berhasil! Silakan masukkan password baru Anda.');
            } else {
                jsonResponse(false, null, 'Username dan email tidak cocok atau akun tidak aktif', 404);
            }
            
            $stmt->close();
            break;
            
        case 'reset_password':
            // Reset password after verification
            $username = sanitizeInput($_POST['username'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            
            if (empty($username) || empty($newPassword)) {
                jsonResponse(false, null, 'Username dan password harus diisi', 400);
                return;
            }
            
            if (strlen($newPassword) < 6) {
                jsonResponse(false, null, 'Password minimal 6 karakter', 400);
                return;
            }
            
            $conn = getDBConnection();
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE username = ? AND is_active = 1");
            $stmt->bind_param('ss', $hashedPassword, $username);
            
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                jsonResponse(true, null, 'Password berhasil direset!');
            } else {
                jsonResponse(false, null, 'Gagal mereset password. Username tidak ditemukan.', 500);
            }
            
            $stmt->close();
            break;
            
        default:
            jsonResponse(false, null, 'Invalid action', 400);
    }
}

/**
 * Handle DELETE requests
 */
function handleDelete($action) {
    // ... (Logic deleteReport) ...
    jsonResponse(false, null, 'Invalid action', 400);
}
?>