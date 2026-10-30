<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/koneksi.php";

$nama = $_SESSION['nama'] ?? 'User';
$role = strtolower($_SESSION['role'] ?? 'user');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$dashboardNotice = $_SESSION['dashboard_notice'] ?? '';
unset($_SESSION['dashboard_notice']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($role !== 'admin' || !hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Permintaan tidak diizinkan.');
    }

    $returnTo = ($_POST['return_to'] ?? '') === 'laporan_masuk_admin.php'
        ? 'laporan_masuk_admin.php'
        : 'dashboard.php#laporan-masuk';
    $laporanId = (int) ($_POST['laporan_id'] ?? 0);
    $statusBaru = $_POST['status'] ?? '';

    if ($laporanId <= 0 || !in_array($statusBaru, ['Diproses', 'Selesai'], true)) {
        $_SESSION['dashboard_notice'] = 'Permintaan perubahan status tidak valid.';
        header('Location: ' . $returnTo);
        exit;
    }

    $stmtLaporan = mysqli_prepare($koneksi, "SELECT jenis_laporan, status FROM laporan WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtLaporan, "i", $laporanId);
    mysqli_stmt_execute($stmtLaporan);
    $laporanStatus = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtLaporan));
    mysqli_stmt_close($stmtLaporan);

    $bolehDiproses = $laporanStatus && $laporanStatus['status'] === 'Pending' && $statusBaru === 'Diproses';
    $bolehDiselesaikan = $laporanStatus && $laporanStatus['status'] === 'Diproses' && $statusBaru === 'Selesai';

    if (!$bolehDiproses && !$bolehDiselesaikan) {
        $_SESSION['dashboard_notice'] = 'Laporan tidak ditemukan atau statusnya tidak dapat diubah.';
        header('Location: ' . $returnTo);
        exit;
    }

    mysqli_begin_transaction($koneksi);
    try {
        $stmtUpdate = mysqli_prepare($koneksi, "UPDATE laporan SET status = ?, updated_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmtUpdate, "si", $statusBaru, $laporanId);
        mysqli_stmt_execute($stmtUpdate);
        mysqli_stmt_close($stmtUpdate);

        if ($statusBaru === 'Selesai') {
            $stmtBarang = mysqli_prepare($koneksi, "
                UPDATE barang b
                JOIN laporan l ON l.barang_id = b.id
                SET b.status_barang = 'Dikembalikan', b.updated_at = NOW()
                WHERE l.id = ?
            ");
            mysqli_stmt_bind_param($stmtBarang, "i", $laporanId);
            mysqli_stmt_execute($stmtBarang);
            mysqli_stmt_close($stmtBarang);
        }

        mysqli_commit($koneksi);
        $_SESSION['dashboard_notice'] = 'Status laporan berhasil diperbarui.';
    } catch (Throwable $e) {
        mysqli_rollback($koneksi);
        throw $e;
    }

    header('Location: ' . $returnTo);
    exit;
}

$stats = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT
        COUNT(*) AS total_laporan,
        SUM(jenis_laporan = 'Hilang') AS total_hilang,
        SUM(jenis_laporan = 'Temuan') AS total_ditemukan,
        SUM(status = 'Pending') AS menunggu
    FROM laporan
")) ?: [
    'total_laporan' => 0,
    'total_hilang' => 0,
    'total_ditemukan' => 0,
    'menunggu' => 0,
];

