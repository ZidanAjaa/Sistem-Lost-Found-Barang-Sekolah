<?php
session_start();
require_once __DIR__ . "/koneksi.php";

$isLoggedIn = isset($_SESSION["login"]) && $_SESSION["login"] === true;

$stats = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS total_barang,
        SUM(CASE WHEN status_barang = 'Hilang' THEN 1 ELSE 0 END) AS total_hilang,
        SUM(CASE WHEN status_barang = 'Ditemukan' THEN 1 ELSE 0 END) AS total_ditemukan
    FROM barang
"));

$totalDilaporkan = (int) ($stats["total_barang"] ?? 0);
$totalDitemukan = (int) ($stats["total_ditemukan"] ?? 0);
$tingkatKeberhasilan = $totalDilaporkan > 0 ? round(($totalDitemukan / $totalDilaporkan) * 100) : 0;

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

$categories = mysqli_query($koneksi, "SELECT nama_kategori FROM kategori ORDER BY nama_kategori ASC");

$sqlBarang = "
    SELECT
        b.id,
        b.nama_barang,
        b.foto,
        b.deskripsi,
        b.lokasi_kejadian,
        b.tanggal_kejadian,
        b.status_barang,
        k.nama_kategori,
        u.nama AS pemilik,
        u.kelas,
        u.no_telepon
    FROM barang b
    LEFT JOIN kategori k ON k.id = b.kategori_id
    LEFT JOIN users u ON u.id = b.pemilik_id
    WHERE 1=1
";

if ($search !== "") {
    $sqlBarang .= " AND (
        b.nama_barang LIKE ? OR
        b.deskripsi LIKE ? OR
        b.lokasi_kejadian LIKE ?
    )";
}

if ($category !== "" && strtolower($category) !== "semua") {
    $sqlBarang .= " AND k.nama_kategori = ?";
}

$sqlBarang .= " ORDER BY b.created_at DESC";

$stmtBarang = mysqli_prepare($koneksi, $sqlBarang);

if ($search !== "" && $category !== "" && strtolower($category) !== "semua") {
    $like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmtBarang, "ssss", $like, $like, $like, $category);
} elseif ($search !== "") {
    $like = "%" . $search . "%";
    mysqli_stmt_bind_param($stmtBarang, "sss", $like, $like, $like);
} elseif ($category !== "" && strtolower($category) !== "semua") {
    mysqli_stmt_bind_param($stmtBarang, "s", $category);
}

mysqli_stmt_execute($stmtBarang);
$resultBarang = mysqli_stmt_get_result($stmtBarang);

