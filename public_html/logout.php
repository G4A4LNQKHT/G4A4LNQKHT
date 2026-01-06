<?php
/**
 * LOGOUT.PHP - ĐĂNG XUẤT
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Destroy session
destroySession();

// Redirect về trang chủ
redirect(BASE_URL . 'login.php?logout=1');

?>
