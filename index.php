<?php
// login.php (pastikan file ini bernama .php dan diakses lewat http://localhost/...)
session_start();
require 'db.php'; // jika tidak ada DB di sini, hapus baris ini atau sesuaikan
$err = '';

// proses login kalau form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // contoh: ambil user dari DB (pastikan $mysqli tersedia di db.php)
    if (isset($mysqli)) {
        $stmt = $mysqli->prepare("SELECT id, username, name, password, role FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role']     = $user['role'];
                header('Location: dashboard.php');
                exit;
            } else {
                $err = "Username atau password salah.";
            }
        } else {
            $err = "Username atau password salah.";
        }
    } else {
        // fallback kalau tidak memakai DB
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Admin';
            header('Location: dashboard.php');
            exit;
        } else {
            $err = "Username atau password salah (no DB).";
        }
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login - Sistem Penjualan Es Teh</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="login-page">

    <div class="login-wrapper">
        <div class="login-card">

            <img src="img/logo.png" class="login-logo" alt="Logo">

            <h2 class="login-title">Sistem Penjualan<br>Teh Solo</h2>

            <!-- pakai isset/!empty supaya aman jika $err tidak didefinisikan -->
            <?php if (!empty($err)): ?>
                <div class="alert"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="text" name="username" placeholder="Username" class="input-login" required>
                <input type="password" name="password" placeholder="Password" class="input-login" required>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <p class="default-info">

            </p>

        </div>
    </div>

</body>

</html>