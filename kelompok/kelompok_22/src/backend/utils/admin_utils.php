<?php
// Pastikan file ini membutuhkan koneksi database ($db_conn) yang didefinisikan di db.php

/**
 * Mengambil data statistik ringkas untuk Dashboard Admin.
 * @param mysqli $db Koneksi database.
 * @return array
 */
function getDashboardStats($db) {
    // Query untuk menghitung laporan berdasarkan status
    $sql = "
        SELECT 
            COUNT(id) AS total_reports,
            COUNT(CASE WHEN status = 'Menunggu' THEN 1 END) AS pending_count,
            COUNT(CASE WHEN status = 'Diproses' THEN 1 END) AS processing_count,
            COUNT(CASE WHEN status = 'Selesai' THEN 1 END) AS completed_count,
            COUNT(CASE WHEN status = 'Tuntas' THEN 1 END) AS tuntas_count,
            COUNT(CASE WHEN status = 'Ditolak' THEN 1 END) AS rejected_count
        FROM reports;
    ";
    
    $result = $db->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // Nilai default jika query gagal
    return [
        'total_reports' => 0,
        'pending_count' => 0,
        'processing_count' => 0,
        'completed_count' => 0,
        'tuntas_count' => 0,
        'rejected_count' => 0,
    ];
}

function getActiveReports() {
    $conn = getDBConnection(); // Asumsi ini mengembalikan objek mysqli

    $sql = "SELECT 
                r.*, 
                u.full_name AS reported_by, 
                p.full_name AS assigned_to_name
            FROM 
                reports r
            LEFT JOIN 
                users u ON r.user_id = u.id 
            LEFT JOIN 
                users p ON r.assigned_to = p.id AND p.role = 'petugas'
            WHERE 
                r.status IN ('Menunggu', 'Diproses')
            ORDER BY 
                r.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // --- START PERBAIKAN UNTUK MYSQLI ---
    $result = $stmt->get_result(); // Dapatkan objek result
    $reports = $result->fetch_all(MYSQLI_ASSOC); // Gunakan fetch_all() dari mysqli_result
    // --- END PERBAIKAN UNTUK MYSQLI ---
    
    $stmt->close(); // Tutup statement

    return $reports;
}

function getValidationReports() {
    $conn = getDBConnection(); 
    $reports = []; // Inisialisasi untuk menghindari warning

    $sql = "SELECT 
                r.*, 
                u.full_name AS reported_by, 
                p.full_name AS assigned_to_name
            FROM 
                reports r
            LEFT JOIN 
                users u ON r.user_id = u.id 
            LEFT JOIN 
                users p ON r.assigned_to = p.id AND p.role = 'petugas'
            WHERE 
                r.status = 'Selesai'
            ORDER BY 
                r.updated_at DESC";

    try {
        // Menggunakan prepared statement mysqli
        $stmt = $conn->prepare($sql);
        
        if ($stmt === FALSE) {
            // Tangani error jika prepare gagal
            throw new Exception("Prepare failed: " . $conn->error);
        }

        if ($stmt->execute()) {
            // 1. Dapatkan objek hasil (mysqli_result)
            $result = $stmt->get_result(); 
            
            // 2. Ambil semua baris sebagai array asosiatif (menggunakan fetch_all)
            $reports = $result->fetch_all(MYSQLI_ASSOC); 
        }
        
        // Tutup statement
        $stmt->close();

    } catch (Exception $e) {
        // Penanganan error (misalnya logging)
        error_log("DB Error in getValidationReports: " . $e->getMessage());
        // Mengembalikan array kosong jika terjadi kesalahan
    }
    
    return $reports; 
}

function getReportsByStatus($conn, $statuses) {
    if (empty($statuses)) {
        return [];
    }

    // 1. Persiapan Query: Buat placeholder (?) sebanyak jumlah status
    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    
    // 2. Tentukan Tipe Data Binding (semua status adalah string 's')
    $types = str_repeat('s', count($statuses));
    
    // 3. Query dengan LEFT JOIN untuk mendapatkan nama petugas
    $sql = "SELECT 
                r.*, 
                p.full_name AS assigned_to_name 
            FROM reports r
            LEFT JOIN users p ON r.assigned_to = p.id
            WHERE r.status IN ($placeholders)
            ORDER BY r.created_at DESC";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        // Catat error SQL, jangan tampilkan ke user
        error_log("SQL Prepare Error (getReportsByStatus): " . $conn->error);
        return [];
    }
    
    // 4. Bind Parameter secara Dinamis
    // mysqli::bind_param memerlukan argumen yang di-pass by reference, jadi kita menggunakan call_user_func_array
    $params = array_merge([$types], $statuses);
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    call_user_func_array([$stmt, 'bind_param'], $refs);


    // 5. Eksekusi dan Ambil Hasil
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $reports = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $reports;
    }
    
    $stmt->close();
    return [];
}

// ============================================
// SUPER ADMIN FUNCTIONS - User Management
// ============================================

/**
 * Change user role (only for super admin)
 */
function changeUserRole($user_id, $new_role, $admin_id) {
    $conn = getDBConnection();
    
    // Get old role for audit
    $stmt = $conn->prepare("SELECT role, username FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $old_role = $user['role'];
    $username = $user['username'];
    
    // Update role
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param('si', $new_role, $user_id);
    
    if ($stmt->execute()) {
        // Log audit
        require_once __DIR__ . '/../middleware/auth.php';
        logAudit($admin_id, 'role_change', 'users', $user_id, 
                "Changed role of user '$username' from '$old_role' to '$new_role'", 
                $old_role, $new_role);
        return ['success' => true, 'message' => 'Role berhasil diubah'];
    }
    
    return ['success' => false, 'message' => 'Gagal mengubah role: ' . $conn->error];
}

/**
 * Toggle user active status (only for super admin)
 */
function toggleUserStatus($user_id, $is_active, $admin_id) {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $username = $stmt->get_result()->fetch_assoc()['username'];
    
    $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $stmt->bind_param('ii', $is_active, $user_id);
    
    if ($stmt->execute()) {
        require_once __DIR__ . '/../middleware/auth.php';
        $action = $is_active ? 'activated' : 'deactivated';
        logAudit($admin_id, 'user_management', 'users', $user_id, 
                "User '$username' $action", !$is_active, $is_active);
        return ['success' => true, 'message' => 'Status user berhasil diubah'];
    }
    
    return ['success' => false, 'message' => 'Gagal mengubah status: ' . $conn->error];
}

// Handle POST requests for super admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/../middleware/auth.php';
    
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $user = getUserInfo();
    
    // Only super admin can perform these actions
    if (!$user || $user['role'] !== 'super_admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    switch ($action) {
        case 'change_user_role':
            $user_id = (int)$_POST['user_id'];
            $new_role = $_POST['new_role'];
            $result = changeUserRole($user_id, $new_role, $user['id']);
            echo json_encode($result);
            exit;
            
        case 'toggle_user_status':
            $user_id = (int)$_POST['user_id'];
            $is_active = (int)$_POST['is_active'];
            $result = toggleUserStatus($user_id, $is_active, $user['id']);
            echo json_encode($result);
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
}
?>
    
    