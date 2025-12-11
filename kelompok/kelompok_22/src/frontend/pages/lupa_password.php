<?php
require_once __DIR__ . '/../../backend/middleware/auth.php';
redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SiPaMaLi</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

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
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="glass-card rounded-2xl shadow-2xl p-8 fade-in">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-gradient-to-br from-eco to-teal-600 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-key text-white text-2xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-slate-800 mb-2">Lupa Password?</h1>
                    <p class="text-slate-600 text-sm">Masukkan username dan email Anda untuk reset password</p>
                </div>

                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Step 1: Request Reset -->
                <div id="step1">
                    <form id="requestResetForm" class="space-y-5">
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">
                                <i class="fa-solid fa-user text-eco mr-1"></i> Username
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none transition text-sm"
                                placeholder="Masukkan username Anda"
                            >
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                                <i class="fa-solid fa-envelope text-eco mr-1"></i> Email
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none transition text-sm"
                                placeholder="contoh@email.com"
                            >
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit" 
                            id="submitBtn"
                            class="w-full py-3.5 bg-gradient-to-r from-eco to-teal-600 hover:from-eco-dark hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-eco/30 transition transform active:scale-95"
                        >
                            <i class="fa-solid fa-paper-plane mr-2"></i>
                            Kirim Link Reset
                        </button>
                    </form>
                </div>

                <!-- Step 2: Reset Password Form (Hidden initially) -->
                <div id="step2" style="display: none;">
                    <form id="resetPasswordForm" class="space-y-5">
                        <input type="hidden" id="reset_username" name="username">
                        
                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-sm font-semibold text-slate-700 mb-2">
                                <i class="fa-solid fa-lock text-eco mr-1"></i> Password Baru
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    required 
                                    minlength="6"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none transition text-sm pr-12"
                                    placeholder="Minimal 6 karakter"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePassword('new_password', 'toggleIcon1')" 
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                >
                                    <i class="fa-solid fa-eye" id="toggleIcon1"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-semibold text-slate-700 mb-2">
                                <i class="fa-solid fa-lock text-eco mr-1"></i> Konfirmasi Password
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    required 
                                    minlength="6"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-eco focus:ring-2 focus:ring-eco/20 outline-none transition text-sm pr-12"
                                    placeholder="Ulangi password baru"
                                >
                                <button 
                                    type="button" 
                                    onclick="togglePassword('confirm_password', 'toggleIcon2')" 
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                >
                                    <i class="fa-solid fa-eye" id="toggleIcon2"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button 
                            type="submit"
                            id="resetBtn"
                            class="w-full py-3.5 bg-gradient-to-r from-eco to-teal-600 hover:from-eco-dark hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-eco/30 transition transform active:scale-95"
                        >
                            <i class="fa-solid fa-key mr-2"></i>
                            Reset Password
                        </button>
                    </form>
                </div>

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <a href="/login.php" class="text-sm text-slate-500 hover:text-slate-700 inline-flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAlert(message, type = 'info') {
            const colors = {
                success: 'bg-green-50 border-green-200 text-green-800',
                error: 'bg-red-50 border-red-200 text-red-800',
                info: 'bg-blue-50 border-blue-200 text-blue-800'
            };
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle'
            };
            
            document.getElementById('alertContainer').innerHTML = `
                <div class="${colors[type]} border px-4 py-3 rounded-xl mb-4 flex items-start gap-3">
                    <i class="fa-solid ${icons[type]} mt-0.5"></i>
                    <p class="text-sm">${message}</p>
                </div>
            `;
        }

        function togglePassword(fieldId, iconId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Step 1: Request Reset Link
        document.getElementById('requestResetForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('submitBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...';
            btn.disabled = true;

            const formData = new FormData(e.target);
            formData.append('action', 'request_reset');

            try {
                const response = await fetch('/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    document.getElementById('reset_username').value = formData.get('username');
                    
                    // Switch to step 2
                    setTimeout(() => {
                        document.getElementById('step1').style.display = 'none';
                        document.getElementById('step2').style.display = 'block';
                    }, 1500);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });

        // Step 2: Reset Password
        document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                showAlert('Password tidak cocok!', 'error');
                return;
            }

            const btn = document.getElementById('resetBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...';
            btn.disabled = true;

            const formData = new FormData(e.target);
            formData.append('action', 'reset_password');

            try {
                const response = await fetch('/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Password berhasil direset! Mengalihkan ke login...', 'success');
                    setTimeout(() => {
                        window.location.href = '/login.php';
                    }, 2000);
                } else {
                    showAlert(result.message, 'error');
                }
            } catch (error) {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
