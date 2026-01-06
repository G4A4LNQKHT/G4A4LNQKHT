<?php
/**
 * CONFIG.PHP - CẤU HÌNH CHUNG
 * Kết nối database, constants, cấu hình toàn cục
 * 
 * Lưu ý: Trong production, nên bảo vệ file này
 * Sử dụng environment variables thay vì hard-code
 */

// ============================================
// 1. CẤU HÌNH DATABASE
// ============================================

// Development (localhost)
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'gaqzzint_db');
}
// Production (Shared Hosting)
else {
    define('DB_HOST', 'localhost');           // Thường là localhost
    define('DB_USER', 'gaqzzint_db');         // Username từ hosting
    define('DB_PASS', 'g4a4database');        // Password từ hosting
    define('DB_NAME', 'gaqzzint_db');         // Database name
}

define('DB_PORT', 3306);
define('DB_CHARSET', 'utf8mb4');

// ============================================
// 2. CẤU HÌNH CHUNG
// ============================================

// URL gốc của website
define('BASE_URL', 'https://g4a4.qzz.io/');
define('SITE_NAME', 'Tổ 4 - Lớp A4');
define('ADMIN_EMAIL', 'admin@g4a4.qzz.io');

// Thư mục upload
define('UPLOAD_DIR', __DIR__ . '/../public_html/uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Cấu hình session
define('SESSION_LIFETIME', 7200);  // 2 giờ (giây)
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// ============================================
// 3. BỚT ĐẦU SESSION
// ============================================

session_start();

// Kiểm tra timeout session
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
        session_destroy();
        $_SESSION = [];
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['is_admin']);
    }
}
$_SESSION['last_activity'] = time();

// ============================================
// 4. KẾT NỐI DATABASE - MySQLI
// ============================================

class Database {
    private $conn;
    private $host = DB_HOST;
    private $db_user = DB_USER;
    private $db_pass = DB_PASS;
    private $db_name = DB_NAME;
    private $charset = DB_CHARSET;
    private static $instance = null;

    // Singleton pattern - chỉ tạo 1 connection
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function connect() {
        try {
            $this->conn = new mysqli(
                $this->host,
                $this->db_user,
                $this->db_pass,
                $this->db_name,
                DB_PORT
            );

            // Kiểm tra lỗi kết nối
            if ($this->conn->connect_error) {
                throw new Exception("Lỗi kết nối: " . $this->conn->connect_error);
            }

            // Đặt charset
            $this->conn->set_charset($this->charset);

            return $this->conn;
        } catch (Exception $e) {
            die("Không thể kết nối database: " . $e->getMessage());
        }
    }

    public function getConnection() {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }

    // Query có tham số (Prepared Statements - chống SQL injection)
    public function prepare($query) {
        return $this->getConnection()->prepare($query);
    }

    // Query đơn giản (có bind parameters)
    public function query($sql, $params = [], $types = '') {
        $stmt = $this->prepare($sql);
        
        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }

    // Thực thi query (INSERT, UPDATE, DELETE)
    public function execute($sql, $params = [], $types = '') {
        $stmt = $this->prepare($sql);
        
        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        
        return $stmt->execute();
    }

    // Lấy ID vừa insert
    public function getLastInsertId() {
        return $this->getConnection()->insert_id;
    }

    // Đóng kết nối
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Khởi tạo database connection
$db = Database::getInstance()->getConnection();

// ============================================
// 5. HÀM TRỢ GIÚP
// ============================================

/**
 * Lấy user_id từ session
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Kiểm tra đã login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Kiểm tra là admin
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

/**
 * Lấy tên username từ session
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? 'Khách';
}

/**
 * Redirect đến URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Escape string (chống XSS)
 */
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input
 */
function sanitize($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Lấy dữ liệu từ POST
 */
function getPost($key, $default = '') {
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

/**
 * Lấy dữ liệu từ GET
 */
function getGet($key, $default = '') {
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Format ngày tháng
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Ghi log
 */
function logAction($user_id, $action, $description = '', $ip = '') {
    global $db;
    if (empty($ip)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    $sql = "INSERT INTO logs (user_id, action, description, ip_address) 
            VALUES (?, ?, ?, ?)";
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->bind_param('isss', $user_id, $action, $description, $ip);
    return $stmt->execute();
}

/**
 * Gửi JSON response (dùng cho AJAX)
 */
function jsonResponse($status, $message = '', $data = null) {
    header('Content-Type: application/json; charset=utf-8');
    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

// ============================================
// 6. CẤU HÌNH BẢO MẬT
// ============================================

// Tắt error display (trong production)
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') === false) {
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

?>
