<?php
require_once 'includes/auth.php';
require_once 'includes/koneksi.php';

if (isLoggedIn()) { header("Location: " . BASE_URL . "/index.php"); exit; }

$pageTitle = 'Daftar Akun – Kopi Nusantara';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama_lengkap'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = trim($_POST['password'] ?? '');
    $pass2   = trim($_POST['konfirmasi'] ?? '');
    $role    = $_POST['role'] ?? 'pembeli';
    $no_hp   = trim($_POST['no_hp'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if (empty($nama) || empty($email) || empty($pass)) {
        $error = 'Nama, email, dan password wajib diisi.';
    } elseif ($pass !== $pass2) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($pass) < 5) {
        $error = 'Password minimal 5 karakter.';
    } elseif (!in_array($role, ['pembeli', 'agen'])) {
        $error = 'Role tidak valid.';
    } else {
        // Cek email duplikat
        $cek = $koneksi->prepare("SELECT id_user FROM users WHERE email = ?");
        $cek->bind_param("s", $email);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            $error = 'Email sudah terdaftar.';
        } else {
            $stmt = $koneksi->prepare("INSERT INTO users (nama_lengkap, email, password, role, no_hp, alamat) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("ssssss", $nama, $email, $pass, $role, $no_hp, $alamat);
            if ($stmt->execute()) {
                header("Location: " . BASE_URL . "/login.php?registered=1");
                exit;
            } else {
                $error = 'Terjadi kesalahan, silakan coba lagi.';
            }
            $stmt->close();
        }
        $cek->close();
    }
}
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

<div class="auth-page">
    <div class="auth-card" style="max-width:560px">
        <div class="auth-logo">☕ Kopi <span>Nusantara</span></div>
        <p class="auth-subtitle">Bergabung dengan marketplace kopi premium kami</p>
        <h2 class="auth-title">Buat Akun Baru</h2>

        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group" style="margin:0">
                    <label>Nama Lengkap *</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Anda" value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group" style="margin:0">
                    <label>No. HP</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top:16px">
                <label>Email *</label>
                <div class="input-icon-wrap">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group" style="margin:0">
                    <label>Password *</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Min. 5 karakter" required>
                    </div>
                </div>
                <div class="form-group" style="margin:0">
                    <label>Konfirmasi Password *</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password" required>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-top:16px">
                <label>Daftar Sebagai</label>
                <div style="display:flex;gap:16px;margin-top:8px">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:12px 20px;border:1.5px solid var(--cream-dark);border-radius:var(--radius-sm);flex:1;font-weight:400" id="lbl-pembeli">
                        <input type="radio" name="role" value="pembeli" <?= ($_POST['role'] ?? 'pembeli') === 'pembeli' ? 'checked' : '' ?> onchange="highlightRole()">
                        <i class="fas fa-user" style="color:var(--gold)"></i> Pembeli
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:12px 20px;border:1.5px solid var(--cream-dark);border-radius:var(--radius-sm);flex:1;font-weight:400" id="lbl-agen">
                        <input type="radio" name="role" value="agen" <?= ($_POST['role'] ?? '') === 'agen' ? 'checked' : '' ?> onchange="highlightRole()">
                        <i class="fas fa-store" style="color:var(--brown-mid)"></i> Agen
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" class="form-control" placeholder="Alamat lengkap Anda" rows="3"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="<?= BASE_URL ?>/login.php">Masuk di sini</a>
        </div>
    </div>
</div>

<script>
function highlightRole() {
    const pembeli = document.querySelector('input[value="pembeli"]');
    const agen = document.querySelector('input[value="agen"]');
    document.getElementById('lbl-pembeli').style.borderColor = pembeli.checked ? 'var(--gold)' : 'var(--cream-dark)';
    document.getElementById('lbl-agen').style.borderColor = agen.checked ? 'var(--gold)' : 'var(--cream-dark)';
}
highlightRole();
</script>
</body>
</html>
