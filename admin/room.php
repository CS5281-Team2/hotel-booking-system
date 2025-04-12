<?php
$pageTitle = 'Manage Rooms';
$adminPage = true;
include '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// 验证管理员权限
if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// 定义函数来添加/更新房间
function saveRoom($roomData) {
    global $rooms;
    
    $roomId = $roomData['id'];
    $isNewRoom = true;
    $updatedRooms = [];
    
    // 检查是否已存在相同ID的房间
    foreach ($rooms as $room) {
        if ($room['id'] == $roomId) {
            $isNewRoom = false;
            $updatedRooms[] = implode('|', [
                $roomId,
                $roomData['type'],
                $roomData['price'],
                $roomData['breakfast'],
                $roomData['capacity'],
                $roomData['description'],
                $roomData['image'],
                $roomData['quantity']
            ]);
        } else {
            $updatedRooms[] = implode('|', [
                $room['id'],
                $room['type'],
                $room['price'],
                $room['breakfast'],
                $room['capacity'],
                $room['description'],
                $room['image'],
                $room['quantity']
            ]);
        }
    }
    
    // 如果是新房间，添加到列表中
    if ($isNewRoom) {
        $updatedRooms[] = implode('|', [
            $roomId,
            $roomData['type'],
            $roomData['price'],
            $roomData['breakfast'],
            $roomData['capacity'],
            $roomData['description'],
            $roomData['image'],
            $roomData['quantity']
        ]);
    }
    
    // 写入文件
    if (!is_writable(ROOMS_FILE)) {
        return false;
    }
    
    $result = file_put_contents(ROOMS_FILE, implode("\n", $updatedRooms));
    return $result !== false;
}

// 处理表单提交
$successMessage = '';
$errorMessage = '';
$editRoom = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_room'])) {
        // 处理编辑请求
        $roomId = $_POST['room_id'];
        foreach ($rooms as $room) {
            if ($room['id'] == $roomId) {
                $editRoom = $room;
                break;
            }
        }
    } elseif (isset($_POST['save_room'])) {
        // 保存房间信息
        $roomId = isset($_POST['room_id']) ? $_POST['room_id'] : generateId();
        $roomType = isset($_POST['room_type']) ? trim($_POST['room_type']) : '';
        $roomPrice = isset($_POST['room_price']) ? floatval($_POST['room_price']) : 0;
        $roomBreakfast = isset($_POST['room_breakfast']) ? 'Yes' : 'No';
        $roomCapacity = isset($_POST['room_capacity']) ? intval($_POST['room_capacity']) : 1;
        $roomDescription = isset($_POST['room_description']) ? trim($_POST['room_description']) : '';
        $roomImage = isset($_POST['room_image']) ? trim($_POST['room_image']) : 'standard.jpg';
        $roomQuantity = isset($_POST['room_quantity']) ? intval($_POST['room_quantity']) : 1;
        
        // 验证数据
        $validData = true;
        if (empty($roomType)) {
            $validData = false;
            $errorMessage = 'Please enter room type';
        } elseif ($roomPrice <= 0) {
            $validData = false;
            $errorMessage = 'Price must be greater than 0';
        } elseif ($roomCapacity <= 0) {
            $validData = false;
            $errorMessage = 'Capacity must be greater than 0';
        } elseif ($roomQuantity <= 0) {
            $validData = false;
            $errorMessage = 'Room quantity must be greater than 0';
        }
        
        if ($validData) {
            $roomData = [
                'id' => $roomId,
                'type' => $roomType,
                'price' => number_format($roomPrice, 2, '.', ''),
                'breakfast' => $roomBreakfast,
                'capacity' => $roomCapacity,
                'description' => $roomDescription,
                'image' => $roomImage,
                'quantity' => $roomQuantity
            ];
            
            if (saveRoom($roomData)) {
                $successMessage = 'Room information saved successfully';
                // 重新加载房间列表
                $rooms = getRooms();
                $editRoom = null;
            } else {
                $errorMessage = 'Failed to save room information';
            }
        }
    } elseif (isset($_POST['cancel_edit'])) {
        // 取消编辑
        $editRoom = null;
    }
}

