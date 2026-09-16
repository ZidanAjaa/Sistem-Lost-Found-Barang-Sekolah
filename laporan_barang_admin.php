<?php
session_start();
require_once __DIR__ . "/koneksi.php";

if (!isset($_SESSION["user_id"]) || (strtolower($_SESSION["role"] ?? "user") !== "admin")) {
    header("Location: login.php");
    exit;
}

$search = trim($_GET["search"] ?? "");
$filter_jenis = in_array($_GET["jenis"] ?? "", ["Hilang", "Temuan"], true) ? $_GET["jenis"] : "";

$query = "
    SELECT 
        b.id, 
        b.nama_barang, 
        b.foto, 
        b.status_barang, 
        b.deskripsi,
        b.lokasi_kejadian, 
        b.tanggal_kejadian, 
        k.nama_kategori,
        u.id AS user_id,
        u.nama, 
        u.kelas, 
        u.no_telepon,
        b.created_at
    FROM barang b
    JOIN kategori k ON k.id = b.kategori_id
    JOIN users u ON u.id = b.pemilik_id
    WHERE 1=1
";

if ($search !== "") {
    $searchEscaped = mysqli_real_escape_string($koneksi, $search);
    $query .= " AND (b.nama_barang LIKE '%$searchEscaped%' OR u.nama LIKE '%$searchEscaped%' OR b.lokasi_kejadian LIKE '%$searchEscaped%')";
}

if ($filter_jenis !== "") {
    $filterEscaped = mysqli_real_escape_string($koneksi, $filter_jenis);
    $query .= " AND b.status_barang = '$filterEscaped'";
}

$query .= " ORDER BY b.created_at DESC";

