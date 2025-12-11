<?php

require_once __DIR__ . '/../../backend/middleware/auth.php';

redirectIfLoggedIn();

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi';
    } else {
        $result = loginUser($username, $password);
        
        if ($result['success']) {
            // Redirect berdasarkan role
            $role = $result['role'];
            if ($role === 'super_admin') {
                header('Location: /super_admin.php');
            } elseif ($role === 'admin') {
                header('Location: /admin.php');
            } elseif ($role === 'petugas') {
                header('Location: /petugas.php');
            } else {
                header('Location: /pelapor.php');
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SiPaMaLi</title>
    
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
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0c4a6e 0%, #059669 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="antialiased">
    
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            
            <!-- Logo & Header -->
            <div class="text-center mb-8 fade-in">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4">
                    <i class="fa-solid fa-leaf text-4xl text-eco"></i>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">SiPaMaLi</h1>
                <p class="text-white/80 text-sm">Admin Login Portal</p>
            </div>

            <!-- Login Card -->
            <div class="glass-card rounded-2xl shadow-2xl p-8 fade-in" style="animation-delay: 0.1s;">
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Masuk ke Dashboard</h2>
                <p class="text-slate-500 text-sm mb-6">Silakan login untuk mengelola laporan warga</p>

                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                    <span class="text-sm"><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-5">
                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fa-solid fa-user mr-1 text-slate-400"></i> Username
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            autocomplete="username"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none transition text-sm"
                            placeholder="Masukkan username"
                            value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        >
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="fa-solid fa-lock mr-1 text-slate-400"></i> Password
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none transition text-sm pr-12"
                                placeholder="Masukkan password"
                            >
                            <button 
                                type="button" 
                                onclick="togglePassword()" 
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                            >
                                <i class="fa-solid fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me (Optional) -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-eco border-slate-300 rounded focus:ring-eco">
                            <span class="text-sm text-slate-600">Ingat saya</span>
                        </label>
                        <a href="/lupa_password.php" class="text-sm text-eco hover:text-eco-dark font-medium">Lupa password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full py-3.5 bg-gradient-to-r from-eco to-teal-600 hover:from-eco-dark hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-eco/30 transition transform active:scale-95 flex items-center justify-center gap-2"
                    >
                        <i class="fa-solid fa-right-to-bracket"></i>
                        <span>Masuk Dashboard</span>
                    </button>
                </form>

                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-slate-600">
                        Belum punya akun? 
                        <a href="/registrasi.php" class="text-eco hover:text-eco-dark font-bold hover:underline">
                            Daftar Sekarang
                        </a>
                    </p>
                </div>

                <!-- Back to Home -->
                <div class="mt-4 text-center">
                    <a href="/" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-8 text-white/60 text-xs">
                <p>&copy; 2025 SiPaMaLi - Kelompok 22</p>
                <p class="mt-1">Sistem Pelaporan & Pemantauan Masalah Lingkungan</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Focus on username field on load
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>