// 获取所有房间
$rooms = getRooms();
?>

<section class="admin-rooms py-50">
    <div class="container">
        <h1 class="mb-20">Manage Rooms</h1>
        
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
        
        <?php if ($editRoom): ?>
        <!-- 编辑房间表单 -->
        <div style="margin-bottom: 30px; background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
            <h2><?php echo $editRoom ? 'Edit Room' : 'Add New Room'; ?></h2>
            <form action="room.php" method="POST">
                <input type="hidden" name="room_id" value="<?php echo $editRoom ? $editRoom['id'] : ''; ?>">
                
                <div class="form-group">
                    <label for="room_type">Room Type</label>
                    <input type="text" id="room_type" name="room_type" class="form-control" value="<?php echo $editRoom ? $editRoom['type'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="room_price">Price (per night)</label>
                    <input type="number" id="room_price" name="room_price" class="form-control" value="<?php echo $editRoom ? $editRoom['price'] : ''; ?>" min="0.01" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="room_breakfast">Includes Breakfast</label>
                    <input type="checkbox" id="room_breakfast" name="room_breakfast" <?php echo ($editRoom && $editRoom['breakfast'] == 'Yes') ? 'checked' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label for="room_capacity">Capacity</label>
                    <input type="number" id="room_capacity" name="room_capacity" class="form-control" value="<?php echo $editRoom ? $editRoom['capacity'] : ''; ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="room_description">Description</label>
                    <textarea id="room_description" name="room_description" class="form-control" rows="3" required><?php echo $editRoom ? $editRoom['description'] : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="room_image">Image Filename</label>
                    <input type="text" id="room_image" name="room_image" class="form-control" value="<?php echo $editRoom ? $editRoom['image'] : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="room_quantity">Room Quantity</label>
                    <input type="number" id="room_quantity" name="room_quantity" class="form-control" value="<?php echo $editRoom ? $editRoom['quantity'] : ''; ?>" min="1" required>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="save_room" class="btn btn-primary">Save</button>
                    <button type="submit" name="cancel_edit" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- 添加新房间按钮 -->
        <div style="margin-bottom: 20px; text-align: right;">
            <form action="room.php" method="POST">
                <input type="hidden" name="room_id" value="">
                <button type="submit" name="edit_room" class="btn btn-primary">Add New Room</button>
            </form>
        </div>
        <?php endif; ?>
        
        <div class="table-responsive" style="margin-top: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: var(--primary-color); color: white;">
                        <th style="padding: 12px 15px; text-align: left;">ID</th>
                        <th style="padding: 12px 15px; text-align: left;">Room Type</th>
                        <th style="padding: 12px 15px; text-align: left;">Capacity</th>
                        <th style="padding: 12px 15px; text-align: left;">Breakfast</th>
                        <th style="padding: 12px 15px; text-align: right;">Price (per night)</th>
                        <th style="padding: 12px 15px; text-align: center;">Quantity</th>
                        <th style="padding: 12px 15px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center;">No rooms found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px 15px;"><?php echo $room['id']; ?></td>
                                <td style="padding: 12px 15px;"><?php echo $room['type']; ?></td>
                                <td style="padding: 12px 15px;"><?php echo $room['capacity']; ?> Person<?php echo $room['capacity'] > 1 ? 's' : ''; ?></td>
                                <td style="padding: 12px 15px;"><?php echo $room['breakfast']; ?></td>
                                <td style="padding: 12px 15px; text-align: right;">$<?php echo $room['price']; ?></td>
                                <td style="padding: 12px 15px; text-align: center;"><?php echo $room['quantity']; ?></td>
                                <td style="padding: 12px 15px; text-align: center;">
                                    <form action="room.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                        <button type="submit" name="edit_room" class="btn btn-sm btn-primary">Edit</button>
                                    </form>
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

<?php include '../includes/footer.php'; ?>