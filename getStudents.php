<?php
include 'dbconnection.php'; // Include your database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the content type to JSON
header('Content-Type: application/json');

// Check if an ID is provided in the request
if (isset($_GET['id'])) {
    // Get the student ID from the request
    $student_id = $_GET['id'];

    try {
        // Prepare the SQL statement to fetch the student data
        $stmt = $connection->prepare("SELECT student_id, first_name, last_name, email, gender, course, user_address, birthdate, profile_image FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);
        
        // Fetch the student data
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if student data was found
        if ($student) {
            // Return the data as JSON
            echo json_encode($student);
        } else {
            // Return error response if no student found
            echo json_encode(['res' => 'error', 'msg' => 'No student found with that ID.']);
        }
    } catch (PDOException $e) {
        // Return error response
        echo json_encode(['res' => 'error', 'msg' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // If no ID is provided, fetch all students
    try {
        $query = "SELECT student_id, first_name, last_name, email, gender, course, user_address, 
                  EXTRACT(YEAR FROM CURRENT_DATE) - EXTRACT(YEAR FROM birthdate) AS age, 
                  profile_image 
                  FROM students";

        $stmt = $connection->prepare($query);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the data as JSON
        echo json_encode($students);
    } catch (PDOException $e) {
        // Return error response
        echo json_encode(['res' => 'error', 'msg' => 'Database error: ' . $e->getMessage()]);
    }
}
?>