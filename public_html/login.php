<?php
/**
 * LOGIN.PHP - TRANG ĐĂNG NHẬP
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Nếu đã login, redirect
if (isLoggedIn()) {
    redirect(BASE_URL . (isAdmin() ? 'admin/dashboard.php' : 'index.php'));
}

$error = '';
$username = '';

// Xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = getPost('username');
    $password = getPost('password');
    
    // Validate
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập username và password!';
    } else {
        // Xác thực
        $user = authenticateUser($username, $password);
        
        if ($user) {
            // Tạo session
            createSession($user);
            
            // Redirect
            $redirect = getGet('redirect', '');
            if ($redirect === 'admin') {
                redirect(BASE_URL . 'admin/dashboard.php');
            } else {
                redirect(BASE_URL);
            }
        } else {
            $error = 'Username hoặc password không đúng!';
        }
    }
}

$page_title = 'Đăng nhập';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
            color: white;
            padding: 2rem 1rem;
            text-align: center;
        }
        
        .login-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .login-header p {
            margin-bottom: 0;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            border-radius: 6px;
            border: 1px solid #ddd;
            padding: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #00897b;
            box-shadow: 0 0 0 0.2rem rgba(0, 137, 123, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #00897b 0%, #00695c 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,137,123,0.3);
            color: white;
        }
        
        .alert {
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }
        
        .login-footer {
            text-align: center;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        
        .login-footer a {
            color: #00897b;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .info-box {
            background-color: #e0f2f1;
            border-left: 4px solid #00897b;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-users"></i></h1>
                <p><?php echo SITE_NAME; ?></p>
            </div>
            
            <div class="login-body">
                <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo escape($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i> Username
                        </label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            name="username" 
                            value="<?php echo escape($username); ?>" 
                            required 
                            autofocus
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Mật khẩu
                        </label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </button>
                </form>
                
                <div class="login-footer">
                    <p>Quên mật khẩu? <a href="<?php echo BASE_URL; ?>forgot-password.php">Đặt lại</a></p>
                    <p><a href="<?php echo BASE_URL; ?>">← Quay lại Trang chủ</a></p>
                </div>
            </div>
        </div>
        
        <div class="info-box" style="margin-top: 1.5rem; background: white; border-left: none; text-align: center;">
            <p style="margin-bottom: 0;">
                <i class="fas fa-info-circle"></i> 
                <strong>Tài khoản test:</strong> admin / admin
            </p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
