<?php
include 'koneksi.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $no_hp  = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $kota   = mysqli_real_escape_string($conn, $_POST['kota']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $cek = mysqli_query($conn, "SELECT id_user FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Email sudah terdaftar! Silakan gunakan email lain atau login.";
    } else {
        $sql = "INSERT INTO users (nama_lengkap, email, password, no_hp, kota, alamat) 
                VALUES ('$nama', '$email', '$pass', '$no_hp', '$kota', '$alamat')";
        if (mysqli_query($conn, $sql)) {
            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <header class="navbar">
        <div class="container nav-content">
            <a href="index.php" class="logo">Ree<span>Mart</span></a>
            <a href="login.php" class="nav-icon">Sudah punya akun? Login &rarr;</a>
        </div>
    </header>

    <main class="container" style="max-width: 500px; margin-top: 30px; margin-bottom: 50px;">
        <div class="checkout-card">
            <h2>📝 Daftar Akun ReeMart</h2>
            <p class="form-subtitle">Satu akun untuk belanja barang preloved sekaligus membuka toko.</p>

            <?php if ($error) : ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 14px;"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Nama Lengkap / Nama Toko *</label>
                    <input type="text" name="nama_lengkap" class="form-control" placeholder="Contoh: Suar Siadi" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required>
                    </div>
                    <div class="form-group">
                        <label>No. WhatsApp / HP *</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="0812xxxxxxxx" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kota Domisili *</label>
                    <input type="text" name="kota" class="form-control" placeholder="Contoh: Pangkalpinang" required>
                </div>
                <div class="form-group">
                    <label>Alamat Lengkap Pengiriman *</label>
                    <textarea name="alamat" class="form-control" rows="2" placeholder="Nama jalan, nomor kos/rumah, kecamatan..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
                <button type="submit" class="btn-pay-now">Daftar Sekarang</button>
            </form>
        </div>
    </main>
</body>
</html>