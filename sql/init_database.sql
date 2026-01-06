-- ============================================
-- SCRIPT KHỞI TẠO DATABASE TỔ 4
-- Database: gaqzzint_db
-- ============================================

-- Xóa database cũ (nếu có) - CẨN THẬN!
-- DROP DATABASE IF EXISTS gaqzzint_db;

-- Tạo database
CREATE DATABASE IF NOT EXISTS gaqzzint_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE gaqzzint_db;

-- ============================================
-- 1. BẢNG USERS - QUẢN LÝ THÀNH VIÊN
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    class_position VARCHAR(50),
    phone VARCHAR(20),
    avatar VARCHAR(255),
    is_admin TINYINT(1) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_is_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. BẢNG POSTS - BÀI VIẾT & THÔNG BÁO
-- ============================================
CREATE TABLE IF NOT EXISTS posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    category ENUM('news', 'memory', 'announcement', 'other') DEFAULT 'news',
    image VARCHAR(255),
    view_count INT DEFAULT 0,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_author_id (author_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. BẢNG COMMENTS - BÌNH LUẬN
-- ============================================
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    author_id INT NOT NULL,
    content TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_author_id (author_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. BẢNG TASKS - TASK LIST & LỊCH TRÌNH
-- ============================================
CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to INT,
    due_date DATETIME,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_due_date (due_date),
    INDEX idx_assigned_to (assigned_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. BẢNG CLASS_DATA - TÀI LIỆU HỌC TẬP
-- ============================================
CREATE TABLE IF NOT EXISTS class_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    file_url VARCHAR(255),
    file_type VARCHAR(50),
    uploaded_by INT,
    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. BẢNG SCHEDULES - THỜI KHÓA BIỂU
-- ============================================
CREATE TABLE IF NOT EXISTS schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    day_of_week ENUM('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'),
    period INT,
    subject VARCHAR(100),
    location VARCHAR(100),
    teacher_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_schedule (day_of_week, period),
    INDEX idx_day_of_week (day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. BẢNG LOGS - THEO DÕI HÀNH ĐỘNG
-- ============================================
CREATE TABLE IF NOT EXISTS logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255),
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_created_at (created_at),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DỮ LIỆU KHỞI TẠO
-- ============================================

-- Tài khoản admin mặc định
-- Username: admin
-- Password: admin (nên đổi sau - thực tế: $2y$10$... bcrypt hash)
INSERT INTO users (username, email, password, full_name, is_admin, status) 
VALUES (
    'admin',
    'admin@g4a4.local',
    '$2y$10$YourBcryptHashedPasswordHere', -- Thay bằng bcrypt hash thật
    'Quản trị viên',
    1,
    'active'
);

-- Dữ liệu mẫu thành viên
INSERT INTO users (username, email, password, full_name, class_position, status) 
VALUES 
    ('member1', 'member1@g4a4.local', '$2y$10$..', 'Nguyễn Văn A', 'Tổ trưởng', 'active'),
    ('member2', 'member2@g4a4.local', '$2y$10$..', 'Trần Thị B', 'Tổ phó', 'active'),
    ('member3', 'member3@g4a4.local', '$2y$10$..', 'Lê Văn C', 'Thành viên', 'active');

-- Dữ liệu mẫu thời khóa biểu
INSERT INTO schedules (day_of_week, period, subject, location, teacher_name) VALUES
    ('Mon', 1, 'Toán', 'Phòng 101', 'Thầy Định'),
    ('Mon', 2, 'Tiếng Anh', 'Phòng 101', 'Cô Liên'),
    ('Mon', 3, 'Văn', 'Phòng 101', 'Thầy Hòa'),
    ('Mon', 4, 'Lịch Sử', 'Phòng 101', 'Cô Trang'),
    ('Tue', 1, 'Lý', 'Phòng 102', 'Thầy Mạnh'),
    ('Tue', 2, 'Hóa', 'Phòng 103', 'Cô Minh');

-- ============================================
-- KIỂM TRA DỮ LIỆU
-- ============================================
-- SELECT COUNT(*) as total_users FROM users;
-- SELECT * FROM users;
-- SELECT * FROM schedules;

