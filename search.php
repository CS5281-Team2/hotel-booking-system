<?php
$pageTitle = 'Search Rooms';
include 'includes/header.php';
require_once 'includes/db.php';

// 获取搜索参数 - 添加默认日期值
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$checkIn = isset($_GET['check_in']) && !empty($_GET['check_in']) ? $_GET['check_in'] : $today;
$checkOut = isset($_GET['check_out']) && !empty($_GET['check_out']) ? $_GET['check_out'] : $tomorrow;
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 2;

// 验证日期
$validDates = true;
$dateError = '';
$dateChanged = false;
$maxBookingDays = 30; // 设置最大预订天数

// 总是进行日期验证，因为现在我们总是有日期
$checkInDate = new DateTime($checkIn);
$checkOutDate = new DateTime($checkOut);
$todayDate = new DateTime();
$todayDate->setTime(0, 0, 0); // 重置时间部分

if ($checkInDate < $todayDate) {
    $validDates = true; // 仍然允许搜索，但通知用户已修正
    $dateError = 'Check-in date cannot be in the past, automatically adjusted to today';
    $dateChanged = true;
    // 自动修正到今天
    $checkIn = $today;
    $checkInDate = new DateTime($checkIn);
} 

if ($checkOutDate <= $checkInDate) {
    $validDates = true; // 仍然允许搜索，但通知用户已修正
    $dateError .= $dateError ? ' and ' : '';
    $dateError .= 'Check-out date must be after check-in date, automatically adjusted';
    $dateChanged = true;
    // 自动修正到入住日期后一天
    $nextDay = clone $checkInDate;
    $nextDay->modify('+1 day');
    $checkOut = $nextDay->format('Y-m-d');
    $checkOutDate = $nextDay;
}

// 检查预订天数是否超过最大限制
$interval = $checkInDate->diff($checkOutDate);
$nights = $interval->days;

if ($nights > $maxBookingDays) {
    $validDates = true; // 仍然允许搜索，但通知用户已修正
    $dateError .= $dateError ? ' and ' : '';
    $dateError .= "Maximum stay is {$maxBookingDays} days, automatically adjusted";
    $dateChanged = true;
    // 自动修正到最大天数
    $maxCheckOut = clone $checkInDate;
    $maxCheckOut->modify("+{$maxBookingDays} days");
    $checkOut = $maxCheckOut->format('Y-m-d');
    $checkOutDate = $maxCheckOut;
    $interval = $checkInDate->diff($checkOutDate);
    $nights = $interval->days;
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
                        <label for="check_in"><i class="far fa-calendar-alt"></i> Check-in Date</label>
                        <input type="date" id="check_in" name="check_in" class="form-control" required
                            min="<?php echo date('Y-m-d'); ?>" value="<?php echo $checkIn; ?>">
                    </div>

                    <div class="search-col">
                        <label for="check_out"><i class="far fa-calendar-alt"></i> Check-out Date</label>
                        <input type="date" id="check_out" name="check_out" class="form-control" required
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo $checkOut; ?>">
                    </div>

                    <div class="search-col">
                        <label for="guests"><i class="fas fa-user-friends"></i> Guests</label>
                        <select id="guests" name="guests" class="form-control" required>
                            <option value="1" <?php echo ($guests == 1) ? 'selected' : ''; ?>>1 Guest</option>
                            <option value="2" <?php echo ($guests == 2) ? 'selected' : ''; ?>>2 Guests</option>
                            <option value="3" <?php echo ($guests == 3) ? 'selected' : ''; ?>>3 Guests</option>
                            <option value="4" <?php echo ($guests == 4) ? 'selected' : ''; ?>>4 Guests</option>
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
            No rooms available for your selected dates and number of guests. Please try different dates or reduce the
            number of guests.
        </div>
        <?php elseif ($dateChanged): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> <?php echo $dateError; ?>
        </div>
        <?php else: ?>
        <div>
            <?php foreach ($availableRooms as $room): ?>
            <div class="card" style="display: flex; margin-bottom: 30px;">
                <div style="flex: 0 0 300px;">
                    <img src="assets/images/rooms/<?php echo $room['image']; ?>" alt="<?php echo $room['type']; ?>"
                        style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div class="card-body"
                    style="flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h3 class="card-title"><?php echo $room['type']; ?></h3>
                        <p class="card-text"><?php echo $room['description']; ?></p>
                        <div style="margin-top: 15px;">
                            <p><strong>Capacity:</strong> <?php echo $room['capacity']; ?> Persons</p>
                            <p><strong>Breakfast:</strong>
                                <?php echo $room['breakfast'] == 'Yes' ? 'Included' : 'Not included'; ?></p>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                        <div>
                            <p style="font-size: 1.2rem;"><strong>$<?php echo $room['price']; ?></strong> per night</p>
                            <p><strong>Total:</strong> $<?php echo number_format($room['total_price'], 2); ?> for
                                <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?></p>
                        </div>
                        <a href="booking.php?room_id=<?php echo $room['id']; ?>&check_in=<?php echo urlencode($checkIn); ?>&check_out=<?php echo urlencode($checkOut); ?>&guests=<?php echo $guests; ?>"
                            class="btn btn-primary">Book Now</a>
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