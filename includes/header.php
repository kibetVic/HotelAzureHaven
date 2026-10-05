<?php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Azure Haven Hotel | Luxury Oceanside Resort</title>
    
    <!-- Favicon (Browser Title Bar Logo) -->
    <link rel="icon" type="image/png" href="assets/images/logos.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/logos.png">
    <link rel="apple-touch-icon" href="assets/images/logos.png">

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <img src="assets/images/logo.png" alt="Azure Haven Logo" class="logo-img">
            <span>Azure Haven</span>
        </a>
        <button class="mobile-toggle" aria-label="Toggle Menu">☰</button>
        <ul class="nav-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="rooms.php">Rooms & Suites</a></li>
            <li><a href="meals.php">Meals & Beverages</a></li>
            <li><a href="services.php">Services</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact</a></li>
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <li><a href="admin/index.php" class="btn btn-outline">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="btn btn-gold">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="btn btn-gold">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>