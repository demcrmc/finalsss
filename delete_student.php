<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'dbconnection.php'; // Include your database connection

// Check if the required data is provided in the request
if (isset($_POST['id'])) {
    $student_id = $_POST['id'];

    try {
        // Fetch the current profile image from the database
        $stmt = $connection->prepare("SELECT profile_image FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $current_image = $stmt->fetchColumn();

        // Delete the student record
        $stmt = $connection->prepare("DELETE FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);

        // Check if the delete was successful
        if ($stmt->rowCount() > 0) {
            // If the student was deleted, delete the associated image file
            if ($current_image && file_exists($current_image)) {
                unlink($current_image); // Delete the old image
            }
            echo json_encode(['res' => 'success']);
        } else {
            echo json_encode(['res' => 'error', 'msg' => 'No student found or already deleted.']);
        }
    } catch (PDOException $e) {
        // Return error response
        echo json_encode(['res' => 'error', 'msg' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Return error response if no student ID was provided
    echo json_encode(['res' => 'error', 'msg' => 'No student ID provided.']);
}
?>