$barang = [];
while ($row = mysqli_fetch_assoc($resultBarang)) {
    $foto = !empty($row["foto"]) && file_exists(__DIR__ . "/" . $row["foto"])
        ? $row["foto"]
        : "https://placehold.co/600x400/f3f4f6/374151?text=Foto+Barang";

    $barang[] = [
        "id" => (int) $row["id"],
        "nama" => $row["nama_barang"],
        "gambar" => $foto,
        "kategori" => $row["nama_kategori"] ?: "Umum",
        "deskripsi" => $row["deskripsi"],
        "lokasi" => $row["lokasi_kejadian"],
        "tanggal" => date("d F Y", strtotime($row["tanggal_kejadian"])),
        "pemilik" => trim(($row["pemilik"] ?? "") . (!empty($row["kelas"]) ? " - " . $row["kelas"] : "")),
        "no_telepon" => $row["no_telepon"] ?? "",
        "status" => $row["status_barang"] === "Hilang" ? "Belum Ditemukan" : "Ditemukan",
        "status_raw" => $row["status_barang"] ?? "Hilang",
    ];
}
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
            <a href="#kontak">Kontak</a>

            <?php if ($isLoggedIn): ?>
                <a href="profil_user.php">Profil User</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>

        <div class="nav-buttons">
            <a href="<?= $isLoggedIn ? 'barang_crud.php?jenis=Hilang' : 'login.php' ?>" class="btn btn-outline">Lapor Hilang</a>
            <a href="<?= $isLoggedIn ? 'barang_crud.php?jenis=Temuan' : 'login.php' ?>" class="btn btn-orange">Lapor Temuan</a>
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

            <form class="search-box" action="index.php" method="GET">
                <span class="search-icon"></span>
                <input
                    type="text"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Cari nama barang, lokasi..."
                >
                <?php if ($category !== ""): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <?php endif; ?>
                <button type="submit">Cari</button>
            </form>
        </div>

        <div class="hero-image">
  
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

        <!-- Header -->
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

        <!-- Filter Kategori -->
        <div class="category-list">
            <a href="index.php<?= $search !== "" ? '?search=' . urlencode($search) : '' ?>" class="category-btn <?= ($category === "" || strtolower($category) === "semua") ? 'active' : '' ?>">Semua</a>
            <?php while ($rowCat = mysqli_fetch_assoc($categories)): ?>
                <?php $namaKategori = $rowCat['nama_kategori']; ?>
                <a href="index.php?category=<?= urlencode($namaKategori) ?><?= $search !== "" ? '&search=' . urlencode($search) : '' ?>" class="category-btn <?= strtolower($category) === strtolower($namaKategori) ? 'active' : '' ?>"><?= htmlspecialchars($namaKategori) ?></a>
            <?php endwhile; ?>
        </div>

        <!-- Daftar Barang -->
        <div class="barang-grid">

            <?php foreach ($barang as $item): ?>

                <article class="barang-card">

                    <!-- Gambar Barang -->
                    <div class="barang-image">

                        <img
                            src="<?= htmlspecialchars($item["gambar"]); ?>"
                            alt="<?= htmlspecialchars($item["nama"]); ?>"
                            style="width:100%;height:220px;object-fit:cover;"
                        >

                        <span class="status-badge <?= 
                            $item["status_raw"] === "Ditemukan"
                            ? "status-found"
                            : "status-lost";
                        ?>">
                            <?= htmlspecialchars($item["status"]); ?>
                        </span>

                    </div>

                    <!-- Isi Card -->
                    <div class="barang-content">

                        <span class="category-label">
                            <?= htmlspecialchars($item["kategori"]); ?>
                        </span>

                        <h3>
                            <?= htmlspecialchars($item["nama"]); ?>
                        </h3>

                        <p class="barang-description">
                            <?= htmlspecialchars($item["deskripsi"]); ?>
                        </p>

                        <!-- Informasi Barang -->
                        <div class="barang-info">

                            <span>
                                <i class="fa-solid fa-location-dot"></i>
                                <?= htmlspecialchars($item["lokasi"]); ?>
                            </span>

                            <span>
                                <i class="fa-regular fa-calendar"></i>
                                <?= htmlspecialchars($item["tanggal"]); ?>
                            </span>

                        </div>

                        <!-- Footer Card -->
                        <div class="barang-footer">

                            <span class="owner">
                                <?= htmlspecialchars($item["pemilik"] ?: "Pelapor"); ?>
                            </span>

                            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:flex-end;">
                                <a href="detail_barang.php?id=<?= (int) $item['id'] ?>" class="contact-btn" style="background:#eef2ff; color:#2c2f5d;">
                                    Detail
                                </a>

                                <?php if (!empty($item["no_telepon"])): ?>
                                    <a
                                        href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D+/', '', $item["no_telepon"])) ?>?text=<?= urlencode(
                                            'Halo, saya melihat barang "' .
                                            $item["nama"] .
                                            '" di LostFound.sch. Saya ingin menghubungi terkait barang tersebut.'
                                        ); ?>"
                                        target="_blank"
                                        class="contact-btn"
                                    >
                                        <i class="fa-brands fa-whatsapp"></i>
                                        Hubungi
                                    </a>
                                <?php else: ?>
                                    <span class="contact-btn" style="opacity:0.7; pointer-events:none;">
                                        <i class="fa-brands fa-whatsapp"></i>
                                        Tidak Tersedia
                                    </span>
                                <?php endif; ?>
                            </div>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

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
                <a href="<?= $isLoggedIn ? 'barang_crud.php?jenis=Temuan' : 'login.php' ?>" class="btn btn-orange">Laporkan Barang Temuan</a>
                <a href="<?= $isLoggedIn ? 'barang_crud.php?jenis=Hilang' : 'login.php' ?>" class="btn btn-white">Laporkan Barang Hilang</a>
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
tess
