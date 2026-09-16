<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION["user_id"];

$username = trim($_POST["username"]);
$email = trim($_POST["email"]);
$nisn = trim($_POST["nisn"]);
$nama = trim($_POST["nama"]);
$kelas = trim($_POST["kelas"]);
$no_telepon = trim($_POST["no_telepon"]);


$foto = null;

if (!empty($_FILES["foto_profil"]["name"])) {

    $ext = strtolower(
        pathinfo($_FILES["foto_profil"]["name"], PATHINFO_EXTENSION)
    );

    if (!in_array($ext, ["jpg", "jpeg", "png", "webp"])) {
        die("Format foto tidak valid.");
    }

    if ($_FILES["foto_profil"]["size"] > 2 * 1024 * 1024) {
        die("Ukuran foto maksimal 2 MB.");
    }

    $foto = "user_" . $id . "." . $ext;

    move_uploaded_file(
        $_FILES["foto_profil"]["tmp_name"],
        "img/profil/" . $foto
    );
}

if ($foto) {

    $sql = "UPDATE users SET
            nisn = ?,
            nama = ?,
            kelas = ?,
            no_telepon = ?,
            email = ?,
            foto_profil = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($koneksi, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssi",
        $nisn,
        $nama,
        $kelas,
        $no_telepon,
        $email,
        $foto,
        $id
    );

} else {

    $sql = "UPDATE users SET
            nisn = ?,
            nama = ?,
            kelas = ?,
            no_telepon = ?,
            email = ?
            WHERE id = ?";

    $stmt = mysqli_prepare($koneksi, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssssi",
        $nisn,
        $nama,
        $kelas,
        $no_telepon,
        $email,
        $id
    );
}

mysqli_stmt_execute($stmt);

$sql = "UPDATE login
        SET username = ?
        WHERE user_id = ?";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "si", $username, $id);
mysqli_stmt_execute($stmt);


header("Location: profil_user.php");
exit;
?>