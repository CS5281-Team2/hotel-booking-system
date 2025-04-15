<?php
$pageTitle = 'Complete Your Booking';
include 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// 验证用户登录状态
if (!isLoggedIn()) {
    // 保存当前URL，登录后重定向回来
    $_SESSION['redirect_back'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// 获取预订参数
$roomId = isset($_GET['room_id']) ? $_GET['room_id'] : '';
$checkIn = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$checkOut = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;

// 获取房间信息
$room = getRoomById($roomId);

// 如果参数不完整或房间不存在，重定向到搜索页
if (empty($roomId) || empty($checkIn) || empty($checkOut) || !$room) {
    header('Location: search.php');
    exit;
}

// 检查房间是否可用
if (!isRoomAvailable($roomId, $checkIn, $checkOut)) {
    header('Location: search.php?error=room_unavailable');
    exit;
}

// 计算住宿天数
$checkInDate = new DateTime($checkIn);
$checkOutDate = new DateTime($checkOut);
$interval = $checkInDate->diff($checkOutDate);
$nights = $interval->days;

// 计算总价
$totalPrice = $room['price'] * $nights;

// 处理表单提交
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobilePhone = isset($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : '';
    $specialRequests = isset($_POST['special_requests']) ? trim($_POST['special_requests']) : '';
    
    // 验证电话号码 (仅结构，不含区号)
    $phoneRegex = '/^1[3-9]\d{9}$|^[569]\d{7}$|^[6]\d{7}$/'; // 支持内地(11位)/香港(8位,5/6/9开头)/澳门(8位,6开头)
    if (empty($mobilePhone)) {
        $errorMessage = 'Please provide a contact phone number.';
    } elseif (!preg_match($phoneRegex, $mobilePhone)) {
        $errorMessage = 'Please enter a valid 11-digit Mainland China, 8-digit Hong Kong, or 8-digit Macau phone number.';
    } else {
        // 创建预订
        $bookingData = [
            'id' => generateId(),
            'user_id' => $_SESSION['user_id'],
            'room_id' => $roomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'total_price' => $totalPrice,
            'mobile_phone' => $mobilePhone,
            'special_requests' => $specialRequests,
            'status' => 'confirmed',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if (addBooking($bookingData)) {
            // 预订成功，重定向到确认页
            header('Location: confirmation.php?booking_id=' . $bookingData['id']);
            exit;
        } else {
            $errorMessage = 'Failed to create booking. Please try again later.';
        }
    }
}
?>

<section style="padding: 50px 0;">
    <div class="container">
        <h1 style="margin-bottom: 20px;">Complete Your Booking</h1>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
        
        <div style="display: flex; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px; padding-right: 30px;">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title" style="margin-bottom: 20px;">Booking Information</h2>
                        
                        <form method="POST" action="" id="booking-form">
                            <div class="form-group">
                                <label for="mobile_phone">Contact Phone</label>
                                <input type="tel" id="mobile_phone" name="mobile_phone" class="form-control" required>
                                <small>This will be used for urgent matters related to your reservation.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="special_requests">Special Requests (optional)</label>
                                <textarea id="special_requests" name="special_requests" class="form-control" rows="3"></textarea>
                                <small>e.g., early check-in, room preference, accessibility needs.</small>
                            </div>
                            
                            <div class="form-group">
                                <h3>Cancellation Policy</h3>
                                <p style="font-size: 0.9rem; color: #666;">Free cancellation up to 24 hours before check-in. 
                                   Cancellations made within 24 hours of check-in are subject to a one-night charge.</p>
                            </div>
                            
                            <div style="margin-top: 30px;">
                                <button type="submit" class="btn btn-primary" style="width: 100%;">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div style="flex: 0 0 350px;">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Booking Summary</h2>
                        
                        <div style="margin-top: 20px; display: flex; margin-bottom: 20px;">
                            <img src="assets/images/rooms/<?php echo $room['image']; ?>" alt="<?php echo $room['type']; ?>" 
                                 style="width: 120px; height: 80px; object-fit: cover; border-radius: 4px; margin-right: 15px;">
                            <div>
                                <h3 style="margin: 0;"><?php echo $room['type']; ?></h3>
                                <p style="margin-top: 5px; margin-bottom: 0;">For <?php echo $guests; ?> guest<?php echo $guests > 1 ? 's' : ''; ?></p>
                            </div>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                            <div>
                                <p><strong>Check-in</strong></p>
                                <p><?php echo date('l, F j, Y', strtotime($checkIn)); ?></p>
                                <p>From 3:00 PM</p>
                            </div>
                            
                            <div>
                                <p><strong>Check-out</strong></p>
                                <p><?php echo date('l, F j, Y', strtotime($checkOut)); ?></p>
                                <p>Until 12:00 PM</p>
                            </div>
                        </div>
                        
                        <div style="border-top: 1px solid #ddd; padding-top: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <p><?php echo $nights; ?> Night<?php echo $nights > 1 ? 's' : ''; ?> x $<?php echo $room['price']; ?></p>
                                <p>$<?php echo number_format($totalPrice, 2); ?></p>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <p>Taxes & Fees</p>
                                <p>Included</p>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 15px;">
                                <p>Total</p>
                                <p>$<?php echo number_format($totalPrice, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('booking-form');
    
    bookingForm.addEventListener('submit', function(event) {
        const mobilePhone = document.getElementById('mobile_phone').value.trim();
        
        if (!mobilePhone) {
            event.preventDefault();
            alert('Please provide a contact phone number.');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 