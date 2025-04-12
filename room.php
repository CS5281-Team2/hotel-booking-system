<?php
$pageTitle = 'Room Details';
include 'includes/header.php';
require_once 'includes/db.php';

// 获取房间ID
$roomId = isset($_GET['id']) ? $_GET['id'] : '';
$room = getRoomById($roomId);

// 如果房间不存在，重定向到搜索页
if (!$room) {
    header('Location: search.php');
    exit;
}
?>

<section style="padding: 50px 0;">
    <div class="container">
        <div style="display: flex; flex-wrap: wrap;">
            <div style="flex: 0 0 100%; max-width: 100%; margin-bottom: 30px;">
                <h1><?php echo $room['type']; ?></h1>
            </div>

            <div style="flex: 0 0 60%; max-width: 60%; padding-right: 30px;">
                <img src="assets/images/rooms/<?php echo $room['image']; ?>" alt="<?php echo $room['type']; ?>"
                    style="width: 100%; border-radius: 8px; margin-bottom: 20px;">

                <h2>Room Description</h2>
                <p style="margin-bottom: 20px;"><?php echo $room['description']; ?></p>

                <h2>Room Features</h2>
                <ul style="margin-bottom: 20px; padding-left: 20px;">
                    <li>Maximum Occupancy: <?php echo $room['capacity']; ?> Persons</li>
                    <li>Breakfast: <?php echo $room['breakfast'] == 'Yes' ? 'Included' : 'Not included'; ?></li>
                    <li>Free Wi-Fi</li>
                    <li>Air Conditioning</li>
                    <li>Flat-screen TV</li>
                    <li>Private Bathroom with Shower</li>
                </ul>

                <h2>Cancellation Policy</h2>
                <p>Free cancellation up to 24 hours before check-in. Cancellations made within 24 hours of check-in are
                    subject to a one-night charge.</p>
            </div>

            <div style="flex: 0 0 40%; max-width: 40%;">
                <div class="card">
                    <div class="card-body">
                        <h2 class="card-title">Book this Room</h2>

                        <p style="font-size: 1.5rem; margin-bottom: 10px;">
                            <strong>$<?php echo $room['price']; ?></strong> <span style="font-size: 1rem;">per
                                night</span></p>

                        <form action="booking.php" method="GET" id="booking-form">
                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">

                            <div class="form-group">
                                <label for="check_in">Check-in Date</label>
                                <input type="date" id="check_in" name="check_in" class="form-control" required
                                    min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label for="check_out">Check-out Date</label>
                                <input type="date" id="check_out" name="check_out" class="form-control" required
                                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>

                            <div class="form-group">
                                <label for="guests">Number of Guests</label>
                                <select id="guests" name="guests" class="form-control" required>
                                    <?php for ($i = 1; $i <= $room['capacity']; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?>
                                        Guest<?php echo $i > 1 ? 's' : ''; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: 100%;">Book Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('booking-form');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');

    // 设置今天日期为入住日期的默认值
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    checkInInput.valueAsDate = today;
    checkOutInput.valueAsDate = tomorrow;

    // 表单验证
    bookingForm.addEventListener('submit', function(event) {
        const checkIn = new Date(checkInInput.value);
        const checkOut = new Date(checkOutInput.value);

        if (checkOut <= checkIn) {
            event.preventDefault();
            alert('Check-out date must be after check-in date');
        }
    });

    // 当入住日期变化时更新退房日期最小值
    checkInInput.addEventListener('change', function() {
        const checkInDate = new Date(this.value);
        const nextDay = new Date(checkInDate);
        nextDay.setDate(nextDay.getDate() + 1);

        const nextDayStr = nextDay.toISOString().split('T')[0];
        checkOutInput.min = nextDayStr;

        // 如果当前选择的退房日期早于新的最小日期，更新它
        if (new Date(checkOutInput.value) <= checkInDate) {
            checkOutInput.value = nextDayStr;
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>