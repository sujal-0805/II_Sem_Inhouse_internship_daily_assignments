<?php
// database setup

// db connection function using pdo
function getDb()
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=localhost;dbname=student_dashboard;charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $ex) {
            die("DB connection failed: " . $ex->getMessage());
        }
    }
    return $pdo;
}

// easy escape function to prevent xss
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
