<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

// 1. Proses Tambah ke Keranjang (dikirim dari detail.php)
if (isset($_GET['add'])) {
    $id_prod = (int)$_GET['add'];
    $harga   = isset($_GET['harga']) ? (float)$_GET['harga'] : 0;

    // Cek apakah produk masih tersedia dan bukan milik sendiri
    $cek_prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id_product = $id_prod AND status_produk = 'Tersedia'"));
    if ($cek_prod && $cek_prod['id_seller'] != $uid) {
        if ($harga <= 0) $harga = $cek_prod['harga'];

        // Cek apakah sudah ada di keranjang user ini (karena preloved stok 1, update saja harganya)
        $cek_cart = mysqli_query($conn, "SELECT id_cart FROM carts WHERE id_user = $uid AND id_product = $id_prod");
        if (mysqli_num_rows($cek_cart) > 0) {
            mysqli_query($conn, "UPDATE carts SET harga_nego = $harga WHERE id_user = $uid AND id_product = $id_prod");
        } else {
            mysqli_query($conn, "INSERT INTO carts (id_user, id_product, harga_nego) VALUES ($uid, $id_prod, $harga)");
        }
    }
    header("Location: keranjang.php");
    exit;
}

// 2. Proses Hapus Item dari Keranjang
if (isset($_GET['hapus'])) {
    $id_cart = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM carts WHERE id_cart = $id_cart AND id_user = $uid");
    header("Location: keranjang.php");
    exit;
}

// Ambil Daftar Isi Keranjang User (hanya yang produknya masih Tersedia)
$items = mysqli_query($conn, "SELECT carts.*, products.nama_produk, products.harga AS harga_asli, products.kondisi, products.skor_kondisi, products.foto_produk, products.nama_penjual, products.kota_penjual 
                              FROM carts 
                              JOIN products ON carts.id_product = products.id_product 
                              WHERE carts.id_user = $uid AND products.status_produk = 'Tersedia' 
                              ORDER BY carts.id_cart DESC");
$jumlah_item = mysqli_num_rows($items);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container dashboard-container">
        <div class="dashboard-header">
            <h2>🛒 Keranjang Belanja Preloved (<?= $jumlah_item ?>)</h2>
            <p>Karena barang preloved bersifat stok tunggal (1 unit), selesaikan checkout segera sebelum dibeli pengguna lain.</p>
        </div>

        <?php if ($jumlah_item == 0) : ?>
            <div class="checkout-card" style="text-align: center; padding: 40px;">
                <p style="font-size: 15px; font-weight: 600; color: #475569; margin-bottom: 10px;">Keranjang belanja Anda masih kosong.</p>
                <a href="index.php" class="btn-sell" style="display: inline-block;">Mulai Cari Barang Preloved</a>
            </div>
        <?php else : ?>
            <?php while ($item = mysqli_fetch_assoc($items)) : ?>
            <div class="order-card">
                <div class="order-header">
                    <span>🏪 Penjual: <strong><?= htmlspecialchars($item['nama_penjual']) ?></strong> (📍 <?= htmlspecialchars($item['kota_penjual']) ?>)</span>
                    <span class="nego-tag"><?= $item['harga_nego'] < $item['harga_asli'] ? 'Harga Deal Nego' : 'Harga Normal' ?></span>
                </div>
                <div class="order-body">
                    <img src="<?= htmlspecialchars($item['foto_produk']) ?>" alt="Produk">
                    <div class="order-info">
                        <h4><?= htmlspecialchars($item['nama_produk']) ?></h4>
                        <p>Kondisi: <strong><?= $item['kondisi'] ?> (<?= $item['skor_kondisi'] ?>/10)</strong></p>
                        <div style="margin-top: 4px;">
                            <?php if ($item['harga_nego'] < $item['harga_asli']) : ?>
                                <small style="text-decoration: line-through; color: #94a3b8;">Rp<?= number_format($item['harga_asli'], 0, ',', '.') ?></small>
                            <?php endif; ?>
                            <span style="font-size: 16px; font-weight: 800; color: #2563eb;">Rp<?= number_format($item['harga_nego'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="checkout.php?id=<?= $item['id_product'] ?>&harga=<?= $item['harga_nego'] ?>" class="btn-confirm" style="text-decoration: none;">Checkout Sekarang</a>
                        <a href="keranjang.php?hapus=<?= $item['id_cart'] ?>" class="btn-retur" style="text-decoration: none;" onclick="return confirm('Hapus barang ini dari keranjang?')">Hapus</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>