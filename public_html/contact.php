<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $content = sanitize($_POST['content'] ?? '');

    // Validate
    if (!$name || !$email || !$subject || !$content) {
        $message = 'Vui lòng điền đầy đủ tất cả trường';
        $messageType = 'error';
    } else {
        // Save to database
        $name_esc = $db->real_escape_string($name);
        $email_esc = $db->real_escape_string($email);
        $subject_esc = $db->real_escape_string($subject);
        $content_esc = $db->real_escape_string($content);
        $userId = isLoggedIn() ? $_SESSION['user_id'] : null;

        $query = "INSERT INTO contact_messages (user_id, name, email, subject, content, created_at) 
                  VALUES ($userId, '$name_esc', '$email_esc', '$subject_esc', '$content_esc', NOW())";

        if ($db->query($query)) {
            // Send email to admin
            $adminEmail = 'admin@g4a4.qzz.io';
            $headers = "From: $email\r\nReply-To: $email\r\n";
            $emailSubject = "Liên hệ từ $name: $subject";
            $emailBody = "Tên: $name\nEmail: $email\nChủ đề: $subject\n\nNội dung:\n$content";

            mail($adminEmail, $emailSubject, $emailBody, $headers);

            $message = 'Cảm ơn bạn! Chúng tôi sẽ phản hồi trong sớm nhất';
            $messageType = 'success';
            $_POST = [];
        } else {
            $message = 'Lỗi gửi tin nhắn. Vui lòng thử lại';
            $messageType = 'error';
        }
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-5">
                    <h1 class="text-center mb-1">💬 Liên Hệ Với Chúng Tôi</h1>
                    <p class="text-center text-muted mb-4">Gửi tin nhắn, câu hỏi hoặc góp ý cho chúng tôi</p>

                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                            <?= $message ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" id="contactForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên Của Bạn</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Nguyễn Văn A" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Chủ Đề</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Chọn chủ đề...</option>
                                <option value="Câu hỏi chung" <?= isset($_POST['subject']) && $_POST['subject'] === 'Câu hỏi chung' ? 'selected' : '' ?>>❓ Câu hỏi chung</option>
                                <option value="Báo cáo sự cố" <?= isset($_POST['subject']) && $_POST['subject'] === 'Báo cáo sự cố' ? 'selected' : '' ?>>🐛 Báo cáo sự cố</option>
                                <option value="Góp ý tính năng" <?= isset($_POST['subject']) && $_POST['subject'] === 'Góp ý tính năng' ? 'selected' : '' ?>>💡 Góp ý tính năng</option>
                                <option value="Khác" <?= isset($_POST['subject']) && $_POST['subject'] === 'Khác' ? 'selected' : '' ?>>➕ Khác</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Nội Dung</label>
                            <textarea class="form-control" id="content" name="content" rows="5" placeholder="Nhập nội dung tin nhắn..." required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">📤 Gửi Tin Nhắn</button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <h6 class="mb-3">📍 Thông Tin Liên Hệ</h6>
                        <p class="mb-1">
                            <strong>Email:</strong><br>
                            <a href="mailto:admin@g4a4.qzz.io">admin@g4a4.qzz.io</a>
                        </p>
                        <p class="mb-1">
                            <strong>Lớp:</strong><br>
                            Tổ 4 - Lớp A4
                        </p>
                        <p>
                            <strong>Thời Gian Phản Hồi:</strong><br>
                            Trong 24 giờ
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Info Cards -->
            <div class="row g-3 mt-4">
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4>⚡ Nhanh Chóng</h4>
                            <p class="mb-0 small">Phản hồi trong 24 giờ</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card text-center">
                        <div class="card-body">
                            <h4>📞 Hỗ Trợ</h4>
                            <p class="mb-0 small">Luôn sẵn lòng giúp đỡ</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    // Optional: Add client-side validation
    const content = document.getElementById('content').value;
    if (content.length < 10) {
        e.preventDefault();
        showError('Nội dung phải có ít nhất 10 ký tự');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
