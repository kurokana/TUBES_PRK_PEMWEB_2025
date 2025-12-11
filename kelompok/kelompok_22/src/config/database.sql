-- Database untuk SiPaMaLi (Sistem Pelaporan & Pemantauan Masalah Lingkungan)
-- Dibuat untuk Tugas Besar Praktikum Pemrograman Web 2025

-- Buat database
CREATE DATABASE IF NOT EXISTS sipamali_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE sipamali_db;

-- Tabel untuk menyimpan laporan
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(20) UNIQUE NOT NULL,
    category VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Menunggu', 'Diproses', 'Selesai', 'Tuntas', 'Ditolak') DEFAULT 'Menunggu',
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_report_id (report_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk admin (opsional - untuk autentikasi)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert admin default (username: admin, password: admin123)
-- Password di-hash menggunakan password_hash() PHP
INSERT INTO admin_users (username, password_hash, full_name) VALUES 
('admin', '$2y$10$eSGgM5C8GTJFgcZFjWwICOuIysAib0gzZutMWf7QrkBgEcOrD9oju', 'Administrator');

-- Insert data dummy untuk testing
INSERT INTO reports (report_id, category, location, description, status, created_at) VALUES
('RPT-001', 'Sampah', 'Jl. Ahmad Yani No. 12', 'Tumpukan sampah plastik menyumbat selokan pasar.', 'Selesai', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('RPT-002', 'Jalan', 'Simpang Lima', 'Lubang besar di tengah jalan sangat membahayakan pengendara motor.', 'Diproses', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('RPT-003', 'Polusi', 'Kawasan Industri B', 'Asap hitam tebal dari pabrik X terlihat sejak pagi.', 'Menunggu', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('RPT-004', 'Drainase', 'Jl. Gatot Subroto', 'Saluran air tersumbat menyebabkan genangan saat hujan.', 'Menunggu', NOW());

-- Stored Procedure untuk generate Report ID otomatis
DELIMITER //
CREATE PROCEDURE generate_report_id(OUT new_id VARCHAR(20))
BEGIN
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(report_id, 5) AS UNSIGNED)), 0) + 1 INTO next_num FROM reports;
    SET new_id = CONCAT('RPT-', LPAD(next_num, 4, '0'));
END //
DELIMITER ;

-- View untuk statistik
CREATE VIEW report_statistics AS
SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as processing_count,
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN category = 'Sampah' THEN 1 ELSE 0 END) as sampah_count,
    SUM(CASE WHEN category = 'Jalan' THEN 1 ELSE 0 END) as jalan_count,
    SUM(CASE WHEN category = 'Drainase' THEN 1 ELSE 0 END) as drainase_count,
    SUM(CASE WHEN category = 'Polusi' THEN 1 ELSE 0 END) as polusi_count
FROM reports;
