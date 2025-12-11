# 🧪 Panduan Testing Upload Warga

## Persiapan

1. **Pastikan server berjalan:**
   ```bash
   cd /media/xzalfs/coding/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_22
   php -S localhost:8000 router.php
   ```

2. **Login sebagai warga:**
   - Buka browser: http://localhost:8000/login.php
   - Gunakan kredensial warga:
     - Username: `warga_ahmad`
     - Password: `password123` (atau password yang sudah diset di database)

## Testing dengan Page Test Upload

1. **Akses halaman test:**
   - Buka: http://localhost:8000/test_upload.html
   
2. **Verifikasi session:**
   - Pastikan di bagian atas halaman muncul informasi login:
     - User ID
     - Username
     - Role (harus warga/pelapor)
     - Full Name

3. **Test upload laporan:**
   - Isi form dengan data test:
     - **Kategori:** Pilih salah satu (misal: Infrastruktur)
     - **Lokasi:** Contoh: "Jl. Sudirman No. 123"
     - **Deskripsi:** Contoh: "Test upload dari role warga - jalan berlubang"
     - **Foto:** Upload foto (opsional, max 5MB)
   
   - Klik tombol "Kirim Laporan Test"
   
4. **Cek hasil:**
   - Jika berhasil:
     - Muncul notifikasi hijau ✅
     - Dapat melihat report_code yang digenerate
   - Jika gagal:
     - Muncul notifikasi merah ❌
     - Lihat pesan error

## Testing dengan Halaman Pelapor Normal

1. **Akses halaman pelapor:**
   - Buka: http://localhost:8000/pelapor.php

2. **Buat laporan:**
   - Isi form seperti biasa
   - Upload foto jika ada
   - Klik "Kirim Laporan"

3. **Verifikasi:**
   - Laporan muncul di daftar laporan di bawah form
   - Foto dapat ditampilkan (jika diupload)

## Verifikasi Foto Dapat Diakses

### 1. Cek di Folder Uploads
```bash
ls -la /media/xzalfs/coding/TUBES_PRK_PEMWEB_2025/kelompok/kelompok_22/uploads/
```

Seharusnya ada file dengan format: `LAP-YYYYMMDD-XXXX_timestamp.jpg`

### 2. Akses via Browser
Jika nama file adalah `LAP-20250611-0001_1765450999.jpg`, akses:
```
http://localhost:8000/uploads/LAP-20250611-0001_1765450999.jpg
```

Foto seharusnya dapat dilihat langsung di browser.

### 3. Cek di Admin Dashboard
- Login sebagai admin:
  - Username: `admin`
  - Password: `admin123`
- Buka: http://localhost:8000/admin.php
- Foto warga seharusnya muncul di daftar laporan

### 4. Cek di Petugas Dashboard
- Login sebagai petugas:
  - Username: `petugas1`
  - Password: `password123`
- Buka: http://localhost:8000/petugas.php
- Foto warga seharusnya muncul di laporan yang di-assign ke petugas

## Troubleshooting

### Error: "Anda harus login untuk membuat laporan"
**Solusi:**
1. Pastikan sudah login terlebih dahulu di `/login.php`
2. Cek apakah session masih aktif dengan membuka `/test_upload.html`
3. Jika session tidak ada, login ulang

### Error: "File terlalu besar"
**Solusi:**
1. Pastikan file foto tidak lebih dari 5MB
2. Compress foto jika perlu

### Error: "Format file tidak didukung"
**Solusi:**
1. Pastikan file adalah JPG, JPEG, PNG, atau GIF
2. Rename file jika ekstensi tidak sesuai

### Foto tidak muncul di browser
**Solusi:**
1. Cek apakah file ada di folder uploads:
   ```bash
   ls -la uploads/
   ```
2. Cek permission folder:
   ```bash
   chmod 755 uploads/
   ```
3. Pastikan router.php sudah handle serving file uploads (sudah diperbaiki)

### Database tidak tersimpan
**Solusi:**
1. Cek koneksi database di `src/backend/utils/config.php`
2. Test koneksi:
   ```bash
   mysql -u sipamali_user -psipamali_password sipamali_db -e "SHOW TABLES;"
   ```
3. Pastikan table `reports` ada dan memiliki kolom yang benar

## Kredensial Test Users

### Warga
- Username: `warga_ahmad` | Password: `password123`
- Username: `warga_sari` | Password: `password123`
- Username: `warga_rizki` | Password: `password123`

### Petugas
- Username: `petugas1` | Password: `password123`
- Username: `petugas2` | Password: `password123`

### Admin
- Username: `admin` | Password: `admin123`

### Super Admin
- Username: `superadmin` | Password: `superadmin123`

## Query Debugging

### Cek laporan terbaru
```bash
mysql -u sipamali_user -psipamali_password sipamali_db -e "SELECT report_id, user_id, category, location, status, image_path, created_at FROM reports ORDER BY created_at DESC LIMIT 5;"
```

### Cek total laporan per user
```bash
mysql -u sipamali_user -psipamali_password sipamali_db -e "SELECT u.username, u.role, COUNT(r.id) as total_reports FROM users u LEFT JOIN reports r ON u.id = r.user_id GROUP BY u.id, u.username, u.role;"
```

### Cek laporan dengan foto
```bash
mysql -u sipamali_user -psipamali_password sipamali_db -e "SELECT report_id, image_path FROM reports WHERE image_path IS NOT NULL ORDER BY created_at DESC LIMIT 10;"
```

## Fitur yang Sudah Diperbaiki

✅ Router bersih dengan clean URLs
✅ API endpoint createReport dengan validasi
✅ Upload foto dengan validasi (5MB max, jpg/jpeg/png/gif)
✅ Session authentication dengan isLoggedIn()
✅ Database connection dengan getDBConnection()
✅ File serving dari /uploads/ dengan MIME type yang benar
✅ Path constants tidak double define
✅ Endpoint checkSession untuk verifikasi login
✅ Test page untuk debugging upload

## Kontak

Jika masih ada masalah, cek:
1. Error log server (di terminal tempat server jalan)
2. Console browser (F12 → Console)
3. Network tab (F12 → Network) untuk melihat request/response API
