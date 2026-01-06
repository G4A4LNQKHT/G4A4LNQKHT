<?php
// upload-file.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if (!isAdmin()) {
    jsonResponse(false, 'Không có quyền');
}

if (!isset($_FILES['file'])) {
    jsonResponse(false, 'Không có file được gửi');
}

$file = $_FILES['file'];
$title = sanitize($_POST['title'] ?? 'Không có tiêu đề');
$description = sanitize($_POST['description'] ?? '');
$category = sanitize($_POST['category'] ?? 'other');
$maxSize = 10 * 1024 * 1024; // 10MB

// Validate
if ($file['size'] > $maxSize) {
    jsonResponse(false, 'File không được vượt quá 10MB');
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(false, 'Lỗi upload');
}

// Create uploads directory if not exists
$uploadsDir = __DIR__ . '/../../uploads/files';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'file_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
$filepath = $uploadsDir . '/' . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $fileUrl = BASE_URL . '/uploads/files/' . $filename;
    
    // Save to database
    $title = $db->real_escape_string($title);
    $description = $db->real_escape_string($description);
    $fileUrl = $db->real_escape_string($fileUrl);
    $userId = $_SESSION['user_id'];
    
    $query = "INSERT INTO class_data (title, description, category, file_url, file_type, uploaded_by, created_at) 
              VALUES ('$title', '$description', '$category', '$fileUrl', '$ext', $userId, NOW())";
    
    if ($db->query($query)) {
        jsonResponse(true, 'Đã tải lên file');
    } else {
        jsonResponse(false, 'Lỗi lưu vào cơ sở dữ liệu');
    }
} else {
    jsonResponse(false, 'Lỗi lưu file');
}

function jsonResponse($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

function sanitize($value) {
    return trim(htmlspecialchars(stripslashes($value)));
}
?>
