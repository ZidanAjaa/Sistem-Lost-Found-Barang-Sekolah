<?php
session_start();
require_once __DIR__ . "/koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (strtolower($_SESSION['role'] ?? 'user') !== 'admin') {
    http_response_code(403);
    exit('Halaman ini hanya dapat diakses oleh admin.');
}

$laporanId = (int) ($_GET['id'] ?? 0);
if ($laporanId <= 0) {
    http_response_code(400);
    exit('ID laporan tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "
    SELECT l.id, l.jenis_laporan, l.deskripsi AS deskripsi_laporan, l.tanggal_lapor,
           l.status, l.created_at AS laporan_dibuat, l.updated_at AS laporan_diperbarui,
           b.nama_barang, b.foto, b.deskripsi AS deskripsi_barang, b.lokasi_kejadian,
           b.tanggal_kejadian, b.status_barang, k.nama_kategori,
           u.nama AS pelapor, u.kelas, u.no_telepon
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

if (!$laporan) {
    http_response_code(404);
    exit('Laporan tidak ditemukan.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$nomorPelapor = preg_replace('/\D+/', '', $laporan['no_telepon'] ?? '');
if (substr($nomorPelapor, 0, 1) === '0') {
    $nomorPelapor = '62' . substr($nomorPelapor, 1);
}
$statusLabel = $laporan['status'] === 'Pending'
    ? 'Menunggu Verifikasi'
    : ($laporan['status'] === 'Selesai'
        ? 'Sudah Diambil / Selesai'
        : ($laporan['jenis_laporan'] === 'Temuan' ? 'Dipublikasikan' : 'Dipublikasikan & Dalam Tindak Lanjut'));
$fotoTersedia = !empty($laporan['foto'])
    && is_file(__DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $laporan['foto']));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan #<?= (int) $laporan['id'] ?> - LostFound</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f5fb; color: #1f2430; }
        .page { max-width: 1000px; margin: 0 auto; padding: 32px 20px 60px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        h1, h2 { margin-top: 0; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .panel { background: #fff; border-radius: 16px; padding: 22px; box-shadow: 0 8px 24px rgba(15, 23, 42, .06); }
        .item { padding: 12px 0; border-bottom: 1px solid #edf0f7; }
        .item:last-child { border-bottom: 0; }
        .label { display: block; color: #667085; font-size: 12px; font-weight: 700; text-transform: uppercase; margin-bottom: 5px; }
        .description { white-space: pre-wrap; line-height: 1.6; }
        .photo { display: block; width: 100%; max-height: 420px; object-fit: contain; background: #f7f9ff; border-radius: 12px; margin-top: 12px; }
        .muted { color: #667085; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .actions form { margin: 0; }
        .btn { display: inline-block; border: 0; border-radius: 8px; padding: 10px 14px; text-decoration: none; font-weight: 700; cursor: pointer; }
        .btn-primary { background: #3f2de0; color: #fff; }
        .btn-complete { background: #16804a; color: #fff; }
        .btn-back { background: #eef2ff; color: #2c2f5d; }
        @media (max-width: 700px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="page">
        <div class="topbar">
            <div>
                <p class="muted" style="margin:0 0 5px;">Laporan #<?= (int) $laporan['id'] ?></p>
                <h1>Detail Laporan User</h1>
            </div>
            <a class="btn btn-back" href="laporan_masuk_admin.php">Kembali ke Verifikasi Laporan</a>
        </div>

        <div class="grid">
            <section class="panel">
                <h2>Informasi Barang</h2>
                <div class="item"><span class="label">Nama Barang</span><strong><?= htmlspecialchars($laporan['nama_barang']) ?></strong></div>
                <div class="item"><span class="label">Kategori</span><?= htmlspecialchars($laporan['nama_kategori'] ?? 'Umum') ?></div>
                <div class="item"><span class="label">Jenis Laporan</span><?= htmlspecialchars($laporan['jenis_laporan'] === 'Temuan' ? 'Barang Temuan' : 'Barang Hilang') ?></div>
                <div class="item"><span class="label">Status Barang</span><?= htmlspecialchars($laporan['status_barang']) ?></div>
                <div class="item"><span class="label">Lokasi Kejadian</span><?= htmlspecialchars($laporan['lokasi_kejadian']) ?></div>
                <div class="item"><span class="label">Tanggal Kejadian</span><?= htmlspecialchars(date('d-m-Y', strtotime($laporan['tanggal_kejadian']))) ?></div>
                <div class="item"><span class="label">Deskripsi Barang</span><div class="description"><?= nl2br(htmlspecialchars($laporan['deskripsi_barang'] ?? '-')) ?></div></div>
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
                <div class="item"><span class="label">Pelapor</span><strong><?= htmlspecialchars($laporan['pelapor']) ?></strong></div>
                <div class="item"><span class="label">Kelas</span><?= htmlspecialchars($laporan['kelas'] ?? '-') ?></div>
                <div class="item"><span class="label">Nomor Telepon</span>
                    <?php if ($nomorPelapor !== ''): ?>
                        <a href="https://wa.me/<?= htmlspecialchars($nomorPelapor) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($laporan['no_telepon']) ?> · Konfirmasi via WhatsApp</a>
                    <?php else: ?>
                        <?= htmlspecialchars($laporan['no_telepon'] ?? '-') ?>
                    <?php endif; ?>
                </div>
                <div class="item"><span class="label">Tanggal Laporan</span><?= htmlspecialchars(date('d-m-Y', strtotime($laporan['tanggal_lapor']))) ?></div>
                <div class="item"><span class="label">Dikirim</span><?= htmlspecialchars(date('d-m-Y H:i', strtotime($laporan['laporan_dibuat']))) ?></div>
                <div class="item"><span class="label">Terakhir Diperbarui</span><?= htmlspecialchars(date('d-m-Y H:i', strtotime($laporan['laporan_diperbarui']))) ?></div>
                <div class="item"><span class="label">Status Verifikasi</span><strong><?= htmlspecialchars($statusLabel) ?></strong></div>
                <div class="item"><span class="label">Deskripsi Laporan</span><div class="description"><?= nl2br(htmlspecialchars($laporan['deskripsi_laporan'] ?? '-')) ?></div></div>

                <?php if ($laporan['status'] === 'Pending' || $laporan['status'] === 'Diproses'): ?>
                    <div class="actions">
                        <form method="POST" action="dashboard.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" name="laporan_id" value="<?= (int) $laporan['id'] ?>">
                            <input type="hidden" name="return_to" value="laporan_masuk_admin.php">
                            <?php if ($laporan['status'] === 'Pending'): ?>
                                <input type="hidden" name="status" value="Diproses">
                                <button class="btn btn-primary" type="submit">Verifikasi & Publikasikan</button>
                            <?php else: ?>
                                <input type="hidden" name="status" value="Selesai">
                                <button class="btn btn-complete" type="submit"><?= $laporan['jenis_laporan'] === 'Temuan' ? 'Tandai Sudah Diambil' : 'Tandai Selesai' ?></button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</body>
</html>
