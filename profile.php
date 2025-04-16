<?php
$pageTitle = 'My Profile';
include 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// 验证用户登录状态
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // 设置重定向地址
    $_SESSION['redirect_after_login'] = 'profile.php';
    header('Location: login.php');
    exit;
}

// 获取用户信息
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// 检查用户是否存在
if (!$user) {
    // 记录错误
    error_log("Error: User not found for ID: $userId in profile.php");
    // 引导用户重新登录
    session_unset();
    session_destroy();
    header('Location: login.php?error=user_not_found');
    exit;
}

$name = $user['name'];
$email = $user['email'];
$phone = $user['phone'];
$membershipNumber = 'LH' . str_pad(rand(1000, 9999), 6, '0', STR_PAD_LEFT);

// 处理表单提交
$updateSuccess = false;
$updateError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newName = isset($_POST['name']) ? trim($_POST['name']) : '';
    $newPhone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    
    $nameRegex = '/^[a-zA-Z\s]+$/'; // 只允许字母和空格
    $phoneRegex = '/^1[3-9]\d{9}$|^[569]\d{7}$|^[6]\d{7}$/'; // 支持内地(11位)/香港(8位,5/6/9开头)/澳门(8位,6开头)

    if (empty($newName)) {
        $updateError = 'Please enter your name';
    } elseif (!preg_match($nameRegex, $newName)) {
        $updateError = 'Name can only contain letters and spaces.';
    } elseif (empty($newPhone)) {
        $updateError = 'Please enter your phone number';
    } elseif (!preg_match($phoneRegex, $newPhone)) {
        $updateError = 'Please enter a valid 11-digit Mainland China, 8-digit Hong Kong, or 8-digit Macau phone number.';
    } else {
        // 更新用户信息
        $result = updateUserProfile($userId, $newName, $newPhone);
        
        if ($result['success']) {
            $name = $newName;
            $phone = $newPhone;
            $updateSuccess = true;
            
            // 更新SESSION中的用户名
            $_SESSION['user_name'] = $name;
        } else {
            $updateError = $result['message'];
        }
    }
}
?>

