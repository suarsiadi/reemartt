<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_order = (int)$_POST['id_order'];
    $aksi     = $_POST['aksi'];

    if ($aksi == 'terima') {
        mysqli_query($conn, "UPDATE orders SET status_order = 'Selesai (Dana Cair)' WHERE id_order = $id_order AND id_buyer = $uid");
    } elseif ($aksi == 'retur') {
        $alasan = mysqli_real_escape_string($conn, $_POST['alasan_retur']);
        mysqli_query($conn, "UPDATE orders SET status_order = 'Dalam Mediasi Admin', alasan_retur = '$alasan' WHERE id_order = $id_order AND id_buyer = $uid");
    } elseif ($aksi == 'batal') {
        $id_prod = (int)$_POST['id_product'];
        mysqli_query($conn, "UPDATE orders SET status_order = 'Dibatalkan' WHERE id_order = $id_order AND id_buyer = $uid AND status_order = 'Menunggu Pembayaran'");
        // Kembalikan stok barang menjadi Tersedia di katalog
        mysqli_query($conn, "UPDATE products SET status_produk = 'Tersedia' WHERE id_product = $id_prod");
    } elseif ($aksi == 'ulas') {
        $rating = (int)$_POST['rating'];
        $ulasan = mysqli_real_escape_string($conn, $_POST['ulasan']);
        mysqli_query($conn, "UPDATE orders SET rating = $rating, ulasan = '$ulasan' WHERE id_order = $id_order AND id_buyer = $uid AND status_order = 'Selesai (Dana Cair)'");
    }
}

$orders = mysqli_query($conn, "SELECT orders.*, products.nama_produk, products.foto_produk FROM orders JOIN products ON orders.id_product = products.id_product WHERE orders.id_buyer = $uid ORDER BY id_order DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Pembeli | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container dashboard-container">
        <div class="dashboard-header">
            <h2>🛍️ Pesanan Saya (Sisi Pembeli)</h2>
            <p>Kelola pembayaran, konfirmasi penerimaan barang, atau berikan ulasan untuk penjual.</p>
        </div>

        <?php if (mysqli_num_rows($orders) == 0) : ?>
            <div class="checkout-card" style="text-align: center; padding: 40px;">
                <p style="font-size: 15px; font-weight: 600; color: #475569; margin-bottom: 10px;">Anda belum memiliki riwayat pesanan.</p>
                <a href="index.php" class="btn-sell" style="display: inline-block;">Mulai Belanja Sekarang</a>
            </div>
        <?php endif; ?>

        <?php while ($o = mysqli_fetch_assoc($orders)) : ?>
        <div class="order-card">
            <div class="order-header">
                <span><strong><?= $o['kode_invoice'] ?></strong> &bull; <?= htmlspecialchars($o['kurir']) ?> <?= $o['no_resi'] ? "(Resi: {$o['no_resi']})" : "" ?></span>
                <span class="status-badge <?= $o['status_order'] == 'Dibatalkan' ? 'status-dispute' : ($o['status_order'] == 'Selesai (Dana Cair)' ? 'status-done' : 'status-shipped') ?>">
                    <?= $o['status_order'] ?>
                </span>
            </div>
            <div class="order-body">
                <img src="<?= htmlspecialchars($o['foto_produk']) ?>" alt="Produk">
                <div class="order-info">
                    <h4><?= htmlspecialchars($o['nama_produk']) ?></h4>
                    <p>Total Bayar: <strong>Rp<?= number_format($o['total_bayar'], 0, ',', '.') ?></strong></p>

                    <!-- Tampilkan Ulasan Jika Sudah Diisi -->
                    <?php if (!empty($o['rating'])) : ?>
                        <div style="margin-top: 6px; background: #f8fafc; padding: 8px 10px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 12.5px;">
                            <span style="color: #f59e0b; font-weight: 700;"><?= str_repeat('★', $o['rating']) . str_repeat('☆', 5 - $o['rating']) ?> (<?= $o['rating'] ?>/5)</span>
                            <p style="margin: 2px 0 0 0; color: #334155;">"<?= htmlspecialchars($o['ulasan']) ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- AKSI SESUAI STATUS PESANAN -->
                <?php if ($o['status_order'] == 'Menunggu Pembayaran') : ?>
                    <div class="order-actions">
                        <a href="pembayaran.php?id_order=<?= $o['id_order'] ?>" class="btn-confirm" style="text-align: center; text-decoration: none;">💳 Bayar Sekarang</a>
                        <form method="POST" onsubmit="return confirm('Batalkan pesanan ini? Barang akan kembali tersedia di katalog.')">
                            <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                            <input type="hidden" name="id_product" value="<?= $o['id_product'] ?>">
                            <input type="hidden" name="aksi" value="batal">
                            <button type="submit" class="btn-retur" style="width: 100%;">✕ Batalkan Pesanan</button>
                        </form>
                    </div>
                <?php elseif ($o['status_order'] == 'Sedang Dikirim' || $o['status_order'] == 'Diproses Penjual (Dana Ditahan)') : ?>
                    <div class="order-actions">
                        <form method="POST">
                            <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                            <input type="hidden" name="aksi" value="terima">
                            <button type="submit" class="btn-confirm" style="width: 100%;">✓ Pesanan Diterima</button>
                        </form>
                        <form method="POST" style="display: flex; gap: 4px;">
                            <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                            <input type="hidden" name="aksi" value="retur">
                            <input type="text" name="alasan_retur" placeholder="Alasan retur..." class="form-control" style="padding: 4px 8px; font-size: 12px;" required>
                            <button type="submit" class="btn-retur">Retur</button>
                        </form>
                    </div>
                <?php elseif ($o['status_order'] == 'Selesai (Dana Cair)' && empty($o['rating'])) : ?>
                    <form method="POST" style="min-width: 240px; background: #eff6ff; padding: 10px; border-radius: 6px; border: 1px solid #bfdbfe;">
                        <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                        <input type="hidden" name="aksi" value="ulas">
                        <label style="font-size: 12px; font-weight: 700; color: #1e3a8a; display: block; margin-bottom: 4px;">Beri Rating & Ulasan Penjual:</label>
                        <select name="rating" class="form-control" style="padding: 4px 8px; font-size: 12px; margin-bottom: 6px;" required>
                            <option value="5">★★★★★ (5 - Sangat Sesuai Deskripsi)</option>
                            <option value="4">★★★★☆ (4 - Bagus & Jujur)</option>
                            <option value="3">★★★☆☆ (3 - Cukup Sesuai)</option>
                            <option value="2">★★☆☆☆ (2 - Kurang Sesuai)</option>
                            <option value="1">★☆☆☆☆ (1 - Mengecewakan)</option>
                        </select>
                        <input type="text" name="ulasan" placeholder="Tulis pengalaman belanja Anda..." class="form-control" style="padding: 5px 8px; font-size: 12px; margin-bottom: 6px;" required>
                        <button type="submit" class="btn-confirm" style="width: 100%; padding: 6px;">Kirim Ulasan</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>