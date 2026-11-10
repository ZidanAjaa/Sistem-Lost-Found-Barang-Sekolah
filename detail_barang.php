<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$viewerId = (int) ($_SESSION['user_id'] ?? 0);
$isAdmin = !empty($_SESSION['user_id']) && (strtolower($_SESSION['role'] ?? 'user') === 'admin');
$isLoggedIn = $viewerId > 0;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$success = $_SESSION['detail_barang_notice'] ?? '';
unset($_SESSION['detail_barang_notice']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isLoggedIn) {
        header('Location: login.php');
        exit;
    }
    if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Permintaan tidak diizinkan.');
    }

    $action = $_POST['action'] ?? '';
    $stmtAccess = mysqli_prepare($koneksi, "SELECT id, pemilik_id, foto, status_barang FROM barang WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtAccess, 'i', $id);
    mysqli_stmt_execute($stmtAccess);
    $accessItem = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAccess));
    mysqli_stmt_close($stmtAccess);

    if (!$accessItem || (!$isAdmin && (int) $accessItem['pemilik_id'] !== $viewerId)) {
        http_response_code(403);
        exit('Anda tidak memiliki izin untuk mengubah barang ini.');
    }

    if ($action === 'delete') {
        $stmtHistory = mysqli_prepare($koneksi, "SELECT COUNT(*) AS total FROM barang_history WHERE barang_id = ?");
        mysqli_stmt_bind_param($stmtHistory, 'i', $id);
        mysqli_stmt_execute($stmtHistory);
        $historyCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtHistory));
        mysqli_stmt_close($stmtHistory);

        $stmtReturn = mysqli_prepare($koneksi, "
            SELECT COUNT(*) AS total
            FROM pengembalian p
            JOIN laporan l ON l.id = p.laporan_id
            WHERE l.barang_id = ?
        ");
        mysqli_stmt_bind_param($stmtReturn, 'i', $id);
        mysqli_stmt_execute($stmtReturn);
        $returnCount = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtReturn));
        mysqli_stmt_close($stmtReturn);

        if ((int) ($historyCount['total'] ?? 0) > 0 || (int) ($returnCount['total'] ?? 0) > 0) {
            $error = 'Barang yang sudah tercatat dalam riwayat pengembalian tidak dapat dihapus.';
        } else {
            try {
                mysqli_begin_transaction($koneksi);
                $stmtDeleteReports = mysqli_prepare($koneksi, "DELETE FROM laporan WHERE barang_id = ?");
                mysqli_stmt_bind_param($stmtDeleteReports, 'i', $id);
                mysqli_stmt_execute($stmtDeleteReports);
                mysqli_stmt_close($stmtDeleteReports);

                $stmtDeleteItem = mysqli_prepare($koneksi, "DELETE FROM barang WHERE id = ?");
                mysqli_stmt_bind_param($stmtDeleteItem, 'i', $id);
                mysqli_stmt_execute($stmtDeleteItem);
                $deleted = mysqli_stmt_affected_rows($stmtDeleteItem);
                mysqli_stmt_close($stmtDeleteItem);
                if ($deleted !== 1) {
                    throw new RuntimeException('Barang tidak ditemukan atau gagal dihapus.');
                }
                mysqli_commit($koneksi);

                $photoPath = $accessItem['foto'] ?? '';
                if (str_starts_with($photoPath, 'img/laporan/')) {
                    $photoFile = __DIR__ . DIRECTORY_SEPARATOR . $photoPath;
                    if (is_file($photoFile)) {
                        unlink($photoFile);
                    }
                }

                $_SESSION['detail_barang_notice'] = 'Barang berhasil dihapus.';
                header('Location: ' . ($isAdmin ? 'laporan_barang_admin.php' : 'dashboard.php'));
                exit;
            } catch (Throwable $e) {
                mysqli_rollback($koneksi);
                $error = 'Barang gagal dihapus. Data laporan atau riwayat terkait harus tetap dipertahankan.';
            }
        }
    } elseif ($action === 'update') {
        $name = trim($_POST['nama_barang'] ?? '');
        $categoryId = (int) ($_POST['kategori_id'] ?? 0);
        $description = trim($_POST['deskripsi'] ?? '');
        $location = trim($_POST['lokasi_kejadian'] ?? '');
        $date = $_POST['tanggal_kejadian'] ?? '';
        $status = $_POST['status_barang'] ?? '';
        $validDate = DateTime::createFromFormat('!Y-m-d', $date);
        $allowedStatuses = $isAdmin
            ? ['Hilang', 'Ditemukan', 'Dikembalikan']
            : ($accessItem['status_barang'] === 'Dikembalikan' ? ['Dikembalikan'] : ['Hilang', 'Ditemukan']);

        if ($name === '' || mb_strlen($name) > 100 || $categoryId <= 0 || $location === '' ||
            mb_strlen($location) > 100 || !$validDate || $validDate->format('Y-m-d') !== $date ||
            !in_array($status, $allowedStatuses, true) || $description === '') {
            $error = 'Periksa kembali nama, kategori, deskripsi, lokasi, tanggal, dan status barang.';
        } else {
            $stmtCategory = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($stmtCategory, 'i', $categoryId);
            mysqli_stmt_execute($stmtCategory);
            $categoryExists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCategory));
            mysqli_stmt_close($stmtCategory);

            if (!$categoryExists) {
                $error = 'Kategori yang dipilih tidak tersedia.';
            } else {
                $newPhoto = null;
                $uploadedPhotoPath = '';
                try {
                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK || $_FILES['foto']['size'] > 5 * 1024 * 1024) {
                            throw new RuntimeException('Upload foto gagal atau ukuran foto melebihi 5 MB.');
                        }

                        $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
                        $mime = mime_content_type($_FILES['foto']['tmp_name']);
                        if (!isset($allowedMimes[$mime])) {
                            throw new RuntimeException('Format foto harus JPG, PNG, atau WEBP.');
                        }

                        $uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'laporan';
                        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
                            throw new RuntimeException('Folder penyimpanan foto tidak dapat dibuat.');
                        }

                        $fileName = 'laporan_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mime];
                        $uploadedPhotoPath = 'img/laporan/' . $fileName;
                        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDirectory . DIRECTORY_SEPARATOR . $fileName)) {
                            throw new RuntimeException('Foto gagal disimpan.');
                        }
                        $newPhoto = $uploadedPhotoPath;
                    }

                    $photo = $newPhoto ?? $accessItem['foto'];
                    if (!$isAdmin && $accessItem['status_barang'] === 'Dikembalikan') {
                        $status = 'Dikembalikan';
                    }

                    $stmtUpdate = mysqli_prepare($koneksi, "
                        UPDATE barang
                        SET kategori_id = ?, nama_barang = ?, foto = ?, deskripsi = ?,
                            lokasi_kejadian = ?, tanggal_kejadian = ?, status_barang = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    mysqli_stmt_bind_param($stmtUpdate, 'issssssi', $categoryId, $name, $photo, $description, $location, $date, $status, $id);
                    mysqli_stmt_execute($stmtUpdate);
                    mysqli_stmt_close($stmtUpdate);

                    if ($newPhoto && !empty($accessItem['foto']) && str_starts_with($accessItem['foto'], 'img/laporan/')) {
                        $oldPhotoFile = __DIR__ . DIRECTORY_SEPARATOR . $accessItem['foto'];
                        if (is_file($oldPhotoFile)) {
                            unlink($oldPhotoFile);
                        }
                    }
                    $_SESSION['detail_barang_notice'] = 'Data barang berhasil diperbarui.';
                    header('Location: detail_barang.php?id=' . $id);
                    exit;
                } catch (Throwable $e) {
                    if ($uploadedPhotoPath !== '') {
                        $uploadedFile = __DIR__ . DIRECTORY_SEPARATOR . $uploadedPhotoPath;
                        if (is_file($uploadedFile)) {
                            unlink($uploadedFile);
                        }
                    }
                    $error = $e instanceof RuntimeException
                        ? $e->getMessage()
                        : 'Data barang gagal diperbarui. Silakan coba kembali.';
                }
            }
        }
    } else {
        $error = 'Permintaan perubahan barang tidak valid.';
    }
}

