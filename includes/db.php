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
    $usersData = [];
    $expectedFieldCount = 7; // 用户文件应有 7 个字段

    if (!file_exists(USERS_FILE) || !is_readable(USERS_FILE)) {
        error_log('User data file does not exist or is not readable.');
        return [];
    }

    $lines = file(USERS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $userData = explode('|', $line);
        if (count($userData) === $expectedFieldCount) {
            $usersData[] = [
                'id' => $userData[0],
                'name' => $userData[1],
                'email' => $userData[2],
                'phone' => $userData[3],
                'password_hash' => $userData[4],
                'role' => $userData[5],
                'created_at' => $userData[6]
            ];
        } else {
             error_log("Skipping malformed user line (expected {$expectedFieldCount} fields, found " . count($userData) . "): " . $line);
        }
    }
    return $usersData;
}

/**
 * 添加新用户
 */
function addUser($userData) {
    // 强制按固定顺序排列字段
    $orderedData = [
        isset($userData['id']) ? $userData['id'] : '',
        isset($userData['name']) ? $userData['name'] : '',
        isset($userData['email']) ? $userData['email'] : '',
        isset($userData['phone']) ? $userData['phone'] : '',
        isset($userData['password_hash']) ? $userData['password_hash'] : '',
        isset($userData['role']) ? $userData['role'] : 'user', // 默认角色
        isset($userData['created_at']) ? $userData['created_at'] : date('Y-m-d H:i:s') // 默认创建时间
    ];

    // 检查字段数量是否正确
    if (count($orderedData) !== 7) {
         error_log("Incorrect number of fields provided to addUser.");
         return false;
    }

    $userString = implode('|', $orderedData);

    // 使用文件锁确保写入安全
    $fp = fopen(USERS_FILE, 'a');
    if (!$fp) {
        error_log("Failed to open users file for appending.");
        return false;
    }
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, $userString . "\n");
        fflush($fp);
        flock($fp, LOCK_UN);
    } else {
        fclose($fp);
        error_log("Could not lock users file for writing.");
        return false;
    }
    fclose($fp);

    return true;
}

/**
 * 通过ID获取用户
 */
function getUserById($userId) {
    $users = getUsers(); // 现在返回结构化数组
    foreach ($users as $user) {
        // 直接比较结构化数据中的 id 字段
        if ($user['id'] == $userId) {
            // 注意：这里返回的是关联数组，不是原始的 explode 数组
            // 如果其他地方期望的是索引数组，需要调整
            return $user; 
        }
    }
    return null;
}

/**
 * 通过邮箱获取用户
 */
