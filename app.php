<?php
session_start();
require_once 'db.php';

// Только администратор может попасть сюда
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$categories = $mysqli
    ->query("SELECT id, name FROM categories WHERE id > 1")
    ->fetch_all(MYSQLI_ASSOC);

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $photo    = trim($_POST['photo']);
    $bio      = trim($_POST['bio']);
    $cats     = $_POST['cats'] ?? [];

    // 1. Создаём пользователя
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("
        INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, 'user')
    ");
    $stmt->bind_param('sss', $name, $email, $hash);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // 2. Создаём преподавателя, привязав к этому user
    $stmt = $mysqli->prepare("
        INSERT INTO teachers (user_id, name, photo, bio)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('isss', $user_id, $name, $photo, $bio);
    $stmt->execute();
    $teacher_id = $stmt->insert_id;
    $stmt->close();

    // 3. Вставляем преподавателя в категорию «Общая» (id = 1)
    $stmt = $mysqli->prepare("
        INSERT INTO teacher_category (teacher_id, category_id)
        VALUES (?, 1)
    ");
    $stmt->bind_param('i', $teacher_id);
    $stmt->execute();
    $stmt->close();

    // 4. Дополнительные категории
    if (!empty($cats)) {
        $stmt = $mysqli->prepare("
            INSERT INTO teacher_category (teacher_id, category_id)
            VALUES (?, ?)
        ");
        foreach ($cats as $cat_id) {
            $cat_id = (int)$cat_id;
            if ($cat_id !== 1) {
                $stmt->bind_param('ii', $teacher_id, $cat_id);
                $stmt->execute();
            }
        }
        $stmt->close();
    }

    // 5. Готово — редирект на список
    header('Location: academics.html');
    exit;
}

// Загружаем текущих преподавателей
$teachers = $mysqli->query("
    SELECT t.id, t.name, u.email,
           GROUP_CONCAT(c.name SEPARATOR ', ') AS categories
    FROM teachers t
    LEFT JOIN users u ON u.id = t.user_id
    LEFT JOIN teacher_category tc ON tc.teacher_id = t.id
    LEFT JOIN categories c ON c.id = tc.category_id
    GROUP BY t.id
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
</head>
<body>
    <h1>Создать преподавателя</h1>
    <form method="POST">
        <label>ФИО:<br>
            <input type="text" name="name" required>
        </label><br><br>

        <label>Email:<br>
            <input type="email" name="email" required>
        </label><br><br>

        <label>Пароль:<br>
            <input type="password" name="password" required>
        </label><br><br>

        <label>Фото (путь):<br>
            <input type="text" name="photo" placeholder="photo/avatar.jpg">
        </label><br><br>

        <label>Биография:<br>
            <textarea name="bio" rows="4"></textarea>
        </label><br><br>

        <fieldset>
            <legend>Доп. категории (опционально):</legend>
            <?php foreach ($categories as $cat): ?>
                <label>
                    <input type="checkbox" name="cats[]" value="<?= $cat['id'] ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset><br>

        <button type="submit">Создать и перейти к списку</button>
    </form>

    <hr>
    <h2>Список преподавателей</h2>
    <table border="1" cellpadding="4" cellspacing="0">
        <tr>
            <th>ID</th><th>Имя</th><th>Email</th><th>Категории</th>
        </tr>
        <?php foreach ($teachers as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['name']) ?></td>
                <td><?= htmlspecialchars($t['email']) ?></td>
                <td><?= htmlspecialchars($t['categories']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="main.html">Перейти на главную</a></p>
</body>
</html>
