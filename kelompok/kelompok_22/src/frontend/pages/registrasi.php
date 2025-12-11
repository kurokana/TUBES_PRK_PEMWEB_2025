<?php require_once __DIR__ . '/../../backend/middleware/auth.php'; redirectIfLoggedIn(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - SiPaMaLi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 font-sans text-slate-800">

<div class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Buat Akun Warga</h1>
            <p class="text-slate-500 text-sm">Bergabung untuk melaporkan masalah lingkungan.</p>
        </div>

        <form id="registerForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="full_name" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none placeholder:text-slate-400" placeholder="Nama Lengkap Anda">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">NIK (Nomor Identitas Keluarga)</label>
                <input type="text" name="nik" required maxlength="16" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none" placeholder="16 digit NIK Anda" pattern="[0-9]{16}">
                <p class="text-xs text-slate-400 mt-1">Masukkan 16 digit NIK Anda (tanpa spasi)</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="username" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">No. HP</label>
                    <input type="text" name="phone" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none placeholder:text-slate-400" placeholder="contoh@email.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <button type="submit" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg transition mt-4 shadow-lg shadow-emerald-500/30">
                Daftar Sekarang
            </button>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center">
            <p class="text-sm text-slate-600">
                Sudah punya akun? 
                <a href="/login.php" class="text-emerald-600 font-bold hover:underline">
                    Masuk disini
                </a>
            </p>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-4">
            <a href="/" class="text-xs text-slate-400 hover:text-slate-600 inline-flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'register');

    // Validasi NIK client-side
    const nik = formData.get('nik');
    if (nik && (nik.length !== 16 || isNaN(nik))) {
        alert('NIK harus 16 digit angka');
        return;
    }

    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.textContent;
    btn.textContent = 'Mendaftar...';
    btn.disabled = true;

    try {
        const res = await fetch('../../backend/middleware/auth.php', { method: 'POST', body: formData });
        const data = await res.json();
        
        if (data.success) {
            alert('Registrasi Berhasil! Silakan login.');
            window.location.href = '/login.php';
        } else {
            alert('Error: ' + data.message);
        }
    } catch (err) {
        alert('Terjadi kesalahan sistem: ' + err.message);
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
});
</script>

</body>
</html>