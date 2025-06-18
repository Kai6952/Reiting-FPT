<?php
$host = 'localhost';
$user = 'root';
$password = 'root'; // или '123', если ты ставил пароль
$database = 'reiting_fpt'; // проверь точное имя своей базы!

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Ошибка подключения к базе данных: " . mysqli_connect_error());
}