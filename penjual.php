<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid   = (int)$_SESSION['user_id'];
$pesan = "";

// 1. Proses Input Nomor Resi Pengiriman
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'kirim_resi') {
    $id_order = (int)$_POST['id_order'];
    $resi     = mysqli_real_escape_string($conn, $_POST['no_resi']);
    mysqli_query($conn, "UPDATE orders SET no_resi = '$resi', status_order = 'Sedang Dikirim' WHERE id_order = $id_order AND id_seller = $uid");
    $pesan = "Nomor resi berhasil diperbarui dan dikirim ke pembeli.";
}

// 2. Hitung Total Pendapatan Bersih (96%) dikurangi Total Penarikan Saldo
$total_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(harga_deal * 0.96) AS total FROM orders WHERE id_seller = $uid AND status_order = 'Selesai (Dana Cair)'"))['total'] ?? 0;
$total_ditarik    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah_tarik) AS total FROM withdrawals WHERE id_seller = $uid"))['total'] ?? 0;
$saldo_cair       = max(0, $total_pendapatan - $total_ditarik);
$saldo_tahan      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(harga_deal * 0.96) AS total FROM orders WHERE id_seller = $uid AND status_order IN ('Diproses Penjual (Dana Ditahan)', 'Sedang Dikirim')"))['total'] ?? 0;

// 3. Proses Penarikan Saldo (Withdraw ReeWallet)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'tarik_saldo') {
    $metode    = mysqli_real_escape_string($conn, $_POST['metode_tujuan']);
    $rekening  = mysqli_real_escape_string($conn, $_POST['nomor_rekening']);
    $atas_nama = mysqli_real_escape_string($conn, $_POST['atas_nama']);
    $jumlah    = (float)$_POST['jumlah_tarik'];

    if ($jumlah >= 10000 && $jumlah <= $saldo_cair) {
        mysqli_query($conn, "INSERT INTO withdrawals (id_seller, metode_tujuan, nomor_rekening, atas_nama, jumlah_tarik) 
                             VALUES ($uid, '$metode', '$rekening', '$atas_nama', $jumlah)");
        header("Location: penjual.php?withdraw=1");
        exit;
    } else {
        $pesan = "Nominal penarikan tidak valid (Minimal Rp10.000 dan tidak melebihi saldo siap tarik).";
    }
}

// 4. Proses Hapus Produk Milik Sendiri
if (isset($_GET['hapus_produk'])) {
    $id_hapus = (int)$_GET['hapus_produk'];
    mysqli_query($conn, "DELETE FROM products WHERE id_product = $id_hapus AND id_seller = $uid AND status_produk = 'Tersedia'");
    header("Location: penjual.php");
    exit;
}

