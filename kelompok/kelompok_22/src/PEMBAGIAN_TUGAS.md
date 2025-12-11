# 📋 Pembagian Tugas Kelompok 22

## 👥 Anggota Tim
1. **Muhammad Faisal (Kuokana)** - Koordinator
2. **Delon (deeloon22)**
3. **Nadjwa Aprisda (najwaaprisda)**
4. **Gerald (geraldilyas)**

---

## 🎯 MODUL 1: User Management & Authentication System
**👤 PIC: Muhammad Faisal (Kuokana)**

### Fitur yang Dikembangkan:
1. **Registrasi Akun Warga**
   - Form registrasi (username, email, password, nama lengkap, no. HP)
   - Validasi input (email unique, password min 8 karakter)
   - Hash password dengan `password_hash()`
   - Email verification (opsional: token via session)
   
2. **Login Multi-Role**
   - Login untuk 3 role: Admin, Petugas, Warga
   - Session management dengan role-based access
   - Remember me functionality
   - Logout dengan clear session
   
3. **Profile Management**
   - Halaman edit profile user
   - Upload foto profile/avatar
   - Change password dengan validasi old password
   - View profile dengan statistik laporan user

### File yang Dibuat/Dimodifikasi:
```
├── register.php                    # Form registrasi (NEW)
├── profile.php                     # Halaman profile user (NEW)
├── edit-profile.php                # Edit profile (NEW)
├── includes/
│   └── auth.php                    # Update: tambah register & multi-role
├── config/
│   └── database.sql                # Update: tabel users dengan role
└── js/
    └── register-validation.js      # Validasi form JS (NEW)
```

### Database Updates:
```sql
-- Tambah tabel users dengan role
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'petugas', 'warga') DEFAULT 'warga',
    avatar VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update tabel reports: tambah user_id
ALTER TABLE reports ADD user_id INT AFTER report_id;
ALTER TABLE reports ADD FOREIGN KEY (user_id) REFERENCES users(id);
```

### Estimasi Waktu: **3-4 hari**

---

## 🎯 MODUL 2: Report Tracking & Assignment System
**👤 PIC: Delon (deeloon22)**

### Fitur yang Dikembangkan:
1. **Dashboard Petugas Lapangan**
   - Halaman khusus untuk role "petugas"
   - List laporan yang di-assign ke petugas
   - Update progress laporan (status: Diproses → Selesai)
   - Upload foto before/after perbaikan
   
2. **Assignment System (Admin)**
   - Admin bisa assign laporan ke petugas tertentu
   - Dropdown pilih petugas dari database
   - Riwayat assignment (siapa assign, kapan)
   - Notifikasi ke petugas saat dapat tugas baru
   
3. **Progress Tracking**
   - Timeline progress laporan
   - Foto dokumentasi before/after
   - Catatan petugas di setiap update
   - Estimasi waktu penyelesaian

### File yang Dibuat/Dimodifikasi:
```
├── petugas.php                     # Dashboard petugas (NEW)
├── assign-report.php               # Admin assign laporan (NEW)
├── update-progress.php             # Petugas update progress (NEW)
├── api.php                         # Update: endpoint assign & progress
└── includes/
    └── notification.php            # Helper notifikasi (NEW)
```

### Database Updates:
```sql
-- Tabel assignment
CREATE TABLE report_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_to INT NOT NULL,
    notes TEXT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
);

-- Tabel progress tracking
CREATE TABLE report_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    notes TEXT,
    image_before VARCHAR(255),
    image_after VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Update reports: tambah assigned_to
ALTER TABLE reports ADD assigned_to INT AFTER status;
ALTER TABLE reports ADD priority ENUM('Rendah','Sedang','Tinggi','Urgent') DEFAULT 'Sedang';
```

### Estimasi Waktu: **3-4 hari**

---

## 🎯 MODUL 3: Comment & Notification System
**👤 PIC: Nadjwa Aprisda (najwaaprisda)**

### Fitur yang Dikembangkan:
1. **Sistem Komentar**
   - User bisa komen di laporan mereka sendiri
   - Admin/petugas bisa reply komentar
   - Tampilkan thread komentar per laporan
   - Real-time update komentar (AJAX)
   
2. **Notification Center**
   - Notifikasi saat status laporan berubah
   - Notifikasi saat ada komentar baru
   - Notifikasi saat laporan di-assign (untuk petugas)
   - Badge counter notifikasi belum dibaca
   
3. **Email Notification (Opsional)**
   - Kirim email saat laporan selesai
   - Template email HTML yang menarik
   - Konfigurasi SMTP (gunakan PHPMailer atau mail())

### File yang Dibuat/Dimodifikasi:
```
├── notifications.php               # Halaman notifikasi (NEW)
├── api-comments.php                # API CRUD komentar (NEW)
├── api-notifications.php           # API notifikasi (NEW)
├── includes/
│   ├── email.php                   # Email helper (NEW)
│   └── notification-helper.php    # Create notification (NEW)
└── js/
    └── realtime-comments.js        # AJAX comments (NEW)
```

### Database Updates:
```sql
-- Tabel komentar
CREATE TABLE report_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    is_internal TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (report_id) REFERENCES reports(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabel notifikasi
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_id INT,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (report_id) REFERENCES reports(id)
);
```

### Estimasi Waktu: **3-4 hari**

---

## 🎯 MODUL 4: Analytics & Reporting Dashboard
**👤 PIC: Gerald (geraldilyas)**

