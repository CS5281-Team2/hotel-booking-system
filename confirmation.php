<?php
$pageTitle = 'Booking Confirmation';
include 'includes/header.php';
require_once 'includes/db.php';

// 验证用户登录状态
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 获取预订ID
$bookingId = isset($_GET['booking_id']) ? $_GET['booking_id'] : '';
$booking = getBookingById($bookingId);

// 如果预订不存在或不属于当前用户，重定向到我的旅程页面
if (!$booking || $booking['user_id'] != $_SESSION['user_id']) {
    header('Location: my-trips.php');
    exit;
}

// 获取房间信息
$room = getRoomById($booking['room_id']);

// 计算住宿天数
$checkInDate = new DateTime($booking['check_in']);
$checkOutDate = new DateTime($booking['check_out']);
$interval = $checkInDate->diff($checkOutDate);
$nights = $interval->days;
?>

<section style="padding: 50px 0;">
    <div class="container">
        <div class="alert alert-success" style="text-align: center;">
            <h2 style="color: #155724; margin-bottom: 10px;">Booking Confirmed!</h2>
            <p>Your booking has been successfully confirmed. Thank you for choosing our Luxury Hotel.</p>
        </div>
        
        <div class="card" style="margin-top: 30px;">
            <div class="card-body">
                <h2 class="card-title">Booking Details</h2>
                
                <div style="margin-top: 20px;">
                    <p><strong>Booking Reference:</strong> <?php echo $booking['id']; ?></p>
                    <p><strong>Booking Date:</strong> <?php echo date('F j, Y', strtotime($booking['created_at'])); ?></p>
                </div>
                
                <div style="display: flex; margin: 20px 0; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; padding: 20px 0;">
                    <img src="assets/images/rooms/<?php echo $room['image']; ?>" alt="<?php echo $room['type']; ?>" style="width: 150px; height: 100px; object-fit: cover; border-radius: 4px; margin-right: 20px;">
                    <div>
                        <h3><?php echo $room['type']; ?></h3>
                        <p><?php echo $room['description']; ?></p>
                        <p><strong>Guests:</strong> <?php echo $booking['guests']; ?> Person<?php echo $booking['guests'] > 1 ? 's' : ''; ?></p>
                        <p><strong>Breakfast:</strong> <?php echo $room['breakfast'] == 'Yes' ? 'Included' : 'Not included'; ?></p>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                    <div>
                        <p><strong>Check-in</strong></p>
                        <p><?php echo date('l, F j, Y', strtotime($booking['check_in'])); ?></p>
                        <p>From 3:00 PM</p>
                    </div>
                    
                    <div>
                        <p><strong>Check-out</strong></p>
                        <p><?php echo date('l, F j, Y', strtotime($booking['check_out'])); ?></p>
                        <p>Until 12:00 PM</p>
                    </div>
                </div>
                
                <div style="border-top: 1px solid #ddd; padding-top: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <p><?php echo $nights; ?> Night<?php echo $nights > 1 ? 's' : ''; ?> x $<?php echo $room['price']; ?></p>
                        <p>$<?php echo number_format($booking['total_price'], 2); ?></p>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <p>Taxes & Fees</p>
                        <p>Included</p>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold; margin-top: 10px; border-top: 1px solid #ddd; padding-top: 15px;">
                        <p>Total</p>
                        <p>$<?php echo number_format($booking['total_price'], 2); ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="my-trips.php" class="btn btn-primary">View My Trips</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>