<section style="padding: 50px 0;">
    <div class="container">
        <div style="max-width: 900px; margin: 0 auto;">
            
            <?php if ($updateSuccess): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> Your profile has been updated successfully.
            </div>
            <?php endif; ?>
            
            <?php if (!empty($updateError)): ?>
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $updateError; ?>
            </div>
            <?php endif; ?>
            
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px;">
                <h1 style="margin: 0;"><i class="fas fa-user-circle" style="margin-right: 10px; color: var(--primary-color);"></i>My Profile</h1>
                <a href="my-trips.php" class="btn btn-outline"><i class="fas fa-suitcase"></i> View My Trips</a>
            </div>
            
            <div class="card" style="margin-bottom: 30px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: var(--primary-color); padding: 15px; color: white;">
                    <h2 style="margin: 0; font-size: 1.4rem;"><i class="fas fa-id-card" style="margin-right: 10px;"></i>Personal Information</h2>
                </div>
                
                <div class="card-body" style="padding: 25px;">
                    <!-- 查看模式 -->
                    <div id="view-mode">
                        <div style="display: flex; flex-wrap: wrap; margin-bottom: 20px;">
                            <div style="flex: 0 0 50%; min-width: 300px; padding-right: 20px; margin-bottom: 20px;">
                                <p style="margin-bottom: 15px;">
                                    <i class="fas fa-user" style="width: 20px; color: var(--primary-color); margin-right: 10px;"></i>
                                    <strong>Name:</strong> <span id="display-name"><?php echo $name; ?></span>
                                </p>
                                <p style="margin-bottom: 15px;">
                                    <i class="fas fa-envelope" style="width: 20px; color: var(--primary-color); margin-right: 10px;"></i>
                                    <strong>Email:</strong> <?php echo $email; ?>
                                </p>
                                <p style="margin-bottom: 15px;">
                                    <i class="fas fa-phone" style="width: 20px; color: var(--primary-color); margin-right: 10px;"></i>
                                    <strong>Phone:</strong> <span id="display-phone"><?php echo $phone; ?></span>
                                </p>
                            </div>
                            
                            <div style="flex: 0 0 50%; min-width: 300px; padding-left: 20px; border-left: 1px solid #eee;">
                                <div style="background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
                                    <h3 style="margin-bottom: 15px; color: var(--primary-color);">
                                        <i class="fas fa-star" style="margin-right: 10px;"></i>Membership Details
                                    </h3>
                                    <p style="margin-bottom: 10px;"><strong>Membership Number:</strong> <span style="font-family: monospace; font-size: 1.1em;"><?php echo $membershipNumber; ?></span></p>
                                    <p style="margin-bottom: 10px;"><strong>Membership Status:</strong> <span style="color: #28a745; font-weight: bold; background-color: rgba(40, 167, 69, 0.1); padding: 3px 8px; border-radius: 4px;">Active</span></p>
                                    <p><strong>Join Date:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <button id="edit-button" class="btn btn-primary" style="margin-top: 10px;">
                            <i class="fas fa-edit" style="margin-right: 5px;"></i> Edit Profile
                        </button>
                    </div>
                    
                    <!-- 编辑模式 -->
                    <div id="edit-mode" style="display: none;">
                        <form id="profile-form" method="POST" action="profile.php">
                            <div style="margin-bottom: 20px;">
                                <label for="name" style="display: block; margin-bottom: 5px; font-weight: bold;">Name</label>
                                <input type="text" id="name" name="name" class="form-control" value="<?php echo $name; ?>" required>
                            </div>
                            
                            <div style="margin-bottom: 20px;">
                                <label for="email" style="display: block; margin-bottom: 5px; font-weight: bold;">Email Address (Cannot be changed)</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" disabled style="background-color: #f9f9f9;">
                                <small style="color: #666; display: block; margin-top: 5px;">To change your email, please contact customer support.</small>
                            </div>
                            
                            <div style="margin-bottom: 30px;">
                                <label for="phone" style="display: block; margin-bottom: 5px; font-weight: bold;">Phone Number</label>
                                <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $phone; ?>" placeholder="China: 13812345678 / Hong Kong: 51234567" required>
                                <small style="color: #666; display: block; margin-top: 5px;">Supported formats: China mainland (11 digits starting with 1) or Hong Kong (8 digits starting with 5-9)</small>
                            </div>
                            
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save" style="margin-right: 5px;"></i> Save Changes
                                </button>
                                <button type="button" id="cancel-button" class="btn btn-outline">
                                    <i class="fas fa-times" style="margin-right: 5px;"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 30px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: var(--primary-color); padding: 15px; color: white;">
                    <h2 style="margin: 0; font-size: 1.4rem;"><i class="fas fa-lock" style="margin-right: 10px;"></i>Security</h2>
                </div>
                
                <div class="card-body" style="padding: 25px;">
                    <p style="margin-bottom: 20px;">Manage your password and account security settings.</p>
                    
                    <button class="btn btn-outline" disabled style="opacity: 0.7;">
                        <i class="fas fa-key" style="margin-right: 5px;"></i> Change Password (Coming Soon)
                    </button>
                </div>
            </div>
            
            <div class="card" style="box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                <div style="background-color: var(--primary-color); padding: 15px; color: white;">
                    <h2 style="margin: 0; font-size: 1.4rem;"><i class="fas fa-bell" style="margin-right: 10px;"></i>Preferences</h2>
                </div>
                
                <div class="card-body" style="padding: 25px;">
                    <p style="margin-bottom: 20px;">Manage your notification preferences and account settings.</p>
                    
                    <button class="btn btn-outline" disabled style="opacity: 0.7;">
                        <i class="fas fa-cog" style="margin-right: 5px;"></i> Manage Preferences (Coming Soon)
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="assets/js/validation.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewMode = document.getElementById('view-mode');
    const editMode = document.getElementById('edit-mode');
    const editButton = document.getElementById('edit-button');
    const cancelButton = document.getElementById('cancel-button');
    const profileForm = document.getElementById('profile-form');
    const nameInput = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    const statusMessage = document.getElementById('update-status-message');
    const displayName = document.getElementById('display-name');
    const displayPhone = document.getElementById('display-phone');
    let errorMessage = '';
    
    // 切换到编辑模式
    editButton.addEventListener('click', function() {
        viewMode.style.display = 'none';
        editMode.style.display = 'block';
    });
    
    // 切换回查看模式
    cancelButton.addEventListener('click', function() {
        editMode.style.display = 'none';
        viewMode.style.display = 'block';
    });
    
    // 表单验证
    if (profileForm) {
        profileForm.addEventListener('submit', function(event) {
            let hasError = false;
            let errorMessage = '';
            
            // 验证姓名
            if (nameInput.value.trim() === '') {
                errorMessage = 'Please enter your name';
                hasError = true;
            } else if (!ValidationRules.validateName(nameInput.value)) {
                errorMessage = 'Name can only contain letters and spaces.';
                hasError = true;
            }
            // 验证手机号
            else if (phoneInput.value.trim() === '') {
                errorMessage = 'Please enter your phone number';
                hasError = true;
            }
            // 验证手机号格式
            else if (!ValidationRules.validatePhone(phoneInput.value)) {
                errorMessage = ValidationRules.getPhoneErrorMessage();
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