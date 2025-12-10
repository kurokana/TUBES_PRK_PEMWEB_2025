<?php
/**
 * Halaman Edit Profil Pelapor
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

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    global $conn;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update basic info
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $phone, $user['id']);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal update profil: " . $stmt->error);
        }
        $stmt->close();
        
        // Handle password change if provided
        if (!empty($current_password) && !empty($new_password)) {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $db_user = $result->fetch_assoc();
            $stmt->close();
            
            if (!password_verify($current_password, $db_user['password'])) {
                throw new Exception("Password saat ini salah");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("Password baru tidak cocok");
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception("Password baru minimal 6 karakter");
            }
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user['id']);
            
            if (!$stmt->execute()) {
                throw new Exception("Gagal update password: " . $stmt->error);
            }
            $stmt->close();
        }
        
        // Handle photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . basename($_FILES['photo']['name']);
            $target_file = $upload_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is actual image
            $check = getimagesize($_FILES['photo']['tmp_name']);
            if ($check === false) {
                throw new Exception("File bukan gambar");
            }
            
            // Check file size (max 2MB)
            if ($_FILES['photo']['size'] > 2000000) {
                throw new Exception("Ukuran file maksimal 2MB");
            }
            
            // Allow certain file formats
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($imageFileType, $allowed_types)) {
                throw new Exception("Hanya format JPG, JPEG, PNG & GIF yang diizinkan");
            }
            
            // Delete old photo if exists
            if (!empty($user['photo'])) {
                $old_file = 'uploads/' . $user['photo'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            
            // Upload new photo
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                $stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id = ?");
                $photo_path = 'profiles/' . $file_name;
                $stmt->bind_param("si", $photo_path, $user['id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Gagal update foto profil");
                }
                $stmt->close();
            } else {
                throw new Exception("Gagal mengupload foto");
            }
        }
        
        $conn->commit();
        $success = "Profil berhasil diperbarui!";
        
        // Refresh user data
        $user = getCurrentUser();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - <?php echo SITE_NAME; ?></title>
    
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
                    <span class="text-sm text-slate-600">Edit Profil</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="riwayat.php" class="text-sm text-slate-600 hover:text-emerald-600 transition">
                        <i class="fa-solid fa-clock-rotate-left mr-1"></i> Riwayat
                    </a>
                    <a href="index.php" class="text-sm text-slate-600 hover:text-emerald-600 transition">
                        <i class="fa-solid fa-home mr-1"></i> Beranda
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-2">Edit Profil</h1>
            <p class="text-slate-600">Kelola informasi akun dan preferensi Anda</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-start gap-3">
            <i class="fa-solid fa-circle-check text-emerald-500 text-lg mt-0.5"></i>
            <div class="flex-1">
                <p class="text-sm font-medium text-emerald-800">Berhasil!</p>
                <p class="text-sm text-emerald-700"><?php echo htmlspecialchars($success); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3">
            <i class="fa-solid fa-circle-exclamation text-red-500 text-lg mt-0.5"></i>
            <div class="flex-1">
                <p class="text-sm font-medium text-red-800">Terjadi Kesalahan</p>
                <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column - Profile Overview -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Profile Card -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 card-hover">
                    <div class="text-center mb-6">
                        <div class="relative inline-block mb-4">
                            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg mx-auto">
                                <?php if (!empty($user['photo'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                                         class="w-full h-full object-cover"
                                         alt="Profile Photo">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center text-white text-4xl font-bold">
                                        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <h3 class="text-xl font-bold text-slate-800 mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-slate-500 text-sm mb-3">Pelapor</p>
                        
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                            <i class="fa-solid fa-circle text-xs"></i>
                            Akun Aktif
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <i class="fa-solid fa-user w-5 text-slate-400"></i>
                            <span><?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <i class="fa-solid fa-envelope w-5 text-slate-400"></i>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <i class="fa-solid fa-phone w-5 text-slate-400"></i>
                            <span><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '-'; ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-slate-600">
                            <i class="fa-solid fa-calendar w-5 text-slate-400"></i>
                            <span>Bergabung: <?php echo date('d M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 card-hover">
                    <h4 class="font-bold text-slate-800 mb-4">Statistik Anda</h4>
                    <div class="space-y-4">
                        <?php
                        global $conn;
                        $stats_stmt = $conn->prepare("
                            SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as menunggu,
                                SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as diproses,
                                SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai
                            FROM reports 
                            WHERE user_id = ?
                        ");
                        $stats_stmt->bind_param("i", $user['id']);
                        $stats_stmt->execute();
                        $stats_result = $stats_stmt->get_result();
                        $user_stats = $stats_result->fetch_assoc();
                        $stats_stmt->close();
                        ?>
                        
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-slate-600">Total Laporan</span>
                                <span class="font-bold text-slate-800"><?php echo $user_stats['total'] ?? 0; ?></span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-sky-500 rounded-full" 
                                     style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-slate-600">Menunggu</span>
                                <span class="font-bold text-orange-600"><?php echo $user_stats['menunggu'] ?? 0; ?></span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-orange-400 rounded-full" 
                                     style="width: <?php echo $user_stats['total'] ? ($user_stats['menunggu'] / $user_stats['total'] * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-slate-600">Diproses</span>
                                <span class="font-bold text-blue-600"><?php echo $user_stats['diproses'] ?? 0; ?></span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-400 rounded-full" 
                                     style="width: <?php echo $user_stats['total'] ? ($user_stats['diproses'] / $user_stats['total'] * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-slate-600">Selesai</span>
                                <span class="font-bold text-emerald-600"><?php echo $user_stats['selesai'] ?? 0; ?></span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-400 rounded-full" 
                                     style="width: <?php echo $user_stats['total'] ? ($user_stats['selesai'] / $user_stats['total'] * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Edit Forms -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information Form -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 card-hover">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <i class="fa-solid fa-user-edit text-emerald-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Informasi Dasar</h3>
                            <p class="text-sm text-slate-500">Perbarui informasi profil Anda</p>
                        </div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <!-- Photo Upload -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3">Foto Profil</label>
                            <div class="flex items-center gap-6">
                                <div class="relative">
                                    <?php if (!empty($user['photo'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                                             class="w-20 h-20 rounded-full object-cover border-2 border-slate-200"
                                             id="profilePhotoPreview">
                                    <?php else: ?>
                                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-emerald-500 to-sky-500 flex items-center justify-center text-white text-2xl font-bold border-2 border-slate-200"
                                             id="profilePhotoPreview">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex-1">
                                    <input type="file" name="photo" id="photoInput" accept="image/*" class="hidden" onchange="previewProfilePhoto(this)">
                                    <label for="photoInput" class="cursor-pointer">
                                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition text-sm font-medium">
                                            <i class="fa-solid fa-camera"></i>
                                            Unggah Foto Baru
                                        </div>
                                    </label>
                                    <p class="text-xs text-slate-500 mt-2">Format: JPG, PNG (Maks 2MB)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nomor Telepon</label>
                            <div class="relative">
                                <i class="fa-solid fa-phone absolute left-4 top-3.5 text-slate-400"></i>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"
                                    pattern="[0-9]{10,13}"
                                    class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition"
                                    placeholder="081234567890">
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Format: 08xxxxxxxxxx (10-13 digit)</p>
                        </div>

                        <!-- Email (readonly) -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed">
                            <p class="text-xs text-slate-500 mt-2">Email tidak dapat diubah</p>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <button type="submit" name="update_profile" value="info"
                                class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                                <i class="fa-solid fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Password Change Form -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 card-hover">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                            <i class="fa-solid fa-lock text-red-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Ubah Password</h3>
                            <p class="text-sm text-slate-500">Perbarui password akun Anda</p>
                        </div>
                    </div>
                    
                    <form method="POST" class="space-y-6">
                        <!-- Current Password -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Password Saat Ini</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                                <input type="password" name="current_password" id="currentPassword"
                                    class="w-full pl-12 pr-12 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition">
                                <button type="button" onclick="togglePassword('currentPassword', 'toggleCurrent')" 
                                    class="absolute right-4 top-3.5 text-slate-400 hover:text-slate-600">
                                    <i class="fa-solid fa-eye" id="toggleCurrent"></i>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Password Baru</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                                <input type="password" name="new_password" id="newPassword"
                                    class="w-full pl-12 pr-12 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition">
                                <button type="button" onclick="togglePassword('newPassword', 'toggleNew')" 
                                    class="absolute right-4 top-3.5 text-slate-400 hover:text-slate-600">
                                    <i class="fa-solid fa-eye" id="toggleNew"></i>
                                </button>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">Minimal 6 karakter</p>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Konfirmasi Password Baru</label>
                            <div class="relative">
                                <i class="fa-solid fa-lock absolute left-4 top-3.5 text-slate-400"></i>
                                <input type="password" name="confirm_password" id="confirmPassword"
                                    class="w-full pl-12 pr-12 py-3 rounded-xl border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none transition">
                                <button type="button" onclick="togglePassword('confirmPassword', 'toggleConfirm')" 
                                    class="absolute right-4 top-3.5 text-slate-400 hover:text-slate-600">
                                    <i class="fa-solid fa-eye" id="toggleConfirm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100">
                            <button type="submit" name="update_profile" value="password"
                                class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition">
                                <i class="fa-solid fa-key mr-2"></i>Ubah Password
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Account Actions -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 card-hover">
                    <h4 class="text-lg font-bold text-slate-800 mb-4">Aksi Akun</h4>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                            <div>
                                <p class="font-medium text-slate-700">Hapus Akun</p>
                                <p class="text-sm text-slate-500">Akun dan semua data akan dihapus permanen</p>
                            </div>
                            <button onclick="confirmDeleteAccount()" 
                                class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 font-medium rounded-lg transition text-sm">
                                Hapus
                            </button>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                            <div>
                                <p class="font-medium text-slate-700">Unduh Data</p>
                                <p class="text-sm text-slate-500">Unduh semua data laporan Anda</p>
                            </div>
                            <button onclick="downloadData()" 
                                class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium rounded-lg transition text-sm">
                                Unduh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Preview profile photo
        function previewProfilePhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profilePhotoPreview');
                    
                    // Check if preview is an image or div
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        // Replace div with image
                        const newImg = document.createElement('img');
                        newImg.id = 'profilePhotoPreview';
                        newImg.className = 'w-20 h-20 rounded-full object-cover border-2 border-slate-200';
                        newImg.src = e.target.result;
                        preview.parentNode.replaceChild(newImg, preview);
                    }
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Confirm account deletion
        function confirmDeleteAccount() {
            if (confirm('Apakah Anda yakin ingin menghapus akun? Semua data akan dihapus permanen dan tidak dapat dikembalikan.')) {
                // Redirect to delete account endpoint
                window.location.href = 'delete_account.php';
            }
        }
        
        // Download user data
        function downloadData() {
            alert('Fitur unduh data akan segera tersedia.');
        }
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Check if it's password form
                    if (form.querySelector('[name="new_password"]')) {
                        const currentPass = form.querySelector('[name="current_password"]').value;
                        const newPass = form.querySelector('[name="new_password"]').value;
                        const confirmPass = form.querySelector('[name="confirm_password"]').value;
                        
                        // If any password field is filled, all must be filled
                        if (currentPass || newPass || confirmPass) {
                            if (!currentPass || !newPass || !confirmPass) {
                                e.preventDefault();
                                alert('Harap isi semua field password untuk mengubah password.');
                                return;
                            }
                            
                            if (newPass.length < 6) {
                                e.preventDefault();
                                alert('Password baru minimal 6 karakter.');
                                return;
                            }
                            
                            if (newPass !== confirmPass) {
                                e.preventDefault();
                                alert('Password baru dan konfirmasi password tidak cocok.');
                                return;
                            }
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>