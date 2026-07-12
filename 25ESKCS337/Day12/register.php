<?php
require_once 'config.php';

// send to dashboard if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
// check POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // make sure inputs are clean and valid
    if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    } else {
        $pdo = getDb();
        // search email in database
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'An account already exists for that email.';
        } else {
            // hash user password and save
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
            $insert->execute([$fullName, $email, $passwordHash]);

            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $fullName;
            setFlash('success', 'Registration successful. Welcome aboard!');
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
    <title>Register | Student Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-xl-5">
                <div class="card shadow-lg border-0 rounded-4 fade-in">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <div class="auth-icon mb-3"><i class="bi bi-person-plus-fill"></i></div>
                            <h2 class="fw-bold mb-2">Create your account</h2>
                            <p class="text-muted mb-0">Start managing student records with confidence.</p>
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

                        <form method="post" novalidate>
                            <div class="mb-3">
                                <label class="form-label" for="full_name">Full Name</label>
                                <input class="form-control" id="full_name" name="full_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="confirm_password">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Register</button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted">Already have an account?</span>
                            <a href="login.php" class="ms-1 text-decoration-none">Sign in</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
