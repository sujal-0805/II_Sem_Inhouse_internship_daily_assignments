<?php
require_once 'config.php';
// load config and check login
requireAuth();

$pdo = getDb();
// check query string for id
$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    // find student profile picture to delete from disk
    $stmt = $pdo->prepare('SELECT photo FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if ($student) {
        deletePhotoFile($student['photo']);
        // delete record from DB table
        $delete = $pdo->prepare('DELETE FROM students WHERE id = ?');
        $delete->execute([$id]);
        setFlash('success', 'Student removed successfully.');
    } else {
        setFlash('danger', 'Student not found.');
    }
}

redirect('dashboard.php');
?>
