<?php
/**
 * REST API untuk SiPaMaLi
 * Tugas Besar Praktikum Pemrograman Web 2025
 * Kelompok 22
 * Endpoints:
 * - GET    /api.php?action=getReports       - Get all reports
 * - GET    /api.php?action=getReport&id=xxx - Get single report
 * - GET    /api.php?action=getStats         - Get statistics
 * - GET    /api.php?action=getPetugas       - GET LIST PETUGAS
 * - POST   /api.php?action=createReport     - Create new report
 * - PUT    /api.php?action=updateStatus     - Update report status
 * - PUT    /api.php?action=assignReport     - ASSIGN PETUGAS (PRIMARY KEY ID)
 * - PUT    /api.php?action=validateReport   - VALIDASI (DITERIMA -> TUNTAS)
 * - PUT    /api.php?action=rejectValidation - VALIDASI (DITOLAK -> DIPROSES)
 * - DELETE /api.php?action=deleteReport&id=xxx - Delete report
 * * CATATAN: Memerlukan fungsi helper: getDBConnection(), jsonResponse(), sanitizeInput(), generateReportId(), validateUploadedFile(), UPLOAD_DIR
 */

require_once '../utils/config.php';

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

$action = $_GET['action'] ?? '';

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
        case 'getReports':
            // ... (Logic getReports) ...
            jsonResponse(false, null, 'Not implemented', 501);
            break;
        
        case 'getReport':
            // ... (Logic getReport) ...
            jsonResponse(false, null, 'Not implemented', 501);
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
    // ... (Logic createReport) ...
    jsonResponse(false, null, 'Invalid action', 400);
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