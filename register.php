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
$phone = '';
$email = '';
$errorMessage = '';
$successMessage = '';

// 处理注册表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    $nameRegex = '/^[a-zA-Z\s]+$/'; // 只允许字母和空格
    $phoneRegex = '/^1[3-9]\d{9}$|^[569]\d{7}$|^[6]\d{7}$/'; // 支持内地(11位)/香港(8位,5/6/9开头)/澳门(8位,6开头)

    if (empty($name) || empty($email) || empty($phone) || empty($password) || empty($confirmPassword)) {
        $errorMessage = 'All fields are required';
    } elseif (!preg_match($nameRegex, $name)) {
        $errorMessage = 'Name can only contain letters and spaces.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Invalid email format';
    } elseif (empty($phone)) {
        $errorMessage = 'Please enter your phone number';
    } elseif (!preg_match($phoneRegex, $phone)) {
        $errorMessage = 'Please enter a valid 11-digit Mainland China, 8-digit Hong Kong, or 8-digit Macau phone number.';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match';
    } else {
        // 尝试注册
        $result = registerUser($name, $phone, $email, $password);
        
        if ($result['success']) {
            $successMessage = 'Registration successful! You can now login.';
            // 重置表单数据
            $name = '';
            $phone = '';
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
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo $phone; ?>" required>
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

<!-- 引入外部JS文件 -->
<script src="assets/js/validation.js"></script>
<script src="register.js"></script>

<?php include 'includes/footer.php'; ?>