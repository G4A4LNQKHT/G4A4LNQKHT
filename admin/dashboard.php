<?php
/**
 * ADMIN/DASHBOARD.PHP - BẢN ĐIỀU KHIỂN QUẢN TRỊ
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Kiểm tra quyền admin
requireAdmin();

$page_title = 'Quản lý Tổ 4';

// Lấy thống kê
$users_result = Database::getInstance()->getConnection()->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
$total_users = $users_result->fetch_assoc()['total'];

$posts_result = Database::getInstance()->getConnection()->query("SELECT COUNT(*) as total FROM posts");
$total_posts = $posts_result->fetch_assoc()['total'];

$tasks_result = Database::getInstance()->getConnection()->query("SELECT COUNT(*) as total FROM tasks WHERE status != 'completed'");
$pending_tasks = $tasks_result->fetch_assoc()['total'];

$comments_result = Database::getInstance()->getConnection()->query("SELECT COUNT(*) as total FROM comments WHERE is_approved = 0");
$pending_comments = $comments_result->fetch_assoc()['total'];

?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="section-header">
    <div class="row align-items-center">
        <div class="col">
            <h1><i class="fas fa-cog"></i> Bảng Điều Khiển Quản Trị</h1>
            <p>Quản lý nội dung, bài viết, công việc và thành viên</p>
        </div>
        <div class="col-auto">
            <a href="<?php echo BASE_URL; ?>" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
</div>

<!-- Thống kê -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-box">
            <div class="stat-number"><?php echo $total_users; ?></div>
            <div class="stat-label">Thành viên</div>
            <a href="manage-users.php" class="btn btn-sm btn-outline-primary mt-2">Quản lý</a>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-box">
            <div class="stat-number"><?php echo $total_posts; ?></div>
            <div class="stat-label">Bài viết</div>
            <a href="manage-posts.php" class="btn btn-sm btn-outline-primary mt-2">Quản lý</a>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-box">
            <div class="stat-number"><?php echo $pending_tasks; ?></div>
            <div class="stat-label">Công việc chưa hoàn</div>
            <a href="manage-tasks.php" class="btn btn-sm btn-outline-primary mt-2">Quản lý</a>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="stat-box bg-warning text-white">
            <div class="stat-number"><?php echo $pending_comments; ?></div>
            <div class="stat-label">Bình luận chờ duyệt</div>
            <a href="manage-comments.php" class="btn btn-sm btn-light mt-2">Duyệt</a>
        </div>
    </div>
</div>

<!-- Menu quản lý -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-file-alt"></i> Bài viết
            </div>
            <div class="card-body">
                <p class="card-text">Tạo, sửa, xóa bài viết và thông báo</p>
            </div>
            <div class="card-footer bg-light">
                <a href="manage-posts.php" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-cog"></i> Quản lý
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-tasks"></i> Công việc
            </div>
            <div class="card-body">
                <p class="card-text">Tạo, giao, theo dõi công việc</p>
            </div>
            <div class="card-footer bg-light">
                <a href="manage-tasks.php" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-cog"></i> Quản lý
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-book"></i> Tài liệu
            </div>
            <div class="card-body">
                <p class="card-text">Quản lý tài liệu học tập và file</p>
            </div>
            <div class="card-footer bg-light">
                <a href="manage-data.php" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-cog"></i> Quản lý
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-comment"></i> Bình luận
            </div>
            <div class="card-body">
                <p class="card-text">Duyệt và quản lý bình luận</p>
            </div>
            <div class="card-footer bg-light">
                <a href="manage-comments.php" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-cog"></i> Quản lý
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Hành động gần đây -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i> Hoạt động gần đây
            </div>
            <div class="card-body">
                <?php
                $logs_result = Database::getInstance()->getConnection()->query(
                    "SELECT l.*, u.full_name FROM logs l 
                     LEFT JOIN users u ON l.user_id = u.id 
                     ORDER BY l.created_at DESC LIMIT 10"
                );
                
                if ($logs_result->num_rows > 0):
                    ?>
                    <div class="timeline">
                        <?php while ($log = $logs_result->fetch_assoc()): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <p class="mb-0">
                                    <strong><?php echo escape($log['full_name'] ?? 'System'); ?></strong> 
                                    <?php echo escape($log['action']); ?>
                                </p>
                                <small class="timeline-date">
                                    <i class="fas fa-clock"></i> 
                                    <?php echo formatDate($log['created_at'], 'd/m/Y H:i'); ?>
                                </small>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php
                else:
                    echo '<p class="text-muted">Chưa có hoạt động</p>';
                endif;
                ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Thông tin hệ thống
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>PHP Version</th>
                        <td><?php echo phpversion(); ?></td>
                    </tr>
                    <tr>
                        <th>Server</th>
                        <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td>
                    </tr>
                    <tr>
                        <th>Database</th>
                        <td>MySQL / MySQLi</td>
                    </tr>
                    <tr>
                        <th>URL Domain</th>
                        <td><?php echo BASE_URL; ?></td>
                    </tr>
                    <tr>
                        <th>User hiện tại</th>
                        <td><?php echo escape(getCurrentUsername()); ?></td>
                    </tr>
                    <tr>
                        <th>Thời gian</th>
                        <td><?php echo date('d/m/Y H:i:s'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
