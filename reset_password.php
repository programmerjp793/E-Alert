<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure this path is correct

include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Check if the email exists in the database
    $email_check_sql = "SELECT COUNT(*) FROM users WHERE email = ?";
    $email_check_stmt = $conn->prepare($email_check_sql);
    $email_check_stmt->bind_param("s", $email);
    $email_check_stmt->execute();
    $email_check_stmt->bind_result($count);
    $email_check_stmt->fetch();
    $email_check_stmt->close();

    if ($count == 0) {
        echo "<script>alert('Invalid email'); window.location.href = 'reset_password.php';</script>";
        exit();
    }

    // Generate a unique token
    $token = bin2hex(random_bytes(16));
    $token_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Store the token and token expiry in the database
    $token_sql = "UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?";
    $token_stmt = $conn->prepare($token_sql);
    $token_stmt->bind_param("sss", $token, $token_expiry, $email);
    $token_stmt->execute();
    $token_stmt->close();

    // Send the token to the user's email using PHPMailer
    $subject = "Password Reset Token";
    $message = "Your password reset token is: $token<br><br>";
    $message .= "Click the link below to reset your password:<br>";
    $message .= "<a href='http://localhost/E-Alert-Web/change_password.php?email=" . urlencode($email) . "&token=" . urlencode($token) . "'>Reset Password</a>";

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ealertexample@gmail.com';
        $mail->Password = 'kqslquordtxtgtdm'; // Use the generated app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('ealertexample@gmail.com', 'E-Alert');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        echo "<script>alert('Token has been sent to your email'); window.location.href = 'E-Alert-Login.php';</script>";
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        echo "<script>alert('Failed to send token'); window.location.href = 'E-Alert-Login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
            <div class="form-box reset-password">
                <form id="reset-password-form" action="reset_password.php" method="POST">
                    <h2 class="line-1">RESET PASSWORD</h2><br><h2 class="line-2">ENTER YOUR EMAIL</h2>

                    <div class="input-box">
                        <span class="icon"><i class='bx bxl-gmail'></i></span>
                        <input type="email" id="reset-email" name="email" placeholder="Enter your Email Address" required>
                    </div>

                    <button type="submit" class="btn" name="reset_password">SEND TOKEN</button>

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