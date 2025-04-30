<?php
include 'db_connection.php';

$id = intval($_GET['id']);  // Ensure ID is an integer
$sql = "DELETE FROM content WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: content_management.php");
    exit();
} else {
    echo "Error deleting content: " . $stmt->error;
}
?>