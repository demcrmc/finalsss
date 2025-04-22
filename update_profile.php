<?php
session_start();
include 'dbconnection.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

try {
    $email = $_SESSION['email'];

    // Fetch the current user data
    $stmt = $connection->prepare("SELECT first_name, last_name, user_address, birthdate, profile_image FROM user_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    // Use existing values if fields are not provided
    $first_name = $_POST['first_name'] ?? $currentUser['first_name'];
    $last_name = $_POST['last_name'] ?? $currentUser['last_name'];
    $user_address = $_POST['user_address'] ?? $currentUser['user_address'];
    $birthdate = $_POST['birthdate'] ?? $currentUser['birthdate'];
    $profile_image = $currentUser['profile_image'];

    // Handle profile image upload
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($_FILES["profileImage"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_file)) {
            if (!empty($currentUser['profile_image']) && $currentUser['profile_image'] !== 'default-profile.png' && file_exists($currentUser['profile_image'])) {
                unlink($currentUser['profile_image']); // Delete the old image
            }
            $profile_image = $target_file;
        }
    }

    // Update the user's details
    $stmt = $connection->prepare("UPDATE user_table SET first_name = :first_name, last_name = :last_name, user_address = :user_address, birthdate = :birthdate, profile_image = :profile_image WHERE email = :email");
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':user_address', $user_address);
    $stmt->bindParam(':birthdate', $birthdate);
    $stmt->bindParam(':profile_image', $profile_image);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Return the updated user data
    echo json_encode([
        'success' => true,
        'user' => [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_address' => $user_address,
            'birthdate' => $birthdate,
            'profile_image' => $profile_image
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}