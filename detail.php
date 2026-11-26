<?php
include 'koneksi.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id_product = $id"));
$is_favorit = false;
if (isset($_SESSION['user_id'])) {
    $cek_fav = mysqli_query($conn, "SELECT id_wishlist FROM wishlists WHERE id_user = " . (int)$_SESSION['user_id'] . " AND id_product = $id");
    $is_favorit = (mysqli_num_rows($cek_fav) > 0);
}
$id_seller = (int)$produk['id_seller'];
$stat_rating = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) AS rata_rating, COUNT(rating) AS jumlah_ulasan FROM orders WHERE id_seller = $id_seller AND rating IS NOT NULL"));
$rata_rating = $stat_rating['rata_rating'] ? number_format($stat_rating['rata_rating'], 1) : 'Baru';
$jumlah_ulasan = (int)$stat_rating['jumlah_ulasan'];
$daftar_ulasan = mysqli_query($conn, "SELECT orders.rating, orders.ulasan, orders.nama_pembeli, products.nama_produk FROM orders JOIN products ON orders.id_product = products.id_product WHERE orders.id_seller = $id_seller AND orders.rating IS NOT NULL ORDER BY orders.id_order DESC LIMIT 3");
if (!$produk) { die("Produk tidak ditemukan."); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($produk['nama_produk']) ?> | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container detail-wrapper">
        <div class="detail-gallery">
            <div class="main-photo">
                <span class="badge like-new"><?= $produk['kondisi'] ?> <?= $produk['skor_kondisi'] ?>/10</span>
                <img src="<?= htmlspecialchars($produk['foto_produk']) ?>" alt="Foto Produk">
            </div>
        </div>

        <div class="detail-info">
            <h1 class="detail-title"><?= htmlspecialchars($produk['nama_produk']) ?></h1>
            <div class="detail-meta">Kategori: <strong><?= $produk['kategori'] ?></strong> &bull; Stok Tunggal Preloved</div>

            <div class="detail-price-box">
                <span class="detail-price" id="display-price">Rp<?= number_format($produk['harga'], 0, ',', '.') ?></span>
                <span class="nego-badge" id="nego-status">Bisa Nego</span>
            </div>

            <div class="condition-box">
                <h3>📋 Laporan Kondisi Barang (Preloved Check)</h3>
                <ul>
                    <li><strong>Skor Kondisi:</strong> <?= $produk['skor_kondisi'] ?> / 10 (<?= $produk['kondisi'] ?>)</li>
                    <li><strong>Lokasi Penjual:</strong> <?= htmlspecialchars($produk['kota_penjual']) ?></li>
                </ul>
                <div class="minus-alert">
                    <strong>⚠️ Catatan Minus:</strong> <?= htmlspecialchars($produk['deskripsi_minus']) ?>
                </div>
            </div>

            <div class="seller-card">
    <div class="seller-avatar"><?= strtoupper(substr($produk['nama_penjual'], 0, 2)) ?></div>
    <div class="seller-meta">
    <h4>
        <a href="toko.php?id=<?= $produk['id_seller'] ?>" style="color: #0f172a; text-decoration: none;">
            <?= htmlspecialchars($produk['nama_penjual']) ?>
        </a>
        <span class="verified-tag">✓ Terverifikasi</span>
    </h4>
    <p>📍 <?= htmlspecialchars($produk['kota_penjual']) ?> &bull; <span style="color: #f59e0b; font-weight: 700;">★ <?= $rata_rating ?></span> (<?= $jumlah_ulasan ?> ulasan) &bull; <a href="toko.php?id=<?= $produk['id_seller'] ?>" style="color: #2563eb; font-size: 12px; font-weight: 600;">Kunjungi Toko &rarr;</a></p>
</div>
</div>

<?php if ($jumlah_ulasan > 0) : ?>
<div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #e2e8f0;">
    <h4 style="font-size: 13px; color: #0f172a; margin-bottom: 8px;">💬 Ulasan Pembeli Sebelumnya:</h4>
    <?php while ($ul = mysqli_fetch_assoc($daftar_ulasan)) : ?>
        <div style="background: #f8fafc; padding: 8px 10px; border-radius: 6px; margin-bottom: 6px; font-size: 12px; border: 1px solid #e2e8f0;">
            <strong><?= htmlspecialchars($ul['nama_pembeli']) ?></strong> <span style="color: #f59e0b;"><?= str_repeat('★', $ul['rating']) ?></span>
            <p style="color: #334155; margin: 2px 0;">"<?= htmlspecialchars($ul['ulasan']) ?>"</p>
            <small style="color: #64748b;">Produk: <?= htmlspecialchars($ul['nama_produk']) ?></small>
        </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

        <div class="detail-action-card">
    <h3>Atur Pembelian</h3>
    <p class="stock-info">Status: <strong><?= $produk['status_produk'] ?></strong></p>

    <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$produk['id_seller']) : ?>
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; padding: 12px; border-radius: 6px; font-size: 13px; text-align: center; font-weight: 600; margin-bottom: 12px;">
            🏪 Ini adalah produk dari toko Anda sendiri. Anda tidak dapat membeli barang milik sendiri.
        </div>
        <a href="edit_produk.php?id=<?= $produk['id_product'] ?>" class="btn-buy-now" style="display: block; text-align: center;">✏️ Edit Produk Ini</a>
    <?php else : ?>
        <div class="nego-section">
            <label>Ajukan Harga Tawar (Rp):</label>
            <div class="nego-input-group">
                <input type="number" id="input-nego" placeholder="Masukkan tawaran">
                <button type="button" onclick="ajukanNego()">Tawar</button>
            </div>
            <small id="nego-feedback" class="nego-feedback"></small>
        </div>

        <div class="action-buttons">
            <a id="btn-checkout" href="checkout.php?id=<?= $produk['id_product'] ?>&harga=<?= $produk['harga'] ?>" class="btn-buy-now">Beli Sekarang (Escrow)</a>
            <a id="btn-cart" href="keranjang.php?add=<?= $produk['id_product'] ?>&harga=<?= $produk['harga'] ?>" class="btn-add-cart" style="text-align: center; text-decoration: none;">+ Masukkan Keranjang</a>
            <a href="chat.php?id_product=<?= $produk['id_product'] ?>&with=<?= $produk['id_seller'] ?>" class="role-link" style="text-align: center; border: 1px solid #cbd5e1; margin-top: 4px;">💬 Tanya / Chat Penjual</a>
            <a href="wishlist.php?toggle=<?= $produk['id_product'] ?>&from=detail" class="role-link" style="text-align: center; border: 1px solid <?= $is_favorit ? '#ef4444' : '#cbd5e1' ?>; color: <?= $is_favorit ? '#ef4444' : '#475569' ?>; margin-top: 4px;">
    <?= $is_favorit ? '❤️ Tersimpan di Wishlist' : '🤍 Simpan ke Wishlist' ?>
