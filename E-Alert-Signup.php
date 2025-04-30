<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | E-Alert</title>
    <link rel="stylesheet" href="Astyles-loginSignup.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <header class="header">
        <nav class="navbar">
            <a href="E-Alert-Home.php">Home</a>
            <a href="emergency_reports.php">Emergency Reports</a>
            <a href="E-Alert-Login.php">Report Emergency</a>
        </nav>
    </header>

    <div class="logreg-box-bg"></div>
    <div class="background"></div>
    <div class="container">
        <div class="content">
            <div class="text-sci">
                <img src="Logo.png" alt="Above Logo" class="above-logo-image" />
                <h2>About Us</h2>
                <p>Stay Safe with Instant Emergency Alerts!<br>

                    Report emergencies like earthquakes, fires, and floods in real time and get the help you need—fast.
                    Stay connected with emergency response teams and ensure your community’s safety. <br><br>Login now to be a part
                    of a smarter, safer future! </p>

                <div class="social-icons">
                    <a href="#"><i class='bx bxl-facebook-square'></i></a>
                    <a href="#"><i class='bx bxl-instagram-alt'></i></a>
                    <a href="#"><i class='bx bxl-twitter'></i></a>
                </div>
            </div>

            <div class="logreg-box">
                <div class="form-box signup">
                    <form id="signup-form" action="signup.php" method="POST">
                        <h2 class="line-1">SIGN UP</h2><br>
                        <h2 class="line-2">CREATE AN ACCOUNT</h2>

                        <div class="input-box">
                            <span class="icon"><i class='bx bxs-user'></i></span>
                            <input type="text" id="username" name="username" placeholder="Enter a username" required>
                            <label for="username">Username</label>
                        </div>

                        <div class="input-box">
                            <span class="icon"><i class='bx bxl-gmail'></i></span>
                            <input type="email" id="email" name="email" placeholder="Enter a valid Email Address"
                                required>
                            <label for="email">Email</label>
                        </div>

                        <div class="input-box">
                            <span class="toggle-password"><i class='bx bxs-show'></i></span>
                            <input type="password" id="password" name="password" placeholder="Min. of 8 characters"
                                required>
                            <label for="password">Password</label>
                        </div>

                        <div class="input-box">
                            <span class="toggle-password"><i class='bx bxs-show'></i></span>
                            <input type="password" id="confirm-password" name="confirm_password"
                                placeholder="Confirm your password" required>
                            <label for="confirm-password">Confirm Password</label>
                        </div>

                        <button type="submit" class="btn">Sign Up</button>

                        <div class="login-register">
                            <p>Already have an account?<br>
                                <a href="E-Alert-Login.php" class="register-link">Login</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>

    document.addEventListener("DOMContentLoaded", function () {
            // PASSWORD FIELD VISIBILITY
            document.querySelector('.toggle-password').addEventListener('click', function () {
                const passwordField = document.getElementById('password');
                const icon = this.querySelector('i');

                // TOGGLE PASSWORD VISIBILITY
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    icon.classList.remove('bxs-show');
                    icon.classList.add('bxs-hide');
                } else {
                    passwordField.type = 'password';
                    icon.classList.remove('bxs-hide');
                    icon.classList.add('bxs-show');
                }
            });
        });

        $(document).ready(function () {
            $("#signup-form").submit(function (event) {
                event.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "signup.php",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function (response) {
                        alert(response.message);
                        if (response.status === "success") {
                            window.location.href = "E-Alert-Login.php";
                        }
                    }
                });
            });
        });


        // Fade-in effect on load
        document.addEventListener("DOMContentLoaded", function () {
            document.body.classList.add("loaded");
        });

        // Fade-out effect before leaving the page
        document.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                let href = this.href;
                document.body.style.opacity = 0;
                setTimeout(() => { window.location.href = href; }, 500);
            });
        });
    </script>
    </script>
</body>

</html>