<?php
/**
 * DB.PHP - HÀM THAO TÁC DATABASE
 * Các hàm tiện ích lấy dữ liệu từ database
 */

// ============================================
// HÀM POSTS (BÀI VIẾT)
// ============================================

/**
 * Lấy tất cả bài viết công khai
 */
function getAllPosts($limit = 10, $offset = 0) {
    $sql = "SELECT p.*, u.full_name as author_name, u.avatar
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.status = 'published'
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy 1 bài viết theo ID
 */
function getPostById($post_id) {
    $sql = "SELECT p.*, u.full_name as author_name, u.avatar, u.id as author_id
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            WHERE p.id = ? AND p.status = 'published'";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Tạo bài viết mới
 */
function createPost($author_id, $title, $content, $category = 'news', $image = null, $status = 'draft') {
    $sql = "INSERT INTO posts (author_id, title, content, category, image, status)
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('isssss', $author_id, $title, $content, $category, $image, $status);
    
    if ($stmt->execute()) {
        $post_id = Database::getInstance()->getLastInsertId();
        logAction($author_id, 'Tạo bài viết', "Bài viết #$post_id");
        return ['success' => true, 'post_id' => $post_id];
    }
    return ['success' => false];
}

/**
 * Cập nhật bài viết
 */
function updatePost($post_id, $title, $content, $category, $image = null, $status = null) {
    $sql = "UPDATE posts SET title = ?, content = ?, category = ?";
    $params = [$title, $content, $category];
    $types = 'sss';
    
    if ($image !== null) {
        $sql .= ", image = ?";
        $params[] = $image;
        $types .= 's';
    }
    
    if ($status !== null) {
        $sql .= ", status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $sql .= " WHERE id = ?";
    $params[] = $post_id;
    $types .= 'i';
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    return $stmt->execute();
}

/**
 * Xóa bài viết
 */
function deletePost($post_id) {
    $sql = "DELETE FROM posts WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $post_id);
    
    return $stmt->execute();
}

/**
 * Tăng view count
 */
function incrementPostViews($post_id) {
    $sql = "UPDATE posts SET view_count = view_count + 1 WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $post_id);
    
    return $stmt->execute();
}

// ============================================
// HÀM COMMENTS (BÌNH LUẬN)
// ============================================

/**
 * Lấy bình luận theo bài viết
 */
function getCommentsByPost($post_id, $approved_only = true) {
    $where = "WHERE c.post_id = ?";
    if ($approved_only) {
        $where .= " AND c.is_approved = 1";
    }
    
    $sql = "SELECT c.*, u.full_name as author_name, u.avatar
            FROM comments c
            LEFT JOIN users u ON c.author_id = u.id
            $where
            ORDER BY c.created_at DESC";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Tạo bình luận mới
 */
function createComment($post_id, $author_id, $content, $auto_approve = false) {
    $is_approved = ($auto_approve || isAdmin()) ? 1 : 0;
    
    $sql = "INSERT INTO comments (post_id, author_id, content, is_approved)
            VALUES (?, ?, ?, ?)";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('iisi', $post_id, $author_id, $content, $is_approved);
    
    if ($stmt->execute()) {
        return ['success' => true, 'comment_id' => Database::getInstance()->getLastInsertId()];
    }
    return ['success' => false];
}

/**
 * Xóa bình luận
 */
function deleteComment($comment_id) {
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $comment_id);
    
    return $stmt->execute();
}

/**
 * Duyệt bình luận
 */
function approveComment($comment_id) {
    $sql = "UPDATE comments SET is_approved = 1 WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $comment_id);
    
    return $stmt->execute();
}

// ============================================
// HÀM TASKS (CÔNG VIỆC)
// ============================================

/**
 * Lấy tất cả task
 */
function getAllTasks($filter_status = null) {
    $sql = "SELECT t.*, u.full_name as assigned_name, creator.full_name as creator_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            LEFT JOIN users creator ON t.created_by = creator.id
            WHERE 1=1";
    
    if ($filter_status) {
        $sql .= " AND t.status = '$filter_status'";
    }
    
    $sql .= " ORDER BY t.due_date ASC, t.priority DESC";
    
    $result = Database::getInstance()->getConnection()->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy task theo ID
 */
function getTaskById($task_id) {
    $sql = "SELECT t.*, u.full_name as assigned_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            WHERE t.id = ?";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $task_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Tạo task mới
 */
function createTask($title, $description, $due_date, $priority = 'medium', $assigned_to = null, $created_by) {
    $sql = "INSERT INTO tasks (title, description, due_date, priority, assigned_to, created_by)
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('ssssii', $title, $description, $due_date, $priority, $assigned_to, $created_by);
    
    if ($stmt->execute()) {
        return ['success' => true, 'task_id' => Database::getInstance()->getLastInsertId()];
    }
    return ['success' => false];
}

/**
 * Cập nhật trạng thái task
 */
function updateTaskStatus($task_id, $status) {
    $sql = "UPDATE tasks SET status = ? WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('si', $status, $task_id);
    
    return $stmt->execute();
}

/**
 * Xóa task
 */
function deleteTask($task_id) {
    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $task_id);
    
    return $stmt->execute();
}

// ============================================
// HÀM CLASS_DATA (TÀI LIỆU HỌC TẬP)
// ============================================

/**
 * Lấy tất cả dữ liệu học tập
 */
function getAllClassData($category = null) {
    $sql = "SELECT cd.*, u.full_name as uploader_name
            FROM class_data cd
            LEFT JOIN users u ON cd.uploaded_by = u.id
            WHERE 1=1";
    
    if ($category) {
        $sql .= " AND cd.category = '$category'";
    }
    
    $sql .= " ORDER BY cd.created_at DESC";
    
    $result = Database::getInstance()->getConnection()->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Tạo dữ liệu mới
 */
function createClassData($title, $category, $file_url, $file_type, $description = '', $uploaded_by) {
    $sql = "INSERT INTO class_data (title, category, file_url, file_type, description, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('sssssi', $title, $category, $file_url, $file_type, $description, $uploaded_by);
    
    if ($stmt->execute()) {
        return ['success' => true, 'data_id' => Database::getInstance()->getLastInsertId()];
    }
    return ['success' => false];
}

/**
 * Xóa dữ liệu
 */
function deleteClassData($data_id) {
    $sql = "DELETE FROM class_data WHERE id = ?";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $data_id);
    
    return $stmt->execute();
}

// ============================================
// HÀM SCHEDULES (THỜI KHÓA BIỂU)
// ============================================

/**
 * Lấy thời khóa biểu theo ngày
 */
function getScheduleByDay($day) {
    $sql = "SELECT * FROM schedules WHERE day_of_week = ? ORDER BY period ASC";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('s', $day);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy toàn bộ thời khóa biểu
 */
function getAllSchedules() {
    $sql = "SELECT * FROM schedules ORDER BY FIELD(day_of_week, 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'), period ASC";
    
    $result = Database::getInstance()->getConnection()->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// ============================================
// HÀM USERS (THÀNH VIÊN)
// ============================================

/**
 * Lấy tất cả user
 */
function getAllUsers() {
    $sql = "SELECT id, username, full_name, email, class_position, avatar, status, created_at
            FROM users
            WHERE status = 'active'
            ORDER BY full_name ASC";
    
    $result = Database::getInstance()->getConnection()->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Lấy user theo ID
 */
function getUserById($user_id) {
    $sql = "SELECT id, username, full_name, email, phone, class_position, avatar, is_admin, status
            FROM users
            WHERE id = ?";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}

?>
