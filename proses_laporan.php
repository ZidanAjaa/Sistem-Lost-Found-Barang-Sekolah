<?php
session_start();
require_once __DIR__ . "/koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$jenis = $_POST["jenis_laporan"] ?? "";

if (!in_array($jenis, ["Hilang", "Temuan"], true)) {
    die("Jenis laporan tidak valid.");
}

$nama_barang = trim($_POST["nama_barang"] ?? "");
$kategori_id = (int)($_POST["kategori_id"] ?? 0);
$deskripsi = trim($_POST["deskripsi"] ?? "");
$lokasi = trim($_POST["lokasi_kejadian"] ?? "");
$tanggal = $_POST["tanggal_kejadian"] ?? "";

if ($nama_barang === "" || $kategori_id <= 0 || $lokasi === "" || $tanggal === "" || $deskripsi === "") {
    die("Semua data wajib diisi.");
}

/* Validasi kategori */
$cek = $koneksi->prepare("SELECT id FROM kategori WHERE id = ? LIMIT 1");
$cek->bind_param("i", $kategori_id);
$cek->execute();
if ($cek->get_result()->num_rows !== 1) {
    die("Kategori tidak ditemukan.");
}
$cek->close();

/* Upload foto */
$foto = null;

if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        die("Upload foto gagal.");
    }

    if ($_FILES["foto"]["size"] > 5 * 1024 * 1024) {
        die("Ukuran foto maksimal 5 MB.");
    }

    $tmp = $_FILES["foto"]["tmp_name"];
    $mime = mime_content_type($tmp);
    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowed[$mime])) {
        die("Format foto harus JPG, PNG, atau WEBP.");
    }

    $dir = __DIR__ . "/img/laporan";
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $namaFile = "laporan_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $allowed[$mime];
    if (!move_uploaded_file($tmp, $dir . "/" . $namaFile)) {
        die("Foto gagal disimpan.");
    }

    $foto = "img/laporan/" . $namaFile;
}

$status_barang = ($jenis === "Hilang") ? "Hilang" : "Ditemukan";

/*
 * Struktur tabel saat ini mewajibkan barang.pemilik_id NOT NULL.
 * Untuk laporan temuan, sistem memakai user yang membuat laporan
 * sebagai pemilik sementara/pelapor sampai admin mencocokkan pemilik asli.
 */
$koneksi->begin_transaction();

try {
    $stmtBarang = $koneksi->prepare("
        INSERT INTO barang
        (kategori_id, pemilik_id, nama_barang, foto, deskripsi, lokasi_kejadian, tanggal_kejadian, status_barang, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");

    $stmtBarang->bind_param(
        "iissssss",
        $kategori_id,
        $user_id,
        $nama_barang,
        $foto,
        $deskripsi,
        $lokasi,
        $tanggal,
        $status_barang
    );
    $stmtBarang->execute();

    $barang_id = $koneksi->insert_id;
    $stmtBarang->close();

    $stmtLaporan = $koneksi->prepare("
        INSERT INTO laporan
        (user_id, barang_id, jenis_laporan, deskripsi, tanggal_lapor, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, CURDATE(), 'Pending', NOW(), NOW())
    ");

    $stmtLaporan->bind_param(
        "iiss",
        $user_id,
        $barang_id,
        $jenis,
        $deskripsi
    );
    $stmtLaporan->execute();
    $stmtLaporan->close();

    $koneksi->commit();

    header("Location: index.php?lapor=sukses&jenis=" . urlencode($jenis));
    exit;

} catch (Throwable $e) {
    $koneksi->rollback();

    if ($foto && file_exists(__DIR__ . "/" . $foto)) {
        unlink(__DIR__ . "/" . $foto);
    }

    die("Laporan gagal disimpan: " . htmlspecialchars($e->getMessage()));
}
?>
