<?php
$servername = "localhost"; // Your database server
$username = "root";         // Your database username
$password = "";             // Your database password
$dbname = "user_database";     // Your database name

try {
    // Create the PDO connection
    $connection = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);

    // Set the error mode to exception for better error handling
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $th) {
    // If connection fails, catch the exception and display an error
    die(json_encode(['error' => "Database Error: " . $th->getMessage()]));
}
?>