<?php
require 'vendor/autoload.php'; // Load PHPMailer
require 'dbconnection.php'; // Include your database connection file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input from the form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $user_address = $_POST['user_address'];
    $birthdate = $_POST['birthdate'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $verification_code = bin2hex(random_bytes(16)); // Generate a random verification code

    // Handle file upload
    $profile_image = null; // Default value if no file is uploaded
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
        $target_dir = "uploads/"; // Directory to store uploaded images

        // Check if the uploads directory exists, if not, create it
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory with proper permissions
        }

        $target_file = $target_dir . basename($_FILES["profileImage"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES["profileImage"]["tmp_name"]);
        if ($check === false) {
            echo "Error: File is not an image.";
            exit;
        }

        // Check file size (e.g., 2MB limit)
        if ($_FILES["profileImage"]["size"] > 2 * 1024 * 1024) {
            echo "Error: File size must be less than 2MB.";
            exit;
        }

        // Allow only specific file types
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            echo "Error: Only JPG, JPEG, PNG, and GIF files are allowed.";
            exit;
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_file)) {
            $profile_image = $target_file; 
        } else {
            echo "Error: There was an error uploading your file.";
            exit;
        }
    }

    // Insert user into the database
    $stmt = $connection->prepare("INSERT INTO user_table (first_name, last_name, user_address, birthdate, email, user_password, verification_code, profile_image) VALUES (:first_name, :last_name, :user_address, :birthdate, :email, :user_password, :verification_code, :profile_image)");
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':user_address', $user_address);
    $stmt->bindParam(':birthdate', $birthdate);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':user_password', $password);
    $stmt->bindParam(':verification_code', $verification_code);
    $stmt->bindParam(':profile_image', $profile_image);

    if ($stmt->execute()) {
        // Send verification email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; 
            $mail->SMTPAuth = true;
            $mail->Username = 'jamesamit001@gmail.com'; 
            $mail->Password = 'gsiz ghjy csus yttv'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('jamesamit001@gmail.com', 'Mailer');
            $mail->addAddress($email); 

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Account Verification';
            $mail->Body = 'Click the link to verify your account: <a href="http://localhost/withdashboard_cosep-main/verify.php?code=' . $verification_code . '">Verify Account</a>';

            $mail->send();
            echo 'Registration successful! Please check your email to verify your account.';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: Could not register user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>User Registration</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .text-center {
            margin-top: 20px;
        }
    </style>
    <style>
        /* General Dark Mode Styles */
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        .sidebar.dark-mode {
            background-color: #23272b;
        }

        .sidebar.dark-mode .menu a {
            color: #ffffff;
        }

        .sidebar.dark-mode .menu a:hover,
        .sidebar.dark-mode .menu a.active {
            background-color: #3a3f44;
        }

        .content.dark-mode {
            background-color: #1e1e1e;
            color: #ffffff;
        }

        .real-time-clock-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff; /* Adjust text color for better visibility */
        }

        .info-box-container .system-health-box.dark-mode,
        .info-box-container .verified-users-box.dark-mode,
        .info-box-container .new-box.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .table-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .table.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .chart-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h2 class="text-center">Create an Account</h2>
        <form method="post" action="user_register.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="user_address">Address</label>
                <input type="text" class="form-control" id="user_address" name="user_address" required>
            </div>
            <div class="form-group">
                <label for="birthdate">Birthdate</label>
                <input type="date" class="form-control" id="birthdate" name="birthdate" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="profileImage">Profile Picture</label>
                <input type="file" class="form-control" id="profileImage" name="profileImage" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
    </div>

</body>

</html>