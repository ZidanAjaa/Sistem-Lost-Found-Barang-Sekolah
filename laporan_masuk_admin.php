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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$notice = $_SESSION['dashboard_notice'] ?? '';
unset($_SESSION['dashboard_notice']);

$laporan = mysqli_query($koneksi, "
    SELECT l.id, l.jenis_laporan, l.status, l.tanggal_lapor, l.created_at,
           b.nama_barang, b.lokasi_kejadian, b.tanggal_kejadian,
           u.nama AS pelapor
    FROM laporan l
    JOIN barang b ON b.id = l.barang_id
    JOIN users u ON u.id = l.user_id
    ORDER BY FIELD(l.status, 'Pending', 'Diproses', 'Selesai'), l.created_at DESC
");
if (!$laporan) {
    throw new RuntimeException('Gagal mengambil daftar laporan: ' . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan Masuk - LostFound</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; background: #f3f5fb; color: #1f2430; }
        .page { max-width: 1200px; margin: 0 auto; padding: 32px 20px 60px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        h1 { margin: 0; }
        .panel { background: #fff; border-radius: 16px; padding: 22px; box-shadow: 0 8px 24px rgba(15, 23, 42, .06); }
        .muted { color: #667085; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #edf0f7; text-align: left; vertical-align: top; }
        th { background: #f8faff; color: #475467; }
        .actions { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
        .actions form { margin: 0; }
        .btn { display: inline-block; border: 0; border-radius: 8px; padding: 9px 12px; text-decoration: none; font-weight: 700; cursor: pointer; }
        .btn-primary { background: #3f2de0; color: #fff; }
        .btn-complete { background: #16804a; color: #fff; }
        .btn-back { background: #eef2ff; color: #2c2f5d; }
        .pill { display: inline-block; padding: 5px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .pending { background: #fff4d6; color: #986400; }
        .progress { background: #e8edff; color: #3947ce; }
        .done { background: #ebf9f1; color: #1e7d4f; }
        .notice { padding: 14px 18px; margin-bottom: 18px; border-radius: 12px; background: #ebf9f1; color: #17633f; font-weight: 700; }
    </style>
</head>
<body>
    <main class="page">
        <div class="topbar">
            <div>
                <p class="muted" style="margin:0 0 5px;">Admin · Laporan pengguna</p>
                <h1>Verifikasi Laporan Masuk</h1>
            </div>
            <a class="btn btn-back" href="dashboard.php">Kembali ke Dashboard</a>
        </div>

        <?php if ($notice !== ''): ?>
            <div class="notice"><?= htmlspecialchars($notice) ?></div>
        <?php endif; ?>

        <section class="panel">
            <p class="muted">Periksa detail laporan sebelum memverifikasi. Laporan barang hilang dan temuan akan dipublikasikan setelah diverifikasi.</p>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Jenis Laporan</th>
                            <th>Pelapor</th>
                            <th>Tanggal Laporan</th>
                            <th>Status</th>
                            <th>Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($laporan) === 0): ?>
                            <tr><td colspan="6" class="muted">Belum ada laporan yang masuk.</td></tr>
                        <?php else: ?>
                            <?php while ($row = mysqli_fetch_assoc($laporan)): ?>
                                <?php
                                $statusClass = $row['status'] === 'Pending'
                                    ? 'pending'
                                    : ($row['status'] === 'Selesai' ? 'done' : 'progress');
                                $statusLabel = $row['status'] === 'Pending'
                                    ? 'Menunggu Verifikasi'
                                    : ($row['status'] === 'Selesai'
                                        ? 'Sudah Diambil / Selesai'
                                        : ($row['jenis_laporan'] === 'Temuan' ? 'Dipublikasikan' : 'Dipublikasikan & Dalam Tindak Lanjut'));
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['nama_barang']) ?></strong><br>
                                        <span class="muted"><?= htmlspecialchars($row['lokasi_kejadian']) ?> · <?= htmlspecialchars(date('d-m-Y', strtotime($row['tanggal_kejadian']))) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['jenis_laporan'] === 'Temuan' ? 'Barang Temuan' : 'Barang Hilang') ?></td>
                                    <td><?= htmlspecialchars($row['pelapor']) ?></td>
                                    <td class="muted"><?= htmlspecialchars(date('d-m-Y', strtotime($row['created_at']))) ?></td>
                                    <td><span class="pill <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn btn-back" href="detail_laporan_admin.php?id=<?= (int) $row['id'] ?>">Lihat Detail</a>
                                            <?php if ($row['status'] === 'Pending' || $row['status'] === 'Diproses'): ?>
                                                <form method="POST" action="dashboard.php">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="laporan_id" value="<?= (int) $row['id'] ?>">
                                                    <input type="hidden" name="return_to" value="laporan_masuk_admin.php">
                                                    <?php if ($row['status'] === 'Pending'): ?>
                                                        <input type="hidden" name="status" value="Diproses">
                                                        <button class="btn btn-primary" type="submit">Verifikasi & Publikasikan</button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="status" value="Selesai">
                                                        <button class="btn btn-complete" type="submit"><?= $row['jenis_laporan'] === 'Temuan' ? 'Tandai Sudah Diambil' : 'Tandai Selesai' ?></button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
