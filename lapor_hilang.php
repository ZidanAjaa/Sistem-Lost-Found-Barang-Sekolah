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
body {
    font-family: "Segoe UI", Arial, sans-serif;
    margin: 0;
    min-height: 100vh;
    color: #1f2a44;
    background: linear-gradient(135deg, rgba(191, 219, 254, 0.72), rgba(224, 242, 254, 0.68), rgba(239, 246, 255, 0.8));
    background-attachment: fixed;
}

.form-page {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
}

.form-card {
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid rgba(147, 197, 253, 0.45);
    border-radius: 28px;
    padding: 34px 40px;
    box-shadow: 0 18px 45px rgba(37, 99, 235, 0.12);
    backdrop-filter: blur(8px);
}

h1 {
    margin-top: 0;
    margin-bottom: 12px;
    color: #1d4ed8;
    font-size: clamp(2rem, 2.5vw, 2.8rem);
    display: flex;
    align-items: center;
    gap: 12px;
}

.subtitle {
    color: #475569;
    font-size: 1.05rem;
    margin-bottom: 22px;
    line-height: 1.7;
}

.notice {
    padding: 16px 18px;
    background: linear-gradient(135deg, rgba(254, 240, 138, 0.7), rgba(254, 224, 182, 0.8));
    border: 1px solid rgba(251, 191, 36, 0.45);
    border-radius: 14px;
    margin-bottom: 26px;
    color: #7c4a00;
    font-weight: 600;
    line-height: 1.6;
}

.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;
    font-weight: 800;
    margin-bottom: 8px;
    color: #1e3a8a;
    font-size: 1rem;
}

input,
select,
textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 14px 16px;
    border-radius: 14px;
    border: 1.5px solid #bfdbfe;
    background: rgba(248, 250, 255, 0.8);
    font-size: 15px;
    color: #1f2937;
    transition: 0.2s ease;
    box-shadow: inset 0 1px 3px rgba(148, 163, 184, 0.12);
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #60a5fa;
    box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.18);
    background: #fff;
}

textarea {
    min-height: 135px;
    resize: vertical;
}

.actions {
    display: flex;
    gap: 14px;
    margin-top: 28px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 22px;
    border-radius: 12px;
    text-decoration: none;
    border: 0;
    cursor: pointer;
    font-weight: 800;
    transition: 0.22s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 18px rgba(59, 130, 246, 0.18);
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #2563eb, #1d4ed8);
    color: white;
    box-shadow: 0 12px 22px rgba(37, 99, 235, 0.28);
}

.btn-back {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1d4ed8;
    border: 1px solid rgba(96, 165, 250, 0.5);
}

@media (max-width: 640px) {
    .form-card {
        padding: 24px 18px;
        border-radius: 20px;
    }

    .actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}
</style>
</head>
<body>
<div class="form-page">
<div class="form-card">
<h1><i class="fa-solid fa-triangle-exclamation"></i> Lapor Barang Hilang</h1>
<p class="subtitle">Isi data barang yang hilang dengan lengkap agar admin dapat membantu pencarian dan menghubungi kamu jika perlu konfirmasi.</p>
<div class="notice">Laporan akan dikirim ke admin untuk diperiksa dan ditindaklanjuti. Status laporan dapat dilihat dari dashboard.</div>

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
