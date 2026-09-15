<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user_id'];

if (isset($_POST['hapus'])) {

    $query = mysqli_query(
        $koneksi,
        "DELETE FROM users WHERE id = '$id'"
    );

    if ($query) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Akun</title>
    <link rel="stylesheet" href="css/profil.css">
</head>

<body class="profile-page">

<div class="profile-container">
    <div class="profile-card delete-card">

        <div class="profile-top">
            <div>
                <h1>Hapus Akun</h1>
                <p>Konfirmasi penghapusan akun pengguna.</p>
            </div>
        </div>

        <div class="profile-divider"></div>

        <div class="delete-warning">
            Data akun akan dihapus dari sistem dan tidak dapat
            digunakan kembali.
        </div>

        <form method="POST">

            <div class="profile-actions">

                <button type="submit"
                        name="hapus"
                        class="btn-profile"
                        onclick="return confirm('Yakin ingin menghapus akun?')">
                    Hapus Akun
                </button>

                <a href="profil_user.php" class="btn-profile">
                    Batal
                </a>

            </div>

        </form>

    </div>
</div>

</body>
</html>