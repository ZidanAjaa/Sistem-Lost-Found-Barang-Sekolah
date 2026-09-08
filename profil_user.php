<?php
session_start();
require_once "koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Ambil data user
$sql = "SELECT 
            users.id,
            users.nisn,
            users.nama,
            users.kelas,
            users.no_telepon,
            users.role,
            login.username,
            login.last_login,
            login.status
        FROM users
        INNER JOIN login ON users.id = login.user_id
        WHERE users.id = ?
        LIMIT 1";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Jika data user tidak ditemukan
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
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

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <!-- HALAMAN PROFIL -->
    <main class="profile-page">

        <div class="profile-container">

            <!-- HEADER PROFIL -->
            <div class="profile-header">

                <h1>Profil Saya</h1>

                <p>Informasi akun LostFound.sch kamu</p>

            </div>


            <!-- CARD PROFIL -->
            <div class="profile-card">

                <!-- HEADER CARD -->
                <div class="profile-card-header">

                    <div>
                        <h2>Informasi Pengguna</h2>

                        <p>
                            Data akun yang terdaftar pada sistem.
                        </p>
                    </div>

                    <span class="profile-status">
                        <i class="fa-solid fa-circle"></i>
                        <?= htmlspecialchars($user["status"]); ?>
                    </span>

                </div>


                <!-- INFORMASI USER -->
                <div class="profile-info">

                    <!-- Username -->
                    <div class="profile-info-item">

                        <div class="profile-info-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div>
                            <span class="profile-label">
                                Username
                            </span>

                            <strong>
                                <?= htmlspecialchars($user["username"]); ?>
                            </strong>
                        </div>

                    </div>


                    <!-- NISN -->
                    <div class="profile-info-item">

                        <div class="profile-info-icon">
                            <i class="fa-solid fa-id-card"></i>
                        </div>

                        <div>
                            <span class="profile-label">
                                NISN
                            </span>

                            <strong>
                                <?= htmlspecialchars($user["nisn"]); ?>
                            </strong>
                        </div>

                    </div>


                    <!-- Nama -->
                    <div class="profile-info-item">

                        <div class="profile-info-icon">
                            <i class="fa-solid fa-address-card"></i>
                        </div>

                        <div>
                            <span class="profile-label">
                                Nama Lengkap
                            </span>

                            <strong>
                                <?= htmlspecialchars($user["nama"]); ?>
                            </strong>
                        </div>

                    </div>


                    <!-- Kelas -->
                    <div class="profile-info-item">

                        <div class="profile-info-icon">
                            <i class="fa-solid fa-school"></i>
                        </div>

                        <div>
                            <span class="profile-label">
                                Kelas
                            </span>

                            <strong>
                                <?= htmlspecialchars($user["kelas"]); ?>
                            </strong>
                        </div>

                    </div>


                    <!-- No Telepon -->
                    <div class="profile-info-item">

                        <div class="profile-info-icon">
                            <i class="fa-solid fa-phone"></i>
                        </div>

                        <div>
                            <span class="profile-label">
                                No. Telepon
                            </span>

                            <strong>
                                <?= htmlspecialchars($user["no_telepon"]); ?>
                            </strong>
                        </div>

                    </div>


                    <!-- Role -->
                    <div class="profile-info-item">

                        <div class="profile-info-icon">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>

                        <div>
                            <span class="profile-label">
                                Role
                            </span>

                            <strong>
                                <?= htmlspecialchars($user["role"]); ?>
                            </strong>
                        </div>

                    </div>


                    <!-- Login Terakhir -->
                    <div class="profile-info-item">

                        <div class="profile-info-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div>
                            <span class="profile-label">
                                Login Terakhir
                            </span>

                            <strong>
                                <?php
                                if (!empty($user["last_login"])) {
                                    echo htmlspecialchars($user["last_login"]);
                                } else {
                                    echo "Belum tersedia";
                                }
                                ?>
                            </strong>
                        </div>

                    </div>

                </div>


                <!-- TOMBOL -->
                <div class="profile-actions">

                    <a href="update_profil.php"
                       class="profile-edit-button">

                        <i class="fa-solid fa-pen"></i>
                        Edit Profil

                    </a>

                    <a href="index.php"
                       class="profile-back-button">

                        <i class="fa-solid fa-house"></i>
                        Kembali ke Beranda

                    </a>

                    <a href="logout.php"
                       class="profile-logout-button">

                        <i class="fa-solid fa-right-from-bracket"></i>
                        Logout

                    </a>

                </div>

            </div>

        </div>

    </main>

        </div>

    </footer>

</body>

</html>