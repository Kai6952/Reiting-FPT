<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header style="display:flex;justify-content:flex-end;align-items:center">
  <?php if (!empty($_SESSION['role'])): ?>
    <!-- Аватар/иконка профиля -->
    <a href="myprofile.php" style="margin-right:1em">
      <img src="assets/user-icon.png" 
           alt="Мой профиль" 
           width="32" height="32"
           style="vertical-align:middle">
    </a>
    <!-- Кнопка выхода -->
    <form action="logout.php" method="POST" style="margin:0">
      <button type="submit">Выйти</button>
    </form>
  <?php endif; ?>
</header>
<hr>
