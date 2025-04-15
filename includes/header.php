<?php
session_start();
require_once __DIR__ . '/auth.php'; // 确保 auth.php 被包含

// 根据页面位置确定资源路径前缀
$pathPrefix = isset($adminPage) && $adminPage ? '../' : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Luxury Hotel' : 'Luxury Hotel'; ?></title>
    <link rel="stylesheet" href="<?php echo $pathPrefix; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="<?php echo $pathPrefix; ?>index.php" class="navbar-brand">Luxury Hotel</a>

                <ul class="nav-menu">
                    <?php if (isAdmin()): ?>
                        <?php // 管理员导航 ?>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>admin/index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>admin/booking.php" class="nav-link"><i class="fas fa-calendar-check"></i> Manage Bookings</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>admin/room.php" class="nav-link"><i class="fas fa-bed"></i> Manage Rooms</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>index.php" class="nav-link"><i class="fas fa-eye"></i> View Site</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php elseif (isLoggedIn()): ?>
                        <?php // 普通登录用户导航 ?>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>search.php" class="nav-link"><i class="fas fa-search"></i> Rooms</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>my-trips.php" class="nav-link"><i class="fas fa-suitcase"></i> My Trips</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <?php // 未登录用户导航 ?>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>search.php" class="nav-link"><i class="fas fa-search"></i> Rooms</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>login.php" class="nav-link"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li class="nav-item"><a href="<?php echo $pathPrefix; ?>register.php" class="nav-link"><i class="fas fa-user-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main>