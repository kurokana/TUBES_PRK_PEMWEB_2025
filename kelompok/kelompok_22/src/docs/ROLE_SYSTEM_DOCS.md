# Dokumentasi Sistem 4 Role - SiPaMaLi

## Overview
Sistem SiPaMaLi telah diupgrade untuk mendukung 4 role berbeda dengan workflow yang jelas:

### 1. **Warga (Default Role)**
- Role default saat registrasi
- Dapat mengirim laporan masalah lingkungan
- Setelah login, redirect ke `index.html` (halaman utama)
- Dapat melihat status laporan yang dikirimkan
- Menerima notifikasi update status laporan

### 2. **Petugas**
- Menerima laporan yang diteruskan oleh Admin
- Dashboard: `petugas.php`
- **Tugas:**
  - Melihat laporan yang ditugaskan oleh admin
  - Mengerjakan tugas di lapangan
  - Mengirim laporan balik ke admin setelah tugas selesai
  - Melampirkan foto bukti dan catatan penyelesaian

### 3. **Admin**
- Dashboard: `admin.php`
- **Tugas:**
  - Menerima laporan dari warga
  - Menilai dan meneruskan laporan ke petugas yang berwenang
  - Menerima balasan laporan dari petugas
  - Menyatakan laporan selesai
  - Mengirim konfirmasi penyelesaian ke warga/pelapor

### 4. **Super Admin**
- Dashboard: `super_admin.php`
- **Hak Akses:**
  - **View All Reports** (Read-Only): Dapat melihat semua laporan dari warga, petugas, dan admin tanpa akses edit
  - **Audit Logs**: Dapat membuat dan melihat audit log untuk keperluan audit sistem
  - **User Management**: 
    - Mengubah role user (warga → petugas → admin → super_admin)
    - Aktivasi/deaktivasi user
    - Melihat statistik user dan aktivitas sistem

## Workflow Laporan

```
Warga (Pelapor)
    ↓ [Submit Laporan]
Admin Dashboard
    ↓ [Review & Forward]
Petugas Dashboard
    ↓ [Kerjakan & Report Back]
Admin Dashboard
    ↓ [Validate & Finalize]
Warga (Notifikasi Selesai)
```

### Detail Workflow:

1. **Warga submit laporan** → Status: `Menunggu`
2. **Admin review laporan** → Status: `Diteruskan` → Assign ke Petugas
3. **Petugas terima tugas** → Status: `Diproses`
4. **Petugas selesaikan tugas** → Kirim laporan + foto → Status: `Selesai`
5. **Admin validasi hasil** → Status: `Selesai` → Notifikasi ke Warga
6. **Super Admin** dapat melihat semua proses (read-only)

## Database Schema Updates

### Table: `users`
```sql
role ENUM('warga', 'petugas', 'admin', 'super_admin') DEFAULT 'warga'
```

### Table: `audit_logs` (NEW)
```sql
- id: Primary Key
- user_id: User yang melakukan aksi
- action_type: ENUM('login', 'logout', 'create', 'update', 'delete', 'assign', 'status_change', 'role_change', 'user_management')
- target_type: 'users', 'reports', dll
- target_id: ID record yang terpengaruh
- description: Deskripsi aksi
- old_value, new_value: Nilai lama dan baru
- ip_address, user_agent
- created_at
```

### Table: `report_workflow` (NEW)
```sql
- id: Primary Key
- report_id: ID laporan
- from_user_id: User pengirim
- to_user_id: User penerima
- workflow_type: ENUM('submission', 'assignment', 'completion', 'response', 'finalization')
- status: ENUM('pending', 'in_progress', 'completed', 'returned')
- message: Catatan
- image_path: Foto bukti
- created_at, updated_at
```

### Table: `reports` - New Columns
```sql
- forwarded_to: INT (Petugas yang ditugaskan)
- forwarded_by: INT (Admin yang meneruskan)
- completion_notes: TEXT (Catatan penyelesaian dari petugas)
- completion_image: VARCHAR(255) (Foto hasil pekerjaan)
```

