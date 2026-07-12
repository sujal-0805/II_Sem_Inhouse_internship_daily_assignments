<?php
// DB settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'secure_auth_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// secure session config (to keep cookies safe)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    session_set_cookie_params([
        'lifetime' => 0, 
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// connect database using pdo
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("A database connection error occurred. Please verify your settings and import schema.sql.");
}

// helper to clean outputs and prevent xss
function escape($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// check if user is logged in
function is_authenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// enforce page authentication
function require_auth() {
    if (!is_authenticated()) {
        $_SESSION['flash_error'] = "You must be logged in to access that page.";
        header("Location: login.php");
        exit();
    }
}
?>