</a>
        </div>
    <?php endif; ?>

    <div class="escrow-guarantee">
        <strong>🛡️ Jaminan ReeMart Escrow:</strong>
        <p>Dana ditahan sistem hingga Anda mengonfirmasi barang sesuai deskripsi.</p>
    </div>
</div>
    </main>
    <?php include 'footer.php'; ?>

    <script>
        const hargaAsli = <?= $produk['harga'] ?>;
        const idProduk = <?= $produk['id_product'] ?>;

        function ajukanNego() {
            const tawaran = parseInt(document.getElementById('input-nego').value);
            const feedback = document.getElementById('nego-feedback');
            if (!tawaran || tawaran <= 0) return;

            if (tawaran < hargaAsli * 0.75) {
                feedback.style.color = '#d97706';
                feedback.textContent = 'Tawaran ditolak! Maksimal diskon nego 25%.';
            } else {
                feedback.style.color = '#2563eb';
                feedback.textContent = 'Tawaran disetujui! Harga checkout diperbarui.';
                document.getElementById('display-price').textContent = 'Rp' + tawaran.toLocaleString('id-ID');
                document.getElementById('nego-status').textContent = 'Harga Deal Nego';
                document.getElementById('btn-checkout').href = `checkout.php?id=${idProduk}&harga=${tawaran}`;
                document.getElementById('btn-cart').href = `keranjang.php?add=${idProduk}&harga=${tawaran}`;
            }
        }
    </script>
</body>
</html>