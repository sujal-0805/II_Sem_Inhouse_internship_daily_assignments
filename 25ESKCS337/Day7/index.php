<?php
session_start();

$errors = [];
$formData = [
    'name' => '',
    'email' => '',
    'gender' => '',
    'course' => '',
    'address' => ''
];

// load errors from redirect session if there are any
if (!empty($_SESSION['form_errors'])) {
    $errors = $_SESSION['form_errors'];
    $formData = $_SESSION['form_data'] ?? $formData;
    // clear session so it doesn't show again on refresh
    unset($_SESSION['form_errors'], $_SESSION['form_data']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Form</title>
    <!-- bootstrap classes for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="mb-4 text-center">Student Registration Form</h2>

                        <!-- show errors if validation failed -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <h5 class="alert-heading">Please fix the following issues:</h5>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- form action goes to confirm.php -->
                        <form action="confirm.php" method="post" enctype="multipart/form-data" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($formData['name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($formData['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Gender</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="male" value="Male" <?php echo ($formData['gender'] === 'Male') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="male">Male</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="female" value="Female" <?php echo ($formData['gender'] === 'Female') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="female">Female</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="other" value="Other" <?php echo ($formData['gender'] === 'Other') ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="other">Other</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="course" class="form-label">Course</label>
                                    <select class="form-select" id="course" name="course" required>
                                        <option value="">Choose a course</option>
                                        <option value="Computer Science" <?php echo ($formData['course'] === 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                                        <option value="Electronics" <?php echo ($formData['course'] === 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                                        <option value="Mechanical" <?php echo ($formData['course'] === 'Mechanical') ? 'selected' : ''; ?>>Mechanical</option>
                                        <option value="Civil" <?php echo ($formData['course'] === 'Civil') ? 'selected' : ''; ?>>Civil</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="4" required><?php echo htmlspecialchars($formData['address'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label for="photo" class="form-label">Photo</label>
                                    <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
                                    <div id="photoPreview" class="mt-3 border rounded p-3 bg-light d-none">
                                        <p class="mb-2 fw-semibold">Preview</p>
                                        <img id="previewImage" class="img-fluid rounded" alt="Selected preview">
                                        <p id="fileName" class="mt-2 mb-0 text-muted"></p>
                                    </div>
                                </div>
                            </div>

                            <!-- submit -->
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">Register</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
