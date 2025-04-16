document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            let hasError = false;
            let errorMessage = '';
            
            // 获取表单字段值
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // 验证正则表达式
            const nameRegex = /^[a-zA-Z\s]+$/; // 只允许字母和空格
            const phoneRegex = /^1[3-9]\d{9}$|^[569]\d{7}$|^[6]\d{7}$/; // 支持内地(11位)/香港(8位,5/9开头)/澳门(8位,6开头)
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // 验证姓名
            if (name === '') {
                errorMessage = 'Please enter your name';
                hasError = true;
            } else if (!nameRegex.test(name)) {
                errorMessage = 'Name can only contain letters and spaces';
                hasError = true;
            }
            // 验证手机号
            else if (phone === '') {
                errorMessage = 'Please enter your phone number';
                hasError = true;
            } else if (!phoneRegex.test(phone)) {
                errorMessage = 'Please enter a valid 11-digit Mainland China, 8-digit Hong Kong, or 8-digit Macau phone number';
                hasError = true;
            }
            // 验证邮箱
            else if (email === '' || !emailRegex.test(email)) {
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
        
        // 手机号格式化 - 仅允许数字输入，不再限制位数
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                // 移除所有非数字字符
                let value = this.value.replace(/\D/g, '');
                this.value = value;
            });
        }
    }
});