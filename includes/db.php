<?php
// 定义数据文件路径
define('USERS_FILE', __DIR__ . '/../data/users.txt');
define('ROOMS_FILE', __DIR__ . '/../data/rooms.txt');
define('BOOKINGS_FILE', __DIR__ . '/../data/bookings.txt');

// 定义房型常量
define('ROOM_TYPES', [
    'Standard Room' => 'Standard Room',
    'Deluxe Room' => 'Deluxe Room',
    'Executive Suite' => 'Executive Suite',
    'Family Room' => 'Family Room'
]);

// 确保数据目录存在
if (!file_exists(__DIR__ . '/../data')) {
    if (!mkdir(__DIR__ . '/../data', 0777, true)) {
        error_log('无法创建数据目录');
        return false;
    }
}

// 确保数据文件存在
if (!file_exists(USERS_FILE)) {
    if (file_put_contents(USERS_FILE, '') === false) {
        error_log('无法创建用户数据文件');
        return false;
    }
}
if (!file_exists(ROOMS_FILE)) {
    if (file_put_contents(ROOMS_FILE, '') === false) {
        error_log('无法创建房间数据文件');
        return false;
    }
}
if (!file_exists(BOOKINGS_FILE)) {
    if (file_put_contents(BOOKINGS_FILE, '') === false) {
        error_log('无法创建预订数据文件');
        return false;
    }
}

/**
 * 获取所有用户数据
 */
function getUsers() {
    if (!file_exists(USERS_FILE) || !is_readable(USERS_FILE)) {
        error_log('用户数据文件不存在或不可读');
        return [];
    }
    
    $content = file_get_contents(USERS_FILE);
    if ($content === false) {
        error_log('读取用户数据文件失败');
        return [];
    }
    
    $users = $content ? explode("\n", $content) : [];
    return array_filter($users); // 移除空行
}

/**
 * 添加新用户
 */
