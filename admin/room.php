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
?>

<section class="admin-rooms py-50">
    <div class="container">
        <h1 class="mb-20">Manage Rooms</h1>
        
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="6" style="padding: 20px; text-align: center;">No rooms found</td>
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