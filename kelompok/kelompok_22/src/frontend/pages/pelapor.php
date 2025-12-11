<?php
/**
 * Halaman Utama SiPaMaLi
 * Menampilkan tampilan berbeda untuk:
 * 1. Pengunjung (belum login)
 * 2. Pelapor (sudah login)
 */
require_once __DIR__ . '/../../backend/utils/config.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';

$isLoggedIn = isLoggedIn();
$user = $isLoggedIn ? getCurrentUser() : null;
// Allow all logged in users (warga, pelapor) to create reports, except admin roles
$userRole = $_SESSION['role'] ?? 'guest';
$canReport = $isLoggedIn && !in_array($userRole, ['admin', 'super_admin', 'petugas']);

$conn = getDBConnection();
$stats = ['total' => 0, 'diproses' => 0, 'selesai' => 0];
$stats_result = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as diproses,
        SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai
    FROM reports
");
if ($stats_result) {
    $stats = $stats_result->fetch_assoc();
    $stats_result->close();
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Pelaporan Lingkungan Smart City</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/src/frontend/assets/css/styles.css">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #0284c7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #059669 0%, #0284c7 100%);
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(5, 150, 105, 0.3);
        }
        
        .btn-secondary {
            background: white;
            color: #334155;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
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
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    <!-- Navbar -->
    <nav id="navbar" class="fixed w-full z-50 glass-nav shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center gap-3 cursor-pointer" onclick="window.scrollTo({top: 0, behavior: 'smooth'})">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-600 to-sky-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fa-solid fa-leaf text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold gradient-text"><?php echo SITE_NAME; ?></h1>
                        <p class="text-[10px] text-slate-500 font-medium tracking-wider uppercase">Smart City Environment</p>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="#beranda" onclick="scrollToSection('beranda')" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition duration-200">Beranda</a>
                    <a href="#lapor" onclick="scrollToSection('lapor')" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition duration-200">Lapor Masalah</a>
                    <a href="#pantau" onclick="scrollToSection('pantau')" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition duration-200">Pantau Laporan</a>
                    
                    <?php if (!$isLoggedIn): ?>
                        <!-- Menu untuk pengunjung -->
                        <a href="/login.php" class="btn-secondary text-sm">
                            <i class="fa-solid fa-right-to-bracket mr-2"></i>Masuk
                        </a>
                        <a href="register.php" class="btn-primary text-sm">
                            <i class="fa-solid fa-user-plus mr-2"></i>Daftar
                        </a>
                    <?php else: ?>
                        <!-- Menu untuk user yang login -->
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="/admin.php" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition duration-200">Admin Area</a>
                        <?php endif; ?>
                        
                        <!-- Profil User -->
                        <div class="relative">
                            <button onclick="toggleUserMenu()" class="flex items-center gap-3 text-sm font-medium text-slate-700 hover:text-emerald-600 transition duration-200">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center text-white text-sm font-bold">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <span class="hidden lg:inline"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                            </button>
                            
                            <div id="userDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-200 z-50 slide-in">
                                <div class="p-4 border-b border-slate-100">
                                    <p class="text-sm font-semibold text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></p>
                                    <p class="text-xs text-slate-500"><?php echo ucfirst($user['role']); ?></p>
                                </div>
                                <?php if ($canReport): ?>
                                    <a href="riwayat.php" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 border-b border-slate-100">
                                        <i class="fa-solid fa-clock-rotate-left w-5 text-slate-400"></i>
                                        <span>Riwayat Laporan</span>
                                    </a>
                                    <a href="profile.php" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 border-b border-slate-100">
                                        <i class="fa-solid fa-user-edit w-5 text-slate-400"></i>
                                        <span>Edit Profil</span>
                                    </a>
                                <?php endif; ?>
                                <a href="/logout.php" class="flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-right-from-bracket w-5"></i>
                                    <span>Keluar</span>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-slate-600 text-xl" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden hidden bg-white border-t border-slate-200 px-4 py-4 slide-in">
            <div class="space-y-4">
                <a href="#beranda" onclick="scrollToSection('beranda'); toggleMobileMenu();" class="block py-2 text-slate-600 hover:text-emerald-600">Beranda</a>
                <a href="#lapor" onclick="scrollToSection('lapor'); toggleMobileMenu();" class="block py-2 text-slate-600 hover:text-emerald-600">Lapor Masalah</a>
                <a href="#pantau" onclick="scrollToSection('pantau'); toggleMobileMenu();" class="block py-2 text-slate-600 hover:text-emerald-600">Pantau Laporan</a>
                
                <?php if (!$isLoggedIn): ?>
                    <div class="pt-4 border-t border-slate-200 space-y-2">
                        <a href="/login.php" class="block py-2 text-slate-600 hover:text-emerald-600">Masuk</a>
                        <a href="register.php" class="block py-2 text-emerald-600 hover:text-emerald-700 font-medium">Daftar</a>
                    </div>
                <?php else: ?>
                    <div class="pt-4 border-t border-slate-200 space-y-2">
                        <div class="px-2 py-1 text-sm text-slate-500">
                            <i class="fa-solid fa-user mr-2"></i>
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </div>
                        <?php if ($canReport): ?>
                            <a href="riwayat.php" class="block py-2 text-slate-600 hover:text-emerald-600">Riwayat Laporan</a>
                            <a href="profile.php" class="block py-2 text-slate-600 hover:text-emerald-600">Edit Profil</a>
                        <?php endif; ?>
                        <a href="/logout.php" class="block py-2 text-red-600 hover:text-red-700">Keluar</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="pt-16">
        <!-- Hero Section -->
        <section id="beranda" class="bg-gradient-to-br from-emerald-50 to-sky-50 py-16 md:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="lg:w-1/2 text-center lg:text-left space-y-6 fade-in">
                        <span class="inline-flex items-center gap-2 py-2 px-4 rounded-full bg-white/80 backdrop-blur-sm text-emerald-700 text-sm font-semibold border border-emerald-200">
                            <i class="fa-solid fa-circle-check"></i>
                            RESMI PEMERINTAH KOTA
                        </span>
                        
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-slate-900 leading-tight">
                            Jaga Lingkungan
                            <span class="gradient-text block">Untuk Masa Depan</span>
                        </h1>
                        
                        <p class="text-lg text-slate-600 leading-relaxed max-w-2xl">
                            Temukan sampah liar, jalan rusak, atau saluran air tersumbat? 
                            Laporkan segera melalui SiPaMaLi. Bersama wujudkan kota yang cerdas, bersih, dan nyaman.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <button onclick="scrollToSection('lapor')" class="btn-primary px-8 py-4 text-base">
                                <i class="fa-solid fa-camera mr-2"></i>Lapor Sekarang
                            </button>
                            <button onclick="scrollToSection('pantau')" class="btn-secondary px-8 py-4 text-base">
                                <i class="fa-solid fa-magnifying-glass mr-2"></i>Cek Status
                            </button>
                        </div>
                        
                        <!-- Quick Stats -->
                        <div class="grid grid-cols-3 gap-6 pt-8 border-t border-slate-200 max-w-md">
                            <div class="text-center">
                                <p class="text-3xl font-bold text-slate-800"><?php echo $stats['total']; ?></p>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mt-1">Total Laporan</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-sky-600"><?php echo $stats['diproses']; ?></p>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mt-1">Diproses</p>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-emerald-600"><?php echo $stats['selesai']; ?></p>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mt-1">Selesai</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="lg:w-1/2 relative fade-in" style="animation-delay: 0.2s;">
                        <div class="relative">
                            <div class="w-full h-full bg-gradient-to-tr from-emerald-400/20 to-sky-400/20 rounded-3xl blur-3xl absolute"></div>
                            <div class="relative bg-white rounded-2xl shadow-xl p-8 card-hover">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                                        <i class="fa-solid fa-triangle-exclamation text-emerald-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-800 text-lg">Laporan Real-Time</h3>
                                        <p class="text-sm text-slate-500">Sistem terintegrasi dengan dinas terkait</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                                                <i class="fa-solid fa-trash text-red-500 text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium text-slate-700">Sampah Menumpuk</span>
                                        </div>
                                        <span class="status-badge status-diproses">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Diproses
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                                <i class="fa-solid fa-water text-blue-500 text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium text-slate-700">Drainase Tersumbat</span>
                                        </div>
                                        <span class="status-badge status-selesai">
                                            <i class="fa-solid fa-check"></i> Selesai
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <i class="fa-solid fa-road text-gray-500 text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium text-slate-700">Jalan Rusak</span>
                                        </div>
                                        <span class="status-badge status-menunggu">
                                            <i class="fa-regular fa-clock"></i> Menunggu
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-slate-900 mb-4">Layanan yang Tersedia</h2>
                    <p class="text-slate-600 max-w-2xl mx-auto">Laporkan berbagai masalah lingkungan di sekitar Anda</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm card-hover">
                        <div class="w-14 h-14 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-2xl mb-6">
                            <i class="fa-solid fa-trash-can"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Manajemen Sampah</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Laporkan tumpukan sampah liar atau jadwal pengangkutan yang terlewat.</p>
                    </div>
                    
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm card-hover">
                        <div class="w-14 h-14 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl mb-6">
                            <i class="fa-solid fa-water"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Drainase & Banjir</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Laporkan saluran air tersumbat atau genangan air yang mengganggu jalan.</p>
                    </div>
                    
                    <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm card-hover">
                        <div class="w-14 h-14 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl mb-6">
                            <i class="fa-solid fa-wind"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Polusi Udara</h3>
                        <p class="text-slate-600 text-sm leading-relaxed">Laporkan pembakaran sampah ilegal atau asap pabrik yang berlebihan.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Content Area -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-20">
            <!-- FORM SECTION -->
            <section id="lapor" class="scroll-mt-20">
                <div class="grid lg:grid-cols-2 gap-12">
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-slate-100">
                        <div class="mb-8">
                            <h2 class="text-3xl font-bold text-slate-800 mb-2">Buat Laporan Baru</h2>
                            <p class="text-slate-500">
                                <?php if ($canReport): ?>
                                    <span class="text-emerald-600 font-medium"><i class="fa-solid fa-circle-check mr-2"></i>Anda login sebagai Pelapor</span> - Laporan akan tersimpan di riwayat Anda
                                <?php else: ?>
                                    <span class="text-orange-500 font-medium"><i class="fa-solid fa-circle-exclamation mr-2"></i>Silakan <a href="login.php" class="underline hover:text-orange-600">login</a> atau <a href="register.php" class="underline hover:text-orange-600">daftar</a></span> untuk mengirim laporan
                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if ($canReport): ?>
                        <!-- Form untuk Pelapor (bisa submit) -->
                        <form id="reportForm" class="space-y-6">
                            <!-- Kategori -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-3">Kategori Masalah</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="category" value="Sampah" class="peer sr-only" checked>
                                        <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 text-center transition-all duration-200">
                                            <i class="fa-solid fa-trash text-lg mb-2 block"></i>
                                            <span class="text-sm font-medium">Sampah</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="category" value="Drainase" class="peer sr-only">
                                        <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 text-center transition-all duration-200">
                                            <i class="fa-solid fa-water text-lg mb-2 block"></i>
                                            <span class="text-sm font-medium">Drainase</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="category" value="Jalan" class="peer sr-only">
                                        <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-gray-500 peer-checked:bg-gray-50 peer-checked:text-gray-700 text-center transition-all duration-200">
                                            <i class="fa-solid fa-road text-lg mb-2 block"></i>
                                            <span class="text-sm font-medium">Jalan</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="category" value="Lainnya" class="peer sr-only">
                                        <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 text-center transition-all duration-200">
                                            <i class="fa-solid fa-ellipsis text-lg mb-2 block"></i>
                                            <span class="text-sm font-medium">Lainnya</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Lokasi -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-3">Lokasi Kejadian</label>
                                <div class="relative">
                                    <i class="fa-solid fa-location-dot absolute left-4 top-4 text-slate-400"></i>
                                    <input type="text" id="locationInput" placeholder="Contoh: Jl. Merdeka No. 45, Depan Taman Kota" required
                                        class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 outline-none transition text-sm">
                                </div>
                                <p class="text-xs text-slate-500 mt-2">Tulis alamat selengkap mungkin agar mudah ditemukan petugas.</p>
                            </div>

                            <!-- Deskripsi -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-3">Deskripsi Detail</label>
                                <textarea id="descInput" rows="4" placeholder="Jelaskan kondisi masalah secara detail..." required
                                    class="w-full p-4 rounded-xl border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 outline-none transition text-sm"></textarea>
                            </div>

                            <!-- Foto -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-3">Bukti Foto (Opsional)</label>
                                <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:bg-slate-50 transition cursor-pointer" 
                                     onclick="document.getElementById('fileInput').click()">
                                    <input type="file" id="fileInput" class="hidden" accept="image/*" onchange="previewImage(this)">
                                    <div id="uploadPlaceholder">
                                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-slate-400 mb-3"></i>
                                        <p class="text-slate-600 font-medium">Klik untuk unggah foto</p>
                                        <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG (Maks 5MB)</p>
                                    </div>
                                    <div id="imagePreviewContainer" class="hidden">
                                        <img id="imagePreview" src="" class="max-w-full h-48 object-cover rounded-lg mx-auto">
                                        <button type="button" onclick="removeImage()" class="mt-4 text-sm text-red-600 hover:text-red-700 font-medium">
                                            <i class="fa-solid fa-trash mr-1"></i>Hapus Foto
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="w-full btn-primary py-4 text-base">
                                <i class="fa-solid fa-paper-plane mr-2"></i>Kirim Laporan
                            </button>
                        </form>
                        <?php else: ?>
                        <!-- Form untuk Pengunjung (hanya preview) -->
                        <div class="space-y-6">
                            <div class="p-8 border-2 border-dashed border-slate-300 rounded-2xl text-center bg-slate-50/50">
                                <i class="fa-solid fa-lock text-5xl text-slate-400 mb-4"></i>
                                <h3 class="text-xl font-bold text-slate-700 mb-2">Login Diperlukan</h3>
                                <p class="text-slate-600 mb-6">Anda perlu login untuk mengirim laporan. Laporan akan tersimpan di riwayat Anda.</p>
                                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                                    <a href="/login.php" class="btn-primary px-6 py-3">
                                        <i class="fa-solid fa-right-to-bracket mr-2"></i>Masuk
                                    </a>
                                    <a href="register.php" class="btn-secondary px-6 py-3">
                                        <i class="fa-solid fa-user-plus mr-2"></i>Daftar
                                    </a>
                                </div>
                            </div>
                            
                            <div class="text-sm text-slate-500 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <i class="fa-solid fa-circle-info text-emerald-500 mr-2"></i>
                                Setelah login, Anda dapat:
                                <ul class="mt-2 space-y-1 ml-6">
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-xs text-emerald-500"></i> Mengirim laporan baru</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-xs text-emerald-500"></i> Melihat riwayat laporan Anda</li>
                                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-xs text-emerald-500"></i> Menerima notifikasi status laporan</li>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- INFO SECTION -->
                    <div class="space-y-8">
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white p-8 rounded-2xl shadow-lg">
                            <h3 class="text-2xl font-bold mb-4">Mengapa Melapor?</h3>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <i class="fa-solid fa-shield-check mt-1"></i>
                                    <span>Identitas pelapor dirahasiakan</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="fa-solid fa-bolt mt-1"></i>
                                    <span>Laporan diproses dalam 1x24 jam</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="fa-solid fa-chart-line mt-1"></i>
                                    <span>Pantau progress secara real-time</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <i class="fa-solid fa-camera mt-1"></i>
                                    <span>Lengkapi dengan foto untuk bukti</span>
                                </li>
                            </ul>
                        </div>
                        
                        <?php if ($canReport): ?>
                        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                                    <i class="fa-solid fa-user-check text-emerald-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800">Akun Pelapor Anda</h4>
                                    <p class="text-sm text-slate-500">Status: Aktif</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <a href="riwayat.php" class="flex items-center justify-between p-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition">
                                    <span class="text-sm font-medium text-slate-700">Riwayat Laporan</span>
                                    <i class="fa-solid fa-arrow-right text-slate-400"></i>
                                </a>
                                <a href="profile.php" class="flex items-center justify-between p-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition">
                                    <span class="text-sm font-medium text-slate-700">Edit Profil</span>
                                    <i class="fa-solid fa-arrow-right text-slate-400"></i>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <!-- FEED SECTION -->
            <section id="pantau" class="scroll-mt-20">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                        <div>
                            <h2 class="text-3xl font-bold text-slate-800 mb-2">Laporan Terkini</h2>
                            <p class="text-slate-500">Pantau tindak lanjut laporan dari warga</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="filterFeed('all')" class="px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition">Semua</button>
                            <button onclick="filterFeed('Menunggu')" class="px-4 py-2 rounded-lg text-sm font-medium bg-orange-100 text-orange-700 hover:bg-orange-200 transition">Menunggu</button>
                            <button onclick="filterFeed('Selesai')" class="px-4 py-2 rounded-lg text-sm font-medium bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition">Selesai</button>
                        </div>
                    </div>

                    <!-- Report List Container -->
                    <div id="reportList" class="space-y-4 max-h-[600px] overflow-y-auto pr-2 pb-4">
                        <!-- Items will be loaded via JavaScript -->
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                <i class="fa-solid fa-spinner fa-spin text-2xl text-slate-400"></i>
                            </div>
                            <p class="text-slate-500">Memuat laporan...</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 py-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center">
                            <i class="fa-solid fa-leaf text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white"><?php echo SITE_NAME; ?></h3>
                            <p class="text-sm text-slate-400">Smart City Environment</p>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm max-w-md">
                        Platform partisipasi publik untuk mewujudkan lingkungan kota yang cerdas, bersih, dan berkelanjutan.
                        Laporkan masalah di sekitar Anda dengan mudah dan cepat.
                    </p>
                </div>
                
                <div>
                    <h4 class="text-white font-bold mb-4 text-lg">Navigasi</h4>
                    <ul class="space-y-2">
                        <li><a href="#beranda" onclick="scrollToSection('beranda')" class="text-slate-400 hover:text-emerald-400 transition text-sm">Beranda</a></li>
                        <li><a href="#lapor" onclick="scrollToSection('lapor')" class="text-slate-400 hover:text-emerald-400 transition text-sm">Lapor Masalah</a></li>
                        <li><a href="#pantau" onclick="scrollToSection('pantau')" class="text-slate-400 hover:text-emerald-400 transition text-sm">Pantau Laporan</a></li>
                        <?php if ($canReport): ?>
                        <li><a href="riwayat.php" class="text-slate-400 hover:text-emerald-400 transition text-sm">Riwayat Saya</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-white font-bold mb-4 text-lg">Kontak</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-sm text-slate-400">
                            <i class="fa-solid fa-phone text-emerald-400"></i>
                            <span>112 (Bebas Pulsa)</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-400">
                            <i class="fa-solid fa-envelope text-emerald-400"></i>
                            <span>lapor@sipamali.go.id</span>
                        </li>
                        <li class="flex items-center gap-3 text-sm text-slate-400">
                            <i class="fa-solid fa-clock text-emerald-400"></i>
                            <span>24/7 Layanan Darurat</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-slate-800 mt-8 pt-8 text-center">
                <p class="text-sm text-slate-500">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. Smart City Initiative.
                </p>
            </div>
        </div>
    </footer>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-5 right-5 transform translate-y-20 opacity-0 transition-all duration-300 bg-slate-800 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50">
        <i class="fa-solid fa-circle-check text-emerald-400"></i>
        <span id="toastMsg">Berhasil!</span>
    </div>

    <script>
        // --- UTILITIES ---
        function scrollToSection(id) {
            const element = document.getElementById(id);
            if (element) {
                const offset = 80; // Height of navbar
                const elementPosition = element.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = toast.querySelector('i');
            const msgSpan = document.getElementById('toastMsg');
            
            // Set icon based on type
            if (type === 'error') {
                icon.className = 'fa-solid fa-circle-exclamation text-red-400';
            } else if (type === 'warning') {
                icon.className = 'fa-solid fa-triangle-exclamation text-yellow-400';
            } else {
                icon.className = 'fa-solid fa-circle-check text-emerald-400';
            }
            
            msgSpan.innerText = msg;
            toast.classList.remove('translate-y-20', 'opacity-0');
            
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown && !dropdown.contains(event.target) && !event.target.closest('button[onclick*="toggleUserMenu"]')) {
                dropdown.classList.add('hidden');
            }
            
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu && !mobileMenu.contains(event.target) && !event.target.closest('button[onclick*="toggleMobileMenu"]')) {
                mobileMenu.classList.add('hidden');
            }
        });

        // --- IMAGE HANDLING ---
        let currentImageFile = null;
        
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showToast('Ukuran file maksimal 5MB', 'error');
                    input.value = '';
                    return;
                }
                
                // Check file type
                if (!file.type.match('image/jpeg') && !file.type.match('image/png') && !file.type.match('image/jpg')) {
                    showToast('Format file harus JPG atau PNG', 'error');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreviewContainer').classList.remove('hidden');
                    document.getElementById('uploadPlaceholder').classList.add('hidden');
                    currentImageFile = file;
                }
                reader.readAsDataURL(file);
            }
        }

        function removeImage() {
            document.getElementById('imagePreview').src = '';
            document.getElementById('imagePreviewContainer').classList.add('hidden');
            document.getElementById('uploadPlaceholder').classList.remove('hidden');
            document.getElementById('fileInput').value = '';
            currentImageFile = null;
        }

        // --- REPORT HANDLING ---
        <?php if ($canReport): ?>
        async function submitReport(e) {
            console.log('submitReport called');
            e.preventDefault();
            
            const category = document.querySelector('input[name="category"]:checked').value;
            const location = document.getElementById('locationInput').value.trim();
            const description = document.getElementById('descInput').value.trim();
            
            console.log('Form data:', { category, location, description });
            
            // Validation
            if (!location) {
                showToast('Harap isi lokasi kejadian', 'error');
                document.getElementById('locationInput').focus();
                return;
            }
            
            if (!description) {
                showToast('Harap isi deskripsi masalah', 'error');
                document.getElementById('descInput').focus();
                return;
            }
            
            // Show loading
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Mengirim...';
            submitBtn.disabled = true;
            
            try {
                // Prepare FormData
                const formData = new FormData();
                formData.append('action', 'createReport');
                formData.append('category', category);
                formData.append('location', location);
                formData.append('description', description);
                
                if (currentImageFile) {
                    formData.append('image', currentImageFile);
                    console.log('Image attached:', currentImageFile.name);
                }
                
                // Debug: log FormData contents
                console.log('FormData contents:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
                }
                
                console.log('Sending request to /api.php');
                
                // Send request
                const response = await fetch('/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                console.log('Response status:', response.status);
                const result = await response.json();
                console.log('Response data:', result);
                
                if (result.success) {
                    // Reset form
                    e.target.reset();
                    removeImage();
                    
                    showToast('Laporan berhasil dikirim! ID: ' + result.report_code);
                    
                    // Refresh reports list
                    await loadReports();
                } else {
                    showToast('Gagal mengirim laporan: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Terjadi kesalahan. Coba lagi.', 'error');
            } finally {
                // Restore button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }
        <?php endif; ?>

        // --- REPORTS LOADING ---
        async function loadReports(filter = 'all') {
            const container = document.getElementById('reportList');
            
            try {
                const response = await fetch(`/api.php?action=getReports&filter=${filter}`);
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    container.innerHTML = '';
                    
                    result.data.forEach(report => {
                        const reportElement = createReportElement(report);
                        container.appendChild(reportElement);
                    });
                } else {
                    container.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fa-solid fa-inbox text-4xl text-slate-300 mb-4"></i>
                            <p class="text-slate-500">Belum ada laporan</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading reports:', error);
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fa-solid fa-exclamation-triangle text-4xl text-red-300 mb-4"></i>
                        <p class="text-slate-500">Gagal memuat laporan</p>
                    </div>
                `;
            }
        }

        function createReportElement(report) {
            const div = document.createElement('div');
            div.className = 'bg-white p-5 rounded-xl border border-slate-100 hover:border-slate-200 transition card-hover';
            
            // Status badge
            let statusClass = 'status-menunggu';
            let statusIcon = 'fa-regular fa-clock';
            
            if (report.status === 'Diproses') {
                statusClass = 'status-diproses';
                statusIcon = 'fa-solid fa-spinner fa-spin';
            } else if (report.status === 'Selesai') {
                statusClass = 'status-selesai';
                statusIcon = 'fa-solid fa-check';
            }
            
            // Category icon
            let categoryIcon = 'fa-ellipsis';
            let categoryColor = 'text-emerald-500 bg-emerald-50';
            
            if (report.category === 'Sampah') {
                categoryIcon = 'fa-trash';
                categoryColor = 'text-red-500 bg-red-50';
            } else if (report.category === 'Drainase') {
                categoryIcon = 'fa-water';
                categoryColor = 'text-blue-500 bg-blue-50';
            } else if (report.category === 'Jalan') {
                categoryIcon = 'fa-road';
                categoryColor = 'text-gray-500 bg-gray-50';
            }
            
            const date = new Date(report.created_at).toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
            
            div.innerHTML = `
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-xl ${categoryColor} flex items-center justify-center">
                            <i class="fa-solid ${categoryIcon}"></i>
                        </div>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">${report.category}</h4>
                                <p class="text-xs text-slate-500">${report.location}</p>
                            </div>
                            <span class="status-badge ${statusClass}">
                                <i class="${statusIcon}"></i> ${report.status}
                            </span>
                        </div>
                        
                        <p class="text-sm text-slate-600 mb-3 line-clamp-2">${report.description}</p>
                        
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>${date} • ID: ${report.report_code}</span>
                            <?php if ($canReport && isset($user) && $user['id'] == 'USER_ID_PLACEHOLDER'): ?>
                            <button class="text-emerald-600 hover:text-emerald-700 font-medium" onclick="viewReportDetail('${report.id}')">
                                Detail
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            `;
            
            return div;
        }

        function filterFeed(status) {
            loadReports(status);
        }

        function viewReportDetail(reportId) {
            // Implement report detail view
            showToast('Fitur detail laporan akan segera tersedia', 'info');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');
            
            // Load reports on page load
            <?php if ($canReport): ?>
            loadReports();
            
            // Attach form submit handler
            const reportForm = document.getElementById('reportForm');
            if (reportForm) {
                console.log('Report form found, attaching event listener');
                reportForm.addEventListener('submit', submitReport);
            } else {
                console.warn('Report form not found');
            }
            <?php endif; ?>
            
            // Add scroll event for navbar
            window.addEventListener('scroll', function() {
                const navbar = document.getElementById('navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('shadow-md');
                } else {
                    navbar.classList.remove('shadow-md');
                }
            });
            
            // Auto-hide mobile menu when clicking a link
            document.querySelectorAll('#mobileMenu a').forEach(link => {
                link.addEventListener('click', () => {
                    toggleMobileMenu();
                });
            });
        });
    </script>
</body>
</html>