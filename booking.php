<?php
$pageTitle = 'Manage Bookings';
$adminPage = true;
include '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// 验证管理员权限
if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// 获取所有预订
$allBookings = getBookings();

// 按日期降序排序（最新的在前）
usort($allBookings, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// 处理取消预订
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $bookingId = $_POST['booking_id'];
    $booking = getBookingById($bookingId);
    
    if ($booking) {
        // 检查是否是入住当天
        $checkInDate = new DateTime($booking['check_in']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        // 如果今天已经是入住日期，不允许取消
        if ($checkInDate->format('Y-m-d') == $today->format('Y-m-d')) {
            $errorMessage = 'Cannot cancel bookings on check-in day';
        } else {
            if (updateBookingStatus($bookingId, 'cancelled')) {
                $successMessage = 'Booking has been cancelled successfully';
            } else {
                $errorMessage = 'Failed to cancel booking. Please try again later';
            }
        }
    } else {
        $errorMessage = 'Booking not found';
    }
}

// 添加房间和用户信息到预订
foreach ($allBookings as &$booking) {
    $booking['room'] = getRoomById($booking['room_id']);
    $booking['user'] = getUserById($booking['user_id']);
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
            'id' => $bookingId,
            'user_id' => $userId,
            'room_id' => $roomId,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'total_price' => $totalPrice,
            'status' => $status,
            'created_at' => $createdAt
        ];
        
        $bookingString = implode('|', [
            $bookingId,
            $userId,
            $roomId,
            $checkIn,
            $checkOut,
            $guests,
            $totalPrice,
            $status,
            $createdAt
        ]);
        
        if (addBooking($bookingData)) {
            // 发送确认邮件
            require_once 'includes/mail.php';
            
            // 获取用户和房间信息
            $user = getUserById($userId);
            
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
        <h1 style="margin-bottom: 20px;">Manage Bookings</h1>
        
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-error">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>
        
        <div style="overflow-x: auto; margin-top: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: var(--primary-color); color: white;">
                        <th style="padding: 12px 15px; text-align: left;">ID</th>
                        <th style="padding: 12px 15px; text-align: left;">Guest</th>
                        <th style="padding: 12px 15px; text-align: left;">Room</th>
                        <th style="padding: 12px 15px; text-align: left;">Check-in</th>
                        <th style="padding: 12px 15px; text-align: left;">Check-out</th>
                        <th style="padding: 12px 15px; text-align: left;">Guests</th>
                        <th style="padding: 12px 15px; text-align: right;">Total</th>
                        <th style="padding: 12px 15px; text-align: center;">Status</th>
                        <th style="padding: 12px 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allBookings)): ?>
                        <tr>
                            <td colspan="9" style="padding: 20px; text-align: center;">No bookings found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allBookings as $booking): ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px 15px;"><?php echo substr($booking['id'], -8); ?></td>
                                <td style="padding: 12px 15px;"><?php echo $booking['user'][1]; ?></td>
                                <td style="padding: 12px 15px;"><?php echo $booking['room']['type']; ?></td>
                                <td style="padding: 12px 15px;"><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></td>
                                <td style="padding: 12px 15px;"><?php echo date('M j, Y', strtotime($booking['check_out'])); ?></td>
                                <td style="padding: 12px 15px;"><?php echo $booking['guests']; ?></td>
                                <td style="padding: 12px 15px; text-align: right;">$<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td style="padding: 12px 15px; text-align: center;">
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                        <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Confirmed</span>
                                    <?php elseif ($booking['status'] == 'cancelled'): ?>
                                        <span style="background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 15px; text-align: center;">
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                        <form action="booking.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="cancel_booking" style="background-color: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">Cancel</button>
                                        </form>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>