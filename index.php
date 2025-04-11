<?php
$pageTitle = 'Welcome';
include 'includes/header.php';
?>

<section style="background-image: url('https://via.placeholder.com/1920x600?text=Hotel+Banner'); background-size: cover; background-position: center; height: 600px; position: relative;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4);"></div>
    <div class="container" style="position: relative; height: 100%; display: flex; flex-direction: column; justify-content: center;">
        <h1 style="color: white; font-size: 3rem; margin-bottom: 1rem;">Experience Luxury Like Never Before</h1>
        <p style="color: white; font-size: 1.2rem; margin-bottom: 2rem; max-width: 600px;">Indulge in the perfect blend of elegance, comfort, and world-class service at our Luxury Hotel.</p>
        
        <div class="search-box">
            <h2 style="margin-bottom: 20px; color: var(--primary-color);">Find Your Perfect Stay</h2>
            <form action="search.php" method="GET" id="search-form">
                <div class="search-row">
                    <div class="search-col">
                        <label for="check_in">Check-in Date</label>
                        <input type="date" id="check_in" name="check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="search-col">
                        <label for="check_out">Check-out Date</label>
                        <input type="date" id="check_out" name="check_out" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    
                    <div class="search-col">
                        <label for="guests">Guests</label>
                        <select id="guests" name="guests" class="form-control" required>
                            <option value="1">1 Guest</option>
                            <option value="2" selected>2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                        </select>
                    </div>
                    
                    <div class="search-col" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Search Rooms</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<section style="padding: 50px 0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 20px;">Featured Rooms</h2>
        
        <div class="room-grid">
            <?php
            require_once 'includes/db.php';
            $rooms = getRooms();
            $featuredRooms = array_slice($rooms, 0, 3); // 显示前3个房间
            
            foreach ($featuredRooms as $room):
            ?>
            <div class="card">
                <img src="https://via.placeholder.com/400x250?text=<?php echo str_replace(' ', '+', $room['type']); ?>" alt="<?php echo $room['type']; ?>" class="card-image">
                <div class="card-body">
                    <h3 class="card-title"><?php echo $room['type']; ?></h3>
                    <p class="card-text"><?php echo $room['description']; ?></p>
                    <p><strong>From $<?php echo $room['price']; ?> per night</strong></p>
                    <a href="room.php?id=<?php echo $room['id']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    const checkInInput = document.getElementById('check_in');
    const checkOutInput = document.getElementById('check_out');
    
    // 设置今天日期为入住日期的默认值
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    checkInInput.valueAsDate = today;
    checkOutInput.valueAsDate = tomorrow;
    
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