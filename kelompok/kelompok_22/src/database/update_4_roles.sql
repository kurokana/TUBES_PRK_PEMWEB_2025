-- ============================================
-- Update Schema untuk 4 Role System
-- Tambahan: super_admin role dan audit logs enhancement
-- ============================================

USE sipamali_db;

-- Backup existing role values
ALTER TABLE users MODIFY COLUMN role VARCHAR(20) NOT NULL DEFAULT 'warga';

-- Update role enum to include super_admin
ALTER TABLE users MODIFY COLUMN role ENUM('warga', 'petugas', 'admin', 'super_admin') NOT NULL DEFAULT 'warga';

-- ============================================
-- TABEL BARU: AUDIT_LOGS (untuk Super Admin)
-- ============================================
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action_type ENUM('login', 'logout', 'create', 'update', 'delete', 'assign', 'status_change', 'role_change', 'user_management') NOT NULL,
    target_type VARCHAR(50) DEFAULT NULL COMMENT 'users, reports, etc',
    target_id INT DEFAULT NULL COMMENT 'ID of affected record',
    description TEXT NOT NULL,
    old_value TEXT DEFAULT NULL,
    new_value TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_target (target_type, target_id),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================
-- TABEL BARU: REPORT_WORKFLOW (Tracking alur laporan)
-- ============================================
CREATE TABLE IF NOT EXISTS report_workflow (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_id INT NOT NULL,
    from_user_id INT NOT NULL COMMENT 'User yang mengirim',
    to_user_id INT DEFAULT NULL COMMENT 'User yang menerima',
    workflow_type ENUM('submission', 'assignment', 'completion', 'response', 'finalization') NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'returned') DEFAULT 'pending',
    message TEXT DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_report_id (report_id),
    INDEX idx_from_user (from_user_id),
    INDEX idx_to_user (to_user_id),
    INDEX idx_workflow_type (workflow_type),
    FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE,
    FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- INSERT Super Admin Default User
-- ============================================

-- Insert Super Admin (password: superadmin123)
INSERT INTO users (username, email, password_hash, full_name, role, email_verified, is_active) VALUES 
('superadmin', 'superadmin@sipamali.id', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL2Kke9.', 'Super Administrator', 'super_admin', 1, 1)
ON DUPLICATE KEY UPDATE role = 'super_admin';

-- ============================================
-- Update existing reports status enum
-- ============================================
ALTER TABLE reports MODIFY COLUMN status ENUM('Menunggu', 'Diteruskan', 'Diproses', 'Selesai', 'Ditolak', 'Dikembalikan') DEFAULT 'Menunggu';

-- ============================================
-- Tambah kolom untuk tracking workflow
-- ============================================
ALTER TABLE reports ADD COLUMN forwarded_to INT DEFAULT NULL COMMENT 'Petugas yang ditugaskan' AFTER assigned_to;
ALTER TABLE reports ADD COLUMN forwarded_by INT DEFAULT NULL COMMENT 'Admin yang meneruskan' AFTER forwarded_to;
ALTER TABLE reports ADD COLUMN completion_notes TEXT DEFAULT NULL COMMENT 'Catatan penyelesaian dari petugas' AFTER admin_notes;
ALTER TABLE reports ADD COLUMN completion_image VARCHAR(255) DEFAULT NULL COMMENT 'Foto hasil pekerjaan' AFTER completion_notes;

-- Add foreign keys for new columns (check if column exists first)
-- ALTER TABLE reports ADD FOREIGN KEY (forwarded_to) REFERENCES users(id) ON DELETE SET NULL;
-- ALTER TABLE reports ADD FOREIGN KEY (forwarded_by) REFERENCES users(id) ON DELETE SET NULL;

-- ============================================
-- VIEW: Super Admin Dashboard Statistics
-- ============================================
CREATE OR REPLACE VIEW super_admin_stats AS
SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'warga') as total_warga,
    (SELECT COUNT(*) FROM users WHERE role = 'petugas') as total_petugas,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as total_admin,
    (SELECT COUNT(*) FROM users WHERE role = 'super_admin') as total_super_admin,
    (SELECT COUNT(*) FROM users WHERE is_active = 1) as active_users,
    (SELECT COUNT(*) FROM reports) as total_reports,
    (SELECT COUNT(*) FROM reports WHERE status = 'Menunggu') as pending_reports,
    (SELECT COUNT(*) FROM reports WHERE status = 'Diteruskan') as forwarded_reports,
    (SELECT COUNT(*) FROM reports WHERE status = 'Diproses') as processing_reports,
    (SELECT COUNT(*) FROM reports WHERE status = 'Selesai') as completed_reports,
    (SELECT COUNT(*) FROM audit_logs WHERE DATE(created_at) = CURDATE()) as today_audit_count;

-- ============================================
-- VIEW: Report Workflow Summary
-- ============================================
CREATE OR REPLACE VIEW report_workflow_summary AS
SELECT 
    r.id,
    r.report_id,
    r.category,
    r.status,
    u1.full_name as reporter_name,
    u1.username as reporter_username,
    u2.full_name as admin_name,
    u3.full_name as petugas_name,
    r.created_at as submitted_at,
    r.updated_at as last_updated
FROM reports r
LEFT JOIN users u1 ON r.user_id = u1.id
LEFT JOIN users u2 ON r.forwarded_by = u2.id
LEFT JOIN users u3 ON r.forwarded_to = u3.id;

-- ============================================
-- Sample Audit Logs
-- ============================================
INSERT INTO audit_logs (user_id, action_type, target_type, target_id, description, ip_address) VALUES
(1, 'login', NULL, NULL, 'Admin logged in to system', '127.0.0.1'),
(1, 'assign', 'reports', 1, 'Admin assigned report RPT-0001 to petugas', '127.0.0.1');

COMMIT;
