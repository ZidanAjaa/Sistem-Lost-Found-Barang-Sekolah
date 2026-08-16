<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Lost & Found</title>
</head>
<body>
    <h2>Login</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <p style="color:red;"><?= $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <p style="color:green;"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <form action="proses_login.php" method="POST">
        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>

    <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</body>
</html>