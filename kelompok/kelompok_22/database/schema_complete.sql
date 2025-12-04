-- ============================================
-- SiPaMaLi v2.0 - Complete Database Schema
-- Sistem Pelaporan & Pemantauan Masalah Lingkungan
-- Kelompok 22 - Praktikum Pemrograman Web 2025
-- ============================================

CREATE DATABASE IF NOT EXISTS sipamali_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sipamali_db;

-- ============================================
-- TABEL 1: USERS (Multi-Role User Management)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('admin', 'petugas', 'warga') DEFAULT 'warga',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- ============================================
-- TABEL 2: REPORTS (Laporan dengan User Integration)
-- ============================================
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id VARCHAR(20) UNIQUE NOT NULL,
    user_id INT DEFAULT NULL,
    category VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    description TEXT NOT NULL,
    status ENUM('Menunggu', 'Diproses', 'Selesai', 'Ditolak') DEFAULT 'Menunggu',
    priority ENUM('Rendah', 'Sedang', 'Tinggi', 'Urgent') DEFAULT 'Sedang',
    image_path VARCHAR(255) DEFAULT NULL,
    assigned_to INT DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_created_at (created_at),
    INDEX idx_report_id (report_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABEL 3: REPORT_ASSIGNMENTS (Assignment Tracking)
-- ============================================
CREATE TABLE IF NOT EXISTS report_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_to INT NOT NULL,
    notes TEXT DEFAULT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_id (report_id),
    INDEX idx_assigned_to (assigned_to),
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABEL 4: REPORT_PROGRESS (Progress Tracking)
-- ============================================
CREATE TABLE IF NOT EXISTS report_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('Menunggu', 'Diproses', 'Selesai', 'Ditolak') NOT NULL,
    notes TEXT DEFAULT NULL,
    image_before VARCHAR(255) DEFAULT NULL,
    image_after VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_report_id (report_id),
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABEL 5: REPORT_COMMENTS (Komentar)
-- ============================================
CREATE TABLE IF NOT EXISTS report_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    is_internal TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_report_id (report_id),
    INDEX idx_user_id (user_id),
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABEL 6: NOTIFICATIONS (Notifikasi User)
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    report_id INT DEFAULT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- TABEL 7: ACTIVITY_LOGS (Audit Trail)
-- ============================================
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- INSERT DATA AWAL
-- ============================================

-- Insert Admin (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, role, email_verified) VALUES 
('admin', 'admin@sipamali.id', '$2y$10$eSGgM5C8GTJFgcZFjWwICOuIysAib0gzZutMWf7QrkBgEcOrD9oju', 'Administrator SiPaMaLi', 'admin', 1);

-- Insert Petugas (password: petugas123)
INSERT INTO users (username, email, password_hash, full_name, phone, role, email_verified) VALUES 
('petugas1', 'petugas1@sipamali.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', '081234567890', 'petugas', 1),
('petugas2', 'petugas2@sipamali.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Nurhaliza', '081234567891', 'petugas', 1);

-- Insert Warga Demo (password: warga123)
INSERT INTO users (username, email, password_hash, full_name, phone, role, email_verified) VALUES 
('warga1', 'warga1@example.com', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Ahmad Yani', '082345678901', 'warga', 1);

-- Insert Sample Reports dengan user_id
INSERT INTO reports (report_id, user_id, category, location, description, status, priority, assigned_to, created_at) VALUES
('RPT-0001', 4, 'Sampah', 'Jl. Ahmad Yani No. 12', 'Tumpukan sampah plastik menyumbat selokan pasar. Sudah hampir seminggu tidak diangkut.', 'Selesai', 'Tinggi', 2, DATE_SUB(NOW(), INTERVAL 5 DAY)),
('RPT-0002', 4, 'Jalan', 'Simpang Lima', 'Lubang besar di tengah jalan sangat membahayakan pengendara motor. Diameter sekitar 1 meter.', 'Diproses', 'Urgent', 2, DATE_SUB(NOW(), INTERVAL 3 DAY)),
('RPT-0003', NULL, 'Polusi', 'Kawasan Industri B', 'Asap hitam tebal dari pabrik X terlihat sejak pagi. Menyengat dan mengganggu pernafasan.', 'Menunggu', 'Tinggi', NULL, DATE_SUB(NOW(), INTERVAL 1 DAY)),
('RPT-0004', 4, 'Drainase', 'Jl. Gatot Subroto Km 5', 'Saluran air tersumbat menyebabkan genangan saat hujan. Bau tidak sedap.', 'Menunggu', 'Sedang', NULL, NOW());

-- Insert Sample Progress
INSERT INTO report_progress (report_id, user_id, status, notes) VALUES
(1, 2, 'Diproses', 'Tim sudah diterjunkan untuk membersihkan area'),
(1, 2, 'Selesai', 'Sampah sudah dibersihkan dan selokan lancar'),
(2, 2, 'Diproses', 'Pengaspalan akan dilakukan besok pagi');

-- Insert Sample Comments
INSERT INTO report_comments (report_id, user_id, comment) VALUES
(1, 4, 'Terima kasih sudah ditindaklanjuti dengan cepat!'),
(2, 2, 'Sedang menunggu material aspal. Estimasi selesai 2 hari'),
(2, 4, 'Baik, ditunggu. Hati-hati ya kalau lewat sini');

-- Insert Sample Notifications
INSERT INTO notifications (user_id, report_id, type, title, message) VALUES
(4, 1, 'status_update', 'Laporan Diproses', 'Laporan RPT-0001 sedang ditindaklanjuti oleh petugas'),
(4, 1, 'status_update', 'Laporan Selesai', 'Laporan RPT-0001 telah diselesaikan'),
(4, 2, 'status_update', 'Laporan Diproses', 'Laporan RPT-0002 sedang dalam pengerjaan');

-- ============================================
-- VIEWS untuk Analytics
-- ============================================

-- View: Dashboard Statistics
CREATE OR REPLACE VIEW dashboard_stats AS
SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN status = 'Menunggu' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as processing_count,
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) as rejected_count,
    SUM(CASE WHEN category = 'Sampah' THEN 1 ELSE 0 END) as sampah_count,
    SUM(CASE WHEN category = 'Jalan' THEN 1 ELSE 0 END) as jalan_count,
    SUM(CASE WHEN category = 'Drainase' THEN 1 ELSE 0 END) as drainase_count,
    SUM(CASE WHEN category = 'Polusi' THEN 1 ELSE 0 END) as polusi_count,
    (SELECT COUNT(*) FROM users WHERE role = 'warga') as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'petugas') as total_petugas
