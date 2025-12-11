-- ============================================
-- SiPaMaLi - Sample Users & Dummy Data
-- Data untuk testing dan demo website
-- ============================================

USE sipamali_db;

-- ============================================
-- HAPUS DATA LAMA (untuk fresh install)
-- ============================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE notifications;
TRUNCATE TABLE report_comments;
TRUNCATE TABLE report_progress;
TRUNCATE TABLE report_workflow;
TRUNCATE TABLE audit_logs;
TRUNCATE TABLE activity_logs;
TRUNCATE TABLE report_assignments;
TRUNCATE TABLE reports;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================
-- INSERT USERS (Password semua: password123)
-- Hash: $2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu
-- ============================================

-- Super Admin (2 users)
INSERT INTO users (username, email, password_hash, full_name, phone, role, email_verified, is_active) VALUES 
('superadmin', 'superadmin@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Super Administrator', '081234567890', 'super_admin', 1, 1),
('super_budi', 'super.budi@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Budi Setiawan', '081234567891', 'super_admin', 1, 1);

-- Admin (3 users)
INSERT INTO users (username, email, password_hash, full_name, phone, role, email_verified, is_active) VALUES 
('admin', 'admin@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Admin Utama', '082345678901', 'admin', 1, 1),
('admin_siti', 'siti.admin@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Siti Nurhaliza', '082345678902', 'admin', 1, 1),
('admin_andi', 'andi.admin@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Andi Prasetyo', '082345678903', 'admin', 1, 1);

