<?php
require_once 'db.php';

/**
 * 处理用户注册
 */
function registerUser($name, $mobile, $email, $password) {
    // 检查邮箱是否已被使用
    if (getUserByEmail($email)) {
        return [
            'success' => false,
            'message' => 'Email already registered'
        ];
    }
    
    // 创建用户数据
    $userId = generateId();
    $userData = [
        $userId,
        $name,
        $email,
        $mobile,
        password_hash($password, PASSWORD_DEFAULT),
        'user', // 角色
        date('Y-m-d H:i:s') // 注册时间
    ];
    
    // 添加用户
    if (addUser($userData)) {
        return [
            'success' => true,
            'user_id' => $userId
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Failed to register user'
    ];
}

/**
 * 处理用户登录
 */
function loginUser($email, $password) {
    $user = getUserByEmail($email);
    
    if ($user && password_verify($password, $user[4])) {
        $_SESSION['user_id'] = $user[0];
        $_SESSION['user_name'] = $user[1];
        $_SESSION['user_email'] = $user[2];
        $_SESSION['is_admin'] = ($user[5] == 'admin');
        
        return [
            'success' => true,
            'user_id' => $user[0]
        ];
    }
    
    return [
        'success' => false,
        'message' => 'Invalid email or password'
    ];
}

/**
 * 检查用户是否已登录
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * 检查用户是否是管理员
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}

/**
 * 注销用户
 */
function logoutUser() {
    session_unset();
    session_destroy();
    return true;
}

/**
 * 初始化管理员账户（仅在第一次使用时）
 */
function initAdmin() {
    $adminExists = false;
    $users = getUsers();
    
    foreach ($users as $user) {
        $userData = explode('|', $user);
        if (isset($userData[5]) && $userData[5] == 'admin') {
            $adminExists = true;
            break;
        }
    }
    
    if (!$adminExists) {
        $adminId = generateId();
        $adminData = [
            $adminId,
            'Admin User',
            'admin@luxuryhotel.com',
            '1234567890',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin',
            date('Y-m-d H:i:s')
        ];
        
        addUser($adminData);
    }
}

/**
 * 更新用户个人资料
 */
function updateUserProfile($userId, $name, $mobile) {
    $users = getUsers();
    $updatedUsers = [];
    $success = false;
    
    foreach ($users as $user) {
        $userData = explode('|', $user);
        if (isset($userData[0]) && $userData[0] == $userId) {
            // 更新用户信息
            $userData[1] = $name;
            $userData[3] = $mobile;
            $success = true;
        }
        $updatedUsers[] = implode('|', $userData);
    }
    
    if ($success) {
        // 写入更新后的用户数据
        if (file_put_contents(USERS_FILE, implode("\n", $updatedUsers)) !== false) {
            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => 'Failed to update profile'
    ];
}

// 初始化管理员账户
initAdmin();