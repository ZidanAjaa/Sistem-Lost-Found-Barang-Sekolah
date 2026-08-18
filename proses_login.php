<?php

session_start();

include "koneksi.php";


/*
|--------------------------------------------------------------------------
| CEK METHOD
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| AMBIL DATA
|--------------------------------------------------------------------------
*/

$email = trim($_POST['email'] ?? '');

$password = $_POST['password'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDASI
|--------------------------------------------------------------------------
*/

if ($email === '' || $password === '') {

    $_SESSION['login_error'] =
        "Email dan password wajib diisi.";

    header("Location: login.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| CARI USER
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, nisn, nama, kelas, no_telepon, email, password, role
     FROM `user`
     WHERE email = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);


/*
|--------------------------------------------------------------------------
| CEK USER DAN PASSWORD
|--------------------------------------------------------------------------
*/

if ($user && password_verify($password, $user['password'])) {

    /*
    |--------------------------------------------------------------------------
    | BUAT SESSION
    |--------------------------------------------------------------------------
    */

    $_SESSION['user_id'] = $user['id'];

    $_SESSION['user_nama'] = $user['nama'];

    $_SESSION['user_email'] = $user['email'];

    $_SESSION['user_role'] = $user['role'];


    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header("Location: dashboard.php");

    exit;

}


/*
|--------------------------------------------------------------------------
| LOGIN GAGAL
|--------------------------------------------------------------------------
*/

$_SESSION['login_error'] =
    "Email atau password salah.";

header("Location: login.php");

exit;

?>