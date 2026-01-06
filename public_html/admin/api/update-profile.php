<?php
// update-profile.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if (!isLoggedIn()) {
    jsonResponse(false, 'Vui lòng đăng nhập');
}

$userId = $_SESSION['user_id'];
$fullName = sanitize($_POST['full_name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';

if (!$fullName || !$email) {
    jsonResponse(false, 'Tên và email là bắt buộc');
}

// Check email uniqueness
$emailCheck = $db->query("SELECT id FROM users WHERE email = '" . $db->real_escape_string($email) . "' AND id != $userId");
if ($emailCheck && $emailCheck->num_rows > 0) {
    jsonResponse(false, 'Email này đã được sử dụng');
}

$fullName = $db->real_escape_string($fullName);
$email = $db->real_escape_string($email);
$phone = $db->real_escape_string($phone);

$query = "UPDATE users SET full_name = '$fullName', email = '$email', phone = '$phone'";

if ($password) {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $query .= ", password = '" . $db->real_escape_string($hashedPassword) . "'";
}

$query .= " WHERE id = $userId";

if ($db->query($query)) {
    jsonResponse(true, 'Đã cập nhật hồ sơ');
} else {
    jsonResponse(false, 'Lỗi cập nhật');
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
