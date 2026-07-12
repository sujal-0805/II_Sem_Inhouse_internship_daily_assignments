<?php
require 'config.php';

$message = '';

// check if post request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get form variables
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $photoName = '';

    // check if they are all filled
    if ($name === '' || $email === '' || $gender === '' || $course === '' || $address === '') {
        $message = 'Please fill in all required fields.';
    } else {
        // photo upload stuff
        if (!empty($_FILES['photo']['name'])) {
            $uploadDir = __DIR__ . '/uploads/';
            // make folder if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            // generate a unique name
            $photoName = 'photo_' . uniqid() . '.' . $fileExtension;
            $targetPath = $uploadDir . $photoName;

            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                $photoName = basename($targetPath);
            } else {
                $message = 'Photo upload failed.';
            }
        }

        // check if message is empty before saving
        if ($message === '') {
            // insert to db
            $stmt = $pdo->prepare('INSERT INTO students (name, email, gender, course, address, photo) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $gender, $course, $address, $photoName]);
            $message = 'Student registered successfully.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <!-- bootstrap styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Student Registration</h2>
                        
                        <!-- message alert -->
                        <?php if ($message !== ''): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
                        <?php endif; ?>
                        
                        <form method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Course</label>
                                    <input type="text" name="course" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Photo</label>
                                    <input type="file" name="photo" class="form-control">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Register Student</button>
                                    <a href="admin.php" class="btn btn-outline-secondary ms-2">View Records</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
