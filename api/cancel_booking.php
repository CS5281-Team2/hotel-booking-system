<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/mail.php';

// 使用输出缓冲，确保在最终输出前可以清除任何意外输出
ob_start();

// 设置内容类型头部
header('Content-Type: application/json');

// 验证用户是否登录
if (!isLoggedIn()) {
    // 清除缓冲区
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel a booking.']);
    exit;
}

// 获取预订ID
$bookingId = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';
$booking = getBookingById($bookingId);

if (!$booking) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Booking not found. Please refresh the page and try again.']);
    exit;
}

// 检查权限 - 普通用户只能取消自己的预订
if (!isAdmin() && $booking['user_id'] != $_SESSION['user_id']) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'You do not have permission to cancel this booking.']);
    exit;
}

// 管理员可以随时取消预订，无需检查时间限制
// 仅对普通用户检查取消限制
if (!isAdmin()) {
    $checkInDate = new DateTime($booking['check_in']);
    $now = new DateTime();
    
    $interval = $now->diff($checkInDate);
    $hoursUntilCheckIn = $interval->days * 24 + $interval->h;
    
    if ($hoursUntilCheckIn < 24 && $checkInDate > $now) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Cancellation is not permitted within 24 hours of check-in. Please contact our hotel directly for assistance.']);
        exit;
    }
    
    // 不能取消已经过了入住日期的预订
    if ($checkInDate < $now) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Cannot cancel a booking after the check-in date has passed. Please contact our hotel directly if you need assistance.']);
        exit;
    }
}

// 更新预订状态
if (updateBookingStatus($bookingId, 'cancelled')) {
    // 获取房间信息
    $room = getRoomById($booking['room_id']);
    
    // 尝试发送确认邮件，但即使邮件发送失败也返回成功
    try {
        @sendBookingCancellationEmail(
            $_SESSION['user_email'],
            $_SESSION['user_name'],
            $booking,
            $room
        );
    } catch (Exception $e) {
        // 记录错误但不影响返回结果
        error_log("Error sending cancellation email: " . $e->getMessage());
    }
    
    // 清除缓冲区，确保只有JSON被输出
    ob_clean();
    echo json_encode(['success' => true, 'message' => 'Your booking has been cancelled successfully. A confirmation email will be sent shortly.']);
} else {
    // 清除缓冲区
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Failed to cancel booking. Please try again later or contact our support team.']);
}
?> 