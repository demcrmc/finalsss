<?php
include 'dbconnection.php';

// Get the posted data
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$gender = $_POST['gender'];
$course = $_POST['course'];
$user_address = $_POST['user_address'];
$birthdate = $_POST['birthdate'];

// Handle file upload
$profile_image = null; // Default value if no file is uploaded

if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
    $target_dir = "image/"; // Directory to store uploaded images
    $target_file = $target_dir . basename($_FILES["profileImage"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the file is an image
    $check = getimagesize($_FILES["profileImage"]["tmp_name"]);
    if ($check === false) {
        echo json_encode(['res' => 'error', 'msg' => 'File is not an image.']);
        exit;
    }

    // Check file size (e.g., 2MB limit)
    if ($_FILES["profileImage"]["size"] > 2 * 1024 * 1024) {
        echo json_encode(['res' => 'error', 'msg' => 'File size must be less than 2MB.']);
        exit;
    }
    // Allow only specific file types
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowed_types)) {
        echo json_encode(['res' => 'error', 'msg' => 'Only JPG, JPEG, PNG, and GIF files are allowed.']);
        exit;
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_file)) {
        $profile_image = $target_file; // Save the file path
    } else {
        echo json_encode(['res' => 'error', 'msg' => 'Error uploading file.']);
        exit;
    }
}

try {
    // Prepare the SQL statement
    $stmt = $connection->prepare("INSERT INTO students (first_name, last_name, email, gender, course, user_address, birthdate, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Execute the statement with the provided data
    $stmt->execute([$first_name, $last_name, $email, $gender, $course, $user_address, $birthdate, $profile_image]);

    // Return success response
    echo json_encode(['res' => 'success']);
} catch (PDOException $e) {
    // Return error response
    echo json_encode(['res' => 'error', 'msg' => 'Database error: ' . $e->getMessage()]);
}
?>