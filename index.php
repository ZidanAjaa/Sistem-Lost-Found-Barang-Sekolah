<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LostFound.sch - SMK PGRI 3 Tlogomas Malang</title>

    <link rel="stylesheet" href="css/style.css">

    <!-- Font Awesome untuk icon -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
</head>

<body>

<!-- NAVBAR -->

<header class="navbar">

    <div class="container navbar-inner">

        <!-- LOGO -->
        <a href="index.php" class="logo">

            <div class="">
                <i class=""></i>
            </div>

            <div class="logo-text">
                LostFound<span>.sch</span>
            </div>

        </a>


        <!-- MENU -->
        <nav class="nav-menu">

            <a href="#daftar-barang">
                Daftar Barang
            </a>

            <a href="#cara-kerja">
                Cara Kerja
            </a>

            <a href="#kontak">
                Kontak
            </a>

        </nav>


        <!-- BUTTON -->
        <div class="nav-buttons">

            <a href="#" class="btn btn-outline">
                Lapor Hilang
            </a>

            <a href="#" class="btn btn-orange">
                Lapor Temuan
            </a>

        </div>

    </div>

</header>



<!-- HERO -->

<section class="hero">

    <div class="container hero-inner">

        <!-- HERO TEXT -->
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


            <!-- SEARCH -->
            <form class="search-box" action="#" method="GET">

                <span class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>

                <input
                    type="text"
                    name="search"
                    placeholder="Cari nama barang, lokasi..."
                >

                <button type="submit">
                    Cari
                </button>

            </form>

        </div>


        <!-- HERO IMAGE -->
        <div class="hero-image">

            <img
                src="img/siswa1.png"
                alt="Siswa mencari barang hilang"
            >

        </div>

    </div>

</section>



<!-- STATISTICS -->

<section class="statistics">

    <div class="container statistics-grid">


        <!-- STAT 1 -->
        <div class="stat-card">

            <div class="stat-icon">
                
            </div>

            <div class="stat-number">
                312
            </div>

            <div class="stat-title">
                Barang Dilaporkan
            </div>

        </div>


        <!-- STAT 2 -->
        <div class="stat-card">

            <div class="stat-icon">
                
            </div>

            <div class="stat-number">
                200
            </div>

            <div class="stat-title">
                Barang Ditemukan
            </div>

        </div>


        <!-- STAT 3 -->
        <div class="stat-card">

            <div class="stat-icon">
                
            </div>

            <div class="stat-number">
                99%
            </div>

            <div class="stat-title">
                Tingkat Keberhasilan
            </div>

        </div>


        <!-- STAT 4 -->
        <div class="stat-card">

            <div class="stat-icon">
                
            </div>

            <div class="stat-number">
                &lt; 3 Hari
            </div>

            <div class="stat-title">
                Rata - Rata Proses
            </div>

        </div>

    </div>

</section>



<!-- DAFTAR BARANG -->

