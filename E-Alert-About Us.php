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

// Fetch the logged-in user's username
$user_username = '';
$user_role = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username, role FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_stmt->bind_result($user_username, $user_role);
    $user_stmt->fetch();
    $user_stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | E-Alert</title>
    <link rel="stylesheet" href="Astyles-about.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .logged-in-message {
            color: white;
        }
    </style>
</head>

<body>
    <header class="header">
        <img src="Alt Logo.png" class="alt-logo" alt="E-Alert Logo">
        <?php if (!empty($user_username)): ?>
            <span class="logged-in-message">Logged in as <?= htmlspecialchars($user_username) ?>
                (<?= htmlspecialchars($user_role) ?>)</span>
        <?php endif; ?>
        <div class="menu-icon" onclick="toggleMenu()">☰</div>
        <nav class="navbar">
            <a href="E-Alert-Home.php">Home</a>
            <a href="E-Alert-About Us.php">About Us</a>
            <a href="emergency_reports.php">Emergency Reports</a>
            <a href="E-Alert-Inquiry.php">Report Emergency</a>
            <?php if (!empty($user_username)): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="E-Alert-Login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- ALL SECTIONS -->


    <!-- About-Us Section -->
    <section id="reviews">
        <div class="logo-container">
            <img src="Logo.png" />
        </div>
        </div>

        <div class="social-media-container">
            <h1>About Us</h1>
            <p>
                At E-Alert, we are committed to making a difference by providing a quick and reliable
                way for people to report emergencies and urgent situations.Through our website and mobile application,
                individuals can easily seek help, ensuring that the right response
                reaches them as fast as possible. </p>

            <p>As we work towards this mission, we hold on to the promise of Psalms 46:1:
                "God is our refuge and strength, a very present help in trouble."
                At E-Alert, we believe that every second counts—because help should always be within reach. </p>
        </div>
    </section>

    <!-- Social Media Section -->

    <section id="socmed">
        <div class="socmed-header">
            <br><br><br><br>
            <h1>OUR SOCIAL MEDIA</h1>
        </div>
        <div class="image-container">
            <img src="socmed1.png">
            <div class="text">
                <i class='bx bxl-facebook-circle text-logo'></i>
                <h1>FACEBOOK</h1>
                <p>Browse Daily Updates
                    and new about Safety
                    awareness on our Facebook Page</p>
                <a href="#" class="btn">Like And Follow Us Here</a>
            </div>
        </div>

        <div class="image-container2">
            <img src="socmed2.png">
            <div class="text">
                <i class='bx bxl-instagram-alt text-logo'></i>
                <h1>INSTAGRAM</h1>
                <p>See pictures and photos about us or other emergencies on our Instagram Page</p>
                <a href="#" class="btn">Like And Follow Us Here</a>
            </div>
        </div>

        <div class="image-container3">
            <img src="socmed3.png">
            <div class="text">
                <i class='bx bxl-twitter text-logo'></i>
                <h1>TWITTER</h1>
                <p>See pictures and photos about us or other emergencies on our Twitter Page</p>
                <a href="#" class="btn">Like And Follow Us Here</a>
            </div>
        </div>


    </section>

    <!-- Scroll to Top Button -->
    <button id="scrollToTopBtn" class="scroll-to-top-btn" onclick="scrollToTop()"><i
            class='bx bxs-up-arrow-alt'></i></button>

    <script>
        function toggleMenu() {
            document.querySelector(".navbar").classList.toggle("active");
        }

        document.addEventListener("DOMContentLoaded", function () {
            document.body.classList.add("loaded");
        });


        // ---------------------- 1 Section at a time Scroll and UI ----------------------


        // Scroll-To-Top Button
        function toggleScrollToTopButton() {
            const button = document.getElementById('scrollToTopBtn');
            const scrollPosition = window.scrollY + window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;

            button.style.display = (scrollPosition >= documentHeight - 10) ? 'block' : 'none';
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        window.addEventListener('scroll', toggleScrollToTopButton);
        toggleScrollToTopButton();

        // When the page is loaded, add the "loaded" class to fade in
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
</body>

</html>