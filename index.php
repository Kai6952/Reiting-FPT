<?php
  require __DIR__ . '/app.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Админка</title>
  <!-- здесь подключаем CSS -->
  <link rel="stylesheet" href="index.css">
</head>
<body>
  <div class="container">
    <?= $page_content ?>
  </div>
</body>
</html>
