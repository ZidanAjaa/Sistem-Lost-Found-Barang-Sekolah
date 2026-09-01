<?php

session_start();

include "koneksi.php";


/* CEK LOGIN */

if (!isset($_SESSION['user_id'])) {

    header("Location: login.php");

    exit;
}


$user_id = $_SESSION['user_id'];


/* AMBIL DATA USER */


session_start();

require_once "koneksi.php";

/*
|--------------------------------------------------------------------------
| Cek Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}

$user_id = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| Ambil data user
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        users.id,
        users.nisn,
        users.nama,
        users.kelas,
        users.no_telepon,
        users.role,
        users.created_at,
        users.updated_at,
        login.username,
        login.last_login,
        login.status
    FROM users

    INNER JOIN login
        ON users.id = login.user_id

    WHERE users.id = ?

    LIMIT 1
";

$stmt = mysqli_prepare(
    $koneksi,
    $query
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {

    session_destroy();

    header("Location: login.php");
    exit;

}

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Profil User - LostFound.sch</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body class="profile-page">


<!-- =====================================================
     NAVBAR
===================================================== -->

<header class="navbar">

    <div class="container navbar-inner">

        <a
            href="index.php"
            class="logo"
        >
            LostFound<span>.sch</span>
        </a>


        <nav class="nav-menu">

            <a href="index.php#daftar-barang">
                Daftar Barang
            </a>

            <a href="index.php#cara-kerja">
                Cara Kerja
            </a>

            <a href="index.php#kontak">
                Kontak
            </a>

        </nav>


        <div class="nav-buttons">

            <a
                href="profil.php"
                class="btn btn-outline"
            >
                Profil
            </a>

            <a
                href="logout.php"
                class="btn btn-orange"
            >
                Logout
            </a>

        </div>

    </div>

</header>


<!-- =====================================================
     PROFIL
===================================================== -->

<main class="profile-container">

    <div class="profile-card">

        <div class="profile-header">

            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>

            <div>

                <h1>
                    Profil User
                </h1>

                <p>
                    Informasi akun pengguna LostFound.sch
                </p>

            </div>

        </div>


        <!-- DATA USER -->

        <div class="profile-data">


            <div class="profile-item">

                <span class="profile-label">
                    Username
                </span>

                <span class="profile-value">
                    <?= htmlspecialchars($user["username"]); ?>
                </span>

            </div>


            <div class="profile-item">

                <span class="profile-label">
                    NISN
                </span>

                <span class="profile-value">
                    <?= htmlspecialchars($user["nisn"]); ?>
                </span>

            </div>


            <div class="profile-item">

                <span class="profile-label">
                    Nama
                </span>

                <span class="profile-value">
                    <?= htmlspecialchars($user["nama"]); ?>
                </span>

            </div>


            <div class="profile-item">

                <span class="profile-label">
                    Kelas
                </span>

                <span class="profile-value">
                    <?= htmlspecialchars($user["kelas"]); ?>
                </span>

            </div>


            <div class="profile-item">

                <span class="profile-label">
                    Nomor Telepon
                </span>

                <span class="profile-value">

                    <?php

                    if (!empty($user["no_telepon"])) {

                        echo htmlspecialchars(
                            $user["no_telepon"]
                        );

                    } else {

                        echo "-";

                    }

                    ?>

                </span>

            </div>


            <div class="profile-item">

                <span class="profile-label">
                    Role
                </span>

                <span class="profile-value">
                    <?= htmlspecialchars($user["role"]); ?>
                </span>

            </div>


            <div class="profile-item">

                <span class="profile-label">
                    Status Akun
                </span>

                <span class="profile-value">

                    <?php if ($user["status"] === "aktif"): ?>

                        <span class="status-active">
                            Aktif
                        </span>

                    <?php else: ?>

                        <span class="status-inactive">
                            Nonaktif
                        </span>

                    <?php endif; ?>

                </span>

            </div>


        </div>


        <!-- BUTTON -->

        <div class="profile-actions">

            <a
                href="update_profil.php"
                class="profile-edit-button"
            >
                Edit Profil
            </a>

            <a
                href="logout.php"
                class="profile-logout-button"
            >
                Logout
            </a>

        </div>

    </div>

</main>


</body>

</html>

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nisn, nama, kelas, no_telepon, email, role
     FROM `user`
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);


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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Profil User - LostFound.sch</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

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

            box-shadow:
                0 10px 30px rgba(35, 23, 95, 0.12);
        }

        .profile-title {
            text-align: center;

            margin-bottom: 8px;

            color: #251273;
        }

        .profile-description {
            text-align: center;

            color: #777;

            margin-bottom: 30px;
        }

        .profile-role {
            display: block;

            width: fit-content;

            margin: 0 auto 25px;

            padding: 7px 15px;

            background: #fff1df;

            color: #ff6718;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 700;
        }

        .profile-group {
            margin-bottom: 20px;
        }

        .profile-group label {
            display: block;

            margin-bottom: 7px;

            color: #2d1976;

            font-weight: 700;
        }

        .profile-group input {
            width: 100%;

            padding: 13px 15px;

            box-sizing: border-box;

            border: 1px solid #d8d5e7;

            border-radius: 10px;

            outline: none;

            font-size: 15px;
        }

        .profile-group input:focus {
            border-color: #3827d7;
        }

        .profile-group input[readonly] {
            background: #f2f2f6;

            color: #777;
        }

        .profile-actions {
            display: flex;

            justify-content: space-between;

            gap: 15px;

            margin-top: 25px;
        }

        .profile-button {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 12px 20px;

            border-radius: 10px;

            text-decoration: none;

            font-weight: 700;

            border: none;

            cursor: pointer;
        }

        .profile-back {
            background: #eeeeF5;

            color: #3827d7;
        }

        .profile-save {
            background: #3827d7;

            color: white;
        }

        .profile-save:hover {
            background: #2d20b5;
        }


        @media (max-width: 600px) {

            .profile-card {
                padding: 25px 20px;
            }

            .profile-actions {
                flex-direction: column;
            }

            .profile-button {
                width: 100%;
            }

        }

    </style>

</head>

<body>


<section class="profile-page">

    <div class="profile-container">

        <div class="profile-card">

            <h1 class="profile-title">
                Profil Saya
            </h1>

            <p class="profile-description">
                Kelola informasi akun LostFound.sch kamu.
            </p>


            <span class="profile-role">

                <?php
                echo htmlspecialchars($user['role']);
                ?>

            </span>


            <form
                action="proses-profil.php"
                method="POST"
            >


                <!-- NISN -->

                <div class="profile-group">

                    <label for="nisn">
                        NISN
                    </label>

                    <input
                        type="text"
                        id="nisn"
                        value="<?php
                            echo htmlspecialchars($user['nisn']);
                        ?>"
                        readonly
                    >

                </div>


                <!-- NAMA -->

                <div class="profile-group">

                    <label for="nama">
                        Nama Lengkap
                    </label>

                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        value="<?php
                            echo htmlspecialchars($user['nama']);
                        ?>"
                        required
                    >

                </div>


                <!-- KELAS -->

                <div class="profile-group">

                    <label for="kelas">
                        Kelas
                    </label>

                    <input
                        type="text"
                        id="kelas"
                        name="kelas"
                        value="<?php
                            echo htmlspecialchars($user['kelas']);
                        ?>"
                        required
                    >

                </div>


                <!-- NO TELEPON -->

                <div class="profile-group">

                    <label for="no_telepon">
                        Nomor Telepon
                    </label>

                    <input
                        type="text"
                        id="no_telepon"
                        name="no_telepon"
                        value="<?php
                            echo htmlspecialchars($user['no_telepon']);
                        ?>"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="profile-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        value="<?php
                            echo htmlspecialchars($user['email']);
                        ?>"
                        readonly
                    >

                </div>


                <!-- BUTTON -->

                <div class="profile-actions">

                    <a
                        href="dashboard.php"
                        class="profile-button profile-back"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        &nbsp;
                        Kembali
                    </a>


                    <button
                        type="submit"
                        class="profile-button profile-save"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        &nbsp;
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>

</section>


</body>

</html>