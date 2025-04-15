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
    
    $nameRegex = '/^[a-zA-Z\s]+$/'; // 只允许字母和空格
    $phoneRegex = '/^1[3-9]\d{9}$|^[569]\d{7}$|^[6]\d{7}$/'; // 支持内地(11位)/香港(8位,5/6/9开头)/澳门(8位,6开头)

    if (empty($name) || empty($email) || empty($mobile) || empty($password) || empty($confirmPassword)) {
        $errorMessage = 'All fields are required';
    } elseif (!preg_match($nameRegex, $name)) {
        $errorMessage = 'Name can only contain letters and spaces.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Invalid email format';
    } elseif (empty($mobile)) {
        $errorMessage = 'Please enter your mobile number';
    } elseif (!preg_match($phoneRegex, $mobile)) {
        $errorMessage = 'Please enter a valid 11-digit Mainland China, 8-digit Hong Kong, or 8-digit Macau phone number.';
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
    const nameInput = document.getElementById('name');
    const mobile = document.getElementById('mobile').value.trim();
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const statusMessage = document.getElementById('status-message');
    
    registerForm.addEventListener('submit', function(event) {
        event.preventDefault();
        statusMessage.innerHTML = '';
        
        const name = nameInput.value.trim();
        const email = emailInput.value.trim();
        const mobile = mobile.value.trim();
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();
        let errorMessage = '';
        
        const nameRegex = /^[a-zA-Z\s]+$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const phoneRegex = /^1[3-9]\d{9}$|^[569]\d{7}$|^[6]\d{7}$/; // 支持内地/香港/澳门
        
        if (name === '' || email === '' || mobile === '' || password === '' || confirmPassword === '') {
            errorMessage = 'All fields are required';
        } else if (!nameRegex.test(name)) {
            errorMessage = 'Name can only contain letters and spaces.';
        } else if (!emailRegex.test(email)) {
            errorMessage = 'Invalid email format';
        } else if (mobile === '') {
            errorMessage = 'Please enter your mobile number';
        } else if (!phoneRegex.test(mobile)) {
            errorMessage = 'Please enter a valid 11-digit Mainland China, 8-digit Hong Kong, or 8-digit Macau phone number.';
        } else if (password !== confirmPassword) {
            errorMessage = 'Passwords do not match';
        }
        
        if (errorMessage) {
            event.preventDefault();
            statusMessage.innerHTML = errorMessage;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>