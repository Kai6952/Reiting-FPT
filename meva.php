<?php
session_start();
require_once 'db.php';

$teacher_id = intval($_GET['id'] ?? 0);

// Получаем данные о преподавателе
$stmt = $conn->prepare("SELECT name, photo, bio FROM teachers WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) die("Преподаватель не найден.");

// Получаем все оценки
$stmt = $conn->prepare("SELECT crit1, crit2, crit3, crit4, crit5 FROM reviews WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$sum = [0, 0, 0, 0, 0];
$total = 0;
while ($row = $result->fetch_assoc()) {
  for ($i = 0; $i < 5; $i++) {
    $sum[$i] += $row["crit" . ($i + 1)];
  }
  $total++;
}

$avg = $total ? array_map(fn($v) => round($v / $total, 2), $sum) : ['–','–','–','–','–'];
$overall = $total ? round(array_sum($avg) / 5, 2) : '–';

$labels = ['Знание предмета', 'Качество изложения', 'Коммуникабельность', 'Справедливость', 'Доступность'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Профиль преподавателя</title>
  <link rel="stylesheet" href="reit.css">
</head>
<body>
  <div class="rounded-block">
    <h2><?= htmlspecialchars($teacher['name']) ?></h2>
    <img src="<?= htmlspecialchars($teacher['photo']) ?>" alt="Фото" class="teacher-photo">
    <p class="teacher-bio"><?= nl2br(htmlspecialchars($teacher['bio'])) ?></p>

    <h2 class="rating-title">Общий рейтинг: <?= $overall ?></h2>
    <div class="criteria-container">
      <?php foreach ($labels as $i => $label): ?>
        <div class="criteria">
          <label><?= $label ?>:</label>
          <input type="range" min="1" max="5" value="<?= $avg[$i] ?>" step="0.01" class="slider" disabled>
          <span class="rating"><?= $avg[$i] ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="centered-review-block">
  <?php if ($_SESSION['role'] === 'guest'): ?>
    <button class="button" onclick="showGuestModal()">Оставить отзыв</button>
  <?php else: ?>
    <a href="reit.php?id=<?= $teacher_id ?>" class="button">Оставить отзыв</a>
  <?php endif; ?>
</div>
<div id="guestModal" class="modal-overlay" onclick="hideGuestModal()">
  <div class="modal-box" onclick="event.stopPropagation()">
    <p>Войдите, чтобы оставить отзыв</p>
    <a href="index.php" class="button">Войти</a><br><br>
    <button onclick="hideGuestModal()" class="button secondary">Закрыть</button>
  </div>
</div>

<script>
function showGuestModal() {
  document.getElementById('guestModal').style.display = 'flex';
}
function hideGuestModal() {
  document.getElementById('guestModal').style.display = 'none';
}
</script>
</body>
</html>
