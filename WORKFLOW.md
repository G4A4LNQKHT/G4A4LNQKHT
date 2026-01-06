# LUỒNG HOẠT ĐỘNG WEBSITE - TỔ 4

## 1. LUỒNG TỔNG QUÁT

```
Khách truy cập              Thành viên đã login         Admin
       │                           │                     │
       ▼                           ▼                     ▼
  index.php          ┌────────────────────┐      admin/dashboard.php
  posts.php          │   Kiểm tra session  │      admin/manage-posts.php
  members.php        │   $_SESSION['user'] │      admin/manage-tasks.php
  data.php           └────────────────────┘      admin/manage-data.php
  tasks.php                    │
  contact.php                  ▼
                      Hiển thị nội dung
                      riêng cho thành viên
                             │
                             ▼
                      Có quyền comment,
                      tạo task, xem dữ liệu
                             │
                             ▼
                      API call (AJAX)
                      add-comment.php
                      add-task.php
```

---

## 2. LUỒNG ĐĂNG NHẬP (Login Flow)

```
┌─────────────────────┐
│  Khách vào login.php│
└──────────┬──────────┘
           │
           ▼
    ┌──────────────────────┐
    │  Form POST gửi lên   │
    │ username + password  │
    └──────────┬───────────┘
               │
               ▼
    ┌──────────────────────────────────┐
    │  includes/auth.php               │
    │  - Kiểm tra username tồn tại     │
    │  - So sánh password (bcrypt)     │
    └──────────┬───────────────────────┘
               │
        ┌──────┴──────┐
        │             │
    ✓ Đúng       ✗ Sai
        │             │
        ▼             ▼
    $_SESSION     Báo lỗi
    ['user_id']   "Sai username/pass"
    ['username']  Quay lại login.php
    ['is_admin']
        │
        ▼
    Redirect →
    index.php
    (hoặc admin/dashboard.php)
```

---

## 3. LUỒNG XEM BÀI VIẾT (Posts Flow)

```
┌────────────────────┐
│  Khách vào posts.php│
└──────────┬─────────┘
           │
           ▼
    ┌──────────────────────────────┐
    │  Lấy tất cả bài viết status  │
    │  = 'published' từ DB         │
    │  SELECT * FROM posts WHERE   │
    │  status = 'published'        │
    └──────────┬───────────────────┘
               │
               ▼
    ┌─────────────────────────────┐
    │  Loop qua từng bài viết     │
    │  - Hiển thị tiêu đề, nội dung│
    │  - Hiển thị tác giả          │
    │  - Hiển thị ngày tạo         │
    └──────────┬──────────────────┘
               │
               ▼
    ┌─────────────────────────────┐
    │  Nếu đã login:              │
    │  - Hiển thị form bình luận   │
    │  - Hiển thị nút sửa/xóa     │
    │    (nếu là tác giả/admin)   │
    └──────────┬──────────────────┘
               │
               ▼
    ┌─────────────────────────────┐
    │  Lấy bình luận từ DB        │
    │  SELECT * FROM comments     │
    │  WHERE post_id = ?          │
    └──────────┬──────────────────┘
               │
               ▼
    ┌─────────────────────────────┐
    │  Hiển thị bình luận         │
    │  (nếu is_approved = 1)      │
    └─────────────────────────────┘
```

---

## 4. LUỒNG ADMIN ĐĂNG BÀI VIẾT

```
┌──────────────────────────┐
│  Admin vào admin/       │
│  manage-posts.php       │
└──────────┬───────────────┘
           │
           ▼ (POST)
    ┌────────────────────────────┐
    │  admin/api/add-post.php    │
    │  - Kiểm tra is_admin       │
    │  - Validate input          │
    │  - Upload ảnh (nếu có)     │
    └──────────┬─────────────────┘
               │
               ▼
    ┌────────────────────────────┐
    │  INSERT INTO posts         │
    │  (author_id, title,        │
    │   content, category,       │
    │   image, status)           │
    └──────────┬─────────────────┘
               │
               ▼
    ┌────────────────────────────┐
    │  Ghi log (logs table)      │
    │  "User X đã tạo bài viết"  │
    └──────────┬─────────────────┘
               │
               ▼
    ┌────────────────────────────┐
    │  JSON Response             │
    │  {"status": "success"}     │
    │  JavaScript cập nhật UI    │
    └────────────────────────────┘
```

