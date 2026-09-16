<?php
session_start();
require_once "koneksi.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION["user_id"];

$sql = "SELECT users.*, login.username
        FROM users
        JOIN login ON users.id = login.user_id
        WHERE users.id = ?";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$foto = !empty($user["foto_profil"])
    ? "img/profil/" . $user["foto_profil"]
    : "img/profil/default.jpg";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Profil - LostFound.sch</title>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profil.css">
</head>

<body>

<main class="profile-page">

    <div class="profile-container">

        <div class="profile-card">

            <div class="profile-header">

                <div class="profile-photo">
                    <img id="preview" src="<?= htmlspecialchars($foto) ?>">
                </div>

                <div>
                    <h1>Edit Profil</h1>
                    <p>Perbarui informasi akun kamu.</p>
                </div>

            </div>


            <form
                method="POST"
                action="proses_update_profil.php"
                enctype="multipart/form-data"
                class="profile-form"
            >

                <div class="form-group">

                    <label>Foto Profil</label>

                    <input
                        type="file"
                        name="foto_profil"
                        id="foto"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small>
                        maksimal 2 MB.
                    </small>

                </div>


                <div class="form-group">

                    <label>Username</label>

                    <input
                        type="text"
                        name="username"
                        value="<?= htmlspecialchars($user["username"]) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Gmail</label>

                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars($user["email"] ?? "") ?>"
                        placeholder="contoh : nama@gmail.com"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>NISN</label>

                    <input
                        type="text"
                        name="nisn"
                        value="<?= htmlspecialchars($user["nisn"]) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Nama Lengkap</label>

                    <input
                        type="text"
                        name="nama"
                        value="<?= htmlspecialchars($user["nama"]) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>Kelas</label>

                    <input
                        type="text"
                        name="kelas"
                        value="<?= htmlspecialchars($user["kelas"]) ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>No. Telepon</label>

                    <input
                        type="text"
                        name="no_telepon"
                        value="<?= htmlspecialchars($user["no_telepon"]) ?>"
                        required
                    >

                </div>


                <div class="profile-actions">

                    <a
                        href="profil_user.php"
                        class="profile-back-button"
                    >
                        Kembali
                    </a>

                    <button
                        type="submit"
                        class="profile-edit-button"
                    >
                        Simpan Perubahan
                    </button>

                </div>

            </form>

        </div>

    </div>

</main>


<script>
document.getElementById("foto").onchange = function () {

    if (this.files[0]) {

        if (this.files[0].size > 2 * 1024 * 1024) {
            alert("Ukuran foto maksimal 2 MB.");
            this.value = "";
            return;
        }

        document.getElementById("preview").src =
            URL.createObjectURL(this.files[0]);
    }

};
</script>

</body>
</html>