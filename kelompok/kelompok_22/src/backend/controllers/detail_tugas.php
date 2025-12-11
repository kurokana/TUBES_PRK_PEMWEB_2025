<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/config.php';

requireLogin();
$userInfo = getUserInfo();

if (!$userInfo) {
    header('Location: /login.php');
    exit;
}

$userRole = $_SESSION['role'] ?? '';
if (!in_array($userRole, ['petugas', 'admin', 'super_admin'])) {
    header('Location: /');
    exit;
}

// Get report ID from query string
$report_id = $_GET['id'] ?? '';
if (empty($report_id)) {
    header('Location: /petugas.php');
    exit;
}

// Fetch report details
$conn = getDBConnection();
$stmt = $conn->prepare("
    SELECT r.*, 
           u.full_name as reporter_name, 
           u.phone as reporter_phone,
           p.full_name as petugas_name
    FROM reports r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN users p ON r.assigned_to = p.id
    WHERE r.report_id = ?
");
$stmt->bind_param('s', $report_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: /petugas.php');
    exit;
}

$report = $result->fetch_assoc();
$stmt->close();

// Check authorization - petugas hanya bisa lihat tugas mereka sendiri
if ($userRole === 'petugas' && (!$report['assigned_to'] || $report['assigned_to'] != $userInfo['id'])) {
    header('Location: /petugas.php');
    exit;
}

// Handle form submission untuk update progress
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'start_task') {
        // Update status menjadi Diproses
        $stmt = $conn->prepare("UPDATE reports SET status = 'Diproses', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('i', $report['id']);
        if ($stmt->execute()) {
            header('Location: /detail_tugas.php?id=' . $report_id . '&success=start');
            exit;
        }
    } elseif ($action === 'complete_task') {
        // Handle image upload
        $completion_image = null;
        if (isset($_FILES['progress_image']) && $_FILES['progress_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $extension = strtolower(pathinfo($_FILES['progress_image']['name'], PATHINFO_EXTENSION));
            $filename = 'progress_' . $report_id . '_' . time() . '.' . $extension;
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['progress_image']['tmp_name'], $target_path)) {
                $completion_image = '/uploads/' . $filename;
            }
        }
        
        $completion_notes = $_POST['petugas_notes'] ?? '';
        
        // Update status menjadi Selesai dengan catatan dan foto
        $stmt = $conn->prepare("UPDATE reports SET status = 'Selesai', completion_notes = ?, completion_image = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param('ssi', $completion_notes, $completion_image, $report['id']);
        if ($stmt->execute()) {
            header('Location: /detail_tugas.php?id=' . $report_id . '&success=complete');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Tugas - SiPaMaLi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="/petugas.php" class="text-slate-600 hover:text-slate-800">
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
                <div class="w-8 h-8 bg-eco-500 rounded-lg flex items-center justify-center text-white">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <h1 class="text-lg font-bold text-slate-800">Detail Tugas</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-600"><?= htmlspecialchars($userInfo['full_name']) ?></span>
                <a href="?logout=1" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6 max-w-5xl">
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                <?php if ($_GET['success'] === 'start'): ?>
                    Tugas berhasil dimulai!
                <?php elseif ($_GET['success'] === 'complete'): ?>
                    Tugas berhasil diselesaikan dan menunggu validasi admin!
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Report Info Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-eco-500 to-eco-600 px-6 py-4 flex justify-between items-center">
                <div>
                    <h2 class="text-white font-bold text-xl">Laporan #<?= htmlspecialchars($report['report_id']) ?></h2>
                    <p class="text-eco-100 text-sm"><?= htmlspecialchars($report['category']) ?></p>
                </div>
                <div>
                    <?php
                    $statusColors = [
                        'Menunggu' => 'bg-orange-100 text-orange-700 border-orange-300',
                        'Diproses' => 'bg-blue-100 text-blue-700 border-blue-300',
                        'Selesai' => 'bg-green-100 text-green-700 border-green-300',
                        'Tuntas' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
                        'Ditolak' => 'bg-red-100 text-red-700 border-red-300'
                    ];
                    $statusClass = $statusColors[$report['status']] ?? 'bg-slate-100 text-slate-700';
                    ?>
                    <span class="<?= $statusClass ?> px-4 py-2 rounded-lg text-sm font-bold border inline-block">
                        <?= htmlspecialchars($report['status']) ?>
                    </span>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Lokasi & Deskripsi -->
                <div class="md:col-span-2">
                    <h3 class="text-sm font-bold text-slate-700 mb-2 flex items-center">
                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i> Lokasi
                    </h3>
                    <p class="text-slate-800 font-semibold"><?= htmlspecialchars($report['location']) ?></p>
                </div>

                <div class="md:col-span-2">
                    <h3 class="text-sm font-bold text-slate-700 mb-2 flex items-center">
                        <i class="fas fa-file-alt text-blue-500 mr-2"></i> Deskripsi Masalah
                    </h3>
                    <p class="text-slate-600 leading-relaxed"><?= htmlspecialchars($report['description']) ?></p>
                </div>

                <!-- Pelapor Info -->
                <div>
                    <h3 class="text-sm font-bold text-slate-700 mb-2 flex items-center">
                        <i class="fas fa-user text-purple-500 mr-2"></i> Pelapor
                    </h3>
                    <p class="text-slate-800"><?= htmlspecialchars($report['reporter_name']) ?></p>
                    <?php if ($report['reporter_phone']): ?>
                        <p class="text-slate-500 text-sm">
                            <i class="fas fa-phone mr-1"></i> <?= htmlspecialchars($report['reporter_phone']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Petugas Info -->
                <div>
                    <h3 class="text-sm font-bold text-slate-700 mb-2 flex items-center">
                        <i class="fas fa-user-tie text-green-500 mr-2"></i> Ditugaskan ke
                    </h3>
                    <p class="text-slate-800"><?= $report['petugas_name'] ? htmlspecialchars($report['petugas_name']) : 'Belum ditugaskan' ?></p>
                </div>

                <!-- Foto Warga -->
                <?php if ($report['image_path']): ?>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-bold text-slate-700 mb-2 flex items-center">
                            <i class="fas fa-camera text-orange-500 mr-2"></i> Foto dari Warga
                        </h3>
                        <img src="<?= htmlspecialchars($report['image_path']) ?>" alt="Foto Laporan" class="w-full max-w-md rounded-lg border border-slate-200 shadow-sm">
                    </div>
                <?php endif; ?>

                <!-- Foto Progress -->
                <?php if ($report['completion_image']): ?>
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-bold text-slate-700 mb-2 flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i> Foto Progress Petugas
                        </h3>
                        <img src="<?= htmlspecialchars($report['completion_image']) ?>" alt="Foto Progress" class="w-full max-w-md rounded-lg border border-slate-200 shadow-sm">
                    </div>
                <?php endif; ?>

                <!-- Catatan Petugas -->
                <?php if ($report['completion_notes']): ?>
                    <div class="md:col-span-2 bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h3 class="text-sm font-bold text-blue-800 mb-2 flex items-center">
                            <i class="fas fa-sticky-note mr-2"></i> Catatan Petugas
                        </h3>
                        <p class="text-blue-900"><?= htmlspecialchars($report['completion_notes']) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Admin Notes (jika ditolak) -->
                <?php if ($report['admin_notes'] && $report['status'] === 'Ditolak'): ?>
                    <div class="md:col-span-2 bg-red-50 p-4 rounded-lg border border-red-200">
                        <h3 class="text-sm font-bold text-red-800 mb-2 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Alasan Penolakan
                        </h3>
                        <p class="text-red-900"><?= htmlspecialchars($report['admin_notes']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons (hanya untuk petugas yang ditugaskan) -->
        <?php if ($userRole === 'petugas' && $report['assigned_to'] == $userInfo['id']): ?>
            <?php if ($report['status'] === 'Menunggu'): ?>
                <!-- Tombol Mulai Tugas -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Mulai Tugas</h3>
                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulai tugas ini?')">
                        <input type="hidden" name="action" value="start_task">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-bold transition">
                            <i class="fas fa-play mr-2"></i> Mulai Mengerjakan Tugas
                        </button>
                    </form>
                </div>
            <?php elseif ($report['status'] === 'Diproses'): ?>
                <!-- Form Selesaikan Tugas -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Selesaikan Tugas</h3>
                    <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('Apakah Anda yakin tugas sudah selesai?')">
                        <input type="hidden" name="action" value="complete_task">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                <i class="fas fa-camera text-green-500 mr-1"></i> Upload Foto Bukti Penyelesaian <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="progress_image" accept="image/*" required 
                                class="w-full border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500">
                            <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG. Maksimal 5MB</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                <i class="fas fa-sticky-note text-blue-500 mr-1"></i> Catatan Penyelesaian <span class="text-red-500">*</span>
                            </label>
                            <textarea name="petugas_notes" rows="4" required
                                class="w-full border border-slate-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500"
                                placeholder="Jelaskan apa yang telah dikerjakan..."></textarea>
                        </div>
                        
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-bold transition">
                            <i class="fas fa-check mr-2"></i> Tandai Selesai
                        </button>
                    </form>
                </div>
            <?php elseif ($report['status'] === 'Selesai'): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <i class="fas fa-clock text-yellow-600 text-3xl mb-2"></i>
                    <p class="text-yellow-800 font-semibold">Menunggu Validasi Admin</p>
                    <p class="text-yellow-600 text-sm">Tugas Anda sedang direview oleh admin.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <style>
        .bg-eco-500 { background-color: #059669; }
        .bg-eco-600 { background-color: #047857; }
        .text-eco-100 { color: #d1fae5; }
    </style>
</body>
</html>
