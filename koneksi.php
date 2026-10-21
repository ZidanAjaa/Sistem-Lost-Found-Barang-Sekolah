<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "lost_found";
define("ADMIN_PHONE", "081331841410");

$koneksi = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($koneksi, "utf8mb4");

?>