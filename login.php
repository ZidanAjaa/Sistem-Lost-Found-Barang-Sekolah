<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_SESSION['login_error'] ?? "";
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login - LostFound.sch</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <style>

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f7f7ff;
        }

        .login-page {
            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 430px;

            background: white;

            padding: 35px;

            border-radius: 18px;

            box-shadow:
                0 10px 30px rgba(35, 23, 95, 0.15);
        }

        .login-logo {
            text-align: center;

            font-size: 28px;

            font-weight: 900;

            color: #21156e;

            margin-bottom: 10px;
        }

        .login-logo span {
            color: #ff6b1a;
        }

        .login-title {
            text-align: center;

            color: #251273;

            margin-bottom: 8px;
        }

        .login-description {
            text-align: center;

            color: #777;

            font-size: 14px;

            margin-bottom: 25px;
        }

        .login-error {
            background: #ffe5e5;

            color: #d62828;

            padding: 12px;

            border-radius: 8px;

            margin-bottom: 18px;

            font-size: 14px;
        }

        .login-group {
            margin-bottom: 18px;
        }

        .login-group label {
            display: block;

            margin-bottom: 7px;

            color: #2d1976;

            font-weight: 700;
        }

        .login-group input {
            width: 100%;

            padding: 13px;

            border: 1px solid #d8d5e7;

            border-radius: 10px;

            outline: none;

            box-sizing: border-box;

            font-size: 15px;
        }

        .login-group input:focus {
            border-color: #3a2bd4;
        }

        .login-button {
            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 10px;

            background: #3827d7;

            color: white;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;
        }

        .login-button:hover {
            background: #2d20b5;
        }

        .register-link {
            text-align: center;

            margin-top: 20px;

            color: #666;

            font-size: 14px;
        }

        .register-link a {
            color: #3827d7;

            font-weight: 700;

            text-decoration: none;
        }

    </style>

</head>

<body>

<div class="login-page">

    <div class="login-card">

        <div class="login-logo">
            LostFound<span>.sch</span>
        </div>

        <h2 class="login-title">
            Login
        </h2>

        <p class="login-description">
            Masuk ke akun LostFound.sch kamu
        </p>


        <?php if (!empty($error)) : ?>

            <div class="login-error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form
            action="proses_login.php"
            method="POST"
        >

            <div class="login-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Masukkan email"
                    required
                >

            </div>


            <div class="login-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >
                Login
            </button>

        </form>


        <div class="register-link">

            Belum punya akun?

            <a href="register.php">
                Daftar sekarang
            </a>

        </div>

    </div>

</div>

</body>

</html>