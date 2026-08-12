<?php
// ======================================================
// LOSTFOUND.SCH - LANDING PAGE
// Sistem Lost & Found SMK PGRI 3 Tlogomas Malang
// ======================================================

// Data statistik
$totalDilaporkan = 312;
$totalDitemukan = 200;
$tingkatKeberhasilan = 99;
$rataProses = "2.4 Hari";

// Data barang
$barang = [
    [
        "nama" => "Tas Ransel Hitam",
        "gambar" => "img/tas.jpg",
        "kategori" => "Tas & Dompet",
        "deskripsi" => "Tas ransel warna hitam dengan gantungan kunci kecil di bagian depan.",
        "lokasi" => "Kelas B.3",
        "tanggal" => "20 Agustus 2026",
        "pemilik" => "Rizky A. - XI DKVA",
        "status" => "Belum Ditemukan"
    ],
    [
        "nama" => "Tumbler Warna Hitam & Putih",
        "gambar" => "img/tumbler.jpg",
        "kategori" => "Lainnya",
        "deskripsi" => "Tumbler berwarna hitam dan putih berukuran 1000 ml yang ditempeli stiker.",
        "lokasi" => "Kelas A.2.1",
        "tanggal" => "08 Juli 2026",
        "pemilik" => "Zidan - XI RPLA",
        "status" => "Ditemukan"
    ],
    [
        "nama" => "AirPods Pro Putih",
        "gambar" => "img/airpods.jpg",
        "kategori" => "Elektronik",
        "deskripsi" => "Earphone Apple warna putih yang hilang saat olahraga.",
        "lokasi" => "Lapangan C",
        "tanggal" => "22 Mei 2026",
        "pemilik" => "Nesya R. - X TKJA",
        "status" => "Ditemukan"
    ],
    [
        "nama" => "Dompet Warna Hitam",
        "gambar" => "img/dompet.jpg",
        "kategori" => "Tas & Dompet",
        "deskripsi" => "Dompet berwarna hitam dengan gantungan kunci berbentuk love.",
        "lokasi" => "Kelas D.2",
        "tanggal" => "13 Mei 2026",
        "pemilik" => "Rizky H. - XI RPLB",
        "status" => "Ditemukan"
    ],
    [
        "nama" => "Bekal Makanan",
        "gambar" => "img/bekal.jpg",
        "kategori" => "Lainnya",
        "deskripsi" => "Bekal makanan berwarna cokelat yang hilang saat istirahat.",
        "lokasi" => "Lab Oracle",
        "tanggal" => "09 Agustus 2026",
        "pemilik" => "Zidan - XI DKVA",
        "status" => "Belum Ditemukan"
    ],
    [
        "nama" => "Kunci Motor",
        "gambar" => "img/kunci.jpg",
        "kategori" => "Lainnya",
        "deskripsi" => "Kunci motor dengan gantungan karakter yang ditemukan di sekitar sekolah.",
        "lokasi" => "Kelas C.4.2",
        "tanggal" => "24 September 2026",
        "pemilik" => "Putra R. - X KJA",
        "status" => "Ditemukan"
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LostFound.sch - Lost & Found SMK PGRI 3 Tlogomas</title>

    <meta
        name="description"
        content="Platform resmi pelaporan barang hilang dan temuan di SMK PGRI 3 Tlogomas Malang."
    >

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- ======================================================
     NAVBAR
====================================================== -->
<header class="navbar">

    <div class="container navbar-inner">

        <!-- Logo -->
        <a href="index.php" class="logo">
            <span class="logo-icon">L</span>
            <span class="logo-text">
                LostFound<span>.sch</span>
            </span>
        </a>

        <!-- Menu -->
        <nav class="nav-menu">
            <a href="#daftar-barang">Daftar Barang</a>
            <a href="#cara-kerja">Cara Kerja</a>
            <a href="#kontak">Kontak</a>
        </nav>

        <!-- Tombol -->
        <div class="nav-buttons">
            <a href="login.php" class="btn btn-outline">
                Lapor Hilang
            </a>

            <a href="login.php" class="btn btn-orange">
                Lapor Temuan
            </a>
        </div>

    </div>

</header>


<main>

<!-- ======================================================
     HERO SECTION
====================================================== -->
<section class="hero">

    <div class="container hero-inner">

        <!-- Hero Text -->
        <div class="hero-content">

            <div class="school-badge">
                
            </div>

            <h1>
                Kehilangan
                <span>Barang</span>
                <br>
                <strong>di Sekolah?</strong>
            </h1>

            <p class="hero-description">
                Platform resmi pelaporan barang hilang dan temuan
                di SMK PGRI 3 Tlogomas Malang. Cepat, mudah, dan
                terpercaya — bantu sesama teman menemukan
                barangnya kembali.
            </p>

            <!-- Search -->
            <form class="search-box" action="index.php" method="GET">

                <span class="search-icon">🔍</span>

                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama barang, lokasi..."
                    autocomplete="off"
                >

                <button type="submit">
                    Cari
                </button>

            </form>

        </div>


        <!-- Hero Image -->
        <div class="hero-image">

            <img
                src="img/siswa1.png"
                alt="Siswa SMK mencari barang yang hilang"
            >

        </div>

    </div>

</section>


<!-- ======================================================
     STATISTIK
====================================================== -->
<section class="statistics">

    <div class="container statistics-grid">

        <div class="stat-card">

            <div class="stat-icon">
                📦
            </div>

            <div class="stat-number">
                <?= $totalDilaporkan; ?>
            </div>

            <div class="stat-title">
                Barang Dilaporkan
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ✅
            </div>

            <div class="stat-number">
                <?= $totalDitemukan; ?>
            </div>

            <div class="stat-title">
                Barang Ditemukan
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🏆
            </div>

            <div class="stat-number">
                <?= $tingkatKeberhasilan; ?>%
            </div>

            <div class="stat-title">
                Tingkat Keberhasilan
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                ⚡
            </div>

            <div class="stat-number">
                <?= $rataProses; ?>
            </div>

            <div class="stat-title">
                Rata - Rata Proses
            </div>

        </div>

    </div>

</section>


<!-- ======================================================
     DAFTAR BARANG
====================================================== -->
<section class="barang-section" id="daftar-barang">

    <div class="container">

        <!-- Header -->
        <div class="section-header">

            <div>
                <h2>Daftar Barang</h2>

                <p>
                    <?= count($barang); ?> barang ditampilkan
                </p>
            </div>

            <div class="filter-status">

                <button class="filter-btn active">
                    Semua
                </button>

                <button class="filter-btn">
                    Hilang
                </button>

                <button class="filter-btn">
                    Ditemukan
                </button>

            </div>

        </div>


        <!-- Kategori -->
        <div class="category-list">

            <button class="category-btn active">
                Semua
            </button>

            <button class="category-btn">
                Tas & Dompet
            </button>

            <button class="category-btn">
                Elektronik
            </button>

            <button class="category-btn">
                Alat Tulis
            </button>

            <button class="category-btn">
                Pakaian
            </button>

            <button class="category-btn">
                Dokumen
            </button>

            <button class="category-btn">
                Lainnya
            </button>

        </div>


        <!-- Cards -->
        <div class="barang-grid">

            <?php foreach ($barang as $item): ?>

                <article class="barang-card">

                    <!-- Gambar -->
                    <div class="barang-image">

                        <img
                            src="<?= htmlspecialchars($item['gambar']); ?>"
                            alt="<?= htmlspecialchars($item['nama']); ?>"
                        >

                        <span
                            class="status-badge
                            <?= $item['status'] === 'Ditemukan'
                                ? 'status-found'
                                : 'status-lost'; ?>"
                        >
                            <?= htmlspecialchars($item['status']); ?>
                        </span>

                    </div>


                    <!-- Isi Card -->
                    <div class="barang-content">

                        <span class="category-label">
                            <?= htmlspecialchars($item['kategori']); ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($item['nama']); ?>
                        </h3>

                        <p class="barang-description">
                            <?= htmlspecialchars($item['deskripsi']); ?>
                        </p>


                        <div class="barang-info">

                            <span>
                                📍
                                <?= htmlspecialchars($item['lokasi']); ?>
                            </span>

                            <span>
                                📅
                                <?= htmlspecialchars($item['tanggal']); ?>
                            </span>

                        </div>


                        <div class="barang-footer">

                            <span class="owner">
                                👤
                                <?= htmlspecialchars($item['pemilik']); ?>
                            </span>

                            <a
                                href="login.php"
                                class="detail-btn"
                            >
                                Lihat Detail
                            </a>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>


        <div class="center-button">

            <a href="login.php" class="view-all-btn">
                Lihat Semua Barang →
            </a>

        </div>

    </div>

</section>


<!-- ======================================================
     CARA KERJA
====================================================== -->
<section class="cara-section" id="cara-kerja">

    <div class="container">

        <div class="cara-top">

            <div class="cara-text">

                <h2>
                    Konsep Cara Kerja
                </h2>

                <p>
                    Proses yang sederhana, cepat, dan mudah dipahami
                    untuk membantu kamu melaporkan barang yang hilang,
                    mencari barang yang ditemukan, mencocokkan informasi
                    barang dengan pemiliknya, hingga membantu mengembalikan
                    barang temuan kepada pemiliknya dengan aman dan
                    terpercaya.
                </p>

            </div>


            <div class="cara-image">

                <img
                    src="img/siswa2.png"
                    alt="Siswa sedang memikirkan proses pencarian barang"
                >

            </div>

        </div>


        <!-- Langkah -->
        <div class="steps">

            <!-- Step 1 -->
            <div class="step-card">

                <div class="step-number">
                    1
                </div>

                <h3>
                    Laporkan
                </h3>

                <p>
                    Isi formulir laporan barang hilang
                    atau barang yang kamu temukan
                    dengan detail lengkap.
                </p>

            </div>


            <!-- Step 2 -->
            <div class="step-card">

                <div class="step-number">
                    2
                </div>

                <h3>
                    Cari & Cocokkan
                </h3>

                <p>
                    Tim kami akan memeriksa laporan
                    dan mencocokkannya dengan data
                    barang yang masuk.
                </p>

            </div>


            <!-- Step 3 -->
            <div class="step-card">

                <div class="step-number">
                    3
                </div>

                <h3>
                    Dihubungi
                </h3>

                <p>
                    Jika terdapat kecocokan, admin
                    akan menghubungi pihak terkait
                    untuk proses pengambilan barang.
                </p>

            </div>


            <!-- Step 4 -->
            <div class="step-card">

                <div class="step-number">
                    4
                </div>

                <h3>
                    Ambil Barang
                </h3>

                <p>
                    Datang ke ruang piket dengan
                    membawa bukti kepemilikan dan
                    ambil barangmu kembali.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- ======================================================
     CTA / LAPOR BARANG TEMUAN
====================================================== -->
<section class="cta-section">

    <div class="container">

        <div class="cta-box">

            <h2>
                Menemukan Barang Orang Lain?
            </h2>

            <p>
             Jadilah pahlawan bagi temanmu! Jika kamu menemukan barang milik orang lain
             di lingkungan sekolah, segera laporkan barang tersebut melalui LostFound.sch
             agar informasi dapat diketahui oleh pemiliknya dan barang tersebut bisa segera kembali dengan aman.
            </p>


            <div class="cta-buttons">

                <a
                    href="login.php"
                    class="btn btn-orange"
                >
                     Laporkan Barang Temuan
                </a>

                <a
                    href="login.php"
                    class="btn btn-white"
                >
                     Laporkan Barang Hilang
                </a>

            </div>

        </div>

    </div>

</section>




<!-- ======================================================
     FOOTER
====================================================== -->
<footer class="footer">

    <div class="container footer-grid">

        <!-- About -->
        <div class="footer-column">

            <a href="index.php" class="footer-logo">
                LostFound<span>.sch</span>
            </a>

            <p>
                Platform resmi Lost and Found di sekolah.
                Bersama kita ciptakan lingkungan sekolah
                yang jujur, peduli, aman, dan terpercaya.
            </p>

        </div>


        <!-- Navigasi -->
        <div class="footer-column">

            <h3>
                Navigasi
            </h3>

            <a href="#daftar-barang">
                Daftar Barang
            </a>

            <a href="#cara-kerja">
                Cara Kerja
            </a>

            <a href="#kontak">
                Kontak
            </a>

        </div>


        <!-- Laporan -->
        <div class="footer-column">

            <h3>
                Laporan
            </h3>

            <a href="login.php">
                Lapor Hilang
            </a>

            <a href="login.php">
                Lapor Temuan
            </a>

        </div>


        <!-- Sosial Media -->
        <div class="footer-column">

            <h3>
                Ikuti Kami
            </h3>

            <div class="social-icons">

                <a href="#" aria-label="Instagram">
                    ◎
                </a>

                <a href="#" aria-label="WhatsApp">
                    ◉
                </a>

                <a href="#" aria-label="YouTube">
                    ▶
                </a>

            </div>

        </div>

    </div>


    <div class="footer-bottom">

        © 2026 LostFound.sch - All rights reserved.

    </div>

</footer>

</body>
</html>