<?php
session_start();
session_unset();
session_destroy();

$redirect = $_GET['redirect'] ?? 'index.php';
header("Location: " . $redirect);
exit;
