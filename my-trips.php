<?php
$pageTitle = 'My Trips';
include 'includes/header.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// 验证用户登录状态
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// 获取用户预订
$userId = $_SESSION['user_id'];
$bookings = getUserBookings($userId);

// 将预订分类为即将到来的和历史预订
$upcomingBookings = [];
$pastBookings = [];
$cancelledBookings = [];

$today = new DateTime();
$today->setTime(0, 0, 0);

foreach ($bookings as $booking) {
    $checkInDate = new DateTime($booking['check_in']);
    $checkOutDate = new DateTime($booking['check_out']);
    
    // 添加房间信息
    $booking['room'] = getRoomById($booking['room_id']);
    
    // 计算住宿天数
    $interval = $checkInDate->diff($checkOutDate);
    $booking['nights'] = $interval->days;
    
    if ($booking['status'] === 'cancelled') {
        $cancelledBookings[] = $booking;
    } elseif ($checkOutDate < $today) {
        $pastBookings[] = $booking;
    } else {
        $upcomingBookings[] = $booking;
    }
}

// 处理取消预订请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $bookingId = $_POST['booking_id'];
    $booking = getBookingById($bookingId);
    
    if ($booking && $booking['user_id'] == $_SESSION['user_id']) {
        // 检查是否在入住前24小时内
        $checkInDate = new DateTime($booking['check_in']);
        $now = new DateTime();
        $interval = $now->diff($checkInDate);
        $hoursUntilCheckIn = $interval->days * 24 + $interval->h;
        
        if ($hoursUntilCheckIn < 24 && $checkInDate > $now) {
            $cancelError = 'Cannot cancel booking within 24 hours of check-in';
        } else if ($checkInDate < $now) {
            $cancelError = 'Cannot cancel booking after check-in date has passed';
        } else {
            if (updateBookingStatus($bookingId, 'cancelled')) {
                $successMessage = 'Booking has been cancelled successfully';
            } else {
                $cancelError = 'Failed to cancel booking. Please try again later';
            }
        }
    } else {
        $cancelError = 'Booking does not exist or you do not have permission to cancel it';
    }
}
?>

