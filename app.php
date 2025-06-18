<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: index.php');
  exit;
}

$categories = $conn->query("SELECT id, name FROM categories")->fetch_all(MYSQLI_ASSOC);

// Создание преподавателя
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name     = trim($_POST['name']);
  $email    = trim($_POST['email']);
  $password = $_POST['password'];
  $photo    = trim($_POST['photo']);
  $bio      = trim($_POST['bio']);

  $hash = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'teacher')");
  $stmt->bind_param("sss", $name, $email, $hash);
  $stmt->execute();
  $user_id = $stmt->insert_id;
  $stmt->close();

  $stmt = $conn->prepare("INSERT INTO teachers (user_id, name, photo, bio) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("isss", $user_id, $name, $photo, $bio);
  $stmt->execute();
  $teacher_id = $stmt->insert_id;
  $stmt->close();

  header("Location: app.php");
  exit;
}

$teachers = $conn->query("
  SELECT t.id, t.name, u.email, t.photo, t.bio
  FROM teachers t
  JOIN users u ON u.id = t.user_id
  WHERE u.role = 'teacher'
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Админ-панель</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-panel">
    <h1>Создание преподавателя</h1>
    <form method="POST" class="form-block">
      <input type="text" name="name" placeholder="ФИО" required>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Пароль" required>
      <input type="text" name="photo" placeholder="Путь к фото (например, images/avatar.jpg)">
      <textarea name="bio" placeholder="Биография (необязательно)"></textarea>
      <button type="submit">Создать</button>
    </form>

    <h2>Список преподавателей</h2>
    <div class="teacher-list">
      <?php foreach ($teachers as $t): ?>
        <div class="teacher-card">
          <img src="<?= htmlspecialchars($t['photo']) ?>" alt="Фото">
          <div class="card-info">
            <strong><?= htmlspecialchars($t['name']) ?></strong><br>
            <?= htmlspecialchars($t['email']) ?>
            <p><a href="edit_teacher.php?id=<?= $t['id'] ?>" class="button">Редактировать</a></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div style="text-align: center; margin-top: 30px;">
  <a href="main.html" class="button">← На главную</a>
</div>
</body>
</html>
