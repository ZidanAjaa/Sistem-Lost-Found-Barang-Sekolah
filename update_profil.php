<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION["user_id"];

$query = "SELECT users.nisn, users.nama, users.kelas, users.no_telepon,
                 login.username
          FROM users
          JOIN login ON users.id = login.user_id
          WHERE users.id = $id";

$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

if (isset($_POST["simpan"])) {

    $username = $_POST["username"];
    $nisn = $_POST["nisn"];
    $nama = $_POST["nama"];
    $kelas = $_POST["kelas"];
    $no_telepon = $_POST["no_telepon"];

    mysqli_query($koneksi, "UPDATE users SET
        nisn = '$nisn',
        nama = '$nama',
        kelas = '$kelas',
        no_telepon = '$no_telepon'
        WHERE id = $id
    ");

    mysqli_query($koneksi, "UPDATE login SET
        username = '$username'
        WHERE user_id = $id
    ");

    header("Location: profil_user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Profil - LostFound.sch</title>

    <link rel="stylesheet" href="css/profil.css">

</head>

<body>

<div class="profile-page">

    <div class="profile-container">

        <div class="profile-card">

            <h1>Edit Profil</h1>
            <p>Ubah data profil kamu.</p>

            <div class="profile-divider"></div>

            <form method="POST" class="profile-form">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text"
                           name="username"
                           value="<?= htmlspecialchars($user['username']) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>NISN</label>
                    <input type="text"
                           name="nisn"
                           value="<?= htmlspecialchars($user['nisn']) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text"
                           name="nama"
                           value="<?= htmlspecialchars($user['nama']) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>Kelas</label>
                    <input type="text"
                           name="kelas"
                           value="<?= htmlspecialchars($user['kelas']) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text"
                           name="no_telepon"
                           value="<?= htmlspecialchars($user['no_telepon']) ?>"
                           required>
                </div>

                <div class="profile-actions">

                    <button type="submit"
                            name="simpan"
                            class="btn-profilee">
                        Simpan Perubahan
                    </button>

                    <a href="profil_user.php"
                       class="btn-profilei">
                        Batal
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>