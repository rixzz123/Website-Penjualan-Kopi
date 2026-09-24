<?php
require_once 'includes/auth.php';
require_once 'includes/koneksi.php';

$pageTitle = 'Katalog Produk – Kopi Nusantara';

$search = isset($_GET['search']) ? $koneksi->real_escape_string($_GET['search']) : '';
$jenis  = isset($_GET['jenis'])  ? $koneksi->real_escape_string($_GET['jenis'])  : '';
$sort   = isset($_GET['sort'])   ? $_GET['sort'] : 'terbaru';

$where = "WHERE 1=1";
if ($search) $where .= " AND (p.nama_kopi LIKE '%$search%' OR p.deskripsi LIKE '%$search%')";
if ($jenis)  $where .= " AND p.jenis_kopi = '$jenis'";

$orderBy = match($sort) {
    'termurah'  => "p.harga ASC",
    'termahal'  => "p.harga DESC",
    'terbanyak' => "p.stok DESC",
    default     => "p.id_produk DESC"
};

$sql = "SELECT p.*, u.nama_lengkap AS nama_agen FROM produk p 
        LEFT JOIN users u ON p.id_agen = u.id_user 
        $where ORDER BY $orderBy";
$produkList = $koneksi->query($sql);

include 'includes/header.php';
?>

<div class="catalog-header">
    <h1>Katalog Kopi Nusantara</h1>
    <p>Temukan berbagai pilihan kopi premium dari seluruh penjuru Indonesia</p>
</div>

<div style="max-width:1200px;margin:0 auto">
    <form method="GET" class="catalog-filters">
        <div class="filter-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="search" class="filter-input" placeholder="Cari nama kopi..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="jenis" class="filter-select">
            <option value="">Semua Jenis</option>
            <option value="Arabica"  <?= $jenis === 'Arabica'  ? 'selected' : '' ?>>Arabica</option>
            <option value="Robusta"  <?= $jenis === 'Robusta'  ? 'selected' : '' ?>>Robusta</option>
            <option value="Liberika" <?= $jenis === 'Liberika' ? 'selected' : '' ?>>Liberika</option>
        </select>
        <select name="sort" class="filter-select">
            <option value="terbaru"  <?= $sort === 'terbaru'  ? 'selected' : '' ?>>Terbaru</option>
            <option value="termurah" <?= $sort === 'termurah' ? 'selected' : '' ?>>Harga Termurah</option>
            <option value="termahal" <?= $sort === 'termahal' ? 'selected' : '' ?>>Harga Termahal</option>
            <option value="terbanyak"<?= $sort === 'terbanyak'? 'selected' : '' ?>>Stok Terbanyak</option>
        </select>
        <button type="submit" class="btn-sm-gold">
            <i class="fas fa-filter"></i> Filter
        </button>
        <?php if ($search || $jenis || $sort !== 'terbaru'): ?>
        <a href="<?= BASE_URL ?>/katalog.php" style="color:var(--text-light);font-size:.88rem;padding:10px">Reset</a>
        <?php endif; ?>
    </form>
</div>

<section class="section" style="padding-top:32px">
    <div class="container">
        <?php if ($produkList && $produkList->num_rows > 0): ?>
        <p style="color:var(--text-light);font-size:.88rem;margin-bottom:24px">
            Menampilkan <strong><?= $produkList->num_rows ?></strong> produk
        </p>
        <div class="products-grid">
            <?php while ($p = $produkList->fetch_assoc()): ?>
            <div class="product-card">
                <div class="product-img">
                    <?php if ($p['gambar_produk'] && file_exists(__DIR__ . "/uploads/produk/" . $p['gambar_produk'])): ?>
                        <img src="<?= BASE_URL ?>/uploads/produk/<?= htmlspecialchars($p['gambar_produk']) ?>" alt="<?= htmlspecialchars($p['nama_kopi']) ?>">
                    <?php else: ?>
                        <div class="product-placeholder">Foto belum tersedia</div>
                    <?php endif; ?>
                    <span class="product-badge <?= strtolower($p['jenis_kopi']) === 'robusta' ? 'robusta' : '' ?>">
                        <?= htmlspecialchars($p['jenis_kopi']) ?>
                    </span>
                </div>
                <div class="product-body">
                    <div class="product-meta">
                        <span class="product-type"><?= htmlspecialchars($p['jenis_kopi']) ?></span>
                        <?php if ($p['stok'] <= 10): ?>
                            <span style="font-size:.72rem;color:var(--error);font-weight:600">⚠ Stok Terbatas</span>
                        <?php endif; ?>
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
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>Produk tidak ditemukan</h3>
            <p>Coba ubah kata kunci atau filter pencarian Anda.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
