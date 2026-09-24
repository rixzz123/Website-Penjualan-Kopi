<?php
// 1. Munculkan semua error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/auth.php';

// 2. Cek apakah filenya beneran ada di folder includes
if (!file_exists('includes/koneksi.php')) {
    die("<h3>❌ Salah Tempat, Bre!</h3><p>File <code>koneksi.php</code> gak ada di folder <b>includes</b>. Lu pasti naruhnya di luar folder ya? Coba pindahin ke dalam folder <b>includes</b>.</p>");
}

require_once 'includes/koneksi.php';

// 3. Cek apakah variabel $koneksi beneran keisi atau kosong
if (!isset($koneksi) || $koneksi === null) {
    die("<h3>❌ Filenya Kosong atau Salah Variabel!</h3><p>File <code>htdocs/includes/koneksi.php</code> berhasil dibaca, tapi isinya KOSONG atau belum tersimpan sempurna di hostingan. Coba buka lagi folder <b>includes</b>, terus cek isi <code>koneksi.php</code>-nya, beneran ada kodenya gak?</p>");
}

$pageTitle = 'Kopi Nusantara – Marketplace Kopi Premium';

// Produk unggulan: 4 termahal
$sql = "SELECT p.*, u.nama_lengkap AS nama_agen 
        FROM produk p 
        LEFT JOIN users u ON p.id_agen = u.id_user \n        ORDER BY p.harga DESC LIMIT 4";
$produkUnggulan = $koneksi->query($sql);
// Stats
$totalProduk  = $koneksi->query("SELECT COUNT(*) AS c FROM produk")->fetch_assoc()['c'];
$totalAgen    = $koneksi->query("SELECT COUNT(*) AS c FROM users WHERE role='agen'")->fetch_assoc()['c'];
$totalOrder   = $koneksi->query("SELECT COUNT(*) AS c FROM transaksi")->fetch_assoc()['c'];

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-text">
            <div class="hero-badge">
                <i class="fas fa-leaf"></i> Premium Coffee Marketplace
            </div>
            <h1>Cita Rasa Kopi <span>Nusantara</span> di Ujung Jarimu</h1>
            <p>Temukan koleksi kopi terbaik dari berbagai penjuru Indonesia. Dari dataran tinggi Gayo hingga pegunungan Toraja, langsung dari tangan agen terpercaya.</p>
            <div class="hero-btns">
                <a href="<?= BASE_URL ?>/katalog.php" class="hero-btn-primary">
                    <i class="fas fa-coffee"></i> Jelajahi Katalog
                </a>
                <?php if (!isLoggedIn()): ?>
                <a href="<?= BASE_URL ?>/register.php" class="hero-btn-secondary">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </a>
                <?php elseif (isLoggedIn() && getRole() === 'pembeli'): ?>
                <a href="<?= BASE_URL ?>/riwayat.php" class="hero-btn-secondary">
                    <i class="fas fa-receipt"></i> Lihat Riwayat Pesanan
                </a>
                <?php endif; ?>
            </div>
            <div class="hero-stats">
                <div class="stat-item">
                    <div class="stat-number"><?= $totalProduk ?>+</div>
                    <div class="stat-label">Produk Kopi</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $totalAgen ?>+</div>
                    <div class="stat-label">Agen Aktif</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= $totalOrder ?>+</div>
                    <div class="stat-label">Transaksi</div>
                </div>
            </div>
        </div>
        <div class="hero-visual">
            <div class="hero-coffee-art">☕</div>
        </div>
    </div>
</section>

