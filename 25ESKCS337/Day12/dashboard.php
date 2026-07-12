<?php
require_once 'config.php';
// check auth
requireAuth();

$pdo = getDb();

$flash = getFlash();

// load stats for top dashboard cards
$statsStmt = $pdo->query('SELECT COUNT(*) AS total_students, ROUND(AVG(cgpa), 2) AS avg_cgpa FROM students');
$stats = $statsStmt->fetch();

// branch stats
$branchStmt = $pdo->query('SELECT branch, COUNT(*) AS total FROM students GROUP BY branch ORDER BY total DESC');
$branches = $branchStmt->fetchAll();

// show 5 most recent registrations
$recentStmt = $pdo->prepare('SELECT * FROM students ORDER BY date_registered DESC LIMIT 5');
$recentStmt->execute();
$recentStudents = $recentStmt->fetchAll();

// check filters and search queries
$search = trim($_GET['search'] ?? '');
$courseFilter = trim($_GET['course'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$query = 'SELECT * FROM students WHERE 1=1';
$params = [];

// search term
if ($search !== '') {
    $query .= ' AND (name LIKE ? OR email LIKE ? OR branch LIKE ?)';
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

// course select
if ($courseFilter !== '') {
    $query .= ' AND course = ?';
    $params[] = $courseFilter;
}

// status select
if ($statusFilter !== '') {
    $query .= ' AND status = ?';
    $params[] = $statusFilter;
}

$query .= ' ORDER BY date_registered DESC';

// run query to get students
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

// get list of courses for dropdown
$coursesStmt = $pdo->query('SELECT DISTINCT course FROM students ORDER BY course');
$courses = $coursesStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="dashboard.php"><i class="bi bi-mortarboard-fill me-2"></i>Student Portal</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50">Hello, <?= h($_SESSION['user_name'] ?? 'User') ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <?php if ($flash): ?>
            <div class="alert alert-<?= h($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= h($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard</h2>
                <p class="text-muted mb-0">Monitor student records and manage registrations.</p>
            </div>
            <a href="add-student.php" class="btn btn-success"><i class="bi bi-plus-circle me-2"></i>Add Student</a>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm rounded-4 h-100 slide-up">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total Students</h6>
                                <h3 class="fw-bold mb-0"><?= h((int) ($stats['total_students'] ?? 0)) ?></h3>
                            </div>
                            <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm rounded-4 h-100 slide-up">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Average CGPA</h6>
                                <h3 class="fw-bold mb-0"><?= h($stats['avg_cgpa'] ?? '0.00') ?></h3>
                            </div>
                            <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-graph-up-arrow"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm rounded-4 h-100 slide-up">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Students per Branch</h6>
                                <div class="small text-muted">
                                    <?php foreach ($branches as $branch): ?>
                                        <div><?= h($branch['branch']) ?>: <strong><?= h($branch['total']) ?></strong></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-diagram-3-fill"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm rounded-4 h-100 slide-up">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3"><i class="bi bi-clock-history me-2"></i>Recent Registrations</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentStudents as $student): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <div class="fw-semibold"><?= h($student['name']) ?></div>
                                        <div class="small text-muted"><?= h($student['course']) ?></div>
                                    </div>
                                    <span class="badge bg-light text-dark"><?= h(date('M d', strtotime($student['date_registered']))) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card shadow-sm rounded-4 slide-up">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-semibold mb-0"><i class="bi bi-people me-2"></i>Student Directory</h5>
                            <a href="add-student.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus me-1"></i>New</a>
                        </div>

                        <form method="get" class="row g-2 mb-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Search by name, email, branch" value="<?= h($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="course">
                                    <option value="">All Courses</option>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= h($course) ?>" <?= $courseFilter === $course ? 'selected' : '' ?>><?= h($course) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="Active" <?= $statusFilter === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= $statusFilter === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-grid">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th>Course</th>
                                        <th>Branch</th>
                                        <th>Status</th>
                                        <th>CGPA</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($students): ?>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><img src="<?= h(getPhotoUrl($student['photo'])) ?>" alt="<?= h($student['name']) ?>" class="avatar-sm"></td>
                                                <td>
                                                    <div class="fw-semibold"><?= h($student['name']) ?></div>
                                                    <div class="small text-muted"><?= h($student['email']) ?></div>
                                                </td>
                                                <td><?= h($student['course']) ?></td>
                                                <td><?= h($student['branch']) ?></td>
                                                <td><span class="badge <?= $student['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"><?= h($student['status']) ?></span></td>
                                                <td><?= h($student['cgpa']) ?></td>
                                                <td>
                                                    <a href="edit-student.php?id=<?= (int) $student['id'] ?>" class="btn btn-outline-primary btn-sm me-2"><i class="bi bi-pencil"></i></a>
                                                    <a href="delete-student.php?id=<?= (int) $student['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this student record?');"><i class="bi bi-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No students match your filters yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
