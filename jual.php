<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_seller    = (int)$_SESSION['user_id'];
    $nama_penjual = mysqli_real_escape_string($conn, $_SESSION['user_nama']);
    $nama         = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori     = mysqli_real_escape_string($conn, $_POST['kategori']);
    $harga        = (float) $_POST['harga'];
    $kondisi      = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $skor         = (float) $_POST['skor_kondisi'];
    $minus        = mysqli_real_escape_string($conn, $_POST['deskripsi_minus']);
    $kota         = mysqli_real_escape_string($conn, $_POST['kota_penjual']);

    $foto_path = "https://placehold.co/300x300/eff6ff/1e3a8a?text=Produk+Preloved";
    if (!empty($_FILES['foto']['name'])) {
        $nama_file = time() . "_" . basename($_FILES['foto']['name']);
        $target    = "uploads/" . $nama_file;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
            $foto_path = $target;
        }
    }

    $sql = "INSERT INTO products (id_seller, nama_produk, kategori, harga, kondisi, skor_kondisi, deskripsi_minus, kota_penjual, nama_penjual, foto_produk) 
            VALUES ('$id_seller', '$nama', '$kategori', '$harga', '$kondisi', '$skor', '$minus', '$kota', '$nama_penjual', '$foto_path')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jual Barang Preloved | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container sell-container">
        <div class="checkout-card">
            <h2>📤 Unggah Barang Preloved Baru</h2>
            <p class="form-subtitle">Data akan langsung disimpan ke database MySQL dan tayang di katalog.</p>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Produk *</label>
                    <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Sepatu Converse Hitam Size 42" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori *</label>
                        <select name="kategori" class="form-control" required>
                            <option value="Fashion">Fashion & Thrifting</option>
                            <option value="Gadget">Gadget & Elektronik</option>
                            <option value="Buku">Buku & Perlengkapan Kuliah</option>
                            <option value="Kos">Perabotan Kamar & Kos</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Harga Jual (Rp) *</label>
                        <input type="number" name="harga" class="form-control" placeholder="Contoh: 250000" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tingkat Kondisi *</label>
                        <select name="kondisi" class="form-control" required>
                            <option value="Like New">Like New (95% - 99%)</option>
                            <option value="Good">Good (80% - 94%)</option>
                            <option value="Fair">Fair (70% - 79%)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Skor Kondisi (1 - 10) *</label>
                        <input type="number" name="skor_kondisi" min="1" max="10" step="0.5" class="form-control" value="9.0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Kota Lokasi Barang *</label>
                    <input type="text" name="kota_penjual" class="form-control" value="Pangkalpinang" required>
                </div>

                <div class="form-group">
                    <label>Catatan Minus / Cacat Barang (Wajib Diisi) *</label>
                    <textarea name="deskripsi_minus" class="form-control" rows="3" placeholder="Jelaskan kondisi minus secara jujur..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Unggah Foto Asli Barang (Real Pict)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn-pay-now">Terbitkan Produk ke Database</button>
            </form>
        </div>
    </main>
</body>
</html>