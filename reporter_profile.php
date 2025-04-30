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

// Fetch the logged-in user's profile data
$user_profile = [];
$user_role = '';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $profile_sql = "SELECT birthdate, gender, id_type, id_number, mobile_no, landline, address, id_image, lastname, firstname, middlename, role, verify_id FROM users WHERE id = ?";
    $profile_stmt = $conn->prepare($profile_sql);
    $profile_stmt->bind_param("i", $user_id);
    $profile_stmt->execute();
    $profile_result = $profile_stmt->get_result();
    $user_profile = $profile_result->fetch_assoc();
    $profile_stmt->close();

    // Check the user's role
    $user_role = $user_profile['role'] ?? '';
    if ($user_role === 'receiver') {
        header("Location: reporttable.php");
        exit();
    }
}

// Handle form submission to update profile data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['firstname'];
    $middle_name = $_POST['middlename'];
    $last_name = $_POST['lastname'];
    $birth_date = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $id_type = $_POST['id_type'];
    $id_number = $_POST['id_number'];
    $mobile_no = $_POST['mobile_no'];
    $landline = $_POST['landline'];
    $address = $_POST['address'];
    $id_image = $user_profile['id_image'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $id_image = 'uploads/' . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $id_image);
    }

    $verify_id = $user_profile['verify_id'];

    if (isset($_FILES['verify_id']) && $_FILES['verify_id']['error'] == 0) {
        $verify_id = 'uploads/verify_id/' . uniqid() . '_' . basename($_FILES['verify_id']['name']);
        
        // Create directory if it doesn't exist
        if (!file_exists('uploads/verify_id')) {
            mkdir('uploads/verify_id', 0777, true);
        }
        
        move_uploaded_file($_FILES['verify_id']['tmp_name'], $verify_id);
    }

    $update_sql = "UPDATE users SET firstname = ?, middlename = ?, lastname = ?, birthdate = ?, gender = ?, id_type = ?, id_number = ?, mobile_no = ?, landline = ?, address = ?, id_image = ?, verify_id = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssssssssi", $first_name, $middle_name, $last_name, $birth_date, $gender, $id_type, $id_number, $mobile_no, $landline, $address, $id_image, $verify_id, $user_id);

    if ($update_stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Profile updated successfully.'];
    } else {
        $response = ['status' => 'error', 'message' => 'Failed to update profile.'];
    }

    $update_stmt->close();
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporter Profile | E-Alert</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href='Astyles_profile.css' rel='stylesheet'>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <form id="profile-form" action="reporter_profile.php" method="POST" enctype="multipart/form-data">
            <h2>Profile Information</h2>

            <div class="profile-picture">
                <img id="profile-pic" src="<?= htmlspecialchars($user_profile['id_image'] ?? 'default_profile.png') ?>" alt="Profile Picture">
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" onchange="previewProfilePicture(event)">
                <label for="profile_picture">Change Profile Picture</label>
            </div>

            <div class="input-box">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="firstname" value="<?= htmlspecialchars($user_profile['firstname'] ?? '') ?>" required>
            </div>

            <div class="input-box">
                <label for="middle_name">Middle Name</label>
                <input type="text" id="middle_name" name="middlename" value="<?= htmlspecialchars($user_profile['middlename'] ?? '') ?>">
            </div>

            <div class="input-box">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="lastname" value="<?= htmlspecialchars($user_profile['lastname'] ?? '') ?>" required>
            </div>

            <div class="input-box">
                <label for="birth_date">Birth Date</label>
                <input type="date" id="birth_date" name="birthdate" value="<?= htmlspecialchars($user_profile['birthdate'] ?? '') ?>" required>
            </div>

            <div class="input-box">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?= (isset($user_profile['gender']) && $user_profile['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= (isset($user_profile['gender']) && $user_profile['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                </select>
            </div>

            <div class="input-box">
                <label for="id_type">ID Type</label>
                <input type="text" id="id_type" name="id_type" value="<?= htmlspecialchars($user_profile['id_type'] ?? '') ?>" required>
            </div>

            <div class="input-box">
                <label for="id_number">ID Number</label>
                <input type="text" id="id_number" name="id_number" value="<?= htmlspecialchars($user_profile['id_number'] ?? '') ?>" required>
            </div>

            <div class="input-box">
                <label for="verify_id">Verification ID</label>
                <input type="file" name="verify_id" id="verify_id" accept="image/*">
                <?php if (!empty($user_profile['verify_id'])): ?>
                    <img src="<?php echo htmlspecialchars($user_profile['verify_id']); ?>" alt="Verification ID" style="max-width: 100%; height: auto; margin-top: 10px;">
                <?php endif; ?>
            </div>

            <div class="input-box">
                <label for="mobile_no">Mobile No.</label>
                <input type="text" id="mobile_no" name="mobile_no" value="<?= htmlspecialchars($user_profile['mobile_no'] ?? '') ?>" required>
            </div>

            <div class="input-box">
                <label for="landline">Landline</label>
                <input type="text" id="landline" name="landline" value="<?= htmlspecialchars($user_profile['landline'] ?? '') ?>">
            </div>

            <div class="input-box">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($user_profile['address'] ?? '') ?>" required>
            </div>

            

            <button type="submit" class="btn">Save Profile</button>
        </form>
    </div>

    <script>
        function previewProfilePicture(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('profile-pic');
                output.src = reader.result;
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        $(document).ready(function() {
            $("#profile-form").submit(function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type: "POST",
                    url: "reporter_profile.php",
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function(response) {
                        alert(response.message);
                        if (response.status === "success") {
                            window.location.href = "E-Alert-Home.php";
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>