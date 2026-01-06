<?php
// download-file.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

if (!isLoggedIn()) {
    http_response_code(403);
    exit('Vui lòng đăng nhập');
}

$fileId = (int)($_GET['id'] ?? 0);

if (!$fileId) {
    http_response_code(400);
    exit('ID file không hợp lệ');
}

$result = $db->query("SELECT * FROM class_data WHERE id = $fileId");
$file = $result ? $result->fetch_assoc() : null;

if (!$file) {
    http_response_code(404);
    exit('File không tìm thấy');
}

// Increment download count
$db->query("UPDATE class_data SET download_count = download_count + 1 WHERE id = $fileId");

// Get actual file path from URL
$fileUrlPath = parse_url($file['file_url'], PHP_URL_PATH);
$actualPath = __DIR__ . '/../../' . trim($fileUrlPath, '/');

// Security check
$realpath = realpath($actualPath);
$basedir = realpath(__DIR__ . '/../../uploads');

if ($realpath === false || strpos($realpath, $basedir) !== 0) {
    http_response_code(403);
    exit('Không được phép truy cập file này');
}

if (!file_exists($realpath)) {
    http_response_code(404);
    exit('File không tìm thấy');
}

// Generate download filename
$downloadName = $file['title'] . '.' . $file['file_type'];

// Send file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . filesize($realpath));
header('Cache-Control: no-cache, no-store, must-revalidate');

readfile($realpath);
exit;
?>