<section class="barang-section" id="daftar-barang">

    <div class="container">


        <!-- HEADER -->
        <div class="section-header">

            <div>

                <h2>
                    Daftar Barang
                </h2>

                <p>
                    10 barang ditemukan
                </p>

            </div>


            <!-- STATUS -->
            <div class="filter-status">

                <button
                    class="filter-btn active"
                    type="button"
                >
                    Semua
                </button>

                <button
                    class="filter-btn"
                    type="button"
                >
                    Hilang
                </button>

                <button
                    class="filter-btn"
                    type="button"
                >
                    Ditemukan
                </button>

            </div>

        </div>



        <!-- CATEGORY -->
        <div class="category-list">

            <button
                class="category-btn active"
                type="button"
            >
                Semua
            </button>

            <button
                class="category-btn"
                type="button"
            >
                Tas & Dompet
            </button>

            <button
                class="category-btn"
                type="button"
            >
                Elektronik
            </button>

            <button
                class="category-btn"
                type="button"
            >
                Alat Tulis
            </button>

            <button
                class="category-btn"
                type="button"
            >
                Pakaian
            </button>

            <button
                class="category-btn"
                type="button"
            >
                Dokumen
            </button>

        </div>



        <!-- BARANG GRID -->
        <div class="barang-grid">


            <!-- TAS -->

            <article class="barang-card">

                <div class="barang-image">

                    <img
                        src="img/tas.jpg"
                        alt="Tas Ransel Hitam"
                    >

                    <span class="status-badge status-lost">
                        Belum Ditemukan
                    </span>

                </div>


                <div class="barang-content">

                    <span class="category-label">
                        Tas
                    </span>

                    <h3>
                        Tas Ransel Hitam
                    </h3>

                    <p class="barang-description">
                        Tas ransel warna hitam dengan gantungan
                        kunci hello kitty.
                    </p>


                    <div class="barang-info">

                        <span>
                            📍 Kelas XI B 3
                        </span>

                        <span>
                            📅 20 Agustus 2026
                        </span>

                    </div>


                    <div class="barang-footer">

                        <span class="owner">
                            Rizky A - XI DKVA
                        </span>

                        <a href="#" class="detail-btn">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            </article>



            <!-- TUMBLER -->

            <article class="barang-card">

                <div class="barang-image">

                    <img
                        src="img/tumbler.jpg"
                        alt="Tumbler Warna Hitam dan Putih"
                    >

                    <span class="status-badge status-found">
                        Ditemukan
                    </span>

                </div>


                <div class="barang-content">

                    <span class="category-label">
                        Peralatan
                    </span>

                    <h3>
                        Tumbler Warna Hitam & Putih
                    </h3>

                    <p class="barang-description">
                        Tumbler berwarna hitam dan putih
                        berukuran 1000 ml.
                    </p>


                    <div class="barang-info">

                        <span>
                            📍 Kelas A.2.1
                        </span>

                        <span>
                            📅 08 Juli 2026
                        </span>

                    </div>


                    <div class="barang-footer">

                        <span class="owner">
                            Zidan - XI RPLA
                        </span>

                        <a href="#" class="detail-btn">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            </article>



            <!-- AIRPODS -->

            <article class="barang-card">

                <div class="barang-image">

                    <img
                        src="img/airpods.jpg"
                        alt="AirPods Pro Putih"
                    >

                    <span class="status-badge status-found">
                        Ditemukan
                    </span>

                </div>


                <div class="barang-content">

                    <span class="category-label">
                        Elektronik
                    </span>

                    <h3>
                        AirPods Pro Putih
                    </h3>

                    <p class="barang-description">
                        Earphone Apple warna putih yang
                        hilang saat olahraga.
                    </p>


                    <div class="barang-info">

                        <span>
                            📍 Kelas C 3.2
                        </span>

                        <span>
                            📅 21 Mei 2026
                        </span>

                    </div>


                    <div class="barang-footer">

                        <span class="owner">
                            Nesya R - TKJ A
                        </span>

                        <a href="#" class="detail-btn">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            </article>



            <!-- DOMPET -->

            <article class="barang-card">

                <div class="barang-image">

                    <img
                        src="img/dompet.jpg"
                        alt="Dompet Warna Hitam"
                    >

                    <span class="status-badge status-lost">
                        Belum Ditemukan
                    </span>

                </div>


                <div class="barang-content">

                    <span class="category-label">
                        Dompet
                    </span>

                    <h3>
                        Dompet Warna Hitam
                    </h3>

                    <p class="barang-description">
                        Dompet berwarna hitam yang memiliki
                        gantungan kecil berbentuk love.
                    </p>


                    <div class="barang-info">

                        <span>
                            📍 Kelas D.2
                        </span>

                        <span>
                            📅 13 Mei 2026
                        </span>

                    </div>


                    <div class="barang-footer">

                        <span class="owner">
                            Rizky H - XI RPLB
                        </span>

                        <a href="#" class="detail-btn">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            </article>



            <!-- BEKAl -->

            <article class="barang-card">

                <div class="barang-image">

                    <img
                        src="img/bekal.jpg"
                        alt="Bekal Makanan"
                    >

                    <span class="status-badge status-lost">
                        Belum Ditemukan
                    </span>

                </div>


                <div class="barang-content">

                    <span class="category-label">
                        Peralatan
                    </span>

                    <h3>
                        Bekal Makanan
                    </h3>

                    <p class="barang-description">
                        Bekal makanan berwarna cokelat yang
                        hilang saat istirahat.
                    </p>


                    <div class="barang-info">

                        <span>
                            📍 Lab Oracle
                        </span>

                        <span>
                            📅 09 Agustus 2026
                        </span>

                    </div>


                    <div class="barang-footer">

                        <span class="owner">
                            Yona - XI DKVA
                        </span>

                        <a href="#" class="detail-btn">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            </article>



            <!-- KUNCI -->

            <article class="barang-card">

                <div class="barang-image">

                    <img
                        src="img/kunci.jpg"
                        alt="Kunci Motor"
                    >

                    <span class="status-badge status-found">
                        Ditemukan
                    </span>

                </div>


                <div class="barang-content">

                    <span class="category-label">
                        Aksesori
                    </span>

                    <h3>
                        Kunci Motor
                    </h3>

                    <p class="barang-description">
                        Kunci motor dengan gantungan kunci
                        sederhana yang hilang.
                    </p>


                    <div class="barang-info">

                        <span>
                            📍 Kelas C.4.2
                        </span>

                        <span>
                            📅 24 September 2026
                        </span>

                    </div>


                    <div class="barang-footer">

                        <span class="owner">
                            Putra R - XI KJ
                        </span>

                        <a href="#" class="detail-btn">
                            Lihat Detail
                        </a>

                    </div>

                </div>

            </article>

        </div>

    </div>

</section>



<!-- CARA KERJA -->

