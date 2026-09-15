<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION["user_id"];

$query = "SELECT users.id, users.nisn, users.nama, users.kelas,
                 users.no_telepon, login.username
          FROM users
          INNER JOIN login ON users.id = login.user_id
          WHERE users.id = ?
          LIMIT 1";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "Data pengguna tidak ditemukan.";
    exit;
}

/* Hapus akun */
if (isset($_POST["hapus_akun"])) {

    mysqli_begin_transaction($koneksi);

    try {
        mysqli_query($koneksi, "DELETE FROM login WHERE user_id = $id");
        mysqli_query($koneksi, "DELETE FROM users WHERE id = $id");

        mysqli_commit($koneksi);

        session_destroy();
        header("Location: login.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "Akun gagal dihapus.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
    <link rel="stylesheet" href="css/profil.css">
</head>

<body class="profile-page">

<div class="profile-container">
    <div class="profile-card">

        <div class="profile-top">
            <div>
                <h1>Informasi Pengguna</h1>
                <p>Data akun yang terdaftar pada sistem.</p>
            </div>

            <div class="status-active">Aktif</div>
        </div>

        <div class="profile-divider"></div>

        <div class="profile-grid">

            <div class="info-box">
                <label>Username</label>
                <strong><?= htmlspecialchars($user["username"]) ?></strong>
            </div>

            <div class="info-box">
                <label>NISN</label>
                <strong><?= htmlspecialchars($user["nisn"]) ?></strong>
            </div>

            <div class="info-box">
                <label>Nama Lengkap</label>
                <strong><?= htmlspecialchars($user["nama"]) ?></strong>
            </div>

            <div class="info-box">
                <label>Kelas</label>
                <strong><?= htmlspecialchars($user["kelas"]) ?></strong>
            </div>

            <div class="info-box">
                <label>No. Telepon</label>
                <strong><?= htmlspecialchars($user["no_telepon"]) ?></strong>
            </div>

        </div>

        <div class="profile-actions">

            <a href="update_profil.php" class="btn-edit">
                Edit Profil
            </a>

            <a href="index.php" class="btn-kembali">
                Kembali ke Beranda
            </a>

            <form method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus akun ini?');"
                  style="display:inline;">

                <button type="submit"
                        name="hapus_akun"
                        class="btn-hapus">
                    Hapus Akun
                </button>

            </form>

            <a href="logout.php" class="btn-logout">
                Logout
            </a>

        </div>

    </div>
</div>

</body>
</html>