document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('booking-form');
    
    if (bookingForm) {
        // 验证信用卡号格式
        const cardNumberInput = document.getElementById('card_number');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function(e) {
                // 移除所有非数字字符
                let value = this.value.replace(/\D/g, '');
                
                // 限制长度为16位
                if (value.length > 16) {
                    value = value.slice(0, 16);
                }
                
                // 添加空格格式化
                let formattedValue = '';
                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formattedValue += ' ';
                    }
                    formattedValue += value[i];
                }
                
                this.value = formattedValue;
            });
        }
        
        // 验证过期日期格式
        const expiryInput = document.getElementById('card_expiry');
        if (expiryInput) {
            expiryInput.addEventListener('input', function(e) {
                // 移除所有非数字字符
                let value = this.value.replace(/\D/g, '');
                
                // 限制长度为4位
                if (value.length > 4) {
                    value = value.slice(0, 4);
                }
                
                // 添加斜杠格式化
                if (value.length > 2) {
                    value = value.slice(0, 2) + '/' + value.slice(2);
                }
                
                this.value = value;
            });
        }
        
        // 验证CVV格式
        const cvvInput = document.getElementById('card_cvv');
        if (cvvInput) {
            cvvInput.addEventListener('input', function(e) {
                // 仅允许数字
                let value = this.value.replace(/\D/g, '');
                
                // 限制长度为3位
                if (value.length > 3) {
                    value = value.slice(0, 3);
                }
                
                this.value = value;
            });
        }
        
        // 表单提交验证
        bookingForm.addEventListener('submit', function(event) {
            let isValid = true;
            let errorMessage = '';
            
            // 验证持卡人姓名
            const cardName = document.getElementById('card_name').value.trim();
            if (cardName === '') {
                isValid = false;
                errorMessage = 'Please enter the name on card';
            }
            
            // 验证卡号
            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            if (cardNumber === '' || cardNumber.length !== 16 || !/^\d+$/.test(cardNumber)) {
                isValid = false;
                errorMessage = 'Please enter a valid 16-digit card number';
            }
            
            // 验证过期日期
            const expiry = document.getElementById('card_expiry').value;
            if (expiry === '' || !expiry.match(/^\d{2}\/\d{2}$/)) {
                isValid = false;
                errorMessage = 'Please enter a valid expiry date (MM/YY)';
            } else {
                // 验证月份是否有效
                const month = parseInt(expiry.split('/')[0], 10);
                if (month < 1 || month > 12) {
                    isValid = false;
                    errorMessage = 'Please enter a valid month (01-12)';
                }
                
                // 验证是否过期
                const year = parseInt('20' + expiry.split('/')[1], 10);
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = currentDate.getMonth() + 1;
                
                if (year < currentYear || (year === currentYear && month < currentMonth)) {
                    isValid = false;
                    errorMessage = 'Your card has expired';
                }
            }
            
            // 验证CVV
            const cvv = document.getElementById('card_cvv').value;
            if (cvv === '' || cvv.length !== 3 || !/^\d+$/.test(cvv)) {
                isValid = false;
                errorMessage = 'Please enter a valid 3-digit CVV';
            }
            
            if (!isValid) {
                event.preventDefault();
                alert(errorMessage);
            }
        });
    }
});