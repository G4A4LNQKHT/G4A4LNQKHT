<?php
// 404.php - Not Found
require_once 'includes/config.php';
include 'includes/header.php';
http_response_code(404);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div style="font-size: 8rem; margin-bottom: 20px;">🔍</div>
            
            <h1 class="display-1 fw-bold">404</h1>
            <h2 class="mb-3">Không Tìm Thấy Trang</h2>
            
            <p class="lead text-muted mb-4">
                Xin lỗi, trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.
            </p>

            <div class="btn-group" role="group">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-lg">🏠 Trang Chủ</a>
                <a href="<?= BASE_URL ?>/posts.php" class="btn btn-secondary btn-lg">📝 Bài Viết</a>
                <a href="<?= BASE_URL ?>/members.php" class="btn btn-info btn-lg">👥 Thành Viên</a>
            </div>

            <hr class="my-4">

            <p class="text-muted small">
                Nếu bạn tin đây là lỗi, vui lòng <a href="<?= BASE_URL ?>/contact.php">liên hệ với chúng tôi</a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
