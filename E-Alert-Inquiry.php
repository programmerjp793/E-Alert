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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry Form with Dynamic Map</title>
    <link rel="stylesheet" href="inquiry.css"> <!-- Linking external CSS file -->
    <style>
        .logo-container {
            display: flex;
            align-items: center;
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
        <img src="Alt Logo.png" class="alt-logo" alt="E-Alert Logo">
        <?php if (!empty($user_username)): ?>
            <span class="logged-in-message">Logged in as <?= htmlspecialchars($user_username) ?>
                (<?= htmlspecialchars($user_role) ?>)</span>
        <?php endif; ?>
        <div class="menu-icon" onclick="toggleMenu()">☰</div>
        <nav class="navbar">
            <a href="E-Alert-Home.php">Home</a>
            <a href="reporter_profile.php">Profile</a>
            <a href="emergency_reports.php">Emergency Reports</a>
            <a href="E-Alert-Inquiry.php">Report Emergency</a>
            <?php if (!empty($user_username)): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="E-Alert-Login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <section class="inquiry">
        <div class="inquiry-form">
            <h1>Report an Emergency</h1>
            <p>Submit emergency reports happening within your city or barangay.
                Provide key details about the incident, including location, type
                of emergency, and any additional information that can help responders act quickly.</p>

            <form action="process_inquiry.php" method="POST" enctype="multipart/form-data">
                <h3 style="margin-top:40px; margin-bottom: 2px">Basic Information</h3> <br>
                <p>Your user information is filled up here</p>
                <div class="report-section">
                    <label>USERNAME</label>
                    <p><?= htmlspecialchars($user_username) ?></p>
                    <label>ROLE</label>
                    <p><?= htmlspecialchars($user_role) ?></p>
                    <label>PHONE NUMBER</label>
                    <p><?= htmlspecialchars($mobile_no) ?></p>
                    <label>ADDRESS</label>
                    <p><?= htmlspecialchars($address) ?></p>
                </div>

                <div class="subject-and-type">
                    <div class="subject">
                        <label>SUBJECT <br>
                            <p>Title of your Report</p>
                        </label>
                        <input type="text" name="subject_report" class="subject-report"
                            placeholder="What is your report about?" minlength="5" maxlength="50" required>
                    </div>
                    <div class="type">
                        <label>SELECT EMERGENCY TYPE: <br>
                            <p>What type of Emergency was took place?</p>
                        </label>
                        <select name="emergency_type" required>
                            <option value="FIRE">FIRE</option>
                            <option value="MEDICAL">MEDICAL</option>
                            <option value="ACCIDENT">ACCIDENT</option>
                            <option value="PERSONAL THREAT">PERSONAL THREAT</option>
                            <option value="DISASTER">DISASTER</option>
                            <option value="OTHERS">OTHER ISSUES</option>
                        </select>
                    </div>
                </div>

                <!-- Google Maps Autocomplete -->
                <div class="search-input">
                    <label for="locationSearch">Search Location <br>
                        <p>Where did the incident take place?</p>
                    </label>
                    <input type="text" id="locationSearch" class="location-search" placeholder="Enter a location"
                        name="location">
                </div>

                <!-- Google Maps Container -->
                <div id="map" style="height: 300px; width: 100%;"></div>

                <!-- Hidden inputs for coordinates -->
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">


                <div class="inquiry-container">
                    <div class="inquiry-box">
                        <label style="margin-bottom: 10px">Emergency Description</label>
                        <textarea name="client_inquiry" rows="10" cols="100"
                            placeholder="Write a short description of your Emergency Report" required></textarea>
                    </div>
                    <!-- Attach Photo -->
                    <div class="photo">
                        <br>
                        <br>
                        <br>
                        <br>
                        <label>Attach a Photo: </label>
                        <input type="file" name="photo_attachment" accept="image/*">
                    </div>
                </div>

                <!-- Terms & Agreements -->
                <div class="terms">
                    <label><input type="checkbox" required> Agree to our Terms and Conditions</label>
                    <label><input type="checkbox" required> Agree to our Privacy Policy</label>
                </div>


                <button class="submit-btn" type="submit">SUBMIT</button>
            </form>
        </div>
    </section>

    <script>
        function initAutocomplete() {
            let input = document.getElementById('locationSearch');
            let autocomplete = new google.maps.places.Autocomplete(input);

            let defaultLocation = { lat: 14.5995, lng: 120.9842 }; // Default to Manila
            let map = new google.maps.Map(document.getElementById('map'), {
                center: defaultLocation,
                zoom: 13
            });

            let marker = new google.maps.Marker({
                position: defaultLocation,
                map: map,
                draggable: true
            });

            autocomplete.addListener('place_changed', function () {
                let place = autocomplete.getPlace();
                if (!place.geometry) {
                    alert("No details available for the selected location.");
                    return;
                }
                let lat = place.geometry.location.lat();
                let lng = place.geometry.location.lng();
                let locationName = place.formatted_address || "Unknown Location";

                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                document.getElementById('locationSearch').value = locationName;

                marker.setPosition(place.geometry.location);
                map.setCenter(place.geometry.location);
                map.setZoom(15);
            });

            google.maps.event.addListener(marker, 'dragend', function (event) {
                let newLat = event.latLng.lat();
                let newLng = event.latLng.lng();

                document.getElementById('latitude').value = newLat;
                document.getElementById('longitude').value = newLng;
            });
        }
    </script>

    <!-- Include Google Maps API -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDiCmJu27l3xt3SDIgwpfwwBeTfzn-iCAM&libraries=places&callback=initAutocomplete"
        async defer></script>
    <script src="inquiry.js"></script>
</body>

</html>