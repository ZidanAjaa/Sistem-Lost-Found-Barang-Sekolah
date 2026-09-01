<?php

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

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| Ambil data saat pertama kali dibuka
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        users.id,
        users.nisn,
        users.nama,
        users.kelas,
        users.no_telepon,
        login.username
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

    header("Location: logout.php");
    exit;

}

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| Proses Update
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $nisn = trim($_POST["nisn"] ?? "");
    $nama = trim($_POST["nama"] ?? "");
    $kelas = trim($_POST["kelas"] ?? "");
    $no_telepon = trim($_POST["no_telepon"] ?? "");
    $password = $_POST["password"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validasi
    |--------------------------------------------------------------------------
    */

    if (
        $username === "" ||
        $nisn === "" ||
        $nama === "" ||
        $kelas === ""
    ) {

        $error = "Username, NISN, nama, dan kelas wajib diisi.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Cek username apakah sudah digunakan user lain
        |--------------------------------------------------------------------------
        */

        $checkUsername = "
            SELECT id
            FROM login
            WHERE username = ?
            AND user_id != ?
            LIMIT 1
        ";

        $stmtCheck = mysqli_prepare(
            $koneksi,
            $checkUsername
        );

        mysqli_stmt_bind_param(
            $stmtCheck,
            "si",
            $username,
            $user_id
        );

        mysqli_stmt_execute($stmtCheck);

        $resultCheck = mysqli_stmt_get_result(
            $stmtCheck
        );

        if (mysqli_num_rows($resultCheck) > 0) {

            $error = "Username tersebut sudah digunakan.";

        }

        mysqli_stmt_close($stmtCheck);


        /*
        |--------------------------------------------------------------------------
        | Jika tidak ada error
        |--------------------------------------------------------------------------
        */

        if ($error === "") {

            mysqli_begin_transaction($koneksi);

            try {

                /*
                |--------------------------------------------------------------------------
                | Update users
                |--------------------------------------------------------------------------
                */

                $updateUser = "
                    UPDATE users
                    SET
                        nisn = ?,
                        nama = ?,
                        kelas = ?,
                        no_telepon = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ";

                $stmtUser = mysqli_prepare(
                    $koneksi,
                    $updateUser
                );

                mysqli_stmt_bind_param(
                    $stmtUser,
                    "ssssi",
                    $nisn,
                    $nama,
                    $kelas,
                    $no_telepon,
                    $user_id
                );

                if (!mysqli_stmt_execute($stmtUser)) {

                    throw new Exception(
                        "Gagal memperbarui data user."
                    );

                }

                mysqli_stmt_close($stmtUser);


                /*
                |--------------------------------------------------------------------------
                | Update username
                |--------------------------------------------------------------------------
                */

                $updateLogin = "
                    UPDATE login
                    SET username = ?
                    WHERE user_id = ?
                ";

                $stmtLogin = mysqli_prepare(
                    $koneksi,
                    $updateLogin
                );

                mysqli_stmt_bind_param(
                    $stmtLogin,
                    "si",
                    $username,
                    $user_id
                );

                if (!mysqli_stmt_execute($stmtLogin)) {

                    throw new Exception(
                        "Gagal memperbarui username."
                    );

                }

                mysqli_stmt_close($stmtLogin);


                /*
                |--------------------------------------------------------------------------
                | Jika password diisi
                |--------------------------------------------------------------------------
                */

                if ($password !== "") {

                    $passwordHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $updatePassword = "
                        UPDATE login
                        SET password = ?
                        WHERE user_id = ?
                    ";

                    $stmtPassword = mysqli_prepare(
                        $koneksi,
                        $updatePassword
                    );

                    mysqli_stmt_bind_param(
                        $stmtPassword,
                        "si",
                        $passwordHash,
                        $user_id
                    );

                    if (!mysqli_stmt_execute($stmtPassword)) {

                        throw new Exception(
                            "Gagal memperbarui password."
                        );

                    }

                    mysqli_stmt_close($stmtPassword);

                }


                /*
                |--------------------------------------------------------------------------
                | Commit
                |--------------------------------------------------------------------------
                */

                mysqli_commit($koneksi);

                /*
                |--------------------------------------------------------------------------
                | Update Session
                |--------------------------------------------------------------------------
                */

                $_SESSION["username"] = $username;
                $_SESSION["nama"] = $nama;

                $success = "Profil berhasil diperbarui.";

                /*
                |--------------------------------------------------------------------------
                | Ambil data terbaru
                |--------------------------------------------------------------------------
                */

                $user["username"] = $username;
                $user["nisn"] = $nisn;
                $user["nama"] = $nama;
                $user["kelas"] = $kelas;
                $user["no_telepon"] = $no_telepon;


            } catch (Exception $e) {

                mysqli_rollback($koneksi);

                $error = $e->getMessage();

            }

        }

    }

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

    <title>Edit Profil - LostFound.sch</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body class="profile-page">


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


<main class="profile-container">

    <div class="profile-card">

        <div class="profile-header">

            <div class="profile-icon">
                <i class="fa-solid fa-user-pen"></i>
            </div>

            <div>

                <h1>
                    Edit Profil
                </h1>

                <p>
                    Perbarui informasi akun kamu.
                </p>

            </div>

        </div>


        <?php if ($error !== ""): ?>

            <div class="form-error">
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <?php if ($success !== ""): ?>

            <div class="form-success">
                <?= htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
            class="profile-form"
        >


            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($user["username"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="nisn">
                    NISN
                </label>

                <input
                    type="text"
                    id="nisn"
                    name="nisn"
                    value="<?= htmlspecialchars($user["nisn"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="nama">
                    Nama Lengkap
                </label>

                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="<?= htmlspecialchars($user["nama"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="kelas">
                    Kelas
                </label>

                <input
                    type="text"
                    id="kelas"
                    name="kelas"
                    value="<?= htmlspecialchars($user["kelas"]); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="no_telepon">
                    Nomor Telepon
                </label>

                <input
                    type="text"
                    id="no_telepon"
                    name="no_telepon"
                    value="<?= htmlspecialchars($user["no_telepon"] ?? ""); ?>"
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password Baru
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Kosongkan jika tidak ingin mengubah password"
                >

                <small>
                    Kosongkan bagian ini jika password tidak ingin diubah.
                </small>

            </div>


            <div class="profile-actions">

                <button
                    type="submit"
                    class="profile-edit-button"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="profil.php"
                    class="profile-logout-button"
                >
                    Batal
                </a>

            </div>


        </form>

    </div>

</main>


</body>

</html>