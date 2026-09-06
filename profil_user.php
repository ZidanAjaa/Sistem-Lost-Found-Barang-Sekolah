<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$sql = "SELECT users.id, users.nisn, users.nama, users.kelas,
        users.no_telepon, users.role, login.username, login.last_login,
        login.status
        FROM users
        INNER JOIN login ON users.id = login.user_id
        WHERE users.id = ?
        LIMIT 1";

$stmt = mysqli_prepare($db, $sql);

if (!$stmt) {
    die("Query gagal: " . mysqli_error($db));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

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

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .profile-page {
            min-height: 100vh;
            background: #f7f7ff;
            padding: 60px 20px;
        }

        .profile-container {
            width: 100%;
            max-width: 800px;
            margin: auto;
        }

        .profile-card {
            background: white;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(35,23,95,.12);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .profile-icon {
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #3827d7;
            color: white;
            font-size: 22px;
        }

        .profile-header h1 {
            margin: 0;
            color: #251273;
        }

        .profile-header p {
            margin: 5px 0 0;
            color: #777;
        }

        .profile-data {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .profile-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            gap: 20px;
        }

        .profile-label {
            color: #555;
            font-weight: 600;
        }

        .profile-value {
            color: #251273;
            font-weight: 600;
            text-align: right;
        }

        .status-active {
            color: #16853b;
        }

        .status-inactive {
            color: #d33;
        }

        .profile-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .profile-edit-button,
        .profile-logout-button {
            flex: 1;
            padding: 13px 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
        }

        .profile-edit-button {
            background: #3827d7;
            color: white;
        }

        .profile-logout-button {
            background: #fff;
            color: #3827d7;
            border: 1px solid #3827d7;
        }

        .profile-edit-button:hover {
            background: #2d20b5;
        }

        .profile-logout-button:hover {
            background: #f1efff;
        }

        @media (max-width: 600px) {
            .profile-card {
                padding: 25px 20px;
            }

            .profile-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .profile-value {
                text-align: left;
            }

            .profile-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body class="profile-page">

<header class="navbar">
    <div class="container navbar-inner">

        <a href="index.php" class="logo">
            LostFound<span>.sch</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php#daftar-barang">Daftar Barang</a>
            <a href="index.php#cara-kerja">Cara Kerja</a>
            <a href="index.php#kontak">Kontak</a>
        </nav>

        <div class="nav-buttons">
            <a href="profil_user.php" class="btn btn-outline">Profil</a>
            <a href="logout.php" class="btn btn-orange">Logout</a>
        </div>

    </div>
</header>

<main class="profile-container">

    <div class="profile-card">

        <div class="profile-header">

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

            <div>
                <h1>Profil User</h1>
                <p>Informasi akun pengguna LostFound.sch</p>
            </div>

        </div>

        <div class="profile-data">

            <div class="profile-item">
                <span class="profile-label">Username</span>
                <span class="profile-value">
                    <?= htmlspecialchars($user["username"]); ?>
                </span>
            </div>

            <div class="profile-item">
                <span class="profile-label">NISN</span>
                <span class="profile-value">
                    <?= htmlspecialchars($user["nisn"]); ?>
                </span>
            </div>

            <div class="profile-item">
                <span class="profile-label">Nama</span>
                <span class="profile-value">
                    <?= htmlspecialchars($user["nama"]); ?>
                </span>
            </div>

            <div class="profile-item">
                <span class="profile-label">Kelas</span>
                <span class="profile-value">
                    <?= htmlspecialchars($user["kelas"]); ?>
                </span>
            </div>

            <div class="profile-item">
                <span class="profile-label">Nomor Telepon</span>
                <span class="profile-value">
                    <?= !empty($user["no_telepon"])
                        ? htmlspecialchars($user["no_telepon"])
                        : "-"; ?>
                </span>
            </div>

            <div class="profile-item">
                <span class="profile-label">Role</span>
                <span class="profile-value">
                    <?= htmlspecialchars($user["role"]); ?>
                </span>
            </div>

            <div class="profile-item">
                <span class="profile-label">Status Akun</span>
                <span class="profile-value">

                    <?php if ($user["status"] === "aktif"): ?>
                        <span class="status-active">Aktif</span>
                    <?php else: ?>
                        <span class="status-inactive">Nonaktif</span>
                    <?php endif; ?>

                </span>
            </div>

        </div>

        <div class="profile-actions">

            <a href="update_profil.php" class="profile-edit-button">
                <i class="fa-solid fa-pen"></i>
                Edit Profil
            </a>

            <a href="logout.php" class="profile-logout-button">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>

        </div>

    </div>

</main>

</body>
</html>