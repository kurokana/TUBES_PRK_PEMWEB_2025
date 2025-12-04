# 🔥 FINAL PROJECT

## Praktikum Pemrograman Web

### Laboratorium Teknik Komputer — Universitas Lampung

![Status](https://img.shields.io/badge/CI-Structure%20Check-blue)
![PRs](https://img.shields.io/badge/PR-Welcome-green)
![GitHub](https://img.shields.io/badge/GitHub-Repository-black)

Repositori ini digunakan sebagai **Tempat Pengumpulan Tugas Besar Praktikum Pemrograman Web**.  
Setiap kelompok **WAJIB** mengikuti aturan, struktur folder, dan mekanisme CI yang berlaku.

---

## 📝 1. Mekanisme Pengumpulan

### **1. Fork Repository**

Setiap ketua kelompok melakukan **fork** repository ini.

### **2. Buat Folder Kelompok**

Gunakan format:

```bash
kelompok/kelompok_XX/
```

Contoh:

```bash
kelompok/kelompok_04/
```

### **3. Struktur Wajib di Dalam Folder Kelompok**

```bash
kelompok_04/
└── README.md # Dokumentasi + anggota kelompok
```

> **README.md wajib berisi:**
>
> - Daftar anggota
> - Judul & summary project
> - Cara menjalankan aplikasi

### **4. Push Perubahan ke Repo Fork**

### **5. Buat Pull Request**

Format judul PR:

```

[Kelompok-04] Nama Proyek

```

### **6. PR Akan Dicek Oleh CI**

CI memverifikasi:

- Struktur folder sesuai format
- Hanya mengubah folder kelompok sendiri
- File wajib tersedia
- README tidak kosong
- Tidak menyentuh folder/berkas milik kelompok lain

Jika salah → **CI otomatis gagal & memberikan komentar.**

---

## 🎯 2. Tema Final Project (pilih satu)

| No  | Tema                                    | Deskripsi Singkat                        |
| --- | --------------------------------------- | ---------------------------------------- |
| 1   | **Good Governance**                     | Layanan publik, perizinan, sosial, pajak |
| 2   | **Innovation in Health**                | Telemedicine, rekam medis, jadwal dokter |
| 3   | **Innovation in Education**             | E-learning, aplikasi edukasi             |
| 4   | **Digital Transformation for SMEs**     | POS, marketplace, inventori              |
| 5   | **Community & Organization Management** | Sistem komunitas, voting, donasi         |
| 6   | **Smart City & Environment**            | Pelaporan infrastruktur, lingkungan      |

---

## ⚙️ 3. Ketentuan Umum

### **Persyaratan Teknis**

- **Frontend:**
  HTML5, CSS3 (Native/Tailwind/Bootstrap), JavaScript Native
  _Tidak boleh memakai framework JS (React/Vue/Angular)._

- **Backend:** PHP Native
- **Database:** MySQL

  - Wajib menyediakan ERD & SQL schema

- **Version Control:** Git & GitHub

---

## 🚀 4. Fitur Wajib

### **1. User Management**

- Login
- Logout
- Registrasi
- Role / hak akses

### **2. Fitur Transaksi/Layanan**

Contoh:

- CRUD data
- Proses transaksi
- Validasi data
- Pelaporan

---

## 📦 5. Deliverables

Setiap kelompok wajib mengumpulkan:

- Source code lengkap pada folder `src/`
- File SQL database
- ERD (PNG/JPG/PDF)
- Screenshot aplikasi
- README.md dalam folder kelompok:
  - Instalasi & cara menjalankan
  - Dokumentasi singkat
- Presentasi & demo (opsional)

---

## 📁 6. Struktur Repo

```bash
TUBES_PRK_PEMWEB_2025/
│
├── README.md
├── .github/
│ └── workflows/
│ └── format-check.yml
│
└── kelompok/
      └── kelompok_01/
```

---

## ⚠️ 7. Aturan Tambahan

- Dilarang mengubah folder kelompok lain
- Dilarang membuat folder di luar `kelompok/`
- Nama folder tidak boleh diganti setelah dibuat
- PR dengan struktur salah akan ditolak CI

---

## 📞 8. Kontak Resmi

Silakan hubungi Asisten Praktikum jika ada kendala teknis terkait CI atau mekanisme pengumpulan.

---

### 🎉 Selamat mengerjakan!

Gunakan Git dengan baik, commit secara bertahap, dan kerjakan proyek dengan rapi.

> Laboratorium Teknik Komputer — Universitas Lampung
