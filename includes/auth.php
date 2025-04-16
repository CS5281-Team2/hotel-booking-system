<?php
require_once 'db.php';

/**
 * 处理用户注册
 */
function registerUser($name, $phone, $email, $password) {
    // 检查邮箱是否已被使用
    if (getUserByEmail($email)) {
        return [
            'success' => false,
            'message' => 'Email already registered'
        ];
    }
    
    // 创建用户数据 - 使用关联数组而非索引数组
    $userId = generateId();
    $userData = [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
        'phone' => $phone, // 统一使用 phone 字段
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user', // 角色
        'created_at' => date('Y-m-d H:i:s') // 注册时间
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
    
    if ($user && password_verify($password, $user['password_hash'])) { // 修改这里
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = ($user['role'] == 'admin');
        
        return [
            'success' => true,
            'user_id' => $user['id']
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
    $users = getUsers(); // getUsers 现在返回结构化数组列表
    
    foreach ($users as $user) {
        // 直接访问结构化数组的 'role' 键
        if (isset($user['role']) && $user['role'] == 'admin') {
            $adminExists = true;
            break;
        }
    }
    
    if (!$adminExists) {
        $adminId = generateId(); // 假设 generateId() 存在且可用
        // 使用关联数组以匹配 addUser 的期望格式
        $adminData = [
            'id' => $adminId,
            'name' => 'Admin User',
            'email' => 'admin@luxuryhotel.com',
            'phone' => '1234567890', // 示例电话
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // addUser 处理关联数组
        addUser($adminData);
    }
}

/**
 * 更新用户个人资料
 */
function updateUserProfile($userId, $name, $phone) {
    // 获取所有用户（已经是关联数组格式）
    $users = getUsers();
    $updatedUsers = [];
    $success = false;
    
    // 遍历用户并更新目标用户
    foreach ($users as $user) {
        if ($user['id'] == $userId) {
            // 更新字段
            $user['name'] = $name;
            $user['phone'] = $phone;
            $success = true;
        }
        
        // 准备要写入文件的格式（与 addUser 的 orderedData 保持一致）
        $orderedData = [
            $user['id'],
            $user['name'],
            $user['email'],
            $user['phone'],
            $user['password_hash'],
            $user['role'],
            $user['created_at']
        ];
        
        $updatedUsers[] = implode('|', $orderedData);
    }
    
    if ($success) {
        // 使用文件锁安全地写入更新后的用户数据
        $fp = fopen(USERS_FILE, 'w');
        if (!$fp) {
            error_log("Failed to open users file for profile update.");
            return ['success' => false, 'message' => 'Failed to update profile (file error)'];
        }
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, implode("\n", $updatedUsers) . "\n");
            fflush($fp);
            flock($fp, LOCK_UN);
            fclose($fp);
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            fclose($fp);
            error_log("Could not lock users file for profile update.");
            return ['success' => false, 'message' => 'Failed to update profile (lock error)'];
        }
    }
    
    return [
        'success' => false,
        'message' => 'User not found or failed to update profile'
    ];
}

// 初始化管理员账户
initAdmin();