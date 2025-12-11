# 📁 Database Setup - SiPaMaLi v3.0

Folder ini berisi file SQL untuk setup database sistem SiPaMaLi.

## 📋 Daftar File

### 1. `sipamali_complete_schema.sql`
**Fungsi**: Membuat struktur database lengkap (DDL - Data Definition Language)

**Berisi**:
- ✅ DROP & CREATE Database
- ✅ Definisi 9 Tabel Utama:
  - `users` - User Management (4 roles)
  - `reports` - Laporan Warga
  - `report_workflow` - Tracking Alur Laporan
  - `audit_logs` - Logging untuk Super Admin
  - `report_assignments` - Assignment Tracking
  - `report_progress` - Progress Tracking
  - `report_comments` - Komentar Laporan
  - `notifications` - Notifikasi User
  - `activity_logs` - General Activity Log
- ✅ Views untuk Analytics
- ✅ Stored Procedures
- ✅ Triggers
- ✅ Indexes untuk Optimization

**Tidak Berisi**: Data seed/dummy

---

### 2. `sample_users_and_data.sql`
**Fungsi**: Mengisi data seed untuk testing & demo (DML - Data Manipulation Language)

**Berisi**:
- ✅ 20 Sample Users:
  - 2 Super Admin
  - 3 Admin
  - 5 Petugas (berbagai bidang)
  - 10 Warga
- ✅ 21 Sample Reports (berbagai kategori & status)
- ✅ Report Progress & Comments
- ✅ Notifications
- ✅ Audit Logs

**Password untuk semua user**: `password123`

**Tidak Berisi**: Struktur tabel/schema

---

## 🚀 Cara Instalasi

### Method 1: Command Line (Recommended)

```bash
# 1. Login ke MySQL
mysql -u root -p

# 2. Jalankan schema (membuat database & tabel)
source /path/to/sipamali_complete_schema.sql

# 3. Jalankan seed data (isi data dummy)
source /path/to/sample_users_and_data.sql

# 4. Selesai!
exit
```

### Method 2: Satu Baris

```bash
mysql -u root -p < sipamali_complete_schema.sql && mysql -u root -p < sample_users_and_data.sql
```

### Method 3: phpMyAdmin

1. Buka phpMyAdmin
2. Klik tab **SQL**
3. Upload atau paste isi `sipamali_complete_schema.sql`
4. Klik **Go**
5. Ulangi untuk `sample_users_and_data.sql`

---

## 📊 Sample Login Credentials

Semua user menggunakan password: **`password123`**

| Role | Username | Email |
|------|----------|-------|
| **Super Admin** | `superadmin` | superadmin@sipamali.id |
| **Super Admin** | `super_budi` | super.budi@sipamali.id |
| **Admin** | `admin` | admin@sipamali.id |
| **Admin** | `admin_siti` | siti.admin@sipamali.id |
| **Admin** | `admin_andi` | andi.admin@sipamali.id |
| **Petugas** | `petugas_sampah` | petugas.sampah@sipamali.id |
| **Petugas** | `petugas_jalan` | petugas.jalan@sipamali.id |
| **Petugas** | `petugas_drainase` | petugas.drainase@sipamali.id |
| **Petugas** | `petugas_polusi` | petugas.polusi@sipamali.id |
| **Petugas** | `petugas_taman` | petugas.taman@sipamali.id |
| **Warga** | `warga_ahmad` | ahmad.yani@gmail.com |
| **Warga** | `warga_sari` | sari.dewi@gmail.com |
| **Warga** | ... | ... (10 users warga) |

---

## 🔄 Reset Database

Jika ingin reset database ke kondisi awal:

```bash
# Jalankan ulang schema (akan DROP database)
mysql -u root -p < sipamali_complete_schema.sql

# Isi ulang dengan seed data
mysql -u root -p < sample_users_and_data.sql
```

---

## ⚠️ Troubleshooting

### Error: "Database exists"
```sql
-- Hapus manual dulu
DROP DATABASE IF EXISTS sipamali_db;
```

### Error: "Foreign key constraint fails"
```sql
-- Matikan foreign key check sementara
SET FOREIGN_KEY_CHECKS = 0;
-- Jalankan SQL Anda
SET FOREIGN_KEY_CHECKS = 1;
```

### Error: "Access denied"
```bash
# Pastikan user MySQL punya privileges
GRANT ALL PRIVILEGES ON sipamali_db.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📝 Catatan Penting

1. **Urutan Eksekusi**: HARUS menjalankan `sipamali_complete_schema.sql` terlebih dahulu
2. **Development Only**: Data seed ini untuk development/testing, JANGAN untuk production
3. **Password Hash**: Password `password123` sudah di-hash dengan bcrypt
4. **Foreign Keys**: Ada relasi antar tabel, jangan hapus data sembarangan

---

## 🔍 Database Statistics

Setelah install, database akan berisi:

| Item | Jumlah |
|------|--------|
| Total Users | 20 |
| Super Admin | 2 |
| Admin | 3 |
| Petugas | 5 |
| Warga | 10 |
| Total Reports | 21 |
| Completed Reports | 6 |
| Processing Reports | 6 |
| Pending Reports | 9 |

---

## 📞 Support

Jika ada masalah, hubungi:
- **Kelompok**: 22
- **Mata Kuliah**: Praktikum Pemrograman Web 2025

---

**Last Updated**: December 2025
