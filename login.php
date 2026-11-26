<?php
include 'koneksi.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass  = $_POST['password'];

    $res = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    if ($user = mysqli_fetch_assoc($res)) {
        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id']   = $user['id_user'];
            $_SESSION['user_nama'] = $user['nama_lengkap'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Password yang Anda masukkan salah.";
        }
    } else {
        $error = "Email tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <header class="navbar">
        <div class="container nav-content">
            <a href="index.php" class="logo">Ree<span>Mart</span></a>
            <a href="register.php" class="nav-icon">Belum punya akun? Daftar &rarr;</a>
        </div>
    </header>

    <main class="container" style="max-width: 440px; margin-top: 40px; margin-bottom: 50px;">
        <div class="checkout-card">
            <h2>🔐 Masuk ke ReeMart</h2>
            <p class="form-subtitle">Gunakan akun Anda untuk bertransaksi dengan perlindungan escrow.</p>

            <?php if (isset($_GET['registered'])) : ?>
                <div class="success-box" style="margin-bottom: 14px; margin-top: 0;">Pendaftaran berhasil! Silakan login menggunakan akun baru Anda.</div>
            <?php endif; ?>

            <?php if ($error) : ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 14px;"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="suar@reemart.id" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="******" required>
                </div>
                <button type="submit" class="btn-pay-now">Masuk</button>
            </form>

            <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;">
                <strong>Akun Demo Cepat (Password: 123456):</strong><br>
                &bull; User: <code>suar@reemart.id</code><br>
                &bull; Admin: <code>admin@reemart.id</code>
            </div>
        </div>
    </main>
</body>
</html>