<?php
require_once __DIR__ . '/config.php';

$pdo = getDb();

// check if user posted a form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['student_action'])) {
        $action = $_POST['student_action'];

        // create or update a student
        if ($action === 'create' || $action === 'edit') {
            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $branch = trim($_POST['branch'] ?? '');
            $cgpa = (float) ($_POST['cgpa'] ?? 0);
            $status = $_POST['status'] ?? 'Active';
            $photoPath = null;

            // if editing, get the old photo name first
            if ($action === 'edit' && $id > 0) {
                $stmt = $pdo->prepare('SELECT photo FROM students WHERE id = ?');
                $stmt->execute([$id]);
                $existing = $stmt->fetch();
            }

            // check photo upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                // check photo extension
                if (!in_array($ext, $allowed, true)) {
                    $error = 'Only JPG, PNG, and GIF images are allowed.';
                } else {
                    // upload directory
                    $uploadsDir = __DIR__ . '/uploads/students';
                    if (!is_dir($uploadsDir)) {
                        mkdir($uploadsDir, 0777, true);
                    }
                    $fileName = uniqid('student_', true) . '.' . $ext;
                    $targetPath = $uploadsDir . '/' . $fileName;
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                        $photoPath = 'uploads/students/' . $fileName;
                    } else {
                        $error = 'Photo upload failed.';
                    }
                }
            }

            // save to db if no errors
            if (empty($error)) {
                if ($action === 'create') {
                    // create insert sql query
                    $sql = 'INSERT INTO students (name, email, branch, cgpa, status, photo) VALUES (?, ?, ?, ?, ?, ?)';
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$name, $email, $branch, $cgpa, $status, $photoPath]);
                } else {
                    // update existing student
                    $sql = 'UPDATE students SET name = ?, email = ?, branch = ?, cgpa = ?, status = ?' . ($photoPath ? ', photo = ?' : '') . ' WHERE id = ?';
                    $params = [$name, $email, $branch, $cgpa, $status];
                    if ($photoPath) {
                        $params[] = $photoPath;
                    }
                    $params[] = $id;
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                }
            }
        }

        // quick update status button handler
        if ($action === 'update_status') {
            $id = (int) ($_POST['student_id'] ?? 0);
            $status = $_POST['status'] ?? 'Active';
            $stmt = $pdo->prepare('UPDATE students SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);
        }
    }

    // go back to main page to prevent resubmit on refresh
    header('Location: index.php');
    exit;
}

$viewAll = isset($_GET['view']) && $_GET['view'] === 'all';
$branchFilter = trim($_GET['branch'] ?? '');
$searchTerm = trim($_GET['search'] ?? '');
$cgpaMin = $_GET['cgpa_min'] ?? '';
$cgpaMax = $_GET['cgpa_max'] ?? '';

$where = ['1 = 1'];
$params = [];

if (!$viewAll) {
    $where[] = 'status = ?';
    $params[] = 'Active';
}

if ($branchFilter !== '') {
    $where[] = 'branch = ?';
    $params[] = $branchFilter;
}

// check search query
if ($searchTerm !== '') {
    $where[] = '(name LIKE ? OR email LIKE ? OR branch LIKE ?)';
    $searchPattern = '%' . $searchTerm . '%';
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $params[] = $searchPattern;
}

if ($cgpaMin !== '') {
    $where[] = 'cgpa >= ?';
    $params[] = (float) $cgpaMin;
}

if ($cgpaMax !== '') {
    $where[] = 'cgpa <= ?';
    $params[] = (float) $cgpaMax;
}

// query students from table
$sql = 'SELECT * FROM students WHERE ' . implode(' AND ', $where) . ' ORDER BY name ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// branches list
$branchesStmt = $pdo->query('SELECT DISTINCT branch FROM students ORDER BY branch ASC');
$branches = $branchesStmt->fetchAll(PDO::FETCH_COLUMN);

// load counts and average cgpa
$totalStmt = $pdo->query('SELECT COUNT(*) AS total_students FROM students');
$totalStudents = $totalStmt->fetch()['total_students'];

$avgStmt = $pdo->query('SELECT AVG(cgpa) AS average_cgpa FROM students');
$averageCgpa = (float) $avgStmt->fetch()['average_cgpa'];

$branchStatsStmt = $pdo->query('SELECT branch, COUNT(*) AS total FROM students GROUP BY branch ORDER BY branch ASC');
$branchStats = $branchStatsStmt->fetchAll();

