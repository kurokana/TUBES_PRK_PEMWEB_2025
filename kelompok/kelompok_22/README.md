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
Jika menggunakan Linux dan kredensial database berbeda, edit file `src/backend/utils/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'dev');
define('DB_PASS', 'DevPass123!');
define('DB_NAME', 'pamali2');
```

Jika menggunakan Windows
```php 
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pamali2');
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

**Kelompok 22** - Praktikum Pemrograman Web 2025
