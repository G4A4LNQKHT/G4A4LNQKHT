# SƠ ĐỒ CƠ SỞ DỮ LIỆU - TỔ 4

## Thông tin kết nối
- **Database Name**: `gaqzzint_db`
- **Username**: `gaqzzint_db`
- **Password**: `g4a4database`
- **Charset**: UTF8MB4 (hỗ trợ emoji, tiếng Việt)

---

## Sơ đồ quan hệ bảng (ER Diagram)

```
┌─────────────────┐
│     users       │
├─────────────────┤
│ id (PK)         │
│ username (UQ)   │
│ email (UQ)      │
│ password        │
│ full_name       │
│ class_position  │
│ is_admin        │
│ avatar          │
│ created_at      │
└─────────────────┘
       │
       │ (1:N)
       ├─────────────────────────┐
       │                         │
       ▼                         ▼
┌─────────────────┐      ┌──────────────────┐
│     posts       │      │   comments       │
├─────────────────┤      ├──────────────────┤
│ id (PK)         │      │ id (PK)          │
│ author_id (FK)  │◄─────│ post_id (FK)     │
│ title           │      │ author_id (FK)   │
│ content         │      │ content          │
│ category        │      │ created_at       │
│ image           │      │ updated_at       │
│ created_at      │      └──────────────────┘
│ updated_at      │
└─────────────────┘

┌──────────────────┐
│      tasks       │
├──────────────────┤
│ id (PK)          │
│ title            │
│ description      │
│ assigned_to (FK) │
│ due_date         │
│ priority         │
│ status           │
│ created_at       │
│ updated_at       │
└──────────────────┘

┌─────────────────┐
│   class_data    │
├─────────────────┤
│ id (PK)         │
│ title           │
│ category        │
│ file_url        │
│ description     │
│ uploaded_by(FK) │
│ created_at      │
└─────────────────┘

┌──────────────────┐
│    schedules     │
├──────────────────┤
│ id (PK)          │
│ day_of_week      │
│ period           │
│ subject          │
│ location         │
└──────────────────┘
```

---

## Chi tiết từng bảng

### 1️⃣ Bảng `users` - Quản lý thành viên

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,          -- bcrypt hash
    full_name VARCHAR(100) NOT NULL,
    class_position VARCHAR(50),               -- VD: "Tổ trưởng", "Tổ phó", "Thành viên"
    phone VARCHAR(20),
    avatar VARCHAR(255),                      -- URL ảnh đại diện
    is_admin TINYINT(1) DEFAULT 0,           -- 1 = admin, 0 = member
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_is_admin (is_admin)
);
```

**Dữ liệu mẫu**:
```
id=1, username=admin, password=bcrypt(admin), full_name=Admin, is_admin=1
id=2, username=member1, full_name=Nguyễn A, class_position=Tổ trưởng
id=3, username=member2, full_name=Trần B, class_position=Tổ phó
```

---

### 2️⃣ Bảng `posts` - Bài viết & Thông báo

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    category ENUM('news', 'memory', 'announcement', 'other') DEFAULT 'news',
    image VARCHAR(255),                      -- Ảnh đại diện bài viết
    view_count INT DEFAULT 0,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

**Loại bài viết**:
- `news`: Tin tức tổ
- `memory`: Lưu kỷ niệm
- `announcement`: Thông báo
- `other`: Khác

---

### 3️⃣ Bảng `comments` - Bình luận

```sql
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    author_id INT NOT NULL,
    content TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 1,        -- Có thể duyệt bình luận
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_post_id (post_id),
    INDEX idx_created_at (created_at)
);
```

---

### 4️⃣ Bảng `tasks` - Task List & Lịch trình

```sql
CREATE TABLE tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to INT,                         -- NULL = task chung
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
);
```

---

### 5️⃣ Bảng `class_data` - Tài liệu học tập

```sql
CREATE TABLE class_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),                   -- VD: "Toán", "Lý", "Hóa", "Đề thi", "Tài liệu"
    file_url VARCHAR(255),                   -- Link hoặc tên file
    file_type VARCHAR(50),                   -- VD: pdf, docx, xlsx, link
    uploaded_by INT,
    view_count INT DEFAULT 0,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_created_at (created_at)
);
```

---

### 6️⃣ Bảng `schedules` - Thời khóa biểu

```sql
CREATE TABLE schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    day_of_week ENUM('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'),
    period INT,                              -- 1, 2, 3, 4... (tiết)
    subject VARCHAR(100),                    -- Môn học
    location VARCHAR(100),                   -- Phòng học
    teacher_name VARCHAR(100),               -- Tên giáo viên
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_schedule (day_of_week, period)
);
```

**Dữ liệu mẫu**:
```
day_of_week=Mon, period=1, subject=Toán, location=Phòng 101
day_of_week=Mon, period=2, subject=Anh văn, location=Phòng 101
```

---

### 7️⃣ Bảng `logs` (Tuỳ chọn) - Theo dõi hành động

```sql
CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255),                     -- VD: "Tạo bài viết", "Xóa task"
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_created_at (created_at)
);
```

---

## Chỉ mục (Indexes) - Tối ưu tốc độ

**Tại sao cần index**:
- Tăng tốc độ tìm kiếm từ O(n) → O(log n)
- Giảm tải cho Shared Hosting

**Index được tạo**:
```
users:              username (UNIQUE), is_admin
posts:              category, status, created_at, author_id
comments:           post_id, created_at
tasks:              status, due_date, assigned_to
class_data:         category, created_at
```

---

## Mối quan hệ chi tiết

| Quan hệ | Từ bảng | Đến bảng | Kiểu | Mô tả |
|--------|---------|----------|------|-------|
| 1:N | `users` | `posts` | author_id | Một thành viên viết nhiều bài viết |
| 1:N | `users` | `comments` | author_id | Một thành viên viết nhiều bình luận |
| 1:N | `posts` | `comments` | post_id | Một bài viết có nhiều bình luận |
| 1:N | `users` | `tasks` | assigned_to | Một thành viên được giao nhiều task |
| 1:N | `users` | `class_data` | uploaded_by | Một thành viên tải lên nhiều file |

---

## Nguyên tắc thiết kế CSDL

✅ **Áp dụng**:
- Chuẩn hóa 3NF (3rd Normal Form)
- Sử dụng UNSIGNED INT cho ID (tiết kiệm không gian)
- Timestamp tự động cho audit trail
- Foreign Key để duy trì toàn vẹn dữ liệu
- ENUM cho các giá trị có sẵn

✅ **Tối ưu Shared Hosting**:
- Index chọn lọc (không index toàn bộ)
- LONGTEXT thay vì TEXT khi cần (posts.content)
- Không sử dụng trigger phức tạp
- Archive dữ liệu cũ định kỳ (nếu cần)

