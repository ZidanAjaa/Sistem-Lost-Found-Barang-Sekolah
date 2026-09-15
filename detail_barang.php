<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$sql = "
    SELECT
        b.id,
        b.nama_barang,
        b.foto,
        b.deskripsi,
        b.lokasi_kejadian,
        b.tanggal_kejadian,
        b.status_barang,
        k.nama_kategori,
        u.nama AS pemilik,
        u.kelas,
        u.no_telepon
    FROM barang b
    LEFT JOIN kategori k ON k.id = b.kategori_id
    LEFT JOIN users u ON u.id = b.pemilik_id
    WHERE b.id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$item) {
    header('Location: index.php');
    exit;
}

$foto = !empty($item['foto']) && file_exists(__DIR__ . '/' . $item['foto'])
    ? $item['foto']
    : 'https://placehold.co/900x600/f3f4f6/374151?text=Foto+Barang';

$wa = !empty($item['no_telepon']) ? preg_replace('/\D+/', '', $item['no_telepon']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['nama_barang']) ?> - LostFound.sch</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #1f2430;
        }
        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
        }
        .btn-primary { background: #3f2de0; color: #fff; }
        .btn-secondary { background: #eef2ff; color: #2c2f5d; }
        .grid {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 24px;
        }
        .card {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 24px;
        }
        .foto {
            width: 100%;
            height: 420px;
            object-fit: cover;
            border-radius: 18px;
        }
        .tag {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .tag-lost { background: #fff0df; color: #b76d00; }
        .tag-found { background: #ebf9f1; color: #1e7d4f; }
        h1 {
            margin: 0 0 12px;
            font-size: 2rem;
        }
        .meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin: 18px 0;
        }
        .meta-item {
            padding: 14px 16px;
            background: #f7f9ff;
            border-radius: 12px;
        }
        .meta-item .label {
            display: block;
            color: #667085;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .desc {
            line-height: 1.7;
            color: #475467;
            margin-top: 16px;
        }
        .contact-box {
            margin-top: 18px;
            padding: 16px;
            border-radius: 14px;
            background: #f7f9ff;
        }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <h1>Detail Barang</h1>
            <a href="index.php" class="btn btn-secondary">← Kembali</a>
        </div>

        <div class="grid">
            <div class="card">
                <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($item['nama_barang']) ?>" class="foto">
            </div>

            <div class="card">
                <span class="tag <?= $item['status_barang'] === 'Ditemukan' ? 'tag-found' : 'tag-lost' ?>">
                    <?= htmlspecialchars($item['status_barang']) ?>
                </span>
                <h1><?= htmlspecialchars($item['nama_barang']) ?></h1>
                <div style="margin-bottom: 12px; font-weight: 700; color: #3f2de0;">
                    <?= htmlspecialchars($item['nama_kategori'] ?: 'Umum') ?>
                </div>

                <div class="meta">
                    <div class="meta-item">
                        <span class="label">Lokasi</span>
                        <strong><?= htmlspecialchars($item['lokasi_kejadian']) ?></strong>
                    </div>
                    <div class="meta-item">
                        <span class="label">Tanggal</span>
                        <strong><?= htmlspecialchars(date('d F Y', strtotime($item['tanggal_kejadian']))) ?></strong>
                    </div>
                    <div class="meta-item">
                        <span class="label">Pemilik</span>
                        <strong><?= htmlspecialchars($item['pemilik'] ?: 'Tidak diketahui') ?></strong>
                    </div>
                    <div class="meta-item">
                        <span class="label">Kelas</span>
                        <strong><?= htmlspecialchars($item['kelas'] ?: '-') ?></strong>
                    </div>
                </div>

                <div class="desc">
                    <strong>Deskripsi:</strong><br>
                    <?= nl2br(htmlspecialchars($item['deskripsi'])) ?>
                </div>

                <?php if ($wa !== ''): ?>
                    <div class="contact-box">
                        <strong>Hubungi pelapor:</strong><br>
                        <a href="https://wa.me/<?= htmlspecialchars($wa) ?>?text=<?= urlencode('Halo, saya ingin menanyakan tentang barang ' . $item['nama_barang'] . ' yang dilaporkan di LostFound.sch.') ?>" class="btn btn-primary" style="margin-top:12px;" target="_blank">
                            <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
