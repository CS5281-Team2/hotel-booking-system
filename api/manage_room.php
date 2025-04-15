<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// 使用输出缓冲，确保在最终输出前可以清除任何意外输出
ob_start();

// 设置内容类型头部
header('Content-Type: application/json');

// 验证管理员权限
if (!isAdmin()) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// 获取操作类型
$action = isset($_POST['action']) ? $_POST['action'] : '';

// 根据操作类型处理请求
switch ($action) {
    case 'add':
    case 'edit':
        // 获取房间数据
        $roomId = isset($_POST['room_id']) ? $_POST['room_id'] : '';
        $roomType = isset($_POST['room_type']) ? trim($_POST['room_type']) : '';
        $roomPrice = isset($_POST['room_price']) ? floatval($_POST['room_price']) : 0;
        $roomBreakfast = isset($_POST['room_breakfast']) ? 'Yes' : 'No';
        $roomCapacity = isset($_POST['room_capacity']) ? intval($_POST['room_capacity']) : 1;
        $roomDescription = isset($_POST['room_description']) ? trim($_POST['room_description']) : '';
        $roomImage = isset($_POST['room_image']) ? trim($_POST['room_image']) : 'standard.jpg';
        $roomQuantity = isset($_POST['room_quantity']) ? intval($_POST['room_quantity']) : 1;
        $roomStatus = isset($_POST['room_status']) ? trim($_POST['room_status']) : 'available';
        
        // 验证数据
        if (empty($roomType) || $roomPrice <= 0 || $roomCapacity <= 0 || $roomQuantity <= 0) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields with valid values']);
            exit;
        }
        
        // 如果是新增房间且没有指定ID，生成一个ID
        if ($action === 'add' && empty($roomId)) {
            $roomId = generateId();
        }
        
        // 创建房间数据
        $roomData = [
            'id' => $roomId,
            'type' => $roomType,
            'price' => $roomPrice,
            'breakfast' => $roomBreakfast,
            'capacity' => $roomCapacity,
            'description' => $roomDescription,
            'image' => $roomImage,
            'quantity' => $roomQuantity,
            'status' => $roomStatus
        ];
        
        // 更新或添加房间
        $result = updateRoom($roomId, $roomData);
        
        if ($result) {
            // 获取所有房间信息，返回更新后的列表
            $rooms = getRooms();
            
            ob_clean();
            echo json_encode([
                'success' => true, 
                'message' => ($action === 'add' ? 'Room added' : 'Room updated') . ' successfully',
                'room' => $roomData,
                'rooms' => $rooms
            ]);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Failed to ' . ($action === 'add' ? 'add' : 'update') . ' room']);
        }
        break;
        
    case 'delete':
        // 获取房间ID
        $roomId = isset($_POST['room_id']) ? $_POST['room_id'] : '';
        
        if (empty($roomId)) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Room ID is required']);
            exit;
        }
        
        // 删除房间
        $result = deleteRoom($roomId);
        
        if ($result) {
            // 获取所有房间信息，返回更新后的列表
            $rooms = getRooms();
            
            ob_clean();
            echo json_encode([
                'success' => true, 
                'message' => 'Room deleted successfully',
                'rooms' => $rooms
            ]);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Failed to delete room']);
        }
        break;
        
    case 'get':
        // 获取房间ID
        $roomId = isset($_POST['room_id']) ? $_POST['room_id'] : '';
        
        if (empty($roomId)) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Room ID is required']);
            exit;
        }
        
        // 获取房间信息
        $room = getRoomById($roomId);
        
        if ($room) {
            ob_clean();
            echo json_encode([
                'success' => true, 
                'room' => $room
            ]);
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Room not found']);
        }
        break;
        
    default:
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?> 