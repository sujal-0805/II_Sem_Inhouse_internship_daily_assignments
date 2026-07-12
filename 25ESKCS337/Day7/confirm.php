<?php
session_start();

// sanitise inputs
function sanitizeInput($value)
{
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}

$errors = [];
$formData = [
    'name' => '',
    'email' => '',
    'gender' => '',
    'course' => '',
    'address' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get form data
    $formData['name'] = sanitizeInput($_POST['name'] ?? '');
    $formData['email'] = sanitizeInput($_POST['email'] ?? '');
    $formData['gender'] = sanitizeInput($_POST['gender'] ?? '');
    $formData['course'] = sanitizeInput($_POST['course'] ?? '');
    $formData['address'] = sanitizeInput($_POST['address'] ?? '');

    // check name only has letters and spaces
    if ($formData['name'] === '' || !preg_match('/^[A-Za-z ]+$/', $formData['name'])) {
        $errors[] = 'Name is required and must contain only letters and spaces.';
    }

    // check email formatting
    if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    // address needs to be long enough
    if (strlen(trim($_POST['address'] ?? '')) < 10) {
        $errors[] = 'Address must be at least 10 characters long.';
    }

    // gender and course can't be empty
    if ($formData['gender'] === '') {
        $errors[] = 'Gender is required.';
    }

    if ($formData['course'] === '') {
        $errors[] = 'Course is required.';
    }

    $photoName = '';
    if (!empty($_FILES['photo']['name'])) {
        $photoName = basename($_FILES['photo']['name']);
    }

    // if we have errors, redirect back to index.php with the errors
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $formData;
        // header redirect
        header('Location: index.php');
        exit;
    }
} else {
    // go back if someone tries to access directly
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="alert alert-success" role="alert">
                            <h2 class="h4 mb-2">Registration Successful</h2>
                            <p class="mb-0">Your student details were received successfully.</p>
                        </div>

                        <!-- print details -->
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Name:</strong> <?php echo $formData['name']; ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?php echo $formData['email']; ?></li>
                            <li class="list-group-item"><strong>Gender:</strong> <?php echo $formData['gender']; ?></li>
                            <li class="list-group-item"><strong>Course:</strong> <?php echo $formData['course']; ?></li>
                            <li class="list-group-item"><strong>Address:</strong> <?php echo $formData['address']; ?></li>
                            <li class="list-group-item"><strong>Photo:</strong> <?php echo $photoName !== '' ? htmlspecialchars($photoName, ENT_QUOTES, 'UTF-8') : 'No file selected'; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
