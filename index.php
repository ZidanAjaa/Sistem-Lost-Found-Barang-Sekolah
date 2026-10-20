<?php
session_start();
require_once __DIR__ . "/koneksi.php";

$isLoggedIn = !empty($_SESSION["login"]);
$fotoProfil = "";

if ($isLoggedIn && !empty($_SESSION["user_id"])) {
    $stmtFoto = mysqli_prepare($koneksi, "SELECT foto_profil FROM users WHERE id = ? LIMIT 1");
    $userId = (int) $_SESSION["user_id"];
    mysqli_stmt_bind_param($stmtFoto, "i", $userId);
    mysqli_stmt_execute($stmtFoto);
    $dataFoto = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtFoto));
    mysqli_stmt_close($stmtFoto);

    if (!empty($dataFoto["foto_profil"])) {
        $pathFoto = "img/profil/" . basename($dataFoto["foto_profil"]);
        if (is_file(__DIR__ . "/" . $pathFoto)) {
            $fotoProfil = $pathFoto;
        }
    }
}

$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

$stats = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT COUNT(*) AS total_barang,
           SUM(status_barang = 'Ditemukan' AND (
               NOT EXISTS (SELECT 1 FROM laporan l WHERE l.barang_id = barang.id)
               OR EXISTS (
                   SELECT 1 FROM laporan l
                   WHERE l.barang_id = barang.id
                     AND l.jenis_laporan = 'Temuan'
                     AND l.status = 'Diproses'
               )
           )) AS total_ditemukan
    FROM barang
")) ?: ["total_barang" => 0, "total_ditemukan" => 0];

$totalDilaporkan = (int)($stats["total_barang"] ?? 0);
$totalDitemukan = (int)($stats["total_ditemukan"] ?? 0);
$tingkatKeberhasilan = $totalDilaporkan
    ? round(($totalDitemukan / $totalDilaporkan) * 100)
    : 0;

$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT no_telepon
    FROM users
    WHERE role = 'admin' AND no_telepon <> ''
    ORDER BY id ASC
    LIMIT 1
"));
$adminWhatsApp = preg_replace('/\D+/', '', $admin["no_telepon"] ?? "");
if (substr($adminWhatsApp, 0, 1) === "0") {
    $adminWhatsApp = "62" . substr($adminWhatsApp, 1);
}

$categories = mysqli_query($koneksi,
    "SELECT nama_kategori FROM kategori ORDER BY nama_kategori"
);


$sql = "SELECT b.id, b.nama_barang, b.foto, b.deskripsi,
        b.lokasi_kejadian, b.tanggal_kejadian, b.status_barang,
        k.nama_kategori
        FROM barang b
        LEFT JOIN kategori k ON k.id=b.kategori_id
        WHERE b.status_barang = 'Ditemukan'
          AND (
              NOT EXISTS (SELECT 1 FROM laporan l WHERE l.barang_id = b.id)
              OR EXISTS (
                  SELECT 1 FROM laporan l
                  WHERE l.barang_id = b.id
                    AND l.jenis_laporan = 'Temuan'
                    AND l.status = 'Diproses'
              )
          )";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (b.nama_barang LIKE ? OR
                    b.deskripsi LIKE ? OR
                    b.lokasi_kejadian LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
    $types = "sss";
}

