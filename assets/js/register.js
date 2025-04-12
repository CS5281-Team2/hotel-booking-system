document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            let hasError = false;
            let errorMessage = '';
            
            // 获取表单字段值
            const name = document.getElementById('name').value.trim();
            const mobile = document.getElementById('mobile').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // 验证姓名
            if (name === '') {
                errorMessage = 'Please enter your name';
                hasError = true;
            }
            // 验证手机号
            else if (mobile === '' || !/^\d{10}$/.test(mobile)) {
                errorMessage = 'Please enter a valid 10-digit mobile number';
                hasError = true;
            }
            // 验证邮箱
            else if (email === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errorMessage = 'Please enter a valid email address';
                hasError = true;
            }
            // 验证密码长度
            else if (password.length < 6) {
                errorMessage = 'Password must be at least 6 characters long';
                hasError = true;
            }
            // 验证密码匹配
            else if (password !== confirmPassword) {
                errorMessage = 'Passwords do not match';
                hasError = true;
            }
            
            // 如果有错误，阻止表单提交并显示错误
            if (hasError) {
                event.preventDefault();
                alert(errorMessage);
            }
        });
        
        // 手机号格式化
        const mobileInput = document.getElementById('mobile');
        if (mobileInput) {
            mobileInput.addEventListener('input', function(e) {
                // 移除所有非数字字符
                let value = this.value.replace(/\D/g, '');
                
                // 限制长度为10位
                if (value.length > 10) {
                    value = value.slice(0, 10);
                }
                
                this.value = value;
            });
        }
    }
});