<section class="cara-section" id="cara-kerja">

    <div class="container">


        <div class="cara-top">


            <!-- TEXT -->
            <div class="cara-text">

                <h2>
                    Konsep Cara Kerja
                </h2>

                <p>

                    Proses yang sederhana, cepat, dan mudah
                    dipahami untuk membantu kamu melaporkan
                    barang yang hilang, mencari barang yang
                    ditemukan, mencocokkan informasi barang
                    dengan pemiliknya, hingga membantu
                    mengembalikan barang temuan kepada
                    pemiliknya dengan aman dan terpercaya.

                </p>

            </div>


            <!-- IMAGE -->
            <div class="cara-image">

                <img
                    src="img/siswa2.png"
                    alt="Siswa mencari barang"
                >

            </div>

        </div>



        <!-- STEPS -->
        <div class="steps">


            <!-- STEP 1 -->
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
                    dengan detail yang lengkap.

                </p>

            </div>


            <!-- STEP 2 -->
            <div class="step-card">

                <div class="step-number">
                    2
                </div>

                <h3>
                    Cari & Cocokan
                </h3>

                <p>

                    Tim kami akan mencocokkan laporan
                    kehilangan dengan barang temuan
                    yang masuk.

                </p>

            </div>


            <!-- STEP 3 -->
            <div class="step-card">

                <div class="step-number">
                    3
                </div>

                <h3>
                    Dihubungi
                </h3>

                <p>

                    Jika ada kecocokan, kami akan
                    menghubungi pemilik untuk proses
                    pengambilan barang.

                </p>

            </div>


            <!-- STEP 4 -->
            <div class="step-card">

                <div class="step-number">
                    4
                </div>

                <h3>
                    Ambil Barang
                </h3>

                <p>

                    Datang ke ruang piket dengan
                    membawa bukti kepemilikan untuk
                    mengambil barangmu kembali.

                </p>

            </div>

        </div>

    </div>

</section>



<!-- CTA -->

<section class="cta-section">

    <div class="container">

        <div class="cta-box">

            <h2>
                Menemukan Barang Orang Lain?
            </h2>

            <p>

                Jadilah pahlawan bagi temanmu Jika kamu
                menemukan barang milik orang lain di
                lingkungan sekolah, segera laporkan barang
                tersebut melalui LostFound.sch agar informasi
                dapat diketahui oleh pemiliknya dan barang
                tersebut bisa segera dikembalikan dengan aman.

            </p>


            <div class="cta-buttons">

                <a href="#" class="btn btn-orange">
                   
                    &nbsp; Laporkan Barang Temuan
                </a>

                <a href="#" class="btn btn-white">
                    
                    &nbsp; Laporkan Barang Hilang
                </a>

            </div>

        </div>

    </div>

</section>



<!-- FOOTER -->

<footer class="footer" id="kontak">

    <div class="container">

        <div class="footer-grid">


            <!-- KOLOM 1 - PROFIL WEBSITE -->

            <div class="">

                <a
                    href="index.php"
                    class="footer-logo"
                >
                    LostFound<span>.sch</span>
                </a>


                <p>

                    Platform resmi Lost & Found SMK PGRI 3
                    Tlogomas Malang yang dirancang untuk
                    membantu siswa dan staf melaporkan,
                    mencari, serta menemukan kembali barang
                    berharga mereka dengan proses yang mudah,
                    cepat, aman, dan terpercaya.

                </p>


                <!-- SOCIAL MEDIA -->

                <div class="">

                    <a
                       
                        
                    >
                        
                    </a>

                    <a
                       
                       
                    >
                        
                    </a>

                    <a
                        
                        
                    >
                       
                    </a>

                </div>

            </div>



            <!-- KOLOM 2 - HUBUNGI KAMI -->

            <div class="footer-column">

                <h3>
                    Hubungi Kami
                </h3>


                <p>
                    Jalan Raya Tlogomas Gang 9 Nomor 29,
                    Malang
                </p>


                <p>
                    Nomor telepon: (0341) 554383
                </p>


                <p>
                    mail.smkpgri3malang@gmail.com
                </p>

            </div>



            <!-- KOLOM 3 - JAM OPERASIONAL -->

            <div class="footer-column operational-column">

                <h3>
                    Jam Operasional
                </h3>


                <div class="operational-row">

                    <span>
                        Senin - Jumat
                    </span>

                    <strong>
                        07.00 - 15.00
                    </strong>

                </div>


                <div class="operational-row">

                    <span>
                        Sabtu - Minggu
                    </span>

                    <strong>
                        Tutup
                    </strong>

                </div>

            </div>


        </div>



        <!-- FOOTER BOTTOM -->

        <div class="footer-bottom">

            © 2026 LostFound.sch - All rights reserved.

        </div>

    </div>

</footer>



<!-- JAVASCRIPT -->

<script>

    /*FILTER STATUS */

    const filterButtons =
        document.querySelectorAll(".filter-btn");

    filterButtons.forEach(function(button) {

        button.addEventListener("click", function() {

            filterButtons.forEach(function(btn) {

                btn.classList.remove("active");

            });

            this.classList.add("active");

        });

    });



    /* FILTER CATEGORY */

    const categoryButtons =
        document.querySelectorAll(".category-btn");

    categoryButtons.forEach(function(button) {

        button.addEventListener("click", function() {

            categoryButtons.forEach(function(btn) {

                btn.classList.remove("active");

            });

            this.classList.add("active");

        });

    });


</script>


</body>
</html>