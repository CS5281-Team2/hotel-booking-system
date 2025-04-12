<?php
$pageTitle = 'Register';
include 'includes/header.php';
require_once 'includes/auth.php';

// 检查用户是否已登录
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$name = '';
$mobile = '';
$email = '';
$errorMessage = '';
$successMessage = '';

// 处理注册表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // 验证数据
    if (empty($name)) {
        $errorMessage = 'Please enter your name';
    } elseif (empty($mobile)) {
        $errorMessage = 'Please enter your mobile number';
    } elseif (!preg_match('/^1[3-9]\d{9}$/', $mobile) && !preg_match('/^[5-9]\d{7}$/', $mobile)) {
        $errorMessage = 'Please enter a valid phone number (China mainland: 11 digits starting with 1, Hong Kong: 8 digits starting with 5-9)';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address';
    } elseif (empty($password) || strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match';
    } else {
        // 尝试注册
        $result = registerUser($name, $mobile, $email, $password);
        
        if ($result['success']) {
            $successMessage = 'Registration successful! You can now login.';
            // 重置表单数据
            $name = '';
            $mobile = '';
            $email = '';
        } else {
            $errorMessage = $result['message'];
        }
    }
}
?>

<section style="padding: 50px 0;">
    <div class="container">
        <div style="max-width: 500px; margin: 0 auto;">
            <h1 style="text-align: center; margin-bottom: 20px;">Create an Account</h1>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success">
                    <?php echo $successMessage; ?>
                    <p style="margin-top: 10px;">
                        <a href="login.php">Click here to login</a>
                    </p>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <form action="register.php" method="POST" id="register-form">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo $name; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="mobile">Mobile Number</label>
                                <input type="tel" id="mobile" name="mobile" class="form-control" value="<?php echo $mobile; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group" style="margin-top: 20px;">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            let hasError = false;
            let errorMessage = '';
            
            // 获取表单字段值
            const name = document.getElementById('name').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // 验证姓名
            if (name === '') {
                errorMessage = 'Please enter your name';
                hasError = true;
            }
            // 验证手机号
            else if (mobile === '') {
                errorMessage = 'Please enter your mobile number';
                hasError = true;
            }
            // 验证手机号格式 - 中国大陆或香港
            else if (!(/^1[3-9]\d{9}$/.test(mobile) || /^[5-9]\d{7}$/.test(mobile))) {
                errorMessage = 'Please enter a valid phone number (China mainland: 11 digits starting with 1, Hong Kong: 8 digits starting with 5-9)';
                hasError = true;
            }
            // 验证邮箱
            else if (email === '') {
                errorMessage = 'Please enter your email address';
                hasError = true;
            }
            // 验证邮箱格式
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errorMessage = 'Please enter a valid email address';
                hasError = true;
            }
            // 验证密码长度
            else if (password.length < 6) {
                errorMessage = 'Password must be at least 6 characters long';
                hasError = true;
            }
            // 验证密码匹配
            else if (password !== confirmPassword) {
                errorMessage = 'Passwords do not match';
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