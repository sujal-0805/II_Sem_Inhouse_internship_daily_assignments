<?php
session_start();

// only allow post requests, otherwise redirect to index
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$errors = [];

// get details from post and clean them
$full_name = isset($_POST['full_name']) ? htmlspecialchars(trim($_POST['full_name'])) : '';
$email     = isset($_POST['email']) ? htmlspecialchars(trim($_POST['email'])) : '';
$phone     = isset($_POST['phone']) ? htmlspecialchars(trim($_POST['phone'])) : '';
$gender    = isset($_POST['gender']) ? htmlspecialchars(trim($_POST['gender'])) : '';
$course    = isset($_POST['course']) ? htmlspecialchars(trim($_POST['course'])) : '';
$address   = isset($_POST['address']) ? htmlspecialchars(trim($_POST['address'])) : '';

// validate the input details
if (empty($full_name) || empty($email) || empty($phone) || empty($gender) || empty($course) || empty($address)) {
    $errors[] = 'All fields are required. Please fill out the entire form.';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'The email address format is invalid.';
}

// do photo uploading stuff
$photo_path = '';
if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] === UPLOAD_ERR_OK) {
    // print_r($_FILES['student_photo']); // debug
    
    $file_tmp_path = $_FILES['student_photo']['tmp_name'];
    $file_name     = $_FILES['student_photo']['name'];
    $file_size     = $_FILES['student_photo']['size'];
    $file_type     = $_FILES['student_photo']['type'];
    
    $file_parts = explode('.', $file_name);
    $file_ext   = strtolower(end($file_parts));

    // allowed types
    $allowed_extensions = ['jpg', 'jpeg', 'png'];
    $allowed_mimetypes  = ['image/jpeg', 'image/png', 'image/jpg'];

    // check photo extension
    if (!in_array($file_ext, $allowed_extensions)) {
        $errors[] = 'Invalid file extension. Only JPG, JPEG, and PNG files are allowed.';
    }

    // check mime type
    if (function_exists('finfo_open')) {
        $finfo     = finfo_open(FILEINFO_MIME_TYPE);
        $real_mime = finfo_file($finfo, $file_tmp_path);
        finfo_close($finfo);
        if (!in_array($real_mime, $allowed_mimetypes)) {
            $errors[] = 'Invalid image content. Please upload a real JPG or PNG image.';
        }
    } else {
        if (!in_array($file_type, $allowed_mimetypes)) {
            $errors[] = 'Invalid file type. Only JPG, JPEG, and PNG images are allowed.';
        }
    }

    // 2MB size limit (2 * 1024 * 1024 bytes)
    $max_size = 2 * 1024 * 1024;
    if ($file_size > $max_size) {
        $errors[] = 'Uploaded image size exceeds the 2MB limit.';
    }

    // move to uploads directory
    if (empty($errors)) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // rename file randomly so there's no duplicate name errors
        $new_file_name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
        $dest_path     = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            $photo_path = $dest_path;
        } else {
            $errors[] = 'An error occurred while saving the uploaded photo.';
        }
    }
} else {
    $errors[] = 'Please select and upload a student profile photo.';
}

// redirect back if there are errors
if (!empty($errors)) {
    // clean up uploaded photo if any
    if (!empty($photo_path) && file_exists($photo_path)) {
        unlink($photo_path);
    }
    
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: index.php');
    exit();
}

// save into database file (json)
$json_db_path = 'data.json';
$registrations = [];

if (file_exists($json_db_path)) {
    $json_content = file_get_contents($json_db_path);
    $registrations = json_decode($json_content, true);
    if (!is_array($registrations)) {
        $registrations = [];
    }
}

$new_registration = [
    'full_name' => $full_name,
    'email'     => $email,
    'phone'     => $phone,
    'gender'    => $gender,
    'course'    => $course,
    'address'   => $address,
    'photo'     => $photo_path,
    'timestamp' => date('Y-m-d H:i:s')
];

$registrations[] = $new_registration;
file_put_contents($json_db_path, json_encode($registrations, JSON_PRETTY_PRINT));
// echo "Saved successfully!";

// choose badge colors
$gender_badge_class = 'other';
if (strtolower($gender) === 'male') {
    $gender_badge_class = 'male';
} elseif (strtolower($gender) === 'female') {
    $gender_badge_class = 'female';
}

$course_slug = strtolower(str_replace(' ', '-', $course));
$course_badge_class = 'other';
if ($course_slug === 'computer-science' || $course_slug === 'business' || $course_slug === 'design' || $course_slug === 'engineering') {
    $course_badge_class = $course_slug;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Registration Confirmation details.">
    <title>Registration Confirmed - Student Profile</title>
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

            <!-- Confirmation Card -->
            <div class="card card-registration">
                
                <!-- Card Header -->
                <div class="gradient-header">
                    <h2><i class="fa-solid fa-circle-check me-2"></i>Registration Success!</h2>
                    <p>Your profile has been registered and saved successfully.</p>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    
                    <!-- photo preview -->
                    <div class="confirmation-photo-wrapper">
                        <img src="<?php echo htmlspecialchars($photo_path); ?>" alt="Uploaded Student Photo" class="confirmation-photo">
                    </div>
                    
                    <h4 class="text-center mb-4 fw-bold text-indigo">Student Profile Details</h4>
                    
                    <!-- Summary List -->
                    <div class="summary-list mb-4">
                        
                        <!-- Name -->
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa-solid fa-user"></i> Full Name
                            </span>
                            <span class="summary-val fw-bold">
                                <?php echo htmlspecialchars($full_name); ?>
                            </span>
                        </div>

                        <!-- Email -->
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa-solid fa-envelope"></i> Email Address
                            </span>
                            <span class="summary-val">
                                <?php echo htmlspecialchars($email); ?>
                            </span>
                        </div>

                        <!-- Phone -->
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa-solid fa-phone"></i> Phone Number
                            </span>
                            <span class="summary-val">
                                <?php echo htmlspecialchars($phone); ?>
                            </span>
                        </div>

                        <!-- Gender -->
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa-solid fa-venus-mars"></i> Gender
                            </span>
                            <span class="summary-val">
                                <span class="badge-gender <?php echo $gender_badge_class; ?>">
                                    <?php if ($gender_badge_class === 'male'): ?>
                                        <i class="fa-solid fa-mars me-1"></i>
                                    <?php elseif ($gender_badge_class === 'female'): ?>
                                        <i class="fa-solid fa-venus me-1"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-genderless me-1"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($gender); ?>
                                </span>
                            </span>
                        </div>

                        <!-- Course -->
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa-solid fa-graduation-cap"></i> Academic Course
                            </span>
                            <span class="summary-val">
                                <span class="badge-course <?php echo $course_badge_class; ?>">
                                    <i class="fa-solid fa-book-open me-1"></i>
                                    <?php echo htmlspecialchars($course); ?>
                                </span>
                            </span>
                        </div>

                        <!-- Address -->
                        <div class="summary-item">
                            <span class="summary-label">
                                <i class="fa-solid fa-map-marker-alt"></i> Address
                            </span>
                            <span class="summary-val text-wrap text-end">
                                <?php echo nl2br(htmlspecialchars($address)); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Return Buttons -->
                    <div class="text-center mt-5">
                        <a href="index.php" class="btn btn-submit">
                            <i class="fa-solid fa-arrow-left me-2"></i>Register Another Student
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
