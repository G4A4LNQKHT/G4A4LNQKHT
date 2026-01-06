<?php
// delete-user.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if (!isAdmin()) {
    jsonResponse(false, 'Không có quyền');
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = (int)($data['user_id'] ?? 0);

if (!$userId) {
    jsonResponse(false, 'ID người dùng không hợp lệ');
}

// Prevent deleting self
if (isLoggedIn() && $_SESSION['user_id'] === $userId) {
    jsonResponse(false, 'Không thể xóa chính mình');
}

// Delete user (soft delete recommended)
if ($db->query("UPDATE users SET status = 'deleted' WHERE id = $userId")) {
    jsonResponse(true, 'Đã xóa thành viên');
} else {
    jsonResponse(false, 'Lỗi xóa');
}

function jsonResponse($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}
?>
