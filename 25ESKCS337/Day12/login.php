<?php
require_once 'config.php';

// go to dashboard if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
// check POST submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    } else {
        $pdo = getDb();
        // validate credentials against DB
        $stmt = $pdo->prepare('SELECT id, full_name, password_hash FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            setFlash('success', 'Signed in successfully.');
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
    <title>Login | Student Management System</title>
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
                            <div class="auth-icon mb-3"><i class="bi bi-shield-lock-fill"></i></div>
                            <h2 class="fw-bold mb-2">Welcome back</h2>
                            <p class="text-muted mb-0">Please sign in to continue managing student records.</p>
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
                                <label class="form-label" for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted">Need an account?</span>
                            <a href="register.php" class="ms-1 text-decoration-none">Create one</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
