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

include 'db_connection.php';


// Fetch the inserted values from the reports table
$sql = "SELECT id, username, emergency_type, date, status, subject_report FROM reports";
$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responder Report Table</title>
    <link rel="stylesheet" href="reporttable.css">

    <style>
        /* Flexbox for logo and logged-in message */


        .alt-logo {
            height: 30px;
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


    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <img src="Alt Logo.png" class="alt-logo" alt="E-Alert Logo">
            <?php if (!empty($user_username)): ?>
                <span class="logged-in-message">Logged in as <?= htmlspecialchars($user_username) ?>
                    (<?= htmlspecialchars($user_role) ?>)</span>
            <?php endif; ?>
        </div>
        <div class="menu-icon" onclick="toggleMenu()">☰</div>
        <nav class="navbar">
            <a href="E-Alert-Home.php">Home</a>
            <a href="reporttable.php">Reports Recieved</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Responder Report Table</h2>

        <!-- Filters -->
        <div class="filters">
            <select id="emergencyFilter">
                <option value="">Filter by Emergency Type</option>
            </select>
            <select id="statusFilter">
                <option value="">Filter by Status</option>
                <option value="pending">Pending</option>
                <option value="in-progress">In Progress</option>
                <option value="resolved">Resolved</option>
                <option value="cancel">Cancel</option>
            </select>
        </div>

        <!-- Table -->
        <table id="reportTable">
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Reported by</th>
                    <th>Emergency Type</th>
                    <th>Date/Time Reported</th>
                    <th>Subject Report</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr data-id='{$row['id']}' data-username='{$row['username']}' data-emergency='{$row['emergency_type']}' data-status='{$row['status']}' data-subject='{$row['subject_report']}'>
                            <td>{$row['id']}</td>
                            <td>{$row['username']}</td>
                            <td>{$row['emergency_type']}</td>
                            <td>{$row['date']}</td>
                            <td>{$row['subject_report']}</td>
                            <td>{$row['status']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No reports found.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const emergencyFilter = document.getElementById("emergencyFilter");
            const statusFilter = document.getElementById("statusFilter");
            const tableRows = document.querySelectorAll("#reportTable tbody tr");

            let emergencyOptions = new Set();

            // Populate filters
            tableRows.forEach(row => {
                emergencyOptions.add(row.dataset.emergency);

                // Make table rows clickable
                row.addEventListener("click", function () {
                    const reportId = row.dataset.id;
                    window.location.href = `reportform.php?id=${reportId}`;
                });
            });

            emergencyOptions.forEach(emergency => {
                let option = document.createElement("option");
                option.value = emergency;
                option.textContent = emergency;
                emergencyFilter.appendChild(option);
            });

            function filterTable() {
                let emergencyValue = emergencyFilter.value.toLowerCase();
                let statusValue = statusFilter.value.toLowerCase();

                tableRows.forEach(row => {
                    let rowEmergency = row.dataset.emergency.toLowerCase();
                    let rowStatus = row.dataset.status.toLowerCase();

                    row.style.display =
                        (emergencyValue === "" || rowEmergency === emergencyValue) &&
                            (statusValue === "" || rowStatus === statusValue)
                            ? "" : "none";
                });
            }

            emergencyFilter.addEventListener("change", filterTable);
            statusFilter.addEventListener("change", filterTable);
        });

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
    </script>

</body>

</html>