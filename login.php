<?php
require_once 'config.php';

if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        $_SESSION['login'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Product Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-card" style="max-width: 400px; margin: 80px auto;">
        <h2 style="text-align: center; margin-bottom: 20px;">Login System</h2>
        <?php if ($error): ?>
            <div class="alert danger"><?= e($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn primary" style="width: 100%;">Masuk</button>
        </form>
    </div>
</body>
</html>