<?php
// TANGGAL: 9 Desember 2025

// --- 1. SETUP & INCLUDES ---
require_once 'includes/auth.php'; 
require_once 'includes/config.php'; 
require_once 'includes/admin_utils.php'; // Pastikan fungsi utilitas ada di sini

requireAdmin(); 

$adminInfo = getUserInfo();
$db_conn = getDBConnection();
// Pastikan getDashboardStats() mengembalikan array yang berisi semua kunci
$stats = getDashboardStats($db_conn); // Digunakan untuk cards dan notifikasi

// --- 2. LOGIC NAVIGASI & FILTER ---
$current_page = $_GET['page'] ?? 'dashboard';

$menu_items = [
    'dashboard' => ['icon' => 'fa-tachometer-alt', 'title' => 'Dashboard Utama'],
    'laporan_warga' => ['icon' => 'fa-users', 'title' => 'Laporan Warga'],
    'laporan_petugas' => ['icon' => 'fa-user-tie', 'title' => 'Validasi Petugas'], // Ganti judul
    'statistik_laporan' => ['icon' => 'fa-chart-bar', 'title' => 'Statistik Laporan'],
    'riwayat_laporan' => ['icon' => 'fa-history', 'title' => 'Riwayat Laporan'],
    'pengaturan_admin' => ['icon' => 'fa-cog', 'title' => 'Pengaturan Akun'],
];

$page_title = $menu_items[$current_page]['title'] ?? "Dashboard";

$all_reports = [];
$active_reports = [];
$validation_reports = [];
$history_reports = [];  

if ($current_page === 'dashboard') {
    $current_filter = $_GET['status'] ?? 'Semua';
    $status_db_filter = null;
    if ($current_filter != 'Semua') {
        if ($current_filter === 'Diterima') {
            $status_db_filter = 'Diproses'; 
        } elseif ($current_filter === 'Tuntas') { 
            $status_db_filter = 'Tuntas'; 
        } elseif ($current_filter === 'Lainnya') {
             // Asumsi: Semua status non-terminal adalah 'Menunggu', 'Diproses', 'Selesai'
            $status_db_filter = 'Selesai'; 
        } else {
            $status_db_filter = $current_filter; 
        }
    }
    $all_reports = getAllReports($status_db_filter); 
} elseif ($current_page === 'laporan_warga') {
    $active_reports = getReportsByStatus($db_conn, ['Menunggu']);
} elseif ($current_page === 'laporan_petugas') {
    $validation_reports = getReportsByStatus($db_conn, ['Selesai', 'Diproses']); 
} elseif ($current_page === 'riwayat_laporan') {
    $history_filter = $_GET['history_filter'] ?? 'semua';
    
    if ($history_filter === 'tuntas') {
        $history_reports = getReportsByStatus($db_conn, ['Tuntas']); 
    } elseif ($history_filter === 'ditolak') {
        $history_reports = getReportsByStatus($db_conn, ['Ditolak']); 
    } else {
        // 'semua' - tampilkan kedua status
        $history_reports = getReportsByStatus($db_conn, ['Tuntas', 'Ditolak']); 
    }
}