$result = mysqli_query($koneksi, $query);
$total = mysqli_num_rows($result);

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Barang - Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { 
            margin: 0; 
            font-family: Arial, sans-serif; 
            background: #f3f5fb; 
            color: #1f2430; 
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 32px 20px 60px; 
        }
        .topbar { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 24px; 
            flex-wrap: wrap;
            gap: 16px;
        }
        .topbar h1 { 
            margin: 0; 
            font-size: 2rem; 
        }
        .topbar a { 
            color: #3f2de0; 
            text-decoration: none; 
            font-weight: 700; 
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-box {
            background: #fff;
            padding: 18px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        .stat-box .label {
            color: #667085;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .stat-box .value {
            font-size: 28px;
            font-weight: 800;
            color: #3f2de0;
        }
        .filter-bar {
            background: #fff;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-bar input,
        .filter-bar select {
            padding: 10px 14px;
            border: 1px solid #dfe3ef;
            border-radius: 8px;
            font-size: 14px;
        }
        .filter-bar input {
            flex: 1;
            min-width: 200px;
        }
        .filter-bar button {
            padding: 10px 18px;
            background: #3f2de0;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }
        .filter-bar a {
            padding: 10px 18px;
            background: #eef2ff;
            color: #2c2f5d;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
        }
        .panel {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .panel h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .grid-barang {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 0;
        }
        .card-barang {
            background: #fff;
            border: 1px solid #edf0f7;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .card-barang:hover {
            box-shadow: 0 8px 16px rgba(63, 45, 224, 0.1);
            transform: translateY(-4px);
        }
        .card-foto {
            width: 100%;
            height: 180px;
            background: #f0f4ff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 12px;
            color: #667085;
        }
        .card-foto img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .card-body {
            padding: 16px;
        }
        .card-title {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 700;
            color: #1f2430;
        }
        .card-kategori {
            display: inline-block;
            padding: 4px 8px;
            background: #f3e8ff;
            color: #6b21a8;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .card-info {
            font-size: 13px;
            color: #667085;
            margin-bottom: 8px;
        }
        .card-info strong {
            color: #1f2430;
        }
        .badge-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .badge-hilang {
            background: #fff2e5;
            color: #c77700;
        }
        .badge-temuan {
            background: #ebf9f1;
            color: #18794e;
        }
        .card-pemilik {
            background: #f6f8ff;
            padding: 12px;
            border-radius: 8px;
            margin: 12px 0;
            font-size: 13px;
        }
        .card-pemilik strong {
            display: block;
            color: #1f2430;
            margin-bottom: 4px;
        }
        .card-pemilik .kelas {
            color: #667085;
            font-size: 12px;
        }
        .card-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .btn-small {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-align: center;
        }
        .btn-whatsapp {
            background: #25d366;
            color: #fff;
        }
        .btn-whatsapp:hover {
            background: #1ba855;
        }
        .btn-detail {
            background: #3f2de0;
            color: #fff;
        }
        .btn-detail:hover {
            background: #2e22b8;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #667085;
        }
        .empty-state i {
            font-size: 48px;
            margin-bottom: 16px;
            color: #dfe3ef;
        }
        .table-view {
            overflow-x: auto;
        }
        .table-list {
            width: 100%;
            border-collapse: collapse;
        }
        .table-list th,
        .table-list td {
            padding: 12px 10px;
            border-bottom: 1px solid #edf0f7;
            text-align: left;
            vertical-align: top;
        }
        .table-list th {
            background: #f6f8ff;
            color: #475467;
            font-weight: 700;
        }
        .table-list img {
            width: 60px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }
        .muted { color: #667085; }
        .pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }
        .pill-hilang { background: #fff2e5; color: #c77700; }
        .pill-temuan { background: #ebf9f1; color: #18794e; }
        .view-toggle {
            display: flex;
            gap: 8px;
            margin-left: auto;
        }
        .view-toggle button {
            padding: 8px 12px;
            border: 1px solid #dfe3ef;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            color: #475467;
        }
        .view-toggle button.active {
            background: #3f2de0;
            color: #fff;
            border-color: #3f2de0;
        }
        @media (max-width: 768px) {
            .topbar { flex-direction: column; align-items: flex-start; }
            .filter-bar { flex-direction: column; }
            .filter-bar input { width: 100%; }
            .grid-barang { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <h1><i class="fa-solid fa-list"></i> Laporan Data Barang Lengkap</h1>
            <a href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="label">Total Barang</div>
                <div class="value"><?= (int) ($stats['total_barang'] ?? 0) ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Hilang</div>
                <div class="value"><?= (int) ($stats['total_hilang'] ?? 0) ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Ditemukan</div>
                <div class="value"><?= (int) ($stats['total_ditemukan'] ?? 0) ?></div>
            </div>
            <div class="stat-box">
                <div class="label">Hasil Pencarian</div>
                <div class="value"><?= $total ?></div>
            </div>
        </div>

        <!-- Filter -->
        <div class="filter-bar">
            <form method="GET" action="laporan_barang_admin.php" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; width: 100%;">
                <input type="text" name="search" placeholder="Cari nama barang, pemilik, atau lokasi..." value="<?= htmlspecialchars($search) ?>">
                <select name="jenis">
                    <option value="">-- Semua Jenis --</option>
                    <option value="Hilang" <?= ($filter_jenis === "Hilang" ? "selected" : "") ?>>Hilang</option>
                    <option value="Temuan" <?= ($filter_jenis === "Temuan" ? "selected" : "") ?>>Ditemukan</option>
                </select>
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
                <a href="laporan_barang_admin.php"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </form>
        </div>

        <!-- Panel Barang -->
        <div class="panel">
            <?php if ($total === 0): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    <p>Tidak ada data barang yang ditemukan.</p>
                </div>
            <?php else: ?>
                <div class="grid-barang">
                    <?php while ($barang = mysqli_fetch_assoc($result)): ?>
                        <div class="card-barang">
                            <div class="card-foto">
                                <?php if (!empty($barang['foto'])): ?>
                                    <img src="<?= htmlspecialchars($barang['foto']) ?>" alt="<?= htmlspecialchars($barang['nama_barang']) ?>">
                                <?php else: ?>
                                    <i class="fa-solid fa-image"></i> Tidak ada foto
                                <?php endif; ?>
                            </div>

                            <div class="card-body">
                                <h3 class="card-title"><?= htmlspecialchars($barang['nama_barang']) ?></h3>
                                
                                <span class="card-kategori"><?= htmlspecialchars($barang['nama_kategori']) ?></span>
                                <br>
                                <span class="badge-status <?= $barang['status_barang'] === 'Hilang' ? 'badge-hilang' : 'badge-temuan' ?>">
                                    <?= htmlspecialchars($barang['status_barang']) ?>
                                </span>

                                <div class="card-info">
                                    <strong>Lokasi:</strong> <?= htmlspecialchars($barang['lokasi_kejadian']) ?>
                                </div>
                                <div class="card-info">
                                    <strong>Tanggal:</strong> <?= htmlspecialchars(date('d-m-Y', strtotime($barang['tanggal_kejadian']))) ?>
                                </div>
                                <div class="card-info">
                                    <strong>Deskripsi:</strong> 
                                    <div style="margin-top: 4px; color: #667085; font-size: 12px;">
                                        <?= htmlspecialchars(substr($barang['deskripsi'], 0, 80)) ?><?= (strlen($barang['deskripsi']) > 80 ? '...' : '') ?>
                                    </div>
                                </div>

                                <div class="card-pemilik">
                                    <strong><?= htmlspecialchars($barang['nama']) ?></strong>
                                    <div class="kelas"><?= htmlspecialchars($barang['kelas']) ?></div>
                                    <?php if (!empty($barang['no_telepon'])): ?>
                                        <div class="kelas" style="margin-top: 4px;"><?= htmlspecialchars($barang['no_telepon']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="card-actions">
                                    <a href="detail_barang.php?id=<?= (int) $barang['id'] ?>" class="btn-small btn-detail">
                                        <i class="fa-solid fa-eye"></i> Detail
                                    </a>
                                    <?php if (!empty($barang['no_telepon'])): 
                                        $noWa = preg_replace('/[^0-9]/', '', $barang['no_telepon']);
                                        if (substr($noWa, 0, 1) === '0') {
                                            $noWa = '62' . substr($noWa, 1);
                                        }
                                        $linkWa = "https://wa.me/$noWa";
                                    ?>
                                        <a href="<?= $linkWa ?>" target="_blank" class="btn-small btn-whatsapp">
                                            <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
