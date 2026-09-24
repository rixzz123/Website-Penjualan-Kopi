<?php
require_once 'includes/auth.php';
require_once 'includes/koneksi.php';
requireLogin();

// Redirect jika user bukan pembeli
if (getRole() !== 'pembeli') {
    header("Location: " . BASE_URL . "/index.php");
    exit;
}

$pageTitle = 'Riwayat Transaksi – Kopi Nusantara';
$id_user   = (int)$_SESSION['id_user'];
$filter_status = $_GET['filter'] ?? '';

// Build where clause
$where = "WHERE t.id_user = $id_user";
if ($filter_status) {
    $where .= " AND t.status_pesanan = '" . $koneksi->real_escape_string($filter_status) . "'";
}

// Get transaction list
$transaksiList = $koneksi->query("
    SELECT t.*, p.nama_kopi, p.jenis_kopi, p.harga, p.gambar_produk,
           u.nama_lengkap AS nama_agen, u.email AS email_agen
    FROM transaksi t
    JOIN produk p ON t.id_produk = p.id_produk
    LEFT JOIN users u ON p.id_agen = u.id_user
    $where
    ORDER BY t.tgl_transaksi DESC, t.id_transaksi DESC
");

// Get total spending
$totalSpending = $koneksi->query("SELECT COALESCE(SUM(t.total_harga),0) AS total FROM transaksi t WHERE t.id_user = $id_user")->fetch_assoc()['total'];

include 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="breadcrumb-container">
        <a href="<?= BASE_URL ?>/index.php">Beranda</a>
        <i class="fas fa-chevron-right"></i>
        <span>Riwayat Transaksi</span>
    </div>
</div>

<div style="max-width:1200px;margin:0 auto;padding:32px 24px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:32px;flex-wrap:wrap;gap:20px">
        <div>
            <h1 style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--brown-dark);margin:0">
                <i class="fas fa-receipt" style="color:var(--gold);margin-right:12px"></i>Riwayat Transaksi
            </h1>
            <p style="color:var(--text-light);margin-top:8px;font-size:.95rem">Pantau status pesanan Anda</p>
        </div>
        <div style="text-align:right">
            <div style="font-size:.85rem;color:var(--text-light);margin-bottom:4px">Total Pengeluaran</div>
            <div style="font-family:'Playfair Display',serif;font-size:1.5rem;color:var(--gold);font-weight:700">
                <?= formatRupiah($totalSpending) ?>
            </div>
        </div>
    </div>

    <!-- Filter Status -->
    <div style="margin-bottom:24px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
        <span style="font-size:.88rem;color:var(--text-light);font-weight:500">Filter Status:</span>
        <?php foreach ([''=>'Semua','pending'=>'Pending','dibayar'=>'Dibayar','dikirim'=>'Dikirim'] as $val => $lbl): ?>
        <a href="<?= BASE_URL ?>/riwayat.php?filter=<?= $val ?>" 
           class="btn-action <?= $filter_status === $val ? 'btn-sm-gold' : 'btn-edit' ?>" style="text-decoration:none;font-size:.85rem">
            <?= $lbl ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Transactions List -->
    <?php if ($transaksiList && $transaksiList->num_rows > 0): ?>
        <div style="display:grid;gap:16px">
            <?php while ($t = $transaksiList->fetch_assoc()): ?>
            <div class="admin-card" style="border-left:4px solid var(--gold);padding:0">
                <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;border-bottom:1px solid var(--cream-dark)">
                    <!-- Product Info -->
                    <div style="display:flex;gap:16px">
                        <div style="width:80px;height:80px;background:var(--cream-mid);border-radius:8px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.8rem">
                            <?php if ($t['gambar_produk'] && file_exists(__DIR__ . "/uploads/produk/" . $t['gambar_produk'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/produk/<?= htmlspecialchars($t['gambar_produk']) ?>" style="width:100%;height:100%;object-fit:cover" alt="">
                            <?php else: ?>
                                ☕
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:.95rem;color:var(--brown-dark);margin-bottom:4px">
                                <?= htmlspecialchars($t['nama_kopi']) ?>
                            </div>
                            <div style="font-size:.82rem;color:var(--text-light);margin-bottom:8px">
                                <span class="badge badge-<?= strtolower($t['jenis_kopi']) ?>"><?= $t['jenis_kopi'] ?></span>
                            </div>
                            <div style="font-size:.82rem;color:var(--text-light)">
                                Dari: <strong><?= htmlspecialchars($t['nama_agen'] ?? 'Kopi Nusantara') ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Info -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div>
                            <div style="font-size:.75rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Tanggal</div>
                            <div style="font-weight:600;font-size:.9rem"><?= date('d M Y', strtotime($t['tgl_transaksi'])) ?></div>
                        </div>
                        <div>
                            <div style="font-size:.75rem;color:var(--text-light);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Jumlah</div>
                            <div style="font-weight:600;font-size:.9rem"><?= $t['jumlah_beli'] ?> pcs</div>
                        </div>
                    </div>
                </div>

                <!-- Status & Price -->
                <div style="padding:20px;display:grid;grid-template-columns:auto 1fr auto;gap:20px;align-items:center">
                    <div>
                        <span class="badge badge-<?= $t['status_pesanan'] ?>" style="padding:8px 16px;font-weight:600">
                            <?php 
                            $status_text = [
                                'pending' => '⏳ Pending',
                                'dibayar' => '✓ Dibayar',
                                'dikirim' => '📦 Dikirim'
                            ];
                            echo $status_text[$t['status_pesanan']] ?? ucfirst($t['status_pesanan']);
                            ?>
                        </span>
                    </div>
                    <div></div>
                    <div style="text-align:right">
                        <div style="font-size:.82rem;color:var(--text-light);margin-bottom:4px">Total</div>
                        <div style="font-family:'Playfair Display',serif;font-size:1.2rem;color:var(--gold);font-weight:700">
                            <?= formatRupiah($t['total_harga']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state" style="padding:60px 24px">
            <div class="empty-icon">📦</div>
            <h3>Belum ada transaksi</h3>
            <p>Mulai belanja kopi favorit Anda sekarang!</p>
            <a href="<?= BASE_URL ?>/katalog.php" class="hero-btn-primary" style="display:inline-flex;margin-top:20px">
                <i class="fas fa-coffee"></i> Belanja Sekarang
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
