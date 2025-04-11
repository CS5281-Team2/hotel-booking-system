<?php
$pageTitle = 'My Profile';
include 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// 验证用户登录状态
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// 获取用户信息
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if (!$user) {
    header('Location: logout.php'); // 如果找不到用户，注销并重定向
    exit;
}

$name = $user[1];
$email = $user[2];
$mobile = $user[3];
$membershipNumber = 'LH' . str_pad(rand(1000, 9999), 6, '0', STR_PAD_LEFT);
?>

<section style="padding: 50px 0;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <h1 style="margin-bottom: 20px;">My Profile</h1>
            
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-body">
                    <h2 class="card-title">Personal Information</h2>
                    
                    <div style="display: flex; flex-wrap: wrap; margin-top: 20px;">
                        <div style="flex: 0 0 50%; min-width: 300px; padding-right: 20px; margin-bottom: 20px;">
                            <p><strong>Name:</strong> <?php echo $name; ?></p>
                            <p><strong>Email:</strong> <?php echo $email; ?></p>
                            <p><strong>Mobile:</strong> <?php echo $mobile; ?></p>
                        </div>
                        
                        <div style="flex: 0 0 50%; min-width: 300px; padding-left: 20px; border-left: 1px solid #ddd;">
                            <div style="background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
                                <h3 style="margin-bottom: 10px;">Membership Details</h3>
                                <p><strong>Membership Number:</strong> <?php echo $membershipNumber; ?></p>
                                <p><strong>Membership Status:</strong> <span style="color: var(--primary-color); font-weight: bold;">Active</span></p>
                                <p><strong>Join Date:</strong> <?php echo date('F j, Y', strtotime($user[6])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="my-trips.php" class="btn btn-primary">View My Trips</a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>