$recent_users = mysqli_query($koneksi, "
    SELECT u.id, u.nama, u.kelas, u.role, l.username
    FROM users u
    LEFT JOIN login l ON l.user_id = u.id
    ORDER BY u.id DESC
    LIMIT 5
");

$recent_barang = mysqli_query($koneksi, "
    SELECT b.id, b.nama_barang, b.status_barang, b.lokasi_kejadian, b.tanggal_kejadian,
           u.nama AS pemilik, k.nama_kategori
    FROM barang b
    JOIN users u ON u.id = b.pemilik_id
    LEFT JOIN kategori k ON k.id = b.kategori_id
    ORDER BY b.created_at DESC
    LIMIT 6
");

$recent_categories = mysqli_query($koneksi, "
    SELECT k.id, k.nama_kategori, COUNT(b.id) AS total_barang
    FROM kategori k
    LEFT JOIN barang b ON b.kategori_id = k.id
    GROUP BY k.id, k.nama_kategori
    ORDER BY k.nama_kategori
");

$recent_reports = mysqli_query($koneksi, "
    SELECT l.id, l.jenis_laporan, l.status, l.tanggal_lapor, l.created_at,
           b.nama_barang, b.foto, b.deskripsi, b.status_barang, b.lokasi_kejadian, b.tanggal_kejadian,
           u.nama AS pelapor, u.no_telepon
    FROM laporan l
    JOIN barang b ON b.id = l.barang_id
    JOIN users u ON u.id = l.user_id
    ORDER BY l.created_at DESC
    LIMIT 30
");

$recent_activity = mysqli_query($koneksi, "
    SELECT l.id, l.jenis_laporan, l.status, l.updated_at, b.nama_barang, u.nama AS pelapor
    FROM laporan l
    JOIN barang b ON b.id = l.barang_id
    JOIN users u ON u.id = l.user_id
    ORDER BY l.updated_at DESC
    LIMIT 5
");

$recent_found_items = mysqli_query($koneksi, "
    SELECT b.id, b.nama_barang, b.lokasi_kejadian, b.tanggal_kejadian,
           b.created_at, k.nama_kategori
    FROM barang b
    LEFT JOIN kategori k ON k.id = b.kategori_id
    WHERE b.status_barang = 'Ditemukan'
      AND (
          NOT EXISTS (SELECT 1 FROM laporan l WHERE l.barang_id = b.id)
          OR EXISTS (
              SELECT 1 FROM laporan l
              WHERE l.barang_id = b.id
                AND l.jenis_laporan = 'Temuan'
                AND l.status = 'Diproses'
          )
      )
    ORDER BY b.created_at DESC
    LIMIT 5
");

$my_report_stats = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT
        SUM(jenis_laporan = 'Hilang') AS total_hilang,
        SUM(jenis_laporan = 'Temuan') AS total_ditemukan,
        SUM(status = 'Diproses') AS diproses,
        SUM(status = 'Selesai') AS selesai
    FROM laporan
    WHERE user_id = " . (int) $_SESSION['user_id']
)) ?: ['total_hilang' => 0, 'total_ditemukan' => 0, 'diproses' => 0, 'selesai' => 0];

$my_reports_stmt = mysqli_prepare($koneksi, "
    SELECT l.id, l.jenis_laporan, l.status, l.created_at, b.nama_barang
    FROM laporan l
    JOIN barang b ON b.id = l.barang_id
    WHERE l.user_id = ?
    ORDER BY l.created_at DESC
    LIMIT 10
");
$currentUserId = (int) $_SESSION['user_id'];
mysqli_stmt_bind_param($my_reports_stmt, "i", $currentUserId);
mysqli_stmt_execute($my_reports_stmt);
$my_reports = mysqli_stmt_get_result($my_reports_stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrap">
        <div class="topbar">
            <?php if ($role === 'admin' || $role === 'user'): ?>
                <button type="button" class="sidebar-toggle" aria-label="Buka atau tutup navigasi" aria-controls="dashboard-sidebar" aria-expanded="false">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
            <label class="dashboard-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" id="dashboard-search" placeholder="Cari di dashboard..." autocomplete="off">
            </label>
            <div class="topbar-actions">
                <a class="notification-link" href="<?= $role === 'admin' ? '#aktivitas-terbaru' : '#riwayat-laporan' ?>" aria-label="Lihat laporan terbaru" title="Laporan terbaru">
                    <i class="fa-regular fa-bell" aria-hidden="true"></i>
                </a>
                <a class="profile-link" href="profil_user.php">
                    <span class="profile-avatar" aria-hidden="true"><?= htmlspecialchars(strtoupper(substr($nama, 0, 1))) ?></span>
                    <span class="profile-name"><?= htmlspecialchars($nama) ?></span>
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </a>
            </div>
        </div>

        <?php if ($dashboardNotice !== ''): ?>
            <div class="notice dashboard-notice"><?= htmlspecialchars($dashboardNotice) ?></div>
        <?php endif; ?>

        <div class="dashboard-shell sidebar-collapsed <?= $role === 'admin' ? 'dashboard-shell-admin' : 'dashboard-shell-user' ?>">
                <aside class="sidebar" id="dashboard-sidebar">
                    <div class="brand-box">
                        <div class="brand-icon">LF</div>
                        <div>
                            <h3>LostFound</h3>
                            <small><?= $role === 'admin' ? 'Admin Panel' : 'User Panel' ?></small>
                        </div>
                    </div>

                    <nav class="sidebar-nav" aria-label="Navigasi utama">
                        <?php if ($role === 'admin'): ?>
                            <div class="nav-group">
                                <p class="nav-group-label">Main Menu</p>
                                <a href="#analisa-data" class="active" title="Dashboard"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Dashboard</span></a>
                                <a href="laporan_masuk_admin.php" title="Semua Laporan"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i><span>Semua Laporan</span></a>
                                <a href="laporan_barang_admin.php" title="Data Barang"><i class="fa-solid fa-box" aria-hidden="true"></i><span>Data Barang</span></a>
                                <a href="#kategori-barang" title="Kategori Barang"><i class="fa-solid fa-tags" aria-hidden="true"></i><span>Kategori Barang</span></a>
                            </div>
                            <div class="nav-group">
                                <p class="nav-group-label">Management</p>
                                <a href="#informasi-user" title="Data Pengguna"><i class="fa-solid fa-users" aria-hidden="true"></i><span>Data Pengguna</span></a>
                                <a href="#aktivitas-terbaru" title="Notifikasi"><i class="fa-regular fa-bell" aria-hidden="true"></i><span>Notifikasi</span></a>
                                <a href="#laporan-masuk" title="Laporan &amp; Statistik"><i class="fa-solid fa-chart-column" aria-hidden="true"></i><span>Laporan &amp; Statistik</span></a>
                            </div>
                            <div class="nav-group">
                                <p class="nav-group-label">Account</p>
                                <a href="profil_user.php" title="Profil Admin"><i class="fa-regular fa-user" aria-hidden="true"></i><span>Profil Admin</span></a>
                                <a href="#pengaturan" title="Pengaturan"><i class="fa-solid fa-gear" aria-hidden="true"></i><span>Pengaturan</span></a>
                                <a href="logout.php" title="Logout"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Logout</span></a>
                            </div>
                        <?php else: ?>
                            <div class="nav-group">
                                <p class="nav-group-label">Main Menu</p>
                                <a href="#dashboard-home" class="active" title="Dashboard"><i class="fa-solid fa-house" aria-hidden="true"></i><span>Dashboard</span></a>
                                <a href="index.php#daftar-barang" title="Cari Barang"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span>Cari Barang</span></a>
                                <a href="lapor_hilang.php" title="Laporkan Kehilangan"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>Laporkan Kehilangan</span></a>
                                <a href="lapor_temuan.php" title="Laporkan Penemuan"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>Laporkan Penemuan</span></a>
                            </div>
                            <div class="nav-group">
                                <p class="nav-group-label">My Activities</p>
                                <a href="#laporan-saya" title="Laporan Saya"><i class="fa-solid fa-folder-open" aria-hidden="true"></i><span>Laporan Saya</span></a>
                                <a href="#riwayat-laporan" title="Notifikasi"><i class="fa-regular fa-bell" aria-hidden="true"></i><span>Notifikasi</span></a>
                            </div>
                            <div class="nav-group">
                                <p class="nav-group-label">Account</p>
                                <a href="profil_user.php" title="Profil Saya"><i class="fa-regular fa-user" aria-hidden="true"></i><span>Profil Saya</span></a>
                                <a href="index.php#kontak" title="Bantuan"><i class="fa-regular fa-circle-question" aria-hidden="true"></i><span>Bantuan</span></a>
                                <a href="logout.php" title="Logout"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Logout</span></a>
                            </div>
                        <?php endif; ?>
                    </nav>

                    <div class="sidebar-footer">
                        <small>Login sebagai</small>
                        <strong><?= htmlspecialchars($nama) ?></strong>
                    </div>
                </aside>
                <button type="button" class="sidebar-backdrop" aria-label="Tutup navigasi"></button>

        <?php if ($role === 'admin'): ?>
                <main class="admin-main">
                    <section id="analisa-data" class="section-block">
                        <div class="section-header dashboard-hero">
                            <div>
                                <p class="eyebrow">Lost &amp; Found · Admin</p>
                                <h2>Dashboard</h2>
                                <p class="hero-description">Pantau laporan dan aktivitas barang di lingkungan sekolah.</p>
                            </div>
                            <a href="#laporan-masuk" class="btn btn-hero">Tinjau Laporan</a>
                        </div>

                        <div id="analytics" class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-card-heading"><h3>Total Laporan</h3><span class="stat-icon"><i class="fa-solid fa-clipboard-list" aria-hidden="true"></i></span></div>
                                <p class="value"><?= (int) ($stats['total_laporan'] ?? 0) ?></p>
                                <div class="note">Semua laporan masuk</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-heading"><h3>Laporan Kehilangan</h3><span class="stat-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span></div>
                                <p class="value"><?= (int) ($stats['total_hilang'] ?? 0) ?></p>
                                <div class="note">Laporan barang hilang</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-heading"><h3>Laporan Penemuan</h3><span class="stat-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span></div>
                                <p class="value"><?= (int) ($stats['total_ditemukan'] ?? 0) ?></p>
                                <div class="note">Laporan barang temuan</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-heading"><h3>Menunggu Konfirmasi</h3><span class="stat-icon"><i class="fa-regular fa-clock" aria-hidden="true"></i></span></div>
                                <p class="value"><?= (int) ($stats['menunggu'] ?? 0) ?></p>
                                <div class="note">Belum diverifikasi</div>
                            </div>
                        </div>

                        <div class="panel-grid">
                            <div class="panel">
                                <h2>Ringkasan Analisa</h2>
                                <ul class="mini-list">
                                    <li>
                                        <span>Barang Hilang</span>
                                        <span class="pill pill-hilang"><?= (int) ($stats['total_hilang'] ?? 0) ?> data</span>
                                    </li>
                                    <li>
                                        <span>Barang Ditemukan</span>
                                        <span class="pill pill-ditemukan"><?= (int) ($stats['total_ditemukan'] ?? 0) ?> data</span>
                                    </li>
                                    <li>
                                        <span>Persentase Hilang</span>
                                        <span>
                                            <?php
                                            $persenHilang = ($stats['total_laporan'] > 0)
                                                ? round(((int) ($stats['total_hilang'] ?? 0) / (int) ($stats['total_laporan'] ?? 1)) * 100)
                                                : 0;
                                            echo $persenHilang . '%';
                                            ?>
                                        </span>
                                    </li>
                                    <li>
                                        <span>Persentase Ditemukan</span>
                                        <span>
                                            <?php
                                            $persenDitemukan = ($stats['total_laporan'] > 0)
                                                ? round(((int) ($stats['total_ditemukan'] ?? 0) / (int) ($stats['total_laporan'] ?? 1)) * 100)
                                                : 0;
                                            echo $persenDitemukan . '%';
                                            ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <div class="panel">
                                <h2>Tindakan Cepat</h2>
                                <div class="btn-row vertical">
                                    <a href="laporan_masuk_admin.php" class="btn btn-primary">Verifikasi Laporan Masuk</a>
                                    <a href="laporan_barang_admin.php" class="btn btn-secondary">Lihat Data Barang</a>
                                    <a href="index.php#daftar-barang" class="btn btn-secondary">Lihat Barang Dipublikasikan</a>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="informasi-user" class="section-block">
                        <div class="section-header">
                            <div>
                                <p class="eyebrow">Accounts</p>
                                <h2>Informasi Akun User</h2>
                            </div>
                        </div>

                        <div class="panel">
                            <table class="table-list dashboard-searchable" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Username</th>
                                        <th>Kelas</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($user['nama']) ?></strong>
                                            </td>
                                            <td class="muted"><?= htmlspecialchars($user['username'] ?? '-') ?></td>
                                            <td class="muted"><?= htmlspecialchars($user['kelas'] ?? '-') ?></td>
                                            <td>
                                                <span class="pill <?= strtolower($user['role'] ?? 'user') === 'admin' ? 'pill-ditemukan' : 'pill-hilang' ?>">
                                                    <?= htmlspecialchars(strtoupper($user['role'] ?? 'user')) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="user_detail.php?id=<?= (int) $user['id'] ?>" class="table-link">Lihat Detail</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="kelola-barang" class="section-block">
                        <div class="section-header">
                            <div>
                                <p class="eyebrow">Management</p>
                                <h2>Kelola Data Barang</h2>
                            </div>
                            <a href="laporan_barang_admin.php" class="btn btn-primary">LIHAT DETAIL</a>
                        </div>

                        <div class="panel">
                            <table class="table-list dashboard-searchable" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Pemilik</th>
                                        <th>Kategori</th>
                                        <th>Status</th>
                                        <th>Lokasi</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($barang = mysqli_fetch_assoc($recent_barang)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($barang['nama_barang']) ?></td>
                                            <td class="muted"><?= htmlspecialchars($barang['pemilik']) ?></td>
                                            <td>
                                                <span class="pill pill-progress"><?= htmlspecialchars($barang['nama_kategori'] ?? 'Umum') ?></span>
                                            </td>
                                            <td>
                                                <span class="pill <?= $barang['status_barang'] === 'Hilang' ? 'pill-hilang' : 'pill-ditemukan' ?>">
                                                    <?= htmlspecialchars($barang['status_barang']) ?>
                                                </span>
                                            </td>
                                            <td class="muted"><?= htmlspecialchars($barang['lokasi_kejadian']) ?></td>
                                            <td class="muted"><?= htmlspecialchars(date('d-m-Y', strtotime($barang['tanggal_kejadian']))) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section id="kategori-barang" class="section-block">
                        <div class="section-header">
                            <div>
                                <p class="eyebrow">Data Barang</p>
                                <h2>Kategori Barang</h2>
                            </div>
                        </div>

                        <div class="panel">
                            <ul class="mini-list">
                                <?php if (!$recent_categories || mysqli_num_rows($recent_categories) === 0): ?>
                                    <li><span class="muted">Belum ada kategori barang.</span></li>
                                <?php else: ?>
                                    <?php while ($category = mysqli_fetch_assoc($recent_categories)): ?>
                                        <li>
                                            <span><?= htmlspecialchars($category['nama_kategori']) ?></span>
                                            <span class="pill pill-progress"><?= (int) $category['total_barang'] ?> barang</span>
                                        </li>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </section>

                    <section id="laporan-masuk" class="section-block">
                        <div class="section-header">
                            <div>
                                <p class="eyebrow">Reports</p>
                                <h2>Laporan Terbaru</h2>
                            </div>
                            <a href="laporan_masuk_admin.php" class="btn btn-primary">Buka Halaman Verifikasi</a>
                        </div>

                        <div class="panel">
                            <p class="muted workflow-note">Periksa informasi dan hubungi pelapor untuk konfirmasi. Barang temuan hanya tampil di halaman publik setelah admin memilih “Verifikasi &amp; Publikasikan”.</p>
                            <div class="table-wrap">
                                <table class="table-list dashboard-searchable" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>Barang</th>
                                            <th>Jenis</th>
                                            <th>Pelapor</th>
                                            <th>Tanggal Laporan</th>
                                            <th>Status</th>
                                            <th>Tindakan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!$recent_reports || mysqli_num_rows($recent_reports) === 0): ?>
                                            <tr><td colspan="6" class="muted">Belum ada laporan yang masuk.</td></tr>
                                        <?php else: ?>
                                            <?php while ($report = mysqli_fetch_assoc($recent_reports)): ?>
                                                <?php
                                                $statusLabel = $report['status'] === 'Pending'
                                                    ? 'Menunggu Verifikasi'
                                                    : ($report['status'] === 'Selesai'
                                                        ? 'Sudah Diambil / Selesai'
                                                        : ($report['jenis_laporan'] === 'Temuan' ? 'Dipublikasikan' : 'Dipublikasikan & Dalam Tindak Lanjut'));
                                                $statusClass = $report['status'] === 'Pending'
                                                    ? 'pill-pending'
                                                    : ($report['status'] === 'Selesai' ? 'pill-done' : 'pill-progress');
                                                $nomorPelapor = preg_replace('/\D+/', '', $report['no_telepon'] ?? '');
                                                if (substr($nomorPelapor, 0, 1) === '0') {
                                                    $nomorPelapor = '62' . substr($nomorPelapor, 1);
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($report['nama_barang']) ?></strong><br>
                                                        <span class="muted"><?= htmlspecialchars($report['lokasi_kejadian']) ?> · <?= htmlspecialchars(date('d-m-Y', strtotime($report['tanggal_kejadian']))) ?></span><br>
                                                        <span class="muted"><?= htmlspecialchars(mb_strimwidth($report['deskripsi'] ?? '', 0, 120, '...')) ?></span>
                                                        <?php if (!empty($report['foto'])): ?>
                                                            <br><a class="table-link" href="<?= htmlspecialchars($report['foto']) ?>" target="_blank" rel="noopener">Lihat foto</a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($report['jenis_laporan'] === 'Temuan' ? 'Barang Temuan' : 'Barang Hilang') ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($report['pelapor']) ?><br>
                                                        <?php if ($nomorPelapor !== ''): ?>
                                                            <a class="table-link" href="https://wa.me/<?= htmlspecialchars($nomorPelapor) ?>" target="_blank" rel="noopener">Konfirmasi via WhatsApp</a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="muted"><?= htmlspecialchars(date('d-m-Y', strtotime($report['created_at']))) ?></td>
                                                    <td><span class="pill <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                                    <td>
                                                        <?php if ($report['status'] === 'Pending' || $report['status'] === 'Diproses'): ?>
                                                            <div class="report-actions">
                                                                <a class="table-link" href="detail_laporan_admin.php?id=<?= (int) $report['id'] ?>">Lihat Detail</a>
                                                                <form method="POST" action="dashboard.php#laporan-masuk">
                                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                                    <input type="hidden" name="laporan_id" value="<?= (int) $report['id'] ?>">
                                                                    <?php if ($report['status'] === 'Pending'): ?>
                                                                        <input type="hidden" name="status" value="Diproses">
                                                                        <button type="submit">Verifikasi & Publikasikan</button>
                                                                    <?php else: ?>
                                                                        <input type="hidden" name="status" value="Selesai">
                                                                        <button class="complete" type="submit"><?= $report['jenis_laporan'] === 'Temuan' ? 'Tandai Sudah Diambil' : 'Tandai Selesai' ?></button>
                                                                    <?php endif; ?>
                                                                </form>
                                                            </div>
                                                        <?php else: ?>
                                                            <a class="table-link" href="detail_laporan_admin.php?id=<?= (int) $report['id'] ?>">Lihat Detail</a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section id="aktivitas-terbaru" class="section-block">
                        <div class="section-header">
                            <div>
                                <p class="eyebrow">Aktivitas Sistem</p>
                                <h2>Aktivitas Terbaru</h2>
                            </div>
                        </div>

                        <div class="panel">
                            <ul class="mini-list">
                                <?php if (!$recent_activity || mysqli_num_rows($recent_activity) === 0): ?>
                                    <li><span class="muted">Belum ada aktivitas laporan.</span></li>
                                <?php else: ?>
                                    <?php while ($activity = mysqli_fetch_assoc($recent_activity)): ?>
                                        <li>
                                            <span>
                                                <strong><?= htmlspecialchars($activity['nama_barang']) ?></strong>
                                                <span class="muted"> · <?= htmlspecialchars($activity['pelapor']) ?> · <?= htmlspecialchars($activity['jenis_laporan'] === 'Temuan' ? 'Penemuan' : 'Kehilangan') ?></span>
                                            </span>
                                            <span>
                                                <span class="pill <?= $activity['status'] === 'Pending' ? 'pill-pending' : ($activity['status'] === 'Selesai' ? 'pill-done' : 'pill-progress') ?>"><?= htmlspecialchars($activity['status']) ?></span>
                                                <small class="muted"><?= htmlspecialchars(date('d-m-Y H:i', strtotime($activity['updated_at']))) ?></small>
                                            </span>
                                        </li>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </section>

                    <section id="pengaturan" class="section-block">
                        <div class="section-header">
                            <div>
                                <p class="eyebrow">System</p>
                                <h2>Pengaturan</h2>
                            </div>
                        </div>

                        <div class="panel">
                            <ul class="mini-list">
                                <li><span>Profil Saya</span><a href="profil_user.php" class="table-link">Buka</a></li>
                                <li><span>Halaman Utama</span><a href="index.php" class="table-link">Lihat</a></li>
                                <li><span>Keluar</span><a href="logout.php" class="table-link">Logout</a></li>
                            </ul>
                        </div>
                    </section>
                </main>

        <?php else: ?>
            <main class="user-dashboard">
            <section id="dashboard-home" class="section-header dashboard-hero">
                <div>
                    <p class="eyebrow">Lost &amp; Found · Pengguna</p>
                    <h2>Dashboard</h2>
                    <p class="hero-description">Cari barang temuan dan pantau perkembangan laporan Anda.</p>
                </div>
                <a href="lapor_hilang.php" class="btn btn-hero">Buat Laporan</a>
            </section>
            <div class="stats-grid user-stats-grid">
                <div class="stat-card">
                    <div class="stat-card-heading"><h3>Barang Hilang</h3><span class="stat-icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span></div>
                    <p class="value"><?= (int) ($my_report_stats['total_hilang'] ?? 0) ?></p>
                    <div class="note">Laporan kehilangan Anda</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-heading"><h3>Barang Temuan</h3><span class="stat-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span></div>
                    <p class="value"><?= (int) ($my_report_stats['total_ditemukan'] ?? 0) ?></p>
                    <div class="note">Laporan penemuan Anda</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-heading"><h3>Laporan Diproses</h3><span class="stat-icon"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></span></div>
                    <p class="value"><?= (int) ($my_report_stats['diproses'] ?? 0) ?></p>
                    <div class="note">Sedang ditinjau admin</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-heading"><h3>Laporan Selesai</h3><span class="stat-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span></div>
                    <p class="value"><?= (int) ($my_report_stats['selesai'] ?? 0) ?></p>
                    <div class="note">Laporan telah diselesaikan</div>
                </div>
            </div>

            <div class="panel-grid">
                <div class="panel">
                    <h2>Informasi Akun Saya</h2>
                    <ul class="mini-list">
                        <li><span>Nama</span><strong><?= htmlspecialchars($_SESSION['nama'] ?? 'User') ?></strong></li>
                        <li><span>Username</span><strong><?= htmlspecialchars($_SESSION['username'] ?? '-') ?></strong></li>
                        <li><span>Role</span><strong><?= htmlspecialchars(ucfirst($role)) ?></strong></li>
                    </ul>
                    <div class="btn-row">
                        <a href="profil_user.php" class="btn btn-primary">Lihat Profil</a>
                        <a href="index.php#daftar-barang" class="btn btn-secondary">Lihat Barang Dipublikasikan</a>
                    </div>
                </div>

                <div class="panel" id="laporan-saya">
                    <h2>Riwayat Laporan</h2>
                    <p class="muted workflow-note">Lihat perkembangan laporan kehilangan dan penemuan yang Anda kirim.</p>
                    <div class="btn-row">
                        <a href="lapor_hilang.php" class="btn btn-primary">Lapor Barang Hilang</a>
                        <a href="lapor_temuan.php" class="btn btn-secondary">Lapor Barang Temuan</a>
                    </div>
                    <div class="table-wrap">
                        <table id="riwayat-laporan" class="table-list dashboard-searchable" style="width:100%;">
                            <thead><tr><th>Barang</th><th>Jenis</th><th>Status</th><th>Tanggal</th></tr></thead>
                            <tbody>
                                <?php if (!$my_reports || mysqli_num_rows($my_reports) === 0): ?>
                                    <tr><td colspan="4" class="muted">Kamu belum mengirim laporan.</td></tr>
                                <?php else: ?>
                                    <?php while ($report = mysqli_fetch_assoc($my_reports)): ?>
                                        <?php
                                        $statusLabel = $report['status'] === 'Pending'
                                            ? 'Menunggu Verifikasi'
                                            : ($report['status'] === 'Selesai'
                                                ? 'Sudah Diambil / Selesai'
                                                : ($report['jenis_laporan'] === 'Temuan' ? 'Dipublikasikan' : 'Dalam Tindak Lanjut'));
                                        $statusClass = $report['status'] === 'Pending'
                                            ? 'pill-pending'
                                            : ($report['status'] === 'Selesai' ? 'pill-done' : 'pill-progress');
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($report['nama_barang']) ?></td>
                                            <td><?= htmlspecialchars($report['jenis_laporan'] === 'Temuan' ? 'Barang Temuan' : 'Barang Hilang') ?></td>
                                            <td><span class="pill <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                            <td class="muted"><?= htmlspecialchars(date('d-m-Y', strtotime($report['created_at']))) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="btn-row">
                        <a href="index.php" class="btn btn-outline">Lihat Beranda</a>
                    </div>
                </div>

                <div class="panel user-found-panel">
                    <div class="section-header">
                        <div>
                            <h2>Barang Temuan Terbaru</h2>
                            <p class="muted workflow-note">Barang temuan yang telah tersedia untuk dilihat.</p>
                        </div>
                        <a href="index.php#daftar-barang" class="table-link">Cari semua</a>
                    </div>
                    <ul class="mini-list">
                        <?php if (!$recent_found_items || mysqli_num_rows($recent_found_items) === 0): ?>
                            <li><span class="muted">Belum ada barang temuan yang dipublikasikan.</span></li>
                        <?php else: ?>
                            <?php while ($item = mysqli_fetch_assoc($recent_found_items)): ?>
                                <li>
                                    <span>
                                        <strong><?= htmlspecialchars($item['nama_barang']) ?></strong><br>
                                        <small class="muted"><?= htmlspecialchars($item['nama_kategori'] ?? 'Umum') ?> · <?= htmlspecialchars($item['lokasi_kejadian']) ?></small>
                                    </span>
                                    <a class="table-link" href="detail_barang.php?id=<?= (int) $item['id'] ?>">Lihat</a>
                                </li>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            </main>
        <?php endif; ?>
        </div>
    </div>
    <script src="js/dashboard.js"></script>
</body>
</html>