<?php
$currentAdmin = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        ☕ Kopi <span>Nusantara</span>
        <div style="font-size:.75rem;color:rgba(253,246,238,.45);font-family:'Inter',sans-serif;margin-top:4px;font-weight:400">Panel <?= ucfirst($_SESSION['role'] ?? '') ?></div>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Menu Utama</div>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= $currentAdmin === 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="<?= BASE_URL ?>/admin/produk.php" class="<?= $currentAdmin === 'produk.php' ? 'active' : '' ?>">
            <i class="fas fa-coffee"></i> Kelola Produk
        </a>
        <a href="<?= BASE_URL ?>/admin/transaksi.php" class="<?= $currentAdmin === 'transaksi.php' ? 'active' : '' ?>">
            <i class="fas fa-receipt"></i> Data Transaksi
        </a>
        <div class="sidebar-section-label" style="margin-top:16px">Halaman Publik</div>
        <a href="<?= BASE_URL ?>/index.php">
            <i class="fas fa-home"></i> Beranda
        </a>
        <a href="<?= BASE_URL ?>/katalog.php">
            <i class="fas fa-th-large"></i> Katalog
        </a>
        <div class="sidebar-section-label" style="margin-top:16px">Akun</div>
        <a href="<?= BASE_URL ?>/logout.php" style="color:rgba(220,80,80,.8)">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </nav>
</aside>
