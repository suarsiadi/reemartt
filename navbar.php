<?php
$jml_chat_baru    = 0;
$jml_pesanan_toko = 0;
$jml_sengketa     = 0;

if (isset($_SESSION['user_id'])) {
    $nav_uid = (int)$_SESSION['user_id'];
    
    // 1. Hitung pesan chat belum dibaca
    $q_chat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM messages WHERE id_receiver = $nav_uid AND is_read = 0"));
    $jml_chat_baru = (int)$q_chat['total'];

    // 2. Hitung pesanan masuk yang perlu segera dikirim penjual
    $q_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE id_seller = $nav_uid AND status_order = 'Diproses Penjual (Dana Ditahan)'"));
    $jml_pesanan_toko = (int)$q_order['total'];

    // 3. Hitung sengketa aktif (khusus admin)
    if ($_SESSION['user_role'] == 'admin') {
        $q_disp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE status_order = 'Dalam Mediasi Admin'"));
        $jml_sengketa = (int)$q_disp['total'];
    }
}
?>
<header class="navbar">
    <div class="container nav-content">
        <a href="index.php" class="logo">Ree<span>Mart</span></a>
        
        <div class="role-switcher">
            <a href="index.php" class="role-link">Katalog</a>
            
            <?php if (isset($_SESSION['user_id'])) : ?>
                <a href="keranjang.php" class="role-link">🛒 Keranjang</a>
                <a href="wishlist.php" class="role-link">❤️ Wishlist</a>
                <a href="chat.php" class="role-link">
                    💬 Chat<?php if ($jml_chat_baru > 0) : ?><span class="notif-badge"><?= $jml_chat_baru ?></span><?php endif; ?>
                </a>
                <a href="pembeli.php" class="role-link">Pesanan</a>
                <a href="penjual.php" class="role-link">
                    Toko Saya<?php if ($jml_pesanan_toko > 0) : ?><span class="notif-badge"><?= $jml_pesanan_toko ?></span><?php endif; ?>
                </a>
                
                <?php if ($_SESSION['user_role'] == 'admin') : ?>
                    <a href="admin.php" class="role-link">
                        Panel Admin<?php if ($jml_sengketa > 0) : ?><span class="notif-badge"><?= $jml_sengketa ?></span><?php endif; ?>
                    </a>
                <?php endif; ?>
                
                <a href="profil.php" class="role-link">👤 <?= htmlspecialchars($_SESSION['user_nama']) ?></a>
                <a href="jual.php" class="btn-sell">+ Jual</a>
                <a href="logout.php" class="btn-retur" style="text-decoration: none; padding: 6px 10px;">Keluar</a>
            <?php else : ?>
                <a href="login.php" class="role-link">Masuk</a>
                <a href="register.php" class="btn-sell">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>