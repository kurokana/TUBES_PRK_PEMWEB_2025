<?php
require_once '../middleware/auth.php';
require_once '../utils/config.php';

requireSuperAdmin();

$user = getUserInfo();
$conn = getDBConnection();

// Get statistics
$stats_query = "SELECT * FROM super_admin_stats";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Get recent audit logs
$audit_query = "SELECT al.*, u.username, u.full_name 
                FROM audit_logs al 
                LEFT JOIN users u ON al.user_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT 50";
$audit_logs = $conn->query($audit_query);

// Get all users for management
$users_query = "SELECT id, username, email, full_name, role, is_active, created_at, last_login 
                FROM users 
                ORDER BY created_at DESC";
$users = $conn->query($users_query);

// Get all reports summary
$reports_query = "SELECT * FROM report_workflow_summary ORDER BY last_updated DESC LIMIT 50";
$reports = $conn->query($reports_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - SiPaMaLi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-purple-600 to-purple-800 text-white shadow-lg">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-shield-alt text-2xl"></i>
                <div>
                    <h1 class="text-xl font-bold">Super Admin</h1>
                    <p class="text-xs text-purple-200">Sistem Manajemen SiPaMaLi</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm"><?= htmlspecialchars($user['full_name']) ?></span>
                <a href="?logout=1" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm transition">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-500 text-white rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">Total Users</p>
                        <p class="text-3xl font-bold"><?= $stats['total_users'] ?></p>
                    </div>
                    <i class="fas fa-users text-4xl opacity-50"></i>
                </div>
            </div>
            <div class="bg-green-500 text-white rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">Total Reports</p>
                        <p class="text-3xl font-bold"><?= $stats['total_reports'] ?></p>
                    </div>
                    <i class="fas fa-file-alt text-4xl opacity-50"></i>
                </div>
            </div>
            <div class="bg-yellow-500 text-white rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">Pending</p>
                        <p class="text-3xl font-bold"><?= $stats['pending_reports'] ?></p>
                    </div>
                    <i class="fas fa-clock text-4xl opacity-50"></i>
                </div>
            </div>
            <div class="bg-purple-500 text-white rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-80">Today Audits</p>
                        <p class="text-3xl font-bold"><?= $stats['today_audit_count'] ?></p>
                    </div>
                    <i class="fas fa-history text-4xl opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Role Distribution -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 shadow">
                <p class="text-sm text-slate-600">Warga</p>
                <p class="text-2xl font-bold text-blue-600"><?= $stats['total_warga'] ?></p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow">
                <p class="text-sm text-slate-600">Petugas</p>
                <p class="text-2xl font-bold text-green-600"><?= $stats['total_petugas'] ?></p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow">
                <p class="text-sm text-slate-600">Admin</p>
                <p class="text-2xl font-bold text-orange-600"><?= $stats['total_admin'] ?></p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow">
                <p class="text-sm text-slate-600">Super Admin</p>
                <p class="text-2xl font-bold text-purple-600"><?= $stats['total_super_admin'] ?></p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-lg mb-6">
            <div class="border-b">
                <div class="flex space-x-4 px-6">
                    <button onclick="showTab('users')" id="tab-users" class="px-4 py-3 font-semibold border-b-2 border-purple-600 text-purple-600">
                        <i class="fas fa-users mr-2"></i>User Management
                    </button>
                    <button onclick="showTab('reports')" id="tab-reports" class="px-4 py-3 font-semibold text-slate-600 hover:text-purple-600">
                        <i class="fas fa-file-alt mr-2"></i>All Reports (Read-Only)
                    </button>
                    <button onclick="showTab('audit')" id="tab-audit" class="px-4 py-3 font-semibold text-slate-600 hover:text-purple-600">
                        <i class="fas fa-history mr-2"></i>Audit Logs
                    </button>
                </div>
            </div>

            <!-- User Management Tab -->
            <div id="content-users" class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Manajemen User</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Username</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nama Lengkap</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Email</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Role</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Last Login</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php while ($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u['full_name']) ?></td>
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        <?= $u['role'] === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' ?>
                                        <?= $u['role'] === 'admin' ? 'bg-orange-100 text-orange-800' : '' ?>
                                        <?= $u['role'] === 'petugas' ? 'bg-green-100 text-green-800' : '' ?>
                                        <?= $u['role'] === 'warga' ? 'bg-blue-100 text-blue-800' : '' ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?= $u['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    <?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : 'Never' ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <button onclick="editUserRole(<?= $u['id'] ?>, '<?= $u['username'] ?>', '<?= $u['role'] ?>')" 
                                            class="text-blue-600 hover:text-blue-800 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleUserStatus(<?= $u['id'] ?>, <?= $u['is_active'] ?>)" 
                                            class="text-orange-600 hover:text-orange-800">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reports Tab -->
            <div id="content-reports" class="p-6 hidden">
                <h2 class="text-xl font-bold mb-4">All Reports (Read-Only View)</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Report ID</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Category</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Reporter</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Admin</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Petugas</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Submitted</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php while ($r = $reports->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono"><?= htmlspecialchars($r['report_id']) ?></td>
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($r['category']) ?></td>
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($r['reporter_name'] ?? 'Anonymous') ?></td>
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($r['admin_name'] ?? '-') ?></td>
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($r['petugas_name'] ?? '-') ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        <?= $r['status'] === 'Menunggu' ? 'bg-yellow-100 text-yellow-800' : '' ?>
                                        <?= $r['status'] === 'Diproses' ? 'bg-blue-100 text-blue-800' : '' ?>
                                        <?= $r['status'] === 'Selesai' ? 'bg-green-100 text-green-800' : '' ?>">
                                        <?= htmlspecialchars($r['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    <?= date('d/m/Y H:i', strtotime($r['submitted_at'])) ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Audit Logs Tab -->
            <div id="content-audit" class="p-6 hidden">
                <h2 class="text-xl font-bold mb-4">Audit Logs</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Time</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">User</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Action</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Description</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php while ($log = $audit_logs->fetch_assoc()): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <?= htmlspecialchars($log['full_name'] ?? 'System') ?>
                                    <span class="text-xs text-slate-500">(<?= htmlspecialchars($log['username'] ?? '-') ?>)</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-800">
                                        <?= htmlspecialchars($log['action_type']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm"><?= htmlspecialchars($log['description']) ?></td>
                                <td class="px-4 py-3 text-sm font-mono text-xs"><?= htmlspecialchars($log['ip_address']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div id="editRoleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-96">
            <h3 class="text-xl font-bold mb-4">Edit User Role</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Username</label>
                <input type="text" id="editUsername" readonly class="w-full px-3 py-2 border rounded bg-slate-100">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">New Role</label>
                <select id="editRole" class="w-full px-3 py-2 border rounded">
                    <option value="warga">Warga</option>
                    <option value="petugas">Petugas</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button onclick="saveUserRole()" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                    Save
                </button>
                <button onclick="closeModal()" class="flex-1 bg-slate-300 hover:bg-slate-400 text-slate-800 px-4 py-2 rounded">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentUserId = null;

        function showTab(tab) {
            // Hide all content
            document.getElementById('content-users').classList.add('hidden');
            document.getElementById('content-reports').classList.add('hidden');
            document.getElementById('content-audit').classList.add('hidden');
            
            // Reset all tab styles
            document.getElementById('tab-users').className = 'px-4 py-3 font-semibold text-slate-600 hover:text-purple-600';
            document.getElementById('tab-reports').className = 'px-4 py-3 font-semibold text-slate-600 hover:text-purple-600';
            document.getElementById('tab-audit').className = 'px-4 py-3 font-semibold text-slate-600 hover:text-purple-600';
            
            // Show selected content and highlight tab
            document.getElementById('content-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).className = 'px-4 py-3 font-semibold border-b-2 border-purple-600 text-purple-600';
        }

        function editUserRole(userId, username, currentRole) {
            currentUserId = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editRole').value = currentRole;
            document.getElementById('editRoleModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('editRoleModal').classList.add('hidden');
        }

        async function saveUserRole() {
            const newRole = document.getElementById('editRole').value;
            
            const formData = new FormData();
            formData.append('action', 'change_user_role');
            formData.append('user_id', currentUserId);
            formData.append('new_role', newRole);

            try {
                const res = await fetch('../utils/admin_utils.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    alert('Role berhasil diubah!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Terjadi kesalahan: ' + err.message);
            }
        }

        async function toggleUserStatus(userId, currentStatus) {
            const newStatus = currentStatus ? 0 : 1;
            const action = newStatus ? 'activate' : 'deactivate';
            
            if (!confirm(`Yakin ingin ${action} user ini?`)) return;

            const formData = new FormData();
            formData.append('action', 'toggle_user_status');
            formData.append('user_id', userId);
            formData.append('is_active', newStatus);

            try {
                const res = await fetch('../utils/admin_utils.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                if (data.success) {
                    alert('Status user berhasil diubah!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) {
                alert('Terjadi kesalahan: ' + err.message);
            }
        }
    </script>
</body>
</html>
