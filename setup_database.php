<?php
$host = "localhost";
$user = "root";
$pass = "";
$database = "lost_found";

$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("Koneksi ke MySQL gagal: " . mysqli_connect_error());
}

$sql_create_db = "CREATE DATABASE IF NOT EXISTS `$database`";
if (mysqli_query($conn, $sql_create_db)) {
    echo "Database $database berhasil dibuat.<br>";
} else {
    die("Gagal membuat database: " . mysqli_error($conn));
}

mysqli_select_db($conn, $database);

$queries = [
    "users" => "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nisn VARCHAR(20) NOT NULL,
            nama VARCHAR(100) NOT NULL,
            kelas VARCHAR(20) NOT NULL,
            no_telepon VARCHAR(15) NOT NULL,.
            role ENUM('admin','user') NOT NULL DEFAULT 'user',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ",
    "login" => "
        CREATE TABLE IF NOT EXISTS login (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            last_login DATETIME NULL,
            status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ",
    "kategori" => "
        CREATE TABLE IF NOT EXISTS kategori (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_kategori VARCHAR(50) NOT NULL UNIQUE
        )
    ",
    "barang" => "
        CREATE TABLE IF NOT EXISTS barang (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kategori_id INT NOT NULL,
            pemilik_id INT NOT NULL,
            nama_barang VARCHAR(100) NOT NULL,
            foto VARCHAR(255) NULL,
            deskripsi TEXT NULL,
            lokasi_kejadian VARCHAR(100) NOT NULL,
            tanggal_kejadian DATE NOT NULL,
            status_barang ENUM('Hilang','Ditemukan','Dikembalikan') NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (kategori_id) REFERENCES kategori(id),
            FOREIGN KEY (pemilik_id) REFERENCES users(id)
        )
    ",
    "laporan" => "
        CREATE TABLE IF NOT EXISTS laporan (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            barang_id INT NOT NULL,
            jenis_laporan ENUM('Hilang','Temuan') NOT NULL,
            deskripsi TEXT NULL,
            tanggal_lapor DATE NOT NULL,
            status ENUM('Pending','Diproses','Selesai') NOT NULL DEFAULT 'Pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (barang_id) REFERENCES barang(id)
        )
    ",
    "pengembalian" => "
        CREATE TABLE IF NOT EXISTS pengembalian (
            id INT AUTO_INCREMENT PRIMARY KEY,
            laporan_id INT NOT NULL,
            admin_id INT NOT NULL,
            tanggal_kembali DATE NOT NULL,
            catatan TEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (laporan_id) REFERENCES laporan(id),
            FOREIGN KEY (admin_id) REFERENCES users(id)
        )
    ",
];

foreach ($queries as $nama_tabel => $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "Tabel $nama_tabel berhasil dibuat.<br>";
    } else {
        echo "Gagal membuat tabel $nama_tabel: " . mysqli_error($conn) . "<br>";
    }
}

$cek_kategori = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM kategori");
$jumlah_kategori = mysqli_fetch_assoc($cek_kategori)['jumlah'];

if ($jumlah_kategori == 0) {
    $kategori_default = ["Tas & Dompet", "Elektronik", "Alat Tulis", "Pakaian", "Dokumen"];
    $stmt = mysqli_prepare($conn, "INSERT INTO kategori (nama_kategori) VALUES (?)");
    foreach ($kategori_default as $nama) {
        mysqli_stmt_bind_param($stmt, "s", $nama);
        mysqli_stmt_execute($stmt);
    }
    echo "Data kategori awal berhasil ditambahkan.<br>";
}

echo "<br>Setup database selesai.";
mysqli_close($conn);
?>