if ($category !== "" && strtolower($category) !== "semua") {
    $sql .= " AND k.nama_kategori=?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = mysqli_prepare($koneksi, $sql);

if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$barang = [];

while ($row = mysqli_fetch_assoc($result)) {
    $foto = !empty($row["foto"]) &&
            file_exists(__DIR__ . "/" . $row["foto"])
        ? $row["foto"]
        : "https://placehold.co/600x400/f3f4f6/374151?text=Foto+Barang";

    $barang[] = [
        "id" => $row["id"],
        "nama" => $row["nama_barang"],
        "gambar" => $foto,
        "kategori" => $row["nama_kategori"] ?: "Umum",
        "deskripsi" => $row["deskripsi"],
        "lokasi" => $row["lokasi_kejadian"],
        "tanggal" => date("d F Y", strtotime($row["tanggal_kejadian"])),
        "pemilik" => "Diverifikasi Admin",
        "status" => $row["status_barang"] === "Hilang"
            ? "Belum Ditemukan"
            : "Ditemukan",
        "status_raw" => $row["status_barang"]
    ];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LostFound.sch - SMK PGRI 3 Tlogomas Malang</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<!-- NAVBAR -->
<header class="navbar">
    <div class="container navbar-inner">

        <a href="index.php" class="logo">
            <div class="logo-text">LostFound<span>.sch</span></div>
        </a>

        <div class="navbar-actions">
            <nav class="nav-menu" aria-label="Navigasi utama">
                <a href="#daftar-barang">Daftar Barang</a>
                <a href="#kontak">Kontak</a>
            </nav>

            <div class="nav-buttons">
                <a href="<?= $isLoggedIn ? 'lapor_hilang.php' : 'login.php' ?>"
                   class="btn btn-outline">Lapor Hilang</a>

                <a href="<?= $isLoggedIn ? 'lapor_temuan.php' : 'login.php' ?>"
                   class="btn btn-orange">Lapor Temuan</a>

                <?php if ($isLoggedIn): ?>
                    <a href="profil_user.php" class="nav-profile" aria-label="Buka profil pengguna" title="Profil pengguna">
                        <?php if ($fotoProfil): ?>
                            <img src="<?= htmlspecialchars($fotoProfil, ENT_QUOTES, 'UTF-8') ?>" alt="Foto profil">
                        <?php else: ?>
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-login">Login</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</header>


<!-- HERO -->
<section class="hero">
    <div class="container hero-inner">

        <div class="hero-content">
            <h1>
                Kehilangan <span>Barang</span>
                <strong>di Sekolah?</strong>
            </h1>

            <p class="hero-description">
                Platform resmi pelaporan barang hilang dan temuan
                di SMK PGRI 3 Tlogomas Malang. Cepat, mudah, dan terpercaya.
            </p>

        </div>

        <div class="hero-image"></div></div>
</section>

<section class="statistics">
    <div class="container statistics-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $totalDilaporkan ?></div>
            <div class="stat-title">Barang Dilaporkan</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $totalDitemukan ?></div>
            <div class="stat-title">Barang Ditemukan</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $tingkatKeberhasilan ?>%</div>
            <div class="stat-title">Tingkat Keberhasilan</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">&lt; 3 Hari</div>
            <div class="stat-title">Rata - Rata Proses</div>
        </div></div>
</section>

<section class="barang-section" id="daftar-barang">
    <div class="container">

        <div class="section-header">
            <div>
                <h2>Daftar Barang</h2>
                <p id="barang-count"><?= count($barang) ?> barang ditemukan</p>
            </div>
        </div>

        <div class="barang-toolbar">
            <div class="barang-search">
                <form class="search-box" id="barang-search-form">
                <span class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>

                <input
                    type="text"
                    id="barang-search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Cari nama barang, lokasi, atau kategori..."
                    autocomplete="off"
                >

                <button type="submit">Cari</button>
                </form>
            </div>

            <div class="filter-status" aria-label="Filter status barang">
                <button type="button" class="filter-btn active" data-status="semua">
                    Semua
                </button>

                <button type="button" class="filter-btn" data-status="ditemukan">
                    Barang Dipublikasikan
                </button>
            </div>
        </div>

        <div class="category-list">
            <button type="button"
                    class="category-btn active"
                    data-category="semua">
                Semua Kategori
            </button>

            <?php
            mysqli_data_seek($categories, 0);
            while ($cat = mysqli_fetch_assoc($categories)):
            ?>
                <button type="button"
                        class="category-btn"
                        data-category="<?= htmlspecialchars(strtolower($cat["nama_kategori"])) ?>">
                    <?= htmlspecialchars($cat["nama_kategori"]) ?>
                </button>
            <?php endwhile; ?>
        </div>

        <div class="barang-grid" id="barang-grid">
            <?php foreach ($barang as $item): ?>
                <article
                    class="barang-card"
                    data-status="<?= strtolower($item["status_raw"]) ?>"
                    data-category="<?= htmlspecialchars(strtolower($item["kategori"])) ?>"
                >
                    <div class="barang-image">
                        <img
                            src="<?= htmlspecialchars($item["gambar"]) ?>"
                            alt="<?= htmlspecialchars($item["nama"]) ?>"
                        >

                        <span class="status-badge <?= $item["status_raw"] === "Ditemukan" ? "status-found" : "status-lost" ?>">
                            <?= $item["status"] ?>
                        </span>
                    </div>

                    <div class="barang-content">
                        <span class="category-label">
                            <?= htmlspecialchars($item["kategori"]) ?>
                        </span>

                        <h3><?= htmlspecialchars($item["nama"]) ?></h3>

                        <p class="barang-description">
                            <?= htmlspecialchars($item["deskripsi"]) ?>
                        </p>

                        <div class="barang-info">
                            <span>
                                <i class="fa-solid fa-location-dot"></i>
                                <?= htmlspecialchars($item["lokasi"]) ?>
                            </span>

                            <span>
                                <i class="fa-regular fa-calendar"></i>
                                <?= htmlspecialchars($item["tanggal"]) ?>
                            </span>
                        </div>

                        <div class="barang-footer">
                            <span class="owner">
                                <?= htmlspecialchars($item["pemilik"]) ?>
                            </span>

                            <div class="card-buttons">
                                <a
                                    href="detail_barang.php?id=<?= $item["id"] ?>"
                                    class="contact-btn detail-btn"
                                >
                                    Detail
                                </a>

                                <?php if ($adminWhatsApp !== ""): ?>
                                    <?php $pesan = urlencode("Halo admin LostFound.sch, saya ingin mengonfirmasi kepemilikan barang \"" . $item["nama"] . "\" yang dipublikasikan di website."); ?>
                                    <a
                                        href="https://wa.me/<?= htmlspecialchars($adminWhatsApp) ?>?text=<?= $pesan ?>"
                                        target="_blank" rel="noopener"
                                        class="contact-btn"
                                    >
                                        <i class="fa-brands fa-whatsapp"></i>
                                        Hubungi
                                    </a>
                                <?php else: ?>
                                    <span class="contact-btn" style="opacity:.6; pointer-events:none;">
                                        WhatsApp admin belum tersedia
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
            <p>Laporkan barang temuan kepada admin. Informasi akan diperiksa terlebih dahulu dan baru ditampilkan setelah dinyatakan valid.</p>
  <div class="cta-buttons">
                <a href="<?= $isLoggedIn ? 'lapor_temuan.php' : 'login.php' ?>"
                   class="btn btn-orange">
                    Laporkan Barang Temuan
                </a>
         <a href="<?= $isLoggedIn ? 'lapor_hilang.php' : 'login.php' ?>"
                   class="btn btn-white">
                    Laporkan Barang Hilang
                </a>
                <?php if ($adminWhatsApp !== ""): ?>
                    <a href="https://wa.me/<?= htmlspecialchars($adminWhatsApp) ?>?text=<?= urlencode('Halo admin LostFound.sch, saya membutuhkan bantuan terkait barang hilang atau temuan.') ?>"
                       class="btn btn-white" target="_blank" rel="noopener">
                        Hubungi Admin via WhatsApp
                    </a>
                <?php endif; ?>
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
            <p>Platform resmi Lost & Found SMK PGRI 3 Tlogomas Malang
                    untuk membantu siswa dan staf menemukan kembali barang mereka</p>
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
</div></div>
</footer>
<script src="js/script.js"></script>
</body>
</html>
