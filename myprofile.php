<?php
session_start();
require_once 'db.php';

// 1. Устанавливаем роль по умолчанию
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'guest';
}

// 2. ADMIN: показываем простую страницу
if ($_SESSION['role'] === 'admin') {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head><meta charset="UTF-8"><title>Профиль администратора</title></head>
    <body>
      <h1>Вы вошли как администратор</h1>
      <p>Для управления системой перейдите в <a href="app.php">админ-панель</a>.</p>
      <form action="logout.php" method="POST"><button>Выйти</button></form>
    </body>
    </html>
    <?php exit;
}

// 3. Если пользователь → пробуем найти, есть ли он в таблице teachers
$uid = $_SESSION['user_id'] ?? null;
$teacher = null;

if ($_SESSION['role'] === 'user' && $uid) {
    $stmt = $mysqli->prepare("SELECT id, name, photo, bio FROM teachers WHERE user_id = ?");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $teacher = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

function ratingColor($v) {
    if ($v >= 4.51) return '#36F87D';
    if ($v >= 3.0)  return '#F8B436';
    return '#CF3232';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Мой профиль</title>
  <style>
    .slider { background:#eee; height:8px; border-radius:4px; margin-bottom:10px; }
    .slider-fill { height:100%; }
  </style>
</head>
<body>
  <nav>
    <a href="index.php">Главная</a> |
    <form action="logout.php" method="POST" style="display:inline">
      <button type="submit">Выйти</button>
    </form>
  </nav>
  <hr>

<?php if ($teacher): ?>
  <h1><?= htmlspecialchars($teacher['name']) ?></h1>
  <img src="<?= htmlspecialchars($teacher['photo']) ?>" alt="Фото" width="200"><br>
  <p><?= nl2br(htmlspecialchars($teacher['bio'])) ?></p>

  <?php
    $stmt = $mysqli->prepare("
      SELECT AVG(crit1) AS c1, AVG(crit2) AS c2, AVG(crit3) AS c3,
             AVG(crit4) AS c4, AVG(crit5) AS c5
      FROM reviews WHERE teacher_id = ?
    ");
    $stmt->bind_param('i', $teacher['id']);
    $stmt->execute();
    $avg = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $labels = [
      'Знание предмета'    => round((float)$avg['c1'], 2),
      'Качество изложения' => round((float)$avg['c2'], 2),
      'Коммуникабельность' => round((float)$avg['c3'], 2),
      'Справедливость'     => round((float)$avg['c4'], 2),
      'Доступность'        => round((float)$avg['c5'], 2)
    ];
    $total = array_sum($labels) / count($labels);
  ?>

  <h2>Общий рейтинг: 
    <span style="color:<?= ratingColor($total) ?>;">
      <?= number_format($total,2) ?>
    </span>
  </h2>

  <?php foreach ($labels as $label => $val): ?>
    <p><?= $label ?>: 
      <span style="color:<?= ratingColor($val) ?>;">
        <?= $val ?>
      </span>
    </p>
    <div class="slider">
      <div class="slider-fill" style="
        width: <?= $val * 20 ?>%;
        background: <?= ratingColor($val) ?>;"></div>
    </div>
  <?php endforeach; ?>

  <h2>Отзывы студентов</h2>
  <?php
    $stmt = $mysqli->prepare("
      SELECT content, created_at
      FROM reviews
      WHERE teacher_id = ?
      ORDER BY created_at DESC
    ");
    $stmt->bind_param('i', $teacher['id']);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
  ?>

  <?php if (empty($reviews)): ?>
    <p>Отзывов пока нет.</p>
  <?php else: ?>
    <?php foreach ($reviews as $r): ?>
      <blockquote>
        <p><?= nl2br(htmlspecialchars($r['content'])) ?></p>
        <small><?= $r['created_at'] ?></small>
      </blockquote>
    <?php endforeach; ?>
  <?php endif; ?>

<?php else: ?>
  <h1>Вы вошли как «Гость»</h1>
  <p>Вы просматриваете систему как гость. Доступны следующие действия:</p>
  <ul>
    <li><a href="index.php">Войти как преподаватель</a></li>
    <li><a href="academics.html">Посмотреть преподавателей</a></li>
  </ul>
<?php endif; ?>

</body>
</html>
