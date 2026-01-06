# HƯỚNG DẪN TRIỂN KHAI TRÊN SHARED HOSTING

## 1. CHUẨN BỊ VÀ ĐẶC ĐIỂM SHARED HOSTING

### Shared Hosting là gì?
- **Một máy chủ, nhiều website** - Website của bạn chia sẻ tài nguyên với các website khác
- **Giới hạn tài nguyên** - CPU, RAM, Traffic bị giới hạn
- **Giá rẻ** - Phù hợp cho website vừa và nhỏ
- **Dễ sử dụng** - Không cần kỹ năng quản trị máy chủ cao

### Điều kiện tối thiểu
```
✓ PHP 7.4+ (Khuyến nghị 8.0+)
✓ MySQL 5.7+ (Khuyến nghị 8.0+)
✓ Thư mục public_html hoặc public (khoảng 2GB)
✓ Hỗ trợ cPanel/Plesk
✓ SSL certificate (HTTPS)
```

---

## 2. CẤU HÌNH DOMAIN VÀ HOSTING

### Bước 1: Trỏ domain g4a4.qzz.io

**Nếu domain được cấp bởi hoster:**
1. Vào cPanel → Zone Editor
2. Tạo A record:
   ```
   Type:  A
   Name:  g4a4.qzz.io
   Value: [IP của hosting]
   ```
3. Chờ 10 phút - 24h để DNS cập nhật

**Nếu domain ở nhà cung cấp khác:**
1. Đăng nhập nhà cung cấp domain
2. Cập nhật Nameserver thành:
   ```
   ns1.[hosting-provider].com
   ns2.[hosting-provider].com
   ```
3. Hoặc cập nhật A record (nếu hỗ trợ)

---

## 3. KHỞI TẠO DATABASE

### Bước 1: Tạo database trong cPanel

1. Đăng nhập cPanel → MySQL Databases
2. Tạo database mới:
   - **Database Name**: `username_gaqzzint_db`
   - Nhấn "Create Database"

3. Tạo user MySQL:
   - **Username**: `username_gaqzzint_db`
   - **Password**: `g4a4database` (hoặc đặt password khác)
   - Nhấn "Create User"

4. Gán user vào database:
   - Chọn user vừa tạo
   - Chọn database vừa tạo
   - Cấp quyền ALL PRIVILEGES
   - Nhấn "Add User to Database"

### Bước 2: Chạy script SQL

**Phương pháp 1: Dùng phpMyAdmin**

1. Đăng nhập cPanel → phpMyAdmin
2. Chọn database `username_gaqzzint_db`
3. Chọn tab "Import"
4. Upload file `sql/init_database.sql`
5. Nhấn "Go" để chạy script

**Phương pháp 2: Dùng MySQL CLI (SSH)**

```bash
# SSH vào hosting
ssh username@g4a4.qzz.io

# Vào thư mục project
cd public_html

# Chạy script
mysql -u username_gaqzzint_db -p gaqzzint_db < sql/init_database.sql

# Nhập password khi được hỏi
```

### Verify database đã được tạo

```sql
-- Chạy trong phpMyAdmin → SQL tab
SHOW TABLES;  -- Phải thấy 7 bảng: users, posts, comments, tasks, class_data, schedules, logs
SELECT COUNT(*) FROM users;  -- Phải thấy ít nhất 4 user (1 admin + 3 sample)
```

---

## 4. UPLOAD FILES LÊN HOSTING

### Cấu trúc upload trên Shared Hosting

```
public_html/
├── index.php                 ← Trang chủ
├── login.php                 ← Đăng nhập
├── logout.php
├── members.php
├── posts.php
├── data.php
├── tasks.php
├── contact.php
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── responsive.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── admin/
│   ├── dashboard.php
│   ├── manage-posts.php
│   ├── manage-tasks.php
│   ├── manage-data.php
│   └── api/
│       ├── add-post.php
│       ├── add-comment.php
│       └── add-task.php
└── uploads/                  ← Thư mục upload (cần chmod 755)

../includes/                  ← NGOÀI public_html
├── config.php
├── auth.php
├── db.php
├── header.php
└── footer.php

../sql/
└── init_database.sql
```

### Bước 1: Upload bằng File Manager (cPanel)

1. Đăng nhập cPanel → File Manager
2. Vào thư mục `public_html`
3. Upload các file PHP:
   - Kéo thả hoặc chọn "Upload Files"
   - Upload từng file hoặc zip rồi giải nén

### Bước 2: Upload bằng FTP

```bash
# Sử dụng FileZilla, WinSCP, hoặc command line

# SSH (Linux/Mac)
sftp username@g4a4.qzz.io
> cd public_html
> put -r * .
> exit

# FTP (Windows)
# Dùng FileZilla hoặc WinSCP
# Host: g4a4.qzz.io
# Username: username (SSH user)
# Port: 22 (SFTP) hoặc 21 (FTP)
```

