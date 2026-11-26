<?php
include 'koneksi.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid     = (int)$_SESSION['user_id'];
$id_prod = isset($_GET['id_product']) ? (int)$_GET['id_product'] : 0;
$lawan   = isset($_GET['with']) ? (int)$_GET['with'] : 0;

// Proses Kirim Pesan Baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['pesan'])) {
    $p_id    = (int)$_POST['id_product'];
    $r_id    = (int)$_POST['id_receiver'];
    $isi     = mysqli_real_escape_string($conn, $_POST['pesan']);

    if ($p_id > 0 && $r_id > 0 && $r_id !== $uid) {
        mysqli_query($conn, "INSERT INTO messages (id_product, id_sender, id_receiver, pesan) VALUES ($p_id, $uid, $r_id, '$isi')");
        header("Location: chat.php?id_product=$p_id&with=$r_id");
        exit;
    }
}

// Ambil Daftar Percakapan Unik milik User Ini
$threads = mysqli_query($conn, "SELECT DISTINCT m.id_product, 
                                CASE WHEN m.id_sender = $uid THEN m.id_receiver ELSE m.id_sender END AS lawan_id,
                                p.nama_produk, p.foto_produk, u.nama_lengkap AS nama_lawan
                                FROM messages m
                                JOIN products p ON m.id_product = p.id_product
                                JOIN users u ON u.id_user = (CASE WHEN m.id_sender = $uid THEN m.id_receiver ELSE m.id_sender END)
                                WHERE m.id_sender = $uid OR m.id_receiver = $uid
                                ORDER BY m.id_message DESC");

// Jika membuka chat baru dari detail.php atau mengklik salah satu thread
$produk_aktif = null;
$lawan_aktif  = null;
$isi_chat     = null;

if ($id_prod > 0 && $lawan > 0) {
    mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE id_product = $id_prod AND id_sender = $lawan AND id_receiver = $uid");
    $produk_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id_product = $id_prod"));
    $lawan_aktif  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user, nama_lengkap FROM users WHERE id_user = $lawan"));
    $isi_chat     = mysqli_query($conn, "SELECT messages.*, users.nama_lengkap FROM messages JOIN users ON messages.id_sender = users.id_user 
                                         WHERE id_product = $id_prod AND ((id_sender = $uid AND id_receiver = $lawan) OR (id_sender = $lawan AND id_receiver = $uid)) 
                                         ORDER BY id_message ASC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesan & Tanya Penjual | ReeMart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%232563eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container dashboard-container">
        <div class="dashboard-header">
            <h2>💬 Pesan & Diskusi Produk Preloved</h2>
            <p>Tanyakan detail kondisi fisik, ukuran, atau kesepakatan COD langsung dengan penjual/pembeli.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <!-- KOLOM KIRI: DAFTAR OBROLAN -->
            <div class="checkout-card" style="height: fit-content;">
                <h3>Daftar Percakapan</h3>
                <?php if (mysqli_num_rows($threads) == 0 && !$produk_aktif) : ?>
                    <p style="font-size: 13px; color: #64748b; margin-top: 8px;">Belum ada percakapan. Klik tombol <strong>💬 Chat Penjual</strong> di halaman detail produk untuk memulai.</p>
                <?php endif; ?>

                <?php while ($t = mysqli_fetch_assoc($threads)) : 
                    $is_active = ($t['id_product'] == $id_prod && $t['lawan_id'] == $lawan);
                ?>
                    <a href="chat.php?id_product=<?= $t['id_product'] ?>&with=<?= $t['lawan_id'] ?>" style="display: flex; gap: 10px; align-items: center; padding: 10px; border-radius: 8px; text-decoration: none; color: inherit; border: 1px solid <?= $is_active ? '#2563eb' : '#e2e8f0' ?>; background: <?= $is_active ? '#eff6ff' : '#ffffff' ?>; margin-bottom: 8px;">
                        <img src="<?= htmlspecialchars($t['foto_produk']) ?>" alt="Produk" style="width: 45px; height: 45px; border-radius: 6px; object-fit: cover;">
                        <div style="overflow: hidden;">
                            <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;"><?= htmlspecialchars($t['nama_lawan']) ?></div>
                            <div style="font-size: 12px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($t['nama_produk']) ?></div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>

            <!-- KOLOM KANAN: RUANG CHAT -->
            <div class="checkout-card">
                <?php if ($produk_aktif && $lawan_aktif) : ?>
                    <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0; margin-bottom: 14px;">
                        <img src="<?= htmlspecialchars($produk_aktif['foto_produk']) ?>" alt="Produk" style="width: 50px; height: 50px; border-radius: 6px; object-fit: cover;">
                        <div style="flex: 1;">
                            <h4 style="font-size: 14.5px; color: #0f172a;"><?= htmlspecialchars($lawan_aktif['nama_lengkap']) ?></h4>
                            <p style="font-size: 12.5px; color: #2563eb; font-weight: 600;">Membahas: <?= htmlspecialchars($produk_aktif['nama_produk']) ?> (Rp<?= number_format($produk_aktif['harga'], 0, ',', '.') ?>)</p>
                        </div>
                        <a href="detail.php?id=<?= $produk_aktif['id_product'] ?>" class="role-link" style="border: 1px solid #cbd5e1; font-size: 12px;">Lihat Barang</a>
                    </div>

                    <!-- Kotak Riwayat Pesan -->
                    <div style="height: 300px; overflow-y: auto; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 12px; display: flex; flex-direction: column; gap: 10px;">
                        <?php if (mysqli_num_rows($isi_chat) == 0) : ?>
                            <p style="text-align: center; color: #94a3b8; font-size: 13px; margin-top: 110px;">Mulai obrolan dengan menanyakan kondisi detail atau ketersediaan COD...</p>
                        <?php endif; ?>

                        <?php while ($msg = mysqli_fetch_assoc($isi_chat)) : 
                            $is_me = ($msg['id_sender'] == $uid);
                        ?>
                            <div style="align-self: <?= $is_me ? 'flex-end' : 'flex-start' ?>; max-width: 75%; background: <?= $is_me ? '#2563eb' : '#ffffff' ?>; color: <?= $is_me ? '#ffffff' : '#1e293b' ?>; padding: 10px 14px; border-radius: 10px; border: <?= $is_me ? 'none' : '1px solid #cbd5e1' ?>; font-size: 13.5px;">
                                <div style="font-size: 11px; opacity: 0.8; margin-bottom: 3px; font-weight: 600;">
                                    <?= $is_me ? 'Anda' : htmlspecialchars($msg['nama_lengkap']) ?>
                                </div>
                                <?= nl2br(htmlspecialchars($msg['pesan'])) ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <?php include 'footer.php'; ?>

                    <!-- Form Kirim Pesan -->
                    <form method="POST" style="display: flex; gap: 8px;">
                        <input type="hidden" name="id_product" value="<?= $produk_aktif['id_product'] ?>">
                        <input type="hidden" name="id_receiver" value="<?= $lawan_aktif['id_user'] ?>">
                        <input type="text" name="pesan" class="form-control" placeholder="Tulis pesan untuk <?= htmlspecialchars($lawan_aktif['nama_lengkap']) ?>..." required autocomplete="off">
                        <button type="submit" class="btn-confirm" style="padding: 10px 20px;">Kirim</button>
                    </form>
                <?php else : ?>
                    <div style="text-align: center; padding: 80px 20px; color: #64748b;">
                        <p style="font-size: 15px; font-weight: 600;">Pilih percakapan di sebelah kiri untuk membaca pesan</p>
                        <small>Atau buka halaman produk mana pun dan klik tombol <strong>💬 Chat Penjual</strong>.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>