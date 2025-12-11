# SiPaMaLi - Struktur Direktori v3.0

## 📁 Struktur Folder

```
src/
├── index.php                          # Entry point utama
│
├── frontend/                          # Frontend Files (HTML, CSS, JS)
│   ├── pages/                         # Halaman-halaman HTML/PHP
│   │   ├── index.html                 # Landing page untuk warga
│   │   ├── login.php                  # Halaman login
│   │   ├── registrasi.php             # Halaman registrasi
│   │   ├── pelapor.php                # Form laporan
│   │   └── riwayat.php                # Riwayat laporan
│   │
│   ├── assets/                        # Asset statis
│   │   ├── css/                       # Stylesheets
│   │   │   └── styles.css
│   │   ├── js/                        # JavaScript files
│   │   │   └── app.js
│   │   └── images/                    # Images/icons
│   │
│   └── uploads/                       # User uploaded files
│
├── backend/                           # Backend Files (PHP Logic)
│   ├── controllers/                   # Controllers (Page handlers)
│   │   ├── admin.php                  # Admin dashboard
│   │   ├── petugas.php                # Petugas dashboard
│   │   ├── super_admin.php            # Super Admin dashboard
│   │   ├── api.php                    # REST API endpoints
│   │   └── logout.php                 # Logout handler
│   │
│   ├── middleware/                    # Middleware functions
│   │   └── auth.php                   # Authentication & authorization
│   │
│   ├── utils/                         # Utility functions
│   │   ├── config.php                 # Database config & helpers
│   │   └── admin_utils.php            # Admin utility functions
│   │
│   ├── models/                        # Data models (optional, untuk OOP)
│   │   ├── User.php                   # User model
│   │   ├── Report.php                 # Report model
│   │   └── AuditLog.php               # Audit log model
│   │
│   └── config/                        # Configuration files
│       └── database.sql               # Legacy database file
│
├── database_new/                      # Database Schema
│   └── sipamali_complete_schema.sql   # Complete schema v3.0
│
└── docs/                              # Documentation
    ├── ROLE_SYSTEM_DOCS.md            # Role system documentation
    ├── PEMBAGIAN_TUGAS.md             # Task division
    └── TASK_TRACKER.md                # Task tracker
```

## 🚀 Setup & Installation

### 1. Database Setup

```bash
# Import database schema
mysql -u root -p < database_new/sipamali_complete_schema.sql

# Atau dengan sudo
sudo mysql -u root < database_new/sipamali_complete_schema.sql
```

### 2. Konfigurasi Database

Edit file `backend/utils/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'sipamali_user');
define('DB_PASS', 'sipamali_password');
define('DB_NAME', 'sipamali_db');
```

### 3. File Permissions

```bash
# Set permissions untuk uploads directory
chmod 755 frontend/uploads
```

### 4. Start Development Server

```bash
# Dari root directory src/
php -S localhost:8000

# Atau gunakan XAMPP/LAMP/WAMP
```

## 📌 Path Conventions

### Frontend Pages
- **Landing Page**: `frontend/pages/index.html`
- **Login**: `frontend/pages/login.php`
- **Register**: `frontend/pages/registrasi.php`

### Backend Controllers
- **Admin Dashboard**: `backend/controllers/admin.php`
- **Petugas Dashboard**: `backend/controllers/petugas.php`
- **Super Admin Dashboard**: `backend/controllers/super_admin.php`
- **API**: `backend/controllers/api.php`

### Assets
- **CSS**: `frontend/assets/css/`
- **JavaScript**: `frontend/assets/js/`
- **Images**: `frontend/assets/images/`
- **Uploads**: `frontend/uploads/`

## 🔧 Updating File Paths

Karena struktur berubah, path dalam file perlu disesuaikan:

### Dari Frontend ke Backend:
```php
// OLD
require_once 'includes/auth.php';

// NEW
require_once '../../backend/middleware/auth.php';
require_once '../../backend/utils/config.php';
```

### Dari Backend Controller:
```php
// NEW
require_once '../middleware/auth.php';
require_once '../utils/config.php';
require_once '../utils/admin_utils.php';
```

### Asset Links (CSS/JS):
```html
<!-- OLD -->
<link rel="stylesheet" href="css/styles.css">

<!-- NEW -->
<link rel="stylesheet" href="../assets/css/styles.css">
```

## 🔐 Default Users

| Username | Password | Role | Dashboard |
|----------|----------|------|-----------|
| superadmin | superadmin123 | super_admin | `/backend/controllers/super_admin.php` |
| admin | admin123 | admin | `/backend/controllers/admin.php` |
| petugas1 | petugas123 | petugas | `/backend/controllers/petugas.php` |
| warga1 | warga123 | warga | `/frontend/pages/index.html` |

## 📊 Database Schema

Schema lengkap ada di: `database_new/sipamali_complete_schema.sql`

**Features:**
- ✅ 4 Role System (warga, petugas, admin, super_admin)
- ✅ Audit Logs untuk Super Admin
- ✅ Report Workflow Tracking
- ✅ Notifications System
- ✅ Views untuk Analytics
- ✅ Stored Procedures & Triggers

## 🎯 Workflow

```
Warga (frontend/pages/index.html)
    ↓ Submit Laporan
Admin (backend/controllers/admin.php)
    ↓ Review & Forward ke Petugas
Petugas (backend/controllers/petugas.php)
    ↓ Selesaikan & Report Back
Admin (backend/controllers/admin.php)
    ↓ Validate & Finalize
Warga (Notifikasi)
    ↓
Super Admin (backend/controllers/super_admin.php)
    - View All (Read-Only)
    - Audit Logs
    - User Management
```

## 📝 Notes

- **Frontend** berisi semua file yang diakses user (HTML, CSS, JS, uploads)
- **Backend** berisi semua logika PHP (controllers, middleware, utils)
- **Separation of Concerns**: Frontend tidak langsung akses database, semua melalui backend
- **Security**: File sensitif (config, auth) ada di backend folder

## 🔄 Migration Checklist

- [x] Pindahkan HTML pages ke `frontend/pages/`
- [x] Pindahkan CSS ke `frontend/assets/css/`
- [x] Pindahkan JS ke `frontend/assets/js/`
- [x] Pindahkan PHP controllers ke `backend/controllers/`
- [x] Reorganisasi utils dan middleware
- [x] Gabungkan database schema
- [ ] Update semua path references
- [ ] Test semua halaman
- [ ] Update .htaccess jika perlu

## 📖 Dokumentasi

Lihat folder `docs/` untuk dokumentasi lengkap:
- `ROLE_SYSTEM_DOCS.md` - Dokumentasi sistem 4 role
- `PEMBAGIAN_TUGAS.md` - Pembagian tugas kelompok
- `TASK_TRACKER.md` - Tracking progress

---

**SiPaMaLi v3.0** - Sistem Pelaporan & Pemantauan Masalah Lingkungan  
Kelompok 22 - Praktikum Pemrograman Web 2025
