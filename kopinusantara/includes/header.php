<?php
$cartCount = isset($_SESSION['keranjang']) ? array_sum(array_column($_SESSION['keranjang'], 'qty')) : 0;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Kopi Nusantara' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
            <span class="logo-icon">☕</span>
            <span class="logo-text">Kopi <strong>Nusantara</strong></span>
        </a>

        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>/index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Beranda</a></li>
            <li><a href="<?= BASE_URL ?>/katalog.php" class="<?= $currentPage === 'katalog.php' ? 'active' : '' ?>">Katalog</a></li>
            <?php if (isLoggedIn() && getRole() === 'agen'): ?>
            <li><a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= strpos($currentPage, 'admin') !== false ? 'active' : '' ?>">Dashboard</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-actions">
            <?php if (isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/keranjang.php" class="btn-cart">
                    <i class="fas fa-shopping-bag"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="cart-badge"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <div class="user-dropdown">
                    <button class="btn-user">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <?php if (getRole() === 'pembeli'): ?>
                        <a href="<?= BASE_URL ?>/riwayat.php"><i class="fas fa-receipt"></i> Riwayat Transaksi</a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="btn-outline">Masuk</a>
                <a href="<?= BASE_URL ?>/register.php" class="btn-primary">Daftar</a>
            <?php endif; ?>
        </div>

        <button class="nav-toggle" id="navToggle">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
