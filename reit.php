<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'guest') {
  header("Location: index.php");
  exit;
}

$teacher_id = intval($_GET['id'] ?? 0);

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $c = array_map('intval', [
    $_POST['crit1'], $_POST['crit2'], $_POST['crit3'], $_POST['crit4'], $_POST['crit5']
  ]);
  $content = trim($_POST['content']);

  $stmt = $conn->prepare("
    INSERT INTO reviews (user_id, teacher_id, crit1, crit2, crit3, crit4, crit5, content, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
  ");
  $stmt->bind_param('iiiiiiis', $user_id, $teacher_id, $c[0], $c[1], $c[2], $c[3], $c[4], $content);
  $stmt->execute();
  $stmt->close();

  header("Location: meva.php?id=$teacher_id");
  exit;
}

$labels = ['Знание предмета', 'Качество изложения', 'Коммуникабельность', 'Справедливость', 'Доступность'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Оценить преподавателя</title>
  <link rel="stylesheet" href="reit.css">
</head>
<body>
  <div class="rounded-block">
    <h2>Оцените преподавателя</h2>
    <form method="POST">
      <div class="criteria-container">
        <?php foreach ($labels as $i => $label): $index = $i + 1; ?>
          <div class="criteria">
            <label for="crit<?= $index ?>"><?= $label ?>:</label>
            <input type="range" id="crit<?= $index ?>" name="crit<?= $index ?>" min="1" max="5" value="1" class="slider">
            <span class="rating" id="val<?= $index ?>">1</span>
          </div>
        <?php endforeach; ?>
      </div>
      <textarea name="content" placeholder="Оставьте отзыв (необязательно)"></textarea>
      <button type="submit" class="button">Отправить</button>
    </form>
  </div>
  <script>
    const sliders = document.querySelectorAll('.slider');
    sliders.forEach(slider => {
      slider.addEventListener('input', function() {
        this.nextElementSibling.textContent = this.value;
      });
    });
  </script>
</body>
</html>
