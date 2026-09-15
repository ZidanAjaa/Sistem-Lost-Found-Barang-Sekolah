<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/koneksi.php";

$nama = $_SESSION['nama'] ?? 'User';
$role = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lost & Found</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; margin: 0; color: #1f2430; }
        .dashboard-wrap { max-width: 1100px; margin: 40px auto; padding: 0 20px 60px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; }
        nav a { color: #2f5dd2; font-weight: 700; text-decoration: none; margin-left: 12px; margin-bottom:20px; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
        .card { background: #8b9ca3; padding: 24px; border-radius: 18px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06); }
        .card h3 { margin-top: 0; }
        .btn { display: inline-block; margin-top: 12px; padding: 12px 18px; border-radius: 10px; text-decoration: none; font-weight: 700; }
        .btn-primary { background: #3f2de0; color: #fff; }
        .btn-profil { margin: 40px 0 0 0 ; background: #edeff5; color: #28315f; }
        .btn-secondary { background: #eef2ff; color: #28315f; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; background: #eef2ff; color: #3947ce; font-weight: 700; font-size: 12px; }
    </style>
</head>
<body>
    <div class="dashboard-wrap">
        <div class="topbar">
            <div>
        
                <h1 style="margin: 8px 0 0;">Halo, <?= htmlspecialchars($nama) ?> <span class="badge"><?= htmlspecialchars($role) ?></span></h1>
            </div>
            <nav>
                <a href="index.php">👤 | Beranda </a>
                
                <a href="profil_user.php">🏠 | Profil</a>
                <a href="logout.php">🚪 | Logout</a>
            </nav>
        </div>

        <div class="card-grid">
            <div class="card">
                <h3>Kelola Barang</h3>
                <p>Tambah, edit, dan hapus data barang hilang atau ditemukan.</p>
                <a href="barang_crud.php" class="btn btn-primary">Buka CRUD Barang</a>
            </div>

            <div class="card">
                <h3>Laporan Baru</h3>
                <p>Masuk ke form laporan cepat untuk barang yang hilang atau ditemukan.</p>
                <a href="lapor_hilang.php" class="btn btn-secondary">Lapor Hilang</a>
                <a href="lapor_temuan.php" class="btn btn-secondary" style="margin-left: 8px;">Lapor Temuan</a>
            </div>

            <div class="card">
                <h3>Profil</h3>
                <p>Lihat dan ubah data diri akun Anda.</p>
                <a href="profil_user.php" class="btn btn-profil" >Lihat Profil</a>
            </div>
        </div>
    </div>
</body>
</html>