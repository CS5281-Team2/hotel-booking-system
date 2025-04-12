<?php
$pageTitle = 'Book a Room';
include 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// 验证用户登录状态
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 获取请求参数
$roomId = isset($_GET['room_id']) ? $_GET['room_id'] : '';
$checkIn = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$checkOut = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 2;

// 验证数据
$validData = false;
$errorMessage = '';

if (empty($roomId) || empty($checkIn) || empty($checkOut)) {
    $errorMessage = 'Missing required booking information';
} else {
    // 获取房间信息
    $room = getRoomById($roomId);
    
    if (!$room) {
        $errorMessage = 'Room not found';
    } else {
        // 验证日期
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $todayDate = new DateTime();
        $todayDate->setTime(0, 0, 0);
        
        if ($checkInDate < $todayDate) {
            $errorMessage = 'Check-in date cannot be in the past';
        } elseif ($checkOutDate <= $checkInDate) {
            $errorMessage = 'Check-out date must be after check-in date';
        } elseif ($guests > $room['capacity']) {
            $errorMessage = 'Room capacity exceeded';
        } elseif (!isRoomAvailable($roomId, $checkIn, $checkOut)) {
            $errorMessage = 'Room is not available for the selected dates';
        } else {
            $validData = true;
            
            // 计算住宿天数和总价格
            $interval = $checkInDate->diff($checkOutDate);
            $nights = $interval->days;
            $totalPrice = $room['price'] * $nights;
        }
    }
}

// 处理预订提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validData) {
    // 验证支付信息
    $cardName = isset($_POST['card_name']) ? trim($_POST['card_name']) : '';
    $cardNumber = isset($_POST['card_number']) ? str_replace(' ', '', $_POST['card_number']) : '';
    $cardExpiry = isset($_POST['card_expiry']) ? trim($_POST['card_expiry']) : '';
    $cardCvv = isset($_POST['card_cvv']) ? trim($_POST['card_cvv']) : '';
    $mobilePhone = isset($_POST['mobile_phone']) ? trim($_POST['mobile_phone']) : '';
    
    $validPayment = true;
    
    // 验证手机号码（支持中国大陆和香港格式）
    if (empty($mobilePhone)) {
        $validPayment = false;
        $errorMessage = 'Please enter your mobile phone number';
    } elseif (!preg_match('/^1[3-9]\d{9}$/', $mobilePhone) && !preg_match('/^[5-9]\d{7}$/', $mobilePhone)) {
        $validPayment = false;
        $errorMessage = 'Please enter a valid phone number (China mainland: 11 digits starting with 1, Hong Kong: 8 digits starting with 5-9)';
    } elseif (empty($cardName)) {
        $validPayment = false;
        $errorMessage = 'Please enter the name on card';
    } elseif (empty($cardNumber) || !preg_match('/^\d{16}$/', $cardNumber)) {
        $validPayment = false;
        $errorMessage = 'Please enter a valid 16-digit card number';
    } elseif (empty($cardExpiry) || !preg_match('/^\d{2}\/\d{2}$/', $cardExpiry)) {
        $validPayment = false;
        $errorMessage = 'Please enter a valid expiry date (MM/YY)';
    } elseif (empty($cardCvv) || !preg_match('/^\d{3}$/', $cardCvv)) {
        $validPayment = false;
        $errorMessage = 'Please enter a valid 3-digit CVV';
    }
    
    if ($validPayment) {
        // 创建预订
        $bookingId = generateId();
        $userId = $_SESSION['user_id'];
        $status = 'confirmed';
        $createdAt = date('Y-m-d H:i:s');
        
        $bookingData = [
            'id' => $bookingId,
            'user_id' => $userId,
            'room_id' => $roomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'total_price' => $totalPrice,
            'status' => $status,
            'created_at' => $createdAt,
            'mobile_phone' => $mobilePhone
        ];
        
        if (addBooking($bookingData)) {
            // 发送确认邮件
            require_once 'includes/mail.php';
            
            // 发送确认邮件
            sendBookingConfirmationEmail(
                $_SESSION['user_email'],
                $_SESSION['user_name'],
                $bookingData,
                $room
            );
            
            // 重定向到确认页面
            header("Location: confirmation.php?booking_id=$bookingId");
            exit;
        } else {
            $errorMessage = 'Failed to create booking. Please try again.';
        }
    }
}
?>

