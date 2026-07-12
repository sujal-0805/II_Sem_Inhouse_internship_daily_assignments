<?php
require_once 'config.php';
// check auth
requireAuth();

$pdo = getDb();
$errors = [];

// check query parameter for student ID
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    setFlash('danger', 'Invalid student selected.');
    redirect('dashboard.php');
}

// find student by ID in table
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    setFlash('danger', 'Student not found.');
    redirect('dashboard.php');
}

// if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $course = trim($_POST['course'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $cgpa = trim($_POST['cgpa'] ?? '');
    $branch = trim($_POST['branch'] ?? '');
    $status = $_POST['status'] ?? 'Active';
    $photoName = $student['photo'];

    // fields verification
    if ($name === '' || $email === '' || $gender === '' || $course === '' || $address === '' || $cgpa === '' || $branch === '') {
        $errors[] = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    } elseif (!is_numeric($cgpa) || (float) $cgpa < 0 || (float) $cgpa > 10) {
        $errors[] = 'CGPA must be a numeric value between 0 and 10.';
    } else {
        try {
            // handle photo upload if new one was selected
            if (!empty($_FILES['photo']['tmp_name'])) {
                $newPhotoName = uploadPhoto($_FILES['photo']);
                if ($newPhotoName !== '') {
                    deletePhotoFile($student['photo']);
                    $photoName = $newPhotoName;
                }
            }
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }

        // update database details
        if (!$errors) {
            $update = $pdo->prepare('UPDATE students SET name = ?, email = ?, gender = ?, course = ?, address = ?, cgpa = ?, branch = ?, status = ?, photo = ? WHERE id = ?');
            $update->execute([$name, $email, $gender, $course, $address, $cgpa, $branch, $status, $photoName, $id]);
            setFlash('success', 'Student updated successfully.');
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student | Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="dashboard.php"><i class="bi bi-mortarboard-fill me-2"></i>Student Portal</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm rounded-4 fade-in">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h3 class="fw-bold mb-1">Edit Student</h3>
                                <p class="text-muted mb-0">Update the selected student information and photo.</p>
                            </div>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
                        </div>

                        <?php if ($errors): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= h($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Name</label>
                                    <input class="form-control" id="name" name="name" value="<?= h($student['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= h($student['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="gender">Gender</label>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="Male" <?= $student['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= $student['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= $student['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="course">Course</label>
                                    <input class="form-control" id="course" name="course" value="<?= h($student['course']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="branch">Branch</label>
                                    <input class="form-control" id="branch" name="branch" value="<?= h($student['branch']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="cgpa">CGPA</label>
                                    <input type="number" step="0.01" min="0" max="10" class="form-control" id="cgpa" name="cgpa" value="<?= h($student['cgpa']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="status">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Active" <?= $student['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                        <option value="Inactive" <?= $student['status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="photo">Replace Photo</label>
                                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                    <img src="<?= h(getPhotoUrl($student['photo'])) ?>" alt="Current photo" class="avatar-preview mt-3">
                                    <img id="photoPreview" class="avatar-preview mt-3 d-none" alt="Preview">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required><?= h($student['address']) ?></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Update Student</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
