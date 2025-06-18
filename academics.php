<?php
include 'db.php';

$sql = "
SELECT 
    t.id, t.name, t.photo, t.bio,
    ROUND(AVG(r.crit1),1) AS avg1,
    ROUND(AVG(r.crit2),1) AS avg2,
    ROUND(AVG(r.crit3),1) AS avg3,
    ROUND(AVG(r.crit4),1) AS avg4,
    ROUND(AVG(r.crit5),1) AS avg5,
    ROUND(AVG((r.crit1 + r.crit2 + r.crit3 + r.crit4 + r.crit5)/5),1) AS avg_total
FROM teachers t
LEFT JOIN reviews r ON t.id = r.teacher_id
GROUP BY t.id;
";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="academics.css" />
  <title>Преподаватели</title>
</head>
<body>

  <header> 
    <img src="logo/NORM logo.svg" alt="Логотип" class="logo original">
    <img src="logo/adaptive-logo.svg" alt="Логотип" class="logo adaptive">
    <a class="profile-block-link" href="golova.php">
      <div class="profile-rating">
        <svg class="star-icon" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 .587l3.668 7.568 8.332 1.209-6.001 5.852 1.416 8.291L12 18.895l-7.415 3.895 1.416-8.291-6.001-5.852 8.332-1.209z"/>
        </svg>
        <span class="average-rating">4.94</span>
      </div>
      <img src="photo/golovan.jpg" alt="Аватар пользователя" class="avatar-image">
    </a>
  </header>

  <a class="back" href="main.html">Вернуться</a>

  <div class="academics-l-container">
    <?php while ($row = mysqli_fetch_assoc($result)):
      $score = $row['avg_total'] ?? 0.0;
      $scoreColor = 'gray';
      if ($score >= 4) $scoreColor = 'green';
      elseif ($score >= 3) $scoreColor = 'orange';
      elseif ($score > 0) $scoreColor = 'red';
    ?>
    <a class="academics-l-link" href="meva.php?id=<?= $row['id'] ?>">
      <div class="academics-l-item">
        <div class="img">
          <img src="<?= $row['photo'] ?>" alt="<?= $row['name'] ?>">
        </div>
        <div class="academic-t-container">
          <h2><?= $row['name'] ?></h2>
          <p class="discipline"><?= $row['bio'] ?></p>
          <p>Общий рейтинг: 
            <span style="color:<?= $scoreColor ?>;">
              <?= $score > 0 ? $score : '0.0' ?>
            </span>
          </p>
        </div>
      </div>
    </a>
    <?php endwhile; ?>
  </div>

  <script src="academics.js"></script>
</body>
</html>