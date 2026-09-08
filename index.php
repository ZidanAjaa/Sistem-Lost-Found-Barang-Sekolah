<?php
session_start();

$isLoggedIn = isset($_SESSION["login"]) && $_SESSION["login"] === true;

$totalDilaporkan = 312;
$totalDitemukan = 200;
$tingkatKeberhasilan = 99;
$rataProses = "2.4 Hari";

$barang = [
    [
        "nama" => "Tas Ransel Hitam",
        "gambar" => "img/tas.jpg",
        "kategori" => "Tas",
        "deskripsi" => "Tas ransel warna hitam dengan gantungan kunci hello kitty.",
        "lokasi" => "Kelas XI B 3",
        "tanggal" => "20 Agustus 2026",
        "pemilik" => "Rizky A - XI DKVA",
        "status" => "Belum Ditemukan"
    ],
    [
        "nama" => "Tumbler Warna Hitam & Putih",
        "gambar" => "img/tumbler.jpg",
        "kategori" => "Peralatan",
        "deskripsi" => "Tumbler berwarna hitam dan putih berukuran 1000 ml.",
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
        "lokasi" => "Kelas C 3.2",
        "tanggal" => "21 Mei 2026",
        "pemilik" => "Nesya R - TKJ A",
        "status" => "Ditemukan"
    ],
    [
        "nama" => "Dompet Warna Hitam",
        "gambar" => "img/dompet.jpg",
        "kategori" => "Dompet",
        "deskripsi" => "Dompet berwarna hitam yang memiliki gantungan kecil berbentuk love.",
        "lokasi" => "Kelas D.2",
        "tanggal" => "13 Mei 2026",
        "pemilik" => "Rizky H - XI RPLB",
        "status" => "Belum Ditemukan"
    ],
    [
        "nama" => "Bekal Makanan",
        "gambar" => "img/bekal.jpg",
        "kategori" => "Peralatan",
        "deskripsi" => "Bekal makanan berwarna cokelat yang hilang saat istirahat.",
        "lokasi" => "Lab Oracle",
        "tanggal" => "09 Agustus 2026",
        "pemilik" => "Yona - XI DKVA",
        "status" => "Belum Ditemukan"
    ],
    [
        "nama" => "Kunci Motor",
        "gambar" => "img/kunci.jpg",
        "alt" => "Kunci Motor",
        "kategori" => "Aksesoris",
        "deskripsi" => "Kunci motor dengan gantungan kunci sederhana yang hilang.",
        "lokasi" => "Kelas C.4.2",
        "tanggal" => "24 September 2026",
        "pemilik" => "Putra R - XI KJ",
        "status" => "Ditemukan"
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LostFound.sch - SMK PGRI 3 Tlogomas Malang</title>
    <meta
        name="description"
        content="Platform resmi pelaporan barang hilang dan temuan di SMK PGRI 3 Tlogomas Malang."
    >

    <link rel="stylesheet" href="css/style.css">
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
</head>

<body>

<header class="navbar">
    <div class="container navbar-inner">

        <a href="index.php" class="logo">
            <div><i class=""></i></div>
            <div class="logo-text">LostFound<span>.sch</span></div>
        </a>

        <nav class="nav-menu">
            <a href="#daftar-barang">Daftar Barang</a>
            <a href="#cara-kerja">Cara Kerja</a>
            <a href="#kontak">Kontak</a>

            <?php if ($isLoggedIn): ?>
                <a href="profil_user.php">Profil User</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>

        <div class="nav-buttons">
            <a href="#" class="btn btn-outline">Lapor Hilang</a>
            <a href="#" class="btn btn-orange">Lapor Temuan</a>
        </div>

    </div>
</header>

<section class="hero">
    <div class="container hero-inner">

        <div class="hero-content">
            <h1>
                Kehilangan
                <span>Barang</span>
                <strong>di Sekolah?</strong>
            </h1>

            <p class="hero-description">
                Platform resmi pelaporan barang hilang dan temuan
                di SMK PGRI 3 Tlogomas Malang. Cepat, mudah, dan
                terpercaya untuk membantu siswa dan staf menemukan
                kembali barang mereka.
            </p>

            <form class="search-box" action="#" method="GET">
                <span class="search-icon"></span>
                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama barang, lokasi..."
                >
                <button type="submit">Cari</button>
            </form>
        </div>

        <div class="hero-image">
            <img src="img/siswa1.png" alt="Siswa mencari barang hilang">
        </div>

    </div>
</section>

<section class="statistics">
    <div class="container statistics-grid">

        <div class="stat-card">
            <div class="stat-icon"></div>
            <div class="stat-number"><?= $totalDilaporkan; ?></div>
            <div class="stat-title">Barang Dilaporkan</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"></div>
            <div class="stat-number"><?= $totalDitemukan; ?></div>
            <div class="stat-title">Barang Ditemukan</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"></div>
            <div class="stat-number"><?= $tingkatKeberhasilan; ?>%</div>
            <div class="stat-title">Tingkat Keberhasilan</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"></div>
            <div class="stat-number">&lt; 3 Hari</div>
            <div class="stat-title">Rata - Rata Proses</div>
        </div>

    </div>
</section>

<section class="barang-section" id="daftar-barang">
    <div class="container">

        <div class="section-header">
            <div>
                <h2>Daftar Barang</h2>
                <p><?= count($barang); ?> barang ditemukan</p>
            </div>

            <div class="filter-status">
                <button class="filter-btn active" type="button">Semua</button>
                <button class="filter-btn" type="button">Hilang</button>
                <button class="filter-btn" type="button">Ditemukan</button>
            </div>
        </div>

        <div class="category-list">
            <button class="category-btn active" type="button">Semua</button>
            <button class="category-btn" type="button">Tas & Dompet</button>
            <button class="category-btn" type="button">Elektronik</button>
            <button class="category-btn" type="button">Alat Tulis</button>
            <button class="category-btn" type="button">Pakaian</button>
            <button class="category-btn" type="button">Dokumen</button>
        </div>

        <div class="barang-grid">

            <?php foreach ($barang as $item): ?>
                <article class="barang-card">

                    <div class="barang-image">
                        <img
                            src="<?= htmlspecialchars($item["gambar"]); ?>"
                            alt="<?= htmlspecialchars($item["nama"]); ?>"
                        >

                        <span class="status-badge <?= $item["status"] === "Ditemukan" ? "status-found" : "status-lost"; ?>">
                            <?= htmlspecialchars($item["status"]); ?>
                        </span>
                    </div>

                    <div class="barang-content">
                        <span class="category-label">
                            <?= htmlspecialchars($item["kategori"]); ?>
                        </span>

                        <h3><?= htmlspecialchars($item["nama"]); ?></h3>

                        <p class="barang-description">
                            <?= htmlspecialchars($item["deskripsi"]); ?>
                        </p>

                        <div class="barang-info">
                            <span><?= htmlspecialchars($item["lokasi"]); ?></span>
                            <span><?= htmlspecialchars($item["tanggal"]); ?></span>
                        </div>

                        <div class="barang-footer">
                            <span class="owner">
                                <?= htmlspecialchars($item["pemilik"]); ?>
                            </span>

                            <a href="#" class="detail-btn">Lihat Detail</a>
                        </div>
                    </div>

                </article>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<section class="cara-section" id="cara-kerja">
    <div class="container">

        <div class="cara-top">
            <div class="cara-text">
                <h2>Konsep Cara Kerja</h2>

                <p>
                    Proses yang sederhana, cepat, dan mudah dipahami untuk membantu
                    kamu melaporkan barang yang hilang, mencari barang yang ditemukan,
                    mencocokkan informasi barang dengan pemiliknya, hingga membantu
                    mengembalikan barang temuan kepada pemiliknya dengan aman dan terpercaya.
                </p>
            </div>

            <div class="cara-image">
                <img src="img/siswa2.png" alt="Siswa mencari barang">
            </div>
        </div>

        <div class="steps">

            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Laporkan</h3>
                <p>
                    Isi formulir laporan barang hilang atau barang yang kamu temukan
                    dengan detail yang lengkap.
                </p>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Cari & Cocokan</h3>
                <p>
                    Tim kami akan mencocokkan laporan kehilangan dengan barang temuan
                    yang masuk.
                </p>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Dihubungi</h3>
                <p>
                    Jika ada kecocokan, kami akan menghubungi pemilik untuk proses
                    pengambilan barang.
                </p>
            </div>

            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Ambil Barang</h3>
                <p>
                    Datang ke ruang piket dengan membawa bukti kepemilikan
                    untuk mengambil barangmu kembali.
                </p>
            </div>

        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">

        <div class="cta-box">
            <h2>Menemukan Barang Orang Lain?</h2>

            <p>
                Jadilah pahlawan bagi temanmu! Jika kamu menemukan barang milik orang
                lain di lingkungan sekolah, segera laporkan barang tersebut melalui
                LostFound.sch agar informasi dapat diketahui oleh pemiliknya dan barang
                tersebut bisa segera dikembalikan dengan aman serta membantu menciptakan
                lingkungan sekolah yang lebih peduli, bertanggung jawab, dan saling membantu
                antarwarga sekolah.
            </p>

            <div class="cta-buttons">
                <a href="#" class="btn btn-orange">Laporkan Barang Temuan</a>
                <a href="#" class="btn btn-white">Laporkan Barang Hilang</a>
            </div>
        </div>

    </div>
</section>

<footer class="footer" id="kontak">
    <div class="container">

        <div class="footer-grid">

            <div>
                <a href="index.php" class="footer-logo">
                    LostFound<span>.sch</span>
                </a>

                <p>
                    Platform resmi Lost & Found SMK PGRI 3 Tlogomas Malang
                    yang dirancang untuk membantu siswa dan staf melaporkan,
                    mencari, serta menemukan kembali barang berharga mereka
                    dengan proses yang mudah, cepat, aman, dan terpercaya.
                </p>

                <div>
                    <a></a>
                    <a></a>
                    <a></a>
                </div>
            </div>

            <div class="footer-column">
                <h3>Hubungi Kami</h3>
                <p>Jln Tlogomas Gang 9 Nomor 29, Malang</p>
                <p>Nomor telepon: (0341) 554383</p>
                <p>mail.smkpgri3malang@gmail.com</p>
            </div>

            <div class="footer-column operational-column">
                <h3>Jam Operasional</h3>

                <div class="operational-row">
                    <span>Senin - Jumat</span>
                    <strong>07.00 - 15.00</strong>
                </div>

                <div class="operational-row">
                    <span>Sabtu - Minggu</span>
                    <strong>Tutup</strong>
                </div>
            </div>

        </div>
    </div>
</footer>

<script src="js/script.js"></script>

</body>
</html>