<section style="padding: 50px 0;">
    <div class="container">
        <h1 style="margin-bottom: 20px;">My Trips</h1>
        
        <div id="status-message"></div>
        
        <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($cancelError)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $cancelError; ?>
        </div>
        <?php endif; ?>
        
        <?php if (empty($bookings)): ?>
            <div style="text-align: center; padding: 30px; background-color: #f9f9f9; border-radius: 8px; margin-top: 20px;">
                <p>You don't have any upcoming stays.</p>
                <a href="index.php" class="btn btn-primary" style="margin-top: 15px;">Book a Room</a>
            </div>
        <?php else: ?>
            <div style="margin-bottom: 40px;">
                <h2>Upcoming Stays</h2>
                
                <?php if (empty($upcomingBookings)): ?>
                    <div style="text-align: center; padding: 30px; background-color: #f9f9f9; border-radius: 8px; margin-top: 20px;">
                        <p>You don't have any upcoming stays.</p>
                        <a href="index.php" class="btn btn-primary" style="margin-top: 15px;">Book a Room</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingBookings as $booking): ?>
                        <div class="card booking-card" data-booking-id="<?php echo $booking['id']; ?>" style="margin-top: 20px;">
                            <div class="card-body" style="display: flex; flex-wrap: wrap;">
                                <div style="flex: 0 0 150px; margin-right: 20px;">
                                    <img src="https://via.placeholder.com/150x100?text=<?php echo str_replace(' ', '+', $booking['room']['type']); ?>" alt="<?php echo $booking['room']['type']; ?>" style="width: 150px; height: 100px; object-fit: cover; border-radius: 4px;">
                                </div>
                                
                                <div style="flex: 1; min-width: 300px;">
                                    <h3><?php echo $booking['room']['type']; ?></h3>
                                    
                                    <div style="display: flex; margin-top: 10px;">
                                        <div style="margin-right: 30px;">
                                            <p><strong>Check-in:</strong> <?php echo date('M j, Y', strtotime($booking['check_in'])); ?></p>
                                            <p><strong>Check-out:</strong> <?php echo date('M j, Y', strtotime($booking['check_out'])); ?></p>
                                        </div>
                                        
                                        <div>
                                            <p><strong>Guests:</strong> <?php echo $booking['guests']; ?></p>
                                            <p><strong>Nights:</strong> <?php echo $booking['nights']; ?></p>
                                            <?php if (isset($booking['mobile_phone']) && !empty($booking['mobile_phone'])): ?>
                                            <p><strong>Contact:</strong> <?php echo $booking['mobile_phone']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="flex: 0 0 200px; text-align: right;">
                                    <p><strong>Total:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                    <p><strong>Status:</strong> <span class="booking-status" style="color: #28a745;">Confirmed</span></p>
                                    
                                    <div class="cancel-button-container">
                                        <button class="btn btn-primary cancel-booking-btn" data-booking-id="<?php echo $booking['id']; ?>" style="background-color: #dc3545; margin-top: 10px;">Cancel Booking</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 40px;">
                <h2>Past Stays</h2>
                
                <?php if (empty($pastBookings)): ?>
                    <div style="text-align: center; padding: 30px; background-color: #f9f9f9; border-radius: 8px; margin-top: 20px;">
                        <p>You don't have any past stays.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pastBookings as $booking): ?>
                        <div class="card" style="margin-top: 20px;">
                            <div class="card-body" style="display: flex; flex-wrap: wrap;">
                                <div style="flex: 0 0 150px; margin-right: 20px;">
                                    <img src="https://via.placeholder.com/150x100?text=<?php echo str_replace(' ', '+', $booking['room']['type']); ?>" alt="<?php echo $booking['room']['type']; ?>" style="width: 150px; height: 100px; object-fit: cover; border-radius: 4px;">
                                </div>
                                
                                <div style="flex: 1; min-width: 300px;">
                                    <h3><?php echo $booking['room']['type']; ?></h3>
                                    
                                    <div style="display: flex; margin-top: 10px;">
                                        <div style="margin-right: 30px;">
                                            <p><strong>Check-in:</strong> <?php echo date('M j, Y', strtotime($booking['check_in'])); ?></p>
                                            <p><strong>Check-out:</strong> <?php echo date('M j, Y', strtotime($booking['check_out'])); ?></p>
                                        </div>
                                        
                                        <div>
                                            <p><strong>Guests:</strong> <?php echo $booking['guests']; ?></p>
                                            <p><strong>Nights:</strong> <?php echo $booking['nights']; ?></p>
                                            <?php if (isset($booking['mobile_phone']) && !empty($booking['mobile_phone'])): ?>
                                            <p><strong>Contact:</strong> <?php echo $booking['mobile_phone']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="flex: 0 0 200px; text-align: right;">
                                    <p><strong>Total:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                    <p><strong>Status:</strong> <span style="color: #777;">Completed</span></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($cancelledBookings)): ?>
                <div>
                    <h2>Cancelled Bookings</h2>
                    
                    <?php foreach ($cancelledBookings as $booking): ?>
                        <div class="card" style="margin-top: 20px;">
                            <div class="card-body" style="display: flex; flex-wrap: wrap;">
                                <div style="flex: 0 0 150px; margin-right: 20px;">
                                    <img src="https://via.placeholder.com/150x100?text=<?php echo str_replace(' ', '+', $booking['room']['type']); ?>" alt="<?php echo $booking['room']['type']; ?>" style="width: 150px; height: 100px; object-fit: cover; border-radius: 4px;">
                                </div>
                                
                                <div style="flex: 1; min-width: 300px;">
                                    <h3><?php echo $booking['room']['type']; ?></h3>
                                    
                                    <div style="display: flex; margin-top: 10px;">
                                        <div style="margin-right: 30px;">
                                            <p><strong>Check-in:</strong> <?php echo date('M j, Y', strtotime($booking['check_in'])); ?></p>
                                            <p><strong>Check-out:</strong> <?php echo date('M j, Y', strtotime($booking['check_out'])); ?></p>
                                        </div>
                                        
                                        <div>
                                            <p><strong>Guests:</strong> <?php echo $booking['guests']; ?></p>
                                            <p><strong>Nights:</strong> <?php echo $booking['nights']; ?></p>
                                            <?php if (isset($booking['mobile_phone']) && !empty($booking['mobile_phone'])): ?>
                                            <p><strong>Contact:</strong> <?php echo $booking['mobile_phone']; ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="flex: 0 0 200px; text-align: right;">
                                    <p><strong>Total:</strong> $<?php echo number_format($booking['total_price'], 2); ?></p>
                                    <p><strong>Status:</strong> <span style="color: #dc3545;">Cancelled</span></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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
            const bookingCard = this.closest('.booking-card');
            const buttonContainer = this.parentElement;
            
            // 确认取消
            if (!confirm('Are you sure you want to cancel this booking?')) {
                return;
            }
            
            // 显示加载状态
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
            this.disabled = true;
            
            // 准备表单数据
            const formData = new FormData();
            formData.append('booking_id', bookingId);
            
            // 发送AJAX请求
            fetch('api/cancel_booking.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 更新UI
                    bookingCard.querySelector('.booking-status').textContent = 'Cancelled';
                    bookingCard.querySelector('.booking-status').style.color = '#dc3545';
                    buttonContainer.innerHTML = '-';
                    
                    // 显示成功消息
                    statusMessage.innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ${data.message}
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
                    this.innerHTML = 'Cancel Booking';
                    this.disabled = false;
                    
                    // 显示错误消息
                    statusMessage.innerHTML = `
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // 恢复按钮状态
                this.innerHTML = 'Cancel Booking';
                this.disabled = false;
                
                // 显示错误消息
                statusMessage.innerHTML = `
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> An error occurred. Please try again later.
                    </div>
                `;
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>