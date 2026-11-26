<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

// Proses Toggle Simpan / Hapus dari Wishlist (dikirim dari detail.php)
if (isset($_GET['toggle'])) {
    $id_prod = (int)$_GET['toggle'];
    $cek     = mysqli_query($conn, "SELECT id_wishlist FROM wishlists WHERE id_user = $uid AND id_product = $id_prod");

    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "DELETE FROM wishlists WHERE id_user = $uid AND id_product = $id_prod");
    } else {
        mysqli_query($conn, "INSERT INTO wishlists (id_user, id_product) VALUES ($uid, $id_prod)");
    }

    // Kembalikan ke halaman detail jika diklik dari detail.php
    if (isset($_GET['from']) && $_GET['from'] == 'detail') {
        header("Location: detail.php?id=$id_prod");
    } else {
        header("Location: wishlist.php");
    }
    exit;
}

// Ambil Daftar Barang Favorit User
$items = mysqli_query($conn, "SELECT wishlists.id_wishlist, products.* 
                              FROM wishlists 
                              JOIN products ON wishlists.id_product = products.id_product 
                              WHERE wishlists.id_user = $uid 
                              ORDER BY wishlists.id_wishlist DESC");
$jumlah_fav = mysqli_num_rows($items);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist Favorit | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container dashboard-container">
        <div class="dashboard-header">
            <h2>❤️ Barang Favorit / Wishlist (<?= $jumlah_fav ?>)</h2>
            <p>Simpan barang preloved incaran Anda di sini sebelum dibeli pengguna lain.</p>
        </div>

        <?php if ($jumlah_fav == 0) : ?>
            <div class="checkout-card" style="text-align: center; padding: 40px;">
                <p style="font-size: 15px; font-weight: 600; color: #475569; margin-bottom: 10px;">Belum ada barang yang disimpan ke Wishlist.</p>
                <a href="index.php" class="btn-sell" style="display: inline-block;">Jelajahi Katalog Preloved</a>
            </div>
        <?php else : ?>
            <div class="product-grid">
                <?php while ($row = mysqli_fetch_assoc($items)) : ?>
                <div class="product-card" style="display: flex; flex-direction: column; justify-content: space-between;">
                    <div onclick="window.location.href='detail.php?id=<?= $row['id_product'] ?>'">
                        <div class="product-img">
                            <span class="badge like-new"><?= htmlspecialchars($row['kondisi']) ?> <?= $row['skor_kondisi'] ?>/10</span>
                            <img src="<?= htmlspecialchars($row['foto_produk']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($row['nama_produk']) ?></h3>
                            <p class="product-price">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
                            <p class="product-minus">Status: <strong><?= $row['status_produk'] ?></strong></p>
                        </div>
                    </div>
                    <div style="padding: 0 14px 14px 14px; display: flex; gap: 6px;">
                        <?php if ($row['status_produk'] == 'Tersedia') : ?>
                            <a href="detail.php?id=<?= $row['id_product'] ?>" class="btn-confirm" style="flex: 1; text-align: center; text-decoration: none; padding: 6px;">Lihat</a>
                        <?php endif; ?>
                        <a href="wishlist.php?toggle=<?= $row['id_product'] ?>" class="btn-retur" style="text-decoration: none; padding: 6px 10px;">Hapus</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>