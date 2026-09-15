<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/koneksi.php";

$nama = $_SESSION['nama'] ?? 'User';
$role = strtolower($_SESSION['role'] ?? 'user');

$stats = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT
        SUM(CASE WHEN status_barang = 'Hilang' THEN 1 ELSE 0 END) AS total_hilang,
        SUM(CASE WHEN status_barang = 'Ditemukan' THEN 1 ELSE 0 END) AS total_ditemukan,
        COUNT(*) AS total_barang
    FROM barang
")) ?: [
    'total_hilang' => 0,
    'total_ditemukan' => 0,
    'total_barang' => 0,
];

$total_user = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0);
$total_admin = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'"))['total'] ?? 0);

$recent_users = mysqli_query($koneksi, "
    SELECT u.id, u.nama, u.kelas, u.role, l.username
    FROM users u
    LEFT JOIN login l ON l.user_id = u.id
    ORDER BY u.id DESC
    LIMIT 5
");

$recent_barang = mysqli_query($koneksi, "
    SELECT b.id, b.nama_barang, b.status_barang, b.lokasi_kejadian, b.tanggal_kejadian, u.nama AS pemilik
    FROM barang b
    JOIN users u ON u.id = b.pemilik_id
    ORDER BY b.created_at DESC
    LIMIT 6
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #eef3ff 0%, #f8f9ff 100%);
            color: #1f2430;
        }
        .dashboard-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px 60px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .topbar h1 {
            margin: 0;
            font-size: 2rem;
        }
        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .nav-links a {
            color: #2f5dd2;
            font-weight: 700;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 10px;
            background: rgba(255,255,255,0.7);
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: #e8edff;
            color: #3947ce;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
        }
        .admin-shell {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 24px;
            align-items: start;
        }
        .sidebar {
            background: #121a3b;
            color: #fff;
            border-radius: 22px;
            padding: 20px 16px;
            position: sticky;
            top: 20px;
            min-height: 700px;
        }
        .brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 8px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            margin-bottom: 18px;
        }
        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #5d6bff, #7ec8ff);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }
        .brand-box h3 {
            margin: 0;
            font-size: 1.1rem;
        }
        .brand-box small {
            color: rgba(255,255,255,0.7);
        }
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .sidebar-nav a {
            color: #edf3ff;
            text-decoration: none;
            padding: 12px 14px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }
        .sidebar-nav a.active,
        .sidebar-nav a:hover {
            background: rgba(255,255,255,0.08);
        }
        .sidebar-footer {
            margin-top: 30px;
            padding: 16px 12px 8px;
            border-top: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.8);
        }
        .sidebar-footer strong {
            display: block;
            margin: 5px 0 10px;
            color: #fff;
        }
        .sidebar-footer a {
            color: #9ad2ff;
            text-decoration: none;
        }
        .admin-main {
            display: flex;
            flex-direction: column;
            gap: 22px;
        }
        .section-block {
            scroll-margin-top: 20px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .eyebrow {
            margin: 0 0 4px;
            font-size: 0.75rem;
            color: #6b7280;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
        }
        .section-header h2 {
            margin: 0;
            font-size: 1.6rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 18px;
            margin-bottom: 0;
        }
        .stat-card, .panel {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }
        .stat-card h3 {
            margin: 0 0 10px;
            color: #667085;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .value {
            font-size: 2rem;
            font-weight: 800;
            margin: 0;
        }
        .stat-card .note {
            margin-top: 8px;
            color: #475467;
            font-size: 0.9rem;
        }
        .panel-grid {
            display: grid;
            grid-template-columns: 1.1fr 1.1fr;
            gap: 26px;
            margin-top: 22px;
        }
        .panel h2 {
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 1.3rem;
        }
        .btn-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 18px;
        }
        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        .btn-primary { background: #3f2de0; color: #fff; }
        .btn-secondary { background: #eef2ff; color: #2c2f5d; }
        .btn-outline { background: #fff; border: 1px solid #dfe3ef; color: #374151; }
        .btn-row.vertical {
            flex-direction: column;
            align-items: flex-start;
        }
        .table-link {
            color: #2f5dd2;
            font-weight: 700;
            text-decoration: none;
        }
        .mini-list, .table-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .mini-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #edf0f7;
        }
        .mini-list li:last-child {
            border-bottom: none;
        }
        .pill {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .pill-hilang { background: #fff0df; color: #b76d00; }
        .pill-ditemukan { background: #ebf9f1; color: #1e7d4f; }
        .table-list th, .table-list td {
            padding: 12px 10px;
            border-bottom: 1px solid #edf0f7;
            text-align: left;
            vertical-align: top;
        }
        .table-list th {
            color: #475467;
            background: #f8faff;
        }
        .muted { color: #667085; }
        @media (max-width: 900px) {
            .admin-shell { grid-template-columns: 1fr; }
            .sidebar { position: static; min-height: auto; }
            .panel-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrap">
        <div class="topbar">
            <div>
                <h1>Halo, <?= htmlspecialchars($nama) ?> <span class="badge"><?= htmlspecialchars($role) ?></span></h1>
            </div>
            <nav class="nav-links">
                <a href="index.php">Beranda</a>
                <a href="profil_user.php">Profil</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>

        <?php if ($role === 'admin'): ?>
            <div class="admin-shell">
                <aside class="sidebar">
                    <div class="brand-box">
                        <div class="brand-icon">LF</div>
                        <div>
                            <h3>LostFound</h3>
                            <small>Admin Panel</small>
                        </div>
                    </div>

                    <nav class="sidebar-nav">
                        <a href="#analisa-data" class="active"><span></span> Analisa Data Barang</a>
                        <a href="#informasi-user"><span></span> Informasi Akun User</a>
                        <a href="#kelola-barang"><span></span> Kelola Data Barang</a>
                        <a href="#laporan-masuk"><span></span> Laporan Masuk</a>
                        <a href="#pengaturan"><span></span> Pengaturan</a>
                    </nav>

                    <div class="sidebar-footer">
                        <small>Login sebagai</small>
                        <strong><?= htmlspecialchars($nama) ?></strong>
                        <a href="logout.php">Logout</a>
                    </div>
                </aside>

                <main class="admin-main">
                    <section id="analisa-data" class="section-block">
                        <div class="section-header">
                            <div>
                                <p class="eyebrow">Overview</p>
                                <h2>Analisa Data Barang</h2>
                            </div>
                            <a href="lapor_hilang.php" class="btn btn-primary">+ Buat Laporan</a>
                        </div>

                        <div class="stats-grid">
                            <div class="stat-card">
                                <h3>Total Barang</h3>
                                <p class="value"><?= (int) ($stats['total_barang'] ?? 0) ?></p>
                                <div class="note">Semua laporan aktif</div>
                            </div>
                            <div class="stat-card">
                                <h3>Barang Hilang</h3>
                                <p class="value"><?= (int) ($stats['total_hilang'] ?? 0) ?></p>
                                <div class="note">Perlu tindak lanjut</div>
                            </div>
                            <div class="stat-card">
                                <h3>Barang Ditemukan</h3>
                                <p class="value"><?= (int) ($stats['total_ditemukan'] ?? 0) ?></p>
                                <div class="note">Siap diproses</div>
                            </div>
                            <div class="stat-card">
                                <h3>Pengguna</h3>
                                <p class="value"><?= $total_user ?></p>
                                <div class="note"><?= $total_admin ?> admin</div>
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
                                            $persenHilang = ($stats['total_barang'] > 0)
                                                ? round(((int) ($stats['total_hilang'] ?? 0) / (int) ($stats['total_barang'] ?? 1)) * 100)
                                                : 0;
                                            echo $persenHilang . '%';
                                            ?>
                                        </span>
                                    </li>
                                    <li>
                                        <span>Persentase Ditemukan</span>
                                        <span>
                                            <?php
                                            $persenDitemukan = ($stats['total_barang'] > 0)
                                                ? round(((int) ($stats['total_ditemukan'] ?? 0) / (int) ($stats['total_barang'] ?? 1)) * 100)
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
                                    <a href="barang_crud.php" class="btn btn-primary">Kelola Data Barang</a>
                                    <a href="lapor_hilang.php" class="btn btn-secondary">Tambah Laporan Hilang</a>
                                    <a href="lapor_temuan.php" class="btn btn-secondary">Tambah Laporan Temuan</a>
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
                            <table class="table-list" style="width:100%;">
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
                            <a href="barang_crud.php" class="btn btn-primary">Buka CRUD</a>
                        </div>

                        <div class="panel">
                            <table class="table-list" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Nama Barang</th>
                                        <th>Pemilik</th>
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

                    <section id="laporan-masuk" class="section-block">
                        <div class="section-header">
                            <div>
                                <p class="eyebrow">Reports</p>
                                <h2>Laporan Masuk</h2>
                            </div>
                        </div>

                        <div class="panel">
                            <p class="muted">Aktivitas laporan terbaru akan muncul di sini setelah sistem pelaporan disinkronkan.</p>
                            <div class="btn-row">
                                <a href="lapor_hilang.php" class="btn btn-secondary">Laporan Hilang</a>
                                <a href="lapor_temuan.php" class="btn btn-secondary">Laporan Temuan</a>
                            </div>
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
            </div>

        <?php else: ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Barang Saya</h3>
                    <p class="value"><?= (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM barang WHERE pemilik_id = " . (int) $_SESSION['user_id']))['total'] ?? 0 ?></p>
                    <div class="note">Laporan yang sudah kamu buat</div>
                </div>
                <div class="stat-card">
                    <h3>Barang Hilang</h3>
                    <p class="value"><?= (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM barang WHERE pemilik_id = " . (int) $_SESSION['user_id'] . " AND status_barang = 'Hilang'"))['total'] ?? 0 ?></p>
                    <div class="note">Data barang yang kamu laporkan</div>
                </div>
                <div class="stat-card">
                    <h3>Barang Ditemukan</h3>
                    <p class="value"><?= (int) mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM barang WHERE pemilik_id = " . (int) $_SESSION['user_id'] . " AND status_barang = 'Ditemukan'"))['total'] ?? 0 ?></p>
                    <div class="note">Status barang temuan</div>
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
                        <a href="barang_crud.php" class="btn btn-secondary">Kelola Barang</a>
                    </div>
                </div>

                <div class="panel">
                    <h2>Laporkan Barang</h2>
                    <p class="muted">Pilih jenis laporan yang ingin kamu buat untuk barang hilang atau barang temuan.</p>
                    <div class="btn-row">
                        <a href="lapor_hilang.php" class="btn btn-primary">Lapor Barang Hilang</a>
                        <a href="lapor_temuan.php" class="btn btn-secondary">Lapor Barang Temuan</a>
                    </div>
                    <div class="btn-row">
                        <a href="index.php" class="btn btn-outline">Lihat Beranda</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>