// Data untuk Chart.js (Statistik Laporan)
// Tambahkan Tuntas
$chart_labels = ['Menunggu', 'Diproses', 'Selesai', 'Tuntas', 'Ditolak'];
// --- PERBAIKAN: Menggunakan operator ?? untuk menghindari "Undefined array key" ---
$chart_values = [
    $stats['pending_count'] ?? 0, 
    $stats['processing_count'] ?? 0, 
    $stats['completed_count'] ?? 0, 
    $stats['tuntas_count'] ?? 0, 
    $stats['rejected_count'] ?? 0
];
$chart_colors = ['#f59e0b', '#3b82f6', '#059669', '#10b981', '#ef4444'];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - SiPaMaLi</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], },
                    colors: {
                        smart: { light: '#e0f2fe', DEFAULT: '#0284c7', dark: '#0c4a6e', },
                        eco: { light: '#d1fae5', DEFAULT: '#059669', dark: '#064e3b', }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar { transition: width 0.3s ease-in-out; }
        .modal-body { max-height: 80vh; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <div class="flex min-h-screen">
        
        <div id="sidebar" class="w-64 bg-gradient-to-br from-eco-dark to-smart-dark text-white flex flex-col fixed h-full z-10 shadow-lg sidebar">
            <div class="p-4 text-2xl font-bold border-b border-gray-700 bg-gray-900/50">SiPaMaLi Admin</div>
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                
                <div class="py-2 text-xs font-semibold uppercase text-eco-light/70">Main Menu</div>
                
                <?php foreach ($menu_items as $key => $item): 
                    $isActive = ($current_page === $key);
                    $url = 'admin.php?page=' . $key;
                    $class = $isActive 
                        ? 'bg-white/10 text-white shadow-md border border-white/20' 
                        : 'text-gray-200 hover:bg-white/5 hover:text-white';
                ?>
                
                <a href="<?= $url ?>" class="flex items-center p-3 rounded-lg text-sm font-semibold transition duration-150 ease-in-out <?= $class ?>">
                    <i class="fas <?= $item['icon'] ?> w-5 mr-3"></i> <?= $item['title'] ?>
                </a>
                
                <?php endforeach; ?>
            </nav>
        </div>
        
        <div id="content-wrapper" class="flex-1 ml-64"> 
            
            <header class="bg-white border-b border-slate-200 shadow-sm sticky top-0 z-20">
                <div class="flex justify-between items-center h-16 px-8">
                    
                    <h2 class="text-xl font-bold text-slate-800 hidden sm:block"><?= htmlspecialchars($page_title) ?></h2>
                    
                    <div class="flex items-center space-x-4 relative ml-auto">
                        
                        <button id="notification-btn" class="text-slate-600 hover:text-indigo-600 p-2 rounded-full relative">
                            <i class="fas fa-bell text-xl"></i>
                            <?php if (($stats['pending_count'] ?? 0) > 0 || ($stats['completed_count'] ?? 0) > 0): // Notifikasi untuk Menunggu & Selesai ?>
                                <span class="absolute top-1 right-1 h-3 w-3 bg-red-500 rounded-full border-2 border-white text-xs text-white flex items-center justify-center font-bold"></span>
                            <?php endif; ?>
                        </button>
                        
                        <div class="relative">
                            <button id="account-dropdown-btn" class="flex items-center space-x-2 text-slate-700 hover:text-indigo-600 p-1 rounded-lg">
                                <span class="text-sm font-semibold hidden md:inline"><?= htmlspecialchars($adminInfo['full_name']) ?></span>
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                    <?= substr($adminInfo['full_name'], 0, 1) ?>
                                </div>
                                <i class="fas fa-caret-down text-sm"></i>
                            </button>
                            
                            <div id="account-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl py-2 z-30 hidden border border-slate-100">
                                <a href="admin.php?page=pengaturan_admin" class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                    <i class="fas fa-cog w-4 mr-2"></i> Pengaturan Akun
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt w-4 mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </header>
            
            <div class="p-8">
                 
                <?php switch ($current_page): 
                    
                    case 'dashboard': ?>
                        
                        <div class="bg-gradient-to-r from-eco to-smart rounded-2xl p-8 mb-8 text-white shadow-xl">
                            <h2 class="text-3xl font-bold mb-2">Selamat Datang, <?php echo htmlspecialchars($adminInfo['full_name']); ?>! 👋</h2>
                            <p class="text-white/90">Kelola dan pantau laporan masalah lingkungan dari warga.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                                <div class="flex justify-between items-start"><div><p class="text-xs font-bold text-slate-400 uppercase">Total Masuk</p><h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $stats['total_reports'] ?? 0; ?></h3></div><div class="p-2 bg-indigo-50 rounded-lg text-indigo-600"><i class="fa-solid fa-inbox"></i></div></div>
                            </div>
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                                <div class="flex justify-between items-start"><div><p class="text-xs font-bold text-slate-400 uppercase">Menunggu</p><h3 class="text-3xl font-bold text-orange-500 mt-1"><?= $stats['pending_count'] ?? 0; ?></h3></div><div class="p-2 bg-orange-50 rounded-lg text-orange-600"><i class="fa-solid fa-clock"></i></div></div>
                            </div>
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                                <div class="flex justify-between items-start"><div><p class="text-xs font-bold text-slate-400 uppercase">Diproses</p><h3 class="text-3xl font-bold text-blue-500 mt-1"><?= $stats['processing_count'] ?? 0; ?></h3></div><div class="p-2 bg-blue-50 rounded-lg text-blue-600"><i class="fa-solid fa-spinner"></i></div></div>
                            </div>
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                                <div class="flex justify-between items-start"><div><p class="text-xs font-bold text-slate-400 uppercase">Selesai (Perlu Validasi)</p><h3 class="text-3xl font-bold text-green-500 mt-1"><?= $stats['completed_count'] ?? 0; ?></h3></div><div class="p-2 bg-green-50 rounded-lg text-green-600"><i class="fa-solid fa-clipboard-check"></i></div></div>
                            </div>
                            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                                <div class="flex justify-between items-start"><div><p class="text-xs font-bold text-slate-400 uppercase">Tuntas</p><h3 class="text-3xl font-bold text-emerald-500 mt-1"><?= $stats['tuntas_count'] ?? 0; ?></h3></div><div class="p-2 bg-emerald-50 rounded-lg text-emerald-600"><i class="fa-solid fa-check-double"></i></div></div>
                            </div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-2">
                            <h3 class="text-xl font-bold text-slate-800">Tabel Laporan</h3>
                            <div class="flex space-x-2">
                                <?php
                                $filters = ['Semua', 'Menunggu', 'Diproses', 'Tuntas', 'Ditolak']; 
                                foreach ($filters as $filter):
                                    $isActive = ($current_filter == $filter); 
                                    $url = ($filter == 'Semua') ? 'admin.php?page=dashboard' : 'admin.php?page=dashboard&status=' . urlencode($filter);
                                    $class = $isActive ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-indigo-100 hover:text-indigo-600';
                                ?>
                                    <a href="<?= $url ?>" 
                                    class="px-4 py-2 rounded-lg text-sm font-semibold transition duration-150 ease-in-out <?= $class ?>">
                                        <?= htmlspecialchars($filter) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Laporan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelapor</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                            <th class="px-6 py-3"></th> </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="adminTableBody">
                                        
                                        <?php if (empty($all_reports)): ?>
                                        <tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada laporan yang masuk.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($all_reports as $report): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($report['report_id']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($report['category']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($report['location']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($report['reported_by']) ?></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php 
                                                            if ($report['status'] === 'Menunggu') echo 'bg-yellow-100 text-yellow-800';
                                                            elseif ($report['status'] === 'Diproses') echo 'bg-blue-100 text-blue-800';
                                                            elseif ($report['status'] === 'Selesai') echo 'bg-green-100 text-green-800';
                                                            elseif ($report['status'] === 'Tuntas') echo 'bg-emerald-100 text-emerald-800'; // Status Tuntas
                                                            elseif ($report['status'] === 'Ditolak') echo 'bg-red-100 text-red-800';
                                                            else echo 'bg-gray-100 text-gray-800';
                                                        ?>">
                                                        <?= htmlspecialchars($report['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= date('d M Y H:i', strtotime($report['created_at'])) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button onclick="showDetailModal(<?= $report['id'] ?>, 'dashboard')" class="text-indigo-600 hover:text-indigo-900">Detail</button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php break; ?>
                        
                    <?php case 'laporan_warga': ?>
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold mb-2 text-slate-800">Laporan Aktif Menunggu Penugasan</h3>
                            <p class="text-slate-500">Gunakan halaman ini untuk menugaskan laporan baru ('Menunggu') atau laporan yang sedang diproses ('Diproses') kepada petugas.</p>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-indigo-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">ID Laporan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Kategori</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Lokasi</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Ditugaskan Kepada</th>
                                            <th class="px-6 py-3">Aksi</th> 
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        
                                        <?php if (empty($active_reports)): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                Tidak ada laporan aktif yang memerlukan penugasan saat ini.
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        
                                            <?php foreach ($active_reports as $report): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($report['report_id']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= htmlspecialchars($report['category']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= htmlspecialchars($report['location']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        <?php 
                                                            if ($report['status'] === 'Menunggu') echo 'bg-yellow-100 text-yellow-800';
                                                            elseif ($report['status'] === 'Diproses') echo 'bg-blue-100 text-blue-800';
                                                            else echo 'bg-gray-100 text-gray-800';
                                                        ?>">
                                                        <?= htmlspecialchars($report['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= htmlspecialchars($report['assigned_to_name'] ?? 'Belum Ditugaskan') ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button onclick="showDetailModal(<?= $report['id'] ?>, 'assign')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-xs font-bold transition">
                                                        <i class="fa-solid fa-user-plus mr-1"></i> Penugasan
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                        <?php endif; ?>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php break; ?>
                        
                    <?php case 'laporan_petugas': ?>
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold mb-2 text-slate-800">Validasi Laporan Selesai</h3>
                            <p class="text-slate-500">Ini adalah halaman untuk memvalidasi laporan yang telah diselesaikan oleh petugas (Status 'Selesai').</p>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-green-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">ID Laporan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Kategori</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Lokasi</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Ditugaskan Kepada</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-green-700 uppercase tracking-wider">Status Petugas</th>
                                            <th class="px-6 py-3">Aksi Validasi</th> 
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        
                                        <?php if (empty($validation_reports)): ?>
                                        <tr>
                                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                                Tidak ada laporan yang siap divalidasi saat ini.
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        
                                            <?php foreach ($validation_reports as $report): ?>
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <?= htmlspecialchars($report['report_id']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= htmlspecialchars($report['category']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= htmlspecialchars($report['location']) ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <?= htmlspecialchars($report['assigned_to_name'] ?? 'Tidak Diketahui') ?>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        <?= htmlspecialchars($report['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button onclick="showDetailModal(<?= $report['id'] ?>, 'validate')" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-xs font-bold transition">
                                                        <i class="fa-solid fa-check-square mr-1"></i> Validasi
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                        <?php endif; ?>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php break; ?>
                        
                    <?php case 'statistik_laporan': ?>
                        <div class="bg-white p-6 rounded-xl shadow border border-slate-200">
                            <h3 class="text-xl font-bold mb-4">Distribusi Laporan Berdasarkan Status</h3>
                            <div class="w-full max-w-4xl mx-auto">
                                <canvas id="reportStatusChart"></canvas>
                            </div>
                        </div>
                        <?php break; ?>
                        
                    <?php case 'riwayat_laporan':?>
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Riwayat Laporan Selesai & Diarsipkan</h2>
                        
                        <!-- TAB FILTER -->
                        <div class="flex gap-3 mb-6 border-b border-gray-200">
                            <?php 
                            $history_filter = $_GET['history_filter'] ?? 'semua';
                            $filter_tabs = [
                                'semua' => ['label' => 'Semua Laporan', 'icon' => 'fa-list'],
                                'tuntas' => ['label' => 'Laporan Tuntas', 'icon' => 'fa-check-circle'],
                                'ditolak' => ['label' => 'Laporan Ditolak', 'icon' => 'fa-ban'],
                            ];
                            ?>
                            <?php foreach ($filter_tabs as $key => $tab): ?>
                            <a href="?page=riwayat_laporan&history_filter=<?= $key ?>" 
                               class="px-4 py-3 font-medium transition-colors border-b-2 <?= $history_filter === $key ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-600 hover:text-gray-800' ?>">
                                <i class="fa-solid <?= $tab['icon'] ?> mr-2"></i><?= $tab['label'] ?>
                            </a>
                            <?php endforeach; ?>
                        </div>

                        <?php
                        // Variabel yang diambil di Langkah 1 (A)
                        $reports_to_display = $history_reports ?? [];
                        if (!empty($reports_to_display)):
                            ?>
                            <div class="overflow-x-auto bg-white rounded-lg shadow">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Laporan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Petugas</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach ($reports_to_display as $report): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($report['report_id']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($report['category']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($report['location']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php 
                                                $status_class = $report['status'] === 'Tuntas' 
                                                    ? 'bg-green-100 text-green-800' 
                                                    : 'bg-red-100 text-red-800';
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                                    <?= htmlspecialchars($report['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($report['assigned_to_name'] ?? 'N/A') ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <span title="<?= htmlspecialchars($report['admin_notes'] ?? '-') ?>" class="truncate inline-block max-w-xs">
                                                    <?= htmlspecialchars(substr($report['admin_notes'] ?? '-', 0, 30)) ?>...
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button onclick="showDetailModal(<?= $report['id'] ?>, 'history')" 
                                                        class="text-indigo-600 hover:text-indigo-900">
                                                    <i class="fa-solid fa-eye mr-1"></i>Detail
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                        else:
                            $filter_msg = match($history_filter) {
                                'tuntas' => 'Tidak ada laporan yang sudah divalidasi (Tuntas).',
                                'ditolak' => 'Tidak ada laporan yang ditolak.',
                                default => 'Tidak ada laporan dalam riwayat arsip.'
                            };
                            echo "<p class='text-gray-500 text-center py-8'><i class='fa-solid fa-inbox mr-2'></i>" . $filter_msg . "</p>";
                        endif;
                        break;?>
                        
                    <?php case 'pengaturan_admin': ?>
                        <div class="max-w-5xl mx-auto">
                            <!-- Header Section -->
                            <div class="mb-8">
                                <h1 class="text-3xl font-bold text-slate-800 mb-2">Pengaturan Akun</h1>
                                <p class="text-slate-600">Kelola informasi pribadi dan keamanan akun Administrator Anda.</p>
                            </div>

                            <!-- Main Content - 2 Equal Columns -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                
                                <!-- Left Column: Informasi Pribadi -->
                                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 h-fit">
                                    <div class="flex items-center mb-6 pb-4 border-b border-eco-light">
                                        <div class="w-10 h-10 rounded-lg bg-eco-light flex items-center justify-center text-eco mr-3">
                                            <i class="fas fa-user-circle text-lg"></i>
                                        </div>
                                        <h2 class="text-lg font-bold text-slate-800">Informasi Pribadi</h2>
                                    </div>
                                    
                                    <form action="admin.php?page=pengaturan_admin&action=update_profile" method="POST" class="space-y-4">
                                        <div>
                                            <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-2">
                                                <i class="fas fa-user mr-1.5 text-eco"></i>Nama Lengkap
                                            </label>
                                            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($adminInfo['full_name']) ?>" 
                                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-eco focus:border-transparent transition bg-white"
                                                placeholder="Masukkan nama lengkap" required>
                                        </div>
                                        
                                        <div>
                                            <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">
                                                <i class="fas fa-at mr-1.5 text-eco"></i>Username
                                            </label>
                                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($adminInfo['username']) ?>" 
                                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-eco focus:border-transparent transition bg-white"
                                                placeholder="Masukkan username" required>
                                        </div>

                                        <div class="pt-2">
                                            <button type="submit" class="w-full px-4 py-2.5 bg-eco hover:bg-eco-dark text-white rounded-lg font-semibold transition flex items-center justify-center gap-2">
                                                <i class="fas fa-save"></i> Simpan Informasi
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Right Column: Keamanan (Password) -->
                                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 h-fit">
                                    <div class="flex items-center mb-6 pb-4 border-b border-eco-light">
                                        <div class="w-10 h-10 rounded-lg bg-eco-light flex items-center justify-center text-eco mr-3">
                                            <i class="fas fa-lock text-lg"></i>
                                        </div>
                                        <h2 class="text-lg font-bold text-slate-800">Keamanan</h2>
                                    </div>
                                    
                                    <div class="bg-eco-light border border-eco rounded-lg p-4 mb-5 flex items-start gap-3">
                                        <i class="fas fa-shield-alt text-eco flex-shrink-0 mt-0.5"></i>
                                        <p class="text-sm text-eco-dark">Gunakan password yang kuat dengan kombinasi huruf, angka, dan simbol.</p>
                                    </div>
                                    
                                    <form action="admin.php?page=pengaturan_admin&action=update_password" method="POST" class="space-y-4">
                                        <div>
                                            <label for="current_password" class="block text-sm font-semibold text-slate-700 mb-2">
                                                <i class="fas fa-key mr-1.5 text-eco"></i>Password Saat Ini
                                            </label>
                                            <input type="password" id="current_password" name="current_password"
                                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-eco focus:border-transparent transition bg-white"
                                                placeholder="Masukkan password saat ini" required>
                                        </div>
                                        
                                        <div>
                                            <label for="new_password" class="block text-sm font-semibold text-slate-700 mb-2">
                                                <i class="fas fa-lock-open mr-1.5 text-eco"></i>Password Baru
                                            </label>
                                            <input type="password" id="new_password" name="new_password"
                                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-eco focus:border-transparent transition bg-white"
                                                placeholder="Masukkan password baru (minimal 8 karakter)" required>
                                        </div>
                                        
                                        <div>
                                            <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">
                                                <i class="fas fa-check-circle mr-1.5 text-eco"></i>Konfirmasi Password
                                            </label>
                                            <input type="password" id="confirm_password" name="confirm_password"
                                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-eco focus:border-transparent transition bg-white"
                                                placeholder="Ulangi password baru" required>
                                        </div>

                                        <div class="pt-2">
                                            <button type="submit" class="w-full px-4 py-2.5 bg-eco hover:bg-eco-dark text-white rounded-lg font-semibold transition flex items-center justify-center gap-2">
                                                <i class="fas fa-shield-alt"></i> Ubah Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Info Box - Full Width -->
                            <div class="mt-8 bg-eco-light border-l-4 border-eco rounded-lg p-5">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-lightbulb text-eco text-xl flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="font-semibold text-eco-dark mb-2">💡 Tips Keamanan Akun:</p>
                                        <ul class="space-y-1 text-sm text-eco-dark">
                                            <li><i class="fas fa-check-circle text-eco mr-2"></i>Ubah password secara berkala (minimal setiap 3 bulan)</li>
                                            <li><i class="fas fa-check-circle text-eco mr-2"></i>Jangan gunakan informasi pribadi sebagai password</li>
                                            <li><i class="fas fa-check-circle text-eco mr-2"></i>Logout setiap selesai bekerja</li>
                                            <li><i class="fas fa-check-circle text-eco mr-2"></i>Gunakan koneksi internet yang aman</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php break; ?>
                        
                <?php endswitch; ?>
                 
            </div>
            
        </div>
    </div>
    
    <div id="detailModal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4" onclick="if(event.target.id === 'detailModal') toggleModal(false)">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition-all duration-300 scale-95 opacity-0" id="detailModalContent">
            <div class="p-6 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-800" id="modal-report-id">Detail Laporan</h3>
                <button onclick="toggleModal(false)" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times"></i></button>
            </div>
            
            <div class="p-6 max-h-[80vh] overflow-y-auto modal-body">
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div><p class="text-xs text-slate-500 font-medium uppercase">Status Saat Ini</p><p class="font-bold text-lg" id="modal-status">...</p></div>
                    <div><p class="text-xs text-slate-500 font-medium uppercase">Ditugaskan Kepada</p><p class="font-semibold" id="modal-assigned-to">...</p></div>
                    <div><p class="text-xs text-slate-500 font-medium uppercase">Kategori</p><p class="font-semibold" id="modal-category">...</p></div>
                    <div><p class="text-xs text-slate-500 font-medium uppercase">Lokasi</p><p class="font-semibold" id="modal-location">...</p></div>
                    <div class="col-span-2"><p class="text-xs text-slate-500 font-medium uppercase">Pelapor</p><p class="font-semibold" id="modal-reporter">...</p></div>
                </div>
                
                <div class="mb-4"><p class="text-xs text-slate-500 font-medium uppercase mb-1">Deskripsi Masalah</p><p class="text-slate-700 p-3 bg-slate-50 rounded-lg" id="modal-description">...</p></div>
                <div class="mb-6 border-b pb-4"><p class="text-xs text-slate-500 font-medium uppercase mb-2">Foto Bukti</p><img id="modal-image" src="" alt="Bukti Laporan" class="w-full h-auto max-h-60 object-cover rounded-lg border border-slate-200"><p id="no-image" class="text-center text-slate-400 py-4 hidden"><i class="fa-solid fa-image-slash mr-1"></i> Tidak ada foto</p></div>
                
                <form id="assignmentForm" onsubmit="handleAssignmentSubmit(event)" class="mt-4 p-4 border rounded-lg bg-indigo-50/50">
                    <h4 class="text-md font-bold mb-3 text-indigo-700"><i class="fa-solid fa-user-plus mr-1"></i> Penugasan Laporan</h4>
                    
                    <input type="hidden" name="id" id="assign_report_id"> 
                    
                    <div class="mb-3">
                        <label for="assigned_to" class="block text-sm font-medium text-slate-700">Tugaskan kepada:</label>
                        <select class="w-full border border-slate-300 p-2 rounded-lg mt-1 focus:ring-indigo-500 focus:border-indigo-500" id="assigned_to" name="assigned_to" required>
                            </select>
                        <p class="text-xs text-slate-500 mt-1">Memilih petugas akan mengubah status menjadi 'Diproses'. Memilih 'Kosongkan' akan mengembalikan status menjadi 'Menunggu'.</p>
                    </div>
                    
                    <button type="submit" class="w-full bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700 font-semibold transition">
                        Simpan Penugasan
                    </button>
                </form>
                
                <div id="validationForm" style="display:none;" class="mt-4 p-4 border rounded-lg bg-indigo-50/50">
                    <h4 class="text-md font-bold mb-3 text-indigo-700"><i class="fa-solid fa-check-circle mr-1"></i> Validasi Laporan</h4>
                    
                    <input type="hidden" id="validate_report_id_pk" name="id"> 

                    <div class="mb-3">
                        <label for="admin_notes" class="block text-sm font-medium text-slate-700">Catatan Admin (Opsional untuk Terima, Wajib untuk Tolak):</label>
                        <textarea id="admin_notes" rows="3" class="w-full border border-slate-300 p-2 rounded-lg mt-1 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="flex gap-3">
                        <button onclick="handleValidationSubmit('validateReport')" type="button" class="w-1/2 bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 font-semibold transition">
                            <i class="fa-solid fa-check mr-1"></i> Terima (Tuntas)
                        </button>
                        <button onclick="handleValidationSubmit('rejectValidation')" type="button" class="w-1/2 bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 font-semibold transition">
                            <i class="fa-solid fa-times mr-1"></i> Tolak (Kembali Diproses)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<script>
    const API_URL = 'api.php';
    
    // PHP Variables injected into JS (Pastikan semua variabel ini sudah diinisialisasi di PHP)
    // Menggunakan operator coalescing untuk menjamin variabel selalu berupa array kosong jika null/undefined
    const phpAllReports = <?= json_encode($all_reports ?? []); ?>;
    const phpActiveReports = <?= json_encode($active_reports ?? []); ?>;
    const phpValidationReports = <?= json_encode($validation_reports ?? []); ?>;
    const phpHistoryReports = <?= json_encode($history_reports ?? []); ?>;
    const phpChartLabels = <?= json_encode($chart_labels ?? []); ?>;
    const phpChartValues = <?= json_encode($chart_values ?? []); ?>;
    const phpChartColors = <?= json_encode($chart_colors ?? []); ?>;
    
    const phpCurrentPage = '<?= $current_page ?>';
    const pendingCount = <?= $stats['pending_count'] ?? 0 ?>;
    const completedCount = <?= $stats['completed_count'] ?? 0 ?>;

    // Gabungkan SEMUA laporan yang mungkin ditampilkan di berbagai halaman
    let reports = [
        ...phpAllReports, 
        ...phpActiveReports, 
        ...phpValidationReports, 
        ...phpHistoryReports
    ]; 

    // Bersihkan duplikasi dan pastikan hanya satu entri per ID
    let reportMap = new Map();
    reports.forEach(r => reportMap.set(r.id, r));
    reports = Array.from(reportMap.values());

    let petugasList = [];


    // --- MODAL LOGIC ---
    function toggleModal(show = true) {
        const modal = document.getElementById('detailModal');
        const content = document.getElementById('detailModalContent');
        if (show) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
            }, 50);
        } else {
            content.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        }
    }

    // Fungsi ini dipanggil dari tombol "Detail"
    function showDetailModal(reportId) {
        const assignmentForm = document.getElementById('assignmentForm');
        const validationForm = document.getElementById('validationForm');

        const reportIdStr = String(reportId); 
        const report = reports.find(r => String(r.id) === reportIdStr);
        
        if (!report) {
            alert("Detail laporan tidak ditemukan.");
            return;
        }

        // Sembunyikan semua form secara default
        if (assignmentForm) assignmentForm.style.display = 'none';
        if (validationForm) validationForm.style.display = 'none';
        
        // Isi ID PK ke form
        if (document.getElementById('assign_report_id')) document.getElementById('assign_report_id').value = report.id;
        if (document.getElementById('validate_report_id_pk')) document.getElementById('validate_report_id_pk').value = report.id;

        // Logika Tampil Form
        if (phpCurrentPage === 'laporan_warga') {
            if (assignmentForm) {
                assignmentForm.style.display = 'block'; 
                const assignedToDropdown = document.getElementById('assigned_to');
                if (assignedToDropdown) assignedToDropdown.value = report.assigned_to || ""; 
            }
        } else if (phpCurrentPage === 'laporan_petugas') {
            // Tampilkan form validasi jika statusnya 'Selesai' atau 'Diproses'
            if ((report.status === 'Selesai' || report.status === 'Diproses') && validationForm) {
                validationForm.style.display = 'block';
                document.getElementById('admin_notes').value = report.admin_notes || ''; 
            }
        }

        document.getElementById('modal-report-id').innerText = `Detail Laporan: ${report.report_id} (${report.status})`;
        document.getElementById('modal-status').innerText = report.status;
        document.getElementById('modal-assigned-to').innerText = report.assigned_to_name || 'Belum Ditugaskan'; 
        document.getElementById('modal-category').innerText = report.category;
        document.getElementById('modal-location').innerText = report.location;
        document.getElementById('modal-reporter').innerText = report.reported_by || 'Anonim'; 
        document.getElementById('modal-description').innerText = report.description;

        const imgEl = document.getElementById('modal-image');
        const noImgEl = document.getElementById('no-image');
        if (report.image_path) { 
            imgEl.src = report.image_path; 
            imgEl.classList.remove('hidden');
            noImgEl.classList.add('hidden');
        } else {
            imgEl.classList.add('hidden');
            noImgEl.classList.remove('hidden');
        }
        
        toggleModal(true);
    }

    // --- FETCH & RENDER PETUGAS ---
    async function fetchPetugas() {
        try {
            const response = await fetch(`${API_URL}?action=getPetugas`);
            if (!response.ok) {
                 throw new Error(`Gagal mengambil data petugas. Status: ${response.status} ${response.statusText}`);
            }
            const result = await response.json();
            if (result.success) {
                petugasList = result.data;
                populatePetugasDropdown();
            } else {
                console.error('Failed to fetch petugas:', result.message);
            }
        } catch (error) {
            console.error('Error fetching petugas:', error);
        }
    }

    function populatePetugasDropdown() {
        const dropdown = document.getElementById('assigned_to');
        if (!dropdown) return; 
        
        dropdown.innerHTML = '<option value="">-- Pilih Petugas (Kosongkan) --</option>'; 
        petugasList.forEach(p => {
            const option = document.createElement('option');
            option.value = p.id;
            option.innerText = p.full_name;
            dropdown.appendChild(option);
        });
    }

    async function handleAssignmentSubmit(event) {
        event.preventDefault(); 
        
        const reportId = document.getElementById('assign_report_id').value; 
        const assignedTo = document.getElementById('assigned_to').value; 
        
        if (!reportId) {
            alert("Report ID tidak lengkap.");
            return;
        }

        const action = 'assignReport'; 

        const formData = new URLSearchParams();
        formData.append('id', reportId); 
        formData.append('assigned_to', assignedTo); 

        try {
            const response = await fetch(`${API_URL}?action=${action}`, {
                method: 'PUT', 
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });
            
            let result;
            const clonedResponse = response.clone();
            try {
                result = await response.json();
            } catch (e) {
                const errorText = await clonedResponse.text();
                console.error("Non-JSON Response Error:", errorText);
                throw new Error("Gagal parsing respons server. Kemungkinan Fatal Error PHP. Output server: " + errorText.substring(0, 100));
            }
            
            if (response.ok && result.success) {
                alert(result.message);
                window.location.reload(); 
            } else {
                alert('Gagal Menugaskan: ' + (result.message || 'Unknown server error.'));
            }
        } catch (error) {
            console.error('Error submitting assignment:', error);
            alert('Terjadi kesalahan koneksi atau server saat Penugasan: ' + error.message); 
        }
    }
    
    // --- SUBMIT LOGIC (Validation) ---
    async function handleValidationSubmit(action) { 
        
        const reportId = document.getElementById('validate_report_id_pk').value; 
        const adminNotes = document.getElementById('admin_notes').value; 
        
        if (!reportId) {
            alert("Report ID tidak lengkap.");
            return;
        }
        
        if (action === 'rejectValidation' && adminNotes.trim() === '') {
            alert("Catatan Admin wajib diisi untuk menolak validasi.");
            return;
        }

        const formData = new URLSearchParams();
        formData.append('id', reportId); 
        formData.append('admin_notes', adminNotes);

        if (!confirm(`Anda yakin ingin ${action === 'validateReport' ? 'Menerima (Tuntas)' : 'Menolak (Diproses Kembali)'} laporan ini?`)) {
            return;
        }

        try {
            const response = await fetch(`${API_URL}?action=${action}`, {
                method: 'PUT', 
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });
            
            let result;
            const clonedResponse = response.clone();
            try {
                result = await response.json();
            } catch (e) {
                 const errorText = await clonedResponse.text();
                 console.error("Non-JSON Response Error:", errorText);
                 throw new Error("Gagal parsing respons server (bukan JSON). Cek error PHP di server log.");
            }

            if (response.ok && result.success) {
                alert(result.message);
                window.location.reload(); 
            } else {
                alert('Gagal Validasi: ' + (result.message || 'Unknown server error.'));
            }
        } catch (error) {
            console.error('Error submitting validation:', error);
            alert('Terjadi kesalahan koneksi atau server saat Validasi: ' + error.message); 
        }
    }


    // --- CHART LOGIC ---
    function renderBarChart() {
        const chartElement = document.getElementById('reportStatusChart');
        if (!chartElement) return;

        const ctx = chartElement.getContext('2d');
        
        new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: phpChartLabels,
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: phpChartValues,
                    backgroundColor: phpChartColors,
                    borderColor: phpChartColors.map(c => c.replace('100', '600')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Jumlah Laporan per Status' }
                }
            }
        });
    }


    // --- ACTION & INITIALIZATION ---
    document.addEventListener('DOMContentLoaded', () => {
        fetchPetugas(); 
        
        // Dropdown Akun
        const accountBtn = document.getElementById('account-dropdown-btn');
        const dropdownMenu = document.getElementById('account-dropdown');
        accountBtn.addEventListener('click', () => {
            dropdownMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (event) => {
            if (accountBtn && dropdownMenu && !accountBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        // Notifikasi (Menggunakan completedCount untuk validasi petugas)
        const notificationBtn = document.getElementById('notification-btn');
        notificationBtn.addEventListener('click', () => {
             if (completedCount > 0) { 
                 window.location.href = 'admin.php?page=laporan_petugas'; 
            } else if (pendingCount > 0) {
                window.location.href = 'admin.php?page=laporan_warga';
            } else {
                alert("Tidak ada laporan baru atau laporan yang perlu divalidasi.");
            }
        });
        
        // Render Chart
        if (document.getElementById('reportStatusChart')) {
            renderBarChart();
        }
    });
</script>
</body>
</html>