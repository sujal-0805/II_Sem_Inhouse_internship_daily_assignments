<?php
// DB settings
$host = 'localhost';
$dbname = 'student_records';
$username = 'root';
$password = '';

try {
    // connect to mysql with pdo
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // kill page if db connection fails
    die('Database connection failed: ' . $e->getMessage());
}
?>
