<?php
session_start();
include 'db_connection.php';

if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];

    // Set user as inactive
    $update_active_sql = "UPDATE users SET active = 0 WHERE id = ?";
    $update_active_stmt = $conn->prepare($update_active_sql);
    $update_active_stmt->bind_param("i", $user_id);
    $update_active_stmt->execute();
    $update_active_stmt->close();

    // Destroy session
    session_unset();
    session_destroy();
}

// Redirect to login page
header("Location: E-Alert-Login.php");
exit();
?>