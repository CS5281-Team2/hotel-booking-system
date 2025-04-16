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

// 获取所有房间
$rooms = getRooms();

// 处理表单提交
$successMessage = '';
$errorMessage = '';
$editRoom = null;

// 检查是否有成功消息
if (isset($_GET['success'])) {
    $successMessage = $_GET['success'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_room'])) {
        // 处理编辑请求
        $roomId = $_POST['room_id'];
        if (empty($roomId)) {
            // 如果是添加新房间，使用简短ID
            // 查找当前最大ID号码
            $maxId = 0;
            foreach ($rooms as $room) {
                // 如果ID是纯数字
                if (is_numeric($room['id']) && $room['id'] > $maxId) {
                    $maxId = $room['id'];
                }
            }
            $nextId = $maxId + 1;
            
            $editRoom = [
                'id' => $nextId,
                'type' => '',
                'price' => '',
                'breakfast' => 'No',
                'capacity' => '',
                'description' => '',
                'image' => 'standard.jpg',
                'quantity' => '',
                'status' => 'available'
            ];
        } else {
            // 如果是编辑现有房间
            $editRoom = getRoomById($roomId);
        }
    } elseif (isset($_POST['delete_room'])) {
        // 处理删除请求
        $roomId = $_POST['room_id'];
        if (deleteRoom($roomId)) {
            $successMessage = 'Room deleted successfully';
            // 重新获取最新的房间列表
            $rooms = getRooms();
            // 重定向到当前页面以刷新数据
            header('Location: room.php?success=' . urlencode($successMessage));
            exit;
        } else {
            $errorMessage = 'Failed to delete room';
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
        $roomStatus = isset($_POST['room_status']) ? trim($_POST['room_status']) : 'available';
        
        // 验证数据
        if (empty($roomType) || $roomPrice <= 0 || $roomCapacity <= 0 || $roomQuantity <= 0) {
            $errorMessage = 'Please fill in all required fields with valid values';
        } else {
            // 处理图片上传
            if (isset($_FILES['room_image_upload']) && $_FILES['room_image_upload']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../assets/images/rooms/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileExtension = strtolower(pathinfo($_FILES['room_image_upload']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB
                
                if ($_FILES['room_image_upload']['size'] > $maxFileSize) {
                    $errorMessage = 'File size exceeds 5MB limit';
                } elseif (in_array($fileExtension, $allowedExtensions)) {
                    $newFileName = uniqid('room_') . '.' . $fileExtension;
                    $uploadPath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($_FILES['room_image_upload']['tmp_name'], $uploadPath)) {
                        $roomImage = $newFileName;
                    } else {
                        $errorMessage = 'Failed to upload image';
                    }
                } else {
                    $errorMessage = 'Invalid file type. Allowed types: ' . implode(', ', $allowedExtensions);
                }
            }
            
            if (empty($errorMessage)) {
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
                
                // 区分新增和更新操作
                $isNewRoom = !getRoomById($roomId);
                $success = $isNewRoom ? addRoom($roomData) : updateRoom($roomId, $roomData);
                
                if ($success) {
                    $successMessage = ($isNewRoom ? 'Room added' : 'Room information updated') . ' successfully';
                    $rooms = getRooms();
                    // 重定向到当前页面以刷新数据
                    header('Location: room.php?success=' . urlencode($successMessage));
                    exit;
                } else {
                    $errorMessage = 'Failed to ' . ($isNewRoom ? 'add' : 'update') . ' room information';
                }
            }
        }
    } elseif (isset($_POST['cancel_edit'])) {
        $editRoom = null;
    }
}
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
            <h2><?php echo $editRoom['id'] ? 'Edit Room' : 'Add New Room'; ?></h2>
            <form action="room.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="room_id" value="<?php echo $editRoom['id']; ?>">
                
                <div class="form-group">
                    <label for="room_type">Room Type</label>
                    <select id="room_type" name="room_type" class="form-control" required>
                        <option value="">Select Room Type</option>
                        <?php foreach (ROOM_TYPES as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($editRoom['type'] == $key) ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="room_price">Price (per night)</label>
                    <input type="number" id="room_price" name="room_price" class="form-control" value="<?php echo $editRoom['price']; ?>" min="0.01" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="room_breakfast">Includes Breakfast</label>
                    <input type="checkbox" id="room_breakfast" name="room_breakfast" <?php echo ($editRoom['breakfast'] == 'Yes') ? 'checked' : ''; ?>>
                </div>
                
                <div class="form-group">
                    <label for="room_capacity">Capacity</label>
                    <input type="number" id="room_capacity" name="room_capacity" class="form-control" value="<?php echo $editRoom['capacity']; ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="room_description">Description</label>
                    <textarea id="room_description" name="room_description" class="form-control" rows="3" required><?php echo $editRoom['description']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="room_image">Image Filename</label>
                    <input type="text" id="room_image" name="room_image" class="form-control" value="<?php echo $editRoom['image']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="room_quantity">Room Quantity</label>
                    <input type="number" id="room_quantity" name="room_quantity" class="form-control" value="<?php echo $editRoom['quantity']; ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <label for="room_status">Room Status</label>
                    <select id="room_status" name="room_status" class="form-control" required>
                        <option value="available" <?php echo ($editRoom['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                        <option value="maintenance" <?php echo ($editRoom['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="reserved" <?php echo ($editRoom['status'] == 'reserved') ? 'selected' : ''; ?>>Reserved</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="room_image_upload">Room Image</label>
                    <input type="file" id="room_image_upload" name="room_image_upload" class="form-control" accept="image/*">
                    <?php if ($editRoom['id'] && !empty($editRoom['image'])): ?>
                    <div style="margin-top: 10px;">
                        <img src="../assets/images/rooms/<?php echo $editRoom['image']; ?>" alt="Current Room Image" style="max-width: 200px;">
                    </div>
                    <?php endif; ?>
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
            <table class="table" style="width: 100%; border-collapse: separate; border-spacing: 0 5px;">
                <thead>
                    <tr style="background-color: var(--primary-color); color: white;">
                        <th style="padding: 15px; text-align: left; border-radius: 5px 0 0 5px;">ID</th>
                        <th style="padding: 15px; text-align: left;">Type</th>
                        <th style="padding: 15px; text-align: left;">Price</th>
                        <th style="padding: 15px; text-align: left;">Breakfast</th>
                        <th style="padding: 15px; text-align: left;">Capacity</th>
                        <th style="padding: 15px; text-align: left;">Quantity</th>
                        <th style="padding: 15px; text-align: center;">Status</th>
                        <th style="padding: 15px; text-align: center; border-radius: 0 5px 5px 0;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="8" style="padding: 20px; text-align: center;">No rooms found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr style="margin-bottom: 10px; background-color: #f9f9f9; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <td style="padding: 15px;"><?php echo $room['id']; ?></td>
                                <td style="padding: 15px;"><?php echo ROOM_TYPES[$room['type']] ?? $room['type']; ?></td>
                                <td style="padding: 15px;">$<?php echo $room['price']; ?></td>
                                <td style="padding: 15px;"><?php echo $room['breakfast']; ?></td>
                                <td style="padding: 15px;"><?php echo $room['capacity']; ?></td>
                                <td style="padding: 15px;"><?php echo $room['quantity']; ?></td>
                                <td style="padding: 15px; text-align: center;">
                                    <span class="badge <?php
                                        switch ($room['status']) {
                                            case 'available':
                                                echo 'badge-success';
                                                break;
                                            case 'maintenance':
                                                echo 'badge-warning';
                                                break;
                                            case 'reserved':
                                                echo 'badge-danger';
                                                break;
                                            default:
                                                echo 'badge-secondary';
                                        }
                                    ?>">
                                        <?php echo ucfirst($room['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 15px; text-align: center;">
                                    <form action="room.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                        <button type="submit" name="edit_room" class="btn btn-sm btn-primary">Edit</button>
                                    </form>
                                    <form action="room.php" method="POST" style="display: inline; margin-left: 10px;" onsubmit="return confirm('Are you sure you want to delete this room?');">
                                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                        <button type="submit" name="delete_room" class="btn btn-sm btn-danger">Delete</button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 为删除房间按钮添加AJAX处理
    const deleteButtons = document.querySelectorAll('button[name="delete_room"]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            // 阻止表单提交默认行为
            event.preventDefault();
            
            const roomId = this.closest('form').querySelector('input[name="room_id"]').value;
            const roomRow = this.closest('tr');
            
            // 确认删除
            if (!confirm('Are you sure you want to delete this room?')) {
                return;
            }
            
            // 准备表单数据
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('room_id', roomId);
            
            // 显示加载状态
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            // 发送AJAX请求
            fetch('../api/manage_room.php', {
                method: 'POST',
                body: formData,
                signal: AbortSignal.timeout(10000) // 10秒超时
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Server responded with status: ' + response.status);
                }
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
                    // 删除成功，从DOM中移除该行
                    roomRow.remove();
                    
                    // 显示成功消息
                    const container = document.querySelector('.container');
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success';
                    alertDiv.textContent = data.message;
                    
                    // 将消息插入到页面顶部
                    container.insertBefore(alertDiv, container.firstChild);
                    
                    // 3秒后淡出消息
                    setTimeout(() => {
                        alertDiv.style.transition = 'opacity 1s';
                        alertDiv.style.opacity = '0';
                        setTimeout(() => alertDiv.remove(), 1000);
                    }, 3000);
                } else {
                    // 恢复按钮状态
                    this.innerHTML = 'Delete';
                    this.disabled = false;
                    
                    // 显示错误消息
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // 恢复按钮状态
                this.innerHTML = 'Delete';
                this.disabled = false;
                
                // 显示错误消息
                alert('An error occurred while deleting the room. Please try again later.');
            });
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>