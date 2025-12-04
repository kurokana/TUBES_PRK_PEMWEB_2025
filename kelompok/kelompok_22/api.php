<?php
/**
 * REST API untuk SiPaMaLi
 * Tugas Besar Praktikum Pemrograman Web 2025
 * Kelompok 22
 * 
 * Endpoints:
 * - GET    /api.php?action=getReports         - Get all reports
 * - GET    /api.php?action=getReport&id=xxx   - Get single report
 * - GET    /api.php?action=getStats           - Get statistics
 * - POST   /api.php?action=createReport       - Create new report
 * - PUT    /api.php?action=updateStatus       - Update report status
 * - DELETE /api.php?action=deleteReport&id=xxx - Delete report
 */

require_once 'includes/config.php';

// Ambil action dari query parameter
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
            // Get all reports dengan filter opsional
            $status = $_GET['status'] ?? '';
            $category = $_GET['category'] ?? '';
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            
            $query = "SELECT * FROM reports WHERE 1=1";
            $params = [];
            $types = '';
            
            if (!empty($status)) {
                $query .= " AND status = ?";
                $params[] = $status;
                $types .= 's';
            }
            
            if (!empty($category)) {
                $query .= " AND category = ?";
                $params[] = $category;
                $types .= 's';
            }
            
            $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            $reports = [];
            while ($row = $result->fetch_assoc()) {
                // Format tanggal
                $row['date'] = date('Y-m-d', strtotime($row['created_at']));
                $row['id'] = $row['report_id']; // Untuk kompatibilitas dengan frontend
                $row['desc'] = $row['description'];
                $row['img'] = $row['image_path'] ? 'uploads/' . basename($row['image_path']) : null;
                $reports[] = $row;
            }
            
            jsonResponse(true, $reports, 'Reports retrieved successfully');
            break;
        
        case 'getReport':
            // Get single report by ID
            $reportId = $_GET['id'] ?? '';
            
            if (empty($reportId)) {
                jsonResponse(false, null, 'Report ID is required', 400);
            }
            
            $stmt = $conn->prepare("SELECT * FROM reports WHERE report_id = ?");
            $stmt->bind_param('s', $reportId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                jsonResponse(false, null, 'Report not found', 404);
            }
            
            $report = $result->fetch_assoc();
            $report['date'] = date('Y-m-d', strtotime($report['created_at']));
            $report['id'] = $report['report_id'];
            $report['desc'] = $report['description'];
            $report['img'] = $report['image_path'] ? 'uploads/' . basename($report['image_path']) : null;
            
            jsonResponse(true, $report, 'Report retrieved successfully');
            break;
        
        case 'getStats':
            // Get statistics
            $result = $conn->query("SELECT * FROM report_statistics");
            $stats = $result->fetch_assoc();
            
            jsonResponse(true, $stats, 'Statistics retrieved successfully');
            break;
        
        default:
            jsonResponse(false, null, 'Invalid action', 400);
    }
}

/**
 * Handle POST requests
 */
function handlePost($action) {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'createReport':
            // Validasi input
            $category = sanitizeInput($_POST['category'] ?? '');
            $location = sanitizeInput($_POST['location'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            
            if (empty($category) || empty($location) || empty($description)) {
                jsonResponse(false, null, 'Category, location, and description are required', 400);
            }
            
            // Validasi kategori
            $validCategories = ['Sampah', 'Drainase', 'Jalan', 'Polusi', 'Lainnya'];
            if (!in_array($category, $validCategories)) {
                jsonResponse(false, null, 'Invalid category', 400);
            }
            
            // Generate Report ID
            $reportId = generateReportId();
            
            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $validation = validateUploadedFile($_FILES['image']);
                
                if (!$validation['valid']) {
                    jsonResponse(false, null, $validation['message'], 400);
                }
                
                // Generate unique filename
                $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $filename = $reportId . '_' . time() . '.' . $extension;
                $uploadPath = UPLOAD_DIR . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $imagePath = $uploadPath;
                } else {
                    jsonResponse(false, null, 'Failed to upload image', 500);
                }
            }
            
            // Insert ke database
            $stmt = $conn->prepare("INSERT INTO reports (report_id, category, location, description, status, image_path) VALUES (?, ?, ?, ?, 'Menunggu', ?)");
            $stmt->bind_param('sssss', $reportId, $category, $location, $description, $imagePath);
            
            if ($stmt->execute()) {
                // Get inserted report
                $stmt = $conn->prepare("SELECT * FROM reports WHERE report_id = ?");
                $stmt->bind_param('s', $reportId);
                $stmt->execute();
                $result = $stmt->get_result();
                $report = $result->fetch_assoc();
                
                $report['date'] = date('Y-m-d', strtotime($report['created_at']));
                $report['id'] = $report['report_id'];
                $report['desc'] = $report['description'];
                $report['img'] = $report['image_path'] ? 'uploads/' . basename($report['image_path']) : null;
                
                jsonResponse(true, $report, 'Report created successfully', 201);
            } else {
                jsonResponse(false, null, 'Failed to create report', 500);
            }
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
    
    switch ($action) {
        case 'updateStatus':
            $reportId = sanitizeInput($putData['id'] ?? '');
            $newStatus = sanitizeInput($putData['status'] ?? '');
            
            if (empty($reportId) || empty($newStatus)) {
                jsonResponse(false, null, 'Report ID and status are required', 400);
            }
            
            // Validasi status
            $validStatuses = ['Menunggu', 'Diproses', 'Selesai'];
            if (!in_array($newStatus, $validStatuses)) {
                jsonResponse(false, null, 'Invalid status', 400);
            }
            
            $stmt = $conn->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
            $stmt->bind_param('ss', $newStatus, $reportId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    jsonResponse(true, ['report_id' => $reportId, 'status' => $newStatus], 'Status updated successfully');
                } else {
                    jsonResponse(false, null, 'Report not found', 404);
                }
            } else {
                jsonResponse(false, null, 'Failed to update status', 500);
            }
            break;
        
        default:
            jsonResponse(false, null, 'Invalid action', 400);
    }
}

/**
 * Handle DELETE requests
 */
function handleDelete($action) {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'deleteReport':
            $reportId = $_GET['id'] ?? '';
            
            if (empty($reportId)) {
                jsonResponse(false, null, 'Report ID is required', 400);
            }
            
            // Get image path sebelum delete
            $stmt = $conn->prepare("SELECT image_path FROM reports WHERE report_id = ?");
            $stmt->bind_param('s', $reportId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                jsonResponse(false, null, 'Report not found', 404);
            }
            
            $report = $result->fetch_assoc();
            
            // Delete dari database
            $stmt = $conn->prepare("DELETE FROM reports WHERE report_id = ?");
            $stmt->bind_param('s', $reportId);
            
            if ($stmt->execute()) {
                // Hapus file gambar jika ada
                if ($report['image_path'] && file_exists($report['image_path'])) {
                    unlink($report['image_path']);
                }
                
                jsonResponse(true, ['report_id' => $reportId], 'Report deleted successfully');
            } else {
                jsonResponse(false, null, 'Failed to delete report', 500);
            }
            break;
        
        default:
            jsonResponse(false, null, 'Invalid action', 400);
    }
}
