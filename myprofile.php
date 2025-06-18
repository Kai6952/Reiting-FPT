<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'guest';

if (!$user_id || $role !== 'teacher') {
  ?>
  <!DOCTYPE html>
  <html lang="ru">
  <head>
    <meta charset="UTF-8">
    <title>Профиль</title>
    <link rel="stylesheet" href="profile.css">
  </head>
  <body>
    <div class="profile-page" style="text-align:center;">
      <h1>Профиль недоступен</h1>
      <p style="color:#ccc;">Данная страница доступна только для преподавателей.</p>
      <a href="logout.php?redirect=index.php" class="button" style="margin-top:20px;">Войти в аккаунт</a>
    </div>
  </body>
  </html>
  <?php exit;
}

$stmt = $conn->prepare("
  SELECT t.*, u.name
  FROM teachers t
  JOIN users u ON u.id = t.user_id
  WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$t = $stmt->get_result()->fetch_assoc();
$stmt->close();

$disciplines = [
  'Программирование мобильных модулей',
  'Основы алгоритмизации и программирования',
  'Программирование на языке С++',
  'Основы компьютерных сетей',
  'Оптимизация и настройка ПК'
];

$stmt = $conn->prepare("SELECT crit1, crit2, crit3, crit4, crit5 FROM reviews WHERE teacher_id = ?");
$stmt->bind_param("i", $t['id']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$sum = [0,0,0,0,0]; $count = 0;
while ($row = $result->fetch_assoc()) {
  for ($i = 0; $i < 5; $i++) $sum[$i] += $row["crit".($i+1)];
  $count++;
}
$avg = $count ? array_map(fn($v) => round($v / $count, 2), $sum) : ['–','–','–','–','–'];
$overall = $count ? round(array_sum($avg) / 5, 2) : '–';
$overallClass = is_numeric($overall) ? (
    $overall >= 4.5 ? 'green' :
    ($overall >= 3 ? 'yellow' : 'red')
  ) : 'gray';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Мой профиль</title>
  <link rel="stylesheet" href="profile.css">
</head>
<body>

  <div class="top-actions">
    <img src="images/logo.svg" alt="Логотип">
    <div class="buttons">
      <a href="main.html" class="back">← Назад</a>
      <a href="logout.php" class="logout">Выйти</a>
    </div>
  </div>

  <div class="profile-container">
    <div class="profile-photo">
      <img src="<?= htmlspecialchars($t['photo']) ?>" alt="Фото преподавателя">
    </div>

    <div class="profile-details">
      <h1><?= htmlspecialchars($t['name']) ?></h1>
      <p><strong>Образование:</strong> среднее-профессиональное</p>
      <p><strong>Квалификация:</strong> техник-программист</p>
      <p><strong>Направление:</strong> 09.02.03 Программирование в компьютерных системах</p>
      <p><strong>Стаж:</strong> общий – 2 года, по специальности – 2 года</p>

      <p><strong>Преподаваемые дисциплины:</strong></p>
      <ul>
        <?php foreach ($disciplines as $d): ?>
          <li><?= $d ?></li>
        <?php endforeach; ?>
      </ul>

      <p><strong>Общий рейтинг:</strong> <span class="score <?= $overallClass ?>"><?= $overall ?></span></p>

      <?php
        $labels = ['Знание предмета','Качество написания','Адекватность','Своевременность','Доступность'];
        foreach ($avg as $i => $val):
          $percent = is_numeric($val) ? $val * 20 : 0;
          $class = is_numeric($val) ? (
              $val >= 4.5 ? 'green' :
              ($val >= 3 ? 'yellow' : 'red')
            ) : 'gray';
      ?>
      <div class="rating-line <?= $class ?>">
        <div class="score"><?= is_numeric($val) ? $val : '–' ?></div>
        <div class="bar"><div style="width: <?= $percent ?>%"></div></div>
        <span><?= $labels[$i] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</body>
</html>
