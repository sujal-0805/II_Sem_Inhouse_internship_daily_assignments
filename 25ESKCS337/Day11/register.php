<?php
require_once 'config.php';

// if user is already logged in, send them to profile
if (is_authenticated()) {
    header("Location: profile.php");
    exit();
}

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get details from post and trim
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // validation rules
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $errors[] = "Username must be 3-20 characters long and contain only letters, numbers, and underscores.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // check db for duplicate details
    if (empty($errors)) {
        try {
            // is username taken?
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = "Username is already taken.";
            }

            // is email taken?
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Email is already registered.";
            }

            // no errors, insert student
            if (empty($errors)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashed_password]);

                $_SESSION['flash_success'] = "Registration successful! You can now log in.";
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors[] = "An unexpected error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | SecureAuth</title>
  <!-- Bootstrap 5 CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts: Outfit -->
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Outfit', sans-serif;
      background-color: #0f172a;
      color: #f1f5f9;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .auth-card {
      background: rgba(30, 41, 59, 0.7);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
    }
    .form-control {
      background-color: #0f172a;
      border: 1px solid #334155;
      color: #f1f5f9;
      border-radius: 8px;
    }
    .form-control:focus {
      background-color: #0f172a;
      border-color: #3b82f6;
      color: #f1f5f9;
      box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }
    .form-label {
      color: #94a3b8;
    }
    .btn-primary {
      background-color: #2563eb;
      border: none;
      transition: background-color 0.2s ease, transform 0.1s ease;
    }
    .btn-primary:hover {
      background-color: #1d4ed8;
      transform: translateY(-1px);
    }
    .btn-primary:active {
      transform: translateY(1px);
    }
  </style>
</head>
<body>

  <?php include 'navbar.php'; ?>

  <div class="container my-auto py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="card auth-card shadow-lg p-4">
          <div class="text-center mb-4">
            <h2 class="fw-bold">Create Account</h2>
            <p class="text-white-50 small">Get started with our secure login system</p>
          </div>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
              <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form action="register.php" method="POST" autocomplete="off">
            <div class="mb-3">
              <label for="username" class="form-label">Username</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo escape($username); ?>" required placeholder="e.g. john_doe">
              </div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo escape($email); ?>" required placeholder="name@example.com">
              </div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" required placeholder="Min 8 characters">
              </div>
            </div>

            <div class="mb-4">
              <label for="confirm_password" class="form-label">Confirm Password</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-shield-check"></i></span>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Re-enter password">
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold mb-3">Register</button>
          </form>

          <div class="text-center mt-2">
            <p class="mb-0 text-white-50 small">Already have an account? <a href="login.php" class="text-primary text-decoration-none fw-semibold">Login here</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="text-center py-3 text-white-50 mt-auto border-top border-secondary bg-dark small">
    <div class="container">
      &copy; <?php echo date('Y'); ?> SecureAuth System. Built with security best practices.
    </div>
  </footer>

  <!-- Bootstrap 5 JS Bundle CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
