/**
 * 共享验证规则
 * 用于确保前端和后端验证一致
 */
const ValidationRules = {
    // 统一的电话号码正则表达式
    phoneRegex: /^1[3-9]\d{9}$|^[569]\d{7}$|^[6]\d{7}$/,
    
    // 统一的名称验证
    nameRegex: /^[a-zA-Z\s]+$/,
    
    // 验证电话号码
    validatePhone: function(phone) {
        return this.phoneRegex.test(phone.trim());
    },
    
    // 验证名称
    validateName: function(name) {
        return this.nameRegex.test(name.trim());
    },
    
    // 获取电话验证错误消息
    getPhoneErrorMessage: function() {
        return 'Please enter a valid 11-digit Mainland China, 8-digit Hong Kong, or 8-digit Macau phone number.';
    }
};