<!-- PRODUK UNGGULAN -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">✦ Pilihan Terbaik</div>
            <h2 class="section-title">Produk Unggulan</h2>
            <p class="section-subtitle">Kopi premium pilihan dengan kualitas terjamin dari agen terpercaya kami</p>
        </div>
        <div class="products-grid">
            <?php if ($produkUnggulan && $produkUnggulan->num_rows > 0):
                while ($p = $produkUnggulan->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-img">
                    <?php if ($p['gambar_produk'] && file_exists("uploads/produk/" . $p['gambar_produk'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/produk/<?= htmlspecialchars($p['gambar_produk']) ?>" alt="<?= htmlspecialchars($p['nama_kopi']) ?>">
                    <?php else: ?>
                        ☕
                    <?php endif; ?>
                    <span class="product-badge <?= strtolower($p['jenis_kopi']) === 'robusta' ? 'robusta' : '' ?>">
                        <?= htmlspecialchars($p['jenis_kopi']) ?>
                    </span>
                </div>
                <div class="product-body">
                    <div class="product-meta">
                        <span class="product-type"><?= htmlspecialchars($p['jenis_kopi']) ?></span>
                    </div>
                    <div class="product-name"><?= htmlspecialchars($p['nama_kopi']) ?></div>
                    <p class="product-desc"><?= htmlspecialchars($p['deskripsi']) ?></p>
                    <div class="product-footer">
                        <div>
                            <div class="product-price"><?= formatRupiah($p['harga']) ?></div>
                            <div class="product-stock"><i class="fas fa-box"></i> Stok: <?= $p['stok'] ?></div>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/detail.php?id=<?= $p['id_produk'] ?>" class="btn-detail">
                        <i class="fas fa-eye"></i> Lihat Detail
                    </a>
                </div>
            </div>
            <?php endwhile; else: ?>
            <div class="empty-state" style="grid-column:1/-1">
                <div class="empty-icon">☕</div>
                <h3>Belum ada produk</h3>
            </div>
            <?php endif; ?>
        </div>
        <div style="text-align:center;margin-top:40px">
            <a href="<?= BASE_URL ?>/katalog.php" class="hero-btn-primary" style="display:inline-flex">
                <i class="fas fa-th-large"></i> Lihat Semua Produk
            </a>
        </div>
    </div>
</section>

<!-- PESANAN PEMBELI -->
<?php if (isLoggedIn() && getRole() === 'pembeli'):
    $id_user = (int)$_SESSION['id_user'];
    $recentOrders = $koneksi->query("
        SELECT t.*, p.nama_kopi, p.gambar_produk, p.jenis_kopi
        FROM transaksi t
        JOIN produk p ON t.id_produk = p.id_produk
        WHERE t.id_user = $id_user
        ORDER BY t.tgl_transaksi DESC
        LIMIT 3
    ");
    $totalOrderCount = $koneksi->query("SELECT COUNT(*) AS c FROM transaksi WHERE id_user = $id_user")->fetch_assoc()['c'];
?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">📦 Akun Saya</div>
            <h2 class="section-title">Pesanan Terbaru Anda</h2>
            <p class="section-subtitle">Pantau status pesanan dan riwayat transaksi Anda</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:30px">
            <div class="admin-card" style="text-align:center;padding:30px">
                <div style="font-size:2.5rem;margin-bottom:12px">📦</div>
                <div style="font-size:2rem;color:var(--gold);font-weight:700"><?= $totalOrderCount ?></div>
                <div style="color:var(--text-light);font-size:.95rem">Total Pesanan</div>
            </div>
            <div class="admin-card" style="text-align:center;padding:30px">
                <div style="font-size:2.5rem;margin-bottom:12px">⏳</div>
                <div style="font-size:2rem;color:var(--gold);font-weight:700"><?= $koneksi->query("SELECT COUNT(*) AS c FROM transaksi WHERE id_user = $id_user AND status_pesanan = 'pending'")->fetch_assoc()['c'] ?></div>
                <div style="color:var(--text-light);font-size:.95rem">Menunggu Konfirmasi</div>
            </div>
            <div class="admin-card" style="text-align:center;padding:30px">
                <div style="font-size:2.5rem;margin-bottom:12px">✓</div>
                <div style="font-size:2rem;color:var(--gold);font-weight:700"><?= $koneksi->query("SELECT COUNT(*) AS c FROM transaksi WHERE id_user = $id_user AND status_pesanan IN ('dibayar','dikirim')")->fetch_assoc()['c'] ?></div>
                <div style="color:var(--text-light);font-size:.95rem">Selesai</div>
            </div>
        </div>
        
        <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
        <div style="background:var(--cream-light);border-radius:12px;padding:24px;margin-bottom:24px">
            <h3 style="margin-top:0;margin-bottom:20px;color:var(--brown-dark)">Pesanan Terbaru</h3>
            <div style="display:grid;gap:12px">
                <?php while ($order = $recentOrders->fetch_assoc()): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:white;border-radius:8px;border-left:3px solid var(--gold)">
                    <div style="flex:1">
                        <div style="font-weight:600;color:var(--brown-dark)"><?= htmlspecialchars($order['nama_kopi']) ?></div>
                        <div style="font-size:.85rem;color:var(--text-light)">
                            <?= date('d M Y', strtotime($order['tgl_transaksi'])) ?> • <?= $order['jumlah_beli'] ?> pcs
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:.85rem;color:var(--text-light);margin-bottom:4px">Status</div>
                        <span class="badge badge-<?= $order['status_pesanan'] ?>" style="font-size:.85rem">
                            <?= ucfirst($order['status_pesanan']) ?>
                        </span>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div style="text-align:center">
            <a href="<?= BASE_URL ?>/riwayat.php" class="hero-btn-primary" style="display:inline-flex">
                <i class="fas fa-receipt"></i> Lihat Semua Pesanan
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FITUR -->
<section class="section section-dark">
    <div class="container">
        <div class="section-header">
            <div class="section-tag">Mengapa Kami</div>
            <h2 class="section-title">Keunggulan Kopi Nusantara</h2>
            <p class="section-subtitle">Platform terpercaya untuk jual beli kopi premium Indonesia</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🌿</div>
                <h3>100% Kopi Asli</h3>
                <p>Semua produk terverifikasi langsung dari petani dan agen terpercaya seluruh Nusantara.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🚚</div>
                <h3>Pengiriman Terjamin</h3>
                <p>Pesanan dikemas dengan teliti dan dikirim menggunakan jasa ekspedisi terpercaya.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💎</div>
                <h3>Kualitas Premium</h3>
                <p>Hanya kopi berkualitas tinggi dengan standar rasa terbaik yang tersedia di platform kami.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Transaksi Aman</h3>
                <p>Sistem keamanan berlapis melindungi setiap transaksi dan data pribadi Anda.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
