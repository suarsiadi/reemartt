<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid     = (int)$_SESSION['user_id'];
$id_prod = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id_product = $id_prod AND id_seller = $uid AND status_produk = 'Tersedia'"));
if (!$produk) {
    die("Produk tidak ditemukan, bukan milik Anda, atau sudah terjual.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga    = (float) $_POST['harga'];
    $kondisi  = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $skor     = (float) $_POST['skor_kondisi'];
    $minus    = mysqli_real_escape_string($conn, $_POST['deskripsi_minus']);
    $kota     = mysqli_real_escape_string($conn, $_POST['kota_penjual']);

    $foto_path = $produk['foto_produk'];
    if (!empty($_FILES['foto']['name'])) {
        $nama_file = time() . "_" . basename($_FILES['foto']['name']);
        $target    = "uploads/" . $nama_file;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
            $foto_path = $target;
        }
    }

    $sql = "UPDATE products SET 
            nama_produk = '$nama', 
            kategori = '$kategori', 
            harga = '$harga', 
            kondisi = '$kondisi', 
            skor_kondisi = '$skor', 
            deskripsi_minus = '$minus', 
            kota_penjual = '$kota', 
            foto_produk = '$foto_path' 
            WHERE id_product = $id_prod AND id_seller = $uid";

    if (mysqli_query($conn, $sql)) {
        header("Location: penjual.php?updated=1");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk Preloved | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container sell-container">
        <div class="checkout-card">
            <h2>✏️ Edit Detail Barang Preloved</h2>
            <p class="form-subtitle">Perbarui harga, kondisi, atau catatan minus barang Anda.</p>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Produk *</label>
                    <input type="text" name="nama_produk" class="form-control" value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori *</label>
                        <select name="kategori" class="form-control" required>
                            <option value="Fashion" <?= $produk['kategori'] == 'Fashion' ? 'selected' : '' ?>>Fashion & Thrifting</option>
                            <option value="Gadget" <?= $produk['kategori'] == 'Gadget' ? 'selected' : '' ?>>Gadget & Elektronik</option>
                            <option value="Buku" <?= $produk['kategori'] == 'Buku' ? 'selected' : '' ?>>Buku & Perlengkapan Kuliah</option>
                            <option value="Kos" <?= $produk['kategori'] == 'Kos' ? 'selected' : '' ?>>Perabotan Kamar & Kos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga Jual (Rp) *</label>
                        <input type="number" name="harga" class="form-control" value="<?= (int)$produk['harga'] ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tingkat Kondisi *</label>
                        <select name="kondisi" class="form-control" required>
                            <option value="Like New" <?= $produk['kondisi'] == 'Like New' ? 'selected' : '' ?>>Like New (95% - 99%)</option>
                            <option value="Good" <?= $produk['kondisi'] == 'Good' ? 'selected' : '' ?>>Good (80% - 94%)</option>
                            <option value="Fair" <?= $produk['kondisi'] == 'Fair' ? 'selected' : '' ?>>Fair (70% - 79%)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Skor Kondisi (1 - 10) *</label>
                        <input type="number" name="skor_kondisi" min="1" max="10" step="0.5" class="form-control" value="<?= $produk['skor_kondisi'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kota Lokasi Barang *</label>
                    <input type="text" name="kota_penjual" class="form-control" value="<?= htmlspecialchars($produk['kota_penjual']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Catatan Minus / Cacat Barang *</label>
                    <textarea name="deskripsi_minus" class="form-control" rows="3" required><?= htmlspecialchars($produk['deskripsi_minus']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ganti Foto Produk (Kosongkan jika tidak ingin mengubah foto)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-pay-now">Simpan Perubahan</button>
                    <a href="penjual.php" class="btn-retur" style="text-decoration: none; padding: 12px 20px; text-align: center;">Batal</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>