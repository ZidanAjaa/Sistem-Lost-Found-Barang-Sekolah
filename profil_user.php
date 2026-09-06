
<?php
session_start();
require_once "koneksi.php";

// Cek apakah user sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

// Ambil data user dari tabel users dan login
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Profil User - LostFound.sch</title>

    <link rel="stylesheet"
          href="css/style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">

        <div class="navbar-container">

            <a href="index.php" class="logo">
                LostFound<span>.sch</span>
            </a>

            <div class="nav-menu">

                <a href="index.php">
                    Beranda
                </a>

                <a href="index.php#barang">
                    Daftar Barang
                </a>

                <a href="index.php#cara-kerja">
                    Cara Kerja
                </a>

                <a href="index.php#kontak">
                    Kontak
                </a>

                <a href="profil_user.php"
                   class="btn btn-outline">
                    Profil
                </a>

                <a href="logout.php"
                   class="btn btn-orange">
                    Logout
                </a>

            </div>

        </div>

    </nav>


    <!-- PROFILE -->
    <main class="profile-page">

        <div class="profile-container">

            <!-- HEADER -->
            <div class="profile-header">

                <div class="profile-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>
                    <h1>Profil Saya</h1>

                    <p>
                        Informasi akun LostFound.sch kamu
                    </p>
                </div>

            </div>


            <!-- PROFILE CARD -->
            <div class="profile-card">

                <div class="profile-card-header">

                    <div>
                        <h2>
                            Informasi Pengguna
                        </h2>

                        <p>
                            Data akun yang terdaftar pada sistem.
                        </p>
                    </div>

                    <span class="profile-status">
                        <i class="fa-solid fa-circle"></i>

                        <?= htmlspecialchars($user["status"]); ?>
                    </span>

                </div>


                <!-- USER INFORMATION -->
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


                    <!-- Last Login -->
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


                <!-- ACTION BUTTON -->
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

                </div>

            </div>

        </div>

    </main>


    <!-- FOOTER -->
    <footer>

        <div class="footer-container">

            <div class="footer-logo">
                LostFound<span>.sch</span>
            </div>

            <p>
                Sistem Lost & Found Barang Sekolah
            </p>

            <p class="footer-copyright">
                &copy; <?= date("Y"); ?> LostFound.sch
            </p>

        </div>

    </footer>

</body>

</html>