---

## 5. LUỒNG QUẢN LÝ TASK (Task Management Flow)

```
                   ┌─────────────────────────┐
                   │  admin/manage-tasks.php │
                   └──────────────┬──────────┘
                                  │
                  ┌───────────────┼────────────────┐
                  │               │                │
                  ▼               ▼                ▼
            TẠO TASK       CHỈNH SỬA TASK    HOÀN THÀNH TASK
                  │               │                │
                  │               │                │
        api/add-task.php    api/edit-task.php  api/complete-task.php
                  │               │                │
                  ▼               ▼                ▼
            INSERT INTO      UPDATE tasks       UPDATE tasks
            tasks            SET status =       SET status =
                             'in_progress'      'completed'
                  │               │                │
                  └───────────────┼────────────────┘
                                  │
                                  ▼
                    Ghi log + Response JSON
                          (AJAX update)
                                  │
                                  ▼
                    JavaScript cập nhật bảng
                    hoặc danh sách task
```

---

## 6. LUỒNG COMMENT (Comment Flow)

```
┌──────────────────┐
│  Form bình luận  │
│  posts.php       │
└────────┬─────────┘
         │
         ▼ (POST AJAX)
    ┌──────────────────────────┐
    │ admin/api/add-comment.php│
    │ - Kiểm tra login         │
    │ - Validate nội dung      │
    └────────┬─────────────────┘
             │
             ▼
    ┌──────────────────────────┐
    │ INSERT INTO comments     │
    │ (post_id, author_id,    │
    │  content, is_approved)   │
    └────────┬─────────────────┘
             │
             ▼
    ┌──────────────────────────┐
    │ Nếu từ admin: tự duyệt   │
    │ Nếu từ member: cần admin │
    │ duyệt (is_approved=0)    │
    └────────┬─────────────────┘
             │
             ▼
    ┌──────────────────────────┐
    │ JSON Response            │
    │ JavaScript hiển thị      │
    │ bình luận mới            │
    └──────────────────────────┘
```

---

## 7. LUỒNG CẤU TRÚC THỬPUBLIC (Công khai)

```
┌─────────────────────────────────────────────────────┐
│              TRANG CÔNG KHAI                        │
│         (Khách vào không cần login)                 │
└────────────┬──────────────────────────┬─────────────┘
             │                          │
    ┌────────▼───────────┐   ┌──────────▼─────────┐
    │   index.php        │   │    posts.php       │
    ├────────────────────┤   ├────────────────────┤
    │ - Logo, slide       │   │ - Bài viết công khai
    │ - Giới thiệu tổ 4   │   │ - Bình luận công khai
    │ - Thông báo mới     │   │ - Nút "Đăng nhập"  │
    │ - Quick link        │   │   để comment       │
    └────────┬────────────┘   └────────┬───────────┘
             │                         │
             └────────────┬────────────┘
                          │
                   ┌──────▼────────┐
                   │  data.php     │
                   ├───────────────┤
                   │ - File công khai
                   │ - Links        │
                   │ - Không cần login
                   └───────────────┘

                   ┌──────────────────┐
                   │  members.php     │
                   ├──────────────────┤
                   │ - Danh sách thành viên
                   │ - Ảnh, tên, vị trí
                   │ - Không cần login │
                   └──────────────────┘

                   ┌──────────────────┐
                   │  contact.php     │
                   ├──────────────────┤
                   │ - Form liên hệ   │
                   │ - Gửi email      │
                   │ - Không cần login │
                   └──────────────────┘
```

---

## 8. LUỒNG LOGIN/LOGOUT

