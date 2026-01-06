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
$role = isset($_GET['role']) ? sanitize($_GET['role']) : '';

// Build query conditions
$where = "1=1";
if ($search) $where .= " AND (full_name LIKE '%" . $db->real_escape_string($search) . "%' OR email LIKE '%" . $db->real_escape_string($search) . "%' OR username LIKE '%" . $db->real_escape_string($search) . "%')";
if ($role === 'admin') $where .= " AND is_admin = 1";
elseif ($role === 'member') $where .= " AND is_admin = 0";

// Get total count
$countResult = $db->query("SELECT COUNT(*) as total FROM users WHERE $where");
$countRow = $countResult->fetch_assoc();
$total = $countRow['total'];
$perPage = 10;
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Get users
$result = $db->query("SELECT id, username, email, full_name, class_position, is_admin, status, created_at FROM users WHERE $where ORDER BY created_at DESC LIMIT $offset, $perPage");

$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

include '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>👥 Quản Lý Thành Viên</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary">← Quay Lại</a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm thành viên..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-4">
                    <select name="role" class="form-select">
                        <option value="">Tất cả chức vụ</option>
                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>👑 Quản Trị</option>
                        <option value="member" <?= $role === 'member' ? 'selected' : '' ?>>📝 Thành Viên</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">🔍 Tìm Kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Tên Đăng Nhập</th>
                        <th>Email</th>
                        <th>Tên Đầy Đủ</th>
                        <th>Chức Vụ</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= $user['id'] ?></td>
                                <td><?= escape($user['username']) ?></td>
                                <td><?= escape($user['email']) ?></td>
                                <td><?= escape($user['full_name']) ?></td>
                                <td>
                                    <?php if ($user['is_admin']): ?>
                                        <span class="badge bg-danger">👑 Quản Trị</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">📝 Thành Viên</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= $user['status'] === 'active' ? '✓ Hoạt động' : 'Vô hiệu' ?>
                                    </span>
                                </td>
                                <td><?= formatDate($user['created_at']) ?></td>
                                <td>
                                    <a href="<?= BASE_URL ?>/profile.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-info" title="Xem hồ sơ">👁️</a>
                                    <?php if (!$user['is_admin']): ?>
                                        <button class="btn btn-sm btn-warning" onclick="makeAdmin(<?= $user['id'] ?>)" title="Nâng lên Admin">↑</button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?= $user['id'] ?>)" title="Xóa">🗑️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">Không có thành viên nào</td>
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
                        <a class="page-link" href="?page=1&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>">Đầu</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>">← Trước</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>">Tiếp →</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $totalPages ?>&search=<?= urlencode($search) ?>&role=<?= urlencode($role) ?>">Cuối</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
function makeAdmin(userId) {
    if (confirm('Nâng thành viên này lên Quản Trị?')) {
        fetch('<?= BASE_URL ?>/admin/api/promote-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSuccess('Đã nâng lên Quản Trị');
                setTimeout(() => location.reload(), 500);
            } else {
                showError(data.message || 'Lỗi');
            }
        });
    }
}

function deleteUser(userId) {
    if (confirm('Xóa thành viên này? Hành động không thể hoàn tác!')) {
        fetch('<?= BASE_URL ?>/admin/api/delete-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showSuccess('Đã xóa thành viên');
                setTimeout(() => location.reload(), 1000);
            } else {
                showError(data.message || 'Lỗi xóa');
            }
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