// fallback photo
$placeholder = 'uploads/placeholder.svg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management Dashboard</title>
    <!-- bootstrap styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Student Management Dashboard</h1>
            <p class="text-muted mb-0">Manage student records, filters, search, and uploads.</p>
        </div>
        <a href="README.md" class="btn btn-outline-secondary btn-sm">View README</a>
    </div>

    <!-- stats counters -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Students</h5>
                    <p class="display-6 mb-0"><?= e((string) $totalStudents) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-muted">Average CGPA</h5>
                    <p class="display-6 mb-0"><?= e(number_format($averageCgpa, 2)) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title text-muted">Students per Branch</h5>
                    <ul class="mb-0 ps-3">
                        <?php foreach ($branchStats as $stat): ?>
                            <li><?= e($stat['branch']) ?>: <?= e((string) $stat['total']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- input form for adding new student -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Add or Update Student</h2>
            <form method="post" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="student_action" value="create">
                <div class="col-md-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Branch</label>
                    <input type="text" name="branch" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">CGPA</label>
                    <input type="number" step="0.01" name="cgpa" class="form-control" min="0" max="10" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Photo</label>
                    <input type="file" name="photo" class="form-control">
                </div>
                <div class="col-md-9 d-flex align-items-end justify-content-end">
                    <button type="submit" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- student records list -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Student Records</h2>
                <div>
                    <a href="index.php?view=all" class="btn btn-outline-primary btn-sm <?= $viewAll ? 'active' : '' ?>">View All</a>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm <?= !$viewAll ? 'active' : '' ?>">Active Only</a>
                </div>
            </div>

            <!-- filtering form -->
            <form method="get" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search name, email, or branch" value="<?= e($searchTerm) ?>">
                </div>
                <div class="col-md-2">
                    <select name="branch" class="form-select">
                        <option value="">All Branches</option>
                        <?php foreach ($branches as $branchName): ?>
                            <option value="<?= e($branchName) ?>" <?= $branchFilter === $branchName ? 'selected' : '' ?>><?= e($branchName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="cgpa_min" class="form-control" placeholder="Min CGPA" value="<?= e((string) $cgpaMin) ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" step="0.01" name="cgpa_max" class="form-control" placeholder="Max CGPA" value="<?= e((string) $cgpaMax) ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </div>
                <div class="col-md-1">
                    <a href="index.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Branch</th>
                            <th>CGPA</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No students found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <img src="<?= e($student['photo'] ?: $placeholder) ?>"
                                             alt="Photo of <?= e($student['name']) ?>"
                                             width="48"
                                             height="48"
                                             class="rounded-circle border"
                                             onerror="this.onerror=null;this.src='<?= e($placeholder) ?>';">
                                    </td>
                                    <td><?= e($student['name']) ?></td>
                                    <td><?= e($student['email']) ?></td>
                                    <td><?= e($student['branch']) ?></td>
                                    <td><?= e(number_format((float) $student['cgpa'], 2)) ?></td>
                                    <td>
                                        <!-- change status form -->
                                        <form method="post" class="d-flex gap-2">
                                            <input type="hidden" name="student_action" value="update_status">
                                            <input type="hidden" name="student_id" value="<?= e((string) $student['id']) ?>">
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="Active" <?= $student['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                                <option value="Inactive" <?= $student['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
                                    </td>
                                    <td>
                                        <!-- edit row directly inline -->
                                        <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
                                            <input type="hidden" name="student_action" value="edit">
                                            <input type="hidden" name="id" value="<?= e((string) $student['id']) ?>">
                                            <input type="text" name="name" value="<?= e($student['name']) ?>" class="form-control form-control-sm" required>
                                            <input type="email" name="email" value="<?= e($student['email']) ?>" class="form-control form-control-sm" required>
                                            <input type="text" name="branch" value="<?= e($student['branch']) ?>" class="form-control form-control-sm" required>
                                            <input type="number" step="0.01" name="cgpa" value="<?= e(number_format((float) $student['cgpa'], 2)) ?>" class="form-control form-control-sm" required>
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="Active" <?= $student['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                                <option value="Inactive" <?= $student['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                            <input type="file" name="photo" class="form-control form-control-sm">
                                            <button type="submit" class="btn btn-sm btn-success">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
