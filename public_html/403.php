<?php
// 403.php - Forbidden
require_once 'includes/config.php';
include 'includes/header.php';
http_response_code(403);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div style="font-size: 8rem; margin-bottom: 20px;">🚫</div>
            
            <h1 class="display-1 fw-bold">403</h1>
            <h2 class="mb-3">Truy Cập Bị Từ Chối</h2>
            
            <p class="lead text-muted mb-4">
                Bạn không có quyền truy cập vào trang này.
            </p>

            <div class="alert alert-warning" role="alert">
                <strong>ℹ️ Ghi chú:</strong> Bạn có thể cần đăng nhập hoặc có quyền Quản Trị để truy cập.
            </div>

            <div class="btn-group" role="group">
                <?php if (!isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-lg">🔐 Đăng Nhập</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/profile.php" class="btn btn-primary btn-lg">👤 Hồ Sơ</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary btn-lg">🏠 Trang Chủ</a>
                <a href="<?= BASE_URL ?>/contact.php" class="btn btn-info btn-lg">💬 Liên Hệ</a>
            </div>

            <hr class="my-4">

            <p class="text-muted small">
                Nếu bạn tin đây là lỗi, vui lòng <a href="<?= BASE_URL ?>/contact.php">liên hệ với quản trị viên</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