<section style="padding: 50px 0;">
    <div class="container">
        <h1 style="margin-bottom: 20px;">Complete Your Booking</h1>
        
        <?php if (!$validData): ?>
            <div class="alert alert-error">
                <?php echo $errorMessage; ?>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="search.php" class="btn btn-primary">Back to Search</a>
            </div>
        <?php else: ?>
            <div style="display: flex; margin-bottom: 20px;">
                <div style="flex: 0 0 300px; margin-right: 30px;">
                    <img src="assets/images/rooms/<?php echo $room['image']; ?>" alt="<?php echo $room['type']; ?>" style="width: 100%; border-radius: 4px;">
                    
                    <div style="margin-top: 20px; border: 1px solid #ddd; border-radius: 4px; padding: 15px;">
                        <h3><?php echo $room['type']; ?></h3>
                        <p><?php echo $room['description']; ?></p>
                        
                        <div style="margin-top: 10px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span>Guests:</span>
                                <span><?php echo $guests; ?> Person<?php echo $guests > 1 ? 's' : ''; ?></span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span>Breakfast:</span>
                                <span><?php echo $room['breakfast'] == 'Yes' ? 'Included' : 'Not included'; ?></span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span>Check-in:</span>
                                <span><?php echo date('M j, Y', strtotime($checkIn)); ?></span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span>Check-out:</span>
                                <span><?php echo date('M j, Y', strtotime($checkOut)); ?></span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span>Price per night:</span>
                                <span>$<?php echo $room['price']; ?></span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; font-weight: bold; margin-top: 10px; padding-top: 10px; border-top: 1px solid #ddd;">
                                <span>Total (<?php echo $nights; ?> nights):</span>
                                <span>$<?php echo number_format($totalPrice, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="flex: 1;">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title">Payment Information</h2>
                            
                            <?php if (!empty($errorMessage)): ?>
                                <div class="alert alert-error">
                                    <?php echo $errorMessage; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form action="booking.php?room_id=<?php echo $roomId; ?>&check_in=<?php echo urlencode($checkIn); ?>&check_out=<?php echo urlencode($checkOut); ?>&guests=<?php echo $guests; ?>" method="POST">
                                <div style="margin-bottom: 15px;">
                                    <label for="mobile_phone">Mobile Phone Number</label>
                                    <input type="text" id="mobile_phone" name="mobile_phone" class="form-control" placeholder="China: 13812345678 / Hong Kong: 51234567" required>
                                    <small style="color: #666; font-size: 0.8rem;">For booking confirmation and updates</small>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="card_name">Name on Card</label>
                                    <input type="text" id="card_name" name="card_name" class="form-control" required>
                                </div>
                                
                                <div style="margin-bottom: 15px;">
                                    <label for="card_number">Card Number</label>
                                    <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required>
                                </div>
                                
                                <div style="display: flex; margin-bottom: 15px;">
                                    <div style="flex: 1; margin-right: 10px;">
                                        <label for="card_expiry">Expiry Date (MM/YY)</label>
                                        <input type="text" id="card_expiry" name="card_expiry" class="form-control" placeholder="MM/YY" required>
                                    </div>
                                    
                                    <div style="flex: 1;">
                                        <label for="card_cvv">CVV</label>
                                        <input type="text" id="card_cvv" name="card_cvv" class="form-control" placeholder="123" required>
                                    </div>
                                </div>
                                
                                <div style="margin-top: 20px;">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">Complete Booking</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <a href="search.php" class="btn btn-outline">Back to Search</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.querySelector('form');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(event) {
            let hasError = false;
            let errorMessage = '';
            
            // 获取表单字段值
            const mobilePhone = document.getElementById('mobile_phone').value.trim();
            const cardName = document.getElementById('card_name').value.trim();
            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            const cardExpiry = document.getElementById('card_expiry').value.trim();
            const cardCvv = document.getElementById('card_cvv').value.trim();
            
            // 验证手机号
            if (mobilePhone === '') {
                errorMessage = 'Please enter your mobile phone number';
                hasError = true;
            }
            // 验证手机号格式 - 中国大陆或香港
            else if (!(/^1[3-9]\d{9}$/.test(mobilePhone) || /^[5-9]\d{7}$/.test(mobilePhone))) {
                errorMessage = 'Please enter a valid phone number (China mainland: 11 digits starting with 1, Hong Kong: 8 digits starting with 5-9)';
                hasError = true;
            }
            // 验证持卡人姓名
            else if (cardName === '') {
                errorMessage = 'Please enter the name on card';
                hasError = true;
            }
            // 验证卡号
            else if (cardNumber === '' || !/^\d{16}$/.test(cardNumber)) {
                errorMessage = 'Please enter a valid 16-digit card number';
                hasError = true;
            }
            // 验证有效期
            else if (cardExpiry === '' || !/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                errorMessage = 'Please enter a valid expiry date (MM/YY)';
                hasError = true;
            }
            // 验证CVV
            else if (cardCvv === '' || !/^\d{3}$/.test(cardCvv)) {
                errorMessage = 'Please enter a valid 3-digit CVV';
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