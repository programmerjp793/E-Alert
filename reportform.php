<?php
include 'db_connection.php';
session_start();

if (isset($_GET['id'])) {
    $inquiry_id = $_GET['id'];

    // Fetch the data from inquiry_form, google_maps_api, and reports tables
    $sql = "SELECT i.username, i.address, i.subject_report, i.emergency_type, i.client_inquiry, i.photo_attachment, 
                   g.location, g.latitude, g.longitude, i.created_at, r.status, 
                   u.mobile_no, u.birthdate, u.gender, u.id_type, u.id_number, u.landline, u.lastname, u.firstname, u.middlename, u.id_image, u.verify_id
            FROM inquiry_form i
            JOIN google_maps_api g ON i.id = g.id
            JOIN reports r ON i.id = r.id
            JOIN users u ON i.username = u.username
            WHERE i.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $inquiry_id);
    $stmt->execute();
    $stmt->bind_result($username, $address, $subject_report, $emergency_type, $client_inquiry, $photo_attachment, $location, $latitude, $longitude, $created_at, $status, $mobile_no, $birthdate, $gender, $id_type, $id_number, $landline, $lastname, $firstname, $middlename, $id_image, $verify_id);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "No report ID provided.";
    exit;
}

// Fetch the logged-in user's username
$logged_in_username = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_stmt->bind_result($logged_in_username);
    $user_stmt->fetch();
    $user_stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = $_POST['response'];
    $agency = $_POST['agency'];
    $status = $_POST['status'];

    // Check if a record already exists in the responder_system table
    $check_sql = "SELECT id FROM responder_system WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $inquiry_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // Update the existing record
        $update_sql = "UPDATE responder_system SET username = ?, date = NOW(), status = ?, emergency_agencies = ?, responses = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $logged_in_username, $status, $agency, $response, $inquiry_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Insert a new record
        $insert_sql = "INSERT INTO responder_system (id, username, date, status, emergency_agencies, responses) VALUES (?, ?, NOW(), ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("issss", $inquiry_id, $logged_in_username, $status, $agency, $response);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $check_stmt->close();

    // Update the status in the reports table
    $update_report_sql = "UPDATE reports SET status = ? WHERE id = ?";
    $update_report_stmt = $conn->prepare($update_report_sql);
    $update_report_stmt->bind_param("si", $status, $inquiry_id);
    $update_report_stmt->execute();
    $update_report_stmt->close();

    echo "<script>alert('Response submitted successfully'); window.location.href = 'reporttable.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details</title>
    <link rel="stylesheet" href="reportform.css">
</head>

<body>

    <a href="reporttable.php" class="back-button">← Back</a>
    <div class="container">
        <h2>Report Details</h2>
        <div class="details-container">
            <div class="details">
                <p><strong>Report ID:</strong> <?= htmlspecialchars($inquiry_id) ?></p>
                <p><strong>Reported by:</strong> <?= htmlspecialchars($username) ?></p>
                <br>


                <div class="box">
                    <div clas="text">
                        <h3>Full Name</h3>
                        <p><strong>First Name:</strong> <?= htmlspecialchars($firstname) ?></p>
                        <p><strong>Middle Name:</strong> <?= htmlspecialchars($middlename) ?></p>
                        <p><strong>Last Name:</strong> <?= htmlspecialchars($lastname) ?></p>
                        <p><strong>Birthdate:</strong> <?= htmlspecialchars($birthdate) ?></p>
                        <p><strong>Gender:</strong> <?= htmlspecialchars($gender) ?></p>
                    </div>
                </div>

                <div class="box">
                    <div clas="text">
                        <p><strong>ID Type:</strong> <?= htmlspecialchars($id_type) ?></p>
                        <p><strong>ID Number:</strong> <?= htmlspecialchars($id_number) ?></p>
                    </div>
                </div>

                <div class="box">
                    <div clas="text">
                        <p><strong>Mobile Number:</strong> <?= htmlspecialchars($mobile_no) ?></p>
                        <p><strong>Landline:</strong> <?= htmlspecialchars($landline) ?></p>
                    </div>
                </div>

                <div class="box">
                    <div clas="text">
                        <p><strong>Emergency Type:</strong> <?= htmlspecialchars($emergency_type) ?></p>
                        <p><strong>Date/Time Reported:</strong> <?= htmlspecialchars($created_at) ?></p>
                        <p><strong>Subject Report:</strong> <?= htmlspecialchars($subject_report) ?></p>
                    </div>
                </div>

                <div class="box">
                    <div clas="text">
                        <p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($location) ?></p>
                        <p><strong>Latitude:</strong> <?= htmlspecialchars($latitude) ?></p>
                        <p><strong>Longitude:</strong> <?= htmlspecialchars($longitude) ?></p>
                    </div>
                </div>

                <div class="box">
                    <div clas="text"></div>
                    <p><strong>Status:</strong> <?= htmlspecialchars($status) ?></p>
                    <p><strong>Client Inquiry:</strong></p>
                    <div class="client-inquiry-box"><?= nl2br(htmlspecialchars($client_inquiry)) ?></div>
                </div>
            </div>

            <?php if (!empty($id_image)): ?>
                <div class="photo">
                    <p><strong>Reporter Picture:</strong></p>
                    <img src="<?= htmlspecialchars($id_image) ?>" alt="ID Image" class="id-image">
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($verify_id)): ?>
            <div class="verification-section">
                <h3>Verification ID</h3>
                <div class="photo">
                    <img src="<?= htmlspecialchars($verify_id) ?>" alt="Verification ID" class="verify-id">
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($photo_attachment)): ?>
            <div class="photo">
                <p><strong>Photo Attachment:</strong></p>
                <img src="<?= htmlspecialchars($photo_attachment) ?>" alt="Report Photo">
            </div>
        <?php endif; ?>

        <div id="map" class="map"></div>

        <form method="POST" action="">
            <div class="response-container">
                <label for="response">Respond:</label>
                <textarea id="response" name="response" class="scrollable-text"></textarea>
                <br>
                <br>
                <label for="agency">Select Agency:</label>
                <select id="agency" name="agency" class="select-box">
                    <option value="NDRRMC">NDRRMC</option>
                    <option value="OCD">OCD</option>
                    <option value="PNP">PNP</option>
                    <option value="AFP">AFP</option>
                    <option value="PCG">PCG</option>
                    <option value="DND">DND</option>
                    <option value="BFP">BFP</option>
                    <option value="PRC">PRC</option>
                    <option value="DOH">DOH</option>
                    <option value="DPWH">DPWH</option>
                    <option value="MMDA">MMDA</option>
                    <option value="Others">Others</option>
                </select>
                <br>
                <br>
                <label for="status">Status:</label>
                <select id="status" name="status" class="select-box">
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <br>
                <br>
                <button type="submit" class="submit-button">Submit</button>
            </div>
        </form>
    </div>

    <script>
        function initMap() {
            const location = { lat: <?= $latitude ?>, lng: <?= $longitude ?> };
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 15,
                center: location,
            });
            const marker = new google.maps.Marker({
                position: location,
                map: map,
            });
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Add the 'loaded' class to the body for fade-in effect
            document.body.classList.add('loaded');

            // Load Google Maps API
            const script = document.createElement("script");
            script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyDiCmJu27l3xt3SDIgwpfwwBeTfzn-iCAM&callback=initMap`;
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        });
    </script>

</body>

</html>