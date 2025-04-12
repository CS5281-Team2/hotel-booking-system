<?php
$pageTitle = 'Book Room';
include 'includes/header.php';
require_once 'includes/db.php';

// 检查用户是否已登录
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// 获取预订信息
$roomId = isset($_GET['room_id']) ? $_GET['room_id'] : '';
$checkIn = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$checkOut = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;

// 获取房间信息
$room = getRoomById($roomId);

// 验证数据
$validData = true;
$errorMessage = '';

if (!$room) {
    $validData = false;
    $errorMessage = 'Invalid room selected';
} elseif (empty($checkIn) || empty($checkOut)) {
    $validData = false;
    $errorMessage = 'Please select check-in and check-out dates';
} else {
    // 验证日期
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    if ($checkInDate < $today) {
        $validData = false;
        $errorMessage = 'Check-in date cannot be in the past';
    } elseif ($checkOutDate <= $checkInDate) {
        $validData = false;
        $errorMessage = 'Check-out date must be after check-in date';
    }
    
    // 验证客人数量
    if ($guests < 1 || $guests > $room['capacity']) {
        $validData = false;
        $errorMessage = 'Invalid number of guests';
    }
    
    // 验证房间可用性
    if (!isRoomAvailable($roomId, $checkIn, $checkOut)) {
        $validData = false;
        $errorMessage = 'Room is not available for the selected dates';
    }
}

// 计算总价
$totalPrice = 0;
$nights = 0;

if ($validData) {
    $interval = $checkInDate->diff($checkOutDate);
    $nights = $interval->days;
    $totalPrice = $room['price'] * $nights;
}

