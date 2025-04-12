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