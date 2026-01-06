<?php
// 500.php - Server Error
require_once 'includes/config.php';
include 'includes/header.php';
http_response_code(500);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div style="font-size: 8rem; margin-bottom: 20px;">⚠️</div>
            
            <h1 class="display-1 fw-bold">500</h1>
            <h2 class="mb-3">Lỗi Máy Chủ Nội Bộ</h2>
            
            <p class="lead text-muted mb-4">
                Xin lỗi, có lỗi xảy ra trên máy chủ. Vui lòng thử lại sau.
            </p>

            <div class="alert alert-danger" role="alert">
                <strong>⚠️ Lưu ý:</strong> Đội ngũ kỹ thuật của chúng tôi đã được thông báo về sự cố này.
            </div>

            <div class="btn-group" role="group">
                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary btn-lg">🏠 Trang Chủ</a>
                <button onclick="location.reload()" class="btn btn-secondary btn-lg">🔄 Tải Lại</button>
                <a href="<?= BASE_URL ?>/contact.php" class="btn btn-info btn-lg">💬 Liên Hệ</a>
            </div>

            <hr class="my-4">

            <p class="text-muted small">
                Nếu sự cố tiếp tục xảy ra, vui lòng <a href="<?= BASE_URL ?>/contact.php">báo cáo với chúng tôi</a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
