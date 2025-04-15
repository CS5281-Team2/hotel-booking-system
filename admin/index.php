<?php
$pageTitle = 'Admin Dashboard';
$adminPage = true;
include '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

// 验证管理员权限
if (!isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// 自动清理预订数据文件中的重复记录
cleanupDuplicateBookings();

// 获取所有预订
$allBookings = getBookings();

// 获取今天的预订（入住和退房）
// 使用当前日期替代固定日期
$todayDate = date('Y-m-d'); // 使用当前日期
$todayCheckIns = [];
$todayCheckOuts = [];

// 用于检查重复预订的数组
$processedBookingIds = [];

foreach ($allBookings as $booking) {
    // 检查是否已经处理过此预订，防止重复
    if (in_array($booking['id'], $processedBookingIds)) {
        continue;
    }
    
    // 将预订ID添加到已处理数组中
    $processedBookingIds[] = $booking['id'];
    
    if ($booking['check_in'] == $todayDate && $booking['status'] != 'cancelled') {
        $room = getRoomById($booking['room_id']);
        $user = getUserById($booking['user_id']);
        if ($room && $user) {
            $booking['room'] = $room;
            $booking['user'] = $user;
            $todayCheckIns[] = $booking;
        }
    }
    
    if ($booking['check_out'] == $todayDate && $booking['status'] != 'cancelled') {
        $room = getRoomById($booking['room_id']);
        $user = getUserById($booking['user_id']);
        if ($room && $user) {
            $booking['room'] = $room;
            $booking['user'] = $user;
            $todayCheckOuts[] = $booking;
        }
    }
}

// 计算概览统计数据
$activeBookings = array_filter($allBookings, function($booking) {
    return $booking['status'] != 'cancelled';
});
$totalBookings = count($activeBookings);
$totalRevenue = array_sum(array_column($activeBookings, 'total_price'));
?>

<section style="padding: 50px 0;">
    <div class="container">
        <h1 style="margin-bottom: 20px;">Admin Dashboard</h1>

        <div style="display: flex; flex-wrap: wrap; margin: 0 -10px 30px -10px;">
            <div style="flex: 1; min-width: 250px; padding: 10px;">
                <div
                    style="background-color: #e8f4f8; padding: 20px; border-radius: 8px; border-left: 4px solid var(--primary-color);">
                    <h3><i class="fas fa-calendar-check"></i> Total Bookings</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;"><?php echo $totalBookings; ?></p>
                </div>
            </div>

            <div style="flex: 1; min-width: 250px; padding: 10px;">
                <div
                    style="background-color: #e8f8ea; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
                    <h3>Total Revenue</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;">
                        $<?php echo number_format($totalRevenue, 2); ?></p>
                </div>
            </div>

            <div style="flex: 1; min-width: 250px; padding: 10px;">
                <div
                    style="background-color: #f8f4e8; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <h3>Today's Check-ins</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;">
                        <?php echo count($todayCheckIns); ?></p>
                </div>
            </div>

            <div style="flex: 1; min-width: 250px; padding: 10px;">
                <div
                    style="background-color: #f8e8e8; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;">
                    <h3>Today's Check-outs</h3>
                    <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;">
                        <?php echo count($todayCheckOuts); ?></p>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 40px;">
            <h2>Today's Check-ins</h2>

            <?php if (empty($todayCheckIns)): ?>
            <div
                style="text-align: center; padding: 30px; background-color: #f9f9f9; border-radius: 8px; margin-top: 20px;">
                <p>No check-ins scheduled for today.</p>
            </div>
            <?php else: ?>
            <div style="overflow-x: auto; margin-top: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: var(--primary-color); color: white;">
                            <th style="padding: 12px 15px; text-align: left;">Booking ID</th>
                            <th style="padding: 12px 15px; text-align: left;">Guest</th>
                            <th style="padding: 12px 15px; text-align: left;">Room</th>
                            <th style="padding: 12px 15px; text-align: left;">Check-in</th>
                            <th style="padding: 12px 15px; text-align: left;">Check-out</th>
                            <th style="padding: 12px 15px; text-align: left;">Guests</th>
                            <th style="padding: 12px 15px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayCheckIns as $booking): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px 15px;"><?php echo substr($booking['id'], -8); ?></td>
                            <td style="padding: 12px 15px;">
                                <?php 
                                    if (isset($booking['user']) && is_array($booking['user']) && isset($booking['user'][1])) {
                                        echo $booking['user'][1];
                                    } else {
                                        echo 'Guest Info Not Available';
                                    }
                                ?>
                            </td>
                            <td style="padding: 12px 15px;">
                                <?php 
                                    if (isset($booking['room']) && is_array($booking['room']) && isset($booking['room']['type'])) {
                                        echo $booking['room']['type'];
                                    } else {
                                        echo 'Room Info Not Available';
                                    }
                                ?>
                            </td>
                            <td style="padding: 12px 15px;">
                                <?php echo date('M j, Y', strtotime($booking['check_in'])); ?></td>
                            <td style="padding: 12px 15px;">
                                <?php echo date('M j, Y', strtotime($booking['check_out'])); ?></td>
                            <td style="padding: 12px 15px;"><?php echo $booking['guests']; ?></td>
                            <td style="padding: 12px 15px; text-align: right;">
                                $<?php echo number_format($booking['total_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div>
            <h2>Today's Check-outs</h2>

            <?php if (empty($todayCheckOuts)): ?>
            <div
                style="text-align: center; padding: 30px; background-color: #f9f9f9; border-radius: 8px; margin-top: 20px;">
                <p>No check-outs scheduled for today.</p>
            </div>
            <?php else: ?>
            <div style="overflow-x: auto; margin-top: 20px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: var(--primary-color); color: white;">
                            <th style="padding: 12px 15px; text-align: left;">Booking ID</th>
                            <th style="padding: 12px 15px; text-align: left;">Guest</th>
                            <th style="padding: 12px 15px; text-align: left;">Room</th>
                            <th style="padding: 12px 15px; text-align: left;">Check-in</th>
                            <th style="padding: 12px 15px; text-align: left;">Check-out</th>
                            <th style="padding: 12px 15px; text-align: left;">Guests</th>
                            <th style="padding: 12px 15px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayCheckOuts as $booking): ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px 15px;"><?php echo substr($booking['id'], -8); ?></td>
                            <td style="padding: 12px 15px;">
                                <?php 
                                    if (isset($booking['user']) && is_array($booking['user']) && isset($booking['user'][1])) {
                                        echo $booking['user'][1];
                                    } else {
                                        echo 'Guest Info Not Available';
                                    }
                                ?>
                            </td>
                            <td style="padding: 12px 15px;">
                                <?php 
                                    if (isset($booking['room']) && is_array($booking['room']) && isset($booking['room']['type'])) {
                                        echo $booking['room']['type'];
                                    } else {
                                        echo 'Room Info Not Available';
                                    }
                                ?>
                            </td>
                            <td style="padding: 12px 15px;">
                                <?php echo date('M j, Y', strtotime($booking['check_in'])); ?></td>
                            <td style="padding: 12px 15px;">
                                <?php echo date('M j, Y', strtotime($booking['check_out'])); ?></td>
                            <td style="padding: 12px 15px;"><?php echo $booking['guests']; ?></td>
                            <td style="padding: 12px 15px; text-align: right;">
                                $<?php echo number_format($booking['total_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="booking.php" class="btn btn-primary">View All Bookings</a>
            <a href="room.php" class="btn btn-primary" style="margin-left: 10px;">Manage Rooms</a>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>