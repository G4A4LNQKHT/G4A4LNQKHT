# CẤU TRÚC THƯ MỤC - WEBSITE TỔ 4

## Sơ đồ thư mục

```
g4a4-website/
├── public_html/                    # Thư mục chính (public, trỏ đến domain)
│   ├── index.php                   # Trang chủ
│   ├── members.php                 # Danh sách thành viên
│   ├── data.php                    # Tài liệu học tập
│   ├── tasks.php                   # Task list & lịch trình
│   ├── posts.php                   # Bài viết & thông báo
│   ├── contact.php                 # Liên hệ
│   ├── login.php                   # Trang đăng nhập
│   ├── logout.php                  # Xử lý đăng xuất
│   └── .htaccess                   # Cấu hình URL rewriting (tuỳ chọn)
│
├── includes/                       # Thư mục chia sẻ (không public)
│   ├── config.php                  # Kết nối database & cấu hình
│   ├── auth.php                    # Xử lý xác thực, session
│   ├── functions.php               # Hàm hỗ trợ chung
│   ├── header.php                  # Template header (navbar)
│   ├── footer.php                  # Template footer
│   └── db.php                      # Hàm database thao tác
│
├── admin/                          # Thư mục Admin (cần xác thực)
│   ├── dashboard.php               # Bảng điều khiển admin
│   ├── manage-posts.php            # Quản lý bài viết
│   ├── manage-tasks.php            # Quản lý task & lịch trình
│   ├── manage-data.php             # Quản lý dữ liệu/file
│   ├── manage-comments.php         # Quản lý bình luận
│   └── api/                        # API endpoints (xử lý AJAX)
│       ├── add-post.php
│       ├── edit-post.php
│       ├── delete-post.php
│       ├── add-comment.php
│       └── add-task.php
│
├── assets/                         # Tài nguyên tĩnh
│   ├── css/
│   │   ├── style.css               # Style chính
│   │   └── responsive.css          # Style responsive
│   ├── js/
│   │   ├── main.js                 # JavaScript chính
│   │   ├── form-handler.js         # Xử lý form
│   │   └── admin.js                # JavaScript admin
│   └── images/
│       ├── logo.png                # Logo tổ 4
│       └── placeholder.png         # Ảnh placeholder
│
├── sql/                            # Database scripts
│   └── init_database.sql           # Script khởi tạo database
│
├── config/                         # Cấu hình
│   └── database.example.php        # Ví dụ cấu hình database
│
├── README.md                       # Hướng dẫn chung
├── DEPLOYMENT.md                   # Hướng dẫn triển khai
├── DATABASE.md                     # Sơ đồ database
└── .gitignore                      # Ignore files
```

## Chi tiết từng thư mục

### 📁 public_html/
- **Vị trí**: Là thư mục root của domain g4a4.qzz.io
- **Nội dung**: Các trang PHP công khai, khách có thể truy cập trực tiếp
- **Bảo mật**: Chỉ chứa các file cần hiển thị công khai

### 📁 includes/
- **Vị trí**: Nằm ngoài public_html (không truy cập trực tiếp)
- **Nội dung**: Các file backend, kết nối database, xác thực
- **Bảo mật**: Cao, khách không thể truy cập trực tiếp

### 📁 admin/
- **Vị trị**: Thư mục quản trị riêng
- **Bảo mật**: Yêu cầu đăng nhập và kiểm tra quyền admin
- **Nội dung**: Dashboard, quản lý content, AJAX API

### 📁 assets/
- **Nội dung**: CSS, JavaScript, hình ảnh
- **Tối ưu**: Dễ quản lý và cache trên trình duyệt

---

## Quy ước đặt tên file

- **PHP pages**: `page-name.php` (ví dụ: `members.php`, `manage-posts.php`)
- **CSS files**: `style-name.css` (ví dụ: `style.css`, `responsive.css`)
- **JavaScript**: `script-name.js` (ví dụ: `main.js`, `admin.js`)
- **Includes**: Đặt trong `includes/` với prefix rõ ràng
- **Database functions**: `db.php` hoặc `db-functions.php`

---

## Cách xây dựng trên Shared Hosting

```
Shared Hosting cấu trúc thường là:
/home/username/
├── public_html/              ← Domain chính (g4a4.qzz.io trỏ vào đây)
└── private_html/             ← Thư mục private (không web accessible)

Cách tổ chức:
public_html/
├── index.php
├── .htaccess
├── assets/ (CSS, JS, images)
└── admin/

../private_html/
├── includes/
├── sql/
└── config/

Hoặc toàn bộ để trong public_html:
public_html/
├── index.php
├── includes/
├── assets/
├── admin/
└── sql/
```

---

## Mô tả chức năng từng file

| File | Chức năng |
|------|----------|
| `config.php` | Kết nối DB, hằng số, cấu hình toàn cục |
| `auth.php` | Session, login, logout, kiểm tra quyền |
| `functions.php` | Hàm lấy dữ liệu, format, tiện ích |
| `db.php` | Thực thi query, hàm database |
| `header.php` | Template header, navbar, menu |
| `footer.php` | Template footer, link chân trang |

