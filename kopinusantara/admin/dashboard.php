<?php
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
requireAgen();

$pageTitle = 'Dashboard – Kopi Nusantara';

$totalProduk     = $koneksi->query("SELECT COUNT(*) AS c FROM produk WHERE id_agen = " . (int)$_SESSION['id_user'])->fetch_assoc()['c'];
$totalTransaksi  = $koneksi->query("SELECT COUNT(*) AS c FROM transaksi t JOIN produk p ON t.id_produk = p.id_produk WHERE (p.id_agen = " . (int)$_SESSION['id_user'] . " OR p.id_agen = 0 OR p.id_agen IS NULL)")->fetch_assoc()['c'];
$totalRevenue    = $koneksi->query("SELECT COALESCE(SUM(t.total_harga),0) AS r FROM transaksi t JOIN produk p ON t.id_produk = p.id_produk WHERE (p.id_agen = " . (int)$_SESSION['id_user'] . " OR p.id_agen = 0 OR p.id_agen IS NULL)")->fetch_assoc()['r'];
$totalPembeli    = $koneksi->query("SELECT COUNT(DISTINCT id_user) AS c FROM users WHERE role='pembeli'")->fetch_assoc()['c'];

$recentTrx = $koneksi->query("SELECT t.*, u.nama_lengkap, p.nama_kopi 
    FROM transaksi t 
    JOIN users u ON t.id_user = u.id_user 
    JOIN produk p ON t.id_produk = p.id_produk 
    WHERE (p.id_agen = " . (int)$_SESSION['id_user'] . " OR p.id_agen = 0 OR p.id_agen IS NULL)
    ORDER BY t.id_transaksi DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-topbar">
            <h1><i class="fas fa-chart-line" style="color:var(--gold)"></i> Dashboard</h1>
            <div style="font-size:.88rem;color:var(--text-light)">
                Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
            </div>
        </div>

        <div class="admin-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon brown"><i class="fas fa-coffee" style="color:var(--brown-mid)"></i></div>
                    <div>
                        <div class="stat-card-value"><?= $totalProduk ?></div>
                        <div class="stat-card-label">Produk Saya</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon gold"><i class="fas fa-receipt" style="color:var(--gold)"></i></div>
                    <div>
                        <div class="stat-card-value"><?= $totalTransaksi ?></div>
                        <div class="stat-card-label">Total Transaksi</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon green"><i class="fas fa-money-bill-wave" style="color:var(--success)"></i></div>
                    <div>
                        <div class="stat-card-value" style="font-size:1.3rem"><?= formatRupiah($totalRevenue) ?></div>
                        <div class="stat-card-label">Total Pendapatan</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-icon brown"><i class="fas fa-users" style="color:var(--brown-light)"></i></div>
                    <div>
                        <div class="stat-card-value"><?= $totalPembeli ?></div>
                        <div class="stat-card-label">Total Pembeli</div>
                    </div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><i class="fas fa-clock" style="color:var(--gold)"></i> Transaksi Terbaru</h3>
                    <a href="<?= BASE_URL ?>/admin/transaksi.php" style="font-size:.85rem;color:var(--gold)">Lihat Semua →</a>
                </div>
                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Pembeli</th>
                                <th>Produk</th>
                                <th>Tgl</th>
                                <th>Jumlah</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recentTrx && $recentTrx->num_rows > 0):
                                while ($r = $recentTrx->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $r['id_transaksi'] ?></td>
                                <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
                                <td><?= htmlspecialchars($r['nama_kopi']) ?></td>
                                <td><?= $r['tgl_transaksi'] ?></td>
                                <td><?= $r['jumlah_beli'] ?> pcs</td>
                                <td style="font-weight:700;color:var(--gold)"><?= formatRupiah($r['total_harga']) ?></td>
                                <td><span class="badge badge-<?= $r['status_pesanan'] ?>"><?= ucfirst($r['status_pesanan']) ?></span></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="7" style="text-align:center;color:var(--text-light);padding:40px">Belum ada transaksi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
