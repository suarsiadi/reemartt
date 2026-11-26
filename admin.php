<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_order = (int)$_POST['id_order'];
    if ($_POST['keputusan'] == 'refund') {
        mysqli_query($conn, "UPDATE orders SET status_order = 'Refund ke Pembeli' WHERE id_order = $id_order");
    } elseif ($_POST['keputusan'] == 'cair') {
        mysqli_query($conn, "UPDATE orders SET status_order = 'Selesai (Dana Cair)' WHERE id_order = $id_order");
    }
}

$kas_escrow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_bayar) AS total FROM orders WHERE status_order IN ('Diproses Penjual (Dana Ditahan)', 'Sedang Dikirim', 'Dalam Mediasi Admin')"))['total'] ?? 0;
$komisi_platform = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM((harga_deal * 0.04) + 2000) AS total FROM orders WHERE status_order = 'Selesai (Dana Cair)'"))['total'] ?? 0;
$sengketa = mysqli_query($conn, "SELECT orders.*, products.nama_produk FROM orders JOIN products ON orders.id_product = products.id_product WHERE status_order = 'Dalam Mediasi Admin'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container dashboard-container">
        <div class="dashboard-header">
            <h2>🛡️ Pusat Kontrol Administrator ReeMart</h2>
            <p>Monitoring kas escrow dan pusat resolusi sengketa secara real-time.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Kas Escrow Ditahan Sistem</span>
                <h3 class="stat-value">Rp<?= number_format($kas_escrow, 0, ',', '.') ?></h3>
            </div>
            <div class="stat-card">
                <span class="stat-label">Pendapatan Bersih ReeMart (Komisi 4% + Fee)</span>
                <h3 class="stat-value">Rp<?= number_format($komisi_platform, 0, ',', '.') ?></h3>
            </div>
        </div>

        <div class="checkout-card">
            <h3>⚖️ Pusat Resolusi Sengketa (Komplain Pembeli)</h3>
            <?php if (mysqli_num_rows($sengketa) == 0) : ?>
                <p style="font-size: 13.5px; color: #64748b;">Tidak ada kasus sengketa yang menunggu mediasi saat ini.</p>
            <?php endif; ?>

            <?php while ($s = mysqli_fetch_assoc($sengketa)) : ?>
            <div class="order-card" style="box-shadow: none; border: 1px solid #fecaca; background: #fffafa; margin-top: 12px;">
                <div class="order-header">
                    <span><strong><?= $s['kode_invoice'] ?></strong> &bull; Produk: <?= htmlspecialchars($s['nama_produk']) ?></span>
                    <span class="status-badge status-dispute">Dalam Mediasi Admin</span>
                </div>
                <div class="order-body">
                    <div class="order-info">
                        <p><strong>Keluhan Pembeli:</strong> <?= htmlspecialchars($s['alasan_retur']) ?></p>
                        <p>Nilai Transaksi: <strong>Rp<?= number_format($s['total_bayar'], 0, ',', '.') ?></strong></p>
                    </div>
                    <form method="POST" class="admin-decisions">
                        <input type="hidden" name="id_order" value="<?= $s['id_order'] ?>">
                        <button type="submit" name="keputusan" value="refund" class="btn-refund">Setujui Retur & Refund Pembeli</button>
                        <button type="submit" name="keputusan" value="cair" class="btn-release">Tolak & Cairkan ke Penjual</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>