// 处理预订提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validData) {
    // 验证支付信息
    $cardName = isset($_POST['card_name']) ? trim($_POST['card_name']) : '';
    $cardNumber = isset($_POST['card_number']) ? str_replace(' ', '', $_POST['card_number']) : '';
    $cardExpiry = isset($_POST['card_expiry']) ? trim($_POST['card_expiry']) : '';
    $cardCvv = isset($_POST['card_cvv']) ? trim($_POST['card_cvv']) : '';
    
    $validPayment = true;
    
    if (empty($cardName)) {
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
            $bookingId,
            $userId,
            $roomId,
            $checkIn,
            $checkOut,
            $guests,
            $totalPrice,
            $status,
            $createdAt
        ];
        
        if (addBooking($bookingData)) {
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
        <?php if (!$validData): ?>
        <div class="alert alert-error">
            <?php echo $errorMessage; ?>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="search.php" class="btn btn-primary">Back to Search</a>
        </div>
        <?php else: ?>
        <h1 style="margin-bottom: 30px;">Complete Your Booking</h1>

        <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error">
            <?php echo $errorMessage; ?>
        </div>
        <?php endif; ?>

        <div style="display: flex; flex-wrap: wrap;">
            <div style="flex: 0 0 60%; max-width: 60%; padding-right: 30px;">
                <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" id="booking-form">
                    <h2>Guest Information</h2>

                    <div class="form-group">
                        <label for="guest_name">Guest Name</label>
                        <input type="text" id="guest_name" name="guest_name" class="form-control"
                            value="<?php echo $_SESSION['user_name']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="guest_email">Email</label>
                        <input type="email" id="guest_email" name="guest_email" class="form-control"
                            value="<?php echo $_SESSION['user_email']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">Special Requests (optional)</label>
                        <textarea id="special_requests" name="special_requests" class="form-control"
                            rows="3"></textarea>
                    </div>

                    <h2 style="margin-top: 30px;">Payment Information</h2>

                    <div class="form-group">
                        <label for="card_name">Name on Card</label>
                        <input type="text" id="card_name" name="card_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" class="form-control"
                            placeholder="1234 5678 9012 3456" required>
                    </div>

                    <div style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label for="card_expiry">Expiry Date (MM/YY)</label>
                            <input type="text" id="card_expiry" name="card_expiry" class="form-control"
                                placeholder="MM/YY" required>
                        </div>

                        <div class="form-group" style="flex: 1;">
                            <label for="card_cvv">CVV</label>
                            <input type="text" id="card_cvv" name="card_cvv" class="form-control" placeholder="123"
                                required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Complete Booking</button>
                    </div>
                </form>
            </div>

            <div style="flex: 0 0 40%; max-width: 40%;">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Booking Summary</h2>

                        <div style="display: flex; margin-bottom: 20px;">
                            <img src="assets/images/rooms/<?php echo $room['image']; ?>"
                                alt="<?php echo $room['type']; ?>"
                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; margin-right: 15px;">
                            <div>
                                <h3><?php echo $room['type']; ?></h3>
                                <p><?php echo $room['breakfast'] == 'Yes' ? 'Breakfast Included' : 'No Breakfast'; ?>
                                </p>
                            </div>
                        </div>

                        <div
                            style="border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; padding: 15px 0; margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <p><strong><i class="fas fa-calendar-check"></i> Check-in:</strong></p>
                                <p><?php echo date('l, F j, Y', strtotime($checkIn)); ?></p>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <p><strong><i class="fas fa-calendar-times"></i> Check-out:</strong></p>
                                <p><?php echo date('l, F j, Y', strtotime($checkOut)); ?></p>
                            </div>

                            <div style="display: flex; justify-content: space-between;">
                                <p><strong><i class="fas fa-users"></i> Guests:</strong></p>
                                <p><?php echo $guests; ?> Person<?php echo $guests > 1 ? 's' : ''; ?></p>
                            </div>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <p><?php echo $nights; ?> Night<?php echo $nights > 1 ? 's' : ''; ?> x
                                    $<?php echo $room['price']; ?></p>
                                <p>$<?php echo number_format($totalPrice, 2); ?></p>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <p>Taxes & Fees</p>
                                <p>Included</p>
                            </div>
                        </div>

                        <div
                            style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; border-top: 1px solid #ddd; padding-top: 15px;">
                            <p>Total</p>
                            <p>$<?php echo number_format($totalPrice, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('booking-form');

    if (bookingForm) {
        // 验证信用卡号格式
        const cardNumberInput = document.getElementById('card_number');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                // 移除所有非数字字符
                let value = this.value.replace(/\D/g, '');

                // 限制长度为16位
                if (value.length > 16) {
                    value = value.slice(0, 16);
                }

                // 添加空格格式化
                let formattedValue = '';
                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formattedValue += ' ';
                    }
                    formattedValue += value[i];
                }

                this.value = formattedValue;
            });
        }

        // 验证过期日期格式
        const expiryInput = document.getElementById('card_expiry');
        if (expiryInput) {
            expiryInput.addEventListener('input', function(e) {
                // 移除所有非数字字符
                let value = this.value.replace(/\D/g, '');

                // 限制长度为4位
                if (value.length > 4) {
                    value = value.slice(0, 4);
                }

                // 添加斜杠格式化
                if (value.length > 2) {
                    value = value.slice(0, 2) + '/' + value.slice(2);
                }

                this.value = value;
            });
        }

        // 验证CVV格式
        const cvvInput = document.getElementById('card_cvv');
        if (cvvInput) {
            cvvInput.addEventListener('input', function(e) {
                // 仅允许数字
                let value = this.value.replace(/\D/g, '');

                // 限制长度为3位
                if (value.length > 3) {
                    value = value.slice(0, 3);
                }

                this.value = value;
            });
        }

        // 表单提交验证
        bookingForm.addEventListener('submit', function(event) {
            let isValid = true;
            let errorMessage = '';

            // 验证持卡人姓名
            const cardName = document.getElementById('card_name').value.trim();
            if (cardName === '') {
                isValid = false;
                errorMessage = 'Please enter the name on card';
            }

            // 验证卡号
            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            if (cardNumber === '' || cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                isValid = false;
                errorMessage = 'Please enter a valid 16-digit card number';
            }

            // 验证过期日期
            const expiry = document.getElementById('card_expiry').value;
            if (expiry === '' || !expiry.match(/^\d{2}\/\d{2}$/)) {
                isValid = false;
                errorMessage = 'Please enter a valid expiry date (MM/YY)';
            }

            // 验证CVV
            const cvv = document.getElementById('card_cvv').value;
            if (cvv === '' || cvv.length !== 3 || !/^\d+$/.test(cvv)) {
                isValid = false;
                errorMessage = 'Please enter a valid 3-digit CVV';
            }

            if (!isValid) {
                event.preventDefault();
                alert(errorMessage);
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>