<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_auth_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['status' => 'error', 'message' => 'An error occurred.'];

if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
    $first_name = $_POST['firstname'];
    $middle_name = $_POST['middlename'];
    $last_name = $_POST['lastname'];
    $birth_date = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $id_type = $_POST['id_type'];
    $id_number = $_POST['id_number'];
    $mobile_no = $_POST['mobile_no'];
    $landline = $_POST['landline'];
    $address = $_POST['address'];
    $id_image = '';

    if (isset($_FILES['id_image']) && $_FILES['id_image']['error'] == 0) {
        $id_image = 'uploads/' . basename($_FILES['id_image']['name']);
        move_uploaded_file($_FILES['id_image']['tmp_name'], $id_image);
    }

    $profile_sql = "INSERT INTO users (id, firstname, middlename, lastname, birthdate, gender, id_type, id_number, mobile_no, landline, address, id_image)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE firstname = VALUES(firstname), middlename = VALUES(middlename), lastname = VALUES(lastname), birthdate = VALUES(birthdate), gender = VALUES(gender), id_type = VALUES(id_type), id_number = VALUES(id_number), mobile_no = VALUES(mobile_no), landline = VALUES(landline), address = VALUES(address), id_image = VALUES(id_image)";
    $profile_stmt = $conn->prepare($profile_sql);
    $profile_stmt->bind_param("isssssssssss", $user_id, $first_name, $middle_name, $last_name, $birth_date, $gender, $id_type, $id_number, $mobile_no, $landline, $address, $id_image);

    if ($profile_stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Profile saved successfully.';
    } else {
        $response['message'] = 'Failed to save profile.';
        error_log("Error: " . $profile_stmt->error);
    }

    $profile_stmt->close();
}

echo json_encode($response);
$conn->close();
?>