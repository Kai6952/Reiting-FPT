<?php
// db.php — прямое подключение к MySQL через MySQLi

// 1) Параметры БД
$host   = 'localhost';
$user   = 'root';
$pass   = 'root';
$dbname = 'reiting_fpt';

// 2) Создаём подключение
$mysqli = new mysqli($host, $user, $pass, $dbname);

// 3) Обработка ошибок подключения
if ($mysqli->connect_error) {
    error_log('MySQLi Connect Error: ' . $mysqli->connect_error);
    die('Не удалось подключиться к БД');
}

// 4) Устанавливаем кодировку
$mysqli->set_charset('utf8mb4');