function getUserByEmail($email) {
    $users = getUsers(); // 现在返回结构化数组
    foreach ($users as $user) {
         // 直接比较结构化数据中的 email 字段
        if ($user['email'] == $email) {
             // 注意：这里返回的是关联数组
            return $user;
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
        // 确保字段顺序: id|type|price|breakfast|capacity|description|image|quantity|status
        ['1', 'Deluxe Room', '199.99', 'Yes', '2', 'Spacious room with king-size bed, city view and premium bedding.', 'deluxe.jpg', '5', 'available'],
        ['2', 'Executive Suite', '299.99', 'Yes', '2', 'Luxurious suite with separate living area and premium amenities.', 'executive.jpg', '3', 'available'],
        ['3', 'Family Room', '249.99', 'Yes', '4', 'Perfect for families with two queen beds and extra space.', 'family.jpg', '4', 'available'],
        ['4', 'Standard Room', '149.99', 'No', '2', 'Comfortable room with all essential amenities.', 'standard.jpg', '8', 'available']
    ];

    $fp = fopen(ROOMS_FILE, 'a');
    if (!$fp) {
        error_log("Failed to open rooms file for initialization.");
        return;
    }
    if (flock($fp, LOCK_EX)) {
        foreach ($rooms as $room) {
            if (count($room) === 9) { // 校验字段数
               fwrite($fp, implode('|', $room) . "\n");
            }
        }
        fflush($fp);
        flock($fp, LOCK_UN);
    } else {
         error_log("Could not lock rooms file for initialization.");
    }
    fclose($fp);
}

/**
 * 添加新预订
 */
function addBooking($bookingData) {
    // 强制按固定顺序排列字段
    $orderedData = [
        isset($bookingData['id']) ? $bookingData['id'] : '',
        isset($bookingData['user_id']) ? $bookingData['user_id'] : '',
        isset($bookingData['room_id']) ? $bookingData['room_id'] : '',
        isset($bookingData['check_in']) ? $bookingData['check_in'] : '',
        isset($bookingData['check_out']) ? $bookingData['check_out'] : '',
        isset($bookingData['guests']) ? $bookingData['guests'] : '',
        isset($bookingData['total_price']) ? $bookingData['total_price'] : '',
        isset($bookingData['status']) ? $bookingData['status'] : 'confirmed', // 确保有默认值
        isset($bookingData['created_at']) ? $bookingData['created_at'] : date('Y-m-d H:i:s'), // 确保有默认值
        isset($bookingData['mobile_phone']) ? $bookingData['mobile_phone'] : '', // 手机号
        isset($bookingData['special_requests']) ? $bookingData['special_requests'] : '' // 特殊要求
    ];
    
    $bookingString = implode('|', $orderedData);
    
    // 使用文件锁确保写入安全
    $fp = fopen(BOOKINGS_FILE, 'a'); // 以追加模式打开
    if (flock($fp, LOCK_EX)) { // 获取独占锁
        fwrite($fp, $bookingString . "\n");
        fflush($fp);            // 清空文件指针缓存
        flock($fp, LOCK_UN);    // 释放锁
    } else {
        fclose($fp);
        error_log("Could not lock bookings file for writing.");
        return false; // 无法获取锁
    }
    fclose($fp);
    
    return true;
}

/**
 * 获取所有预订
 */
function getBookings() {
    $bookingsData = [];
    $processedIds = []; 
    $expectedFieldCount = 11; // 更新期望的字段数量

    if (!file_exists(BOOKINGS_FILE) || !is_readable(BOOKINGS_FILE)) {
        error_log("Bookings file does not exist or is not readable.");
        return [];
    }

    $lines = file(BOOKINGS_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $bookingData = explode('|', $line);
        
        // 严格检查字段数量
        if (count($bookingData) === $expectedFieldCount) {
            $bookingId = strval($bookingData[0]);

            // 检查重复ID (保持原有逻辑)
            $idExists = false;
            foreach ($processedIds as $existingId) {
                if (strcmp($existingId, $bookingId) === 0) {
                    $idExists = true;
                    break;
                }
            }
            if ($idExists) {
                continue; 
            }
            $processedIds[] = $bookingId;
            
            // 按统一顺序解析字段
            $bookingInfo = [
                'id' => $bookingId,
                'user_id' => $bookingData[1],
                'room_id' => $bookingData[2],
                'check_in' => $bookingData[3],
                'check_out' => $bookingData[4],
                'guests' => $bookingData[5],
                'total_price' => $bookingData[6],
                'status' => $bookingData[7], // status 在索引 7
                'created_at' => $bookingData[8], // created_at 在索引 8
                'mobile_phone' => $bookingData[9], // mobile_phone 在索引 9
                'special_requests' => $bookingData[10] // special_requests 在索引 10
            ];
            
            // 添加关联数据 (优化：减少重复调用)
            // $bookingInfo['user'] = getUserById($bookingInfo['user_id']); 
            // $bookingInfo['room'] = getRoomById($bookingInfo['room_id']);

            $bookingsData[] = $bookingInfo;
        } else {
             error_log("Skipping malformed booking line (expected {$expectedFieldCount} fields, found " . count($bookingData) . "): " . $line);
        }
    }
    
    // 优化：在循环外批量获取用户信息和房间信息，减少文件读取次数
    $userIds = array_unique(array_column($bookingsData, 'user_id'));
    $roomIds = array_unique(array_column($bookingsData, 'room_id'));
    $usersInfo = [];
    $roomsInfo = [];

    // 批量获取用户信息 (假设有 getUserByMultipleIds 函数, 否则需要循环调用 getUserById)
    // 简化处理：仍然循环调用，但在 admin/booking.php 中完成关联可能更优
    foreach ($userIds as $uid) {
        $usersInfo[$uid] = getUserById($uid);
    }
     foreach ($roomIds as $rid) {
        $roomsInfo[$rid] = getRoomById($rid);
    }

    // 将用户信息和房间信息合并回 bookingsData
    foreach ($bookingsData as $key => $booking) {
        $bookingsData[$key]['user'] = isset($usersInfo[$booking['user_id']]) ? $usersInfo[$booking['user_id']] : null;
        $bookingsData[$key]['room'] = isset($roomsInfo[$booking['room_id']]) ? $roomsInfo[$booking['room_id']] : null;
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
    // 获取所有预订（关联数组格式）
    $bookings = getBookings();
    $updatedBookings = [];
    $updated = false;
    
    // 遍历并更新目标预订
    foreach ($bookings as $booking) {
        if ($booking['id'] == $bookingId) {
            // 更新状态字段
            $booking['status'] = $status;
            $updated = true;
        }
        
        // 按照固定顺序准备写入文件的数据
        $orderedData = [
            $booking['id'],
            $booking['user_id'],
            $booking['room_id'],
            $booking['check_in'],
            $booking['check_out'],
            $booking['guests'],
            $booking['total_price'],
            $booking['status'],
            $booking['created_at'],
            isset($booking['mobile_phone']) ? $booking['mobile_phone'] : '',
            isset($booking['special_requests']) ? $booking['special_requests'] : ''
        ];
        
        $updatedBookings[] = implode('|', $orderedData);
    }
    
    if (!$updated) {
        error_log("Booking ID {$bookingId} not found or already in desired status.");
        return false;
    }
    
    // 使用文件锁安全地写入更新后的数据
    $fp = fopen(BOOKINGS_FILE, 'w');
    if (!$fp) {
        error_log("Failed to open bookings file for status update.");
        return false;
    }
    
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, implode("\n", $updatedBookings) . "\n");
        fflush($fp);
        flock($fp, LOCK_UN);
    } else {
        fclose($fp);
        error_log("Could not lock bookings file for status update.");
        return false;
    }
    fclose($fp);
    
    return true;
}

