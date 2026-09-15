<?php
session_start();
require_once __DIR__ . "/koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = (int) $_SESSION["user_id"];
$success = "";
$error = "";
$edit_id = isset($_GET["edit"]) ? (int) $_GET["edit"] : 0;
$selectedJenis = in_array($_GET["jenis"] ?? "Hilang", ["Hilang", "Temuan"], true) ? $_GET["jenis"] : "Hilang";

function uploadFotoBarang($file): ?string
{
    if (!isset($file) || !is_array($file) || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Upload foto gagal.");
    }

    if ($file["size"] > 5 * 1024 * 1024) {
        throw new RuntimeException("Ukuran foto maksimal 5 MB.");
    }

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp",
    ];

    $tmp = $file["tmp_name"];
    $mime = mime_content_type($tmp);

    if (!isset($allowed[$mime])) {
        throw new RuntimeException("Format foto harus JPG, PNG, atau WEBP.");
    }

    $dir = __DIR__ . "/img/laporan";
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $namaFile = "laporan_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $allowed[$mime];
    $target = $dir . "/" . $namaFile;

    if (!move_uploaded_file($tmp, $target)) {
        throw new RuntimeException("Foto gagal disimpan.");
    }

    return "img/laporan/" . $namaFile;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "save";

    if ($action === "delete") {
        $barang_id = (int) ($_POST["barang_id"] ?? 0);
        $stmtCheck = mysqli_prepare($koneksi, "SELECT foto FROM barang WHERE id = ? AND pemilik_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmtCheck, "ii", $barang_id, $user_id);
        mysqli_stmt_execute($stmtCheck);
        $result = mysqli_stmt_get_result($stmtCheck);
        $barang = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmtCheck);

        if (!$barang) {
            $error = "Data barang tidak ditemukan.";
        } else {
            $fotoPath = $barang["foto"] ?? null;
            mysqli_begin_transaction($koneksi);
            try {
                $stmtLaporan = mysqli_prepare($koneksi, "DELETE FROM laporan WHERE barang_id = ?");
                mysqli_stmt_bind_param($stmtLaporan, "i", $barang_id);
                mysqli_stmt_execute($stmtLaporan);
                mysqli_stmt_close($stmtLaporan);

                $stmtBarang = mysqli_prepare($koneksi, "DELETE FROM barang WHERE id = ? AND pemilik_id = ?");
                mysqli_stmt_bind_param($stmtBarang, "ii", $barang_id, $user_id);
                mysqli_stmt_execute($stmtBarang);
                mysqli_stmt_close($stmtBarang);

                mysqli_commit($koneksi);

                if ($fotoPath && file_exists(__DIR__ . "/" . $fotoPath)) {
                    unlink(__DIR__ . "/" . $fotoPath);
                }

                $success = "Barang berhasil dihapus.";
            } catch (Throwable $e) {
                mysqli_rollback($koneksi);
                $error = "Gagal menghapus barang: " . htmlspecialchars($e->getMessage());
            }
        }
    } else {
        $barang_id = (int) ($_POST["barang_id"] ?? 0);
        $nama_barang = trim($_POST["nama_barang"] ?? "");
        $kategori_id = (int) ($_POST["kategori_id"] ?? 0);
        $jenis = in_array($_POST["jenis"] ?? "Hilang", ["Hilang", "Temuan"], true) ? $_POST["jenis"] : "Hilang";
        $lokasi = trim($_POST["lokasi_kejadian"] ?? "");
        $tanggal = $_POST["tanggal_kejadian"] ?? "";
        $deskripsi = trim($_POST["deskripsi"] ?? "");

        if ($nama_barang === "" || $kategori_id <= 0 || $lokasi === "" || $tanggal === "" || $deskripsi === "") {
            $error = "Semua field wajib diisi.";
        } else {
            $cekKategori = mysqli_prepare($koneksi, "SELECT id FROM kategori WHERE id = ? LIMIT 1");
            mysqli_stmt_bind_param($cekKategori, "i", $kategori_id);
            mysqli_stmt_execute($cekKategori);
            $kategoriResult = mysqli_stmt_get_result($cekKategori);
            mysqli_stmt_close($cekKategori);

            if (mysqli_num_rows($kategoriResult) !== 1) {
                $error = "Kategori tidak valid.";
            } else {
                try {
                    $foto = null;
                    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE) {
                        $foto = uploadFotoBarang($_FILES["foto"]);
                    }

                    if ($barang_id > 0) {
                        $stmtCheck = mysqli_prepare($koneksi, "SELECT foto FROM barang WHERE id = ? AND pemilik_id = ? LIMIT 1");
                        mysqli_stmt_bind_param($stmtCheck, "ii", $barang_id, $user_id);
                        mysqli_stmt_execute($stmtCheck);
                        $existingResult = mysqli_stmt_get_result($stmtCheck);
                        $existingBarang = mysqli_fetch_assoc($existingResult);
                        mysqli_stmt_close($stmtCheck);

                        if (!$existingBarang) {
                            throw new RuntimeException("Data barang tidak ditemukan untuk diedit.");
                        }

                        $fotoFinal = $foto ?? $existingBarang["foto"];
                        if ($foto && $existingBarang["foto"] && file_exists(__DIR__ . "/" . $existingBarang["foto"])) {
                            unlink(__DIR__ . "/" . $existingBarang["foto"]);
                        }

                        $stmtUpdate = mysqli_prepare($koneksi, "
                            UPDATE barang
                            SET kategori_id = ?, nama_barang = ?, foto = ?, deskripsi = ?, lokasi_kejadian = ?, tanggal_kejadian = ?, status_barang = ?, updated_at = NOW()
                            WHERE id = ? AND pemilik_id = ?
                        ");

                        mysqli_stmt_bind_param($stmtUpdate, "issssssii", $kategori_id, $nama_barang, $fotoFinal, $deskripsi, $lokasi, $tanggal, $jenis, $barang_id, $user_id);
                        mysqli_stmt_execute($stmtUpdate);
                        mysqli_stmt_close($stmtUpdate);

                        $success = "Barang berhasil diperbarui.";
                    } else {
                        $stmtInsert = mysqli_prepare($koneksi, "
                            INSERT INTO barang (kategori_id, pemilik_id, nama_barang, foto, deskripsi, lokasi_kejadian, tanggal_kejadian, status_barang, created_at, updated_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                        ");

                        mysqli_stmt_bind_param($stmtInsert, "iissssss", $kategori_id, $user_id, $nama_barang, $foto, $deskripsi, $lokasi, $tanggal, $jenis);
                        mysqli_stmt_execute($stmtInsert);
                        mysqli_stmt_close($stmtInsert);

                        $success = "Barang berhasil ditambahkan.";
                    }

                    $selectedJenis = $jenis;
                    $edit_id = 0;
                } catch (Throwable $e) {
                    $error = "Gagal menyimpan data barang: " . htmlspecialchars($e->getMessage());
                }
            }
        }
    }
}

if ($edit_id > 0) {
    $stmtEdit = mysqli_prepare($koneksi, "SELECT * FROM barang WHERE id = ? AND pemilik_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmtEdit, "ii", $edit_id, $user_id);
    mysqli_stmt_execute($stmtEdit);
    $editData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtEdit));
    mysqli_stmt_close($stmtEdit);

    if ($editData) {
        $selectedJenis = $editData["status_barang"];
    }
}

