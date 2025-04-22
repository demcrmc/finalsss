<?php

require 'dbconnection.php'; // Include your database connection file

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Prepare the SQL statement to update the user's verification status
    $stmt = $connection->prepare("UPDATE user_table SET is_verified = 1, verification_code = NULL WHERE verification_code = :code");
    $stmt->bindParam(':code', $code);

    if ($stmt->execute() && $stmt->rowCount() > 0) {
        // Redirect to login page with a success message
        header("Location: login.php?message=verified");
        exit();
    } else {
        // Invalid or expired verification code
        echo "Invalid or expired verification code.";
    }
} else {
    // No verification code provided
    echo "No verification code provided.";
}
?>