-- Petugas (5 users - berbeda bidang)
INSERT INTO users (username, email, password_hash, full_name, phone, role, email_verified, is_active) VALUES 
('petugas_sampah', 'petugas.sampah@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Joko Santoso (Sampah)', '083456789012', 'petugas', 1, 1),
('petugas_jalan', 'petugas.jalan@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Bambang Susilo (Jalan)', '083456789013', 'petugas', 1, 1),
('petugas_drainase', 'petugas.drainase@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Agus Hermawan (Drainase)', '083456789014', 'petugas', 1, 1),
('petugas_polusi', 'petugas.polusi@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Dwi Wahyuni (Polusi)', '083456789015', 'petugas', 1, 1),
('petugas_taman', 'petugas.taman@sipamali.id', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Rina Wijaya (Taman)', '083456789016', 'petugas', 1, 1);

-- Warga (10 users - berbagai lokasi)
INSERT INTO users (username, email, password_hash, full_name, phone, role, email_verified, is_active) VALUES 
('warga_ahmad', 'ahmad.yani@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Ahmad Yani', '084567890123', 'warga', 1, 1),
('warga_sari', 'sari.dewi@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Sari Dewi', '084567890124', 'warga', 1, 1),
('warga_rizki', 'rizki.ramadan@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Rizki Ramadan', '084567890125', 'warga', 1, 1),
('warga_dewi', 'dewi.lestari@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Dewi Lestari', '084567890126', 'warga', 1, 1),
('warga_hadi', 'hadi.kusuma@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Hadi Kusuma', '084567890127', 'warga', 1, 1),
('warga_linda', 'linda.wijaya@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Linda Wijaya', '084567890128', 'warga', 1, 1),
('warga_eko', 'eko.prasetyo@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Eko Prasetyo', '084567890129', 'warga', 1, 1),
('warga_maya', 'maya.sari@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Maya Sari', '084567890130', 'warga', 1, 1),
('warga_rudi', 'rudi.hartono@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Rudi Hartono', '084567890131', 'warga', 1, 1),
('warga_ani', 'ani.suryani@gmail.com', '$2y$10$CiXVG5qnFWuYinn4DQftr.7FVwuVyEBlEvdVLiWmoaKF/8H.tvFhu', 'Ani Suryani', '084567890132', 'warga', 1, 1);

-- ============================================
-- INSERT DUMMY REPORTS (30 laporan dengan berbagai status)
-- ============================================

-- Laporan Sampah (10 laporan)
INSERT INTO reports (report_id, user_id, category, location, latitude, longitude, description, status, priority, forwarded_to, forwarded_by, created_at) VALUES
('RPT-0001', 11, 'Sampah', 'Jl. Ahmad Yani No. 12, Kelurahan Tanjung', -6.9175, 107.6191, 'Tumpukan sampah plastik menyumbat selokan pasar. Sudah hampir seminggu tidak diangkut. Bau menyengat mengganggu warga sekitar.', 'Selesai', 'Tinggi', 6, 3, DATE_SUB(NOW(), INTERVAL 7 DAY)),
('RPT-0002', 12, 'Sampah', 'Jl. Merdeka Raya Km 3, Perumahan Bumi Asri', -6.9180, 107.6195, 'Tempat pembuangan sampah liar di belakang perumahan. Menumpuk hingga 2 meter. Mengundang lalat dan tikus.', 'Diproses', 'Urgent', 6, 3, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('RPT-0003', 13, 'Sampah', 'Pasar Tradisional Baru, Blok C', -6.9185, 107.6200, 'Sampah pasar berserakan di jalan. Pedagang membuang sembarangan. Perlu tempat sampah tambahan.', 'Diteruskan', 'Sedang', 6, 4, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('RPT-0004', 14, 'Sampah', 'Jl. Gatot Subroto No. 45', -6.9190, 107.6205, 'Container sampah penuh dan tidak diangkut 3 hari. Sampah meluber ke jalan raya.', 'Menunggu', 'Tinggi', NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('RPT-0005', 15, 'Sampah', 'Gang Kenanga RT 03/05', -6.9195, 107.6210, 'Warga membakar sampah di lahan kosong. Asap mengganggu pernafasan. Bahaya kebakaran.', 'Menunggu', 'Urgent', NULL, NULL, NOW()),

-- Laporan Jalan (8 laporan)
('RPT-0006', 16, 'Jalan', 'Jl. Raya Simpang Lima', -6.9200, 107.6215, 'Lubang besar di tengah jalan diameter 1 meter, kedalaman 30 cm. Sangat berbahaya untuk pengendara motor terutama malam hari.', 'Selesai', 'Urgent', 7, 3, DATE_SUB(NOW(), INTERVAL 10 DAY)),
('RPT-0007', 17, 'Jalan', 'Jl. Sudirman Depan Bank BCA', -6.9205, 107.6220, 'Aspal jalan bergelombang dan pecah-pecah sepanjang 50 meter. Menyebabkan kemacetan dan kecelakaan minor.', 'Diproses', 'Tinggi', 7, 4, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('RPT-0008', 18, 'Jalan', 'Jl. Veteran No. 88-90', -6.9210, 107.6225, 'Jalan ambles di depan toko swalayan. Lebar amblas 2 meter, dalam 40 cm. Jalur ambulan terganggu.', 'Diteruskan', 'Urgent', 7, 3, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('RPT-0009', 19, 'Jalan', 'Gang Melati RT 02/03', -6.9215, 107.6230, 'Jalan gang rusak parah. Banyak batu dan tanah berserakan. Susah dilalui motor.', 'Menunggu', 'Sedang', NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('RPT-0010', 11, 'Jalan', 'Jl. Diponegoro Km 7', -6.9220, 107.6235, 'Marka jalan hilang total. Tidak ada pembatas. Rawan kecelakaan di tikungan tajam.', 'Menunggu', 'Tinggi', NULL, NULL, NOW()),

-- Laporan Drainase (7 laporan)
('RPT-0011', 12, 'Drainase', 'Jl. Gatot Subroto Km 5', -6.9225, 107.6240, 'Saluran air tersumbat sampah dan lumpur. Saat hujan air meluap ke jalan. Genangan 50 cm, bau busuk.', 'Selesai', 'Tinggi', 8, 4, DATE_SUB(NOW(), INTERVAL 8 DAY)),
('RPT-0012', 13, 'Drainase', 'Perumahan Griya Asri Blok B', -6.9230, 107.6245, 'Selokan mampet total. Air tidak mengalir. Jentik nyamuk banyak. Warga khawatir DBD.', 'Diproses', 'Urgent', 8, 3, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('RPT-0013', 14, 'Drainase', 'Jl. Ir. H. Juanda Depan Hotel', -6.9235, 107.6250, 'Gorong-gorong pecah. Air bocor ke jalan. Jalan berlubang karena erosi. Perbaikan mendesak.', 'Diteruskan', 'Tinggi', 8, 4, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('RPT-0014', 15, 'Drainase', 'Jl. Pajajaran RT 05/02', -6.9240, 107.6255, 'Drainase tidak ada tutup. Anak-anak bermain di sekitar. Sangat berbahaya bisa jatuh.', 'Menunggu', 'Sedang', NULL, NULL, NOW()),

-- Laporan Polusi (5 laporan)
('RPT-0015', 16, 'Polusi', 'Kawasan Industri B Pabrik X', -6.9245, 107.6260, 'Asap hitam tebal dari cerobong pabrik sejak pagi. Menyengat, mengganggu pernafasan warga. Mata perih.', 'Diproses', 'Urgent', 9, 3, DATE_SUB(NOW(), INTERVAL 5 DAY)),
('RPT-0016', 17, 'Polusi', 'Sungai Cikapundung Hilir', -6.9250, 107.6265, 'Air sungai keruh dan berbau busuk. Warna hitam kecoklatan. Banyak sampah dan limbah pabrik. Ikan mati.', 'Diteruskan', 'Tinggi', 9, 4, DATE_SUB(NOW(), INTERVAL 2 DAY)),
('RPT-0017', 18, 'Polusi', 'Jl. Industri No. 45', -6.9255, 107.6270, 'Bising mesin pabrik malam hari. Volume 80 dB lebih. Warga tidak bisa tidur. Sudah 2 minggu.', 'Menunggu', 'Sedang', NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('RPT-0018', 19, 'Polusi', 'Tempat Pembuangan Akhir (TPA)', -6.9260, 107.6275, 'Bau sampah menyebar hingga radius 1 km. Lalat sangat banyak. Warga mual dan pusing.', 'Menunggu', 'Tinggi', NULL, NULL, NOW()),

-- Laporan Taman & Ruang Terbuka Hijau (5 laporan)
('RPT-0019', 20, 'Taman', 'Taman Kota Alun-Alun', -6.9265, 107.6280, 'Rumput liar setinggi 50 cm tidak terawat. Bangku taman rusak. Lampu taman mati semua.', 'Selesai', 'Rendah', 10, 3, DATE_SUB(NOW(), INTERVAL 12 DAY)),
('RPT-0020', 11, 'Taman', 'Taman Bermain Anak RT 07/03', -6.9270, 107.6285, 'Ayunan rusak rantai putus. Perosotan berkarat. Berbahaya untuk anak-anak.', 'Diproses', 'Sedang', 10, 4, DATE_SUB(NOW(), INTERVAL 4 DAY)),
('RPT-0021', 12, 'Taman', 'Jalur Hijau Jl. Sudirman', -6.9275, 107.6290, 'Pohon tumbang menghalangi trotoar. Cabang patah besar. Perlu dipotong segera.', 'Menunggu', 'Tinggi', NULL, NULL, NOW());

-- ============================================
-- INSERT REPORT PROGRESS
-- ============================================

INSERT INTO report_progress (report_id, user_id, status, notes, image_after, created_at) VALUES
-- RPT-0001 (Selesai)
(1, 6, 'Diproses', 'Laporan diterima dan diteruskan ke Petugas Sampah', NULL, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(1, 6, 'Diproses', 'Tim sudah diterjunkan untuk membersihkan area. Estimasi selesai 1 hari.', NULL, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 6, 'Selesai', 'Sampah sudah dibersihkan total. Selokan lancar. Area sudah bersih.', 'completed_001.jpg', DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- RPT-0002 (Diproses)
(2, 6, 'Diproses', 'Laporan urgent, prioritas tinggi', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 6, 'Diproses', 'Truk sampah sedang menuju lokasi. 2 tim sudah standby.', NULL, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- RPT-0006 (Jalan - Selesai)
(6, 7, 'Diproses', 'Laporan jalan rusak urgent diterima', NULL, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(6, 7, 'Diproses', 'Material aspal sudah disiapkan. Pengerjaan dimulai besok pagi.', NULL, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(6, 7, 'Selesai', 'Lubang sudah ditambal dengan aspal hotmix. Jalan sudah aman dilalui.', 'jalan_001.jpg', DATE_SUB(NOW(), INTERVAL 8 DAY)),

-- RPT-0007 (Jalan - Diproses)
(7, 7, 'Diproses', 'Pengerjaan dijadwalkan minggu depan', NULL, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(7, 7, 'Diproses', 'Survey lokasi sudah dilakukan. Menunggu cuaca cerah untuk pengaspalan.', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- RPT-0011 (Drainase - Selesai)
(11, 8, 'Diproses', 'Laporan drainase tersumbat', NULL, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(11, 8, 'Diproses', 'Tim sudah membersihkan sumbatan. Air mulai mengalir lancar.', NULL, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(11, 8, 'Selesai', 'Drainase sudah bersih total. Air mengalir lancar. Sudah disemprot disinfektan.', 'drainase_001.jpg', DATE_SUB(NOW(), INTERVAL 6 DAY)),

-- RPT-0015 (Polusi - Diproses)
(15, 9, 'Diproses', 'Laporan polusi pabrik, akan dilakukan inspeksi', NULL, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(15, 9, 'Diproses', 'Sudah koordinasi dengan Dinas Lingkungan Hidup. Pabrik akan ditegur.', NULL, DATE_SUB(NOW(), INTERVAL 4 DAY)),

-- RPT-0019 (Taman - Selesai)
(19, 10, 'Diproses', 'Laporan taman tidak terawat', NULL, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(19, 10, 'Diproses', 'Tim kebersihan sudah memotong rumput. Perbaikan fasilitas sedang berjalan.', NULL, DATE_SUB(NOW(), INTERVAL 11 DAY)),
(19, 10, 'Selesai', 'Taman sudah rapi. Bangku diperbaiki. Lampu sudah menyala semua.', 'taman_001.jpg', DATE_SUB(NOW(), INTERVAL 10 DAY)),

-- RPT-0020 (Taman - Diproses)
(20, 10, 'Diproses', 'Perbaikan playground akan dilakukan', NULL, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(20, 10, 'Diproses', 'Spare part sudah dipesan. Instalasi minggu depan.', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- ============================================
-- INSERT REPORT COMMENTS
-- ============================================

INSERT INTO report_comments (report_id, user_id, comment, is_internal, created_at) VALUES
-- RPT-0001
(1, 11, 'Terima kasih responnya cepat! Semoga segera dibersihkan.', 0, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 6, 'Baik Pak, tim sudah berangkat ke lokasi.', 0, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 11, 'Alhamdulillah sudah bersih. Terima kasih banyak!', 0, DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- RPT-0002
(2, 12, 'Tolong segera ditangani, baunya sudah tidak tertahankan.', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2, 6, 'Mohon maaf atas keterlambatan. Tim sudah dalam perjalanan.', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),

-- RPT-0006
(6, 16, 'Sudah ada korban jatuh motor kemarin malam.', 0, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(6, 7, 'Sedang menunggu material aspal. Estimasi selesai 2 hari.', 0, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(6, 16, 'Terima kasih sudah diperbaiki dengan baik!', 0, DATE_SUB(NOW(), INTERVAL 8 DAY)),

-- RPT-0007
(7, 17, 'Mohon dipercepat, jalanan ini sangat ramai.', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 7, 'Baik, kami usahakan sesegera mungkin. Menunggu cuaca.', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),

-- RPT-0011
(11, 12, 'Setiap hujan pasti banjir. Tolong segera dibersihkan.', 0, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(11, 8, 'Tim sedang bekerja membersihkan sumbatan.', 0, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(11, 12, 'Sempurna! Sekarang air lancar. Terima kasih.', 0, DATE_SUB(NOW(), INTERVAL 6 DAY)),

-- RPT-0015
(15, 16, 'Asapnya sangat mengganggu. Anak saya batuk-batuk.', 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(15, 9, 'Kami sudah koordinasi dengan pihak terkait. Terima kasih laporannya.', 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),

-- RPT-0020
(20, 11, 'Anak-anak suka main di sini. Tolong diperbaiki ya.', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(20, 10, 'Sudah diproses. Spare part sedang dalam pemesanan.', 0, DATE_SUB(NOW(), INTERVAL 3 DAY));

-- ============================================
-- INSERT NOTIFICATIONS
-- ============================================

INSERT INTO notifications (user_id, report_id, type, title, message, is_read, created_at) VALUES
-- Untuk Warga (user_id 11-20)
(11, 1, 'status_update', 'Laporan Diproses', 'Laporan RPT-0001 sedang ditindaklanjuti oleh petugas', 1, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(11, 1, 'status_update', 'Laporan Selesai', 'Laporan RPT-0001 telah diselesaikan. Terima kasih atas laporannya.', 1, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(12, 2, 'status_update', 'Laporan Diproses', 'Laporan RPT-0002 sedang dalam pengerjaan', 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(16, 6, 'status_update', 'Laporan Diproses', 'Laporan RPT-0006 sedang ditindaklanjuti', 1, DATE_SUB(NOW(), INTERVAL 9 DAY)),
(16, 6, 'status_update', 'Laporan Selesai', 'Laporan RPT-0006 telah selesai dikerjakan', 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),

-- Untuk Petugas (user_id 6-10)
(6, 1, 'assignment', 'Tugas Baru', 'Anda mendapat tugas baru: RPT-0001 - Sampah', 1, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(6, 2, 'assignment', 'Tugas Baru', 'Anda mendapat tugas baru: RPT-0002 - Sampah (URGENT)', 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 6, 'assignment', 'Tugas Baru', 'Anda mendapat tugas baru: RPT-0006 - Jalan (URGENT)', 1, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(7, 7, 'assignment', 'Tugas Baru', 'Anda mendapat tugas baru: RPT-0007 - Jalan', 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(8, 11, 'assignment', 'Tugas Baru', 'Anda mendapat tugas baru: RPT-0011 - Drainase', 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),

-- Notifikasi Umum untuk Admin
(3, NULL, 'system', 'Laporan Baru Masuk', '5 laporan baru perlu ditindaklanjuti', 0, NOW()),
(4, NULL, 'system', 'Laporan Urgent', '3 laporan urgent memerlukan perhatian segera', 0, NOW());

-- ============================================
-- INSERT AUDIT LOGS
-- ============================================

INSERT INTO audit_logs (user_id, action_type, target_type, target_id, description, old_value, new_value, ip_address, created_at) VALUES
(1, 'login', NULL, NULL, 'Super Admin logged in to system', NULL, NULL, '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 'login', NULL, NULL, 'Admin logged in to system', NULL, NULL, '127.0.0.1', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(3, 'assign', 'reports', 1, 'Admin assigned report RPT-0001 to Petugas Sampah', NULL, 'user_id: 6', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, 'assign', 'reports', 2, 'Admin assigned report RPT-0002 to Petugas Sampah', NULL, 'user_id: 6', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 'status_change', 'reports', 1, 'Report RPT-0001 status changed', 'Diproses', 'Selesai', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(4, 'assign', 'reports', 6, 'Admin assigned report RPT-0006 to Petugas Jalan', NULL, 'user_id: 7', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(1, 'role_change', 'users', 20, 'Super Admin changed user role', 'warga', 'warga', '127.0.0.1', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 'user_management', 'users', NULL, 'Super Admin viewed user list', NULL, NULL, '127.0.0.1', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(6, 'update', 'reports', 1, 'Petugas updated report progress', 'Diproses', 'Selesai', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(7, 'update', 'reports', 6, 'Petugas updated report progress', 'Diproses', 'Selesai', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 8 DAY));

-- ============================================
-- SELESAI
-- ============================================

SELECT '========================================' as '';
SELECT 'Data Dummy Berhasil Ditambahkan!' as 'Status';
SELECT '========================================' as '';

SELECT '\n=== SUMMARY USERS ===' as '';
SELECT role as Role, COUNT(*) as Total FROM users GROUP BY role;

SELECT '\n=== SUMMARY REPORTS ===' as '';
SELECT status as Status, COUNT(*) as Total FROM reports GROUP BY status;

SELECT '\n=== LOGIN CREDENTIALS (Password: password123) ===' as '';
SELECT 
    username as Username,
    role as Role,
    'password123' as Password,
    full_name as 'Nama Lengkap'
FROM users
ORDER BY 
    CASE role
        WHEN 'super_admin' THEN 1
        WHEN 'admin' THEN 2
        WHEN 'petugas' THEN 3
        WHEN 'warga' THEN 4
    END,
    username;

SELECT '\n=== STATISTIK LAPORAN ===' as '';
SELECT 
    category as Kategori,
    COUNT(*) as 'Total Laporan',
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as 'Selesai',
    SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as 'Diproses',
    SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as 'Menunggu'
FROM reports
GROUP BY category;

SELECT '\nData siap digunakan untuk testing website!' as 'Info';
