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

// 自动清理预订数据文件中的重复记录
cleanupDuplicateBookings();

// 获取所有预订
$allBookings = getBookings();

// 过滤掉重复的预订
$uniqueBookings = [];
$processedIds = [];

foreach ($allBookings as $booking) {
    if (!in_array($booking['id'], $processedIds)) {
        $processedIds[] = $booking['id'];
        $uniqueBookings[] = $booking;
    }
}

// 使用过滤后的唯一预订
$allBookings = $uniqueBookings;

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
        // 移除入住当天不能取消的限制
        if (updateBookingStatus($bookingId, 'cancelled')) {
            $successMessage = 'The booking has been cancelled successfully. A confirmation email has been sent to the guest.';
        } else {
            $errorMessage = 'Failed to cancel booking. Please try again later or contact system administrator.';
        }
    } else {
        $errorMessage = 'Booking not found. Please refresh the page and try again.';
    }
}

// 添加房间和用户信息到预订
foreach ($allBookings as $key => $booking) {
    $allBookings[$key]['room'] = getRoomById($booking['room_id']);
    $allBookings[$key]['user'] = getUserById($booking['user_id']);
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
                        <th style="padding: 12px 15px; text-align: left;">Contact</th>
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
                            <td colspan="10" style="padding: 20px; text-align: center;">No bookings found</td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        // 移除 rowIndex，因为第一列应该是 Booking ID
                        // $rowIndex = 1;
                        foreach ($allBookings as $booking): 
                        ?>
                            <tr style="border-bottom: 1px solid #ddd;" class="booking-row" data-booking-id="<?php echo $booking['id']; ?>">
                                <td style="padding: 12px 15px;"><?php echo substr($booking['id'], -8); ?></td>
                                <td style="padding: 12px 15px;">
                                    <?php 
                                        if (isset($booking['user']) && is_array($booking['user'])) {
                                            // 检查用户数据是否是关联数组
                                            if (isset($booking['user']['name'])) {
                                                // 新格式：关联数组
                                                echo $booking['user']['name'];
                                            } elseif (isset($booking['user'][1])) {
                                                // 旧格式：索引数组
                                                echo $booking['user'][1];
                                            } else {
                                                echo 'Guest Info Not Available';
                                            }
                                        } else {
                                            echo 'Guest Info Not Available';
                                        }
                                    ?>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <?php 
                                        if (isset($booking['mobile_phone']) && !empty($booking['mobile_phone'])) {
                                            echo $booking['mobile_phone'];
                                        } elseif (isset($booking['user']) && is_array($booking['user'])) {
                                            // 检查用户数据是否是关联数组
                                            if (isset($booking['user']['phone'])) {
                                                // 新格式：关联数组
                                                echo $booking['user']['phone'];
                                            } elseif (isset($booking['user'][3])) {
                                                // 旧格式：索引数组
                                                echo $booking['user'][3];
                                            } else {
                                                echo 'Contact Not Available';
                                            }
                                        } else {
                                            echo 'Contact Not Available';
                                        }
                                    ?>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <?php 
                                        if (isset($booking['room']) && is_array($booking['room']) && isset($booking['room']['type'])) {
                                            echo $booking['room']['type'];
                                        } else {
                                            echo 'Room Info Not Available';
                                        }
                                    ?>
                                </td>
                                <td style="padding: 12px 15px;"><?php echo date('M j, Y', strtotime($booking['check_in'])); ?></td>
                                <td style="padding: 12px 15px;"><?php echo date('M j, Y', strtotime($booking['check_out'])); ?></td>
                                <td style="padding: 12px 15px;"><?php echo $booking['guests']; ?></td>
                                <td style="padding: 12px 15px; text-align: right;">$<?php echo number_format($booking['total_price'], 2); ?></td>
                                <td style="padding: 12px 15px; text-align: center;" class="status-cell">
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                        <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Confirmed</span>
                                    <?php elseif ($booking['status'] == 'cancelled'): ?>
                                        <span style="background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Cancelled</span>
                                    <?php elseif ($booking['status'] == 'completed'): ?>
                                        <span style="background-color: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Completed</span>
                                    <?php elseif ($booking['status'] == 'active'): ?>
                                        <span style="background-color: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Active</span>
                                    <?php else: ?>
                                        <span style="background-color: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;"><?php echo ucfirst($booking['status']); ?></span>
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
            if (!confirm('Are you sure you want to cancel this booking? This action cannot be undone and will notify the guest.')) {
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
                body: formData,
                // 添加请求超时
                signal: AbortSignal.timeout(10000) // 10秒超时
            })
            .then(response => {
                // 检查响应状态
                if (!response.ok) {
                    throw new Error('Server responded with status: ' + response.status);
                }
                // 获取原始文本响应
                return response.text();
            })
            .then(text => {
                // 尝试解析JSON
                let data;
                try {
                    // 清理响应中可能存在的HTML警告信息
                    let cleanedText = text.replace(/<br\s*\/?>\s*<b>Warning<\/b>.+?<br\s*\/?>/gi, '');
                    data = JSON.parse(cleanedText);
                } catch (e) {
                    console.error("JSON parse error:", e, "Response text:", text);
                    throw new Error('Invalid JSON response');
                }
                
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