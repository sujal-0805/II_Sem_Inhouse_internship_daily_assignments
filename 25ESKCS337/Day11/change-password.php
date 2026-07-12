<?php
require_once 'config.php';

// check if user is logged in
require_auth();

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // validation checks
    if (empty($current_password)) {
        $errors[] = "Current password is required.";
    }
    if (empty($new_password)) {
        $errors[] = "New password is required.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long.";
    }
    if ($new_password !== $confirm_password) {
        $errors[] = "New password and confirmation password do not match.";
    }

    if (empty($errors)) {
        try {
            // query user password hash from database
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user && password_verify($current_password, $user['password'])) {
                // make sure new password isn't the same as current
                if (password_verify($new_password, $user['password'])) {
                    $errors[] = "New password cannot be the same as your current password.";
                } else {
                    // hash new password and save it
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update_stmt->execute([$new_hashed_password, $_SESSION['user_id']]);

                    $success_message = "Password updated successfully!";
                }
            } else {
                $errors[] = "Incorrect current password.";
            }
        } catch (PDOException $e) {
            error_log("Password update error: " . $e->getMessage());
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
  <title>Change Password | SecureAuth</title>
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
            <h2 class="fw-bold">Change Password</h2>
            <p class="text-white-50 small">Update your secure login credentials</p>
          </div>

          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
              <i class="bi bi-check-circle-fill me-2"></i>
              <div><?php echo escape($success_message); ?></div>
            </div>
          <?php endif; ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-3" role="alert">
              <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <form action="change-password.php" method="POST" autocomplete="off">
            <div class="mb-3">
              <label for="current_password" class="form-label">Current Password</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-shield-lock"></i></span>
                <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Enter current password">
              </div>
            </div>

            <div class="mb-3">
              <label for="new_password" class="form-label">New Password</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Minimum 8 characters">
              </div>
            </div>

            <div class="mb-4">
              <label for="confirm_password" class="form-label">Confirm New Password</label>
              <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="bi bi-shield-check"></i></span>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Re-enter new password">
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold mb-3">Update Password</button>
          </form>

          <div class="text-center mt-2">
            <p class="mb-0 text-white-50 small"><a href="profile.php" class="text-primary text-decoration-none fw-semibold"><i class="bi bi-arrow-left me-1"></i> Back to Profile</a></p>
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
