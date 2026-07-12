<?php
require_once 'config.php';

// go to profile if user is already logged in
if (is_authenticated()) {
    header("Location: profile.php");
    exit();
}

$errors = [];
$success_message = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // validate email field
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($errors)) {
        // mock email reset response
        // we don't say if email actually exists in db to be secure
        $success_message = "If an account with " . escape($email) . " is registered, a password reset link has been sent. Please check your inbox and spam folder.";
        $email = ''; // reset input
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | SecureAuth</title>
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
            <h2 class="fw-bold">Forgot Password</h2>
            <p class="text-white-50 small">Enter your email and we'll mock-send a reset link</p>
          </div>

          <?php if (!empty($success_message)): ?>
            <div class="alert alert-info border-0 shadow-sm d-flex" role="alert">
              <i class="bi bi-info-circle-fill me-2 mt-1"></i>
              <div><?php echo $success_message; ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm" role="alert">
              <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form action="forgot-password.php" method="POST" autocomplete="off">
            <div class="mb-4">
              <label for="email" class="form-label">Email Address</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo escape($email); ?>" required placeholder="name@example.com">
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold mb-3">Send Reset Instructions</button>
          </form>

          <div class="text-center mt-2">
            <p class="mb-0 text-white-50 small"><a href="login.php" class="text-primary text-decoration-none fw-semibold"><i class="bi bi-arrow-left me-1"></i> Back to Login</a></p>
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
