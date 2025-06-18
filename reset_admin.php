<?php
// reset_admin.php — одноразово, потом удалите

// 1) Подключаем вашу функцию getMysqli()
//    Скопируйте из index.php именно ту же реализацию
function getMysqli() {
    $host    = 'localhost';
    $dbName  = 'reiting_fpt';
    $dbUser  = 'root';
    $dbPass  = 'root';
    static $m = null;
    if ($m === null) {
        $m = new mysqli($host, $dbUser, $dbPass, $dbName);
        if ($m->connect_error) {
            die("Connect error: " . $m->connect_error);
        }
        $m->set_charset('utf8mb4');
    }
    return $m;
}

// 2) Новый админ
$newEmail    = 'admin@example.com';
$newPassword = 'NewP@ss123';         // задайте свой пароль
$newName     = 'SuperAdmin';         // любое имя
$newHash     = password_hash($newPassword, PASSWORD_DEFAULT);

// 3) Сбрасываем старых и создаём нового
$mysqli = getMysqli();
$mysqli->query("DELETE FROM users WHERE role = 'admin'");
$stmt = $mysqli->prepare(
    "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')"
);
$stmt->bind_param('sss', $newName, $newEmail, $newHash);
$stmt->execute();

// 4) Результат
echo "✔ Админ сброшен и создан заново.\n";
echo "Email: {$newEmail}\n";
echo "Пароль: {$newPassword}\n";

// не забудьте удалить reset_admin.php
