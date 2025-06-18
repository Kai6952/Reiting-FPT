<?php

return [
    'host'    => 'localhost',
    'dbname'  => 'reiting_fpt',
    'user'    => 'root',
    'pass'    => 'root',

    $mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    error_log('MySQLi Connect Error: ' . $mysqli->connect_error);
    die('Не удалось подключиться к БД');
}

?>