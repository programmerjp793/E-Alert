<?php
include 'db_connection.php';

$sql = "SELECT DISTINCT username FROM responder_system";
$result = $conn->query($sql);

$usernames = [];
while ($row = $result->fetch_assoc()) {
    $usernames[] = $row['username'];
}

echo json_encode($usernames);
?>