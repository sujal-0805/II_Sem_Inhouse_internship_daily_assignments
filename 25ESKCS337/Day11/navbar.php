<?php
require_once 'config.php';

$current_page = basename($_SERVER['PHP_SELF']);

$navbar_username = '';
$navbar_user_avatar = 'default_avatar.svg';

if (is_authenticated()) {
    try {
        $stmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $navbar_user = $stmt->fetch();
        if ($navbar_user) {
            $navbar_username = $navbar_user['username'];
            if (!empty($navbar_user['profile_picture']) && file_exists('uploads/' . $navbar_user['profile_picture'])) {
                $navbar_user_avatar = 'uploads/' . $navbar_user['profile_picture'];
            }
        }
    } catch (PDOException $e) {
        $navbar_username = $_SESSION['username'] ?? 'User';
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary shadow-sm py-2">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center fw-bold text-uppercase tracking-wider text-white" href="profile.php">
      <span class="text-primary me-2"><i class="bi bi-shield-lock-fill"></i></span>SecureAuth
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!-- Optional left aligned links -->
      </ul>
      <div class="d-flex align-items-center">
        <?php if (is_authenticated()): ?>
          <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center text-white cursor-pointer" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="<?php echo escape($navbar_user_avatar); ?>" alt="Avatar" class="rounded-circle border border-2 border-primary me-2" style="width: 38px; height: 38px; object-fit: cover;">
              <span class="fw-semibold"><?php echo escape($navbar_username); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border border-secondary mt-2 bg-dark" aria-labelledby="navbarDropdown">
              <li>
                <a class="dropdown-item text-white-50 d-flex align-items-center <?php echo $current_page == 'profile.php' ? 'active text-white' : ''; ?>" href="profile.php">
                  <i class="bi bi-person-fill me-2"></i> Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item text-white-50 d-flex align-items-center <?php echo $current_page == 'change-password.php' ? 'active text-white' : ''; ?>" href="change-password.php">
                  <i class="bi bi-key-fill me-2"></i> Change Password
                </a>
              </li>
              <li><hr class="dropdown-divider border-secondary"></li>
              <li>
                <a class="dropdown-item text-danger d-flex align-items-center" href="logout.php">
                  <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <a class="btn btn-outline-light me-2 btn-sm px-3" href="login.php">Login</a>
          <a class="btn btn-primary btn-sm px-3" href="register.php">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<style>
.dropdown-item:hover {
    background-color: #343a40 !important;
    color: #fff !important;
}
.dropdown-item.active {
    background-color: #0d6efd !important;
    color: #fff !important;
}
</style>
