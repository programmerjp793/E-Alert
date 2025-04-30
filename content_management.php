<?php
include 'db_connection.php';

// Debugging: Check if connected
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
} else {
    echo "<p style='color: green;'>Database connected successfully!</p>";
}

// Debugging: Check if table exists
$sql = "SELECT * FROM content";
$result = $conn->query($sql);

if (!$result) {
    die("<p style='color: red;'>Query failed: " . $conn->error . "</p>");
}

// Debugging: Check if content exists
if ($result->num_rows == 0) {
    echo "<p style='color: red;'>No content found in the database!</p>";
} else {
    echo "<p style='color: green;'>Content fetched successfully!</p>";
}
?>