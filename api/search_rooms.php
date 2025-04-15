<?php
require_once '../includes/db.php';

// 使用输出缓冲，确保在最终输出前可以清除任何意外输出
ob_start();

header('Content-Type: application/json');

// 获取搜索参数
$checkIn = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$checkOut = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 2;

// 验证参数
if (empty($checkIn) || empty($checkOut)) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Check-in and check-out dates are required']);
    exit;
}

// 验证日期
$checkInDate = new DateTime($checkIn);
$checkOutDate = new DateTime($checkOut);
$todayDate = new DateTime();
$todayDate->setTime(0, 0, 0);

if ($checkInDate < $todayDate) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Check-in date cannot be in the past']);
    exit;
}

if ($checkOutDate <= $checkInDate) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Check-out date must be after check-in date']);
    exit;
}

// 计算住宿天数
$interval = $checkInDate->diff($checkOutDate);
$nights = $interval->days;

// 获取所有房间
$allRooms = getRooms();
$availableRooms = [];

// 筛选可容纳指定客人数量的房间
foreach ($allRooms as $room) {
    if ($room['capacity'] >= $guests && isRoomAvailable($room['id'], $checkIn, $checkOut)) {
        // 计算总价格
        $room['total_price'] = number_format($room['price'] * $nights, 2, '.', '');
        $availableRooms[] = $room;
    }
}

ob_clean();
echo json_encode([
    'success' => true, 
    'rooms' => $availableRooms, 
    'nights' => $nights,
    'check_in' => $checkIn,
    'check_out' => $checkOut,
    'guests' => $guests
]);
?> 