<?php
$servername = "localhost";  // Change to your database server (e.g., 127.0.0.1 or your hosting server)
$username = "root";         // Your MySQL username (default is "root" in XAMPP)
$password = "";             // Your MySQL password (leave blank if using XAMPP)
$database = "user_auth_db"; // The database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
