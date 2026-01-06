<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : (isLoggedIn() ? $_SESSION['user_id'] : null);

if (!$userId) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult ? $userResult->fetch_assoc() : null;

if (!$user) {
    header('Location: ' . BASE_URL . 'members.php');
    exit;
}

// Get user's posts
$stmt = $db->prepare("SELECT * FROM posts WHERE author_id = ? AND status = 'published' ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param('i', $userId);
$stmt->execute();
$postsResult = $stmt->get_result();
$posts = [];
if ($postsResult) {
    while ($row = $postsResult->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Check if viewing own profile
$isOwnProfile = isLoggedIn() && $_SESSION['user_id'] === $userId;

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Profile Header -->
    <div class="row mb-4">
        <div class="col-md-3 text-center">
            <?php if ($user['avatar']): ?>
                <img src="<?= escape($user['avatar']) ?>" alt="<?= escape($user['full_name']) ?>" class="rounded-circle img-fluid mb-3" style="max-width: 200px; width: 100%;">
            <?php else: ?>
                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" style="width: 200px; height: 200px; font-size: 80px;">
                    👤
                </div>
            <?php endif; ?>
            
            <?php if ($isOwnProfile): ?>
                <form id="avatarForm" class="d-grid gap-2">
                    <input type="file" id="avatarInput" class="form-control form-control-sm" accept="image/*" onchange="uploadAvatar()">
                    <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('avatarInput').click()">📸 Đổi Ảnh</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2><?= escape($user['full_name']) ?></h2>
                            <p class="text-muted">@<?= escape($user['username']) ?></p>
                        </div>
                        <?php if ($user['is_admin']): ?>
                            <span class="badge bg-danger">👑 Quản Trị</span>
                        <?php else: ?>
                            <?php if ($user['class_position']): ?>
                                <span class="badge bg-info"><?= escape($user['class_position']) ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <strong>Email:</strong>
                            <p><?= escape($user['email']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Điện Thoại:</strong>
                            <p><?= escape($user['phone'] ?? 'Chưa cập nhật') ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Ngày Tham Gia:</strong>
                            <p><?= formatDate($user['created_at']) ?></p>
                        </div>
                    </div>

                    <?php if ($isOwnProfile): ?>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">✏️ Chỉnh Sửa</button>
                            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-danger">🚪 Đăng Xuất</a>
                        </div>
                    <?php else: ?>
                        <button class="btn btn-sm btn-primary" onclick="sendMessage(<?= $userId ?>)">💬 Nhắn Tin</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- User's Posts -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">📝 Bài Viết của <?= escape($user['full_name']) ?> (<?= count($posts) ?>)</h5>
        </div>
        <div class="card-body">
            <?php if (count($posts) > 0): ?>
                <div class="row g-3">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <?php if ($post['image']): ?>
                                    <img src="<?= escape($post['image']) ?>" class="card-img-top" alt="<?= escape($post['title']) ?>" style="height: 150px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="card-title"><?= escape($post['title']) ?></h6>
                                    <p class="card-text text-muted small"><?= substr(strip_tags($post['content']), 0, 80) ?>...</p>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">👁️ <?= $post['view_count'] ?> lượt xem</small>
                                        <a href="<?= BASE_URL ?>/posts.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">Xem</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-4">Chưa có bài viết nào</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Profile Modal (for own profile) -->
<?php if ($isOwnProfile): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Chỉnh Sửa Hồ Sơ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editProfileForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tên Đầy Đủ</label>
                            <input type="text" name="full_name" class="form-control" value="<?= escape($user['full_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= escape($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Điện Thoại</label>
                            <input type="tel" name="phone" class="form-control" value="<?= escape($user['phone'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật Khẩu Mới (để trống nếu không thay đổi)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">💾 Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        fetch('<?= BASE_URL ?>/admin/api/update-profile.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSuccess('Cập nhật hồ sơ thành công');
                setTimeout(() => location.reload(), 1000);
            } else {
                showError(data.message || 'Lỗi cập nhật');
            }
        });
    });

    function uploadAvatar() {
        const fileInput = document.getElementById('avatarInput');
        const formData = new FormData();
        formData.append('avatar', fileInput.files[0]);

        fetch('<?= BASE_URL ?>/admin/api/upload-avatar.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSuccess('Đã cập nhật ảnh');
                setTimeout(() => location.reload(), 500);
            } else {
                showError(data.message || 'Lỗi tải ảnh');
            }
        });
    }

    function sendMessage(userId) {
        alert('Tính năng nhắn tin sẽ được thêm vào sau');
    }
    </script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
