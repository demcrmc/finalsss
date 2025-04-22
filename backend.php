<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

include 'dbconnection.php';

try {
    // Fetch the logged-in user's details
    $email = $_SESSION['email'];
    $stmt = $connection->prepare("SELECT first_name, last_name, user_address, birthdate, email, profile_image FROM user_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit();
    }

    // Query to fetch all users
    $stmt = $connection->prepare("SELECT id AS student_id, first_name, last_name, email, is_verified, profile_image FROM user_table");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Query to count verified users
    $verifiedStmt = $connection->prepare("SELECT COUNT(*) AS verified_count FROM user_table WHERE is_verified = 1");
    $verifiedStmt->execute();
    $verifiedCount = $verifiedStmt->fetch(PDO::FETCH_ASSOC)['verified_count'];

    // Determine system health based on verified users
    $systemHealth = $verifiedCount > 0 ? 'Good' : 'Bad';

    // Return all data as JSON
    echo json_encode([
        'user' => $user,
        'users' => $users,
        'verifiedCount' => $verifiedCount,
        'systemHealth' => $systemHealth
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
