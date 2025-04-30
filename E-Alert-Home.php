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

// Fetch the logged-in user's username, role
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

// Fetch About Us content
$aboutUs = [];
$about_query = "SELECT title, body FROM content WHERE content_id = 'about_us'";
$about_result = $conn->query($about_query);
if ($about_result && $about_result->num_rows > 0) {
    $aboutUs = $about_result->fetch_assoc();
}

// Fetch Carousel images
$carousel = [];
$carousel_query = "SELECT image_1, image_2, image_3, image_4, image_5, image_6, image_7 FROM content WHERE content_id = 'carousel'";
$carousel_result = $conn->query($carousel_query);
if ($carousel_result && $carousel_result->num_rows > 0) {
    $carousel = $carousel_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home | E-Alert</title>
    <link rel="stylesheet" href="Astyles-home.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        /* Flexbox for logo and logged-in message */
        .logo-container {
            display: flex;
            align-items: center;
            /* Align items vertically */
        }

        .alt-logo {
            height: 40px;
            margin-right: 10px;
        }

        .logged-in-message {
            font-size: 16px;
            color: white;
            font-weight: 100;
            padding-left: 10px;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-container">
            <img src="Alt Logo.png" class="alt-logo" alt="E-Alert Logo">
            <?php if (!empty($user_username)): ?>
                <span class="logged-in-message">Logged in as <?= htmlspecialchars($user_username) ?>
                    (<?= htmlspecialchars($user_role) ?>)</span>
            <?php endif; ?>
        </div>
        <nav class="navbar">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="E-Alert-Home.php">Home</a>
                <a href="emergency_reports.php">Emergency Reports</a>
                <a href="E-Alert-Login.php">Report Emergency</a>
                <a href="E-Alert-Login.php">Login</a>
            <?php else: ?>
                <?php if ($user_role == 'reporter'): ?>
                    <a href="E-Alert-Home.php">Home</a>
                    <a href="reporter_profile.php">Profile</a>
                    <a href="emergency_reports.php">Emergency Reports</a>
                    <a href="E-Alert-Inquiry.php">Report Emergency</a>
                <?php elseif ($user_role == 'receiver'): ?>
                    <a href="E-Alert-Home.php">Home</a>
                    <a href="reporter_profile.php">Profile</a>
                    <a href="reporttable.php">Received Requests</a>
                <?php elseif ($user_role == 'admin'): ?>
                    <a href="admin_dashboard.php">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php endif; ?>
        </nav>
    </header>

    <!-- ALL SECTIONS -->
    <!-- Carousel Section -->
    <!-- Carousel Section - Updated to use database images -->
    <section id="carousel" class="fade-in">
        <div class="carousel-container">
            <div class="carousel-slide">
                <?php for ($i = 1; $i <= 7; $i++): ?>
                    <?php if (!empty($carousel["image_$i"])): ?>
                        <img src="<?php echo htmlspecialchars($carousel["image_$i"]); ?>" alt="Carousel Image <?php echo $i; ?>" class="carousel-image">
                    <?php else: ?>
                        <!-- Fallback image if no image in database -->
                        <img src="img <?php echo $i; ?>.png" alt="Default Image <?php echo $i; ?>" class="carousel-image">
                    <?php endif; ?>
                <?php endfor; ?>
            </div>

            <button class="prev-btn" onclick="moveSlide(-1)">&#10094;</button>
            <button class="next-btn" onclick="moveSlide(1)">&#10095;</button>

            <div class="carousel-text">
                <h2>Emergency Action Plan</h2>
            </div>
        </div>
    </section>

    <!-- Inquire Section -->
    <section id="inquire" class="fade-in">
        <h1>Our Partner Agencies</h1>
        <p>Emergency Services and hotlines where users can use services or report issues.</p>

        <div class="emergency-services">
            <div class="service-card">
                <img src="Es-ndrrmc.png" alt="NDRRMC">
                <div class="service-info">
                    <a href="https://ndrrmc.gov.ph/" title="Go To NDDRMC official Website">
                        <h2>NDRRMC</h2>
                        <p>National Disaster Risk Reduction and Management Council
                            serves as the President's adviser on disaster preparedness
                            programs, disaster operations and rehabilitation efforts undertaken
                            by the government and the private sector.
                        </p>
                    </a>
                </div>
            </div>

            <div class="service-card">
                <img src="Es-pnp.png" alt="PNP">
                <div class="service-info">
                    <a href="https://www.facebook.com/pnp.pio/s" title="Go To PNP official Website">
                        <h2>PNP</h2>
                        <p>
                            The PNP (Philippine National Police) is the national police force of the Philippines.
                            It is responsible for enforcing the law, maintaining peace and order, and ensuring
                            public safety across the country.
                        </p>
                    </a>
                </div>
            </div>

            <div class="service-card">
                <img src="Es-bfp.png" alt="BFP">
                <div class="service-info">
                    <a href="https://bfp.gov.ph/" title="Go To BFP official Website">
                        <h2>BFP</h2>
                        <p>The BFP (Bureau of Fire Protection) is the agency responsible for fire prevention,
                            suppression, and emergency response in the Philippines.</p>
                    </a>
                </div>
            </div>

            <div class="service-card">
                <img src="Es-prc (2).png" alt="PRC">
                <div class="service-info">
                    <a href="https://redcross.org.ph/" title="Go To PRC official Website">
                        <h2>PRC</h2>
                        <p>The PRC (Philippine Red Cross) is a humanitarian organization that provides
                            emergency response, disaster relief, health services, and blood donation
                            programs in the Philippines.</p>
                    </a>
                </div>
            </div>

            <div class="service-card">
                <img src="Es-OCD.jpg" alt="OCD">
                <div class="service-info">
                    <a href="https://www.facebook.com/civildefensePH/" title="Go To OCD facebook page">
                        <h2>OCD</h2>
                        <p>The Office of Civil Defense (OCD) in the Philippines is the agency responsible
                             for implementing comprehensive Disaster Risk Reduction and Management (DRRM) programs, 
                             coordinating government agencies, private institutions, and civic organizations to ensure 
                             effective disaster management.</p>
                    </a>
                </div>
            </div>

            <div class="service-card">
                <img src="Es-afp.png" alt="AFP">
                <div class="service-info">
                    <a href="https://www.afp.mil.ph/" title="Go To AFP Official Website">
                        <h2>AFP</h2>
                        <p>The Armed Forces of the Philippines (AFP), or Sandatahang Lakas ng Pilipinas in Filipino, 
                            is the military force of the Philippines, consisting of the Army, Air Force, and Navy 
                            (including the Marine Corps). </p>
                    </a>
                </div>
            </div>

            <div class="service-card">
                <img src="Es-pcg.png" alt="PCG">
                <div class="service-info">
                    <a href="https://www.coastguard.gov.ph/" title="Go To PCG Official Website">
                        <h2>PCG</h2>
                        <p>In the Philippines, "PCG" stands for the Philippine Coast Guard, an armed and uniformed 
                            service under the Department of Transportation, responsible for maritime law enforcement, 
                            search and rescue, and maritime security.  </p>
                    </a>
                </div>
            </div>

            <div class="service-card">
                <img src="Es-dnd.png" alt="DND">
                <div class="service-info">
                    <a href="https://www.facebook.com/DNDPHL/" title="Go To DND Official Website">
                        <h2>DND</h2>
                        <p>The Department of National Defense (DND) is tasked to guard the country against external
                             and internal threats to national peace and security, and to provide support for social and 
                             economic development. </p>
                    </a>
                </div>
            </div>

            <div class="service-card">
                <img src="Es-doh.png" alt="DOH">
                <div class="service-info">
                    <a href="https://doh.gov.ph/" title="Go To DND Official Website">
                        <h2>DOH</h2>
                        <p>The DOH (Department of Health), as the government entity responsible for the promotion of the health and well-being 
                            of every Filipino, needs to ensure that the population has access to quality and affordable health 
                            products and services as well as to social health insuance through efficient regulatory servcies. </p>
                    </a>
                </div>
            </div>


    </section>

    <section id="reviews" class="fade-in">
        <div class="logo-container2">
            <img src="Logo.png" />
        </div>
        </div>

        <div class="aboutUs-container">
            <h1><?php echo htmlspecialchars($aboutUs['title'] ?? 'About Us'); ?></h1>
            <p><?php echo nl2br(htmlspecialchars($aboutUs['body'] ?? 'At E-Alert, we are committed to making a difference by providing a quick and reliable way for people to report emergencies and urgent situations.')); ?></p>
        </div>
    </section>

    <!-- Social Media Section -->

    <section id="socmed" class="fade-in">
        <div class="socmed-header">
            <br>
            <br>
            <br>
            <h1>OUR SOCIAL MEDIA</h1>
        </div>
        <div class="image-container">
            <img src="socmed1.png">
            <div class="soc-text">
                <i class='bx bxl-facebook-circle text-logo'></i>
                <h1>FACEBOOK</h1>
                <p>Browse Daily Updates
                    and new about Safety
                    awareness on our Facebook Page</p>
                <a href="#" class="soc-btn">Like And Follow Us Here</a>
            </div>
        </div>
        <div class="image-container2" class="fade-in">
            <img src="socmed2.png">
            <div class="soc-text">
                <i class='bx bxl-instagram-alt text-logo'></i>
                <h1>INSTAGRAM</h1>
                <p>See pictures and photos about us or other emergencies on our Instagram Page</p>
                <a href="#" class="soc-btn">Like And Follow Us Here</a>
            </div>
        </div>

        <div class="image-container3" class="fade-in">
            <img src="socmed3.png">
            <div class="soc-text">
                <i class='bx bxl-twitter text-logo'></i>
                <h1>TWITTER</h1>
                <p>See pictures and photos about us or other emergencies on our Twitter Page</p>
                <a href="#" class="soc-btn">Like And Follow Us Here</a>
            </div>
        </div>
    </section>

    <section id="emailUs">
        <div class="emailUs-container">
            <h1>EMAIL US</h1>
            <p>
                Have any other concerns or reports? <br>
                Try Reaching Us personally through our official Email account</p>

            <a href="mailto:E-alert@gmail.com" class="soc-btn2">E-alert@gmail.com</a>

            <div class="image-container4">
                <img src="socmed4.png">
            </div>
        </div>
    </section>

    <script>
        function toggleMenu() {
            document.querySelector(".navbar").classList.toggle("active");
        }

        let index = 0;  // Starting index of images

        // Function to change slide
        function moveSlide(direction) {
            const slides = document.querySelectorAll('.carousel-image');
            const totalSlides = slides.length;

            // Calculate new index and ensure looping behavior
            index += direction;

            if (index < 0) {
                index = totalSlides - 1;
            } else if (index >= totalSlides) {
                index = 0;
            }

            // Update the slide position
            document.querySelector('.carousel-slide').style.transition = "transform 0.5s ease-in-out";
            document.querySelector('.carousel-slide').style.transform = `translateX(-${index * 100}%)`;
        }

        // Automatic slideshow (every 2 seconds)
        let autoSlide = setInterval(() => moveSlide(1), 8000);

        // Reset auto-slide when user interacts
        function resetAutoSlide() {
            clearInterval(autoSlide);
            autoSlide = setInterval(() => moveSlide(1), 8000);
        }

        document.addEventListener("DOMContentLoaded", function () {
            document.body.classList.add("loaded");
        });

        document.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", function (e) {
                e.preventDefault();
                let href = this.href;
                document.body.style.opacity = 0;
                setTimeout(() => { window.location.href = href; }, 500);
            });
        });


        document.addEventListener("DOMContentLoaded", () => {
            const fadeInElements = document.querySelectorAll(".fade-in");

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("show");
                    } else {
                        entry.target.classList.remove("show");
                    }
                });
            }, {
                threshold: 0.2
            });

            fadeInElements.forEach(element => observer.observe(element));
        });

    </script>
</body>
</html>