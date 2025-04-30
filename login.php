<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);

        // Check for duplicate accounts
        $duplicate_check_sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $duplicate_check_stmt = $conn->prepare($duplicate_check_sql);
        $duplicate_check_stmt->bind_param("s", $email);
        $duplicate_check_stmt->execute();
        $duplicate_check_stmt->bind_result($count);
        $duplicate_check_stmt->fetch();
        $duplicate_check_stmt->close();

        if ($count > 1) { // If more than one account with the same email exists
            echo json_encode(["status" => "error", "message" => "Duplicate accounts found"]);
            $conn->close();
            exit();
        }

        // Proceed with login if no duplicates
        $sql = "SELECT id, username, password, role, birthdate, gender, id_type, id_number, mobile_no, landline, address, id_image, lastname, firstname, middlename FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $username, $hashed_password, $role, $birthdate, $gender, $id_type, $id_number, $mobile_no, $landline, $address, $id_image, $lastname, $firstname, $middlename);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashed_password)) {
            // Store session variables
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $username;
            $_SESSION["email"] = $email;

            // Set user as active
            $update_active_sql = "UPDATE users SET active = 1 WHERE id = ?";
            $update_active_stmt = $conn->prepare($update_active_sql);
            $update_active_stmt->bind_param("i", $user_id);
            $update_active_stmt->execute();
            $update_active_stmt->close();

            // Check if the user has filled the profile information
            if ($birthdate && $gender && $id_type && $id_number && $mobile_no && $landline && $address && $id_image && $lastname && $firstname && $middlename) {
                // Redirect to E-Alert-Home.php if profile information exists
                header("Location: E-Alert-Home.php");
            } else {
                // Redirect to reporter_profile.php if profile information is missing
                header("Location: reporter_profile.php");
            }
            exit();
        } else {
            echo "<script>alert('Invalid email or password'); window.location.href = 'E-Alert-Login.php';</script>";
        }

        $stmt->close();
    }
}
$conn->close();
?>