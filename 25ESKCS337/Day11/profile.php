<?php
require_once 'config.php';

// check if user is logged in
require_auth();

$errors = [];
$success_message = '';
$user = null;

// query database to get latest user details
try {
    $stmt = $pdo->prepare("SELECT id, username, email, profile_picture, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // logout and go back if user doesn't exist in table
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Profile load error: " . $e->getMessage());
    die("An error occurred while loading your profile.");
}

// upload profile picture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            $errors[] = "The uploaded file is too large.";
        } elseif ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = "Please select an image file to upload.";
        } else {
            $errors[] = "Error uploading file. Code: " . $file['error'];
        }
    } else {
        // 2mb limit
        $max_size = 2 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            $errors[] = "File size exceeds the 2MB limit.";
        }

        // check mime type of image
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_mimes)) {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.";
        }

        // check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed_exts)) {
            $errors[] = "Invalid file extension.";
        }

        // all validations passed, save the image
        if (empty($errors)) {
            // random filename so they don't overwrite
            $new_filename = bin2hex(random_bytes(16)) . '.' . $ext;
            $upload_dir = 'uploads/';
            $dest_path = $upload_dir . $new_filename;

            // make uploads folder if it is missing
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                // delete old profile picture if they have one
                $old_pic = $user['profile_picture'];
                if (!empty($old_pic)) {
                    $old_pic_path = $upload_dir . $old_pic;
                    if (file_exists($old_pic_path)) {
                        unlink($old_pic_path);
                    }
                }

                // save new picture name in db
                try {
                    $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                    $stmt->execute([$new_filename, $_SESSION['user_id']]);

                    // refresh user data on screen
                    $user['profile_picture'] = $new_filename;
                    $success_message = "Profile picture updated successfully!";
                } catch (PDOException $e) {
                    error_log("Database profile picture update error: " . $e->getMessage());
                    $errors[] = "Database failed to save profile picture info.";
                    // delete file if query failed
                    if (file_exists($dest_path)) {
                        unlink($dest_path);
                    }
                }
            } else {
                $errors[] = "Failed to move uploaded file.";
            }
        }
    }
}

// Set up profile picture path for display
$profile_picture_src = 'default_avatar.svg';
if (!empty($user['profile_picture']) && file_exists('uploads/' . $user['profile_picture'])) {
    $profile_picture_src = 'uploads/' . $user['profile_picture'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile | SecureAuth</title>
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
    .profile-card {
      background: rgba(30, 41, 59, 0.7);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 16px;
    }
    .profile-avatar-large {
      width: 150px;
      height: 150px;
      object-fit: cover;
      border: 4px solid #2563eb;
      box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
    }
    .info-label {
      color: #94a3b8;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .info-value {
      font-size: 1.1rem;
      font-weight: 500;
    }
    .file-input-wrapper {
      position: relative;
      overflow: hidden;
      display: inline-block;
    }
    .file-input-wrapper input[type=file] {
      font-size: 100px;
      position: absolute;
      left: 0;
      top: 0;
      opacity: 0;
      cursor: pointer;
    }
  </style>
</head>
<body>

  <?php include 'navbar.php'; ?>

  <div class="container py-5 my-auto">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        
        <?php if (!empty($success_message)): ?>
          <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div><?php echo escape($success_message); ?></div>
          </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0 ps-3">
              <?php foreach ($errors as $error): ?>
                <li><?php echo escape($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="card profile-card shadow-lg p-4">
          <div class="row align-items-center">
            <!-- Left Side: Avatar Display and Form -->
            <div class="col-md-4 text-center border-end border-secondary pb-4 pb-md-0">
              <img src="<?php echo escape($profile_picture_src); ?>" alt="Avatar" class="rounded-circle profile-avatar-large mb-3">
              <h4 class="fw-bold mb-1"><?php echo escape($user['username']); ?></h4>
              <p class="text-white-50 small mb-3">ID: #<?php echo escape($user['id']); ?></p>

              <!-- Upload Form -->
              <form action="profile.php" method="POST" enctype="multipart/form-data">
                <div class="file-input-wrapper w-100 mb-2">
                  <button type="button" class="btn btn-outline-light btn-sm w-100 py-2"><i class="bi bi-camera-fill me-1"></i> Choose Image</button>
                  <input type="file" name="profile_pic" accept="image/*" onchange="this.form.submit();" required>
                </div>
                <small class="text-white-50 d-block">Max 2MB (JPG, PNG, GIF, WEBP)</small>
              </form>
            </div>

            <!-- Right Side: Profile Info details -->
            <div class="col-md-8 ps-md-4">
              <h3 class="fw-bold mb-4">Account Information</h3>
              
              <div class="row mb-3">
                <div class="col-sm-4">
                  <span class="info-label">Username</span>
                </div>
                <div class="col-sm-8">
                  <span class="info-value text-white"><?php echo escape($user['username']); ?></span>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-sm-4">
                  <span class="info-label">Email Address</span>
                </div>
                <div class="col-sm-8">
                  <span class="info-value text-white"><?php echo escape($user['email']); ?></span>
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-sm-4">
                  <span class="info-label">Member Since</span>
                </div>
                <div class="col-sm-8">
                  <span class="info-value text-white"><?php echo date('F d, Y \a\t g:i A', strtotime($user['created_at'])); ?></span>
                </div>
              </div>

              <div class="border-top border-secondary pt-3 d-flex flex-wrap gap-2">
                <a href="change-password.php" class="btn btn-primary btn-sm px-3"><i class="bi bi-key-fill me-1"></i> Change Password</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-3"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
              </div>
            </div>
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
