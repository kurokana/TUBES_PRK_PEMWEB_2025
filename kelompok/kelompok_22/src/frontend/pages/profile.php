<?php
/**
 * Halaman Profile User
 */
require_once __DIR__ . '/../../backend/utils/config.php';
require_once __DIR__ . '/../../backend/middleware/auth.php';

requireLogin();

$user = getCurrentUser();
if (!$user) {
    logoutUser();
    header('Location: /login.php');
    exit;
}

// Handle profile update
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    
    // Basic validation
    $errors = [];
    if (empty($full_name)) $errors[] = 'Nama lengkap harus diisi';
    if (empty($phone)) $errors[] = 'Nomor telepon harus diisi';
    if (empty($email)) $errors[] = 'Email harus diisi';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid';
    
    if (empty($errors)) {
        $conn = getDBConnection();
        
        // Check if email is already used by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param('si', $email, $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = 'Email sudah digunakan oleh pengguna lain';
            $messageType = 'error';
        } else {
            // Update profile
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ?");
            $stmt->bind_param('sssi', $full_name, $phone, $email, $user['id']);
            
            if ($stmt->execute()) {
                // Update session
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['phone'] = $phone;
                $message = 'Profil berhasil diperbarui';
                $messageType = 'success';
                
                // Refresh user data
                $user = getCurrentUser();
                
                // Audit log
                logAudit($user['id'], 'update', 'users', $user['id'], 'User updated profile: ' . $full_name);
            } else {
                $message = 'Gagal memperbarui profil';
                $messageType = 'error';
            }
        }
        $stmt->close();
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}

// Get user statistics
$conn = getDBConnection();
$user_id = $user['id'];

$stats_query = "SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as completed
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
    <title>Profile - <?php echo SITE_NAME; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/src/frontend/assets/css/styles.css">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-text {
            background: linear-gradient(135deg, #059669 0%, #0284c7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="pelapor.php" class="flex items-center gap-2">
                        <i class="fa-solid fa-leaf text-emerald-600 text-xl"></i>
                        <span class="font-bold text-xl gradient-text"><?php echo SITE_NAME; ?></span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <a href="pelapor.php" class="text-slate-600 hover:text-emerald-600 transition">
                        <i class="fa-solid fa-home"></i> Beranda
                    </a>
                    <a href="riwayat.php" class="text-slate-600 hover:text-emerald-600 transition">
                        <i class="fa-solid fa-history"></i> Riwayat
                    </a>
                    <a href="profile.php" class="text-emerald-600 font-medium">
                        <i class="fa-solid fa-user"></i> Profile
                    </a>
                    <a href="logout.php" class="text-slate-600 hover:text-red-600 transition">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Profile Pengguna</h1>
            <p class="text-slate-600">Kelola informasi profil Anda</p>
        </div>

        <!-- Message -->
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
            <div class="flex items-center gap-2">
                <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-xl font-semibold text-slate-800 mb-6">Edit Profil</h2>
                    
                    <form method="POST" class="space-y-6">
                        <div>
                            <label for="username" class="block text-sm font-medium text-slate-700 mb-2">Username</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg bg-slate-50 text-slate-500 cursor-not-allowed" readonly>
                            <p class="text-xs text-slate-500 mt-1">Username tidak dapat diubah</p>
                        </div>
                        
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap *</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">Nomor Telepon *</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" required>
                        </div>
                        
                        <div class="flex gap-4">
                            <button type="submit" name="update_profile" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-medium transition">
                                <i class="fa-solid fa-save mr-2"></i>Simpan Perubahan
                            </button>
                            <a href="pelapor.php" class="bg-slate-500 hover:bg-slate-600 text-white px-6 py-3 rounded-lg font-medium transition">
                                <i class="fa-solid fa-arrow-left mr-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Sidebar -->
            <div class="space-y-6">
                <!-- User Info -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-user text-emerald-600 text-2xl"></i>
                        </div>
                        <h3 class="font-semibold text-slate-800"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-sm text-slate-600"><?php echo ucfirst($user['role']); ?></p>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Statistik Laporan</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Total Laporan</span>
                            <span class="font-semibold text-slate-800"><?php echo $stats['total_reports']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Menunggu</span>
                            <span class="font-semibold text-amber-600"><?php echo $stats['pending']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Diproses</span>
                            <span class="font-semibold text-blue-600"><?php echo $stats['in_progress']; ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-600">Selesai</span>
                            <span class="font-semibold text-emerald-600"><?php echo $stats['completed']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-4">Informasi Akun</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-600">Role:</span>
                            <span class="font-medium"><?php echo ucfirst($user['role']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Status:</span>
                            <span class="font-medium text-emerald-600">Aktif</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600">Bergabung:</span>
                            <span class="font-medium"><?php echo date('d M Y', strtotime($user['login_time'] ?? 'now')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-hide message after 5 seconds
        setTimeout(() => {
            const message = document.querySelector('.bg-emerald-50, .bg-red-50');
            if (message) {
                message.style.transition = 'opacity 0.5s';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>