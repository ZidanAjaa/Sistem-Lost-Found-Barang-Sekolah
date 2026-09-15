<?php
session_start();
require_once __DIR__ . "/koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$kategori = mysqli_query($koneksi, "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lapor Barang Hilang - LostFound.sch</title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<style>
body{font-family:Arial,sans-serif;background:#f7f7fb;margin:0;color:#17173a}
.form-page{max-width:850px;margin:50px auto;padding:20px}
.form-card{background:#fff;border-radius:20px;padding:32px;box-shadow:0 8px 30px rgba(0,0,0,.08)}
h1{margin-top:0}.subtitle{color:#666}
.form-group{margin-bottom:18px}.form-group label{display:block;font-weight:700;margin-bottom:7px}
input,select,textarea{width:100%;box-sizing:border-box;padding:12px 14px;border:1px solid #ddd;border-radius:10px;font-size:15px}
textarea{min-height:120px;resize:vertical}
.actions{display:flex;gap:12px;margin-top:25px}.btn{display:inline-block;padding:13px 20px;border-radius:10px;text-decoration:none;border:0;cursor:pointer;font-weight:700}
.btn-primary{background:#3f2de0;color:#fff}.btn-back{background:#eee;color:#222}
.notice{padding:12px;background:#fff3cd;border-radius:10px;margin-bottom:20px}
</style>
</head>
<body>
<div class="form-page">
<div class="form-card">
<h1><i class="fa-solid fa-triangle-exclamation"></i> Lapor Barang Hilang</h1>
<p class="subtitle">Isi data barang yang hilang dengan lengkap agar mudah dicocokkan.</p>
<div class="notice">Laporan akan tersimpan sebagai <b>Hilang</b> dan masuk ke daftar barang.</div>

<form action="proses_laporan.php" method="POST" enctype="multipart/form-data">
<input type="hidden" name="jenis_laporan" value="Hilang">

<div class="form-group">
<label for="nama_barang">Nama Barang</label>
<input type="text" id="nama_barang" name="nama_barang" placeholder="Contoh: Dompet hitam" required>
</div>

<div class="form-group">
<label for="kategori_id">Kategori</label>
<select id="kategori_id" name="kategori_id" required>
<option value="">-- Pilih kategori --</option>
<?php while($k = mysqli_fetch_assoc($kategori)): ?>
<option value="<?= (int)$k['id'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
<?php endwhile; ?>
</select>
</div>

<div class="form-group">
<label for="lokasi_kejadian">Lokasi Kehilangan</label>
<input type="text" id="lokasi_kejadian" name="lokasi_kejadian" placeholder="Contoh: Lab Oracle" required>
</div>

<div class="form-group">
<label for="tanggal_kejadian">Tanggal Kejadian</label>
<input type="date" id="tanggal_kejadian" name="tanggal_kejadian" required>
</div>

<div class="form-group">
<label for="deskripsi">Deskripsi Barang</label>
<textarea id="deskripsi" name="deskripsi" placeholder="Warna, ciri khusus, isi barang, dan informasi lain..." required></textarea>
</div>

<div class="form-group">
<label for="foto">Foto Barang (opsional)</label>
<input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp">
</div>

<div class="actions">
<a href="index.php" class="btn btn-back">Kembali</a>
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Kirim Laporan</button>
</div>
</form>
</div>
</div>
</body>
</html>
