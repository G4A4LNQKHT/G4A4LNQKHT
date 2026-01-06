<?php
// promote-user.php
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

if ($db->query("UPDATE users SET is_admin = 1 WHERE id = $userId")) {
    jsonResponse(true, 'Đã nâng lên Quản Trị');
} else {
    jsonResponse(false, 'Lỗi cập nhật');
}

function jsonResponse($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}
?>
