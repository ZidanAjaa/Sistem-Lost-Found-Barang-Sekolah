<?php
session_start();

// Proteksi: kalau belum login, tendang ke login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/config/koneksi.php";

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];

// Ambil sedikit statistik untuk ditampilkan (opsional, bisa disesuaikan nanti)
$total_barang = $koneksi->query("SELECT COUNT(*) as jumlah FROM barang")->fetch_assoc()['jumlah'];
$total_laporan = $koneksi->query("SELECT COUNT(*) as jumlah FROM laporan")->fetch_assoc()['jumlah'];
$laporan_pending = $koneksi->query("SELECT COUNT(*) as jumlah FROM laporan WHERE status = 'Pending'")->fetch_assoc()['jumlah'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lost & Found</title>
</head>
<body>
    <nav>
        <strong>Lost & Found</strong>
        &nbsp;|&nbsp;
        Halo, <?= htmlspecialchars($nama) ?> (<?= htmlspecialchars($role) ?>)
        &nbsp;|&nbsp;
        <a href="logout.php">Logout</a>
    </nav>

    <hr>

    <h2>Dashboard</h2>

    <div>
        <p><strong>Total Barang Tercatat:</strong> <?= $total_barang ?></p>
        <p><strong>Total Laporan:</strong> <?= $total_laporan ?></p>
        <p><strong>Laporan Pending:</strong> <?= $laporan_pending ?></p>
    </div>

    <hr>

    <?php if ($role === 'admin'): ?>
        <h3>Menu Admin</h3>
        <ul>
            <li><a href="kelola_barang.php">Kelola Data Barang</a></li>
            <li><a href="kelola_laporan.php">Kelola Semua Laporan</a></li>
            <li><a href="kelola_pengembalian.php">Kelola Pengembalian</a></li>
            <li><a href="kelola_user.php">Kelola User</a></li>
        </ul>
    <?php else: ?>
        <h3>Menu User</h3>
        <ul>
            <li><a href="lapor_barang.php">Lapor Barang Hilang/Temuan</a></li>
            <li><a href="laporan_saya.php">Laporan Saya</a></li>
            <li><a href="cari_barang.php">Cari Barang</a></li>
        </ul>
    <?php endif; ?>

</body>
</html>