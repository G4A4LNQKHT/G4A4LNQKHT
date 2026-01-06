<?php
/**
 * AUTH.PHP - XỬ LÝ XÁC THỰC VÀ SESSION
 * Quản lý login, logout, kiểm tra quyền
 */

// Lưu ý: require_once 'config.php' sẽ được gọi ở file sử dụng

/**
 * Kiểm tra user có phải admin không
 */
function requireAdmin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'login.php?redirect=admin');
    }
    
    if (!isAdmin()) {
        die('Bạn không có quyền truy cập trang này!');
    }
}

/**
 * Kiểm tra user đã login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'login.php');
    }
}

/**
 * Xác thực đăng nhập
 * @param string $username
 * @param string $password
 * @return array|false
 */
function authenticateUser($username, $password) {
    global $db;
    
    $username = sanitize($username);
    
    // Lấy user từ database
    $sql = "SELECT id, username, email, password, full_name, is_admin 
            FROM users 
            WHERE username = ? AND status = 'active'";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $user = $result->fetch_assoc();
    
    // Kiểm tra password (bcrypt)
    if (!password_verify($password, $user['password'])) {
        return false;
    }
    
    // Xóa password khỏi array trước khi return
    unset($user['password']);
    
    return $user;
}

/**
 * Tạo session cho user
 * @param array $user
 */
function createSession($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['is_admin'] = $user['is_admin'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    // Ghi log
    logAction($user['id'], 'Đăng nhập', 'User đã đăng nhập');
}

/**
 * Xóa session (logout)
 */
function destroySession() {
    if (isset($_SESSION['user_id'])) {
        logAction($_SESSION['user_id'], 'Đăng xuất', 'User đã đăng xuất');
    }
    
    session_destroy();
    $_SESSION = [];
}

/**
 * Tạo token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Kiểm tra token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Tạo password hash (bcrypt)
 * Sử dụng khi tạo user mới
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Tạo user mới (chỉ admin)
 * @return array
 */
function createUser($data) {
    global $db;
    
    // Validate dữ liệu
    $errors = [];
    
    if (empty($data['username'])) {
        $errors[] = 'Username không được để trống';
    }
    if (empty($data['password'])) {
        $errors[] = 'Password không được để trống';
    }
    if (empty($data['full_name'])) {
        $errors[] = 'Họ tên không được để trống';
    }
    if (empty($data['email'])) {
        $errors[] = 'Email không được để trống';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Kiểm tra username tồn tại
    $stmt = Database::getInstance()->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param('s', $data['username']);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'errors' => ['Username đã tồn tại']];
    }
    
    // Tạo user
    $password_hash = hashPassword($data['password']);
    $is_admin = isset($data['is_admin']) ? 1 : 0;
    
    $sql = "INSERT INTO users (username, email, password, full_name, class_position, is_admin) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param(
        'sssssi',
        $data['username'],
        $data['email'],
        $password_hash,
        $data['full_name'],
        $data['class_position'] ?? null,
        $is_admin
    );
    
    if ($stmt->execute()) {
        return ['success' => true, 'user_id' => Database::getInstance()->getLastInsertId()];
    } else {
        return ['success' => false, 'errors' => ['Lỗi tạo user']];
    }
}

/**
 * Cập nhật profile user
 */
function updateUserProfile($user_id, $data) {
    global $db;
    
    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('sssi', $data['full_name'], $data['email'], $data['phone'], $user_id);
    
    return $stmt->execute();
}

/**
 * Thay đổi password
 */
function changePassword($user_id, $old_password, $new_password) {
    global $db;
    
    // Lấy password cũ từ database
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Kiểm tra password cũ
    if (!password_verify($old_password, $result['password'])) {
        return ['success' => false, 'error' => 'Password cũ không đúng'];
    }
    
    // Update password mới
    $new_password_hash = hashPassword($new_password);
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('si', $new_password_hash, $user_id);
    
    if ($stmt->execute()) {
        logAction($user_id, 'Thay đổi password', 'User đã thay đổi password');
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'Lỗi khi thay đổi password'];
    }
}

?>