FROM reports;

-- View: Reports with User Details
CREATE OR REPLACE VIEW reports_with_user AS
SELECT 
    r.*,
    u.full_name as reporter_name,
    u.email as reporter_email,
    u.phone as reporter_phone,
    u.role as reporter_role,
    p.full_name as petugas_name,
    p.phone as petugas_phone,
    (SELECT COUNT(*) FROM report_comments WHERE report_id = r.id) as comment_count,
    (SELECT COUNT(*) FROM report_progress WHERE report_id = r.id) as progress_count
FROM reports r
LEFT JOIN users u ON r.user_id = u.id
LEFT JOIN users p ON r.assigned_to = p.id;

-- View: Petugas Leaderboard
CREATE OR REPLACE VIEW petugas_leaderboard AS
SELECT 
    u.id,
    u.full_name,
    u.phone,
    COUNT(r.id) as total_assigned,
    SUM(CASE WHEN r.status = 'Selesai' THEN 1 ELSE 0 END) as total_resolved,
    SUM(CASE WHEN r.status = 'Diproses' THEN 1 ELSE 0 END) as ongoing,
    ROUND(AVG(CASE 
        WHEN r.status = 'Selesai' 
        THEN TIMESTAMPDIFF(HOUR, r.created_at, r.resolved_at) 
        ELSE NULL 
    END), 2) as avg_resolve_hours
FROM users u
LEFT JOIN reports r ON u.id = r.assigned_to
WHERE u.role = 'petugas'
GROUP BY u.id
ORDER BY total_resolved DESC;

-- View: Monthly Report Trends
CREATE OR REPLACE VIEW monthly_report_trends AS
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as completed,
    category
FROM reports
GROUP BY DATE_FORMAT(created_at, '%Y-%m'), category
ORDER BY month DESC;

-- ============================================
-- STORED PROCEDURES
-- ============================================

-- Generate Report ID
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS generate_report_id(OUT new_id VARCHAR(20))
BEGIN
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(report_id, 5) AS UNSIGNED)), 0) + 1 INTO next_num FROM reports;
    SET new_id = CONCAT('RPT-', LPAD(next_num, 4, '0'));
END //
DELIMITER ;

-- Create Notification Helper
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS create_notification(
    IN p_user_id INT,
    IN p_report_id INT,
    IN p_type VARCHAR(50),
    IN p_title VARCHAR(255),
    IN p_message TEXT
)
BEGIN
    INSERT INTO notifications (user_id, report_id, type, title, message)
    VALUES (p_user_id, p_report_id, p_type, p_title, p_message);
END //
DELIMITER ;

-- ============================================
-- TRIGGERS
-- ============================================

-- Trigger: Buat notifikasi saat status laporan berubah
DELIMITER //
CREATE TRIGGER after_report_status_update
AFTER UPDATE ON reports
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status AND NEW.user_id IS NOT NULL THEN
        INSERT INTO notifications (user_id, report_id, type, title, message)
        VALUES (
            NEW.user_id,
            NEW.id,
            'status_update',
            CONCAT('Status Laporan Berubah: ', NEW.report_id),
            CONCAT('Laporan Anda telah diupdate menjadi status: ', NEW.status)
        );
    END IF;
END //
DELIMITER ;

-- Trigger: Log assignment
DELIMITER //
CREATE TRIGGER after_report_assigned
AFTER UPDATE ON reports
FOR EACH ROW
BEGIN
    IF NEW.assigned_to IS NOT NULL AND (OLD.assigned_to IS NULL OR OLD.assigned_to != NEW.assigned_to) THEN
        -- Notifikasi ke petugas
        INSERT INTO notifications (user_id, report_id, type, title, message)
        VALUES (
            NEW.assigned_to,
            NEW.id,
            'assignment',
            'Laporan Baru Ditugaskan',
            CONCAT('Anda mendapat tugas baru: ', NEW.report_id, ' - ', NEW.category)
        );
        
        -- Log activity
        INSERT INTO activity_logs (user_id, action, description)
        VALUES (
            NEW.assigned_to,
            'report_assigned',
            CONCAT('Assigned to report ', NEW.report_id)
        );
    END IF;
END //
DELIMITER ;

-- ============================================
-- INDEXES untuk Optimization
-- ============================================

CREATE INDEX idx_reports_status_created ON reports(status, created_at DESC);
CREATE INDEX idx_reports_user_status ON reports(user_id, status);
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read, created_at DESC);
CREATE INDEX idx_comments_report_created ON report_comments(report_id, created_at DESC);

-- ============================================
-- SELESAI - Database Ready!
-- ============================================

SELECT 'Database schema created successfully!' as Status;
SELECT * FROM dashboard_stats;
SELECT * FROM petugas_leaderboard;
