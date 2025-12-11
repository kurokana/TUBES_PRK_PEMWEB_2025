<?php
// --- LOGIKA LOGOUT KHUSUS ---
// Menangani logout langsung di sini agar bisa redirect ke laman utama
if (isset($_GET['logout'])) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/includes/auth.php';
    logoutUser();
    header('Location: index.php'); // Redirect ke Laman Utama
    exit;
}

require_once 'includes/auth.php';
requireLogin();

// Gunakan helper function agar lebih aman
$userInfo = getAdminInfo();

if (!$userInfo) {
    header('Location: ?logout=1');
    exit;
}

$userRole = $_SESSION['role'] ?? $userInfo['role'] ?? '';
if ($userRole !== 'petugas' && $userRole !== 'admin') {
    header('Location: index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPaMaLi - Dashboard Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { 
            theme: { 
                extend: { 
                    fontFamily: { sans: ['Inter', 'sans-serif'] }, 
                    colors: { 
                        smart: { DEFAULT: '#0284c7' }, 
                        eco: { DEFAULT: '#059669' } 
                    } 
                } 
            } 
        }
    </script>
    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans">

    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-slate-200 fixed top-0 w-full z-50 h-16">
        <div class="px-4 sm:px-6 lg:px-8 h-full">
            <div class="flex justify-between items-center h-full">
                <!-- Brand -->
                <div class="flex items-center gap-3 w-64">
                    <div class="w-8 h-8 bg-eco rounded-lg flex items-center justify-center text-white">
                        <i class="fa-solid fa-helmet-safety"></i>
                    </div>
                    <h1 class="text-lg font-bold text-slate-800 tracking-tight">SiPaMaLi <span class="font-normal text-slate-500 text-sm">Petugas</span></h1>
                </div>

                <!-- Right Side -->
                <div class="flex items-center gap-6">
                    <!-- Notifikasi -->
                    <div class="relative" id="notif-container">
                        <button onclick="toggleNotifications()" class="text-slate-500 hover:text-smart transition relative p-2 rounded-full hover:bg-slate-50">
                            <i class="fa-regular fa-bell text-xl"></i>
                            <span id="notif-count" class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center hidden animate-bounce">0</span>
                        </button>
                        <div id="notif-dropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-100 hidden z-50 overflow-hidden transform origin-top-right transition-all">
                            <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Notifikasi</h3>
                                <button onclick="markAllAsRead()" class="text-[10px] text-smart hover:underline">Tandai telah dibaca</button>
                            </div>
                            <div id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-slate-50"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pl-6 border-l border-slate-200">
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($userInfo['full_name'] ?? 'Petugas'); ?></p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-wider"><?php echo htmlspecialchars($userInfo['agency'] ?? 'Petugas Lapangan'); ?></p>
                        </div>
                        <div class="w-10 h-10 bg-slate-200 rounded-full overflow-hidden border-2 border-white shadow-sm">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userInfo['full_name'] ?? 'Petugas'); ?>&background=059669&color=fff" alt="Profile">
                        </div>
                        <a href="?logout=1" class="text-slate-400 hover:text-red-500 transition ml-2" title="Logout" onclick="return confirm('Keluar dari aplikasi?')">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Layout -->
    <div class="flex pt-16 min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-slate-200 fixed h-full overflow-y-auto hidden md:block z-40">
            <div class="p-6">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Menu Petugas</p>
                <nav class="space-y-2">
                    <button onclick="location.reload()" class="w-full text-left px-4 py-3 rounded-xl text-sm font-semibold transition flex items-center gap-3 bg-eco/10 text-eco">
                        <i class="fa-solid fa-clipboard-check text-lg w-6"></i>
                        Tugas Saya
                    </button>
                </nav>
                <div class="mt-8">
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex gap-3 items-start">
                        <i class="fa-solid fa-circle-info text-blue-600 mt-1"></i>
                        <div>
                            <p class="text-xs text-blue-800 font-bold mb-1">Info Petugas</p>
                            <p class="text-[10px] text-blue-600">Lampirkan foto saat <b>memulai</b> dan <b>menyelesaikan</b> tugas.</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-4 md:p-8 overflow-x-hidden">
            <div class="flex justify-between items-end mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Daftar Tugas Masuk</h2>
                    <p class="text-slate-500 text-sm mt-1">Laporan warga yang perlu ditindaklanjuti.</p>
                </div>
                <button onclick="fetchTasks()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                    <i class="fa-solid fa-rotate mr-2"></i> Refresh
                </button>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div><p class="text-xs text-slate-500 uppercase font-bold">Menunggu</p><h3 class="text-2xl font-bold text-orange-500 mt-1" id="stat-pending">0</h3></div>
                    <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-500 hidden sm:flex"><i class="fa-solid fa-clock"></i></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div><p class="text-xs text-slate-500 uppercase font-bold">Diproses</p><h3 class="text-2xl font-bold text-blue-500 mt-1" id="stat-process">0</h3></div>
                    <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 hidden sm:flex"><i class="fa-solid fa-spinner"></i></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between col-span-2 md:col-span-1">
                    <div><p class="text-xs text-slate-500 uppercase font-bold">Selesai</p><h3 class="text-2xl font-bold text-emerald-500 mt-1" id="stat-done">0</h3></div>
                    <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 hidden sm:flex"><i class="fa-solid fa-check-double"></i></div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 font-semibold whitespace-nowrap">Status</th>
                                <th class="px-6 py-4 font-semibold whitespace-nowrap">ID & Kategori</th>
                                <th class="px-6 py-4 font-semibold min-w-[200px]">Lokasi & Masalah</th>
                                <th class="px-6 py-4 font-semibold whitespace-nowrap">Foto Warga</th>
                                <th class="px-6 py-4 font-semibold whitespace-nowrap">Foto Progress</th>
                                <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-tasks">
                            <tr><td colspan="6" class="text-center py-8"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL UPDATE PROGRESS (DINAMIS) -->
    <div id="modal-progress" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-2xl w-[90%] max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800" id="modal-title">Update Pengerjaan</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-xl"></i></button>
            </div>
            <form onsubmit="submitProgress(event)" class="space-y-4">
                <input type="hidden" id="report-id" name="id">
                <input type="hidden" id="report-status" name="status"> <!-- Status Dinamis -->
                <input type="hidden" id="user-id" name="user_id" value="<?php echo $userInfo['id'] ?? ''; ?>">
                
                <div class="bg-blue-50 p-3 rounded-lg text-xs text-blue-700 mb-4" id="modal-info">
                    <i class="fa-solid fa-circle-info mr-1"></i> Upload foto bukti pengerjaan.
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2" id="modal-label">Foto Bukti</label>
                    <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 transition cursor-pointer relative" onclick="document.getElementById('progressFile').click()">
                        <input type="file" id="progressFile" name="image" class="hidden" accept="image/*" onchange="previewProgress(this)" required>
                        <div id="progressPlaceholder">
                            <i class="fa-solid fa-camera text-2xl text-slate-400 mb-2"></i>
                            <p class="text-xs text-slate-500">Klik untuk ambil/upload foto</p>
                        </div>
                        <img id="progressPreview" src="" class="hidden w-full h-40 object-cover rounded-lg mt-2 mx-auto">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Catatan Tambahan</label>
                    <textarea name="notes" id="progressNotes" rows="2" class="w-full p-3 rounded-lg border border-slate-200 text-sm" placeholder="Tambahkan catatan..."></textarea>
                </div>

                <button type="submit" id="btn-submit-prog" class="w-full py-3 bg-eco hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg transition flex justify-center items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> <span id="btn-submit-text">Kirim Update</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Image Modal Viewer -->
    <div id="imageViewerModal" class="fixed inset-0 bg-black/90 z-[110] hidden flex items-center justify-center p-4" onclick="this.classList.add('hidden')">
        <img id="modalImg" src="" class="max-w-full max-h-[90vh] rounded-lg">
        <button class="absolute top-4 right-4 text-white p-2"><i class="fa-solid fa-xmark text-2xl"></i></button>
    </div>

    <script>
        const API_URL = 'api.php';
        const CURRENT_USER_ID = <?php echo intval($userInfo['id'] ?? 0); ?>;
        const CURRENT_USER_ROLE = '<?php echo $userInfo['role'] ?? 'petugas'; ?>';
        const CURRENT_USER_AGENCY = '<?php echo addslashes($userInfo['agency'] ?? ''); ?>';

        // Notification badge helpers for petugas
        function setNotificationBadge(count) {
            const el = document.getElementById('notif-count');
            if (!el) return;
            if (count && Number(count) > 0) {
                el.textContent = String(count);
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        }

        async function fetchNotificationCount() {
            try {
                const response = await fetch(`${API_URL}?action=getNotifications&user_id=${CURRENT_USER_ID}`);
                const result = await response.json();
                if (result.success) {
                    setNotificationBadge(result.data?.unread_count || 0);
                }
            } catch (e) {
                console.error('Failed to fetch notification count', e);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchTasks();
            fetchNotificationCount();
            window.addEventListener('click', (e) => {
                const container = document.getElementById('notif-container');
                if (container && !container.contains(e.target)) {
                    const dd = document.getElementById('notif-dropdown'); if (dd) dd.classList.add('hidden');
                }
            });
            // Poll notification count periodically to update badge
            setInterval(fetchNotificationCount, 30000);
        });

        function toggleNotif() {
            document.getElementById('notif-dropdown').classList.toggle('hidden');
        }

        function updateNotifications(reports) {
            const list = document.getElementById('notif-list');
            const badge = document.getElementById('notif-count');

            const pending = reports.filter(r => r.status === 'Menunggu');
            const completed = reports.filter(r => r.status === 'Selesai');

            let html = '';
            let newCount = pending.length;

            pending.forEach(r => {
                html += `
                    <div class="p-3 hover:bg-slate-50 transition cursor-pointer flex gap-3 items-start" onclick="highlightTask('${r.id}')">
                        <div class="mt-1 w-2 h-2 rounded-full bg-orange-500 shrink-0 shadow-sm shadow-orange-200"></div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <p class="text-xs font-bold text-slate-700">Tugas Baru: ${r.category}</p>
                                <span class="text-[10px] text-orange-500 font-mono">#${r.id.split('-')[1]}</span>
                            </div>
                            <p class="text-[10px] text-slate-500 line-clamp-1 mt-0.5">${r.location}</p>
                            <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                                <i class="fa-regular fa-clock text-[9px]"></i> ${r.date}
                            </p>
                        </div>
                    </div>`;
            });
            
            completed.sort((a,b) => new Date(b.created_at) - new Date(a.created_at)).slice(0, 3).forEach(r => {
                 html += `
                    <div class="p-3 hover:bg-slate-50 transition flex gap-3 items-start opacity-75">
                        <div class="mt-1 w-2 h-2 rounded-full bg-emerald-500 shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-xs font-bold text-slate-700">Laporan Selesai</p>
                            <p class="text-[10px] text-slate-500 line-clamp-1">Laporan ${r.id} selesai.</p>
                            <p class="text-[10px] text-slate-400 mt-1">${r.date}</p>
                        </div>
                    </div>`;
            });

            if (html === '') {
                html = `<div class="p-6 text-center text-slate-400 text-xs flex flex-col items-center"><i class="fa-regular fa-bell-slash text-2xl mb-2 opacity-50"></i><p>Tidak ada notifikasi baru</p></div>`;
            }

            list.innerHTML = html;
            badge.innerText = newCount > 99 ? '99+' : newCount;
            if(newCount > 0) badge.classList.remove('hidden'); else badge.classList.add('hidden');
        }

        function highlightTask(id) {
            document.getElementById('notif-dropdown').classList.add('hidden');
            document.getElementById('tbody-tasks').scrollIntoView({behavior: 'smooth'});
        }

        // Open progress modal and populate hidden inputs
        function openModal(reportId, status) {
            const modal = document.getElementById('modal-progress');
            if (!modal) return;
            // populate hidden inputs
            const rid = document.getElementById('report-id');
            const rstatus = document.getElementById('report-status');
            const uid = document.getElementById('user-id');
            if (rid) rid.value = reportId;
            if (rstatus) rstatus.value = status;
            if (uid && !uid.value) uid.value = CURRENT_USER_ID;

            // reset file input and preview
            const file = document.getElementById('progressFile');
            const preview = document.getElementById('progressPreview');
            const placeholder = document.getElementById('progressPlaceholder');
            if (file) { file.value = ''; }
            if (preview) { preview.src = ''; preview.classList.add('hidden'); }
            if (placeholder) { placeholder.classList.remove('hidden'); }

            // set modal infos
            const title = document.getElementById('modal-title');
            const label = document.getElementById('modal-label');
            if (title) title.textContent = status === 'Selesai' ? 'Selesaikan Pengerjaan' : 'Mulai / Update Pengerjaan';
            if (label) label.textContent = 'Foto Bukti (' + (status === 'Selesai' ? 'Selesai' : 'Proses') + ')';

            modal.classList.remove('hidden');
        }

        function closeModal() {
            const modal = document.getElementById('modal-progress');
            if (!modal) return;
            modal.classList.add('hidden');
            // clear form
            const form = modal.querySelector('form');
            if (form) form.reset();
            const preview = document.getElementById('progressPreview');
            const placeholder = document.getElementById('progressPlaceholder');
            if (preview) { preview.src = ''; preview.classList.add('hidden'); }
            if (placeholder) { placeholder.classList.remove('hidden'); }
        }

        async function fetchTasks() {
            try {
                const response = await fetch(`${API_URL}?action=getReports`);
                const result = await response.json();
                if (result.success) {
                    renderTable(result.data);
                    updateStats(result.data);
                    updateNotifications(result.data);
                }
            } catch (error) { console.error('Error:', error); }
        }

        function renderTable(data) {
            const tbody = document.getElementById('tbody-tasks');
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-slate-400">Tidak ada tugas saat ini.</td></tr>`;
                return;
            }

            data.sort((a, b) => {
                const order = { 'Menunggu': 1, 'Diproses': 2, 'Selesai': 3 };
                return order[a.status] - order[b.status];
            });

            data.forEach(item => {
                const imgUser = item.img ? `<button onclick="showImage('${item.img}')" class="text-blue-600 hover:text-blue-800 text-xs font-semibold flex items-center gap-1"><i class="fa-regular fa-image"></i> Lihat Foto</button>` : `<span class="text-slate-300 text-xs">-</span>`;
                const imgProg = item.progress_image ? `<button onclick="showImage('${item.progress_image}')" class="text-emerald-600 hover:text-emerald-800 text-xs font-bold flex items-center gap-1"><i class="fa-solid fa-check-circle"></i> Lihat Bukti</button>` : `<span class="text-slate-300 text-xs italic">Belum ada</span>`;

                let actionBtn = '';
                let statusBadge = '';

                if (item.status === 'Menunggu') {
                    statusBadge = `<span class="bg-orange-100 text-orange-700 px-2 py-1 rounded text-xs font-bold border border-orange-200">Menunggu</span>`;
                    // Determine assignment: either assigned to specific petugas (assigned_to)
                    // or assigned to an agency (admin_notes) — allow action for petugas from same agency.
                    const isAssignedToMe = item.assigned_to && Number(item.assigned_to) === Number(CURRENT_USER_ID);
                    const isAssignedToMyAgency = item.admin_notes && item.admin_notes === CURRENT_USER_AGENCY;
                    if (CURRENT_USER_ROLE === 'admin' || isAssignedToMe || isAssignedToMyAgency) {
                        actionBtn = `<button onclick="openModal('${item.id}', 'Diproses')" class="px-3 py-1.5 bg-smart text-white text-xs font-bold rounded-lg hover:bg-sky-700 transition flex items-center gap-1 ml-auto"><i class="fa-solid fa-camera"></i> Proses</button>`;
                    } else {
                        actionBtn = `<span class="text-xs text-slate-400 italic">Menunggu penugasan</span>`;
                    }
                } else if (item.status === 'Diproses') {
                    statusBadge = `<span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold border border-blue-200">Diproses</span>`;
                    const isAssignedToMeProc = item.assigned_to && Number(item.assigned_to) === Number(CURRENT_USER_ID);
                    const isAssignedToMyAgencyProc = item.admin_notes && item.admin_notes === CURRENT_USER_AGENCY;
                    if (CURRENT_USER_ROLE === 'admin' || isAssignedToMeProc || isAssignedToMyAgencyProc) {
                        actionBtn = `<button onclick="openModal('${item.id}', 'Selesai')" class="px-3 py-1.5 bg-eco text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition flex items-center gap-1 ml-auto"><i class="fa-solid fa-camera"></i> Selesaikan</button>`;
                    } else {
                        actionBtn = `<span class="text-xs text-slate-400 italic">Dalam pengerjaan oleh petugas lain</span>`;
                    }
                } else {
                    statusBadge = `<span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold border border-emerald-200">Selesai</span>`;
                    actionBtn = `<span class="text-emerald-600 text-xs font-bold"><i class="fa-solid fa-check"></i> Tuntas</span>`;
                }

                tbody.innerHTML += `
                    <tr class="hover:bg-slate-50 border-b border-slate-100 last:border-0 transition">
                        <td class="px-6 py-4">${statusBadge}</td>
                        <td class="px-6 py-4">
                            <div class="text-xs font-mono text-slate-500 mb-1">${item.id}</div>
                            <span class="bg-slate-100 border border-slate-200 px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 uppercase">${item.category}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 text-sm mb-1 line-clamp-1">${item.location}</div>
                            <div class="text-xs text-slate-500 line-clamp-2">${item.desc}</div>
                        </td>
                        <td class="px-6 py-4">${imgUser}</td>
                        <td class="px-6 py-4">${imgProg}</td>
                        <td class="px-6 py-4 text-right">${actionBtn}</td>
                    </tr>`;
            });
        }

        function updateStats(data) {
            document.getElementById('stat-pending').innerText = data.filter(i => i.status === 'Menunggu').length;
            document.getElementById('stat-process').innerText = data.filter(i => i.status === 'Diproses').length;
            document.getElementById('stat-done').innerText = data.filter(i => i.status === 'Selesai').length;
        }

        function toggleNotifications() {
            const dropdown = document.getElementById('notif-dropdown');
            dropdown.classList.toggle('hidden');
            if (!dropdown.classList.contains('hidden')) {
                fetchNotifications();
            }
        }

        async function fetchNotifications() {
            try {
                const response = await fetch(`${API_URL}?action=getNotifications&user_id=${CURRENT_USER_ID}`);
                const result = await response.json();
                const container = document.getElementById('notif-list');
                const badge = document.getElementById('notif-count');

                if (result.success && result.data.notifications.length > 0) {
                    container.innerHTML = result.data.notifications.map(notif => `
                        <div class="px-4 py-3 hover:bg-slate-50 transition cursor-pointer border-b border-slate-50 last:border-b-0 ${!notif.is_read ? 'bg-eco/5' : ''}">
                            <p class="text-sm text-slate-800 font-semibold">${notif.title || 'Info Sistem'}</p>
                            <p class="text-xs text-slate-600 mt-1">${notif.message}</p>
                            <p class="text-xs text-slate-400 mt-2">${notif.time_ago}</p>
                        </div>
                    `).join('');

                    if (result.data.unread_count > 0) {
                        badge.textContent = result.data.unread_count;
                        badge.classList.remove('hidden');
                    } else {
                        if (badge) badge.classList.add('hidden');
                    }
                } else {
                    container.innerHTML = '<div class="text-center py-8 text-slate-400"><i class="fa-solid fa-bell-slash text-3xl opacity-20 mb-2"></i><p class="text-sm">Tidak ada notifikasi</p></div>';
                    if (badge) badge.classList.add('hidden');
                }
            } catch (error) {
                console.error(error);
            }
        }

        // Mark all notifications as read for current user
        async function markAllAsRead() {
            try {
                const formData = new FormData();
                formData.append('action', 'markNotificationRead');
                formData.append('id', 'all');
                formData.append('user_id', CURRENT_USER_ID);

                await fetch('includes/auth.php', { method: 'POST', body: formData });
                // refresh list and count
                fetchNotifications();
                fetchNotificationCount();
            } catch (e) {
                console.error('Failed to mark notifications as read', e);
            }
        }

        function previewProgress(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('progressPreview').src = e.target.result;
                    document.getElementById('progressPreview').classList.remove('hidden');
                    document.getElementById('progressPlaceholder').classList.add('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        async function submitProgress(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-prog');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
            btn.disabled = true;

            const formData = new FormData(e.target);
            // Append action to FormData is redundant if we also append to URL, but good for completeness.
            // The critical fix is appending to the URL in fetch below.

            try {
                // FIXED: Menambahkan ?action=updateProgress di URL
                const response = await fetch(`${API_URL}?action=updateProgress`, { 
                    method: 'POST', 
                    body: formData 
                });
                
                const result = await response.json();
                if (result.success) {
                    alert("Status laporan berhasil diperbarui!");
                    closeModal();
                    fetchTasks();
                } else {
                    alert("Gagal update: " + result.message);
                }
            } catch(err) {
                console.error(err);
                alert("Gagal mengirim data.");
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        function showImage(src) {
            document.getElementById('modalImg').src = src;
            document.getElementById('imageViewerModal').classList.remove('hidden');
        }
    </script>
</body>
</html>