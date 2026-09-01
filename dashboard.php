<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/config/koneksi.php";

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lost & Found</title>
</head>
<body>
    <nav>
        <strong>Lost & Found</strong> |
        Halo, <?= htmlspecialchars($nama) ?> (<?= htmlspecialchars($role) ?>) |
        <a href="index.php">Beranda</a> |
        <a href="logout.php">Logout</a>
    </nav>
    <hr>
    <h2>Dashboard</h2>

    <?php if ($role === 'admin'): ?>
        <p>Menu admin di sini.</p>
    <?php else: ?>
        <p>Menu user di sini.</p>
    <?php endif; ?>
</body>
</html>