<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];
$sukses = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $no_hp  = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $kota   = mysqli_real_escape_string($conn, $_POST['kota']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama', no_hp='$no_hp', kota='$kota', alamat='$alamat' WHERE id_user=$uid");
    $_SESSION['user_nama'] = $nama;
    $sukses = "Data profil dan alamat berhasil diperbarui!";
}

$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user = $uid"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container sell-container">
        <div class="checkout-card">
            <h2>👤 Profil & Alamat Pengguna</h2>
            <p class="form-subtitle">Informasi ini otomatis digunakan saat Anda melakukan checkout maupun berjualan.</p>

            <?php if ($sukses) : ?>
                <div class="success-box" style="margin-bottom: 14px; margin-top: 0;"><?= $sukses ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Email Akun (Tidak dapat diubah)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" disabled style="background: #f1f5f9;">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Lengkap / Nama Toko</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($u['nama_lengkap']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nomor WhatsApp / HP</label>
                        <input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($u['no_hp']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kota</label>
                    <input type="text" name="kota" class="form-control" value="<?= htmlspecialchars($u['kota']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Alamat Pengiriman Utama</label>
                    <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($u['alamat']) ?></textarea>
                </div>
                <button type="submit" class="btn-pay-now">Simpan Perubahan Profil</button>
            </form>
        </div>
    </main>
</body>
</html>