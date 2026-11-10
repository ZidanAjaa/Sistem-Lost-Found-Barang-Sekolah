<?php
session_start();
require_once __DIR__ . '/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$viewerId = (int) $_SESSION['user_id'];
$isAdmin = strtolower($_SESSION['role'] ?? 'user') === 'admin';
$laporanId = (int) ($_GET['id'] ?? 0);

if ($laporanId <= 0) {
    http_response_code(400);
    exit('ID laporan tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "
    SELECT l.id, l.user_id, l.jenis_laporan, l.deskripsi AS deskripsi_laporan,
           l.tanggal_lapor, l.status, l.created_at, l.updated_at,
           b.nama_barang, b.foto, b.deskripsi AS deskripsi_barang,
           b.lokasi_kejadian, b.tanggal_kejadian, b.status_barang,
           k.nama_kategori, u.nama AS pelapor, u.kelas
    FROM laporan l
    JOIN barang b ON b.id = l.barang_id
    LEFT JOIN kategori k ON k.id = b.kategori_id
    JOIN users u ON u.id = l.user_id
    WHERE l.id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, 'i', $laporanId);
mysqli_stmt_execute($stmt);
$laporan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$laporan || (!$isAdmin && (int) $laporan['user_id'] !== $viewerId)) {
    http_response_code($laporan ? 403 : 404);
    exit($laporan ? 'Anda tidak memiliki akses ke laporan ini.' : 'Laporan tidak ditemukan.');
}

$statusLabel = $laporan['status'] === 'Pending'
    ? 'Menunggu Verifikasi'
    : ($laporan['status'] === 'Selesai'
        ? 'Sudah Diambil / Selesai'
        : ($laporan['jenis_laporan'] === 'Temuan' ? 'Dipublikasikan' : 'Dalam Tindak Lanjut'));
$fotoTersedia = !empty($laporan['foto'])
    && is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $laporan['foto']));
$dashboardUrl = $isAdmin ? 'dashboard.php#history-barang' : 'dashboard.php#riwayat-kembali';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Riwayat Laporan - LostFound</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f2f5f9; color: #273344; font-family: "Segoe UI", Arial, sans-serif; }
        .page { width: min(100% - 32px, 1000px); margin: 0 auto; padding: 32px 0 56px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
        .page-header h1 { margin: 0; font-size: clamp(22px, 3vw, 30px); }
        .eyebrow { margin: 0 0 5px; color: #7b8491; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .panel { min-width: 0; padding: 22px; border: 1px solid #edf0f4; border-radius: 12px; background: #fff; box-shadow: 0 3px 8px rgba(28, 39, 54, .06); }
        .panel h2 { margin: 0 0 14px; color: #2e3948; font-size: 18px; }
        .detail-row { padding: 12px 0; border-bottom: 1px solid #edf0f4; line-height: 1.55; overflow-wrap: anywhere; }
        .detail-row:last-child { border-bottom: 0; }
        .label { display: block; margin-bottom: 4px; color: #788391; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
        .description { white-space: pre-wrap; }
        .muted { color: #788391; }
        .badge { display: inline-block; padding: 5px 9px; border-radius: 6px; background: #e8edff; color: #3f2de0; font-size: 12px; font-weight: 700; }
        .photo { display: block; width: 100%; max-height: 380px; margin-top: 16px; border-radius: 9px; background: #f3f5f8; object-fit: contain; }
        .btn { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 8px; padding: 9px 14px; border: 1px solid #dfe3e9; border-radius: 8px; background: #fff; color: #3c4654; font-weight: 700; text-decoration: none; }
        .btn:hover { border-color: #3f2de0; color: #3f2de0; }
        @media (max-width: 700px) {
            .page { padding-top: 22px; }
            .page-header { align-items: flex-start; flex-direction: column; }
            .grid { grid-template-columns: 1fr; }
            .panel { padding: 18px; }
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="page-header">
            <div>
                <p class="eyebrow">Riwayat Laporan · #<?= (int) $laporan['id'] ?></p>
                <h1>Detail Laporan Barang</h1>
            </div>
            <a class="btn" href="<?= htmlspecialchars($dashboardUrl) ?>">Kembali ke Riwayat Barang</a>
        </header>

        <div class="grid">
            <section class="panel">
                <h2>Informasi Barang</h2>
                <div class="detail-row"><span class="label">Nama Barang</span><strong><?= htmlspecialchars($laporan['nama_barang']) ?></strong></div>
                <div class="detail-row"><span class="label">Kategori</span><?= htmlspecialchars($laporan['nama_kategori'] ?? 'Umum') ?></div>
                <div class="detail-row"><span class="label">Status Barang</span><?= htmlspecialchars($laporan['status_barang']) ?></div>
                <div class="detail-row"><span class="label">Lokasi Kejadian</span><?= htmlspecialchars($laporan['lokasi_kejadian']) ?></div>
                <div class="detail-row"><span class="label">Tanggal Kejadian</span><?= htmlspecialchars(date('d-m-Y', strtotime($laporan['tanggal_kejadian']))) ?></div>
                <div class="detail-row"><span class="label">Deskripsi Barang</span><div class="description"><?= nl2br(htmlspecialchars($laporan['deskripsi_barang'] ?? '-')) ?></div></div>
                <?php if ($fotoTersedia): ?>
                    <a href="<?= htmlspecialchars($laporan['foto']) ?>" target="_blank" rel="noopener">
                        <img class="photo" src="<?= htmlspecialchars($laporan['foto']) ?>" alt="Foto <?= htmlspecialchars($laporan['nama_barang']) ?>">
                    </a>
                <?php else: ?>
                    <p class="muted">Tidak ada foto barang.</p>
                <?php endif; ?>
            </section>

            <section class="panel">
                <h2>Informasi Laporan</h2>
                <div class="detail-row"><span class="label">Pelapor</span><?= htmlspecialchars($laporan['pelapor']) ?> · <?= htmlspecialchars($laporan['kelas'] ?? '-') ?></div>
                <div class="detail-row"><span class="label">Jenis Laporan</span><?= htmlspecialchars($laporan['jenis_laporan'] === 'Temuan' ? 'Barang Temuan' : 'Barang Hilang') ?></div>
                <div class="detail-row"><span class="label">Status Laporan</span><span class="badge"><?= htmlspecialchars($statusLabel) ?></span></div>
                <div class="detail-row"><span class="label">Tanggal Laporan</span><?= htmlspecialchars(date('d-m-Y', strtotime($laporan['tanggal_lapor']))) ?></div>
                <div class="detail-row"><span class="label">Dikirim</span><?= htmlspecialchars(date('d-m-Y H:i', strtotime($laporan['created_at']))) ?></div>
                <div class="detail-row"><span class="label">Terakhir Diperbarui</span><?= htmlspecialchars(date('d-m-Y H:i', strtotime($laporan['updated_at']))) ?></div>
                <div class="detail-row"><span class="label">Isi Laporan User</span><div class="description"><?= nl2br(htmlspecialchars($laporan['deskripsi_laporan'] ?? 'Tidak ada deskripsi laporan.')) ?></div></div>
            </section>
        </div>
    </main>
</body>
</html>
