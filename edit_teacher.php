<?php
session_start();
require_once 'db.php';

if ($_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit;
}

$teacher_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("
  SELECT t.*, u.email
  FROM teachers t
  JOIN users u ON u.id = t.user_id
  WHERE t.id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) die("Преподаватель не найден");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $photo = trim($_POST['photo']);
  $bio = trim($_POST['bio']);

  $stmt = $conn->prepare("UPDATE teachers SET name=?, photo=?, bio=? WHERE id=?");
  $stmt->bind_param("sssi", $name, $photo, $bio, $teacher_id);
  $stmt->execute();
  $stmt->close();

  header("Location: app.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Редактировать преподавателя</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <div class="admin-panel">
    <h1>Редактировать преподавателя</h1>
    <form method="POST" class="form-block">
      <input type="text" name="name" value="<?= htmlspecialchars($teacher['name']) ?>" required>
      <input type="text" name="photo" value="<?= htmlspecialchars($teacher['photo']) ?>">
      <textarea name="bio"><?= htmlspecialchars($teacher['bio']) ?></textarea>
      <button type="submit">Сохранить</button>
      <a href="app.php" class="button">Назад</a>
    </form>
  </div>
</body>
</html>