/**
 * 检查房间在指定日期内是否可用（考虑房间数量）
 */
function isRoomAvailable($roomId, $checkIn, $checkOut) {
    $room = getRoomById($roomId);
    if (!$room || !isset($room['quantity']) || $room['quantity'] <= 0) {
        // 如果房间信息不存在或数量无效，视为不可用
        return false;
    }
    $roomQuantity = intval($room['quantity']);
    
    $allBookings = getBookings();
    $requestedCheckIn = new DateTime($checkIn);
    // 预订日期范围不包括退房当天，所以结束日期需要减一天进行比较
    $requestedCheckOut = (new DateTime($checkOut))->modify('-1 day'); 

    // 创建一个日期范围内的每日预订计数器
    $dailyBookedCount = [];
    $currentDate = clone $requestedCheckIn;
    while ($currentDate <= $requestedCheckOut) {
        $dailyBookedCount[$currentDate->format('Y-m-d')] = 0;
        $currentDate->modify('+1 day');
    }

    // 遍历所有已确认的预订
    foreach ($allBookings as $booking) {
        // 只关心相同房间ID且状态为confirmed的预订
        if ($booking['room_id'] == $roomId && $booking['status'] == 'confirmed') {
            $bookingCheckIn = new DateTime($booking['check_in']);
            // 同样，已有预订的结束日期也减一天
            $bookingCheckOut = (new DateTime($booking['check_out']))->modify('-1 day');

            // 检查这个预订是否与请求的日期范围重叠
            if ($requestedCheckOut >= $bookingCheckIn && $requestedCheckIn <= $bookingCheckOut) {
                // 如果重叠，增加重叠日期范围内的每日计数
                $overlapStart = max($requestedCheckIn, $bookingCheckIn);
                $overlapEnd = min($requestedCheckOut, $bookingCheckOut);
                
                $currentOverlapDate = clone $overlapStart;
                while ($currentOverlapDate <= $overlapEnd) {
                    $dateStr = $currentOverlapDate->format('Y-m-d');
                    if (isset($dailyBookedCount[$dateStr])) {
                        $dailyBookedCount[$dateStr]++;
                    }
                    $currentOverlapDate->modify('+1 day');
                }
            }
        }
    }

    // 检查每日预订数量是否超过房间总数
    foreach ($dailyBookedCount as $date => $count) {
        if ($count >= $roomQuantity) {
            error_log("Room ID {$roomId} is unavailable on {$date}. Booked: {$count}, Quantity: {$roomQuantity}");
            return false; // 某天预订已满
        }
    }

    return true; // 所有查询日期内都有空房
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
    // 获取所有预订（关联数组格式）
    $bookings = getBookings();
    $uniqueBookings = [];
    $processedIds = [];
    $hasChanges = false;
    
    foreach ($bookings as $booking) {
        // 确保ID为字符串
        $bookingId = strval($booking['id']);
        
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
            
            // 准备写入文件的数据（按固定顺序）
            $orderedData = [
                $booking['id'],
                $booking['user_id'],
                $booking['room_id'],
                $booking['check_in'],
                $booking['check_out'],
                $booking['guests'],
                $booking['total_price'],
                $booking['status'],
                $booking['created_at'],
                isset($booking['mobile_phone']) ? $booking['mobile_phone'] : '',
                isset($booking['special_requests']) ? $booking['special_requests'] : ''
            ];
            
            $uniqueBookings[] = implode('|', $orderedData);
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
    $result = file_put_contents(BOOKINGS_FILE, implode("\n", array_filter($uniqueBookings)) . "\n");
    
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
    $expectedFieldCount = 9;
    // 强制按固定顺序排列字段
    $orderedData = [
        isset($roomData['id']) ? $roomData['id'] : generateId(), // 自动生成ID?
        isset($roomData['type']) ? $roomData['type'] : 'Unknown',
        isset($roomData['price']) ? $roomData['price'] : '0.00',
        isset($roomData['breakfast']) ? $roomData['breakfast'] : 'No',
        isset($roomData['capacity']) ? $roomData['capacity'] : '1',
        isset($roomData['description']) ? $roomData['description'] : '',
        isset($roomData['image']) ? $roomData['image'] : 'default.jpg',
        isset($roomData['quantity']) ? $roomData['quantity'] : '1',
        isset($roomData['status']) ? $roomData['status'] : 'available'
    ];

    if (count($orderedData) !== $expectedFieldCount) {
        error_log("Incorrect number of fields provided to addRoom.");
        return false;
    }

    $roomString = implode('|', $orderedData);

    $fp = fopen(ROOMS_FILE, 'a');
    if (!$fp) {
        error_log("Failed to open rooms file for appending.");
        return false;
    }
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, $roomString . "\n");
        fflush($fp);
        flock($fp, LOCK_UN);
    } else {
        fclose($fp);
        error_log("Could not lock rooms file for writing.");
        return false;
    }
    fclose($fp);

    return true;
}

