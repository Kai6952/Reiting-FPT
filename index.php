<?php
session_start();
require_once 'db.php';

// Если уже залогинен — сразу уходим
if (!empty($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: app.php');
    } else {
        header('Location: main.html');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Гостевой вход без валидации HTML
    if (isset($_POST['guest'])) {
        $_SESSION['role']    = 'guest';
        $_SESSION['user_id'] = null;
        header('Location: main.html');
        exit;
    }

    // Обычная авторизация
    $email = trim($_POST['email']   ?? '');
    $pass  =          $_POST['password'] ?? '';

    if ($email && $pass) {
        $stmt = $mysqli->prepare(
          "SELECT id, password, role FROM users WHERE email = ?"
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role']    = $user['role']; // admin или user

            // Редирект по роли
            if ($user['role'] === 'admin') {
                header('Location: app.php');
            } else {
                header('Location: main.html');
            }
            exit;
        }
    }

    $error = 'Неверные учетные данные';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Авторизация</title>
</head>
<body>
  <h1>Вход в систему</h1>
  <?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <form method="POST">
    <label>
      Email:<br>
      <input type="email" name="email" required>
    </label><br><br>
    <label>
      Пароль:<br>
      <input type="password" name="password" required>
    </label><br><br>

    <button type="submit">Войти</button>
    <button type="submit" name="guest" formnovalidate>
      Войти как гость
    </button>
  </form>
</body>
</html>