### Bước 3: Upload bằng Git (nếu hosting hỗ trợ)

```bash
# SSH vào hosting
ssh username@g4a4.qzz.io

# Clone từ GitHub
cd public_html
git clone https://github.com/[username]/g4a4-website.git .

# Hoặc pull nếu đã clone
git pull origin main
```

---

## 5. CẤU HÌNH THƯ MỤC VÀ QUYỀN TRUY CẬP

### Bước 1: Tạo thư mục cần thiết

```bash
# SSH vào hosting
ssh username@g4a4.qzz.io

# Tạo thư mục uploads
mkdir -p ~/public_html/uploads

# Tạo thư mục includes (ngoài public_html)
mkdir -p ~/includes
mkdir -p ~/admin

# Tạo thư mục cache (tuỳ chọn)
mkdir -p ~/cache
```

### Bước 2: Cấp quyền file

```bash
# Cấp quyền thư mục uploads (cho phép ghi file)
chmod 755 ~/public_html/uploads
chmod 755 ~/public_html/cache

# Cấp quyền file config.php (đọc)
chmod 644 ~/includes/config.php

# Cấp quyền file khác (đọc)
chmod 644 ~/public_html/*.php
chmod 644 ~/includes/*.php
chmod 644 ~/admin/*.php

# Cấp quyền CSS, JS (đọc)
chmod 644 ~/public_html/assets/css/*
chmod 644 ~/public_html/assets/js/*

# Cấp quyền database file (nếu SQLite - không cần cho MySQL)
# chmod 666 ~/db/database.sqlite
```

**Quyền hợp lý cho Shared Hosting:**
```
Thư mục:  755 (rwxr-xr-x)  - chủ sở hữu đọc/ghi/thực thi, người khác chỉ đọc/thực thi
File:     644 (rw-r--r--)   - chủ sở hữu đọc/ghi, người khác chỉ đọc
Upload:   755 (rwxr-xr-x)   - cho phép ghi file mới
```

---

## 6. CẬP NHẬT FILE CONFIG.PHP

### Bước 1: Chỉnh sửa thông tin kết nối database

Mở file `includes/config.php` và cập nhật:

```php
// Production (Shared Hosting)
else {
    define('DB_HOST', 'localhost');              // Thường là localhost
    define('DB_USER', 'username_gaqzzint_db');   // Thay username
    define('DB_PASS', 'g4a4database');           // Password từ bước 3
    define('DB_NAME', 'username_gaqzzint_db');   // Thay username
}

// Cập nhật BASE_URL
define('BASE_URL', 'https://g4a4.qzz.io/');

// Cập nhật ADMIN_EMAIL
define('ADMIN_EMAIL', 'admin@g4a4.local');
```

### Bước 2: Kiểm tra kết nối

Tạo file test `public_html/test_db.php`:

```php
<?php
require_once __DIR__ . '/../includes/config.php';

echo "PHP Version: " . phpversion() . "<br>";
echo "Database: " . DB_NAME . "<br>";

try {
    $db = Database::getInstance()->getConnection();
    
    if ($db->connect_error) {
        echo "❌ Lỗi kết nối: " . $db->connect_error;
    } else {
        echo "✅ Kết nối database thành công!<br>";
        
        // Test query
        $result = $db->query("SELECT COUNT(*) as total FROM users");
        $row = $result->fetch_assoc();
        echo "Tổng users: " . $row['total'];
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage();
}
?>
```

Truy cập: `https://g4a4.qzz.io/test_db.php`

Xóa file test này sau khi kiểm tra xong!

---

## 7. CẤU HÌNH SSL CERTIFICATE (HTTPS)

Hầu hết hosting cung cấp SSL miễn phí:

1. **Dùng AutoSSL (cPanel)**:
   - cPanel → AutoSSL
   - Chọn domain g4a4.qzz.io
   - Nhấn "Issue Certificate"
   - Chờ vài phút

2. **Dùng Let's Encrypt**:
   ```bash
   ssh username@g4a4.qzz.io
   sudo certbot certonly --webroot -w ~/public_html -d g4a4.qzz.io
   ```

3. **Cấu hình .htaccess (redirect HTTP → HTTPS)**:
   
Tạo/chỉnh sửa `public_html/.htaccess`:

```apache
# Redirect HTTP to HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Remove www
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ https://%1%{REQUEST_URI} [L,R=301]

# Pretty URLs (tuỳ chọn)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^\.]+)$ $1.php [NC,L]
```

---

## 8. CẤU HÌNH CHO SHARED HOSTING

### Tối ưu tốc độ

**Bật caching trong header (public_html/.htaccess)**:

