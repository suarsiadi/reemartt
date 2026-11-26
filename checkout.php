<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid        = (int)$_SESSION['user_id'];
$user_login = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user = $uid"));

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$harga_deal = isset($_GET['harga']) ? (float)$_GET['harga'] : 0;
$produk     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id_product = $id"));
if ((int)$produk['id_seller'] === $uid) {
    echo "<script>alert('Anda tidak dapat membeli barang dari toko Anda sendiri!'); window.location.href='index.php';</script>";
    exit;
}

if (!$produk || $produk['status_produk'] != 'Tersedia') {
    die("Maaf, produk ini sudah tidak tersedia atau telah terjual.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $invoice      = "INV/" . date("Ymd") . "/RM-" . rand(100, 999);
    $id_seller    = (int)$produk['id_seller'];
    $nama_pembeli = mysqli_real_escape_string($conn, $user_login['nama_lengkap']);
    $alamat       = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kurir        = mysqli_real_escape_string($conn, $_POST['kurir']);
    $metode       = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
    $ongkir       = (float) $_POST['ongkir'];
    $harga        = (float) $_POST['harga_deal'];
    $total        = $harga + $ongkir + 2000;

    mysqli_query($conn, "INSERT INTO orders (kode_invoice, id_product, id_buyer, id_seller, nama_pembeli, alamat_pembeli, harga_deal, ongkir, total_bayar, kurir, metode_pembayaran, status_order) 
                         VALUES ('$invoice', '$id', '$uid', '$id_seller', '$nama_pembeli', '$alamat', '$harga', '$ongkir', '$total', '$kurir', '$metode', 'Menunggu Pembayaran')");
    
    $id_order_baru = mysqli_insert_id($conn);

    // Tandai produk terjual & hapus dari keranjang
    mysqli_query($conn, "UPDATE products SET status_produk = 'Terjual' WHERE id_product = $id");
    mysqli_query($conn, "DELETE FROM carts WHERE id_product = $id");

    // Arahkan ke halaman pembayaran QRIS / Virtual Account
    header("Location: pembayaran.php?id_order=$id_order_baru");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout Escrow | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <form method="POST" class="container checkout-wrapper">
        <input type="hidden" name="harga_deal" value="<?= $harga_deal ?>">
        <input type="hidden" name="kurir" id="kurir-nama" value="J&T Express Reguler">
        <input type="hidden" name="ongkir" id="kurir-ongkir" value="12000">

        <div class="checkout-left">
            <div class="checkout-card">
                <h3>📍 Alamat Pengiriman Pembeli</h3>
                <textarea name="alamat" class="form-control" rows="2" required><?= htmlspecialchars($user_login['alamat']) ?></textarea>
            </div>

            <div class="checkout-card">
                <h3>📦 Barang yang Dibeli</h3>
                <div class="checkout-item">
                    <img src="<?= htmlspecialchars($produk['foto_produk']) ?>" alt="Produk">
                    <div class="item-details">
                        <h4><?= htmlspecialchars($produk['nama_produk']) ?></h4>
                        <p class="item-seller">Kondisi: <?= $produk['kondisi'] ?> (<?= $produk['skor_kondisi'] ?>/10)</p>
                    </div>
                    <div class="item-price">Rp<?= number_format($harga_deal, 0, ',', '.') ?></div>
                </div>
            </div>

            <div class="checkout-card">
                <h3>🚚 Pilih Metode Pengiriman</h3>
                <select id="shipping-select" class="form-control" onchange="updateOngkir()">
                    <option value="0" data-nama="COD Area Kampus">COD Area Kampus / Ketemu Langsung (Rp0)</option>
                    <option value="12000" data-nama="J&T Express Reguler" selected>J&T Express Reguler (Rp12.000)</option>
                    <option value="15000" data-nama="JNE Reguler">JNE Reguler (Rp15.000)</option>
                </select>
            </div>

            <div class="checkout-card">
                <h3>💳 Pilih Metode Pembayaran (Rekening Bersama ReeMart)</h3>
                <div class="payment-options">
                    <label class="pay-option">
                        <input type="radio" name="metode_pembayaran" value="QRIS ReeMart (Instan)" checked>
                        <div>
                            <strong>QRIS (Semua E-Wallet & Mobile Banking)</strong><br>
                            <small style="color: #64748b;">Scan kode QR menggunakan DANA, GoPay, OVO, ShopeePay, BCA/BRI Mobile</small>
                        </div>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="metode_pembayaran" value="BCA Virtual Account">
                        <div>
                            <strong>BCA Virtual Account</strong><br>
                            <small style="color: #64748b;">Verifikasi otomatis ke rekening escrow ReeMart</small>
                        </div>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="metode_pembayaran" value="BRI Virtual Account">
                        <div>
                            <strong>BRI Virtual Account (BRIVA)</strong><br>
                            <small style="color: #64748b;">Transfer mudah via BRImo atau ATM BRI</small>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="checkout-right">
            <div class="checkout-card">
                <h3>Rincian Pembayaran</h3>
                <div class="summary-row"><span>Harga Deal Barang</span><span>Rp<?= number_format($harga_deal, 0, ',', '.') ?></span></div>
                <div class="summary-row"><span>Ongkos Kirim</span><span id="disp-ongkir">Rp12.000</span></div>
                <div class="summary-row"><span>Biaya Layanan Escrow</span><span>Rp2.000</span></div>
                <hr class="divider">
                <div class="summary-row total-row"><span>Total Bayar</span><span id="disp-total">Rp<?= number_format($harga_deal + 14000, 0, ',', '.') ?></span></div>
                <button type="submit" class="btn-pay-now" style="margin-top: 14px;">Buat Pesanan & Lanjut Bayar</button>
            </div>
        </div>
    </form>

    <script>
        const harga = <?= $harga_deal ?>;
        function updateOngkir() {
            const sel = document.getElementById('shipping-select');
            const ongkir = parseInt(sel.value);
            const nama = sel.options[sel.selectedIndex].getAttribute('data-nama');
            document.getElementById('kurir-ongkir').value = ongkir;
            document.getElementById('kurir-nama').value = nama;
            document.getElementById('disp-ongkir').textContent = 'Rp' + ongkir.toLocaleString('id-ID');
            document.getElementById('disp-total').textContent = 'Rp' + (harga + ongkir + 2000).toLocaleString('id-ID');
        }
    </script>
</body>
</html>