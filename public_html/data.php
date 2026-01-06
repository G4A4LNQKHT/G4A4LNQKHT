<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Only for logged in users
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$where = "1=1";
if ($category) $where .= " AND category = '" . $db->real_escape_string($category) . "'";
if ($search) $where .= " AND (title LIKE '%" . $db->real_escape_string($search) . "%' OR description LIKE '%" . $db->real_escape_string($search) . "%')";

// Get total count
$countResult = $db->query("SELECT COUNT(*) as total FROM class_data WHERE $where");
$countRow = $countResult->fetch_assoc();
$total = $countRow['total'];
$perPage = 12;
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Get files
$result = $db->query("SELECT c.*, u.full_name FROM class_data c
    LEFT JOIN users u ON c.uploaded_by = u.id
    WHERE $where
    ORDER BY c.created_at DESC
    LIMIT $offset, $perPage");

$files = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📚 Chia Sẻ Tài Liệu</h1>
        <?php if (isAdmin()): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">📤 Thêm Tài Liệu</button>
        <?php endif; ?>
    </div>

    <!-- Search & Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Tìm tài liệu..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="">Tất cả danh mục</option>
                        <option value="notes" <?= $category === 'notes' ? 'selected' : '' ?>>📝 Ghi chú</option>
                        <option value="slides" <?= $category === 'slides' ? 'selected' : '' ?>>🎞️ Slide</option>
                        <option value="homework" <?= $category === 'homework' ? 'selected' : '' ?>>✏️ Bài tập</option>
                        <option value="exam" <?= $category === 'exam' ? 'selected' : '' ?>>📋 Đề thi</option>
                        <option value="other" <?= $category === 'other' ? 'selected' : '' ?>>➕ Khác</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">🔍 Tìm Kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Files Grid -->
    <div class="row g-3">
        <?php if (count($files) > 0): ?>
            <?php foreach ($files as $file): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-center mb-3" style="font-size: 2.5rem;">
                                <?php
                                $types = [
                                    'pdf' => '📄',
                                    'doc' => '📄',
                                    'docx' => '📄',
                                    'xls' => '📊',
                                    'xlsx' => '📊',
                                    'ppt' => '🎞️',
                                    'pptx' => '🎞️',
                                    'txt' => '📝',
                                    'image' => '🖼️',
                                    'zip' => '📦',
                                    'rar' => '📦'
                                ];
                                
                                $ext = strtolower(pathinfo($file['file_url'], PATHINFO_EXTENSION));
                                echo $types[$ext] ?? '📎';
                                ?>
                            </div>
                            <h6 class="card-title"><?= escape($file['title']) ?></h6>
                            <p class="card-text text-muted small"><?= escape($file['description'] ?? 'Không có mô tả') ?></p>
                            
                            <div class="mb-3">
                                <span class="badge bg-secondary"><?= strtoupper($file['category']) ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center small text-muted mb-3">
                                <span>👤 <?= escape($file['full_name'] ?? 'Ẩn danh') ?></span>
                                <span>📅 <?= formatDate($file['created_at']) ?></span>
                            </div>

                            <div class="d-flex justify-content-between gap-2">
                                <a href="<?= BASE_URL ?>/admin/api/download-file.php?id=<?= $file['id'] ?>" class="btn btn-sm btn-primary flex-grow-1">⬇️ Tải</a>
                                <small class="text-muted text-center" style="width: 40px;">📥 <?= $file['download_count'] ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    📭 Không có tài liệu nào
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=1&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>">← Trước</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>">Tiếp →</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $totalPages ?>&category=<?= urlencode($category) ?>&search=<?= urlencode($search) ?>">Cuối</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Upload Modal (admin only) -->
<?php if (isAdmin()): ?>
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">📤 Thêm Tài Liệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="uploadForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tiêu Đề</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô Tả</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Danh Mục</label>
                            <select name="category" class="form-select" required>
                                <option value="">Chọn danh mục</option>
                                <option value="notes">📝 Ghi chú</option>
                                <option value="slides">🎞️ Slide</option>
                                <option value="homework">✏️ Bài tập</option>
                                <option value="exam">📋 Đề thi</option>
                                <option value="other">➕ Khác</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tải File (Max 10MB)</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">📤 Tải Lên</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        fetch('<?= BASE_URL ?>/admin/api/upload-file.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSuccess('Đã tải tài liệu');
                setTimeout(() => location.reload(), 500);
            } else {
                showError(data.message || 'Lỗi tải lên');
            }
        });
    });
    </script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
