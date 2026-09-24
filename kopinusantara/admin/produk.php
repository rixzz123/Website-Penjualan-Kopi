<?php
require_once '../includes/auth.php';
require_once '../includes/koneksi.php';
requireAgen();

$pageTitle = 'Kelola Produk – Kopi Nusantara';
$id_agen   = (int)$_SESSION['id_user'];
$ownerFilter = "id_agen = $id_agen OR id_agen = 0 OR id_agen IS NULL";
$msg = $err = '';

// ── DELETE
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    // Ambil gambar dulu
    $g = $koneksi->query("SELECT gambar_produk FROM produk WHERE id_produk = $del_id AND ($ownerFilter)")->fetch_assoc();
    if ($g) {
        if ($g['gambar_produk'] && file_exists("../uploads/produk/" . $g['gambar_produk'])) {
            unlink("../uploads/produk/" . $g['gambar_produk']);
        }
        $koneksi->query("DELETE FROM produk WHERE id_produk = $del_id AND ($ownerFilter)");
        $msg = "Produk berhasil dihapus.";
    }
}

// ── CREATE / UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode       = $_POST['mode'] ?? 'create';
    $nama_kopi  = trim($_POST['nama_kopi'] ?? '');
    $jenis_kopi = trim($_POST['jenis_kopi'] ?? '');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');
    $harga      = (int)$_POST['harga'];
    $stok       = (int)$_POST['stok'];
    $edit_id    = (int)($_POST['edit_id'] ?? 0);

    if (empty($nama_kopi) || empty($jenis_kopi) || $harga <= 0) {
        $err = 'Nama, jenis kopi, dan harga wajib diisi.';
    } else {
        // Handle upload gambar
        $gambar = $_POST['gambar_lama'] ?? '';
        if (!empty($_FILES['gambar_produk']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext     = strtolower(pathinfo($_FILES['gambar_produk']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $err = 'Format gambar tidak didukung.';
            } else {
                $filename = uniqid('kopi_') . '.' . $ext;
                $target   = "../uploads/produk/" . $filename;
                if (move_uploaded_file($_FILES['gambar_produk']['tmp_name'], $target)) {
                    // Hapus gambar lama jika update
                    if ($mode === 'edit' && !empty($_POST['gambar_lama']) && file_exists("../uploads/produk/" . $_POST['gambar_lama'])) {
                        unlink("../uploads/produk/" . $_POST['gambar_lama']);
                    }
                    $gambar = $filename;
                } else {
                    $err = 'Gagal mengupload gambar.';
                }
            }
        }

        if (empty($err)) {
            if ($mode === 'edit' && $edit_id) {
                $stmt = $koneksi->prepare("UPDATE produk SET nama_kopi=?, jenis_kopi=?, deskripsi=?, harga=?, stok=?, gambar_produk=? WHERE id_produk=? AND ($ownerFilter)");
                $stmt->bind_param("sssiiisi", $nama_kopi, $jenis_kopi, $deskripsi, $harga, $stok, $gambar, $edit_id);
                $stmt->execute();
                $msg = "Produk berhasil diperbarui.";
            } else {
                $stmt = $koneksi->prepare("INSERT INTO produk (id_agen, nama_kopi, jenis_kopi, deskripsi, harga, stok, gambar_produk) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param("isssiis", $id_agen, $nama_kopi, $jenis_kopi, $deskripsi, $harga, $stok, $gambar);
                $stmt->execute();
                $msg = "Produk baru berhasil ditambahkan.";
            }
            $stmt->close();
        }
    }
}

// ── EDIT MODE
$editData = null;
if (isset($_GET['edit'])) {
    $eid     = (int)$_GET['edit'];
    $editRes = $koneksi->query("SELECT * FROM produk WHERE id_produk = $eid AND ($ownerFilter)");
    $editData = $editRes ? $editRes->fetch_assoc() : null;
}

// ── LIST
$produkList = $koneksi->query("SELECT * FROM produk WHERE ($ownerFilter) ORDER BY id_produk DESC");
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
            <h1><i class="fas fa-coffee" style="color:var(--gold)"></i> Kelola Produk</h1>
        </div>

        <div class="admin-content">
            <?php if ($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
            <?php if ($err): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?></div><?php endif; ?>

            <!-- FORM -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><?= $editData ? '<i class="fas fa-edit" style="color:var(--gold)"></i> Edit Produk' : '<i class="fas fa-plus-circle" style="color:var(--gold)"></i> Tambah Produk Baru' ?></h3>
                    <?php if ($editData): ?>
                    <a href="<?= BASE_URL ?>/admin/produk.php" style="font-size:.85rem;color:var(--text-light)"><i class="fas fa-times"></i> Batal Edit</a>
                    <?php endif; ?>
                </div>
                <div class="admin-card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="mode"       value="<?= $editData ? 'edit' : 'create' ?>">
                        <input type="hidden" name="edit_id"    value="<?= $editData['id_produk'] ?? '' ?>">
                        <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($editData['gambar_produk'] ?? '') ?>">

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                            <div class="form-group">
                                <label>Nama Kopi *</label>
                                <input type="text" name="nama_kopi" class="form-control" value="<?= htmlspecialchars($editData['nama_kopi'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Jenis Kopi *</label>
                                <select name="jenis_kopi" class="form-control" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <?php foreach (['Arabica','Robusta','Liberika','Excelsa'] as $j): ?>
                                    <option value="<?= $j ?>" <?= ($editData['jenis_kopi'] ?? '') === $j ? 'selected' : '' ?>><?= $j ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Harga (Rp) *</label>
                                <input type="number" name="harga" class="form-control" value="<?= $editData['harga'] ?? '' ?>" min="1000" required>
                            </div>
                            <div class="form-group">
                                <label>Stok</label>
                                <input type="number" name="stok" class="form-control" value="<?= $editData['stok'] ?? 0 ?>" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($editData['deskripsi'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Gambar Produk <?= $editData ? '(kosongkan jika tidak ingin mengubah)' : '' ?></label>
                            <input type="file" id="gambar_produk" name="gambar_produk" class="form-control" accept="image/*">
                            <?php if (!empty($editData['gambar_produk']) && file_exists("../uploads/produk/" . $editData['gambar_produk'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/produk/<?= htmlspecialchars($editData['gambar_produk']) ?>" id="imgPreview" style="max-width:180px;max-height:140px;margin-top:12px;border-radius:8px;object-fit:cover;border:2px solid var(--cream-dark)">
                            <?php endif; ?>

                        </div>
                        <button type="submit" class="btn-sm-gold" style="padding:12px 28px">
                            <i class="fas fa-save"></i> <?= $editData ? 'Simpan Perubahan' : 'Tambah Produk' ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- TABLE -->
            <div class="admin-card">
                <div class="admin-card-header">
                    <h3><i class="fas fa-list" style="color:var(--gold)"></i> Daftar Produk</h3>
                    <span style="font-size:.85rem;color:var(--text-light)"><?= $produkList->num_rows ?> produk</span>
                </div>
                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Gambar</th>
                                <th>Nama Kopi</th>
                                <th>Jenis</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($produkList && $produkList->num_rows > 0):
                                while ($p = $produkList->fetch_assoc()): ?>
                            <tr>
                                <td><?= $p['id_produk'] ?></td>
                                <td>
                                    <div style="width:50px;height:50px;background:var(--cream-mid);border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:1.4rem">
                                        <?php if ($p['gambar_produk'] && file_exists("../uploads/produk/" . $p['gambar_produk'])): ?>
                                            <img src="<?= BASE_URL ?>/uploads/produk/<?= htmlspecialchars($p['gambar_produk']) ?>" style="width:100%;height:100%;object-fit:cover">
                                        <?php else: ?>☕<?php endif; ?>
                                    </div>
                                </td>
                                <td style="font-weight:600"><?= htmlspecialchars($p['nama_kopi']) ?></td>
                                <td><span class="badge badge-<?= strtolower($p['jenis_kopi']) ?>"><?= $p['jenis_kopi'] ?></span></td>
                                <td style="font-weight:700;color:var(--gold)"><?= formatRupiah($p['harga']) ?></td>
                                <td><?= $p['stok'] ?></td>
                                <td>
                                    <div style="display:flex;gap:8px">
                                        <a href="<?= BASE_URL ?>/admin/produk.php?edit=<?= $p['id_produk'] ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?= BASE_URL ?>/admin/produk.php?delete=<?= $p['id_produk'] ?>" class="btn-action btn-delete confirm-delete">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-light)">Belum ada produk</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
