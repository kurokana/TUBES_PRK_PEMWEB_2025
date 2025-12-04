<?php
require_once 'includes/auth.php';

requireLogin();

$adminInfo = getAdminInfo();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SiPaMaLi</title>
    
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
                            light: '#e0f2fe',
                            DEFAULT: '#0284c7',
                            dark: '#0c4a6e',
                        },
                        eco: {
                            light: '#d1fae5',
                            DEFAULT: '#059669',
                            dark: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
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
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 bg-gradient-to-br from-eco to-smart rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fa-solid fa-leaf text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-eco-dark to-smart-dark">SiPaMaLi</h1>
                        <p class="text-[10px] text-slate-500 font-medium tracking-wider uppercase">Admin Dashboard</p>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($adminInfo['full_name']); ?></p>
                        <p class="text-xs text-slate-500">@<?php echo htmlspecialchars($adminInfo['username']); ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="index.html" class="px-3 py-2 text-sm text-slate-600 hover:text-eco transition">
                            <i class="fa-solid fa-home mr-1"></i> Beranda
                        </a>
                        <button onclick="logout()" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                            <i class="fa-solid fa-right-from-bracket mr-1"></i> Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-eco to-smart rounded-2xl p-8 mb-8 text-white shadow-xl">
            <h2 class="text-3xl font-bold mb-2">Selamat Datang, <?php echo htmlspecialchars($adminInfo['full_name']); ?>! 👋</h2>
            <p class="text-white/90">Kelola dan pantau laporan masalah lingkungan dari warga.</p>
        </div>

        <!-- Admin Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Total Masuk</p>
                        <h3 class="text-3xl font-bold text-slate-800 mt-1" id="admin-total">0</h3>
                    </div>
                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600"><i class="fa-solid fa-inbox"></i></div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Menunggu</p>
                        <h3 class="text-3xl font-bold text-orange-500 mt-1" id="admin-pending">0</h3>
                    </div>
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-600"><i class="fa-solid fa-clock"></i></div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Diproses</p>
                        <h3 class="text-3xl font-bold text-blue-500 mt-1" id="admin-process">0</h3>
                    </div>
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600"><i class="fa-solid fa-spinner"></i></div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Selesai</p>
                        <h3 class="text-3xl font-bold text-emerald-500 mt-1" id="admin-done">0</h3>
                    </div>
                    <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600"><i class="fa-solid fa-check"></i></div>
                </div>
            </div>
        </div>

        <!-- Filter & Actions -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-slate-800">Daftar Laporan</h3>
            <div class="flex gap-2">
                <select id="filterStatus" onchange="filterReports()" class="px-3 py-2 border border-slate-200 rounded-lg text-sm focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none">
                    <option value="">Semua Status</option>
                    <option value="Menunggu">Menunggu</option>
                    <option value="Diproses">Diproses</option>
                    <option value="Selesai">Selesai</option>
                </select>
                <button onclick="refreshData()" class="px-4 py-2 bg-eco text-white rounded-lg text-sm font-medium hover:bg-eco-dark transition">
                    <i class="fa-solid fa-rotate-right mr-1"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Admin Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-slate-700">ID & Tanggal</th>
                            <th class="px-6 py-4 font-semibold text-slate-700">Kategori</th>
                            <th class="px-6 py-4 font-semibold text-slate-700">Masalah & Lokasi</th>
                            <th class="px-6 py-4 font-semibold text-slate-700">Foto</th>
                            <th class="px-6 py-4 font-semibold text-slate-700">Status</th>
                            <th class="px-6 py-4 font-semibold text-slate-700 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="adminTableBody" class="divide-y divide-slate-100">
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                                <i class="fa-solid fa-spinner fa-spin text-2xl mb-2"></i>
                                <p>Memuat data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-5 right-5 transform translate-y-20 opacity-0 transition-all duration-300 bg-slate-800 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 z-50">
        <i class="fa-solid fa-circle-check text-eco"></i>
        <span id="toastMsg">Berhasil!</span>
    </div>

    <script>
        const API_URL = 'api.php';
        let reports = [];

        // Show toast notification
        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toastMsg').innerText = msg;
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3000);
        }

        // Logout function
        function logout() {
            if (confirm('Yakin ingin logout?')) {
                window.location.href = 'auth.php?logout=1';
                // Alternative: Gunakan AJAX
                fetch('auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=logout'
                })
                .then(() => {
                    window.location.href = 'login.php';
                });
            }
        }

        // Fetch reports from API
        async function fetchReports() {
            try {
                const filterStatus = document.getElementById('filterStatus').value;
                const url = filterStatus ? `${API_URL}?action=getReports&status=${filterStatus}` : `${API_URL}?action=getReports`;
                
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.success) {
                    reports = result.data;
                    renderAdminTable();
                    updateStats();
                } else {
                    console.error('Failed to fetch reports:', result.message);
                }
            } catch (error) {
                console.error('Error fetching reports:', error);
            }
        }

        // Get category icon
        function getCategoryIcon(cat) {
            if(cat === 'Sampah') return 'fa-trash text-red-500 bg-red-50';
            if(cat === 'Drainase') return 'fa-water text-blue-500 bg-blue-50';
            if(cat === 'Jalan') return 'fa-road text-gray-500 bg-gray-50';
            if(cat === 'Polusi') return 'fa-wind text-purple-500 bg-purple-50';
            return 'fa-exclamation text-emerald-500 bg-emerald-50';
        }

        // Get status badge
        function getStatusBadge(status) {
            if (status === 'Menunggu') return `<span class="px-2 py-1 rounded bg-orange-100 text-orange-600 text-xs font-bold border border-orange-200"><i class="fa-regular fa-clock mr-1"></i>Menunggu</span>`;
            if (status === 'Diproses') return `<span class="px-2 py-1 rounded bg-blue-100 text-blue-600 text-xs font-bold border border-blue-200"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Diproses</span>`;
            if (status === 'Selesai') return `<span class="px-2 py-1 rounded bg-emerald-100 text-emerald-600 text-xs font-bold border border-emerald-200"><i class="fa-solid fa-check mr-1"></i>Selesai</span>`;
        }

        // Render admin table
        function renderAdminTable() {
            const tbody = document.getElementById('adminTableBody');
            tbody.innerHTML = '';

            if (reports.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                            <p>Tidak ada laporan</p>
                        </td>
                    </tr>
                `;
                return;
            }

            reports.forEach(r => {
                const imgHtml = r.img ? 
                    `<img src="${r.img}" class="w-12 h-12 object-cover rounded-lg border border-slate-200 cursor-pointer hover:scale-110 transition" onclick="showImage('${r.img}')" title="Klik untuk memperbesar">` : 
                    `<div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center text-slate-300"><i class="fa-solid fa-image-slash"></i></div>`;
                
                const row = `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-bold text-slate-500 block">${r.id}</span>
                            <span class="text-xs text-slate-400">${r.date}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center ${getCategoryIcon(r.category)}">
                                    <i class="fa-solid ${getCategoryIcon(r.category).split(' ')[0]} text-xs"></i>
                                </div>
                                <span class="font-medium">${r.category}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            <div class="text-slate-800 font-medium truncate">${r.location}</div>
                            <div class="text-xs text-slate-500 line-clamp-2" title="${r.desc}">${r.desc}</div>
                        </td>
                        <td class="px-6 py-4">${imgHtml}</td>
                        <td class="px-6 py-4">
                            ${getStatusBadge(r.status)}
                        </td>
                        <td class="px-6 py-4 text-right space-x-1">
                            ${r.status !== 'Diproses' && r.status !== 'Selesai' ? 
                                `<button onclick="updateStatus('${r.id}', 'Diproses')" class="p-2 rounded hover:bg-blue-100 text-blue-600" title="Proses"><i class="fa-solid fa-spinner"></i></button>` : ''}
                            ${r.status !== 'Selesai' ? 
                                `<button onclick="updateStatus('${r.id}', 'Selesai')" class="p-2 rounded hover:bg-emerald-100 text-emerald-600" title="Selesai"><i class="fa-solid fa-check"></i></button>` : ''}
                            <button onclick="deleteReport('${r.id}')" class="p-2 rounded hover:bg-red-100 text-red-600" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        // Update statistics
        function updateStats() {
            document.getElementById('admin-total').innerText = reports.length;
            document.getElementById('admin-pending').innerText = reports.filter(r => r.status === 'Menunggu').length;
            document.getElementById('admin-process').innerText = reports.filter(r => r.status === 'Diproses').length;
            document.getElementById('admin-done').innerText = reports.filter(r => r.status === 'Selesai').length;
        }

        // Update status
        async function updateStatus(id, newStatus) {
            try {
                const response = await fetch(`${API_URL}?action=updateStatus`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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

        // Delete report
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

        // Filter reports
        function filterReports() {
            fetchReports();
        }

        // Refresh data
        function refreshData() {
            showToast('Memuat ulang data...');
            fetchReports();
        }

        // Show image modal
        function showImage(src) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4';
            modal.onclick = () => modal.remove();
            modal.innerHTML = `
                <div class="relative max-w-4xl max-h-full">
                    <img src="${src}" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl">
                    <button class="absolute top-4 right-4 bg-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-slate-100 transition">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(modal);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            fetchReports();
        });
    </script>
</body>
</html>
