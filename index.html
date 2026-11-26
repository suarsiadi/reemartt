<?php
include 'koneksi.php';
$jml_chat_baru    = 0;
$jml_pesanan_toko = 0;
$jml_sengketa     = 0;

if (isset($_SESSION['user_id'])) {
    $nav_uid = (int)$_SESSION['user_id'];
    
    $q_chat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM messages WHERE id_receiver = $nav_uid AND is_read = 0"));
    $jml_chat_baru = (int)$q_chat['total'];

    $q_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE id_seller = $nav_uid AND status_order = 'Diproses Penjual (Dana Ditahan)'"));
    $jml_pesanan_toko = (int)$q_order['total'];

    if ($_SESSION['user_role'] == 'admin') {
        $q_disp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE status_order = 'Dalam Mediasi Admin'"));
        $jml_sengketa = (int)$q_disp['total'];
    }
}
$query = mysqli_query($conn, "SELECT * FROM products WHERE status_produk = 'Tersedia' ORDER BY id_product DESC");
$jumlah_produk = mysqli_num_rows($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReeMart - Belanja Preloved Aman & Terkurasi</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='22' fill='%230d9488'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' fill='white' font-family='Arial, sans-serif' font-weight='900' font-size='62'>R</text></svg>">
</head>
<body>

    <header class="navbar">
        <div class="container nav-content">
            <a href="index.php" class="logo">Ree<span>Mart</span></a>
            
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Cari barang preloved..." onkeyup="filterProduk()">
                <button type="button" onclick="filterProduk()">Cari</button>
            </div>

            <div class="role-switcher">
    <a href="index.php" class="role-link active">Katalog</a>
    
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

    <section class="container banner-section">
        <div class="hero-banner">
            <h1>Belanja Barang Preloved Tanpa Rasa Was-Was</h1>
            <p>Dana ditahan sistem ReeMart (Escrow) hingga barang tiba dan sesuai deskripsi kondisi.</p>
        </div>
    </section>

    <section class="container filter-section">
        <div class="filter-group" id="category-filters">
            <span class="filter-label">Kategori:</span>
            <button type="button" class="chip active" onclick="pilihKategori('Semua', this)">Semua</button>
            <button type="button" class="chip" onclick="pilihKategori('Fashion', this)">Fashion & Thrift</button>
            <button type="button" class="chip" onclick="pilihKategori('Gadget', this)">Gadget & Elektronik</button>
            <button type="button" class="chip" onclick="pilihKategori('Buku', this)">Buku Kuliah</button>
            <button type="button" class="chip" onclick="pilihKategori('Kos', this)">Perlengkapan Kos</button>
        </div>
        <div class="filter-group" id="condition-filters">
            <span class="filter-label">Kondisi Barang:</span>
            <button type="button" class="chip-outline" onclick="pilihKondisi('Semua', this)">Semua Kondisi</button>
            <button type="button" class="chip-outline" onclick="pilihKondisi('Like New', this)">Like New</button>
            <button type="button" class="chip-outline" onclick="pilihKondisi('Good', this)">Good</button>
            <button type="button" class="chip-outline" onclick="pilihKondisi('Fair', this)">Fair</button>
        </div>
    </section>
<!-- HERO BANNER MODERN -->
    <div class="container">
        <div class="hero-banner">
            <div>
                <h1>Temukan Barang Preloved Berkualitas Tanpa Was-Was</h1>
                <p>Setiap transaksi dilindungi sistem Rekening Bersama (Escrow) ReeMart. Dana ditahan aman hingga barang tiba sesuai deskripsi fisik dan catatan kondisi.</p>
            </div>
            <div>
                <a href="jual.php" class="btn-sell" style="padding: 12px 22px; font-size: 14px;">+ Mulai Jual Barang</a>
            </div>
        </div>
    </div>
    <main class="container product-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 class="section-title" style="margin-bottom: 0;">Katalog Preloved Terbaru (Database Live)</h2>
            <span id="product-count" style="font-size: 13px; color: #64748b; font-weight: 600;">Menampilkan <?= $jumlah_produk ?> barang</span>
        </div>
        
        <div class="product-grid" id="product-grid">
            <?php while ($row = mysqli_fetch_assoc($query)) : 
                $badge_class = 'like-new';
                if ($row['kondisi'] == 'Good') $badge_class = 'good';
                if ($row['kondisi'] == 'Fair') $badge_class = 'fair';
            ?>
            <div class="product-card" data-category="<?= htmlspecialchars($row['kategori']) ?>" data-condition="<?= htmlspecialchars($row['kondisi']) ?>" onclick="window.location.href='detail.php?id=<?= $row['id_product'] ?>'">
                <div class="product-img">
                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($row['kondisi']) ?> <?= $row['skor_kondisi'] ?>/10</span>
                    <img src="<?= htmlspecialchars($row['foto_produk']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
                </div>
                <div class="product-info">
                    <h3 class="product-title"><?= htmlspecialchars($row['nama_produk']) ?></h3>
                    <p class="product-price">Rp<?= number_format($row['harga'], 0, ',', '.') ?></p>
                    <p class="product-minus">Minus: <?= htmlspecialchars($row['deskripsi_minus']) ?></p>
                    <div class="product-footer">
                        <span>📍 <?= htmlspecialchars($row['kota_penjual']) ?></span>
                        <span class="nego-tag">Bisa Nego</span>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>
    <?php include 'footer.php'; ?>

    <script>
        let kategoriAktif = 'Semua';
        let kondisiAktif = 'Semua';

        function pilihKategori(kategori, tombol) {
            kategoriAktif = kategori;
            document.querySelectorAll('#category-filters .chip').forEach(btn => btn.classList.remove('active'));
            tombol.classList.add('active');
            filterProduk();
        }

        function pilihKondisi(kondisi, tombol) {
            kondisiAktif = kondisi;
            document.querySelectorAll('#condition-filters .chip-outline').forEach(btn => {
                btn.style.background = 'white'; btn.style.color = '#334155'; btn.style.borderColor = '#cbd5e1';
            });
            tombol.style.background = '#eff6ff'; tombol.style.color = '#2563eb'; tombol.style.borderColor = '#2563eb';
            filterProduk();
        }

        function filterProduk() {
            const kataKunci = document.getElementById('search-input').value.toLowerCase();
            const semuaKartu = document.querySelectorAll('.product-card');
            let jumlahTampil = 0;

            semuaKartu.forEach(kartu => {
                const judul = kartu.querySelector('.product-title').textContent.toLowerCase();
                const kategori = kartu.getAttribute('data-category');
                const kondisi = kartu.getAttribute('data-condition');

                if (judul.includes(kataKunci) && (kategoriAktif === 'Semua' || kategori === kategoriAktif) && (kondisiAktif === 'Semua' || kondisi === kondisiAktif)) {
                    kartu.style.display = 'block';
                    jumlahTampil++;
                } else {
                    kartu.style.display = 'none';
                }
            });
            document.getElementById('product-count').textContent = `Menampilkan ${jumlahTampil} barang`;
        }

    </script>
</body>
</html>