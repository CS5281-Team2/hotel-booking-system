<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Luxury Hotel' : 'Luxury Hotel'; ?></title>
    <link rel="stylesheet" href="<?php echo isset($adminPage) ? '../' : ''; ?>assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="<?php echo isset($adminPage) ? '../' : ''; ?>index.php" class="navbar-brand">Luxury Hotel</a>
                
                <ul class="nav-menu">
                    <li class="nav-item"><a href="<?php echo isset($adminPage) ? '../' : ''; ?>index.php" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="<?php echo isset($adminPage) ? '../' : ''; ?>search.php" class="nav-link">Rooms</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a href="<?php echo isset($adminPage) ? '../' : ''; ?>my-trips.php" class="nav-link">My Trips</a></li>
                        <li class="nav-item"><a href="<?php echo isset($adminPage) ? '../' : ''; ?>profile.php" class="nav-link">Profile</a></li>
                        <li class="nav-item"><a href="<?php echo isset($adminPage) ? '../' : ''; ?>logout.php" class="nav-link">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a href="<?php echo isset($adminPage) ? '../' : ''; ?>login.php" class="nav-link">Login</a></li>
                        <li class="nav-item"><a href="<?php echo isset($adminPage) ? '../' : ''; ?>register.php" class="nav-link">Register</a></li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li class="nav-item"><a href="<?php echo isset($adminPage) ? '' : 'admin/'; ?>index.php" class="nav-link">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main>