<?php
/**
 * INDEX.PHP - TRANG CHỦ
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$page_title = 'Trang chủ';

// Lấy bài viết mới nhất
$posts = getAllPosts(5, 0);

?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<!-- Hero Section -->
<div class="section-header mb-5">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 style="font-size: 3rem; font-weight: 800;">Tổ 4 - Lớp A4</h1>
            <p style="font-size: 1.3rem; margin-bottom: 1rem;">Cộng đồng học tập, chia sẻ và phát triển cùng nhau</p>
            <p style="opacity: 0.9;">
                Nơi lưu trữ tài liệu, chia sẻ kỷ niệm, quản lý công việc và liên lạc
            </p>
            
            <?php if (!isLoggedIn()): ?>
            <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-light btn-lg" style="margin-top: 1rem;">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập để tham gia
            </a>
            <?php else: ?>
            <div style="margin-top: 1rem;">
                <span class="badge badge-light" style="font-size: 1rem; padding: 0.75rem 1.5rem;">
                    Chào mừng, <?php echo escape(getCurrentUsername()); ?>!
                </span>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4 text-center">
            <div style="font-size: 120px; color: rgba(255,255,255,0.3);">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
</div>

<!-- Thông tin nhanh -->
<div class="row mb-5">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                <h5 class="card-title mt-2">Thành viên</h5>
                <p class="card-text">Danh sách các thành viên tổ 4</p>
                <a href="<?php echo BASE_URL; ?>members.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-right"></i> Xem
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-book" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                <h5 class="card-title mt-2">Tài liệu</h5>
                <p class="card-text">Tài liệu học tập và đề thi</p>
                <a href="<?php echo BASE_URL; ?>data.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-right"></i> Xem
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-tasks" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                <h5 class="card-title mt-2">Công việc</h5>
                <p class="card-text">Lịch trình và task list</p>
                <a href="<?php echo BASE_URL; ?>tasks.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-right"></i> Xem
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-newspaper" style="font-size: 2.5rem; color: var(--primary-color);"></i>
                <h5 class="card-title mt-2">Tin tức</h5>
                <p class="card-text">Thông báo và bài viết mới</p>
                <a href="<?php echo BASE_URL; ?>posts.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-right"></i> Xem
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bài viết mới -->
<h2 class="mb-4"><i class="fas fa-newspaper"></i> Bài viết mới nhất</h2>

<?php if (!empty($posts)): ?>
<div class="row">
    <?php foreach ($posts as $post): ?>
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <?php if (!empty($post['image'])): ?>
            <img src="<?php echo escape($post['image']); ?>" class="card-img-top" alt="<?php echo escape($post['title']); ?>" style="height: 200px; object-fit: cover;">
            <?php else: ?>
            <div style="height: 200px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                <i class="fas fa-file-alt"></i>
            </div>
            <?php endif; ?>
            
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge" style="background-color: var(--primary-color);">
                        <?php 
                        $categories = [
                            'news' => 'Tin tức',
                            'memory' => 'Kỷ niệm',
                            'announcement' => 'Thông báo',
                            'other' => 'Khác'
                        ];
                        echo $categories[$post['category']] ?? $post['category'];
                        ?>
                    </span>
                </div>
                
                <h5 class="card-title">
                    <a href="<?php echo BASE_URL; ?>posts.php?id=<?php echo $post['id']; ?>" style="color: inherit; text-decoration: none;">
                        <?php echo escape(substr($post['title'], 0, 50)); ?>...
                    </a>
                </h5>
                
                <p class="card-text text-muted">
                    <?php echo escape(substr($post['content'], 0, 100)); ?>...
                </p>
                
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-user"></i> <?php echo escape($post['author_name'] ?? 'Unknown'); ?>
                    </small>
                    <small class="text-muted">
                        <i class="fas fa-calendar"></i> <?php echo formatDate($post['created_at'], 'd/m/Y'); ?>
                    </small>
                </div>
            </div>
            
            <div class="card-footer bg-light">
                <a href="<?php echo BASE_URL; ?>posts.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-eye"></i> Xem bài viết
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="text-center mb-5">
    <a href="<?php echo BASE_URL; ?>posts.php" class="btn btn-primary btn-lg">
        <i class="fas fa-newspaper"></i> Xem tất cả bài viết
    </a>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> Chưa có bài viết nào. Hãy quay lại sau!
</div>
<?php endif; ?>

<!-- Thông tin tổ -->
<div class="card bg-light border-top-primary">
    <div class="card-body">
        <h5 class="card-title">
            <i class="fas fa-lightbulb"></i> Về Tổ 4
        </h5>
        <p class="card-text">
            Tổ 4 là một cộng đồng gồm các bạn học sinh lớp A4, có mục đích chia sẻ tài liệu học tập, 
            lưu giữ những kỷ niệm đáng quý, và hỗ trợ lẫn nhau trong quá trình học tập và phát triển.
        </p>
        <p class="card-text">
            Website này được xây dựng nhằm tạo một nền tảng để các thành viên có thể giao tiếp, 
            chia sẻ công việc, quản lý lịch trình một cách hiệu quả.
        </p>
        <div class="mt-3">
            <a href="<?php echo BASE_URL; ?>members.php" class="btn btn-outline-primary">
                <i class="fas fa-users"></i> Tìm hiểu thêm
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
