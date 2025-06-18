<?php
// index.php — единый файл авторизации и админ-панели
session_start();

// --- 1. Настройки БД и установка соединения ---
$dbHost    = 'localhost';
$dbName    = 'reiting_fpt';
$dbUser    = 'root';
$dbPass    = 'root';


session_start();

function getMysqli() {
    global $host, $dbName, $dbUser, $dbPass;
    static $mysqli;
    if (!$mysqli) {
        $mysqli = new mysqli($host, $dbUser, $dbPass, $dbName);
        if ($mysqli->connect_error) {
            die('<p class="error">Ошибка подключения: ' . $mysqli->connect_error . '</p>');
        }
        $mysqli->set_charset('utf8mb4');
    }
    return $mysqli;
}

// 🔹 2) Функции авторизации
function login($email, $password) {
    $m = getMysqli();
    $stmt = $m->prepare("SELECT id,name,email,password,role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];

        // ➜ если это обычный пользователь, отправляем на main.html
        if ($user['role'] === 'user') {
            header('Location: main.html');
            exit;
        }

        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    $_SESSION = [];
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function isAdmin() {
    $u = currentUser();
    return $u && $u['role'] === 'admin';
}

// 🔹 3) Функция проверки прав (админ)
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: main.html');
        exit;
    }
}

// 🔹 4) Обработка входа/выхода
$action = $_GET['action'] ?? 'dashboard';
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    if (login($_POST['email'], $_POST['password'])) {
        header('Location: ?action=dashboard');
        exit;
    } else {
        $loginError = '<p class="error">Неверный email или пароль</p>';
    }
}

if ($action === 'logout') {
    logout();
    header('Location: ?action=login');
    exit;
}

// 🔹 5) Генерация контента страницы
$page_content = '';

switch ($action) {
    case 'login':
        $page_content .= "<h1>Вход</h1>{$loginError}";
        $page_content .= <<<HTML
        <form method="post">
          <label>Email:</label>
          <input type="email" name="email" class="form-input" required>
          <label>Пароль:</label>
          <input type="password" name="password" class="form-input" required>
          <button type="submit" class="btn">Войти</button>
        </form>
        HTML;
        break;

    default:
        requireAdmin();
        if ($action === 'dashboard') {
            $u = currentUser();
            $page_content .= "<h1>Привет, ".htmlspecialchars($u['name'])."!</h1>";
            $page_content .= <<<HTML
            <nav>
              <a href="?action=users" class="btn">Пользователи</a>
              <a href="?action=reviews" class="btn">Отзывы</a>
              <a href="?action=logout" class="btn logout">Выход</a>
            </nav>
            HTML;
        }
        break;
}