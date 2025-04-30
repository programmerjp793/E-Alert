<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if ($new_password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.location.href = 'change_password.php?email=" . urlencode($email) . "';</script>";
        exit();
    }

    // Fetch the old password from the database
    $fetch_password_sql = "SELECT password FROM users WHERE email = ?";
    $fetch_password_stmt = $conn->prepare($fetch_password_sql);
    $fetch_password_stmt->bind_param("s", $email);
    $fetch_password_stmt->execute();
    $fetch_password_stmt->bind_result($old_password);
    $fetch_password_stmt->fetch();
    $fetch_password_stmt->close();

    // Check if the new password is the same as the old password
    if (password_verify($new_password, $old_password)) {
        echo "<script>alert('New password cannot be the same as the old password'); window.location.href = 'change_password.php?email=" . urlencode($email) . "';</script>";
        exit();
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the password
    $update_password_sql = "UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?";
    $update_password_stmt = $conn->prepare($update_password_sql);
    $update_password_stmt->bind_param("ss", $hashed_password, $email);
    $update_password_stmt->execute();
    $update_password_stmt->close();

    echo "<script>alert('Password has been reset successfully'); window.location.href = 'E-Alert-Login.php';</script>";

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="mystylesheet.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="form-box change-password">
                <form id="change-password-form" action="change_password.php" method="POST">
                    <h2 class="line-1">CHANGE PASSWORD</h2><br><h2 class="line-2">ENTER NEW PASSWORD</h2>

                    <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($_GET['email']); ?>">

                    <div class="input-box">
                        <span class="icon"><i class='bx bxs-lock'></i></span>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter New Password" required>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class='bx bxs-lock'></i></span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                    </div>

                    <button type="submit" class="btn" id="reset-password-btn">RESET PASSWORD</button>

                    <div class="login-register">
                        <p>Remembered your password?<br>
                            <a href="E-Alert-Login.php" id="back-to-login-link" class="register-link">Back to Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>