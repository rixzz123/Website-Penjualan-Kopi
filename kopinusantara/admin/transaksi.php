<?php
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
requireAgen();

$pageTitle = 'Data Transaksi – Kopi Nusantara';
$id_agen   = (int)$_SESSION['id_user'];

// Update status
if (isset($_GET['status']) && isset($_GET['id'])) {
    $new_status = $_GET['status'];
    $trx_id     = (int)$_GET['id'];
    $allowed    = ['pending','dibayar','dikirim'];
    if (in_array($new_status, $allowed)) {
        $koneksi->query("UPDATE transaksi SET status_pesanan='$new_status' WHERE id_transaksi=$trx_id");
    }
    header("Location: " . BASE_URL . "/admin/transaksi.php?msg=Status+diperbarui");
    exit;
}

$filter_status = $_GET['filter'] ?? '';
$where = "WHERE (p.id_agen = $id_agen OR p.id_agen = 0 OR p.id_agen IS NULL)";
if ($filter_status) $where .= " AND t.status_pesanan = '" . $koneksi->real_escape_string($filter_status) . "'";

$transaksiList = $koneksi->query("
    SELECT t.*, u.nama_lengkap AS nama_pembeli, u.email AS email_pembeli, 
           p.nama_kopi, p.jenis_kopi, p.harga
    FROM transaksi t
    JOIN users u ON t.id_user = u.id_user
    JOIN produk p ON t.id_produk = p.id_produk
    $where
    ORDER BY t.id_transaksi DESC
");

$totalPendapatan = $koneksi->query("SELECT COALESCE(SUM(t.total_harga),0) AS total FROM transaksi t JOIN produk p ON t.id_produk = p.id_produk WHERE (p.id_agen = $id_agen OR p.id_agen = 0 OR p.id_agen IS NULL)")->fetch_assoc()['total'];
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
            <h1><i class="fas fa-receipt" style="color:var(--gold)"></i> Data Transaksi</h1>
            <div style="font-family:'Playfair Display',serif;font-size:1.1rem;color:var(--gold)">
                Total: <?= formatRupiah($totalPendapatan) ?>
            </div>
        </div>

        <div class="admin-content">
            <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['msg']) ?></div>
            <?php endif; ?>

            <!-- Filter -->
            <div style="margin-bottom:20px;display:flex;gap:12px;flex-wrap:wrap;align-items:center">
                <span style="font-size:.88rem;color:var(--text-light)">Filter Status:</span>
                <?php foreach ([''=>'Semua','pending'=>'Pending','dibayar'=>'Dibayar','dikirim'=>'Dikirim'] as $val => $lbl): ?>
                <a href="<?= BASE_URL ?>/admin/transaksi.php?filter=<?= $val ?>" 
                   class="btn-action <?= $filter_status === $val ? 'btn-sm-gold' : 'btn-edit' ?>" style="text-decoration:none">
                    <?= $lbl ?>
                </a>
                <?php endforeach; ?>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <h3>Semua Transaksi</h3>
                    <span style="font-size:.85rem;color:var(--text-light)"><?= $transaksiList->num_rows ?> transaksi</span>
                </div>
                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Pembeli</th>
                                <th>Produk</th>
                                <th>Tgl Transaksi</th>
                                <th>Jml Beli</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Ubah Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($transaksiList && $transaksiList->num_rows > 0):
                                while ($t = $transaksiList->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $t['id_transaksi'] ?></td>
                                <td>
                                    <div style="font-weight:600;font-size:.9rem"><?= htmlspecialchars($t['nama_pembeli']) ?></div>
                                    <div style="font-size:.78rem;color:var(--text-light)"><?= htmlspecialchars($t['email_pembeli']) ?></div>
                                </td>
                                <td>
                                    <div style="font-weight:600;font-size:.9rem"><?= htmlspecialchars($t['nama_kopi']) ?></div>
                                    <div style="font-size:.78rem"><span class="badge badge-<?= strtolower($t['jenis_kopi']) ?>"><?= $t['jenis_kopi'] ?></span></div>
                                </td>
                                <td><?= $t['tgl_transaksi'] ?></td>
                                <td><?= $t['jumlah_beli'] ?> pcs</td>
                                <td style="font-weight:700;color:var(--gold)"><?= formatRupiah($t['total_harga']) ?></td>
                                <td><span class="badge badge-<?= $t['status_pesanan'] ?>"><?= ucfirst($t['status_pesanan']) ?></span></td>
                                <td>
                                    <select onchange="updateStatus(<?= $t['id_transaksi'] ?>, this.value)" style="padding:6px 10px;border:1.5px solid var(--cream-dark);border-radius:6px;font-size:.82rem;background:var(--white);cursor:pointer">
                                        <option value="pending"  <?= $t['status_pesanan']==='pending'  ? 'selected':'' ?>>Pending</option>
                                        <option value="dibayar"  <?= $t['status_pesanan']==='dibayar'  ? 'selected':'' ?>>Dibayar</option>
                                        <option value="dikirim"  <?= $t['status_pesanan']==='dikirim'  ? 'selected':'' ?>>Dikirim</option>
                                    </select>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-light)">Belum ada transaksi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function updateStatus(id, status) {
    if (confirm('Ubah status menjadi ' + status + '?')) {
        window.location.href = '<?= BASE_URL ?>/admin/transaksi.php?id=' + id + '&status=' + status;
    }
}
</script>
</body>
</html>
