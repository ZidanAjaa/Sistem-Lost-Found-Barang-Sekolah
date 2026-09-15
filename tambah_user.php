<?php
require_once "koneksi.php";

$pesan = "";

if (isset($_POST['tambah'])) {

    $username = $_POST['username'];
    $nisn = $_POST['NISN'];
    $nama = $_POST['Nama'];
    $kelas = $_POST['Kelas'];
    $telepon = $_POST['No_Telepon'];
    $role = $_POST['Role'];

    $query = "INSERT INTO users
              (username, NISN, Nama, Kelas, No_Telepon, Role)
              VALUES
              ('$username', '$nisn', '$nama', '$kelas', '$telepon', '$role')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: profil_user.php");
        exit;
    } else {
        $pesan = "Data gagal ditambahkan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
    <link rel="stylesheet" href="css/profil.css">
</head>

<body class="profile-page">

<div class="profile-container">
    <div class="profile-card">

        <div class="profile-top">
            <div>
                <h1>Tambah User</h1>
                <p>Tambahkan data pengguna baru.</p>
            </div>
        </div>

        <div class="profile-divider"></div>

        <?php if ($pesan): ?>
            <div class="form-error"><?= $pesan ?></div>
        <?php endif; ?>

        <form method="POST" class="profile-form">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>NISN</label>
                <input type="text" name="NISN" required>
            </div>

            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="Nama" required>
            </div>

            <div class="form-group">
                <label>Kelas</label>
                <input type="text" name="Kelas" required>
            </div>

            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="No_Telepon" required>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="Role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="profile-actions">
                <button type="submit" name="tambah" class="btn-profile">
                    Simpan
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