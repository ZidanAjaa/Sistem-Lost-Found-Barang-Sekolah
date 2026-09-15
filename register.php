<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Lost & Found</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/register.css">
</head>

<body>

    <div class="register-page">
        <div class="register-container">
            <div class="register-card">

                <h1>Daftar Akun Baru</h1>

                <p class="register-description">
                    Buat akun LostFound.sch
                </p>

                <?php if (isset($_SESSION['error'])): ?>
                    <p style="color:red;">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </p>
                <?php endif; ?>

                <form action="proses-register.php" method="POST" class="register-form">

                    <div class="register-group">
                        <label for="nisn">NISN</label>
                        <input type="text" id="nisn" name="nisn" required>
                    </div>

                    <div class="register-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" name="nama" required>
                    </div>

                    <div class="register-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" id="kelas" name="kelas" required>
                    </div>

                    <div class="register-group">
                        <label for="no_telepon">No. Telepon</label>
                        <input type="text" id="no_telepon" name="no_telepon" required>
                    </div>

                    <div class="register-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="register-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="register-group">
                        <label for="konfirmasi_password">Konfirmasi Password</label>
                        <input type="password" id="konfirmasi_password" name="konfirmasi_password" required>
                    </div>

                    <button type="submit" class="register-button">
                        Daftar
                    </button>

                </form>

                <div class="register-login">
                    <span>Sudah punya akun?</span>
                    <a href="login.php">Login di sini</a>
                </div>

            </div>
        </div>
    </div>

</body>
</html>