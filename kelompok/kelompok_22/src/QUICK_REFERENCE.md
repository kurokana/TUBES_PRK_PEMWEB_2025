# 🎴 Quick Reference Card - SiPaMaLi

## 🔑 Login Quick Access

**Password semua user: `password123`**

```
┌─────────────────────────────────────────────────────────────┐
│  SUPER ADMIN                                                │
├─────────────────────────────────────────────────────────────┤
│  Username: superadmin                                       │
│  Dashboard: /backend/controllers/super_admin.php            │
│  • View all reports (read-only)                            │
│  • Audit logs viewer                                       │
│  • User management (change roles)                          │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  ADMIN                                                      │
├─────────────────────────────────────────────────────────────┤
│  Username: admin                                            │
│  Dashboard: /backend/controllers/admin.php                  │
│  • Terima laporan dari warga                               │
│  • Forward ke petugas                                      │
│  • Validasi & finalisasi                                   │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  PETUGAS                                                    │
├─────────────────────────────────────────────────────────────┤
│  • petugas_sampah    → Joko Santoso                        │
│  • petugas_jalan     → Bambang Susilo                      │
│  • petugas_drainase  → Agus Hermawan                       │
│  • petugas_polusi    → Dwi Wahyuni                         │
│  • petugas_taman     → Rina Wijaya                         │
│  Dashboard: /backend/controllers/petugas.php                │
└─────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│  WARGA                                                      │
├─────────────────────────────────────────────────────────────┤
│  Username: warga_ahmad (atau warga_sari, warga_rizki...)   │
│  Page: /frontend/pages/index.html                          │
│  • Submit laporan                                          │
│  • Lihat riwayat                                           │
│  • Terima notifikasi                                       │
└─────────────────────────────────────────────────────────────┘
```

## 📊 Data Summary

```
👥 USERS: 20 total
   ├─ Super Admin: 2
   ├─ Admin: 3
   ├─ Petugas: 5
   └─ Warga: 10

📋 REPORTS: 21 total
   ├─ Selesai: 4
   ├─ Diproses: 5
   ├─ Diteruskan: 4
   └─ Menunggu: 8

📁 CATEGORIES:
   ├─ Sampah: 5
   ├─ Jalan: 5
   ├─ Drainase: 4
   ├─ Polusi: 4
   └─ Taman: 3
```

## 🎯 Testing Checklist

### ✅ Role Testing
- [ ] Login sebagai Super Admin → View all reports
- [ ] Login sebagai Admin → Assign laporan ke petugas
- [ ] Login sebagai Petugas → Update progress laporan
- [ ] Login sebagai Warga → Submit laporan baru

### ✅ Feature Testing
- [ ] Submit new report (warga)
- [ ] Forward report to petugas (admin)
- [ ] Update progress (petugas)
- [ ] Add comments
- [ ] View notifications
- [ ] Check audit logs (super admin)
- [ ] Change user role (super admin)

### ✅ Workflow Testing
- [ ] Warga → Submit → Admin
- [ ] Admin → Forward → Petugas
- [ ] Petugas → Complete → Admin
- [ ] Admin → Finalize → Warga
- [ ] Super Admin → View all

## 🗃️ Database Import

```bash
# Fresh install (hapus data lama)
cd database_new
sudo mysql -u root sipamali_db < sample_users_and_data.sql

# Atau import complete schema + data
sudo mysql -u root < sipamali_complete_schema.sql
sudo mysql -u root sipamali_db < sample_users_and_data.sql
```

## 🔧 Troubleshooting

### Issue: Cannot login
```bash
# Check database connection
php -r "echo 'DB: ' . (new mysqli('localhost', 'sipamali_user', 'sipamali_password', 'sipamali_db')->connect_error ? 'FAIL' : 'OK');"

# Reset password
mysql -u root -e "UPDATE users SET password_hash='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username='admin';"
```

### Issue: Path not found
```bash
# Check .htaccess
cat .htaccess

# Update paths
python3 update_paths.py
```

## 📞 Contact

**Repository:** TUBES_PRK_PEMWEB_2025  
**Team:** Kelompok 22  
**Branch:** Fix/Direktori

---

**Quick Tip:** Gunakan `superadmin` untuk eksplorasi penuh sistem! 🚀
