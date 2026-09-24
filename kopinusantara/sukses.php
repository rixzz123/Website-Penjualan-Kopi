<?php
require_once 'includes/auth.php';
require_once 'includes/koneksi.php';
$pageTitle = 'Pesanan Berhasil – Kopi Nusantara';
include 'includes/header.php';
?>

<div class="success-page">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h2>Pesanan Berhasil!</h2>
        <p>Terima kasih telah berbelanja di Kopi Nusantara. Pesanan Anda telah berhasil diproses dan sedang menunggu konfirmasi dari agen.</p>
        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
            <a href="<?= BASE_URL ?>/riwayat.php" class="hero-btn-primary" style="display:inline-flex">
                <i class="fas fa-receipt"></i> Lihat Riwayat Pesanan
            </a>
            <a href="<?= BASE_URL ?>/katalog.php" class="hero-btn-secondary" style="display:inline-flex">
                <i class="fas fa-coffee"></i> Belanja Lagi
            </a>
            <a href="<?= BASE_URL ?>/index.php" class="hero-btn-secondary" style="display:inline-flex">
                <i class="fas fa-home"></i> Beranda
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
