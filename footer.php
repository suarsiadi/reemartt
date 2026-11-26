<footer style="background: #ffffff; border-top: 1px solid #0d9488; margin-top: 50px; padding: 32px 0 20px 0; color: #475569; font-size: 13px;">
    <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 24px;">
        <div>
            <a href="index.php" class="logo" style="display: inline-block; margin-bottom: 8px;">Ree<span>Mart</span></a>
            <p style="line-height: 1.6; color: #0d9488;">Platform marketplace re-commerce barang preloved terpercaya dengan perlindungan rekening bersama (Escrow) dan transparansi kondisi barang.</p>
        </div>
        <div>
            <h4 style="color: #0f172a; font-size: 14px; margin-bottom: 10px;">Keamanan Transaksi</h4>
            <ul style="list-style: none; line-height: 1.8; color: #64748b;">
                <li>🛡️ Perlindungan Dana Escrow 100%</li>
                <li>🔍 Wajib Deskripsi Minus & Skor Kondisi</li>
                <li>⚖️ Mediasi Sengketa & Retur oleh Admin</li>
                <li>🤝 Opsi COD Area Kampus Tanpa Ongkir</li>
            </ul>
        </div>
        <div>
            <h4 style="color: #0f172a; font-size: 14px; margin-bottom: 10px;">Navigasi Cepat</h4>
            <ul style="list-style: none; line-height: 1.8;">
                <li><a href="index.php" style="color: #0d9488; text-decoration: none;">Katalog Barang Preloved</a></li>
                <li><a href="jual.php" style="color: #0d9488; text-decoration: none;">Mulai Jual Barang</a></li>
                <li><a href="pembeli.php" style="color: #0d9488; text-decoration: none;">Cek Status Pesanan</a></li>
                <li><a href="penjual.php" style="color: #0d9488; text-decoration: none;">Dasbor Toko & ReeWallet</a></li>
            </ul>
        </div>
    </div>
    <div class="container" style="border-top: 1px solid #0d9488; padding-top: 14px; text-align: center; color: #94a3b8; font-size: 12px;">
        &copy; <?= date('Y') ?> <strong>ReeMart Indonesia</strong> &bull; Proyek Pengembangan Sistem Marketplace Re-Commerce.
    </div>
</footer>
<!-- BOTTOM NAVIGATION BAR (KHUSUS TAMPILAN MOBILE HP) -->
<nav class="mobile-bottom-nav">
    <a href="index.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
        <span>🏠</span>
        <small>Beranda</small>
    </a>
    <?php if (isset($_SESSION['user_id'])) : ?>
        <a href="wishlist.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'wishlist.php' ? 'active' : '' ?>">
            <span>❤️</span>
            <small>Wishlist</small>
        </a>
        <a href="jual.php" class="bottom-nav-item sell-highlight">
            <span>➕</span>
            <small>Jual</small>
        </a>
        <a href="chat.php" class="bottom-nav-item <?= basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : '' ?>">
            <span>💬</span>
            <small>Chat</small>
        </a>
        <a href="pembeli.php" class="bottom-nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['pembeli.php', 'penjual.php', 'profil.php']) ? 'active' : '' ?>">
            <span>👤</span>
            <small>Akun</small>
        </a>
    <?php else : ?>
        <a href="login.php" class="bottom-nav-item">
            <span>🔑</span>
            <small>Masuk</small>
        </a>
        <a href="register.php" class="bottom-nav-item">
            <span>📝</span>
            <small>Daftar</small>
        </a>
    <?php endif; ?>
</nav>