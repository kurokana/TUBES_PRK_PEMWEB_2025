<?php
/**
 * Halaman Riwayat Laporan Pelapor
 */
require_once 'includes/config.php';
require_once 'includes/auth.php';

requireLogin();

// Hanya pelapor yang bisa akses
if ($_SESSION['role'] !== 'pelapor') {
    header('Location: index.php');
    exit;
}

$user = getCurrentUser();
if (!$user) {
    logout();
    exit;
}

// Get user's reports
global $conn;
$user_id = $user['id'];

// Filter parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$query = "SELECT r.*, 
                 (SELECT COUNT(*) FROM comments WHERE report_id = r.id) as comment_count
          FROM reports r 
          WHERE r.user_id = ?";

$params = array($user_id);
$types = "i";

// Apply filters
if ($filter !== 'all') {
    $query .= " AND r.status = ?";
    $params[] = $filter;
    $types .= "s";
}

if (!empty($search)) {
    $query .= " AND (r.location LIKE ? OR r.description LIKE ? OR r.report_code LIKE ?)";
    $search_term = "%{$search}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "sss";
}

$query .= " ORDER BY r.created_at DESC";

// Prepare and execute
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$reports = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get stats for filter badges
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as menunggu,
    SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as diproses,
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai
    FROM reports WHERE user_id = ?";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stats_stmt->close();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Laporan - <?php echo SITE_NAME; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #0284c7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .status-menunggu {
            background: #fed7aa;
            color: #9a3412;
            border: 1px solid #fdba74;
        }
        .status-diproses {
            background: #bfdbfe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .status-selesai {
            background: #bbf7d0;
            color: #166534;
            border: 1px solid #86efac;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <a href="index.php" class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-emerald-600 to-sky-600 rounded-lg flex items-center justify-center text-white">
                            <i class="fa-solid fa-leaf text-sm"></i>
                        </div>
                        <span class="text-lg font-bold gradient-text"><?php echo SITE_NAME; ?></span>
                    </a>
                    <span class="text-slate-400">/</span>
                    <span class="text-sm text-slate-600">Riwayat Laporan</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="profile.php" class="text-sm text-slate-600 hover:text-emerald-600 transition">
                        <i class="fa-solid fa-user-edit mr-1"></i> Profil
                    </a>
                    <a href="index.php#lapor" class="text-sm text-slate-600 hover:text-emerald-600 transition">
                        <i class="fa-solid fa-plus-circle mr-1"></i> Lapor Baru
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Riwayat Laporan</h1>
                    <p class="text-slate-600">Semua laporan yang pernah Anda kirim</p>
                </div>
                
                <a href="index.php#lapor" class="inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium rounded-lg hover:shadow-lg transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Lapor Baru</span>
                </a>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-5 rounded-xl border border-slate-200 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Total Laporan</p>
                            <p class="text-2xl font-bold text-slate-800"><?php echo $stats['total']; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center">
                            <i class="fa-solid fa-file-lines text-slate-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-xl border border-slate-200 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Menunggu</p>
                            <p class="text-2xl font-bold text-orange-600"><?php echo $stats['menunggu']; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center">
                            <i class="fa-solid fa-clock text-orange-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-xl border border-slate-200 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Diproses</p>
                            <p class="text-2xl font-bold text-blue-600"><?php echo $stats['diproses']; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fa-solid fa-spinner text-blue-600"></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white p-5 rounded-xl border border-slate-200 card-hover">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Selesai</p>
                            <p class="text-2xl font-bold text-emerald-600"><?php echo $stats['selesai']; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <i class="fa-solid fa-check text-emerald-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Filter Tabs -->
                <div class="flex flex-wrap gap-2">
                    <a href="?filter=all" 
                       class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'all' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?> transition">
                        Semua (<?php echo $stats['total']; ?>)
                    </a>
                    <a href="?filter=Menunggu" 
                       class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'Menunggu' ? 'bg-orange-100 text-orange-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?> transition">
                        Menunggu (<?php echo $stats['menunggu']; ?>)
                    </a>
                    <a href="?filter=Diproses" 
                       class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'Diproses' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?> transition">
                        Diproses (<?php echo $stats['diproses']; ?>)
                    </a>
                    <a href="?filter=Selesai" 
                       class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'Selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'; ?> transition">
                        Selesai (<?php echo $stats['selesai']; ?>)
                    </a>
                </div>
                
                <!-- Search Form -->
                <form method="GET" class="relative">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-3.5 text-slate-400"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Cari laporan..."
                            class="w-full lg:w-64 pl-12 pr-4 py-3 rounded-lg border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition">
                        <?php if ($filter !== 'all'): ?>
                            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reports List -->
        <div class="space-y-4">
            <?php if (empty($reports)): ?>
                <!-- Empty State -->
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-6">
                        <i class="fa-solid fa-inbox text-3xl text-slate-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-700 mb-2">Belum ada laporan</h3>
                    <p class="text-slate-500 mb-6 max-w-md mx-auto">
                        Anda belum mengirim laporan apapun. Mulai laporkan masalah lingkungan di sekitar Anda.
                    </p>
                    <a href="index.php#lapor" class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                        <i class="fa-solid fa-plus"></i>
                        Buat Laporan Pertama
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($reports as $report): ?>
                    <?php
                    // Determine status class
                    $status_class = 'status-menunggu';
                    $status_icon = 'fa-regular fa-clock';
                    
                    if ($report['status'] === 'Diproses') {
                        $status_class = 'status-diproses';
                        $status_icon = 'fa-solid fa-spinner fa-spin';
                    } else if ($report['status'] === 'Selesai') {
                        $status_class = 'status-selesai';
                        $status_icon = 'fa-solid fa-check';
                    }
                    
                    // Determine category icon
                    $category_icon = 'fa-ellipsis';
                    $category_color = 'text-emerald-500 bg-emerald-50';
                    
                    if ($report['category'] === 'Sampah') {
                        $category_icon = 'fa-trash';
                        $category_color = 'text-red-500 bg-red-50';
                    } else if ($report['category'] === 'Drainase') {
                        $category_icon = 'fa-water';
                        $category_color = 'text-blue-500 bg-blue-50';
                    } else if ($report['category'] === 'Jalan') {
                        $category_icon = 'fa-road';
                        $category_color = 'text-gray-500 bg-gray-50';
                    }
                    
                    // Format date
                    $date = date('d M Y', strtotime($report['created_at']));
                    $time = date('H:i', strtotime($report['created_at']));
                    ?>
                    
                    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden card-hover">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-start gap-6">
                                <!-- Left: Icon and Status -->
                                <div class="flex-shrink-0">
                                    <div class="flex flex-col items-center gap-4">
                                        <div class="w-16 h-16 rounded-xl <?php echo $category_color; ?> flex items-center justify-center">
                                            <i class="fa-solid <?php echo $category_icon; ?> text-xl"></i>
                                        </div>
                                        
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <i class="<?php echo $status_icon; ?>"></i>
                                            <?php echo htmlspecialchars($report['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Middle: Report Details -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-slate-800 mb-1">
                                                <?php echo htmlspecialchars($report['category']); ?>
                                            </h3>
                                            <p class="text-sm text-slate-600">
                                                <i class="fa-solid fa-location-dot text-slate-400 mr-2"></i>
                                                <?php echo htmlspecialchars($report['location']); ?>
                                            </p>
                                        </div>
                                        
                                        <div class="text-right">
                                            <p class="text-sm text-slate-500 mb-1">ID Laporan</p>
                                            <p class="font-mono text-sm font-bold text-slate-700"><?php echo htmlspecialchars($report['report_code']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <p class="text-slate-600 mb-4"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                                    
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-calendar"></i>
                                            <?php echo $date; ?> pukul <?php echo $time; ?>
                                        </span>
                                        
                                        <?php if ($report['image']): ?>
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-image"></i>
                                            <button onclick="viewImage('<?php echo htmlspecialchars($report['image']); ?>')" 
                                                    class="text-emerald-600 hover:text-emerald-700">
                                                Lihat Foto
                                            </button>
                                        </span>
                                        <?php endif; ?>
                                        
                                        <span class="flex items-center gap-2">
                                            <i class="fa-solid fa-comment"></i>
                                            <?php echo $report['comment_count']; ?> komentar
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($report['rejection_reason'])): ?>
                                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <div class="flex items-start gap-2">
                                            <i class="fa-solid fa-triangle-exclamation text-red-500 mt-0.5"></i>
                                            <div>
                                                <p class="text-sm font-medium text-red-700 mb-1">Alasan Penolakan</p>
                                                <p class="text-sm text-red-600"><?php echo htmlspecialchars($report['rejection_reason']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Right: Actions -->
                                <div class="flex-shrink-0">
                                    <div class="flex lg:flex-col gap-2">
                                        <button onclick="viewReportDetail(<?php echo $report['id']; ?>)"
                                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-medium transition">
                                            <i class="fa-solid fa-eye mr-2"></i>Detail
                                        </button>
                                        
                                        <?php if ($report['status'] === 'Menunggu'): ?>
                                        <button onclick="confirmDeleteReport(<?php echo $report['id']; ?>, '<?php echo htmlspecialchars($report['report_code']); ?>')"
                                                class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm font-medium transition">
                                            <i class="fa-solid fa-trash mr-2"></i>Hapus
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Report Updates (if any) -->
                        <?php
                        // Get updates for this report
                        $updates_query = "SELECT * FROM report_updates WHERE report_id = ? ORDER BY created_at DESC LIMIT 3";
                        $updates_stmt = $conn->prepare($updates_query);
                        $updates_stmt->bind_param("i", $report['id']);
                        $updates_stmt->execute();
                        $updates_result = $updates_stmt->get_result();
                        $updates = $updates_result->fetch_all(MYSQLI_ASSOC);
                        $updates_stmt->close();
                        
                        if (!empty($updates)): ?>
                        <div class="border-t border-slate-100">
                            <div class="p-4 bg-slate-50">
                                <h4 class="text-sm font-semibold text-slate-700 mb-3">Update Terbaru</h4>
                                <div class="space-y-3">
                                    <?php foreach ($updates as $update): ?>
                                    <div class="flex items-start gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center">
                                                <i class="fa-solid fa-user-gear text-xs text-slate-500"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-medium text-slate-700">Petugas</span>
                                                <span class="text-xs text-slate-500">
                                                    <?php echo date('d M H:i', strtotime($update['created_at'])); ?>
                                                </span>
                                            </div>
                                            <p class="text-sm text-slate-600"><?php echo nl2br(htmlspecialchars($update['notes'])); ?></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Pagination (if many reports) -->
        <?php if (count($reports) > 10): ?>
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center gap-2">
                <button class="w-10 h-10 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button class="w-10 h-10 rounded-lg bg-emerald-600 text-white font-medium">1</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">2</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">3</button>
                <span class="px-2 text-slate-400">...</span>
                <button class="w-10 h-10 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">5</button>
                <button class="w-10 h-10 rounded-lg border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black/80 z-50 hidden items-center justify-center p-4">
        <div class="relative max-w-4xl w-full">
            <button onclick="closeImageModal()" 
                    class="absolute -top-12 right-0 w-10 h-10 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition">
                <i class="fa-solid fa-times"></i>
            </button>
            <img id="modalImage" src="" class="w-full h-auto rounded-lg shadow-2xl">
        </div>
    </div>

    <script>
        // View image in modal
        function viewImage(imagePath) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            
            modalImage.src = 'uploads/' + imagePath;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        
        // Close image modal
        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
        
        // View report detail
        function viewReportDetail(reportId) {
            // For now, just show a simple detail view
            // In a real application, this would fetch and show more details
            alert('Detail laporan ID: ' + reportId + '\nFitur detail lengkap akan segera tersedia.');
        }
        
        // Confirm report deletion
        function confirmDeleteReport(reportId, reportCode) {
            if (confirm(`Apakah Anda yakin ingin menghapus laporan ${reportCode}?\nLaporan yang dihapus tidak dapat dikembalikan.`)) {
                // Send delete request
                fetch(`api.php?action=deleteReport&id=${reportId}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Laporan berhasil dihapus!');
                        location.reload();
                    } else {
                        alert('Gagal menghapus laporan: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                });
            }
        }
        
        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('imageModal');
            if (e.target === modal) {
                closeImageModal();
            }
        });
        
        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts1 = document.querySelectorAll('[class*="bg-"]:not(.card-hover)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.style.opacity !== '0') {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }
                }, 5000);
            });
        });
    </script>
</body>
</html>