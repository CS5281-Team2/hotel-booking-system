<?php
// 定义数据文件路径
define('USERS_FILE', __DIR__ . '/../data/users.txt');
define('ROOMS_FILE', __DIR__ . '/../data/rooms.txt');
define('BOOKINGS_FILE', __DIR__ . '/../data/bookings.txt');

// 确保数据目录存在
if (!file_exists(__DIR__ . '/../data')) {
    mkdir(__DIR__ . '/../data', 0777, true);
}

// 确保数据文件存在
if (!file_exists(USERS_FILE)) file_put_contents(USERS_FILE, '');
if (!file_exists(ROOMS_FILE)) file_put_contents(ROOMS_FILE, '');
if (!file_exists(BOOKINGS_FILE)) file_put_contents(BOOKINGS_FILE, '');

/**
 * 获取所有用户数据
 */
function getUsers() {
    $content = file_get_contents(USERS_FILE);
    $users = $content ? explode("\n", $content) : [];
    return array_filter($users); // 移除空行
}

/**
 * 添加新用户
 */
function addUser($userData) {
    $userString = implode('|', $userData);
    file_put_contents(USERS_FILE, $userString . "\n", FILE_APPEND);
    return true;
}

/**
 * 通过ID获取用户
 */
function getUserById($userId) {
    $users = getUsers();
    foreach ($users as $user) {
        $userData = explode('|', $user);
        if (isset($userData[0]) && $userData[0] == $userId) {
            return $userData;
        }
    }
    return null;
}

/**
 * 通过邮箱获取用户
 */
function getUserByEmail($email) {
    $users = getUsers();
    foreach ($users as $user) {
        $userData = explode('|', $user);
        if (isset($userData[2]) && $userData[2] == $email) {
            return $userData;
        }
    }
    return null;
}

/**
 * 获取所有房间数据
 */
function getRooms() {
    $content = file_get_contents(ROOMS_FILE);
    $rooms = $content ? explode("\n", $content) : [];
    $roomsData = [];
    
    foreach ($rooms as $room) {
        if (empty($room)) continue;
        $roomData = explode('|', $room);
        if (count($roomData) >= 8) {
            $roomsData[] = [
                'id' => $roomData[0],
                'type' => $roomData[1],
                'price' => $roomData[2],
                'breakfast' => $roomData[3],
                'capacity' => $roomData[4],
                'description' => $roomData[5],
                'image' => $roomData[6],
                'quantity' => $roomData[7]
            ];
        }
    }
    
    return $roomsData;
}

/**
 * 获取房间详情
 */
function getRoomById($roomId) {
    $rooms = getRooms();
    foreach ($rooms as $room) {
        if ($room['id'] == $roomId) {
            return $room;
        }
    }
    return null;
}

/**
 * 初始化房间数据（仅在第一次使用时调用）
 */
function initRooms() {
    if (filesize(ROOMS_FILE) > 0) return;
    
    $rooms = [
        ['1', 'Deluxe Room', '199.99', 'Yes', '2', 'Spacious room with king-size bed, city view and premium bedding.', 'deluxe.jpg', '5'],
        ['2', 'Executive Suite', '299.99', 'Yes', '2', 'Luxurious suite with separate living area and premium amenities.', 'executive.jpg', '3'],
        ['3', 'Family Room', '249.99', 'Yes', '4', 'Perfect for families with two queen beds and extra space.', 'family.jpg', '4'],
        ['4', 'Standard Room', '149.99', 'No', '2', 'Comfortable room with all essential amenities.', 'standard.jpg', '8']
    ];
    
    foreach ($rooms as $room) {
        file_put_contents(ROOMS_FILE, implode('|', $room) . "\n", FILE_APPEND);
    }
}

/**
 * 添加新预订
 */
function addBooking($bookingData) {
    $bookingString = implode('|', $bookingData);
    file_put_contents(BOOKINGS_FILE, $bookingString . "\n", FILE_APPEND);
    return true;
}

/**
 * 获取所有预订
 */
function getBookings() {
    $content = file_get_contents(BOOKINGS_FILE);
    $bookings = $content ? explode("\n", $content) : [];
    $bookingsData = [];
    
    foreach ($bookings as $booking) {
        if (empty($booking)) continue;
        $bookingData = explode('|', $booking);
        if (count($bookingData) >= 8) {
            $bookingsData[] = [
                'id' => $bookingData[0],
                'user_id' => $bookingData[1],
                'room_id' => $bookingData[2],
                'check_in' => $bookingData[3],
                'check_out' => $bookingData[4],
                'guests' => $bookingData[5],
                'total_price' => $bookingData[6],
                'status' => $bookingData[7],
                'created_at' => isset($bookingData[8]) ? $bookingData[8] : date('Y-m-d H:i:s')
            ];
        }
    }
    
    return $bookingsData;
}

/**
 * 获取用户预订
 */
function getUserBookings($userId) {
    $allBookings = getBookings();
    $userBookings = [];
    
    foreach ($allBookings as $booking) {
        if ($booking['user_id'] == $userId) {
            $userBookings[] = $booking;
        }
    }
    
    return $userBookings;
}

/**
 * 获取预订详情
 */
function getBookingById($bookingId) {
    $bookings = getBookings();
    foreach ($bookings as $booking) {
        if ($booking['id'] == $bookingId) {
            return $booking;
        }
    }
    return null;
}

/**
 * 更新预订状态
 */
function updateBookingStatus($bookingId, $status) {
    $bookings = getBookings();
    $updatedBookings = [];
    
    foreach ($bookings as $booking) {
        if ($booking['id'] == $bookingId) {
            $booking['status'] = $status;
        }
        $updatedBookings[] = $booking['id'] . '|' . $booking['user_id'] . '|' . $booking['room_id'] . '|' . 
                             $booking['check_in'] . '|' . $booking['check_out'] . '|' . $booking['guests'] . '|' . 
                             $booking['total_price'] . '|' . $booking['status'] . '|' . $booking['created_at'];
    }
    
    file_put_contents(BOOKINGS_FILE, implode("\n", array_filter($updatedBookings)));
    return true;
}

/**
 * 检查房间在指定日期是否可用
 */
function isRoomAvailable($roomId, $checkIn, $checkOut) {
    $room = getRoomById($roomId);
    if (!$room) return false;
    
    $bookings = getBookings();
    $bookedCount = 0;
    
    foreach ($bookings as $booking) {
        if ($booking['room_id'] == $roomId && $booking['status'] != 'cancelled') {
            // 检查日期是否重叠
            if (($checkIn <= $booking['check_out'] && $checkOut >= $booking['check_in'])) {
                $bookedCount++;
            }
        }
    }
    
    return $bookedCount < $room['quantity'];
}

/**
 * 生成唯一ID
 */
function generateId() {
    return uniqid();
}

// 初始化房间数据
initRooms();