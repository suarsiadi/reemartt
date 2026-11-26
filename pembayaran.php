<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid      = (int)$_SESSION['user_id'];
$id_order = isset($_GET['id_order']) ? (int)$_GET['id_order'] : 0;

// Proses Konfirmasi Pembayaran Masuk ke Escrow
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['konfirmasi_bayar'])) {
    mysqli_query($conn, "UPDATE orders SET status_order = 'Diproses Penjual (Dana Ditahan)' WHERE id_order = $id_order AND id_buyer = $uid");
    header("Location: pembeli.php");
    exit;
}

$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT orders.*, products.nama_produk FROM orders JOIN products ON orders.id_product = products.id_product WHERE orders.id_order = $id_order AND orders.id_buyer = $uid"));
if (!$order) {
    die("Data tagihan tidak ditemukan.");
}

// Generate Nomor Virtual Account Unik berdasarkan ID Order
$kode_bank = (strpos($order['metode_pembayaran'], 'BCA') !== false) ? '88010' : '88020';
$nomor_va  = $kode_bank . str_pad($order['id_order'], 10, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Selesaikan Pembayaran | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container" style="max-width: 540px; margin-top: 30px; margin-bottom: 50px;">
        <div class="checkout-card" style="text-align: center;">
            <span class="status-badge status-pending">Menunggu Pembayaran</span>
            <h2 style="margin-top: 12px;">Selesaikan Pembayaran Escrow</h2>
            <p style="font-size: 13px; color: #64748b;">Invoice: <strong><?= $order['kode_invoice'] ?></strong></p>

            <div style="background: #eff6ff; border: 1px solid #bfdbfe; padding: 14px; border-radius: 8px; margin: 16px 0;">
                <span style="font-size: 12px; color: #1e40af; font-weight: 600;">Total Tagihan (Termasuk Ongkir & Biaya Layanan)</span>
                <div style="font-size: 28px; font-weight: 800; color: #2563eb; margin-top: 4px;">
                    Rp<?= number_format($order['total_bayar'], 0, ',', '.') ?>
                </div>
            </div>

            <!-- TAMPILAN QRIS ATAU VIRTUAL ACCOUNT -->
            <div style="border: 1px solid #e2e8f0; padding: 18px; border-radius: 8px; margin-bottom: 16px;">
                <p style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 10px;">
                    Metode: <?= htmlspecialchars($order['metode_pembayaran']) ?>
                </p>

                <?php if (strpos($order['metode_pembayaran'], 'QRIS') !== false) : ?>
                    <!-- QR Code Dinamis via API QR Server -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=REEMART-ESCROW-<?= $order['kode_invoice'] ?>-RP<?= $order['total_bayar'] ?>" alt="QRIS ReeMart" style="border: 4px solid #eff6ff; border-radius: 8px; margin: 8px 0;">
                    <p style="font-size: 12px; color: #64748b;">NMID: ID1026009988123 &bull; PT ReeMart Re-Commerce Indonesia</p>
                <?php else : ?>
                    <p style="font-size: 12px; color: #64748b;">Nomor Virtual Account (Rekening Bersama ReeMart):</p>
                    <div style="background: #f1f5f9; padding: 12px; border-radius: 6px; font-size: 22px; font-weight: 800; letter-spacing: 2px; color: #1e3a8a; margin: 8px 0;">
                        <?= $nomor_va ?>
                    </div>
                    <p style="font-size: 12px; color: #64748b;">Atas Nama: <strong>REEMART ESCROW - <?= strtoupper(htmlspecialchars($order['nama_pembeli'])) ?></strong></p>
                <?php endif; ?>
            </div>

            <div class="escrow-guarantee" style="text-align: left; margin-bottom: 16px;">
                <strong>🛡️ Keamanan Dana Terjamin:</strong>
                <p>Dana Anda masuk ke rekening bersama ReeMart dan TIDAK langsung dikirim ke penjual sampai barang Anda terima.</p>
            </div>

            <?php if ($order['status_order'] == 'Menunggu Pembayaran') : ?>
                <form method="POST">
                    <button type="submit" name="konfirmasi_bayar" class="btn-pay-now">✓ Saya Sudah Membayar (Simulasi Verifikasi)</button>
                </form>
            <?php else : ?>
                <div class="success-box">Pembayaran sudah terverifikasi! <a href="pembeli.php">Lihat status pesanan &rarr;</a></div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>