// Ambil Data Pesanan, Etalase Produk, dan Riwayat Penarikan
$orders            = mysqli_query($conn, "SELECT orders.*, products.nama_produk, products.foto_produk FROM orders JOIN products ON orders.id_product = products.id_product WHERE orders.id_seller = $uid ORDER BY id_order DESC");
$my_products       = mysqli_query($conn, "SELECT * FROM products WHERE id_seller = $uid ORDER BY id_product DESC");
$riwayat_withdraw  = mysqli_query($conn, "SELECT * FROM withdrawals WHERE id_seller = $uid ORDER BY id_withdraw DESC LIMIT 5");
$total_produk_saya = mysqli_num_rows($my_products);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Toko Saya | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container dashboard-container">
        <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>🏪 Dasbor Toko - <?= htmlspecialchars($_SESSION['user_nama']) ?></h2>
                <p>Kelola etalase produk preloved, pesanan masuk, dan pencairan dana ReeWallet.</p>
            </div>
            <a href="jual.php" class="btn-sell">+ Tambah Produk</a>
        </div>

        <?php if (isset($_GET['updated'])) : ?>
            <div class="success-box" style="margin-bottom: 16px; margin-top: 0;">✓ Detail produk berhasil diperbarui!</div>
        <?php elseif (isset($_GET['withdraw'])) : ?>
            <div class="success-box" style="margin-bottom: 16px; margin-top: 0;">✓ Penarikan saldo ReeWallet berhasil diproses ke rekening tujuan!</div>
        <?php elseif ($pesan) : ?>
            <div class="success-box" style="margin-bottom: 16px; margin-top: 0;"><?= $pesan ?></div>
        <?php endif; ?>

        <!-- STATISTIK SALDO & PRODUK -->
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Saldo ReeWallet (Siap Tarik - Bersih 96%)</span>
                <h3 class="stat-value">Rp<?= number_format($saldo_cair, 0, ',', '.') ?></h3>
                <?php if ($saldo_cair >= 10000) : ?>
                    <button type="button" class="btn-withdraw" onclick="document.getElementById('box-withdraw').style.display='block'">💸 Tarik Saldo Sekarang</button>
                <?php else : ?>
                    <small>Minimal penarikan Rp10.000</small>
                <?php endif; ?>
            </div>
            <div class="stat-card">
                <span class="stat-label">Dana Tertahan di Escrow</span>
                <h3 class="stat-value" style="color: #d97706;">Rp<?= number_format($saldo_tahan, 0, ',', '.') ?></h3>
                <small>Menunggu konfirmasi barang diterima pembeli</small>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Produk di Etalase</span>
                <h3 class="stat-value" style="color: #0284c7;"><?= $total_produk_saya ?> Barang</h3>
                <small>Termasuk barang aktif & terjual</small>
            </div>
        </div>

        <!-- FORM PENARIKAN SALDO REEWALLET (MUNCUL SAAT TOMBOL DIKLIK) -->
        <div id="box-withdraw" class="checkout-card" style="display: none; border: 1.5px solid #2563eb; background: #eff6ff;">
            <h3>💸 Form Pencairan Dana ReeWallet</h3>
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="aksi" value="tarik_saldo">
                <div class="form-row">
                    <div class="form-group">
                        <label>Bank / E-Wallet Tujuan *</label>
                        <select name="metode_tujuan" class="form-control" required>
                            <option value="Bank BCA">Bank BCA</option>
                            <option value="Bank BRI">Bank BRI</option>
                            <option value="Bank Mandiri">Bank Mandiri</option>
                            <option value="DANA">E-Wallet DANA</option>
                            <option value="GoPay">E-Wallet GoPay</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nomor Rekening / No. HP E-Wallet *</label>
                        <input type="text" name="nomor_rekening" class="form-control" placeholder="Contoh: 8890123456" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nama Pemilik Rekening *</label>
                        <input type="text" name="atas_nama" class="form-control" value="<?= htmlspecialchars($_SESSION['user_nama']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nominal Penarikan (Maks: Rp<?= number_format($saldo_cair, 0, ',', '.') ?>) *</label>
                        <input type="number" name="jumlah_tarik" class="form-control" min="10000" max="<?= (int)$saldo_cair ?>" value="<?= (int)$saldo_cair ?>" required>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-confirm">Konfirmasi Pencairan</button>
                    <button type="button" class="btn-retur" onclick="document.getElementById('box-withdraw').style.display='none'">Batal</button>
                </div>
            </form>
        </div>

        <!-- RIWAYAT PENARIKAN SALDO (JIKA ADA) -->
        <?php if (mysqli_num_rows($riwayat_withdraw) > 0) : ?>
        <div class="checkout-card">
            <h3>🧾 Riwayat Penarikan Dana Terakhir</h3>
            <?php while ($w = mysqli_fetch_assoc($riwayat_withdraw)) : ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                    <span><strong><?= htmlspecialchars($w['metode_tujuan']) ?></strong> (<?= htmlspecialchars($w['nomor_rekening']) ?> a.n. <?= htmlspecialchars($w['atas_nama']) ?>)</span>
                    <span><strong style="color: #2563eb;">Rp<?= number_format($w['jumlah_tarik'], 0, ',', '.') ?></strong> &bull; <span class="status-badge status-done"><?= $w['status_withdraw'] ?></span></span>
                </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <!-- BAGIAN 1: ETALASE PRODUK SAYA (DENGAN TOMBOL EDIT & HAPUS) -->
        <div class="checkout-card">
            <h3>🛍️ Etalase Produk Saya (<?= $total_produk_saya ?>)</h3>
            <?php if ($total_produk_saya == 0) : ?>
                <p style="font-size: 13.5px; color: #64748b; margin-top: 8px;">Anda belum menambahkan produk apa pun.</p>
            <?php else : ?>
                <?php while ($p = mysqli_fetch_assoc($my_products)) : ?>
                <div class="order-card" style="box-shadow: none; border: 1px solid #e2e8f0; margin-top: 12px; margin-bottom: 0;">
                    <div class="order-body">
                        <img src="<?= htmlspecialchars($p['foto_produk']) ?>" alt="Foto Produk">
                        <div class="order-info">
                            <h4><?= htmlspecialchars($p['nama_produk']) ?></h4>
                            <p>Harga: <strong>Rp<?= number_format($p['harga'], 0, ',', '.') ?></strong> &bull; Kategori: <?= htmlspecialchars($p['kategori']) ?> &bull; Kondisi: <strong><?= $p['kondisi'] ?> (<?= $p['skor_kondisi'] ?>/10)</strong></p>
                            <small style="color: #b45309;">Minus: <?= htmlspecialchars($p['deskripsi_minus']) ?></small>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                            <?php if ($p['status_produk'] == 'Tersedia') : ?>
                                <span class="status-badge status-done">Tersedia di Katalog</span>
                                <div style="display: flex; gap: 6px;">
                                    <a href="detail.php?id=<?= $p['id_product'] ?>" class="role-link" style="border: 1px solid #cbd5e1; font-size: 12px; padding: 4px 10px;">Lihat</a>
                                    <a href="edit_produk.php?id=<?= $p['id_product'] ?>" class="role-link active" style="font-size: 12px; padding: 4px 10px;">Edit</a>
                                    <a href="penjual.php?hapus_produk=<?= $p['id_product'] ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="btn-retur" style="text-decoration: none; font-size: 12px; padding: 4px 10px;">Hapus</a>
                                </div>
                            <?php else : ?>
                                <span class="status-badge status-pending">Sudah Terjual</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- BAGIAN 2: PESANAN MASUK YANG PERLU DIKIRIM -->
        <div class="checkout-card">
            <h3>📦 Daftar Pesanan Masuk</h3>
            <?php if (mysqli_num_rows($orders) == 0) : ?>
                <p style="font-size: 13.5px; color: #64748b; margin-top: 8px;">Belum ada pesanan masuk untuk produk Anda.</p>
            <?php endif; ?>

            <?php while ($o = mysqli_fetch_assoc($orders)) : ?>
            <div class="order-card" style="box-shadow: none; border: 1px solid #e2e8f0; margin-top: 12px;">
                <div class="order-header">
                    <span><strong><?= $o['kode_invoice'] ?></strong> &bull; Pembeli: <?= htmlspecialchars($o['nama_pembeli']) ?> (<?= $o['kurir'] ?>)</span>
                    <span class="status-badge status-pending"><?= $o['status_order'] ?></span>
                </div>
                <div class="order-body">
                    <img src="<?= htmlspecialchars($o['foto_produk']) ?>" alt="Produk">
                    <div class="order-info">
                        <h4><?= htmlspecialchars($o['nama_produk']) ?></h4>
                        <p>Harga Deal: <strong>Rp<?= number_format($o['harga_deal'], 0, ',', '.') ?></strong> <?= $o['no_resi'] ? "| Resi: <strong>{$o['no_resi']}</strong>" : "" ?></p>
                        <small style="color: #64748b;">Alamat Tujuan: <?= htmlspecialchars($o['alamat_pembeli']) ?></small>
                    </div>
                    <?php if ($o['status_order'] == 'Diproses Penjual (Dana Ditahan)') : ?>
                    <form method="POST" style="display: flex; gap: 6px;">
                        <input type="hidden" name="aksi" value="kirim_resi">
                        <input type="hidden" name="id_order" value="<?= $o['id_order'] ?>">
                        <input type="text" name="no_resi" class="form-control" placeholder="Input No. Resi" required>
                        <button type="submit" class="btn-confirm">Kirim</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>