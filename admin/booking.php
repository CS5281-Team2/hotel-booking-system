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
        
        <div id="status-message">
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
        </div>
        
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
                            <tr style="border-bottom: 1px solid #ddd;" class="booking-row" data-booking-id="<?php echo $booking['id']; ?>">
                                <td style="padding: 12px 15px;"><?php echo substr($booking['id'], -8); ?></td>
                                <td style="padding: 12px 15px;"><?php echo $booking['user'][1]; ?></td>
                                <td style="padding: 12px 15px;"><?php echo $booking['room']['type']; ?></td>
                                <td style="padding: 12px 15px;"><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></td>
                                <td style="padding: 12px 15px;"><?php echo date('M j, Y', strtotime($booking['check_out'])); ?></td>
                                <td style="padding: 12px 15px;"><?php echo $booking['guests']; ?></td>
                                <td style="padding: 12px 15px; text-align: right;">$<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td style="padding: 12px 15px; text-align: center;" class="status-cell">
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                        <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Confirmed</span>
                                    <?php elseif ($booking['status'] == 'cancelled'): ?>
                                        <span style="background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 15px; text-align: center;" class="action-cell">
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                        <button class="cancel-booking-btn" data-booking-id="<?php echo $booking['id']; ?>" style="background-color: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer;">Cancel</button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 找到所有取消预订按钮
    const cancelButtons = document.querySelectorAll('.cancel-booking-btn');
    const statusMessage = document.getElementById('status-message');
    
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const bookingRow = this.closest('.booking-row');
            const statusCell = bookingRow.querySelector('.status-cell');
            const actionCell = bookingRow.querySelector('.action-cell');
            
            // 确认取消
            if (!confirm('Are you sure you want to cancel this booking?')) {
                return;
            }
            
            // 显示加载状态
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            // 准备表单数据
            const formData = new FormData();
            formData.append('booking_id', bookingId);
            
            // 发送AJAX请求
            fetch('../api/cancel_booking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 更新UI
                    statusCell.innerHTML = '<span style="background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Cancelled</span>';
                    actionCell.innerHTML = '-';
                    
                    // 显示成功消息
                    statusMessage.innerHTML = `
                        <div class="alert alert-success">
                            ${data.message}
                        </div>
                    `;
                    
                    // 3秒后淡出消息
                    setTimeout(() => {
                        const alert = statusMessage.querySelector('.alert');
                        if (alert) {
                            alert.style.transition = 'opacity 1s';
                            alert.style.opacity = '0';
                            setTimeout(() => {
                                statusMessage.innerHTML = '';
                            }, 1000);
                        }
                    }, 3000);
                } else {
                    // 恢复按钮状态
                    this.innerHTML = 'Cancel';
                    this.disabled = false;
                    
                    // 显示错误消息
                    statusMessage.innerHTML = `
                        <div class="alert alert-error">
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // 恢复按钮状态
                this.innerHTML = 'Cancel';
                this.disabled = false;
                
                // 显示错误消息
                statusMessage.innerHTML = `
                    <div class="alert alert-error">
                        An error occurred. Please try again later.
                    </div>
                `;
            });
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>