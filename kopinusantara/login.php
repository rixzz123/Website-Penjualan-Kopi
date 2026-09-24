<?php
require_once 'includes/auth.php';
require_once 'includes/koneksi.php';

if (isLoggedIn()) {
    header("Location: " . BASE_URL . (getRole() === 'agen' ? '/admin/dashboard.php' : '/index.php'));
    exit;
}

$pageTitle = 'Masuk – Kopi Nusantara';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $koneksi->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Note: In production, use password_verify() with hashed passwords
        if ($user && $user['password'] === $password) {
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama']    = $user['nama_lengkap'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            $redirect = $_GET['redirect'] ?? ($user['role'] === 'agen' ? BASE_URL . '/admin/dashboard.php' : BASE_URL . '/index.php');
            header("Location: $redirect");
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
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
    <div class="auth-layout">
        <aside class="auth-panel">
            <div class="auth-panel-content">
                <div class="auth-panel-title">Selamat Datang di Kopi Nusantara</div>
                <p class="auth-panel-text">Masuk ke akun pembeli Anda untuk mengakses katalog kopi, keranjang, dan riwayat pesanan dengan cepat.</p>
                <div class="auth-panel-note"><i class="fas fa-coffee"></i> Nikmati aroma kopi Nusantara secara online</div>
            </div>
        </aside>

        <main class="auth-card">
            <div class="auth-logo">☕ Kopi <span>Nusantara</span></div>
            <p class="auth-subtitle">Marketplace Kopi Premium Indonesia</p>
            <h2 class="auth-title">Login</h2>

            <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Registrasi berhasil! Silakan masuk.</div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-icon-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="auth-footer">
                Belum punya akun? <a href="<?= BASE_URL ?>/register.php">Daftar Sekarang</a>
            </div>
        </main>
    </div>
</div>

</body>
</html>
