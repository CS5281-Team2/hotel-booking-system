<?php
session_start();
require_once 'includes/auth.php';

// 注销用户
logoutUser();

// 重定向到首页
header('Location: index.php');
exit;
?>