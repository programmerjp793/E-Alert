<?php
session_start();
include 'db_connection.php';

if (!isset($_GET['id'])) {
    die("User ID not specified");
}

$user_id = $_GET['id'];

// Fetch user data
$sql = "SELECT id, username, role, active, created_at, birthdate, gender, 
               id_type, id_number, mobile_no, landline, address, 
               id_image, lastname, firstname, middlename, verify_id
        FROM users 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result(
    $id, $username, $role, $active, $created_at, $birthdate, $gender,
    $id_type, $id_number, $mobile_no, $landline, $address,
    $id_image, $lastname, $firstname, $middlename, $verify_id
);

if (!$stmt->fetch()) {
    die("User not found");
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #720D2A;
            margin-bottom: 20px;
        }
        .user-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .detail-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .detail-box h3 {
            margin-top: 0;
            color: #720D2A;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .detail-item {
            margin-bottom: 10px;
        }
        .detail-item strong {
            display: inline-block;
            width: 150px;
        }
        .photo-container {
            margin-top: 20px;
            text-align: center;
        }
        .photo-container img {
            max-width: 300px;
            max-height: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.8em;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>User Profile</h2>
        
        <div class="user-details">
            <div class="detail-box">
                <h3>Basic Information</h3>
                <div class="detail-item">
                    <strong>User ID:</strong> <?= htmlspecialchars($id) ?>
                </div>
                <div class="detail-item">
                    <strong>Username:</strong> <?= htmlspecialchars($username) ?>
                </div>
                <div class="detail-item">
                    <strong>Role:</strong> <?= htmlspecialchars($role) ?>
                </div>
                <div class="detail-item">
                    <strong>Status:</strong> 
                    <span class="status-badge <?= $active ? 'status-active' : 'status-inactive' ?>">
                        <?= $active ? 'Active' : 'Inactive' ?>
                    </span>
                </div>
                <div class="detail-item">
                    <strong>Registered:</strong> <?= htmlspecialchars($created_at) ?>
                </div>
            </div>
            
            <div class="detail-box">
                <h3>Personal Information</h3>
                <div class="detail-item">
                    <strong>Full Name:</strong> <?= htmlspecialchars("$firstname $middlename $lastname") ?>
                </div>
                <div class="detail-item">
                    <strong>Birthdate:</strong> <?= htmlspecialchars($birthdate) ?>
                </div>
                <div class="detail-item">
                    <strong>Gender:</strong> <?= htmlspecialchars($gender) ?>
                </div>
            </div>
            
            <div class="detail-box">
                <h3>Contact Information</h3>
                <div class="detail-item">
                    <strong>Mobile:</strong> <?= htmlspecialchars($mobile_no) ?>
                </div>
                <div class="detail-item">
                    <strong>Landline:</strong> <?= htmlspecialchars($landline) ?>
                </div>
                <div class="detail-item">
                    <strong>Address:</strong> <?= htmlspecialchars($address) ?>
                </div>
            </div>
            
            <div class="detail-box">
                <h3>Identification</h3>
                <div class="detail-item">
                    <strong>ID Type:</strong> <?= htmlspecialchars($id_type) ?>
                </div>
                <div class="detail-item">
                    <strong>ID Number:</strong> <?= htmlspecialchars($id_number) ?>
                </div>
            </div>
        </div>
        
            <?php if (!empty($id_image) || !empty($verify_id)): ?>
        <div class="photo-container" style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px;">
            <?php if (!empty($id_image)): ?>
                <div style="flex: 1; min-width: 300px;">
                    <h3>ID Image</h3>
                    <img src="<?= htmlspecialchars($id_image) ?>" alt="ID Image" style="max-width: 100%;">
                </div>
            <?php endif; ?>
            
            <?php if (!empty($verify_id)): ?>
                <div style="flex: 1; min-width: 300px;">
                    <h3>Verification ID</h3>
                    <img src="<?= htmlspecialchars($verify_id) ?>" alt="Verification ID" style="max-width: 100%;">
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    </div>
</body>
</html>

<?php $conn->close(); ?>