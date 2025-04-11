<?php
$pageTitle = 'Login';
include 'includes/header.php';
require_once 'includes/auth.php';

// 检查用户是否已登录
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$email = '';
$errorMessage = '';

// 处理登录表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // 验证数据
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address';
    } elseif (empty($password)) {
        $errorMessage = 'Please enter your password';
    } else {
        // 尝试登录
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            // 检查是否有重定向URL
            if (isset($_SESSION['redirect_after_login'])) {
                $redirectUrl = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: $redirectUrl");
            } else {
                header('Location: index.php');
            }
            exit;
        } else {
            $errorMessage = $result['message'];
        }
    }
}
?>

<section style="padding: 50px 0;">
    <div class="container">
        <div style="max-width: 400px; margin: 0 auto;">
            <h1 style="text-align: center; margin-bottom: 20px;">Login to Your Account</h1>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form action="login.php" method="POST" id="login-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        
                        <div class="form-group" style="margin-top: 20px;">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            let hasError = false;
            let errorMessage = '';
            
            // 获取表单字段值
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            // 验证邮箱
            if (email === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errorMessage = 'Please enter a valid email address';
                hasError = true;
            }
            // 验证密码
            else if (password === '') {
                errorMessage = 'Please enter your password';
                hasError = true;
            }
            
            // 如果有错误，阻止表单提交并显示错误
            if (hasError) {
                event.preventDefault();
                alert(errorMessage);
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>