$sql = "
    SELECT
        b.id,
        b.kategori_id,
        b.pemilik_id,
        b.nama_barang,
        b.foto,
        b.deskripsi,
        b.lokasi_kejadian,
        b.tanggal_kejadian,
        b.status_barang,
        b.is_deleted,
        k.nama_kategori
    FROM barang b
    LEFT JOIN kategori k ON k.id = b.kategori_id
    WHERE b.id = ?
      AND (b.is_deleted = 0 OR ? = 1)
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $id, $isAdmin);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$item) {
    header('Location: ' . ($isAdmin ? 'laporan_barang_admin.php' : 'index.php'));
    exit;
}

$canManage = $isLoggedIn && ($isAdmin || (int) $item['pemilik_id'] === $viewerId);
$categories = mysqli_query($koneksi, "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori");
$foto = !empty($item['foto']) && file_exists(__DIR__ . '/' . $item['foto'])
    ? $item['foto']
    : 'https://placehold.co/900x600/f3f4f6/374151?text=Foto+Barang';

$wa = preg_replace('/\D+/', '', ADMIN_PHONE);
if (substr($wa, 0, 1) === '0') {
    $wa = '62' . substr($wa, 1);
}
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 12px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            border: 0;
            cursor: pointer;
            font: inherit;
        }
        .btn-primary { background: #3f2de0; color: #fff; }
        .btn-secondary { background: #eef2ff; color: #2c2f5d; }
        .btn-danger { background: #fff0ef; color: #b42318; }
        .notice {
            padding: 13px 16px;
            margin-bottom: 18px;
            border-radius: 10px;
            font-weight: 600;
            line-height: 1.5;
        }
        .notice-success { background: #e9f9ee; color: #177a42; }
        .notice-error { background: #fff1f2; color: #b42318; }
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
        .management-card { margin-top: 24px; }
        .management-card h2 { margin: 0 0 8px; font-size: 1.25rem; }
        .management-note { margin: 0 0 18px; color: #667085; line-height: 1.6; }
        .management-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .management-form {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #edf0f7;
        }
        .form-group { min-width: 0; }
        .form-group-full { grid-column: 1 / -1; }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #344054;
            font-size: 13px;
            font-weight: 700;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            min-height: 42px;
            padding: 10px 12px;
            border: 1px solid #dfe3ef;
            border-radius: 9px;
            background: #fff;
            color: #273344;
            font: inherit;
        }
        .form-group textarea { min-height: 120px; resize: vertical; }
        .form-help { display: block; margin-top: 6px; color: #788391; font-size: 12px; }
        .form-actions { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 10px; }
        .form-actions form { margin: 0; }
        @media (max-width: 900px) {
            .grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .page { padding: 22px 14px 40px; }
            .card { padding: 18px; border-radius: 16px; }
            .foto { height: 300px; }
            .management-form { grid-template-columns: 1fr; }
            .form-group-full, .form-actions { grid-column: 1; }
            .management-actions .btn, .form-actions > .btn, .form-actions form, .form-actions form .btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <h1>Detail Barang</h1>
            <a href="index.php" class="btn btn-secondary">← Kembali</a>
        </div>

        <?php if ($success !== ''): ?>
            <div class="notice notice-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="notice notice-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="grid">
            <div class="card">
                <img src="<?= htmlspecialchars($foto) ?>" alt="<?= htmlspecialchars($item['nama_barang']) ?>" class="foto">
            </div>

            <div class="card">
                <span class="tag <?= $item['status_barang'] === 'Ditemukan' ? 'tag-found' : ($item['status_barang'] === 'Dikembalikan' ? 'tag-found' : 'tag-lost') ?>">
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
                        <span class="label">Verifikasi</span>
                        <strong>Diperiksa admin</strong>
                    </div>
                </div>

                <div class="desc">
                    <strong>Deskripsi:</strong><br>
                    <?= nl2br(htmlspecialchars($item['deskripsi'])) ?>
                </div>

                <?php if ($wa !== ''): ?>
                    <div class="contact-box">
                        <strong>Hubungi admin untuk konfirmasi dan pengambilan:</strong><br>
                        <a href="https://wa.me/<?= htmlspecialchars($wa) ?>?text=<?= urlencode('Halo admin LostFound.sch, saya ingin mengonfirmasi kepemilikan barang ' . $item['nama_barang'] . ' yang dipublikasikan di website.') ?>" class="btn btn-primary" style="margin-top:12px;" target="_blank" rel="noopener">
                            <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($canManage): ?>
            <section class="card management-card" aria-labelledby="manage-item-title">
                <h2 id="manage-item-title">Kelola Barang</h2>
                <p class="management-note">
                    <?= $isAdmin ? 'Sebagai admin, Anda dapat memperbarui atau menghapus data barang.' : 'Anda dapat mengubah atau menghapus barang yang Anda laporkan.' ?>
                </p>
                <div class="management-actions">
                    <a href="barang_crud.php" class="btn btn-secondary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Tambah Barang</a>
                </div>

                <form id="update-item-form" method="POST" action="detail_barang.php?id=<?= (int) $item['id'] ?>" enctype="multipart/form-data" class="management-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="action" value="update">

                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <input type="text" id="nama_barang" name="nama_barang" maxlength="100" value="<?= htmlspecialchars($item['nama_barang']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select id="kategori_id" name="kategori_id" required>
                            <?php while ($category = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (int) $item['kategori_id'] === (int) $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="lokasi_kejadian">Lokasi</label>
                        <input type="text" id="lokasi_kejadian" name="lokasi_kejadian" maxlength="100" value="<?= htmlspecialchars($item['lokasi_kejadian']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="tanggal_kejadian">Tanggal Kejadian</label>
                        <input type="date" id="tanggal_kejadian" name="tanggal_kejadian" value="<?= htmlspecialchars($item['tanggal_kejadian']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="status_barang">Status Barang</label>
                        <select id="status_barang" name="status_barang" <?= !$isAdmin && $item['status_barang'] === 'Dikembalikan' ? 'disabled' : '' ?> required>
                            <option value="Hilang" <?= $item['status_barang'] === 'Hilang' ? 'selected' : '' ?>>Hilang</option>
                            <option value="Ditemukan" <?= $item['status_barang'] === 'Ditemukan' ? 'selected' : '' ?>>Ditemukan</option>
                            <?php if ($isAdmin): ?>
                                <option value="Dikembalikan" <?= $item['status_barang'] === 'Dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                            <?php endif; ?>
                        </select>
                        <?php if (!$isAdmin && $item['status_barang'] === 'Dikembalikan'): ?>
                            <input type="hidden" name="status_barang" value="Dikembalikan">
                            <small class="form-help">Status pengembalian dikelola oleh admin.</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="foto">Foto Barang (opsional)</label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                        <small class="form-help">JPG, PNG, atau WEBP; maksimal 5 MB.</small>
                    </div>
                    <div class="form-group form-group-full">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" required><?= htmlspecialchars($item['deskripsi'] ?? '') ?></textarea>
                    </div>
                </form>
                <div class="form-actions">
                    <button type="submit" form="update-item-form" class="btn btn-primary"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Simpan Perubahan</button>
                    <form method="POST" action="detail_barang.php?id=<?= (int) $item['id'] ?>" onsubmit="return confirm('Hapus barang ini beserta laporan terkait?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Hapus Barang</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>
