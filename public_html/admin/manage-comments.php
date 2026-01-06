<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check admin permission
if (!isAdmin()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$approved = isset($_GET['approved']) ? sanitize($_GET['approved']) : '';

// Build query conditions
$where = "1=1";
if ($search) $where .= " AND c.content LIKE '%" . $db->real_escape_string($search) . "%'";
if ($approved !== '') $where .= " AND c.is_approved = " . ($approved ? 1 : 0);

// Get total count
$countResult = $db->query("SELECT COUNT(*) as total FROM comments c WHERE $where");
$countRow = $countResult->fetch_assoc();
$total = $countRow['total'];
$perPage = 15;
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Get comments
$result = $db->query("SELECT c.*, u.full_name, p.title as post_title FROM comments c
    LEFT JOIN users u ON c.author_id = u.id
    LEFT JOIN posts p ON c.post_id = p.id
    WHERE $where
    ORDER BY c.created_at DESC
    LIMIT $offset, $perPage");

$comments = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>💬 Quản Lý Bình Luận</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary">← Quay Lại</a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Tìm bình luận..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="approved" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="1" <?= $approved === '1' ? 'selected' : '' ?>>✓ Đã duyệt</option>
                        <option value="0" <?= $approved === '0' ? 'selected' : '' ?>>⏳ Chờ duyệt</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">🔍 Tìm Kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Comments List -->
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <strong><?= escape($comment['full_name'] ?? 'Ẩn danh') ?></strong>
                            <br>
                            <small class="text-muted">Trên: <a href="<?= BASE_URL ?>/posts.php?id=<?= $comment['post_id'] ?>"><?= escape($comment['post_title']) ?></a></small>
                        </div>
                        <div>
                            <span class="badge bg-<?= $comment['is_approved'] ? 'success' : 'warning' ?>">
                                <?= $comment['is_approved'] ? '✓ Đã duyệt' : '⏳ Chờ duyệt' ?>
                            </span>
                            <small class="text-muted d-block mt-1"><?= formatDate($comment['created_at']) ?></small>
                        </div>
                    </div>
                    <p class="mb-2"><?= escape($comment['content']) ?></p>
                    <div class="btn-group" role="group">
                        <?php if (!$comment['is_approved']): ?>
                            <button class="btn btn-sm btn-success" onclick="approveComment(<?= $comment['id'] ?>)">✓ Duyệt</button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-warning" onclick="unapproveComment(<?= $comment['id'] ?>)">⏸ Bỏ duyệt</button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-danger" onclick="deleteComment(<?= $comment['id'] ?>)">🗑️ Xóa</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info text-center py-4">Không có bình luận nào</div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&approved=<?= urlencode($approved) ?>">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&approved=<?= urlencode($approved) ?>">← Trước</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&approved=<?= urlencode($approved) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&approved=<?= urlencode($approved) ?>">Tiếp →</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&approved=<?= urlencode($approved) ?>">Cuối</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
function approveComment(commentId) {
    fetch('<?= BASE_URL ?>/admin/api/approve-comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comment_id: commentId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showSuccess('Đã duyệt bình luận');
            setTimeout(() => location.reload(), 500);
        } else {
            showError(data.message || 'Lỗi duyệt');
        }
    });
}

function unapproveComment(commentId) {
    fetch('<?= BASE_URL ?>/admin/api/unapprove-comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ comment_id: commentId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showSuccess('Đã bỏ duyệt');
            setTimeout(() => location.reload(), 500);
        } else {
            showError(data.message || 'Lỗi');
        }
    });
}

function deleteComment(commentId) {
    if (confirm('Xóa bình luận này?')) {
        fetch('<?= BASE_URL ?>/admin/api/delete-comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ comment_id: commentId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSuccess('Đã xóa');
                setTimeout(() => location.reload(), 500);
            } else {
                showError(data.message || 'Lỗi xóa');
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
