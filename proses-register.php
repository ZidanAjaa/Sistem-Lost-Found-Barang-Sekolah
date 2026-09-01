<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nisn = trim($_POST['nisn']);
    $nama = trim($_POST['nama']);
    $kelas = trim($_POST['kelas']);
    $no_telepon = trim($_POST['no_telepon']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi_password'];

    if ($password !== $konfirmasi) {
        $_SESSION['error'] = "Password dan konfirmasi tidak sama!";
        header("Location: register.php");
        exit;
    }

    $cek = $koneksi->prepare("SELECT id FROM login WHERE username = ?");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        $_SESSION['error'] = "Username sudah digunakan!";
        header("Location: register.php");
        exit;
    }
    $cek->close();

    $koneksi->begin_transaction();

    try {
        $stmt1 = $koneksi->prepare("INSERT INTO users (nisn, nama, kelas, no_telepon, role, created_at, updated_at) VALUES (?, ?, ?, ?, 'user', NOW(), NOW())");
        $stmt1->bind_param("ssss", $nisn, $nama, $kelas, $no_telepon);
        $stmt1->execute();

        $user_id = $koneksi->insert_id;

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt2 = $koneksi->prepare("INSERT INTO login (user_id, username, password, status) VALUES (?, ?, ?, 'aktif')");
        $stmt2->bind_param("iss", $user_id, $username, $password_hash);
        $stmt2->execute();

        $koneksi->commit();

        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header("Location: login.php");
        exit;

    } catch (Exception $e) {
        $koneksi->rollback();
        $_SESSION['error'] = "Registrasi gagal: " . $e->getMessage();
        header("Location: register.php");
        exit;
    }

} else {
    header("Location: register.php");
    exit;
}