<?php
session_start();
require_once __DIR__ . "/config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $koneksi->prepare("
        SELECT login.id, login.user_id, login.username, login.password, login.status, users.nama, users.role
        FROM login
        JOIN users ON login.user_id = users.id
        WHERE login.username = ?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $data = $result->fetch_assoc();

        if ($data['status'] !== 'aktif') {
            $_SESSION['error'] = "Akun kamu nonaktif. Hubungi admin.";
            header("Location: login.php");
            exit;
        }

        if (password_verify($password, $data['password'])) {
            // Login sukses, simpan data ke session
            $_SESSION['user_id'] = $data['user_id'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role'];

            // Update last_login
            $update = $koneksi->prepare("UPDATE login SET last_login = NOW() WHERE id = ?");
            $update->bind_param("i", $data['id']);
            $update->execute();

            // Redirect sesuai role
            if ($data['role'] === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;

        } else {
            $_SESSION['error'] = "Password salah!";
            header("Location: login.php");
            exit;
        }

    } else {
        $_SESSION['error'] = "Username tidak ditemukan!";
        header("Location: login.php");
        exit;
    }

} else {
    header("Location: login.php");
    exit;
}