function addUser($userData) {
    if (!is_writable(USERS_FILE)) {
        error_log('用户数据文件不可写');
        return false;
    }
    
    $userString = implode('|', $userData);
    $result = file_put_contents(USERS_FILE, $userString . "\n", FILE_APPEND);
    
    if ($result === false) {
        error_log('写入用户数据失败');
        return false;
    }
    
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
        if (count($roomData) >= 9) {
            $roomsData[] = [
                'id' => $roomData[0],
                'type' => $roomData[1],
                'price' => $roomData[2],
                'breakfast' => $roomData[3],
                'capacity' => $roomData[4],
                'description' => $roomData[5],
                'image' => $roomData[6],
                'quantity' => $roomData[7],
                'status' => $roomData[8]
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
        ['1', 'Deluxe Room', '199.99', 'Yes', '2', 'Spacious room with king-size bed, city view and premium bedding.', 'deluxe.jpg', '5', 'available'],
        ['2', 'Executive Suite', '299.99', 'Yes', '2', 'Luxurious suite with separate living area and premium amenities.', 'executive.jpg', '3', 'available'],
        ['3', 'Family Room', '249.99', 'Yes', '4', 'Perfect for families with two queen beds and extra space.', 'family.jpg', '4', 'available'],
        ['4', 'Standard Room', '149.99', 'No', '2', 'Comfortable room with all essential amenities.', 'standard.jpg', '8', 'available']
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
    $processedIds = []; // 添加ID跟踪数组
    
    foreach ($bookings as $booking) {
        if (empty($booking)) continue;
        $bookingData = explode('|', $booking);
        if (count($bookingData) >= 8) {
            // 确保ID被视为字符串处理
            $bookingId = strval($bookingData[0]);
            
            // 使用严格的字符串比较
            $idExists = false;
            foreach ($processedIds as $existingId) {
                if (strcmp($existingId, $bookingId) === 0) {
                    $idExists = true;
                    break;
                }
            }
            
            if ($idExists) {
                continue; // 跳过已经处理过的ID
            }
            
            // 记录此ID已处理
            $processedIds[] = $bookingId;
            
            $bookingInfo = [
                'id' => $bookingId,
                'user_id' => $bookingData[1],
                'room_id' => $bookingData[2],
                'check_in' => $bookingData[3],
                'check_out' => $bookingData[4],
                'guests' => $bookingData[5],
                'total_price' => $bookingData[6],
                'status' => $bookingData[7],
                'created_at' => isset($bookingData[8]) ? $bookingData[8] : date('Y-m-d H:i:s')
            ];
            
            // 添加手机号码字段支持
            if (isset($bookingData[9])) {
                $bookingInfo['mobile_phone'] = $bookingData[9];
            }
            
            $bookingsData[] = $bookingInfo;
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
        
        $bookingStr = $booking['id'] . '|' . $booking['user_id'] . '|' . $booking['room_id'] . '|' . 
                     $booking['check_in'] . '|' . $booking['check_out'] . '|' . $booking['guests'] . '|' . 
                     $booking['total_price'] . '|' . $booking['status'] . '|' . $booking['created_at'];
        
        // 添加手机号码字段
        if (isset($booking['mobile_phone'])) {
            $bookingStr .= '|' . $booking['mobile_phone'];
        }
        
        $updatedBookings[] = $bookingStr;
    }
    
    if (!is_writable(BOOKINGS_FILE)) {
        error_log('预订数据文件不可写');
        return false;
    }
    
    $result = file_put_contents(BOOKINGS_FILE, implode("\n", array_filter($updatedBookings)));
    
    if ($result === false) {
        error_log('更新预订状态失败');
        return false;
    }
    
    return true;
}

/**
 * 检查房间在指定日期是否可用
 */
function isRoomAvailable($roomId, $checkIn, $checkOut) {
    $room = getRoomById($roomId);
    if (!$room) return false;
    
    $bookings = getBookings();
    $totalRooms = $room['quantity'];
    
    // 将入住和退房日期转为日期对象
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    
    // 生成预订期间的所有日期
    $dateRange = new DatePeriod(
        $checkInDate,
        new DateInterval('P1D'),
        $checkOutDate
    );
    
    // 检查每一天的房间可用情况
    foreach ($dateRange as $date) {
        $currentDate = $date->format('Y-m-d');
        $bookedCount = 0;
        
        foreach ($bookings as $booking) {
            if ($booking['room_id'] == $roomId && $booking['status'] != 'cancelled') {
                $bookingCheckIn = new DateTime($booking['check_in']);
                $bookingCheckOut = new DateTime($booking['check_out']);
                
                // 如果当前日期在预订范围内，增加已预订计数
                if ($date >= $bookingCheckIn && $date < $bookingCheckOut) {
                    $bookedCount++;
                }
            }
        }
        
        // 如果任何一天房间数量不足，则返回不可用
        if ($bookedCount >= $totalRooms) {
            return false;
        }
    }
    
    return true;
}

/**
 * 生成唯一ID
 */
function generateId() {
    return uniqid();
}

/**
 * 清理预订数据文件中的重复记录
 */
function cleanupDuplicateBookings() {
    // 读取原始数据并逐行解析
    $content = file_get_contents(BOOKINGS_FILE);
    $bookingLines = $content ? explode("\n", $content) : [];
    $uniqueBookingLines = [];
    $processedIds = [];
    $hasChanges = false;
    
    foreach ($bookingLines as $bookingLine) {
        if (empty($bookingLine)) continue;
        
        $bookingData = explode('|', $bookingLine);
        if (count($bookingData) < 8) continue;
        
        // 确保ID为字符串
        $bookingId = strval($bookingData[0]);
        
        // 使用严格的字符串比较
        $idExists = false;
        foreach ($processedIds as $existingId) {
            if (strcmp($existingId, $bookingId) === 0) {
                $idExists = true;
                $hasChanges = true; // 发现重复项
                break;
            }
        }
        
        // 只有未处理过的ID才添加到结果中
        if (!$idExists) {
            $processedIds[] = $bookingId;
            $uniqueBookingLines[] = $bookingLine;
        }
    }
    
    // 如果没有变化，就不需要写入文件
    if (!$hasChanges) {
        return true;
    }
    
    // 确认文件可写
    if (!is_writable(BOOKINGS_FILE)) {
        error_log('预订数据文件不可写');
        return false;
    }
    
    // 写入唯一记录
    $result = file_put_contents(BOOKINGS_FILE, implode("\n", array_filter($uniqueBookingLines)));
    
    if ($result === false) {
        error_log('清理重复预订失败');
        return false;
    }
    
    return true;
}

/**
 * 添加新房间
 */
function addRoom($roomData) {
    if (!is_writable(ROOMS_FILE)) {
        error_log('房间数据文件不可写');
        return false;
    }
    
    $roomString = implode('|', [
        $roomData['id'],
        $roomData['type'],
        $roomData['price'],
        $roomData['breakfast'],
        $roomData['capacity'],
        $roomData['description'],
        $roomData['image'],
        $roomData['quantity']
    ]);
    
    $result = file_put_contents(ROOMS_FILE, $roomString . "\n", FILE_APPEND);
    
    if ($result === false) {
        error_log('添加房间数据失败');
        return false;
    }
    
    return true;
}

/**
 * 更新房间信息
 */
function updateRoom($roomId, $roomData) {
    $rooms = getRooms();
    $updatedRooms = [];
    $found = false;
    
    foreach ($rooms as $room) {
        if ($room['id'] == $roomId) {
            $updatedRooms[] = implode('|', [
                $roomId,
                $roomData['type'],
                $roomData['price'],
                $roomData['breakfast'],
                $roomData['capacity'],
                $roomData['description'],
                $roomData['image'],
                $roomData['quantity'],
                $roomData['status']
            ]);
            $found = true;
        } else {
            $updatedRooms[] = implode('|', [
                $room['id'],
                $room['type'],
                $room['price'],
                $room['breakfast'],
                $room['capacity'],
                $room['description'],
                $room['image'],
                $room['quantity'],
                $room['status']
            ]);
        }
    }
    
    // 如果是新增房间
    if (!$found) {
        $updatedRooms[] = implode('|', [
            $roomId,
            $roomData['type'],
            $roomData['price'],
            $roomData['breakfast'],
            $roomData['capacity'],
            $roomData['description'],
            $roomData['image'],
            $roomData['quantity'],
            $roomData['status']
        ]);
    }
    
    if (!is_writable(ROOMS_FILE)) {
        error_log('Room data file is not writable');
        return false;
    }
    
    $result = file_put_contents(ROOMS_FILE, implode("\n", $updatedRooms));
    
    if ($result === false) {
        error_log('Failed to update room data');
        return false;
    }
    
    return true;
}

/**
 * 删除房间
 */
function deleteRoom($roomId) {
    $rooms = getRooms();
    $updatedRooms = [];
    
    foreach ($rooms as $room) {
        if ($room['id'] != $roomId) {
            $updatedRooms[] = implode('|', [
                $room['id'],
                $room['type'],
                $room['price'],
                $room['breakfast'],
                $room['capacity'],
                $room['description'],
                $room['image'],
                $room['quantity'],
                $room['status']
            ]);
        }
    }
    
    if (!is_writable(ROOMS_FILE)) {
        error_log('房间数据文件不可写');
        return false;
    }
    
    $result = file_put_contents(ROOMS_FILE, implode("\n", $updatedRooms));
    return $result !== false;
}

// 初始化房间数据
initRooms();