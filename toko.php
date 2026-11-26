<?php
include 'koneksi.php';

$id_seller = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$penjual   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user = $id_seller"));

if (!$penjual) {
    die("Profil toko tidak ditemukan.");
}

// Statistik Toko
$stat_rating   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) AS rata, COUNT(rating) AS jml_ulasan FROM orders WHERE id_seller = $id_seller AND rating IS NOT NULL"));
$rata_rating   = $stat_rating['rata'] ? number_format($stat_rating['rata'], 1) : 'Baru';
$jumlah_ulasan = (int)$stat_rating['jml_ulasan'];

$terjual = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE id_seller = $id_seller AND status_order = 'Selesai (Dana Cair)'"))['total'] ?? 0;

// Daftar Produk Tersedia Milik Toko Ini
$produk_toko = mysqli_query($conn, "SELECT * FROM products WHERE id_seller = $id_seller AND status_produk = 'Tersedia' ORDER BY id_product DESC");
$jumlah_aktif = mysqli_num_rows($produk_toko);

// Daftar Ulasan Pembeli untuk Toko Ini
$ulasan_toko = mysqli_query($conn, "SELECT orders.rating, orders.ulasan, orders.nama_pembeli, products.nama_produk FROM orders JOIN products ON orders.id_product = products.id_product WHERE orders.id_seller = $id_seller AND orders.rating IS NOT NULL ORDER BY orders.id_order DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko <?= htmlspecialchars($penjual['nama_lengkap']) ?> | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container dashboard-container">
        <!-- HEADER PROFIL TOKO -->
        <div class="checkout-card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="seller-avatar" style="width: 60px; height: 60px; font-size: 22px;">
                    <?= strtoupper(substr($penjual['nama_lengkap'], 0, 2)) ?>
                </div>
                <div>
                    <h2 style="font-size: 20px; color: #0f172a; margin-bottom: 4px;">
                        <?= htmlspecialchars($penjual['nama_lengkap']) ?> 
                        <span class="verified-tag">✓ Penjual Terverifikasi</span>
                    </h2>
                    <p style="font-size: 13.5px; color: #64748b;">
                        📍 Kota <?= htmlspecialchars($penjual['kota']) ?> &bull; 
                        <span style="color: #f59e0b; font-weight: 700;">★ <?= $rata_rating ?></span> (<?= $jumlah_ulasan ?> Ulasan) &bull; 
                        <strong><?= $terjual ?></strong> Transaksi Sukses
                    </p>
                </div>
            </div>
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 10px 16px; border-radius: 8px; text-align: center;">
                <span style="font-size: 11.5px; color: #1e40af; font-weight: 600;">Etalase Aktif</span>
                <div style="font-size: 18px; font-weight: 800; color: #2563eb;"><?= $jumlah_aktif ?> Barang</div>
            </div>
        </div>

        <!-- ETALASE BARANG DIJUAL -->
        <div class="checkout-card">
            <h3>🛍️ Etalase Produk Tersedia</h3>
            <?php if ($jumlah_aktif == 0) : ?>
                <p style="font-size: 13.5px; color: #64748b; margin-top: 8px;">Saat ini tidak ada barang aktif yang dijual oleh toko ini.</p>
            <?php else : ?>
                <div class="product-grid" style="margin-top: 14px;">
                    <?php while ($row = mysqli_fetch_assoc($produk_toko)) : ?>
                    <div class="product-card" onclick="window.location.href='detail.php?id=<?= $row['id_product'] ?>'">
                        <div class="product-img">
                            <span class="badge like-new"><?= htmlspecialchars($row['kondisi']) ?> <?= $row['skor_kondisi'] ?>/10</span>
                            <img src="<?= htmlspecialchars($row['foto_produk']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?= htmlspecialchars($row['nama_produk']) ?></h3>
                            <p class="product-price">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
                            <p class="product-minus">Minus: <?= htmlspecialchars($row['deskripsi_minus']) ?></p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- DAFTAR ULASAN PEMBELI -->
        <div class="checkout-card">
            <h3>⭐ Ulasan Pembeli (<?= $jumlah_ulasan ?>)</h3>
            <?php if ($jumlah_ulasan == 0) : ?>
                <p style="font-size: 13.5px; color: #64748b; margin-top: 8px;">Toko ini belum memiliki ulasan dari pembeli.</p>
            <?php else : ?>
                <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 10px;">
                    <?php while ($ul = mysqli_fetch_assoc($ulasan_toko)) : ?>
                        <div style="background: #f8fafc; padding: 12px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                                <strong><?= htmlspecialchars($ul['nama_pembeli']) ?></strong>
                                <span style="color: #f59e0b; font-weight: 700;"><?= str_repeat('★', $ul['rating']) . str_repeat('☆', 5 - $ul['rating']) ?></span>
                            </div>
                            <p style="font-size: 13.5px; color: #1e293b; margin-bottom: 4px;">"<?= htmlspecialchars($ul['ulasan']) ?>"</p>
                            <small style="color: #64748b;">Produk dibeli: <strong><?= htmlspecialchars($ul['nama_produk']) ?></strong></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>