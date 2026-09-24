<?php
require_once 'includes/auth.php';
require_once 'includes/koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: " . BASE_URL . "/katalog.php"); exit; }

$sql  = "SELECT p.*, u.nama_lengkap AS nama_agen, u.no_hp AS hp_agen, u.alamat AS alamat_agen
         FROM produk p LEFT JOIN users u ON p.id_agen = u.id_user
         WHERE p.id_produk = $id";
$res  = $koneksi->query($sql);
$prod = $res ? $res->fetch_assoc() : null;
if (!$prod) { header("Location: " . BASE_URL . "/katalog.php"); exit; }

$pageTitle = htmlspecialchars($prod['nama_kopi']) . ' – Kopi Nusantara';

// Tambah ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_keranjang'])) {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/login.php?redirect=" . urlencode(BASE_URL . "/detail.php?id=$id"));
        exit;
    }
    $qty = max(1, min((int)$_POST['qty'], $prod['stok']));
    if (!isset($_SESSION['keranjang'])) $_SESSION['keranjang'] = [];

    $found = false;
    foreach ($_SESSION['keranjang'] as &$item) {
        if ($item['id_produk'] == $id) {
            $item['qty'] = min($item['qty'] + $qty, $prod['stok']);
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['keranjang'][] = [
            'id_produk'  => $prod['id_produk'],
            'nama_kopi'  => $prod['nama_kopi'],
            'jenis_kopi' => $prod['jenis_kopi'],
            'harga'      => $prod['harga'],
            'gambar'     => $prod['gambar_produk'],
            'qty'        => $qty,
            'stok'       => $prod['stok'],
        ];
    }
    header("Location: " . BASE_URL . "/keranjang.php?success=" . urlencode("Produk berhasil ditambahkan ke keranjang!"));
    exit;
}

include 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="breadcrumb-container">
        <a href="<?= BASE_URL ?>/index.php">Beranda</a>
        <i class="fas fa-chevron-right"></i>
        <a href="<?= BASE_URL ?>/katalog.php">Katalog</a>
        <i class="fas fa-chevron-right"></i>
        <span><?= htmlspecialchars($prod['nama_kopi']) ?></span>
    </div>
</div>

<div class="detail-container">
    <div class="detail-img-box">
        <?php if ($prod['gambar_produk'] && file_exists(__DIR__ . "/uploads/produk/" . $prod['gambar_produk'])): ?>
            <img src="<?= BASE_URL ?>/uploads/produk/<?= htmlspecialchars($prod['gambar_produk']) ?>" alt="<?= htmlspecialchars($prod['nama_kopi']) ?>">
        <?php else: ?>
            ☕
        <?php endif; ?>
    </div>

    <div class="detail-info">
        <div class="detail-tag">
            <i class="fas fa-leaf"></i> <?= htmlspecialchars($prod['jenis_kopi']) ?>
        </div>
        <h1 class="detail-name"><?= htmlspecialchars($prod['nama_kopi']) ?></h1>
        <div class="detail-price"><?= formatRupiah($prod['harga']) ?></div>

        <div class="detail-meta">
            <div class="detail-meta-item">
                <i class="fas fa-box"></i>
                <span>Stok: <strong><?= $prod['stok'] ?> pcs</strong></span>
            </div>
            <div class="detail-meta-item">
                <i class="fas fa-user-tie"></i>
                <span>Agen: <strong><?= htmlspecialchars($prod['nama_agen'] ?? '-') ?></strong></span>
            </div>
            <div class="detail-meta-item">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= htmlspecialchars($prod['alamat_agen'] ?? '-') ?></span>
            </div>
        </div>

        <div class="detail-desc">
            <?= nl2br(htmlspecialchars($prod['deskripsi'])) ?>
        </div>

        <?php if ($prod['stok'] > 0): ?>
        <form method="POST">
            <div class="qty-wrap">
                <label>Jumlah:</label>
                <div class="qty-control">
                    <button type="button" class="qty-btn" data-action="minus">−</button>
                    <input type="number" name="qty" class="qty-input" value="1" min="1" max="<?= $prod['stok'] ?>">
                    <button type="button" class="qty-btn" data-action="plus">+</button>
                </div>
                <span style="font-size:.85rem;color:var(--text-light)">Maks. <?= $prod['stok'] ?></span>
            </div>
            <button type="submit" name="tambah_keranjang" class="btn-detail btn-gold" style="font-size:1rem;padding:14px">
                <i class="fas fa-shopping-bag"></i> Tambah ke Keranjang
            </button>
        </form>
        <?php else: ?>
        <div class="alert alert-error"><i class="fas fa-times-circle"></i> Stok habis</div>
        <?php endif; ?>

        <div style="margin-top:24px">
            <a href="<?= BASE_URL ?>/katalog.php" style="color:var(--text-light);font-size:.88rem;display:inline-flex;align-items:center;gap:8px">
                <i class="fas fa-arrow-left"></i> Kembali ke Katalog
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
