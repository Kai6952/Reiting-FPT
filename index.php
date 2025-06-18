<?php
session_start();
require_once 'db.php';

// 🔐 Редирект по ролям
if (!empty($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: app.php');
        exit;
    } elseif ($_SESSION['role'] === 'teacher' || $_SESSION['role'] === 'user') {
        header('Location: main.html');
        exit;
    }
    // guest → остаётся на форме
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guest'])) {
        $_SESSION['role'] = 'guest';
        $_SESSION['user_id'] = null;
        header('Location: main.html');
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: app.php');
            } else {
                header('Location: main.html');
            }
            exit;
        }
    }

    $error = 'Неверный email или пароль';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Вход</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <div class="login-box">
    <div class="logo">
      <img src="images/logo.svg" alt="Логотип">
    </div>

    <?php if (!empty($error)): ?>
      <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Пароль" required>
      <button type="submit">Войти</button>
      <button type="submit" name="guest" formnovalidate>Войти как гость</button>
    </form>

    <div class="note">Авторизуйтесь, чтобы получить доступ к оценкам и управлению.</div>
  </div>
</body>
</html>
