<?php
session_start();
require_once "koneksi.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {

        $error = "Username dan password wajib diisi.";

    } else {

        $sql = "SELECT 
                    login.id,
                    login.user_id,
                    login.username,
                    login.password,
                    login.status,
                    users.nama,
                    users.nisn,
                    users.kelas,
                    users.no_telepon,
                    users.role
                FROM login
                INNER JOIN users ON login.user_id = users.id
                WHERE login.username = ?
                LIMIT 1";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {

            $data = mysqli_fetch_assoc($result);

            if ($data["status"] != "aktif") {

                $error = "Akun kamu sedang nonaktif.";

            } elseif (password_verify($password, $data["password"])) {

                $_SESSION["login"] = true;
                $_SESSION["login_id"] = $data["id"];
                $_SESSION["user_id"] = $data["user_id"];
                $_SESSION["username"] = $data["username"];
                $_SESSION["nama"] = $data["nama"];
                $_SESSION["nisn"] = $data["nisn"];
                $_SESSION["kelas"] = $data["kelas"];
                $_SESSION["no_telepon"] = $data["no_telepon"];
                $_SESSION["role"] = $data["role"];

                // Update waktu login terakhir
                $update = "UPDATE login 
                           SET last_login = NOW()
                           WHERE id = ?";

                $stmtUpdate = mysqli_prepare($conn, $update);
                mysqli_stmt_bind_param(
                    $stmtUpdate,
                    "i",
                    $data["id"]
                );
                mysqli_stmt_execute($stmtUpdate);

                header("Location: profil.php");
                exit;

            } else {

                $error = "Username atau password salah.";

            }

        } else {

            $error = "Username atau password salah.";

        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - LostFound.sch</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body class="login-page">

<div class="login-container">

    <div class="login-card">

        <a href="index.php"
           class="login-logo">



        </a>

        <h1>Login</h1>

        <p class="login-subtitle">
            Masuk ke akun LostFound.sch
        </p>


        <?php if (!empty($error)) : ?>

            <div class="login-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form method="POST"
              action="login.php">


            <div class="login-form-group">

                <label for="username">
                    Username
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-user"></i>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        required
                    >

                </div>

            </div>


            <div class="login-form-group">

                <label for="password">
                    Password
                </label>

                <div class="input-wrapper">

                    <i class="fa-solid fa-lock"></i>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >

                </div>

            </div>


            <button type="submit"
                    class="login-button">

                <i class="fa-solid fa-right-to-bracket"></i>

                Login

            </button>

        </form>


        <a href="index.php"
           class="back-home">

            <i class="fa-solid fa-arrow-left"></i>

            Kembali ke halaman utama

        </a>

    </div>

</div>

</body>

</html>