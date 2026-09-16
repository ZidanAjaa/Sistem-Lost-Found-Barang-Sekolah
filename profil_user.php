<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION["user_id"];

$sql = "SELECT users.*, login.username, login.last_login, login.status
        FROM users
        JOIN login ON users.id = login.user_id
        WHERE users.id = ?
        LIMIT 1";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("Data pengguna tidak ditemukan.");
}

$foto = "";

if (!empty($user["foto_profil"])) {
    $file = "img/profil/" . $user["foto_profil"];

    if (file_exists($file)) {
        $foto = $file;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Profil User - LostFound.sch</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profil.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
</head>

<body>

<main class="profile-page">

    <div class="profile-container">

        <div class="profile-card">
          <div class="profile-top">

    <div class="profile-user">

        <div class="profile-photo">
            <?php if ($foto): ?>
                <img src="<?= htmlspecialchars($foto) ?>" alt="Foto Profil">
            <?php else: ?>
                <i class="fa-solid fa-user"></i>
            <?php endif; ?>
        </div>

        <div>
            <h1>Profil User</h1>
       
        </div>

    </div>

</div>

            <div class="profile-divider"></div>
            <div class="profile-grid">

                <div class="info-box">
                    <label>Username</label>
                    <strong>
                        <?= htmlspecialchars($user["username"]) ?>
                    </strong>
                </div>

                <div class="info-box">
                    <label>Gmail</label>
                    <strong>
                        <?= !empty($user["email"])
                            ? htmlspecialchars($user["email"])
                            : "-" ?>
                    </strong>
                </div>

                <div class="info-box">
                    <label>NISN</label>
                    <strong>
                        <?= htmlspecialchars($user["nisn"]) ?>
                    </strong>
                </div>

                <div class="info-box">
                    <label>Nama Lengkap</label>
                    <strong>
                        <?= htmlspecialchars($user["nama"]) ?>
                    </strong>
                </div>

                <div class="info-box">
                    <label>Kelas</label>
                    <strong>
                        <?= htmlspecialchars($user["kelas"]) ?>
                    </strong>
                </div>

                <div class="info-box">
                    <label>No. Telepon</label>
                    <strong>
                        <?= htmlspecialchars($user["no_telepon"]) ?>
                    </strong>
                    </div>

            </div>
            <div class="profile-actions">

                <a href="update_profil.php" class="btn-edit">
                    Edit Profil
                </a>

                <a href="index.php" class="btn-kembali">
                    Kembali
                </a>

                <a
                    href="delete_user.php"
                    class="btn-hapus"
                    onclick="return confirm('Yakin ingin menghapus akun?')"
                >
                Hapus Akun
                </a>

                <a href="logout.php" class="btn-logout">
                    Logout
                </a>

            </div>

        </div>

    </div>

</main>

</body>
</html>