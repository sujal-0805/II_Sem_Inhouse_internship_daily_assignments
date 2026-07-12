<?php
// config and helpers for student portal
session_start();

// DB config
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('DEFAULT_AVATAR', 'uploads/default-avatar.svg');

// set timezone
date_default_timezone_set('Asia/Kolkata');

// connect to DB using PDO
function getDb()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $ex) {
        die('Database connection failed: ' . $ex->getMessage());
    }

    return $pdo;
}

// redirect helper
function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

// lock page if not logged in
function requireAuth()
{
    if (empty($_SESSION['user_id'])) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Please sign in to continue.'];
        redirect('login.php');
    }
}

// check user session
function isLoggedIn()
{
    return !empty($_SESSION['user_id']);
}

// store temp message in session
function setFlash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// read temp message from session
function getFlash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// clean text to prevent xss
function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// get path to student photo
function getPhotoUrl($photoName)
{
    if (!empty($photoName) && file_exists(UPLOAD_DIR . $photoName)) {
        return 'uploads/' . h($photoName);
    }
    return DEFAULT_AVATAR;
}

// photo upload function
function uploadPhoto($file)
{
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return '';
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Photo upload failed.');
    }

    // validate image types
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        throw new RuntimeException('Only JPG, PNG, WEBP, and GIF images are allowed.');
    }

    // size check
    if ($file['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException('The photo must be 2MB or smaller.');
    }

    $extension = 'jpg';
    if ($mimeType === 'image/png') {
        $extension = 'png';
    } elseif ($mimeType === 'image/webp') {
        $extension = 'webp';
    } elseif ($mimeType === 'image/gif') {
        $extension = 'gif';
    }

    // unique filename
    $fileName = uniqid('student_', true) . '.' . $extension;
    $destination = UPLOAD_DIR . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Unable to save the uploaded photo.');
    }

    return $fileName;
}

// delete photo
function deletePhotoFile($fileName)
{
    if (empty($fileName)) {
        return;
    }

    $target = UPLOAD_DIR . $fileName;
    if (file_exists($target)) {
        unlink($target);
    }
}
?>
