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

// Fetch the logged-in user's username, role, mobile_no, and address
$user_username = '';
$user_role = '';
$mobile_no = '';
$address = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username, role, mobile_no, address FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_stmt->bind_result($user_username, $user_role, $mobile_no, $address);
    $user_stmt->fetch();
    $user_stmt->close();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_report = $_POST['subject_report'];
    $emergency_type = $_POST['emergency_type'];
    $location = $_POST['location'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $client_inquiry = $_POST['client_inquiry'];
    $photo_attachment = '';

    // Handle file upload
    if (isset($_FILES['photo_attachment']) && $_FILES['photo_attachment']['error'] == 0) {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES["photo_attachment"]["name"], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.'); window.location.href = 'E-Alert-Inquiry.php';</script>";
            exit;
        }

        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["photo_attachment"]["name"]);
        if (move_uploaded_file($_FILES["photo_attachment"]["tmp_name"], $target_file)) {
            $photo_attachment = $target_file;
        }
    }

    // Insert data into inquiry_form table
    $insert_sql = "INSERT INTO inquiry_form (username, address, subject_report, emergency_type, client_inquiry, photo_attachment) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssssss", $user_username, $address, $subject_report, $emergency_type, $client_inquiry, $photo_attachment);
    if ($insert_stmt->execute()) {
        // Get the last inserted ID
        $id = $insert_stmt->insert_id;

        // Insert data into google_maps_api table
        $maps_sql = "INSERT INTO google_maps_api (id, location, latitude, longitude) VALUES (?, ?, ?, ?)";
        $maps_stmt = $conn->prepare($maps_sql);
        $maps_stmt->bind_param("isdd", $id, $location, $latitude, $longitude);
        $maps_stmt->execute();
        $maps_stmt->close();

        // Insert data into reports table
        $status = 'pending'; // Default status
        $reports_sql = "INSERT INTO reports (id, username, emergency_type, date, status, subject_report) VALUES (?, ?, ?, NOW(), ?, ?)";
        $reports_stmt = $conn->prepare($reports_sql);
        $reports_stmt->bind_param("issss", $id, $user_username, $emergency_type, $status, $subject_report);
        $reports_stmt->execute();
        $reports_stmt->close();

        echo "<script>alert('Inquiry submitted successfully'); window.location.href = 'E-Alert-Home.php';</script>";
    } else {
        if ($conn->errno == 1062) { // Duplicate entry error code
            echo "<script>alert('Duplicate entry. Please try again.'); window.location.href = 'E-Alert-Inquiry.php';</script>";
        } else {
            echo "<script>alert('Error submitting inquiry'); window.location.href = 'E-Alert-Inquiry.php';</script>";
        }
    }
    $insert_stmt->close();
}

$conn->close();
?>