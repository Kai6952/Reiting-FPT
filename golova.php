<?php
session_start();
require_once 'db.php';   // $mysqli

// 1) Кто зашёл?
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'guest';
}

// 2) Админу — сразу в админку
if ($_SESSION['role'] === 'admin') {
    header('Location: app.php');
    exit;
}

// 3) Пользователь (не гость)?  
$uid = $_SESSION['user_id'] ?? null;
$teacher = null;
if ($_SESSION['role'] === 'user' && $uid) {
    $stm = $mysqli->prepare(
      "SELECT id,name,photo,bio FROM teachers WHERE user_id=?"
    );
    $stm->bind_param('i',$uid);
    $stm->execute();
    $teacher = $stm->get_result()->fetch_assoc();
    $stm->close();
}

// 4) Готовим данные для шаблона
// если $teacher — массив, то это профиль преподавателя
// иначе — это гость (или студент без профиля)

if ($teacher) {
    // средний общий рейтинг
    $stm = $mysqli->prepare("
      SELECT 
        AVG((crit1+crit2+crit3+crit4+crit5)/5) AS avg_total
      FROM reviews
      WHERE teacher_id = ?
    ");
    $stm->bind_param('i',$teacher['id']);
    $stm->execute();
    $avg = $stm->get_result()->fetch_assoc();
    $stm->close();
    $total = round((float)$avg['avg_total'],2);
} 
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Мой профиль</title>
  <style>
    .slider { background:#eee; height:6px; border-radius:3px; overflow:hidden; }
    .slider-fill { height:100%; }
  </style>
</head>
<body>
  <!-- простое nav -->
  <nav>
    <a href="golova.php">Профиль</a> |
    <a href="logout.php">Выйти</a>
  </nav>
  <hr>

<?php if ($teacher): ?>
  <!-- ПРОФИЛЬ ПРЕПОДА -->
  <h1><?= htmlspecialchars($teacher['name']) ?></h1>
  <img src="<?= htmlspecialchars($teacher['photo']) ?>"
       alt="" width="150" style="display:block;margin-bottom:1em">
  <p><?= nl2br(htmlspecialchars($teacher['bio'])) ?></p>

  <h2>Общий рейтинг: <?= number_format($total,2) ?></h2>

  <!-- 5 критериев -->
  <?php 
    // вытянем средние для каждого
    $stm = $mysqli->prepare("
      SELECT 
        AVG(crit1) c1,AVG(crit2) c2,AVG(crit3) c3,
        AVG(crit4) c4,AVG(crit5) c5
      FROM reviews WHERE teacher_id=?
    ");
    $stm->bind_param('i',$teacher['id']);
    $stm->execute();
    $c = $stm->get_result()->fetch_assoc();
    $stm->close();
    $crit = [
      'Знание предмета'      => round((float)$c['c1'],2),
      'Качество изложения'   => round((float)$c['c2'],2),
      'Коммуникабельность'   => round((float)$c['c3'],2),
      'Справедливость'       => round((float)$c['c4'],2),
      'Доступность'          => round((float)$c['c5'],2),
    ];
    function col($v){
      return $v>=4.51? '#36F87D':($v>=3? '#F8B436':'#CF3232');
    }
  ?>
  <?php foreach($crit as $label=>$val): ?>
    <p><?= $label ?>: 
      <span style="color:<?= col($val) ?>;"><?= number_format($val,2) ?></span>
    </p>
    <div class="slider">
      <div class="slider-fill" 
           style="width:<?= $val*20 ?>%;background:<?= col($val) ?>;">
      </div>
    </div>
  <?php endforeach; ?>

  <p>
    <a href="reit.php">
      <button>Оставить отзыв</button>
    </a>
  </p>

<?php else: ?>
  <!-- ПРОФИЛЬ ГОСТЯ/СТУДЕНТА БЕЗ ПРЕПОДА -->
  <h1>Вы вошли как «Гость»</h1>
  <p>Информация преподавателя недоступна.</p>
  <p>Отзывы можно только просматривать, но не оставлять.</p>
<?php endif; ?>

</body>
</html>
