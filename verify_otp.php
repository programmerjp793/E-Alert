<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify_otp'])) {
        $email = trim($_POST["email"]);
        $otp = trim($_POST["otp"]);

        // Generate OTP expiry time
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Check if the email exists in the database
        $check_email_sql = "SELECT otp, otp_expiry FROM users WHERE email = ?";
        $check_email_stmt = $conn->prepare($check_email_sql);
        $check_email_stmt->bind_param("s", $email);
        $check_email_stmt->execute();
        $check_email_stmt->store_result();
        $check_email_stmt->bind_result($existing_otp, $existing_otp_expiry);
        $check_email_stmt->fetch();

        if ($check_email_stmt->num_rows > 0) {
            // Insert or update the OTP and OTP expiry in the database
            if (empty($existing_otp) || empty($existing_otp_expiry)) {
                $otp_sql = "UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?";
                $otp_stmt = $conn->prepare($otp_sql);
                $otp_stmt->bind_param("sss", $otp, $otp_expiry, $email);
            } else {
                $otp_sql = "UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?";
                $otp_stmt = $conn->prepare($otp_sql);
                $otp_stmt->bind_param("sss", $otp, $otp_expiry, $email);
            }
            $otp_stmt->execute();

            if ($otp_stmt->affected_rows > 0) {
                echo json_encode(["status" => "success", "message" => "OTP verified", "email" => $email]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to update OTP"]);
            }

            $otp_stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Email not found"]);
        }

        $check_email_stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
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
            <div class="form-box verify-otp">
                <form id="verify-otp-form" action="verify_otp.php" method="POST">
                    <h2 class="line-1">VERIFY OTP</h2><br><h2 class="line-2">ENTER EMAIL AND OTP</h2>

                    <div class="input-box">
                        <span class="icon"><i class='bx bxl-gmail'></i></span>
                        <input type="email" id="email" name="email" placeholder="Enter your Email Address" required>
                    </div>

                    <div class="input-box">
                        <span class="icon"><i class='bx bxs-key'></i></span>
                        <input type="number" id="otp" name="otp" placeholder="Enter OTP" required>
                    </div>

                    <button type="button" class="btn" id="verify-otp-btn">VERIFY</button>

                    <div class="login-register">
                        <p>Remembered your password?<br>
                            <a href="E-Alert-Login.php" id="back-to-login-link" class="register-link">Back to Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="login-js.j.js"></script>
</body>
</html>