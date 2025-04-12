<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/mail.php';
header('Content-Type: application/json');

// 验证用户是否登录
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to cancel a booking']);
    exit;
}

// 获取预订ID
$bookingId = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';
$booking = getBookingById($bookingId);

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
}

// 检查权限 - 普通用户只能取消自己的预订
if (!isAdmin() && $booking['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to cancel this booking']);
    exit;
}

// 检查取消限制
$checkInDate = new DateTime($booking['check_in']);
$now = new DateTime();

// 管理员不能在入住当天取消
if (isAdmin() && $checkInDate->format('Y-m-d') == $now->format('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'Cannot cancel bookings on check-in day']);
    exit;
}

// 用户不能在入住前24小时内取消
if (!isAdmin()) {
    $interval = $now->diff($checkInDate);
    $hoursUntilCheckIn = $interval->days * 24 + $interval->h;
    
    if ($hoursUntilCheckIn < 24 && $checkInDate > $now) {
        echo json_encode(['success' => false, 'message' => 'Cannot cancel booking within 24 hours of check-in']);
        exit;
    }
}

// 不能取消已经过了入住日期的预订
if ($checkInDate < $now) {
    echo json_encode(['success' => false, 'message' => 'Cannot cancel booking after check-in date has passed']);
    exit;
}

// 更新预订状态
if (updateBookingStatus($bookingId, 'cancelled')) {
    // 获取房间信息
    $room = getRoomById($booking['room_id']);
    
    // 发送确认邮件
    sendBookingCancellationEmail(
        $_SESSION['user_email'],
        $_SESSION['user_name'],
        $booking,
        $room
    );
    
    echo json_encode(['success' => true, 'message' => 'Booking has been cancelled successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel booking. Please try again later']);
}
?> 