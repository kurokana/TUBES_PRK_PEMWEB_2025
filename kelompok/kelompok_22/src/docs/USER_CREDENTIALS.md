# SiPaMaLi - User Credentials & Data Overview

## 🔐 Login Credentials

**Password untuk semua user: `password123`**

### Super Admin (2 users)
| Username | Email | Nama Lengkap | Phone |
|----------|-------|--------------|-------|
| superadmin | superadmin@sipamali.id | Super Administrator | 081234567890 |
| super_budi | super.budi@sipamali.id | Budi Setiawan | 081234567891 |

**Akses:**
- Dashboard: `/backend/controllers/super_admin.php`
- View all reports (read-only)
- Audit logs viewer
- User management (change role, toggle status)

---

### Admin (3 users)
| Username | Email | Nama Lengkap | Phone |
|----------|-------|--------------|-------|
| admin | admin@sipamali.id | Admin Utama | 082345678901 |
| admin_siti | siti.admin@sipamali.id | Siti Nurhaliza | 082345678902 |
| admin_andi | andi.admin@sipamali.id | Andi Prasetyo | 082345678903 |

**Akses:**
- Dashboard: `/backend/controllers/admin.php`
- Terima laporan dari warga
- Teruskan laporan ke petugas
- Validasi hasil pekerjaan petugas
- Finalisasi laporan

---

### Petugas (5 users - berbeda bidang keahlian)
| Username | Email | Nama Lengkap | Bidang | Phone |
|----------|-------|--------------|--------|-------|
| petugas_sampah | petugas.sampah@sipamali.id | Joko Santoso | Sampah | 083456789012 |
| petugas_jalan | petugas.jalan@sipamali.id | Bambang Susilo | Jalan | 083456789013 |
| petugas_drainase | petugas.drainase@sipamali.id | Agus Hermawan | Drainase | 083456789014 |
| petugas_polusi | petugas.polusi@sipamali.id | Dwi Wahyuni | Polusi | 083456789015 |
| petugas_taman | petugas.taman@sipamali.id | Rina Wijaya | Taman & RTH | 083456789016 |

**Akses:**
- Dashboard: `/backend/controllers/petugas.php`
- Terima tugas dari admin
- Kerjakan di lapangan
- Upload foto bukti
- Kirim laporan penyelesaian ke admin

---

### Warga (10 users - pelapor)
| Username | Email | Nama Lengkap | Phone |
|----------|-------|--------------|-------|
| warga_ahmad | ahmad.yani@gmail.com | Ahmad Yani | 084567890123 |
| warga_sari | sari.dewi@gmail.com | Sari Dewi | 084567890124 |
| warga_rizki | rizki.ramadan@gmail.com | Rizki Ramadan | 084567890125 |
| warga_dewi | dewi.lestari@gmail.com | Dewi Lestari | 084567890126 |
| warga_hadi | hadi.kusuma@gmail.com | Hadi Kusuma | 084567890127 |
| warga_linda | linda.wijaya@gmail.com | Linda Wijaya | 084567890128 |
| warga_eko | eko.prasetyo@gmail.com | Eko Prasetyo | 084567890129 |
| warga_maya | maya.sari@gmail.com | Maya Sari | 084567890130 |
| warga_rudi | rudi.hartono@gmail.com | Rudi Hartono | 084567890131 |
| warga_ani | ani.suryani@gmail.com | Ani Suryani | 084567890132 |

**Akses:**
- Halaman: `/frontend/pages/index.html`
- Submit laporan masalah lingkungan
- Lihat riwayat laporan
- Terima notifikasi update status

---

## 📊 Data Dummy

### Total Laporan: **21 laporan**

#### Status Distribution:
- ✅ **Selesai**: 4 laporan
- 🔄 **Diproses**: 5 laporan  
- 📤 **Diteruskan**: 4 laporan
- ⏳ **Menunggu**: 8 laporan

#### Kategori Laporan:

**1. Sampah (5 laporan)**
- RPT-0001: Selokan tersumbat sampah (Selesai) ✅
- RPT-0002: TPS liar 2 meter (Diproses) 🔄
- RPT-0003: Sampah pasar berserakan (Diteruskan) 📤
- RPT-0004: Container penuh 3 hari (Menunggu) ⏳
- RPT-0005: Pembakaran sampah (Menunggu) ⏳

**2. Jalan (5 laporan)**
- RPT-0006: Lubang diameter 1m (Selesai) ✅
- RPT-0007: Aspal bergelombang 50m (Diproses) 🔄
- RPT-0008: Jalan ambles 2 meter (Diteruskan) 📤
- RPT-0009: Gang rusak parah (Menunggu) ⏳
- RPT-0010: Marka jalan hilang (Menunggu) ⏳