/**
 * 更新房间数据
 */
function updateRoom($roomId, $updatedData) {
    // 获取所有房间（关联数组格式）
    $rooms = getRooms();
    $newLines = [];
    $updated = false;

    foreach ($rooms as $room) {
        if ($room['id'] == $roomId) {
            // 应用更新，保持关联数组格式
            $updatedRoom = [
                'id' => $roomId, // ID 不变
                'type' => isset($updatedData['type']) ? $updatedData['type'] : $room['type'],
                'price' => isset($updatedData['price']) ? $updatedData['price'] : $room['price'],
                'breakfast' => isset($updatedData['breakfast']) ? $updatedData['breakfast'] : $room['breakfast'],
                'capacity' => isset($updatedData['capacity']) ? $updatedData['capacity'] : $room['capacity'],
                'description' => isset($updatedData['description']) ? $updatedData['description'] : $room['description'],
                'image' => isset($updatedData['image']) ? $updatedData['image'] : $room['image'],
                'quantity' => isset($updatedData['quantity']) ? $updatedData['quantity'] : $room['quantity'],
                'status' => isset($updatedData['status']) ? $updatedData['status'] : $room['status']
            ];
            
            // 准备写入文件的数据（按固定顺序）
            $orderedData = [
                $updatedRoom['id'],
                $updatedRoom['type'],
                $updatedRoom['price'],
                $updatedRoom['breakfast'],
                $updatedRoom['capacity'],
                $updatedRoom['description'],
                $updatedRoom['image'],
                $updatedRoom['quantity'],
                $updatedRoom['status']
            ];
            
            $newLines[] = implode('|', $orderedData);
            $updated = true;
        } else {
            // 未修改的房间，仍然需要准备写入的数据
            $orderedData = [
                $room['id'],
                $room['type'],
                $room['price'],
                $room['breakfast'],
                $room['capacity'],
                $room['description'],
                $room['image'],
                $room['quantity'],
                $room['status']
            ];
            
            $newLines[] = implode('|', $orderedData);
        }
    }

    if ($updated) {
        $fp = fopen(ROOMS_FILE, 'w');
        if (!$fp) {
            error_log("Failed to open rooms file for writing.");
            return false;
        }
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, implode("\n", $newLines) . "\n");
            fflush($fp);
            flock($fp, LOCK_UN);
        } else {
            fclose($fp);
            error_log("Could not lock rooms file for writing.");
            return false;
        }
        fclose($fp);
        return true;
    }
    return false; // 未找到或未更新
}

/**
 * 删除房间
 */
function deleteRoom($roomId) {
    // 获取所有房间（关联数组格式）
    $rooms = getRooms();
    $newLines = [];
    $deleted = false;

    foreach ($rooms as $room) {
        // 仅保留 ID 不匹配的房间
        if ($room['id'] == $roomId) {
            $deleted = true;
            continue; // 跳过要删除的房间
        }
        
        // 准备写入文件的数据（按固定顺序）
        $orderedData = [
            $room['id'],
            $room['type'],
            $room['price'],
            $room['breakfast'],
            $room['capacity'],
            $room['description'],
            $room['image'],
            $room['quantity'],
            $room['status']
        ];
        
        $newLines[] = implode('|', $orderedData);
    }

    if ($deleted) {
        $fp = fopen(ROOMS_FILE, 'w');
        if (!$fp) {
            error_log("Failed to open rooms file for writing.");
            return false;
        }
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, implode("\n", $newLines) . "\n");
            fflush($fp);
            flock($fp, LOCK_UN);
        } else {
            fclose($fp);
            error_log("Could not lock rooms file for writing.");
            return false;
        }
        fclose($fp);
        return true;
    }
    return false; // 未找到或未删除
}

// 初始化房间数据
initRooms();