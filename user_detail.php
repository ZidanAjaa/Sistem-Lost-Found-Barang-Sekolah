<?php
session_start();
require_once __DIR__ . "/koneksi.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$user_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($user_id <= 0) {
    header("Location: dashboard.php");
    exit;
}

$sql = "
    SELECT u.id, u.nisn, u.nama, u.kelas, u.no_telepon, u.role,
           l.username, l.status, l.last_login
    FROM users u
    LEFT JOIN login l ON l.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - LostFound.sch</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #1f2430;
        }
        .wrap {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px 60px;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
        }
        .btn-primary {
            background: #3f2de0;
            color: #fff;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-top: 24px;
        }
        .info-item {
            background: #f8faff;
            border-radius: 14px;
            padding: 16px;
        }
        .label {
            display: block;
            font-size: 12px;
            color: #667085;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .value {
            font-size: 1rem;
            font-weight: 700;
        }
        .pill {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .pill-admin { background: #ebf9f1; color: #1e7d4f; }
        .pill-user { background: #fff0df; color: #b76d00; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="topbar">
            <h1 style="margin:0;">Detail Informasi User</h1>
            <a href="dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
        </div>

        <div class="card">
            <h2 style="margin-top:0;">Profil <?= htmlspecialchars($user['nama']) ?></h2>
            <div style="margin-bottom:18px;">
                <span class="pill <?= strtolower($user['role'] ?? 'user') === 'admin' ? 'pill-admin' : 'pill-user' ?>">
                    <?= htmlspecialchars(strtoupper($user['role'] ?? 'user')) ?>
                </span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Nama Lengkap</span>
                    <div class="value"><?= htmlspecialchars($user['nama']) ?></div>
                </div>
                <div class="info-item">
                    <span class="label">Username</span>
                    <div class="value"><?= htmlspecialchars($user['username'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <span class="label">NISN</span>
                    <div class="value"><?= htmlspecialchars($user['nisn'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <span class="label">Kelas</span>
                    <div class="value"><?= htmlspecialchars($user['kelas'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <span class="label">No. Telepon</span>
                    <div class="value"><?= htmlspecialchars($user['no_telepon'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <span class="label">Status</span>
                    <div class="value"><?= htmlspecialchars($user['status'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <span class="label">Login Terakhir</span>
                    <div class="value"><?= !empty($user['last_login']) ? htmlspecialchars(date('d-m-Y H:i:s', strtotime($user['last_login']))) : '-' ?></div>
                </div>
                <div class="info-item">
                    <span class="label">Role</span>
                    <div class="value"><?= htmlspecialchars(ucfirst($user['role'] ?? 'user')) ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