**3. Drainase (4 laporan)**
- RPT-0011: Selokan mampet (Selesai) ✅
- RPT-0012: Genangan 50cm (Diproses) 🔄
- RPT-0013: Gorong-gorong pecah (Diteruskan) 📤
- RPT-0014: Drainase tanpa tutup (Menunggu) ⏳

**4. Polusi (4 laporan)**
- RPT-0015: Asap pabrik hitam (Diproses) 🔄
- RPT-0016: Air sungai tercemar (Diteruskan) 📤
- RPT-0017: Bising mesin pabrik (Menunggu) ⏳
- RPT-0018: Bau TPA radius 1km (Menunggu) ⏳

**5. Taman & RTH (3 laporan)**
- RPT-0019: Taman tidak terawat (Selesai) ✅
- RPT-0020: Playground rusak (Diproses) 🔄
- RPT-0021: Pohon tumbang (Menunggu) ⏳

---

## 📍 Lokasi Laporan (Sample)

Semua laporan memiliki koordinat GPS (latitude & longitude) untuk mapping:
- Jl. Ahmad Yani No. 12
- Jl. Merdeka Raya Km 3
- Pasar Tradisional Baru
- Jl. Gatot Subroto
- Jl. Raya Simpang Lima
- Jl. Sudirman
- Jl. Veteran
- Dan lokasi lainnya...

---

## 💬 Fitur Yang Sudah Ada Data

### 1. Comments (75+ comments)
Setiap laporan memiliki percakapan antara:
- Warga yang melaporkan
- Admin yang menindaklanjuti
- Petugas yang mengerjakan

Contoh:
```
Warga: "Tolong segera ditangani, baunya sudah tidak tertahankan."
Admin: "Mohon maaf atas keterlambatan. Tim sudah dalam perjalanan."
Petugas: "Sedang dalam pengerjaan. Estimasi selesai 2 hari."
```

### 2. Progress Tracking (25+ progress logs)
Setiap laporan yang diproses memiliki timeline:
- Status: Diteruskan → Diproses → Selesai
- Notes dari petugas
- Foto before/after (placeholder)
- Timestamp setiap perubahan

### 3. Notifications (15+ notifikasi)
- Untuk warga: Update status laporan
- Untuk petugas: Tugas baru assigned
- Untuk admin: Laporan urgent masuk

### 4. Audit Logs (10+ logs)
- Super admin login
- Admin assign laporan
- Role changes
- Status updates
- User management activities

---

## 🧪 Testing Scenarios

### Scenario 1: Warga Melaporkan
```
1. Login: warga_ahmad / password123
2. Submit laporan baru
3. Lihat di riwayat
4. Tunggu notifikasi update
```

### Scenario 2: Admin Meneruskan
```
1. Login: admin / password123
2. Lihat laporan pending
3. Assign ke petugas sesuai kategori
4. Tambah catatan
```

### Scenario 3: Petugas Menyelesaikan
```
1. Login: petugas_sampah / password123
2. Lihat tugas yang assigned
3. Update progress
4. Upload foto hasil
5. Tandai selesai
```

### Scenario 4: Super Admin Monitor
```
1. Login: superadmin / password123
2. View all reports (read-only)
3. Check audit logs
4. Manage users (change role)
```

---

## 📈 Dashboard Metrics

Setiap dashboard menampilkan statistik real-time:

**Admin Dashboard:**
- Total Laporan: 21
- Pending: 8
- Diproses: 5
- Selesai: 4

**Petugas Dashboard:**
- Tugas Assigned: Varies per petugas
- Ongoing: 1-2 per petugas
- Completed: 1 per petugas

**Super Admin Dashboard:**
- Total Users: 20
- Total Reports: 21
- Active Users: 20
- Today Audits: Varies

---

## 🚀 Quick Start

1. **Import database:**
   ```bash
   sudo mysql -u root sipamali_db < database_new/sample_users_and_data.sql
   ```

2. **Login ke berbagai role:**
   - Super Admin: `superadmin` / `password123`
   - Admin: `admin` / `password123`
   - Petugas: `petugas_sampah` / `password123`
   - Warga: `warga_ahmad` / `password123`

3. **Eksplorasi fitur:**
   - Submit laporan baru
   - Assign laporan
   - Update progress
   - View audit logs
   - Manage users

---

## 📝 Notes

- Semua password: **password123**
- Data ini untuk **testing & demo** saja
- Untuk production, ganti semua password
- Koordinat GPS adalah sample, bukan lokasi real
- Foto hasil (completion_image) masih placeholder

---

**Data Ready!** 🎉  
Database sudah terisi lengkap dan siap untuk testing website.