```
┌────────────────────────────────┐
│    Nhấn "Đăng nhập"            │
│    hoặc truy cập login.php     │
└────────────┬───────────────────┘
             │
             ▼
    ┌─────────────────────────┐
    │   login.php             │
    │ - Form POST             │
    │   (username, password)  │
    └────────┬────────────────┘
             │
             ▼
    ┌─────────────────────────────────┐
    │  includes/auth.php              │
    │  - password_verify()            │
    │  - Tạo $_SESSION['user_id']    │
    │  - Tạo $_SESSION['is_admin']    │
    │  - setcookie() nếu cần          │
    └────────┬────────────────────────┘
             │
             ▼
    ┌─────────────────────────────────┐
    │  Redirect:                      │
    │  - Admin → admin/dashboard.php  │
    │  - Member → index.php           │
    └─────────────────────────────────┘

-------- LOGOUT --------

┌────────────────────────────────┐
│    Nhấn "Đăng xuất"            │
│    Vào logout.php              │
└────────────┬───────────────────┘
             │
             ▼
    ┌──────────────────────┐
    │  logout.php          │
    │  - session_destroy() │
    │  - unset($_SESSION)  │
    │  - Xóa cookie (nếu)  │
    └────────┬─────────────┘
             │
             ▼
    ┌──────────────────────┐
    │  Redirect            │
    │  login.php           │
    │  hoặc index.php      │
    └──────────────────────┘
```

---

## 9. LUỒNG SESSION & XỬ LÝ QUYỀN

```
┌──────────────────────────────────┐
│  Mỗi request PHP                 │
└────────────┬─────────────────────┘
             │
             ▼
    ┌──────────────────────────────┐
    │  session_start() (config.php)│
    │  Kiểm tra $_SESSION          │
    └────────┬─────────────────────┘
             │
        ┌────┴─────────────────────┐
        │                          │
    ✓ Có session              ✗ Không
        │                          │
        ▼                          ▼
    $user_id =          Cho phép view công khai
    $_SESSION           Redirect login nếu
    ['user_id']         cần login

    $is_admin =
    $_SESSION
    ['is_admin']

        │
        ▼
    ┌─────────────────────────────┐
    │ Kiểm tra quyền:             │
    │ if ($is_admin) {            │
    │   Cho xem admin panel       │
    │   Cho edit/delete content   │
    │ } else {                    │
    │   Chỉ view + comment        │
    │   Không edit content khác   │
    │ }                           │
    └─────────────────────────────┘
```

---

## 10. LUỒNG TỐI ƯU SHARED HOSTING

```
┌─ Optimization Flow ─────────────────┐
│                                     │
│  1. Kết nối DB một lần (config.php)│
│     - Reuse connection              │
│                                     │
│  2. Cache query kết quả             │
│     - Lưu vào $_SESSION, cookie     │
│                                     │
│  3. Giảm query database             │
│     - Dùng JOIN thay vì N+1         │
│     - Limit kết quả (pagination)    │
│                                     │
│  4. Gzip output                     │
│     - ob_start("ob_gzhandler")      │
│                                     │
│  5. Lazy loading ảnh                │
│     - loading="lazy"                │
│                                     │
│  6. Minimize CSS/JS                 │
│     - Tập hợp vào 1 file            │
│                                     │
│  7. Static files caching            │
│     - .htaccess headers             │
│                                     │
└─────────────────────────────────────┘
```

---

## 11. CHUYỂN TIẾP TRANG (Navigation)

```
┌─────────────────────────────────────────────────┐
│           NAVIGATION BAR (Header)               │
│  Logo | Home | Members | Data | Tasks | Posts   │
│                              │ Contact | Login  │
└─────────────────────────────────────────────────┘

Khi LOGIN:
┌─────────────────────────────────────────────────┐
│  Logo | Home | Members | Data | Tasks | Posts   │
│  Contact | [username ▼] | [Admin Dashboard]    │
│                    │ (dropdown)                 │
│                    ├─ Profile                  │
│                    ├─ Settings                 │
│                    └─ Logout                   │
└─────────────────────────────────────────────────┘

Admin panel: Thêm menu "Quản lý"
├─ Quản lý bài viết
├─ Quản lý task
├─ Quản lý dữ liệu
└─ Quản lý bình luận
```

---

## Tóm tắt luồng chính

| Luồng | Entry Point | Validation | Output |
|-------|------------|------------|--------|
| Đăng nhập | `login.php` | Username/password | `$_SESSION` |
| Xem bài viết | `posts.php` | Public posts | HTML render |
| Bình luận | `api/add-comment.php` | Need login | JSON response |
| Admin quản lý | `admin/dashboard.php` | `is_admin=1` | Admin panel |
| Task management | `admin/manage-tasks.php` | Admin only | Task list + CRUD |
| Tải file | `api/download.php` | Member login | File stream |

