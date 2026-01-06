<?php
// upload-avatar.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Vui lòng đăng nhập');
}

$userId = $_SESSION['user_id'];

if (!isset($_FILES['avatar'])) {
    jsonResponse(false, 'Không có file được gửi');
}

$file = $_FILES['avatar'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate
if (!in_array($file['type'], $allowed)) {
    jsonResponse(false, 'Chỉ cho phép ảnh (JPG, PNG, GIF, WebP)');
}

if ($file['size'] > $maxSize) {
    jsonResponse(false, 'File không được vượt quá 5MB');
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(false, 'Lỗi upload: ' . $file['error']);
}

// Create uploads directory if not exists
$uploadsDir = __DIR__ . '/../../uploads/avatars';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
$filepath = $uploadsDir . '/' . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $avatarUrl = BASE_URL . 'uploads/avatars/' . $filename;
    
    // Update database
    $db->query("UPDATE users SET avatar = '" . $db->real_escape_string($avatarUrl) . "' WHERE id = $userId");
    
    jsonResponse(true, 'Đã cập nhật ảnh');
} else {
    jsonResponse(false, 'Lỗi lưu file');
}

function jsonResponse($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}
?>
