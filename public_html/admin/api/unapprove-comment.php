<?php
// unapprove-comment.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if (!isAdmin()) {
    jsonResponse(false, 'Không có quyền');
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = (int)($data['comment_id'] ?? 0);

if (!$commentId) {
    jsonResponse(false, 'ID bình luận không hợp lệ');
}

if ($db->query("UPDATE comments SET is_approved = 0 WHERE id = $commentId")) {
    jsonResponse(true, 'Đã bỏ duyệt');
} else {
    jsonResponse(false, 'Lỗi cập nhật');
}

function jsonResponse($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}
?>