$kategori = mysqli_query($koneksi, "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC");

$barangList = mysqli_query($koneksi, "
    SELECT b.*, k.nama_kategori
    FROM barang b
    JOIN kategori k ON k.id = b.kategori_id
    WHERE b.pemilik_id = $user_id
    ORDER BY b.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Barang - LostFound.sch</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f3f5fb; margin: 0; color: #1f2430; }
        .container { max-width: 1200px; margin: 0 auto; padding: 32px 20px 60px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .topbar a { color: #3f2de0; text-decoration: none; font-weight: 700; }
        .panel { background: #fff; border-radius: 18px; padding: 28px; box-shadow: 0 10px 25px rgba(31, 36, 48, 0.06); }
        .grid { display: grid; grid-template-columns: 1.1fr 1.4fr; gap: 24px; margin-top: 22px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 8px; }
        input, select, textarea { width: 100%; box-sizing: border-box; padding: 12px 14px; border: 1px solid #dfe3ef; border-radius: 10px; font-size: 15px; }
        textarea { min-height: 120px; resize: vertical; }
        .btn { display: inline-block; padding: 12px 18px; border: none; border-radius: 10px; text-decoration: none; cursor: pointer; font-weight: 700; }
        .btn-primary { background: #3f2de0; color: #fff; }
        .btn-secondary { background: #eef2ff; color: #2c2f5d; }
        .btn-danger { background: #ffebee; color: #b42318; }
        .alert { padding: 12px 14px; border-radius: 10px; margin-bottom: 18px; font-weight: 600; }
        .alert-success { background: #e9f9ee; color: #177a42; }
        .alert-error { background: #fff1f2; color: #b42318; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #edf0f7; text-align: left; vertical-align: top; }
        th { background: #f6f8ff; color: #2d3666; }
        .badge { display: inline-block; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-hilang { background: #fff2e5; color: #c77700; }
        .badge-temuan { background: #ebf9f1; color: #18794e; }
        .img-preview { width: 90px; height: 70px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7ef; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        @media (max-width: 900px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <h1 style="margin: 0;">Kelola Barang Hilang & Ditemukan</h1>
            <a href="dashboard.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <?php if ($success !== ""): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="grid">
            <div class="panel">
                <h2 style="margin-top:0;"><?= ($edit_id > 0 ? "Edit" : "Tambah") . " Barang" ?></h2>
                <form method="POST" action="barang_crud.php?jenis=<?= urlencode($selectedJenis) ?><?= $edit_id > 0 ? "&edit=" . $edit_id : "" ?>" enctype="multipart/form-data">
                    <input type="hidden" name="barang_id" value="<?= (int) ($editData["id"] ?? 0) ?>">
                    <input type="hidden" name="action" value="save">

                    <div class="form-group">
                        <label for="jenis">Jenis Laporan</label>
                        <select id="jenis" name="jenis" required>
                            <option value="Hilang" <?= (($selectedJenis ?? "Hilang") === "Hilang" ? "selected" : "") ?>>Hilang</option>
                            <option value="Temuan" <?= (($selectedJenis ?? "Hilang") === "Temuan" ? "selected" : "") ?>>Ditemukan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nama_barang">Nama Barang</label>
                        <input type="text" id="nama_barang" name="nama_barang" value="<?= htmlspecialchars($editData["nama_barang"] ?? "") ?>" placeholder="Contoh: Dompet hitam" required>
                    </div>

                    <div class="form-group">
                        <label for="kategori_id">Kategori</label>
                        <select id="kategori_id" name="kategori_id" required>
                            <option value="">-- Pilih kategori --</option>
                            <?php while ($k = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= (int) $k["id"] ?>" <?= (($editData["kategori_id"] ?? 0) == $k["id"] ? "selected" : "") ?>><?= htmlspecialchars($k["nama_kategori"]) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="lokasi_kejadian">Lokasi</label>
                        <input type="text" id="lokasi_kejadian" name="lokasi_kejadian" value="<?= htmlspecialchars($editData["lokasi_kejadian"] ?? "") ?>" placeholder="Contoh: Lab Oracle" required>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_kejadian">Tanggal</label>
                        <input type="date" id="tanggal_kejadian" name="tanggal_kejadian" value="<?= htmlspecialchars($editData["tanggal_kejadian"] ?? "") ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" placeholder="Deskripsi lengkap barang..." required><?= htmlspecialchars($editData["deskripsi"] ?? "") ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="foto">Foto Barang (opsional)</label>
                        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
                        <?php if (!empty($editData["foto"])): ?>
                            <div style="margin-top:10px;">
                                <img src="<?= htmlspecialchars($editData["foto"]) ?>" alt="Foto barang" class="img-preview">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="actions">
                        <button type="submit" class="btn btn-primary"><?= $edit_id > 0 ? "Update Barang" : "Simpan Barang" ?></button>
                        <?php if ($edit_id > 0): ?>
                            <a href="barang_crud.php" class="btn btn-secondary">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="panel">
                <h2 style="margin-top:0;">Daftar Barang Saya</h2>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Jenis</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($barangList) === 0): ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; color:#667085; padding:20px;">Belum ada barang yang ditambahkan.</td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($barangList)): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($row["foto"])): ?>
                                                <img src="<?= htmlspecialchars($row["foto"]) ?>" alt="Foto barang" class="img-preview" style="display:block; margin-bottom:8px;">
                                            <?php endif; ?>
                                            <strong><?= htmlspecialchars($row["nama_barang"]) ?></strong><br>
                                            <small><?= htmlspecialchars($row["nama_kategori"]) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= $row["status_barang"] === "Hilang" ? "badge-hilang" : "badge-temuan" ?>">
                                                <?= htmlspecialchars($row["status_barang"]) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($row["lokasi_kejadian"]) ?></td>
                                        <td><?= htmlspecialchars(date("d-m-Y", strtotime($row["tanggal_kejadian"]))) ?></td>
                                        <td>
                                            <div class="actions">
                                                <a href="barang_crud.php?edit=<?= (int) $row["id"] ?>" class="btn btn-secondary" style="padding:8px 10px;">Edit</a>
                                                <form method="POST" action="barang_crud.php" style="display:inline;" onsubmit="return confirm('Hapus barang ini?')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="barang_id" value="<?= (int) $row["id"] ?>">
                                                    <button type="submit" class="btn btn-danger" style="padding:8px 10px;">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
