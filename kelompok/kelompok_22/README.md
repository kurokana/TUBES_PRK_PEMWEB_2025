# SiPaMaLi - Sistem Pelaporan & Pemantauan Masalah Lingkungan

## 👥 Daftar Anggota Kelompok 22
1. **Muhammad Faisal** - [@Kurokana](https://github.com/Kurokana)
2. **Delon** - [@deeloon22](https://github.com/deeloon22)
3. **Najwa Aprisda** - [@najwaaprisda](https://github.com/najwaaprisda)
4. **Gerald** - [@geraldilyas](https://github.com/geraldilyas)

---

## 📖 Tentang Project

**SiPaMaLi** (Sistem Pelaporan & Pemantauan Masalah Lingkungan) adalah platform web berbasis partisipasi publik yang memungkinkan warga untuk melaporkan berbagai masalah lingkungan di sekitar mereka seperti:

- 🗑️ Sampah berserakan
- 💧 Drainase tersumbat
- 🛣️ Jalan rusak
- 🏭 Polusi udara/air
- 🌳 Kerusakan taman
- Dan masalah lingkungan lainnya

### ✨ Fitur Utama

#### Untuk Warga/Pelapor:
- Membuat laporan masalah lingkungan dengan foto dan lokasi
- Melihat riwayat laporan yang telah dibuat
- Melacak status penanganan laporan (Menunggu, Diproses, Selesai)
- Mengelola profil pengguna

#### Untuk Petugas:
- Menerima dan mengelola penugasan laporan
- Mengupdate status dan progress penanganan
- Menambahkan komentar/update pada laporan
- Menandai laporan sebagai selesai

#### Untuk Admin:
- Mengelola semua laporan dari warga
- Menugaskan laporan ke petugas yang sesuai
- Monitoring dan statistik laporan
- Mengelola pengguna dan petugas

#### Untuk Super Admin:
- Semua akses admin
- Mengelola user (create, update, delete, role change)
- Melihat audit logs sistem
- Mengelola pengaturan sistem

---

## 🚀 Cara Menjalankan Aplikasi

### Prasyarat
Pastikan sistem Anda telah terinstall:
- **PHP** >= 8.0
- **MySQL** >= 5.7 atau **MariaDB** >= 10.3
- **Web Browser** (Chrome, Firefox, Edge, dll)

### Langkah 1: Clone Repository
```bash
git clone <repository-url>
cd kelompok_22/src
```

### Langkah 2: Setup Database
1. Buat database MySQL:
```sql
CREATE DATABASE sipamali_db;
CREATE USER 'sipamali_user'@'localhost' IDENTIFIED BY 'sipamali_password';
GRANT ALL PRIVILEGES ON sipamali_db.* TO 'sipamali_user'@'localhost';
FLUSH PRIVILEGES;
```

2. Import schema database:
```bash
mysql -u sipamali_user -p sipamali_db < src/database/sipamali_complete_schema.sql
```

3. Import sample data (opsional):
```bash
mysql -u sipamali_user -p sipamali_db < src/database/sample_users_and_data.sql
```

### Langkah 3: Konfigurasi Database (Opsional)
Jika menggunakan kredensial database berbeda, edit file `src/backend/utils/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'sipamali_user');
define('DB_PASS', 'sipamali_password');
define('DB_NAME', 'sipamali_db');
```

### Langkah 4: Jalankan Aplikasi
1. Buka terminal di direktori `kelompok_22`
2. Jalankan PHP built-in server:
```bash
php -S localhost:8000 index.php
```

3. Buka browser dan akses:
```
http://localhost:8000
```

### Langkah 5: Login ke Sistem
Gunakan kredensial berikut untuk testing (jika menggunakan sample data):

#### Super Admin:
- Username: `superadmin`
- Password: `password123`

#### Admin:
- Username: `admin`
- Password: `password123`

#### Petugas:
- Username: `petugas_sampah`
- Password: `password123`

#### Warga/Pelapor:
- Username: `warga_ahmad`
- Password: `password123`

> **Note:** Semua password default adalah `password123` untuk kemudahan testing.

---

## 📁 Struktur Project
```
kelompok_22/
├── README.md                 # Dokumentasi project
├── index.php                 # Entry point aplikasi
└── src/                      # Source code aplikasi
    ├── backend/              # Backend logic
    │   ├── controllers/      # Controller (admin, petugas, super_admin, api)
    │   ├── middleware/       # Middleware (authentication)
    │   ├── models/           # Database models
    │   └── utils/            # Utility functions & config
    ├── frontend/             # Frontend files
    │   ├── assets/           # CSS, JS, Images
    │   └── pages/            # HTML/PHP pages
    ├── database/             # SQL schema & sample data
    ├── docs/                 # Documentation
    └── uploads/              # User uploaded files
```

---

## 🛠️ Teknologi yang Digunakan
- **Backend:** PHP 8.x (Native)
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **UI Framework:** Tailwind CSS
- **Icons:** Font Awesome
- **Server:** PHP Built-in Server (Development)

---

## 📝 Catatan
- Aplikasi ini menggunakan PHP built-in server untuk development
- Untuk production, disarankan menggunakan Apache/Nginx
- Pastikan folder `src/uploads/` memiliki permission write untuk menyimpan file upload
- Database credentials dapat diubah di `src/backend/utils/config.php`

---

## 📄 Lisensi
Project ini dibuat untuk keperluan Tugas Besar Praktikum Pemrograman Web 2025.

---


#### Dokumentasi Website 
  1. Landing Page 1
     <img width="1911" height="997" alt="landingpage1" src="https://github.com/user-attachments/assets/792b37ad-c118-419a-8810-cd210b3ca176" />


  2. Landing Page 2
    <img width="1919" height="926" alt="LandingPage2" src="https://github.com/user-attachments/assets/82aff1a6-994c-4e4b-9fea-237e3e5b94bb" />


  3. Login
     <img width="1919" height="998" alt="login" src="https://github.com/user-attachments/assets/c4d88d12-54ec-46d1-853f-a072e384ede4" />


  4. Daftar
     <img width="1919" height="988" alt="daftar" src="https://github.com/user-attachments/assets/04f46958-bd38-49c4-a8c1-750f3ca903c1" />


  5. Lupa Password
      <img width="1919" height="997" alt="lupaPassword" src="https://github.com/user-attachments/assets/b8c46795-0911-49a8-b868-dccdc825b49c" />


  6. Dashboard Warga 1
      <img width="1919" height="968" alt="dashboardUser1" src="https://github.com/user-attachments/assets/dd7ab456-0c1b-43ff-bbae-4d11a7945e52" />


  7. Dashboard Warga 2
      <img width="1919" height="995" alt="dashboardUser2" src="https://github.com/user-attachments/assets/9c767824-5b35-4aa3-8792-b9b67c6e59ce" />


  8. Pantau Laporan Warga
      <img width="1919" height="965" alt="pantauLaporanUser" src="https://github.com/user-attachments/assets/5ba618f2-2fdf-4747-9749-ea4e77d776a6" />


  9. Edit Profil Warga
      <img width="1919" height="995" alt="editProfilUser" src="https://github.com/user-attachments/assets/5d136772-b638-4ede-8ff3-252a9b0a3775" />


  10. Dashboard Petugas
      <img width="1919" height="995" alt="dashboardPetugas" src="https://github.com/user-attachments/assets/707c13d0-1e03-468f-92c0-17e701df6dde" />


  11. Detail Tugas Petugas
      <img width="1919" height="994" alt="detailTugasPetugas" src="https://github.com/user-attachments/assets/8eee1aa1-4095-407d-b27a-5fca24d39a13" />


  12. Dashboard Admin
      <img width="1919" height="992" alt="dashboardAdmin" src="https://github.com/user-attachments/assets/f229d958-bd99-4471-b56c-cc514590549d" />


  13. Laporan Warga Admin
      <img width="1919" height="997" alt="detailLaporanAdmin" src="https://github.com/user-attachments/assets/71794339-e8d5-424c-a2e1-2a0c2e2895af" />


  14. Validasi Petugas Admin
      <img width="1919" height="995" alt="validasiPetugasAdmin" src="https://github.com/user-attachments/assets/3ee7954d-8d4e-421d-b59d-98102f19a8d1" />


  15. Statistik Laporan Admin
      <img width="1919" height="988" alt="statistikLaporanAdmin" src="https://github.com/user-attachments/assets/0769ec20-8079-4949-adcc-6b1e65a95150" />


  16. Riwayat Laporan Admin
      <img width="1919" height="995" alt="riwayatLaporanAdmin" src="https://github.com/user-attachments/assets/f0a45f29-4dbd-4575-8896-31b01104e8e9" />


  17. Management User Admin
      <img width="1919" height="992" alt="managementUserAdmin" src="https://github.com/user-attachments/assets/536b8c25-2220-49a1-a5c5-42af3a18f2bb" />


  18. Pengaturan Akun Admin
      <img width="1902" height="985" alt="pengaturanAkunAdmin" src="https://github.com/user-attachments/assets/c8ba8c4d-c2e0-4039-b923-8cb73f2a081b" />


  19. Dashboard Super Admin
      <img width="1919" height="997" alt="dashboardSuperAdmin" src="https://github.com/user-attachments/assets/2af467ee-0505-41f6-a983-cb6af036b1d5" />


  20. All report Super Admin
      <img width="1919" height="802" alt="allReportSuperAdmin" src="https://github.com/user-attachments/assets/fc216f15-46fd-4d03-aa60-52cb9d3786d5" />


  21. Audit Log Super Admin
      <img width="1919" height="807" alt="auditLogSuperAdmin" src="https://github.com/user-attachments/assets/a72606f3-8bd5-4147-a68d-060f510f3f0e" />


  22. Edit User Role Super Admin
      <img width="1919" height="808" alt="editUserRoleSuperAdmin" src="https://github.com/user-attachments/assets/031ce369-8166-4856-8616-ca2ffd1faa88" />



**Kelompok 22** - Praktikum Pemrograman Web 2025
