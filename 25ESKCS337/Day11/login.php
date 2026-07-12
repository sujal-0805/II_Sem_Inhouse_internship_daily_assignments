<?php
require_once 'config.php';

// if user is already logged in, redirect them to profile
if (is_authenticated()) {
    header("Location: profile.php");
    exit();
}

$errors = [];
$identity = ''; // stores username or email

// show flash success/error messages if set
$success_message = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$error_message = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    // validation
    if (empty($identity)) {
        $errors[] = "Username or Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            // search user in db by username or email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$identity, $identity]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // login success! regenerate session id
                session_regenerate_id(true);

                // store info in session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header("Location: profile.php");
                exit();
            } else {
                $errors[] = "Invalid username/email or password.";
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
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
  <title>Login | SecureAuth</title>
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
            <h2 class="fw-bold">Welcome Back</h2>
            <p class="text-white-50 small">Sign in to your secure account</p>
          </div>

          <!-- Flash success message -->
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              <div><?php echo escape($success_message); ?></div>
            </div>
          <?php endif; ?>

          <!-- Flash error message -->
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <div><?php echo escape($error_message); ?></div>
            </div>
          <?php endif; ?>

          <!-- Form processing errors -->
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
              <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form action="login.php" method="POST" autocomplete="off">
            <div class="mb-3">
              <label for="identity" class="form-label">Username or Email</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" id="identity" name="identity" value="<?php echo escape($identity); ?>" required placeholder="Enter username or email">
              </div>
            </div>

            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label mb-0">Password</label>
                <a href="forgot-password.php" class="text-primary text-decoration-none small fw-semibold">Forgot Password?</a>
              </div>
              <div class="input-group mt-2">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold mb-3">Sign In</button>
          </form>

          <div class="text-center mt-2">
            <p class="mb-0 text-white-50 small">Don't have an account? <a href="register.php" class="text-primary text-decoration-none fw-semibold">Register here</a></p>
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
