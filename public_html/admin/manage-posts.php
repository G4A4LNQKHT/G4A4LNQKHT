<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check admin permission
if (!isAdmin()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query conditions
$where = "1=1";
if ($search) $where .= " AND (title LIKE '%" . $db->real_escape_string($search) . "%' OR content LIKE '%" . $db->real_escape_string($search) . "%')";
if ($category) $where .= " AND category = '" . $db->real_escape_string($category) . "'";
if ($status) $where .= " AND status = '" . $db->real_escape_string($status) . "'";

// Get total count
$countResult = $db->query("SELECT COUNT(*) as total FROM posts WHERE $where");
$countRow = $countResult->fetch_assoc();
$total = $countRow['total'];
$perPage = 10;
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Get posts
$result = $db->query("SELECT p.*, u.full_name, u.avatar FROM posts p 
    LEFT JOIN users u ON p.author_id = u.id 
    WHERE $where 
    ORDER BY p.created_at DESC 
    LIMIT $offset, $perPage");

$posts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📝 Quản Lý Bài Viết</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary">← Quay Lại</a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Tìm bài viết..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">Tất cả danh mục</option>
                        <option value="news" <?= $category === 'news' ? 'selected' : '' ?>>📰 Tin tức</option>
                        <option value="memory" <?= $category === 'memory' ? 'selected' : '' ?>>📸 Kỷ niệm</option>
                        <option value="announcement" <?= $category === 'announcement' ? 'selected' : '' ?>>📢 Thông báo</option>
                        <option value="other" <?= $category === 'other' ? 'selected' : '' ?>>➕ Khác</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>✓ Đã xuất bản</option>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>📋 Nháp</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">🔍 Tìm Kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Posts Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tiêu Đề</th>
                        <th>Tác Giả</th>
                        <th>Danh Mục</th>
                        <th>Trạng Thái</th>
                        <th>Lượt Xem</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($posts) > 0): ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>#<?= $post['id'] ?></td>
                                <td>
                                    <strong><?= escape($post['title']) ?></strong>
                                    <?php if ($post['image']): ?>
                                        <br><small class="text-muted">🖼️ Có hình ảnh</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= escape($post['full_name'] ?? 'Admin') ?></td>
                                <td>
                                    <?php
                                    $categories = ['news' => '📰 Tin tức', 'memory' => '📸 Kỷ niệm', 'announcement' => '📢 Thông báo', 'other' => '➕ Khác'];
                                    echo $categories[$post['category']] ?? $post['category'];
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $post['status'] === 'published' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($post['status']) ?>
                                    </span>
                                </td>
                                <td><span class="badge bg-info"><?= $post['view_count'] ?></span></td>
                                <td><?= formatDate($post['created_at']) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/posts.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-info" title="Xem">👁️</a>
                                    <button class="btn btn-sm btn-danger" onclick="deletePost(<?= $post['id'] ?>)" title="Xóa">🗑️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">Không có bài viết nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>">← Trước</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>">Tiếp →</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&status=<?= urlencode($status) ?>">Cuối</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
function deletePost(postId) {
    if (confirm('Bạn chắc chắn muốn xóa bài viết này?')) {
        fetch('<?= BASE_URL ?>/admin/api/delete-post.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: postId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSuccess('Đã xóa bài viết');
                setTimeout(() => location.reload(), 1000);
            } else {
                showError(data.message || 'Lỗi xóa bài viết');
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
