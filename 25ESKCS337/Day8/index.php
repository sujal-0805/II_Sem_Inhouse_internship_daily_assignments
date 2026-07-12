<?php
session_start();
$error = isset($_SESSION['error']) ? $_SESSION['error'] : (isset($_GET['error']) ? $_GET['error'] : '');
$success = isset($_SESSION['success']) ? $_SESSION['success'] : (isset($_GET['success']) ? $_GET['success'] : '');

// clear the success/error messages so they don't show twice
unset($_SESSION['error']);
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Student Registration Confirmation System.">
    <title>Student Registration System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <div class="row justify-content-center animate-fade-in">
        <div class="col-lg-8 col-md-10">
            
            <!-- error alerts -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- signup form card -->
            <div class="card card-registration">
                <!-- gradient header -->
                <div class="gradient-header">
                    <h2><i class="fa-solid fa-user-graduate me-2"></i>Student Registration</h2>
                    <p>Provide your accurate details below to create your student profile.</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <form action="confirm.php" method="POST" enctype="multipart/form-data" id="registrationForm" class="needs-validation" novalidate>
                        
                        <!-- photo preview -->
                        <div class="photo-preview-wrapper">
                            <div class="photo-preview-container" title="Click to upload profile photo">
                                <i class="fa-solid fa-user default-avatar" id="default_avatar"></i>
                                <img src="" alt="Student Preview" id="photo_preview" style="display: none;">
                                <div class="upload-overlay" id="upload_overlay">
                                    <i class="fa-solid fa-camera"></i>
                                    <span>Upload</span>
                                </div>
                            </div>
                            <span class="text-muted small mt-2">Live Photo Preview</span>
                        </div>

                        <div class="row g-4">
                            <!-- student name input -->
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">
                                    <i class="fa-solid fa-user"></i> Full Name
                                </label>
                                <input type="text" class="form-control" id="full_name" name="full_name" placeholder="John Doe" required>
                                <div class="invalid-feedback">Please enter your full name.</div>
                            </div>

                            <!-- email input -->
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    <i class="fa-solid fa-envelope"></i> Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="john.doe@example.com" required>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>

                            <!-- phone number -->
                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    <i class="fa-solid fa-phone"></i> Phone Number
                                </label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="123-456-7890" required>
                                <div class="invalid-feedback">Please enter your phone number.</div>
                            </div>

                            <!-- course list dropdown -->
                            <div class="col-md-6">
                                <label for="course" class="form-label">
                                    <i class="fa-solid fa-graduation-cap"></i> Academic Course
                                </label>
                                <select class="form-select" id="course" name="course" required>
                                    <option value="" selected disabled>Select your course</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Business">Business Administration</option>
                                    <option value="Design">Digital Design</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Other">Other / General</option>
                                </select>
                                <div class="invalid-feedback">Please select a course.</div>
                            </div>

                            <!-- select gender -->
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fa-solid fa-venus-mars"></i> Gender
                                </label>
                                <div class="gender-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_male" value="Male" required>
                                        <label class="form-check-label" for="gender_male">Male</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_female" value="Female">
                                        <label class="form-check-label" for="gender_female">Female</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_other" value="Other">
                                        <label class="form-check-label" for="gender_other">Other</label>
                                    </div>
                                </div>
                            </div>

                            <!-- address -->
                            <div class="col-12">
                                <label for="address" class="form-label">
                                    <i class="fa-solid fa-map-marker-alt"></i> Address
                                </label>
                                <textarea class="form-control" id="address" name="address" placeholder="Enter your full street address..." required></textarea>
                                <div class="invalid-feedback">Please enter your address.</div>
                            </div>

                            <!-- upload photo input -->
                            <div class="col-12">
                                <label for="student_photo" class="form-label">
                                    <i class="fa-solid fa-image"></i> Upload Student Photo
                                </label>
                                <input type="file" class="form-control" id="student_photo" name="student_photo" accept="image/jpeg, image/png" required>
                                <div class="invalid-feedback">Please upload a valid profile image (JPG or PNG, max 2MB).</div>
                                <div class="form-text mt-1 text-muted">Supported formats: JPG, JPEG, PNG. Max size: 2MB.</div>
                            </div>

                            <!-- buttons -->
                            <div class="col-12 mt-5 text-end">
                                <button type="reset" class="btn btn-secondary-custom me-2"><i class="fa-solid fa-rotate-left me-2"></i>Reset</button>
                                <button type="submit" class="btn btn-submit"><i class="fa-solid fa-paper-plane me-2"></i>Register Profile</button>
                            </div>

                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script src="script.js"></script>
</body>
</html>
