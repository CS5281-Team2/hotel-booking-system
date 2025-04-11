<?php
$pageTitle = 'Search Rooms';
include 'includes/header.php';
require_once 'includes/db.php';

// 获取搜索参数
$checkIn = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$checkOut = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 2;

// 验证日期
$validDates = true;
$dateError = '';

if (!empty($checkIn) && !empty($checkOut)) {
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $today = new DateTime();
    $today->setTime(0, 0, 0); // 重置时间部分
    
    if ($checkInDate < $today) {
        $validDates = false;
        $dateError = 'Check-in date cannot be in the past';
    } elseif ($checkOutDate <= $checkInDate) {
        $validDates = false;
        $dateError = 'Check-out date must be after check-in date';
    }
} else {
    $validDates = false;
}

// 获取所有房间
$allRooms = getRooms();
$availableRooms = [];

if ($validDates) {
    // 计算住宿天数
    $interval = $checkInDate->diff($checkOutDate);
    $nights = $interval->days;
    
    // 筛选可容纳指定客人数量的房间
    foreach ($allRooms as $room) {
        if ($room['capacity'] >= $guests && isRoomAvailable($room['id'], $checkIn, $checkOut)) {
            // 计算总价格
            $room['total_price'] = $room['price'] * $nights;
            $availableRooms[] = $room;
        }
    }
}
?>

<section style="padding: 50px 0;">
    <div class="container">
        <h1 style="margin-bottom: 20px;">Available Rooms</h1>
        
        <div class="search-box" style="margin-bottom: 20px;">
            <form action="search.php" method="GET" id="search-form">
                <div class="search-row">
                    <div class="search-col">
                        <label for="check_in">Check-in Date</label>
                        <input type="date" id="check_in" name="check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo $checkIn; ?>">
                    </div>
                    
                    <div class="search-col">
                        <label for="check_out">Check-out Date</label>
                        <input type="date" id="check_out" name="check_out" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo $checkOut; ?>">
                    </div>
                    
                    <div class="search-col">
                        <label for="guests">Guests</label>
                        <select id="guests" name="guests" class="form-control" required>
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $guests == $i ? 'selected' : ''; ?>><?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="search-col" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Update Search</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (!$validDates): ?>
            <div class="alert alert-error">
                <?php echo !empty($dateError) ? $dateError : 'Please select valid check-in and check-out dates to search for available rooms.'; ?>
            </div>
        <?php elseif (empty($availableRooms)): ?>
            <div class="alert alert-error">
                No rooms available for your selected dates and number of guests. Please try different dates or reduce the number of guests.
            </div>
        <?php else: ?>
            <div>
                <?php foreach ($availableRooms as $room): ?>
                    <div class="card" style="display: flex; margin-bottom: 30px;">
                        <div style="flex: 0 0 300px;">
                            <img src="https://via.placeholder.com/300x200?text=<?php echo str_replace(' ', '+', $room['type']); ?>" alt="<?php echo $room['type']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="card-body" style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h3 class="card-title"><?php echo $room['type']; ?></h3>
                                <p class="card-text"><?php echo $room['description']; ?></p>
                                <div style="margin-top: 15px;">
                                    <p><strong>Capacity:</strong> <?php echo $room['capacity']; ?> Persons</p>
                                    <p><strong>Breakfast:</strong> <?php echo $room['breakfast'] == 'Yes' ? 'Included' : 'Not included'; ?></p>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                <div>
                                    <p style="font-size: 1.2rem;"><strong>$<?php echo $room['price']; ?></strong> per night</p>
                                    <p><strong>Total:</strong> $<?php echo number_format($room['total_price'], 2); ?> for <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?></p>
                                </div>
                                <a href="booking.php?room_id=<?php echo $room['id']; ?>&check_in=<?php echo urlencode($checkIn); ?>&check_out=<?php echo urlencode($checkOut); ?>&guests=<?php echo $guests; ?>" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    // 表单验证
    searchForm.addEventListener('submit', function(event) {
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