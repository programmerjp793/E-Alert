<?php
session_start();

// Sample data for demonstration purposes
$user_profile = [
    'first_name' => 'John',
    'middle_name' => 'A.',
    'last_name' => 'Doe',
    'birth_date' => '1990-01-01',
    'gender' => 'Male',
    'id_type' => 'Passport',
    'id_number' => 'A1234567',
    'mobile_no' => '09171234567',
    'landline' => '028123456',
    'address' => '123 Sample Street, Sample City',
    'id_image' => ''
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Form | Sample</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            box-shadow: 3px 3px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .input-box {
            margin-bottom: 15px;
        }

        .input-box label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .input-box input,
        .input-box select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .input-box input[type="file"] {
            padding: 3px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <form id="profile-form" action="save_profile.php" method="POST" enctype="multipart/form-data">
        <h2>Profile Information</h2>

        <div class="input-box">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user_profile['first_name']) ?>" required>
        </div>

        <div class="input-box">
            <label for="middle_name">Middle Name</label>
            <input type="text" id="middle_name" name="middle_name" value="<?= htmlspecialchars($user_profile['middle_name']) ?>">
        </div>

        <div class="input-box">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user_profile['last_name']) ?>" required>
        </div>

        <div class="input-box">
            <label for="birth_date">Birth Date</label>
            <input type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($user_profile['birth_date']) ?>" required>
        </div>

        <div class="input-box">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" required>
                <option value="Male" <?= ($user_profile['gender'] == 'Male') ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= ($user_profile['gender'] == 'Female') ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= ($user_profile['gender'] == 'Other') ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="input-box">
            <label for="id_type">ID Type</label>
            <input type="text" id="id_type" name="id_type" value="<?= htmlspecialchars($user_profile['id_type']) ?>" required>
        </div>

        <div class="input-box">
            <label for="id_number">ID Number</label>
            <input type="text" id="id_number" name="id_number" value="<?= htmlspecialchars($user_profile['id_number']) ?>" required>
        </div>

        <div class="input-box">
            <label for="mobile_no">Mobile No.</label>
            <input type="text" id="mobile_no" name="mobile_no" value="<?= htmlspecialchars($user_profile['mobile_no']) ?>" required>
        </div>

        <div class="input-box">
            <label for="landline">Landline</label>
            <input type="text" id="landline" name="landline" value="<?= htmlspecialchars($user_profile['landline']) ?>">
        </div>

        <div class="input-box">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?= htmlspecialchars($user_profile['address']) ?>" required>
        </div>

        <div class="input-box">
            <label for="id_image">ID Image</label>
            <input type="file" id="id_image" name="id_image" accept="image/*">
        </div>

        <button type="submit" class="btn">Save Profile</button>
    </form>
</div>

</body>
</html>