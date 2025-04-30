<?php
include 'db_connection.php';

$dates = [];

// Fetch distinct dates
$date_sql = "SELECT DISTINCT DATE(date) as date FROM reports ORDER BY date";
$date_result = $conn->query($date_sql);
while ($row = $date_result->fetch_assoc()) {
    $dates[] = $row['date'];
}

echo json_encode(['dates' => $dates]);
?>