## Authentication & Authorization

### Functions (includes/auth.php):

- `loginUser($username, $password)` - Login dengan audit log
- `registerUser($data)` - Registrasi dengan default role 'warga'
- `logAudit(...)` - Logging aktivitas untuk audit trail
- `requireSuperAdmin()` - Require super admin access
- `requireAdminOrSuperAdmin()` - Require admin atau super admin
- `redirectIfLoggedIn()` - Redirect ke dashboard sesuai role

### User Management (includes/admin_utils.php):

- `changeUserRole($user_id, $new_role, $admin_id)` - Ubah role user (super admin only)
- `toggleUserStatus($user_id, $is_active, $admin_id)` - Aktifkan/nonaktifkan user

## Default Users

Setelah menjalankan `update_4_roles.sql`:

| Username | Password | Role | Email |
|----------|----------|------|-------|
| superadmin | superadmin123 | super_admin | superadmin@sipamali.id |
| admin | admin123 | admin | admin@sipamali.id |
| petugas1 | petugas123 | petugas | petugas1@sipamali.id |
| petugas2 | petugas123 | petugas | petugas2@sipamali.id |
| warga1 | warga123 | warga | warga1@example.com |

## Security Notes

1. **Hanya Super Admin** yang dapat mengubah role user
2. **Default role** saat registrasi adalah 'warga'
3. Semua perubahan penting dicatat dalam **audit_logs**
4. Petugas hanya dapat melihat laporan yang ditugaskan ke mereka
5. Admin tidak dapat melihat audit logs (hanya super admin)
6. Super admin hanya dapat **view** reports, tidak dapat edit

## Files Modified/Created

### Modified:
- `includes/auth.php` - Tambah fungsi register, audit log, support 4 role
- `includes/admin_utils.php` - Tambah user management functions
- `login.php` - Dynamic redirect berdasarkan role
- `registrasi.php` - Update action ke 'register'

### Created:
- `super_admin.php` - Dashboard super admin
- `database/update_4_roles.sql` - Schema update untuk 4 role
- `ROLE_SYSTEM_DOCS.md` - Dokumentasi ini

## Testing Workflow

1. **Register sebagai warga**:
   - Buka `registrasi.php`
   - Isi form dan submit
   - Default role: `warga`

2. **Login sebagai super admin**:
   - Username: `superadmin`
   - Password: `superadmin123`
   - Redirect ke: `super_admin.php`

3. **Change role dari warga ke petugas**:
   - Login sebagai super admin
   - Buka tab "User Management"
   - Klik edit pada user warga
   - Ubah role ke "petugas"
   - Save

4. **Test workflow laporan**:
   - Login sebagai warga → Submit laporan
   - Login sebagai admin → Forward ke petugas
   - Login sebagai petugas → Selesaikan laporan
   - Login sebagai admin → Finalize laporan
   - Login sebagai super admin → View semua dalam audit log

## Next Steps (TODO)

- [ ] Update `admin.php` untuk workflow baru (forward ke petugas, terima balasan)
- [ ] Update `petugas.php` untuk workflow baru (terima tugas, kirim laporan balik)
- [ ] Implementasi notifikasi real-time
- [ ] Implementasi email notification
- [ ] Export audit logs ke CSV/PDF
- [ ] Advanced filtering di super admin dashboard

## API Endpoints

### Super Admin Only:
- `POST includes/admin_utils.php?action=change_user_role` - Ubah role user
- `POST includes/admin_utils.php?action=toggle_user_status` - Toggle status user

### Authentication:
- `POST includes/auth.php?action=register` - Registrasi user baru
- `POST includes/auth.php?action=login` - Login user
- `POST includes/auth.php?action=logout` - Logout user
- `POST includes/auth.php?action=check` - Check login status

## Notes

- Sistem ini menggunakan **session-based authentication**
- Audit logs otomatis tercatat untuk semua aksi penting
- Super admin dapat melihat IP address dan user agent di audit logs
- Role hierarchy: `super_admin` > `admin` > `petugas` > `warga`
