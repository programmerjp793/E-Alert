<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agency_id = intval($_POST['agency_id']);
    $current_status = intval($_POST['current_status']);
    $new_status = $current_status ? 0 : 1;

    $stmt = $conn->prepare("UPDATE agencies SET available = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $agency_id);

    if ($stmt->execute()) {
        echo $new_status;  // Output new status: 1 = Available, 0 = Unavailable
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}