### Fitur yang Dikembangkan:
1. **Dashboard Analytics (Admin)**
   - Chart statistik laporan (Chart.js atau Google Charts)
   - Grafik batang: Laporan per kategori
   - Pie chart: Status laporan
   - Line chart: Tren laporan per bulan
   - Heat map lokasi (opsional: Google Maps API)
   
2. **Laporan & Export**
   - Filter laporan (tanggal, status, kategori, petugas)
   - Export laporan ke PDF (library TCPDF/FPDF)
   - Export laporan ke Excel (CSV/XLS)
   - Print preview dengan styling
   
3. **Public Statistics Page**
   - Halaman statistik untuk publik (tanpa login)
   - Transparansi data: total laporan selesai
   - Leaderboard petugas terbaik (most resolved)
   - Timeline laporan terbaru

### File yang Dibuat/Dimodifikasi:
```
├── dashboard-analytics.php         # Dashboard chart (NEW)
├── statistics.php                  # Public stats page (NEW)
├── export-pdf.php                  # Export to PDF (NEW)
├── export-excel.php                # Export to Excel (NEW)
├── api-stats.php                   # API untuk chart data (NEW)
├── js/
│   └── charts.js                   # Chart.js initialization (NEW)
└── css/
    └── print.css                   # Print stylesheet (NEW)
```

### Database Updates:
```sql
-- View untuk analytics
CREATE VIEW analytics_summary AS
SELECT 
    DATE(created_at) as date,
    category,
    status,
    COUNT(*) as count
FROM reports
GROUP BY DATE(created_at), category, status;

-- View leaderboard petugas
CREATE VIEW petugas_leaderboard AS
SELECT 
    u.full_name,
    COUNT(r.id) as total_assigned,
    SUM(CASE WHEN r.status = 'Selesai' THEN 1 ELSE 0 END) as total_resolved,
    AVG(TIMESTAMPDIFF(HOUR, r.created_at, r.updated_at)) as avg_resolve_time
FROM users u
LEFT JOIN reports r ON u.id = r.assigned_to
WHERE u.role = 'petugas'
GROUP BY u.id;
```

### Library yang Digunakan:
- **Chart.js** (CDN): Untuk grafik
- **TCPDF** atau **FPDF**: Untuk export PDF
- **PhpSpreadsheet** (opsional): Untuk Excel

### Estimasi Wakti: **3-4 hari**

---

## 📊 Timeline Pengerjaan

```
Week 1: Setup & Database Design
├── Hari 1-2: Setup repo, database schema, roles
└── Hari 3: Testing database & koordinasi

Week 2: Development Sprint 1
├── Faisal: User Management
├── Delon: Assignment System
├── Nadjwa: Comment System
└── Gerald: Analytics Basic

Week 3: Development Sprint 2
├── Integration testing
├── Bug fixing
└── UI/UX polish

Week 4: Final Testing & Documentation
├── Hari 1-2: Testing lengkap
├── Hari 3: Update README & docs
└── Hari 4: Demo preparation
```

---

## 🔗 Integrasi Antar Modul

### Flow Sistem Lengkap:
```
1. WARGA (Role: warga)
   → Register (Modul 1)
   → Login (Modul 1)
   → Buat Laporan (Existing)
   → Lihat Notifikasi (Modul 3)
   → Komen di Laporan (Modul 3)
   → Lihat Progress (Modul 2)

2. PETUGAS (Role: petugas)
   → Login (Modul 1)
   → Lihat Laporan Assigned (Modul 2)
   → Update Progress (Modul 2)
   → Upload Foto Before/After (Modul 2)
   → Reply Komentar (Modul 3)

3. ADMIN (Role: admin)
   → Login (Modul 1)
   → Assign Laporan ke Petugas (Modul 2)
   → Lihat Analytics (Modul 4)
   → Export Laporan (Modul 4)
   → Manage Users (Modul 1)
```

---

## 📝 Aturan Kolaborasi

### Git Workflow:
1. **Branch Naming:**
   - `feature/user-management` (Faisal)
   - `feature/assignment-system` (Delon)
   - `feature/comment-notification` (Nadjwa)
   - `feature/analytics-export` (Gerald)

2. **Commit Message Format:**
   ```
   feat(modul): deskripsi fitur
   fix(modul): perbaikan bug
   docs: update dokumentasi
   style: perubahan UI/CSS
   ```

3. **Pull Request:**
   - Setiap anggota buat PR ke branch `development`
   - Minimal 1 orang review sebelum merge
   - Testing lokal dulu sebelum push

### Komunikasi:
- **Daily Standup:** Update progress harian di grup
- **Code Review:** Saling review kode di PR
- **Help Needed:** Gunakan label `help-wanted` di issue

---

## ✅ Definition of Done

Setiap modul dianggap selesai jika:
- [ ] Kode berjalan tanpa error
- [ ] Database schema terupdate
- [ ] API/endpoint sudah di-test
- [ ] UI responsive (mobile & desktop)
- [ ] Sudah di-review minimal 1 orang
- [ ] Dokumentasi di README.md diupdate
- [ ] Commit sudah di-push ke branch masing-masing

---

## 🎓 Bonus Features (Jika Ada Waktu)

1. **Real-time Notification** (WebSocket/Pusher)
2. **Google Maps Integration** (Peta lokasi laporan)
3. **QR Code** untuk laporan (scan = lihat detail)
4. **Dark Mode** toggle
5. **PWA** (Progressive Web App)
6. **Multi-language** (Indonesia & English)

---

**Good Luck, Team! 🚀**

Koordinator: Muhammad Faisal (Kuokana)
