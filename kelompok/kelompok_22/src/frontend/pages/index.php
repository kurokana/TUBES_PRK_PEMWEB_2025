<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPaMaLi - Pelaporan Lingkungan Smart City</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        smart: {
                            light: '#e0f2fe', // Sky 100
                            DEFAULT: '#0284c7', // Sky 600
                            dark: '#0c4a6e', // Sky 900
                        },
                        eco: {
                            light: '#d1fae5', // Emerald 100
                            DEFAULT: '#059669', // Emerald 600
                            dark: '#064e3b', // Emerald 900
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        ::-webkit-scrollbar-thumb {
            background: #059669; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #047857; 
        }
        
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center gap-2 cursor-pointer" onclick="switchView('home')">
                    <div class="w-10 h-10 bg-gradient-to-br from-eco to-smart rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fa-solid fa-leaf text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-eco-dark to-smart-dark">SiPaMaLi</h1>
                        <p class="text-[10px] text-slate-500 font-medium tracking-wider uppercase">Smart City Environment</p>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                     <a href="#beranda" onclick="event.preventDefault(); scrollToElement('home')" class="text-sm font-medium text-slate-600 hover:text-eco transition">Beranda</a>
                    <a href="#lapor" onclick="event.preventDefault(); scrollToElement('form-lapor')" class="text-sm font-medium text-slate-600 hover:text-eco transition">Lapor Masalah</a>
                    <a href="#pantau" onclick="event.preventDefault(); scrollToElement('public-feed')" class="text-sm font-medium text-slate-600 hover:text-eco transition">Pantau Laporan</a>
                    <a href="login.php" class="px-4 py-2 rounded-full text-sm font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 transition border border-slate-200">
                        <i class="fa-solid fa-right-to-bracket mr-2"></i>Login
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button class="md:hidden text-slate-600 text-xl">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- VIEW: USER / PUBLIC (DEFAULT) -->
    <div id="view-user" class="pt-16">
        
        <!-- Hero Section -->
        <header class="relative bg-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-eco-light/30 to-smart-light/30 z-0"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 relative z-10 flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2 text-center md:text-left space-y-6 fade-in">
                    <span class="inline-block py-1 px-3 rounded-full bg-eco/10 text-eco text-xs font-bold tracking-wide border border-eco/20">
                        <i class="fa-solid fa-circle-check mr-1"></i> RESMI PEMERINTAH KOTA
                    </span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 leading-tight">
                        Jaga Lingkungan <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-eco to-smart">Untuk Masa Depan</span>
                    </h1>
                    <p class="text-lg text-slate-600 leading-relaxed">
                        Temukan sampah liar, jalan rusak, atau saluran air tersumbat? 
                        Laporkan segera melalui SiPaMaLi. Bersama wujudkan kota yang cerdas, bersih, dan nyaman.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                        <button onclick="scrollToElement('form-lapor')" class="px-8 py-4 rounded-xl bg-gradient-to-r from-eco to-teal-600 text-white font-bold shadow-lg shadow-eco/30 hover:shadow-eco/50 hover:-translate-y-1 transition transform flex items-center justify-center gap-2">
                            <i class="fa-solid fa-camera"></i> Lapor Sekarang
                        </button>
                        <button onclick="scrollToElement('public-feed')" class="px-8 py-4 rounded-xl bg-white text-slate-700 font-bold border border-slate-200 shadow-sm hover:bg-slate-50 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i> Cek Status
                        </button>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="grid grid-cols-3 gap-4 pt-4 border-t border-slate-200">
                        <div>
                            <p class="text-2xl font-bold text-slate-800" id="stat-total">0</p>
                            <p class="text-xs text-slate-500 uppercase tracking-wide">Total Laporan</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-smart" id="stat-process">0</p>
                            <p class="text-xs text-slate-500 uppercase tracking-wide">Diproses</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-eco" id="stat-done">0</p>
                            <p class="text-xs text-slate-500 uppercase tracking-wide">Selesai</p>
                        </div>
                    </div>
                </div>
                
                <div class="md:w-1/2 relative fade-in" style="animation-delay: 0.2s;">
                    <!-- Illustration Placeholder using CSS/FontAwesome -->
                    <div class="relative w-full aspect-square max-w-md mx-auto bg-gradient-to-tr from-eco-light to-smart-light rounded-full flex items-center justify-center shadow-2xl overflow-hidden border-8 border-white">
                         <i class="fa-solid fa-city text-9xl text-white/40 absolute bottom-0"></i>
                         <i class="fa-solid fa-tree text-8xl text-eco absolute bottom-10 right-10 animate-bounce" style="animation-duration: 3s;"></i>
                         <i class="fa-solid fa-cloud text-6xl text-white absolute top-20 left-10 opacity-80"></i>
                         <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white/90 backdrop-blur p-6 rounded-2xl shadow-xl text-center border border-slate-100 max-w-xs">
                             <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                 <i class="fa-solid fa-triangle-exclamation text-orange-500 text-xl"></i>
                             </div>
                             <p class="font-bold text-slate-800">Laporan Real-Time</p>
                             <p class="text-xs text-slate-500 mt-1">Sistem terintegrasi langsung dengan dinas terkait.</p>
                         </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
            
            <!-- Features Grid -->
            <section class="grid md:grid-cols-3 gap-8">
                <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-lg bg-red-100 text-red-600 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                        <i class="fa-solid fa-trash-can"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Manajemen Sampah</h3>
                    <p class="text-slate-600 text-sm">Laporkan tumpukan sampah liar atau jadwal pengangkutan yang terlewat.</p>
                </div>
                <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-lg bg-smart-light text-smart flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                        <i class="fa-solid fa-water"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Drainase & Banjir</h3>
                    <p class="text-slate-600 text-sm">Laporkan saluran air tersumbat atau genangan air yang mengganggu jalan.</p>
                </div>
                <div class="p-6 bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition group">
                    <div class="w-12 h-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                        <i class="fa-solid fa-wind"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Polusi Udara</h3>
                    <p class="text-slate-600 text-sm">Laporkan pembakaran sampah ilegal atau asap pabrik yang berlebihan.</p>
                </div>
            </section>

            <div class="grid lg:grid-cols-2 gap-12">
                <!-- FORM SECTION -->
                <section id="form-lapor" class="bg-white p-8 rounded-3xl shadow-xl border border-slate-100 h-fit">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-slate-800">Buat Laporan Baru</h2>
                        <p class="text-slate-500 text-sm">Identitas pelapor akan dirahasiakan.</p>
                    </div>

                    <form id="reportForm" onsubmit="submitReport(event)" class="space-y-5">
                        <!-- Kategori -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori Masalah</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="Sampah" class="peer sr-only" checked>
                                    <div class="p-3 rounded-xl border border-slate-200 peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-700 text-center hover:bg-slate-50 transition">
                                        <i class="fa-solid fa-trash mb-1 block"></i> <span class="text-xs font-medium">Sampah</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="Drainase" class="peer sr-only">
                                    <div class="p-3 rounded-xl border border-slate-200 peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 text-center hover:bg-slate-50 transition">
                                        <i class="fa-solid fa-water mb-1 block"></i> <span class="text-xs font-medium">Drainase</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="Jalan" class="peer sr-only">
                                    <div class="p-3 rounded-xl border border-slate-200 peer-checked:bg-gray-50 peer-checked:border-gray-500 peer-checked:text-gray-700 text-center hover:bg-slate-50 transition">
                                        <i class="fa-solid fa-road mb-1 block"></i> <span class="text-xs font-medium">Jalan</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="category" value="Lainnya" class="peer sr-only">
                                    <div class="p-3 rounded-xl border border-slate-200 peer-checked:bg-emerald-50 peer-checked:border-emerald-500 peer-checked:text-emerald-700 text-center hover:bg-slate-50 transition">
                                        <i class="fa-solid fa-ellipsis mb-1 block"></i> <span class="text-xs font-medium">Lainnya</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Lokasi -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Lokasi Kejadian</label>
                            <div class="relative">
                                <i class="fa-solid fa-location-dot absolute left-4 top-3.5 text-slate-400"></i>
                                <input type="text" id="locationInput" placeholder="Contoh: Jl. Merdeka No. 45, Depan Taman..." required
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none transition text-sm">
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Detail</label>
                            <textarea id="descInput" rows="3" placeholder="Jelaskan masalah secara singkat..." required
                                class="w-full p-4 rounded-xl border border-slate-200 focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none transition text-sm"></textarea>
                        </div>

                        <!-- Foto -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Bukti Foto</label>
                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 transition cursor-pointer relative" onclick="document.getElementById('fileInput').click()">
                                <input type="file" id="fileInput" class="hidden" accept="image/*" onchange="previewImage(this)">
                                <div id="uploadPlaceholder">
                                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                                    <p class="text-xs text-slate-500">Klik untuk unggah foto (Opsional)</p>
                                </div>
                                <img id="imagePreview" src="" class="hidden w-full h-32 object-cover rounded-lg mt-2 mx-auto">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-4 bg-eco hover:bg-eco-dark text-white font-bold rounded-xl shadow-lg shadow-eco/30 transition transform active:scale-95 flex justify-center items-center gap-2">
                            <span>Kirim Laporan</span>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </section>

                <!-- FEED SECTION -->
                <section id="public-feed" class="space-y-6">
                    <div class="flex justify-between items-end">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Laporan Terkini</h2>
                            <p class="text-slate-500 text-sm">Pantau tindak lanjut laporan warga.</p>
                        </div>
                        <div class="hidden sm:flex gap-2">
                            <button onclick="filterFeed('all')" class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 hover:bg-slate-300 transition">Semua</button>
                            <button onclick="filterFeed('Selesai')" class="px-3 py-1 rounded-full text-xs font-semibold bg-eco-light text-eco-dark hover:bg-eco/20 transition">Selesai</button>
                        </div>
                    </div>

                    <!-- Report List Container -->
                    <div id="reportList" class="space-y-4 max-h-[600px] overflow-y-auto pr-2 pb-4">
                        <!-- Items inserted via JS -->
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-2 mb-4 text-white">
                    <i class="fa-solid fa-leaf text-eco text-xl"></i>
                    <span class="text-xl font-bold">SiPaMaLi</span>
                </div>
                <p class="text-sm text-slate-400 max-w-sm">
                    Platform partisipasi publik untuk mewujudkan lingkungan kota yang cerdas, bersih, dan berkelanjutan. Laporkan masalah di sekitar Anda.
                </p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Navigasi</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-eco transition">Beranda</a></li>
                    <li><a href="#" class="hover:text-eco transition">Cara Melapor</a></li>
                    <li><a href="#" class="hover:text-eco transition">Statistik Kota</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4">Kontak Darurat</h4>
                <ul class="space-y-2 text-sm">
                    <li><i class="fa-solid fa-phone mr-2 text-eco"></i> 112 (Bebas Pulsa)</li>
                    <li><i class="fa-solid fa-envelope mr-2 text-eco"></i> lapor@sipamali.go.id</li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 mt-8 pt-8 border-t border-slate-800 text-center text-xs text-slate-500">
            &copy; 2023 SiPaMaLi Project. Smart City Initiative.
        </div>
    </footer>

    <!-- Notification Toast -->
    <div id="toast" class="fixed bottom-5 right-5 transform translate-y-20 opacity-0 transition-all duration-300 bg-slate-800 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50">
        <i class="fa-solid fa-circle-check text-eco"></i>
        <span id="toastMsg">Berhasil!</span>
    </div>

    <script>
        // --- DATA & STATE MANAGEMENT ---
        
        // API Base URL
        const API_URL = 'api.php';

        // Load data from API
        let reports = [];

        // --- UTILITIES ---

        // Fetch data from API
        async function fetchReports() {
            try {
                const response = await fetch(`${API_URL}?action=getReports`);
                const result = await response.json();
                
                if (result.success) {
                    reports = result.data;
                    renderAll();
                } else {
                    console.error('Failed to fetch reports:', result.message);
                }
            } catch (error) {
                console.error('Error fetching reports:', error);
            }
        }

        function saveReports() {
            // No longer needed - data saved to server
            renderAll();
        }

        function scrollToElement(id) {
            document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toastMsg').innerText = msg;
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        // --- IMAGE PREVIEW ---
        let currentImageFile = null;
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                    document.getElementById('uploadPlaceholder').classList.add('hidden');
                    currentImageFile = input.files[0]; // Store file object
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // --- CORE FUNCTIONS ---

        async function submitReport(e) {
            e.preventDefault();
            
            const category = document.querySelector('input[name="category"]:checked').value;
            const location = document.getElementById('locationInput').value;
            const description = document.getElementById('descInput').value;
            
            // Prepare FormData untuk upload file
            const formData = new FormData();
            formData.append('category', category);
            formData.append('location', location);
            formData.append('description', description);
            
            if (currentImageFile) {
                formData.append('image', currentImageFile);
            }
            
            try {
                const response = await fetch(`${API_URL}?action=createReport`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Reset Form
                    e.target.reset();
                    document.getElementById('imagePreview').classList.add('hidden');
                    document.getElementById('uploadPlaceholder').classList.remove('hidden');
                    currentImageFile = null;
                    
                    showToast('Laporan berhasil dikirim!');
                    
                    // Refresh data
                    await fetchReports();
                } else {
                    showToast('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error submitting report:', error);
                showToast('Gagal mengirim laporan. Periksa koneksi Anda.');
            }
        }

        async function updateStatus(id, newStatus) {
            try {
                const response = await fetch(`${API_URL}?action=updateStatus`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(newStatus)}`
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(`Status laporan ${id} diperbarui!`);
                    await fetchReports();
                } else {
                    showToast('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error updating status:', error);
                showToast('Gagal memperbarui status.');
            }
        }

        async function deleteReport(id) {
            if(!confirm('Hapus laporan ini?')) return;
            
            try {
                const response = await fetch(`${API_URL}?action=deleteReport&id=${encodeURIComponent(id)}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Laporan berhasil dihapus!');
                    await fetchReports();
                } else {
                    showToast('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error deleting report:', error);
                showToast('Gagal menghapus laporan.');
            }
        }

        // --- RENDERING UI ---

        function getStatusBadge(status) {
            if (status === 'Menunggu') return `<span class="px-2 py-1 rounded bg-orange-100 text-orange-600 text-xs font-bold border border-orange-200"><i class="fa-regular fa-clock mr-1"></i>Menunggu</span>`;
            if (status === 'Diproses') return `<span class="px-2 py-1 rounded bg-blue-100 text-blue-600 text-xs font-bold border border-blue-200"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Diproses</span>`;
            if (status === 'Selesai') return `<span class="px-2 py-1 rounded bg-emerald-100 text-emerald-600 text-xs font-bold border border-emerald-200"><i class="fa-solid fa-check mr-1"></i>Selesai</span>`;
        }

        function getCategoryIcon(cat) {
            if(cat === 'Sampah') return 'fa-trash text-red-500 bg-red-50';
            if(cat === 'Drainase') return 'fa-water text-blue-500 bg-blue-50';
            if(cat === 'Jalan') return 'fa-road text-gray-500 bg-gray-50';
            return 'fa-exclamation text-emerald-500 bg-emerald-50';
        }

        function renderPublicFeed(filter = 'all') {
            const container = document.getElementById('reportList');
            container.innerHTML = '';

            const filteredReports = filter === 'all' ? reports : reports.filter(r => r.status === filter);

            if(filteredReports.length === 0) {
                container.innerHTML = `<div class="text-center py-10 text-slate-400">Belum ada laporan.</div>`;
                return;
            }

            filteredReports.forEach(r => {
                const imgHtml = r.img ? `<img src="${r.img}" class="w-16 h-16 object-cover rounded-lg border border-slate-200">` : 
                                        `<div class="w-16 h-16 bg-slate-100 rounded-lg flex items-center justify-center text-slate-300"><i class="fa-solid fa-image-slash"></i></div>`;
                
                const html = `
                    <div class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm flex gap-4 hover:shadow-md transition">
                        ${imgHtml}
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-slate-800 text-sm">${r.category} <span class="text-slate-400 font-normal mx-1">•</span> <span class="text-xs text-slate-500 font-normal">${r.location}</span></h4>
                                ${getStatusBadge(r.status)}
                            </div>
                            <p class="text-sm text-slate-600 mb-2 line-clamp-2">${r.desc}</p>
                            <p class="text-xs text-slate-400">${r.date} • ID: ${r.id}</p>
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            });
        }

        function renderStats() {
            // Public Stats
            document.getElementById('stat-total').innerText = reports.length;
            document.getElementById('stat-process').innerText = reports.filter(r => r.status === 'Diproses').length;
            document.getElementById('stat-done').innerText = reports.filter(r => r.status === 'Selesai').length;
        }

        function renderAll() {
            renderPublicFeed();
            renderStats();
        }

        function filterFeed(status) {
            renderPublicFeed(status);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            fetchReports(); // Load data from server
        });

    </script>
</body>
</html>