```apache
# Cache static files
<IfModule mod_expires.c>
    ExpiresActive On
    
    # Images
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    
    # CSS/JS
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    
    # Default
    ExpiresDefault "access plus 2 days"
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/xml
</IfModule>

# Disable directory listing
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Cấu hình PHP

**Yêu cầu hosting provider bật:**
- PHP 8.0+
- MySQLi extension
- cURL extension
- JSON extension (default)

**Nếu tự quản lý, chỉnh sửa `php.ini`:**

```ini
; Bảo mật
display_errors = Off
log_errors = On
error_log = /var/log/php-errors.log

; Performance
max_execution_time = 30
memory_limit = 128M
upload_max_filesize = 50M
post_max_size = 50M

; Session
session.gc_maxlifetime = 7200
session.cookie_httponly = 1
session.cookie_secure = 1
```

---

## 9. KIỂM TRA VÀ TESTING

### Checklist

- [ ] Domain g4a4.qzz.io đã trỏ
- [ ] Database đã tạo và chạy script SQL
- [ ] Files đã upload lên public_html
- [ ] config.php đã cập nhật với DB credentials
- [ ] Test page hiển thị ✅ kết nối DB thành công
- [ ] Có thể truy cập https://g4a4.qzz.io
- [ ] Có thể đăng nhập với admin/admin
- [ ] Admin dashboard hiển thị đúng

### Test các chức năng chính

1. **Login/Logout**
   - Đăng nhập: admin / admin
   - Kiểm tra session
   - Đăng xuất

2. **Tạo bài viết (Admin)**
   - admin/manage-posts.php
   - Tạo bài viết mới
   - Kiểm tra xuất hiện ở index.php

3. **View công khai**
   - posts.php (xem bài viết)
   - members.php (danh sách thành viên)

---

## 10. BẢO MẬT VÀ MAINTENANCE

### Bảo mật

```php
// Đổi password admin (quan trọng!)
UPDATE users SET password = PASSWORD('new_password_here') WHERE id = 1;

// Hoặc dùng bcrypt từ PHP:
UPDATE users SET password = '$2y$10$...' WHERE id = 1;
```

**Xóa các file test/demo**:
```bash
rm ~/public_html/test_db.php
rm ~/sql/init_database.sql  # Hoặc bảo vệ
```

**Bảo vệ thư mục nhạy cảm**:

`includes/.htaccess`:
```apache
Deny from all
```

`admin/.htaccess`:
```apache
# Yêu cầu login (nếu Apache mod_auth hỗ trợ)
Require all denied
```

### Backup định kỳ

**Script backup tự động** (chạy hàng ngày):

```bash
#!/bin/bash
# backup.sh

BACKUP_DIR="/home/username/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup database
mysqldump -u username_gaqzzint_db -p[password] username_gaqzzint_db > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /home/username/public_html

# Xóa backup cũ (>30 ngày)
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Backup completed: $DATE"
```

Thêm vào crontab:
```bash
crontab -e

# Backup hàng ngày lúc 2:00 AM
0 2 * * * /home/username/backup.sh
```

---

## 11. TROUBLESHOOTING

### Lỗi: "Cannot connect to database"

```php
// Check:
1. DB_HOST = 'localhost' (correct cho Shared Hosting)
2. DB_USER = 'username_gaqzzint_db' (check username)
3. DB_PASS = đúng password
4. DB_NAME = 'username_gaqzzint_db' (thêm prefix username)

// Debug:
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}
```

### Lỗi: "404 Not Found" cho pages

- Kiểm tra file php đã upload chưa
- Kiểm tra .htaccess có gây conflict không
- Xoá .htaccess test và upload lại

### Lỗi: "Permission denied" khi ghi file

```bash
# Cấp quyền folder uploads
chmod 755 ~/public_html/uploads
chmod 755 ~/public_html/cache
```

### Lỗi: Session không lưu

```php
// Check trong config.php:
session_start();  // Phải gọi trước các output
session.save_path = '/tmp'  // Hoặc thư mục temp hợp lệ
```

### Lỗi: Timeout (30s)

```php
// Trong config.php:
set_time_limit(60);  // Tăng thời gian timeout

// Hoặc trong .htaccess:
php_value max_execution_time 60
```

---

## 12. THÔNG TIN LIÊN LẠC & HỖ TRỢ

- **Domain**: g4a4.qzz.io
- **Email Admin**: admin@g4a4.local
- **Database**: `gaqzzint_db`
- **SSH User**: username (cung cấp bởi hosting)

**Hosting provider info:**
- Có thể liên hệ support qua live chat/ticket
- Thường support 24/7

---

## 13. NEXT STEPS (SAU TRIỂN KHAI)

1. ✅ Kiểm tra toàn bộ chức năng
2. ✅ Tạo tài khoản cho các thành viên
3. ✅ Upload tài liệu mẫu
4. ✅ Cập nhật logo tổ
5. ✅ Tạo bài viết chào mừng
6. ✅ Chia sẻ URL cho các thành viên
7. ✅ Tạo backup định kỳ
8. ✅ Monitor logs và performance

---

**Chúc mừng! Website Tổ 4 đã sẵn sàng sử dụng! 🎉**

