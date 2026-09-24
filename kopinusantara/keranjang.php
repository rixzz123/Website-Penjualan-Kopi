<?php
require_once 'includes/auth.php';
require_once 'includes/koneksi.php';
requireLogin();

$pageTitle = 'Keranjang Belanja – Kopi Nusantara';

// Hapus item
if (isset($_GET['hapus'])) {
    $idx = (int)$_GET['hapus'];
    if (isset($_SESSION['keranjang'][$idx])) {
        array_splice($_SESSION['keranjang'], $idx, 1);
        $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
    }
    header("Location: " . BASE_URL . "/keranjang.php");
    exit;
}

// Update qty
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $i => $q) {
        if (isset($_SESSION['keranjang'][$i])) {
            $q = max(1, (int)$q);
            $_SESSION['keranjang'][$i]['qty'] = min($q, $_SESSION['keranjang'][$i]['stok']);
        }
    }
    header("Location: " . BASE_URL . "/keranjang.php");
    exit;
}

// Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (empty($_SESSION['keranjang'])) {
        header("Location: " . BASE_URL . "/keranjang.php");
        exit;
    }
    $id_user = $_SESSION['id_user'];
    $today   = date('Y-m-d');
    $success = true;

    foreach ($_SESSION['keranjang'] as $item) {
        $id_produk   = (int)$item['id_produk'];
        $jumlah_beli = (int)$item['qty'];
        $total_harga = $item['harga'] * $jumlah_beli;
        $status      = 'pending';

        $stmt = $koneksi->prepare("INSERT INTO transaksi (id_user, id_produk, tgl_transaksi, jumlah_beli, total_harga, status_pesanan) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("iisiis", $id_user, $id_produk, $today, $jumlah_beli, $total_harga, $status);
        if (!$stmt->execute()) {
            $success = false;
            $checkoutError = $stmt->error;
        } else {
            // Kurangi stok
            $koneksi->query("UPDATE produk SET stok = stok - $jumlah_beli WHERE id_produk = $id_produk AND stok >= $jumlah_beli");
        }
        $stmt->close();
    }

    if ($success) {
        unset($_SESSION['keranjang']);
        header("Location: " . BASE_URL . "/sukses.php");
        exit;
    } else {
        $err = 'Gagal memproses checkout. Silakan coba lagi.';
        if (!empty($checkoutError)) {
            $err .= ' (' . htmlspecialchars($checkoutError) . ')';
        }
    }
}

$keranjang   = $_SESSION['keranjang'] ?? [];
$totalHarga  = array_sum(array_map(fn($i) => $i['harga'] * $i['qty'], $keranjang));
$totalItem   = array_sum(array_column($keranjang, 'qty'));

include 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="breadcrumb-container">
        <a href="<?= BASE_URL ?>/index.php">Beranda</a>
        <i class="fas fa-chevron-right"></i>
        <span>Keranjang Belanja</span>
    </div>
</div>

<div style="max-width:1200px;margin:0 auto;padding:20px 24px 0">
    <h1 style="font-family:'Playfair Display',serif;font-size:1.8rem;color:var(--brown-dark)">
        <i class="fas fa-shopping-bag" style="color:var(--gold)"></i> Keranjang Belanja
    </h1>
</div>

<?php if (!empty($err)): ?>
<div class="alert alert-error" style="max-width:1200px;margin:0 auto 20px;padding:16px;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.06);background:rgba(255,230,230,.95);color:#a33;">
    <i class="fas fa-exclamation-circle" style="margin-right:8px"></i> <?= htmlspecialchars($err) ?>
</div>
<?php endif; ?>

<?php if (empty($keranjang)): ?>
<div class="empty-state" style="padding:100px 24px">
    <div class="empty-icon">🛒</div>
    <h3>Keranjang masih kosong</h3>
    <p>Yuk, mulai belanja kopi favoritmu!</p>
    <a href="<?= BASE_URL ?>/katalog.php" class="hero-btn-primary" style="display:inline-flex;margin-top:24px">
        <i class="fas fa-coffee"></i> Belanja Sekarang
    </a>
</div>
<?php else: ?>

<div class="cart-container">
    <div>
        <form method="POST" id="cartForm">
        <div class="cart-table">
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keranjang as $i => $item): ?>
                    <tr>
                        <td>
                            <div class="cart-product-info">
                                <div class="cart-img">
                                    <?php if ($item['gambar'] && file_exists("uploads/produk/" . $item['gambar'])): ?>
                                        <img src="<?= BASE_URL ?>/uploads/produk/<?= htmlspecialchars($item['gambar']) ?>" alt="">
                                    <?php else: ?>
                                        ☕
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="cart-product-name"><?= htmlspecialchars($item['nama_kopi']) ?></div>
                                    <div class="cart-product-type"><?= htmlspecialchars($item['jenis_kopi']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="cart-price"><?= formatRupiah($item['harga']) ?></td>
                        <td>
                            <div class="qty-control" style="width:fit-content">
                                <button type="button" class="qty-btn" data-action="minus" onclick="changeQty(this, <?= $i ?>)">−</button>
                                <input type="number" name="qty[<?= $i ?>]" class="qty-input" value="<?= $item['qty'] ?>" min="1" max="<?= $item['stok'] ?>" style="width:50px">
                                <button type="button" class="qty-btn" data-action="plus" onclick="changeQty(this, <?= $i ?>)">+</button>
                            </div>
                        </td>
                        <td class="cart-price"><?= formatRupiah($item['harga'] * $item['qty']) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/keranjang.php?hapus=<?= $i ?>" class="btn-remove" onclick="return confirm('Hapus item ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px;display:flex;gap:12px;flex-wrap:wrap">
            <button type="submit" name="update_cart" class="btn-action btn-edit">
                <i class="fas fa-sync"></i> Update Keranjang
            </button>
            <a href="<?= BASE_URL ?>/katalog.php" class="btn-action btn-edit" style="text-decoration:none">
                <i class="fas fa-arrow-left"></i> Lanjut Belanja
            </a>
        </div>
        </form>
    </div>

    <div class="cart-summary">
        <h3>Ringkasan Pesanan</h3>
        <div class="summary-row">
            <span>Total Item</span>
            <span><?= $totalItem ?> produk</span>
        </div>
        <div class="summary-row">
            <span>Subtotal</span>
            <span><?= formatRupiah($totalHarga) ?></span>
        </div>
        <div class="summary-row">
            <span>Biaya Kirim</span>
            <span style="color:var(--success)">Gratis</span>
        </div>
        <div class="summary-total">
            <span>Total Bayar</span>
            <span class="amount"><?= formatRupiah($totalHarga) ?></span>
        </div>
        <form method="POST" style="margin-top:24px">
            <button type="submit" name="checkout" class="btn-submit" onclick="return confirm('Konfirmasi checkout?')">
                <i class="fas fa-credit-card"></i> Checkout Sekarang
            </button>
        </form>
        <p style="text-align:center;font-size:.78rem;color:var(--text-light);margin-top:12px">
            <i class="fas fa-lock"></i> Transaksi aman & terpercaya
        </p>
    </div>
</div>
<?php endif; ?>

<script>
function changeQty(btn, idx) {
    const input = btn.closest('.qty-control').querySelector('input');
    let val = parseInt(input.value);
    const action = btn.dataset.action;
    if (action === 'plus') val++;
    if (action === 'minus' && val > 1) val--;
    const max = parseInt(input.max);
    if (val > max) val = max;
    input.value = val;
}
</script>

<?php include 'includes/footer.php'; ?>
