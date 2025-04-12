document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.querySelector('form[action="booking.php"]');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(event) {
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            
            // 转换为日期对象进行比较
            const checkInDate = new Date(checkIn);
            const checkOutDate = new Date(checkOut);
            const today = new Date();
            today.setHours(0, 0, 0, 0); // 重置时间部分以便比较日期
            
            let hasError = false;
            let errorMessage = '';
            
            // 检查入住日期是否在今天或之后
            if (checkInDate < today) {
                errorMessage = 'Check-in date cannot be in the past.';
                hasError = true;
            }
            
            // 检查退房日期是否在入住日期之后
            if (checkOutDate <= checkInDate) {
                errorMessage = 'Check-out date must be after check-in date.';
                hasError = true;
            }
            
            // 如果出现错误，阻止表单提交并显示错误
            if (hasError) {
                event.preventDefault();
                alert(errorMessage);
            }
        });
    }
    
    // 设置入住日期变化时更新退房日期最小值
    const checkInInput = document.getElementById('check_in');
    if (checkInInput) {
        checkInInput.addEventListener('change', function() {
            const checkOutInput = document.getElementById('check_out');
            if (checkOutInput) {
                // 将退房日期最小值设为入住日期后一天
                const checkInDate = new Date(this.value);
                checkInDate.setDate(checkInDate.getDate() + 1);
                const minCheckOutDate = checkInDate.toISOString().split('T')[0];
                checkOutInput.setAttribute('min', minCheckOutDate);
                
                // 如果当前选择的退房日期小于新的最小日期，则更新退房日期
                if (checkOutInput.value && new Date(checkOutInput.value) < checkInDate) {
                    checkOutInput.value = minCheckOutDate;
                }
            }
        });
    }
});