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
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Build query conditions
$where = "1=1";
if ($search) $where .= " AND (title LIKE '%" . $db->real_escape_string($search) . "%' OR description LIKE '%" . $db->real_escape_string($search) . "%')";
if ($status) $where .= " AND status = '" . $db->real_escape_string($status) . "'";

// Get total count
$countResult = $db->query("SELECT COUNT(*) as total FROM tasks WHERE $where");
$countRow = $countResult->fetch_assoc();
$total = $countRow['total'];
$perPage = 10;
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Get tasks
$result = $db->query("SELECT t.*, u1.full_name as assigned_name, u2.full_name as creator_name FROM tasks t
    LEFT JOIN users u1 ON t.assigned_to = u1.id
    LEFT JOIN users u2 ON t.created_by = u2.id
    WHERE $where
    ORDER BY 
        CASE 
            WHEN status = 'pending' THEN 0
            WHEN status = 'in_progress' THEN 1
            WHEN status = 'completed' THEN 2
            WHEN status = 'cancelled' THEN 3
        END,
        due_date ASC
    LIMIT $offset, $perPage");

$tasks = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>📋 Quản Lý Công Việc</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary">← Quay Lại</a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm công việc..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>⏳ Chờ xử lý</option>
                        <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>🔄 Đang làm</option>
                        <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>✓ Hoàn thành</option>
                        <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>❌ Hủy</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">🔍 Tìm Kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tiêu Đề</th>
                        <th>Người Giao</th>
                        <th>Ưu Tiên</th>
                        <th>Trạng Thái</th>
                        <th>Hạn Chót</th>
                        <th>Người Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tasks) > 0): ?>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td>#<?= $task['id'] ?></td>
                                <td><?= escape($task['title']) ?></td>
                                <td><?= escape($task['assigned_name'] ?? 'Không gán') ?></td>
                                <td>
                                    <?php
                                    $priorities = ['low' => '🟢 Thấp', 'medium' => '🟡 Trung', 'high' => '🔴 Cao'];
                                    echo $priorities[$task['priority']] ?? $task['priority'];
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo match($task['status']) {
                                            'pending' => 'warning',
                                            'in_progress' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php
                                        $statuses = ['pending' => '⏳ Chờ', 'in_progress' => '🔄 Làm', 'completed' => '✓ Xong', 'cancelled' => '❌ Hủy'];
                                        echo $statuses[$task['status']] ?? $task['status'];
                                        ?>
                                    </span>
                                </td>
                                <td><?= $task['due_date'] ? formatDate($task['due_date']) : 'Không có' ?></td>
                                <td><?= escape($task['creator_name'] ?? 'Admin') ?></td>
                                <td>
                                    <select class="form-select form-select-sm" onchange="updateTaskStatus(<?= $task['id'] ?>, this.value)">
                                        <option value="">Cập nhật...</option>
                                        <option value="pending">⏳ Chờ xử lý</option>
                                        <option value="in_progress">🔄 Đang làm</option>
                                        <option value="completed">✓ Hoàn thành</option>
                                        <option value="cancelled">❌ Hủy</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">Không có công việc nào</td>
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
                        <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">← Trước</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Tiếp →</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">Cuối</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
function updateTaskStatus(taskId, status) {
    if (!status) return;
    
    fetch('<?= BASE_URL ?>/admin/api/update-task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ task_id: taskId, status: status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showSuccess('Đã cập nhật công việc');
            setTimeout(() => location.reload(), 500);
        } else {
            showError(data.message || 'Lỗi cập nhật');
        }
    });
}
</script>

<?php include '../includes/footer.php'; ?>
