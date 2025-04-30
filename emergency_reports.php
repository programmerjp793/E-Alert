<?php
include 'db_connection.php';
session_start();

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

// Pagination settings
$limit = 10; // Number of entries to show in a page.
if (isset($_GET["page"])) {
    $page = $_GET["page"];
} else {
    $page = 1;
}
;
$start_from = ($page - 1) * $limit;

// Filter settings
$filter_query = "";
$filter_params = [];
if (isset($_GET['filter_type']) && !empty($_GET['filter_value'])) {
    $filter_type = $_GET['filter_type'];
    if ($filter_type == 'r.status') {
        $filter_query = " AND r.status = ?";
        $filter_params[] = $_GET['filter_value'];
    } elseif ($filter_type == 'responder_system.username') {
        $filter_query = " AND rs.username = ?";
        $filter_params[] = $_GET['filter_value'];
    } elseif ($filter_type == 'r.date') {
        $filter_query = " AND DATE(r.date) = ?";
        $filter_params[] = $_GET['filter_value'];
    } else {
        $filter_query = " AND $filter_type = ?";
        $filter_params[] = $_GET['filter_value'];
    }
}

// Fetch data from the database
$sql = "SELECT i.id, i.username, i.subject_report, i.client_inquiry, i.address, r.emergency_type, i.photo_attachment, 
               g.location, g.latitude, g.longitude, i.created_at, r.status, r.date 
        FROM inquiry_form i
        LEFT JOIN google_maps_api g ON i.id = g.id
        LEFT JOIN reports r ON i.id = r.id
        LEFT JOIN responder_system rs ON i.id = rs.id
        WHERE i.username = ? $filter_query
        ORDER BY i.created_at DESC
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$types = str_repeat('s', count($filter_params)) . 'sii';
$params = array_merge([$user_username], $filter_params, [$start_from, $limit]);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Fetch total records for pagination
$total_sql = "SELECT COUNT(*) FROM inquiry_form i
              LEFT JOIN reports r ON i.id = r.id
              LEFT JOIN responder_system rs ON i.id = rs.id
              WHERE i.username = ? $filter_query";
$total_stmt = $conn->prepare($total_sql);
$total_types = str_repeat('s', count($filter_params) + 1);
$total_params = array_merge([$user_username], $filter_params);
$total_stmt->bind_param($total_types, ...$total_params);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_records = $total_result->fetch_row()[0];
$total_pages = ceil($total_records / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Reports</title>
    <link rel="stylesheet" href="Ereports_styles.css">
    <style>
        .logged-in-message {
            color: white;
        }
    </style>
    <script>
        function updateFilterValues() {
            var filterType = document.getElementById('filter_type').value;
            var filterValueContainer = document.getElementById('filter_value_container');
            filterValueContainer.innerHTML = '';

            if (filterType === 'r.emergency_type') {
                var options = ['Fire', 'Medical', 'Accident', 'Threat', 'Crime', 'Disaster', 'Other'];
                var select = document.createElement('select');
                select.name = 'filter_value';
                options.forEach(function (option) {
                    var opt = document.createElement('option');
                    opt.value = option;
                    opt.innerHTML = option;
                    select.appendChild(opt);
                });
                filterValueContainer.appendChild(select);
            } else if (filterType === 'r.status') {
                var options = ['pending', 'in-progress', 'resolved', 'cancelled'];
                var select = document.createElement('select');
                select.name = 'filter_value';
                options.forEach(function (option) {
                    var opt = document.createElement('option');
                    opt.value = option;
                    opt.innerHTML = option;
                    select.appendChild(opt);
                });
                filterValueContainer.appendChild(select);
            } else if (filterType === 'responder_system.username') {
                fetch('fetch_responders.php')
                    .then(response => response.json())
                    .then(data => {
                        var select = document.createElement('select');
                        select.name = 'filter_value';
                        data.forEach(function (option) {
                            var opt = document.createElement('option');
                            opt.value = option;
                            opt.innerHTML = option;
                            select.appendChild(opt);
                        });
                        filterValueContainer.appendChild(select);
                    });
            } else if (filterType === 'r.date') {
                fetch('fetch_dates.php')
                    .then(response => response.json())
                    .then(data => {
                        var select = document.createElement('select');
                        select.name = 'filter_value';
                        data.dates.forEach(function (date) {
                            var opt = document.createElement('option');
                            opt.value = date;
                            opt.innerHTML = date;
                            select.appendChild(opt);
                        });
                        filterValueContainer.appendChild(select);
                    });
            }
        }
    </script>
</head>

<header class="header">
    <img src="Alt Logo.png" class="alt-logo" alt="E-Alert Logo">
    <?php if (!empty($user_username)): ?>
        <span class="logged-in-message">Logged in as <?= htmlspecialchars($user_username) ?>
            (<?= htmlspecialchars($user_role) ?>)</span>
    <?php endif; ?>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
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
    </nav>
</header>

<div class="filters-header">
    <h2>Emergency Reports</h2>

    <div class="filters-wrapper">
        <label class="filter-label">Filter By:</label>
        <div class="filter-container">
            <form method="GET" action="emergency_reports.php">
                <select name="filter_type" id="filter_type" onchange="updateFilterValues()">
                    <option value="r.emergency_type">Emergency Type</option>
                    <option value="r.status">Status</option>
                    <option value="responder_system.username">Receiver</option>
                    <option value="r.date">Date/Time Reported</option>
                </select>
                <div id="filter_value_container">
                    <!-- Options will be populated based on filter_type selection -->
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</div>

<div class="container">


    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card">
            <hr style="margin: 20px;"s>
            <h3 style="width: 40%; text-align: center; background-color:rgb(125, 44, 44);
            padding: 10px; border: none; border-radius: 30px; display: flex; flex-direction: column; align-items: center;">
                <?= htmlspecialchars($row['subject_report']) ?></h3>
            <p><strong>Reported by:</strong> <?= htmlspecialchars($row['username']) ?></p>
            <p><strong>Emergency Type:</strong> <?= htmlspecialchars($row['emergency_type']) ?></p>
            <p><strong>Date/Time Reported:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
            <br>
            <p><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></p>
            <p><strong>Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
            <p><strong>Latitude:</strong> <?= htmlspecialchars($row['latitude']) ?></p>
            <p><strong>Longitude:</strong> <?= htmlspecialchars($row['longitude']) ?></p>
            <br>
            <p><strong>Status:</strong> <?= htmlspecialchars($row['status']) ?></p>
            <br>
            <p><strong>Client Inquiry:</strong></p>
            <div class="client-inquiry-box"><?= nl2br(htmlspecialchars($row['client_inquiry'])) ?></div>
            <?php if (!empty($row['photo_attachment'])): ?>
                <p><strong>Photo Attachment:</strong></p>
                <img src="<?= htmlspecialchars($row['photo_attachment']) ?>" alt="Report Photo"
                    style="max-width: 100%; height: auto; margin: auto; display: flex; flex-direction: column; align-items: center;">
            <?php endif; ?>

            <!-- Fetch and display responses from responder_system table -->
            <?php
            $response_sql = "SELECT id, username, date, status, emergency_agencies, responses FROM responder_system WHERE id = ?";
            $response_stmt = $conn->prepare($response_sql);
            $response_stmt->bind_param("i", $row['id']);
            $response_stmt->execute();
            $response_result = $response_stmt->get_result();
            while ($response_row = $response_result->fetch_assoc()):
                ?>
                <button type="button" class="collapsible">View Response from
                    <?= htmlspecialchars($response_row['username']) ?></button>
                    <div class="content">
                        <p style="color: black;"><strong>ID:</strong> <?= htmlspecialchars($response_row['id']) ?></p>
                        <p style="color: black;"><strong>Receiver:</strong> <?= htmlspecialchars($response_row['username']) ?></p>
                        <p style="color: black;"><strong>Date:</strong> <?= htmlspecialchars($response_row['date']) ?></p>
                        <p style="color: black;"><strong>Status:</strong> <?= htmlspecialchars($response_row['status']) ?></p>
                        <p style="color: black;"><strong>Emergency Agencies:</strong> <?= htmlspecialchars($response_row['emergency_agencies']) ?></p>
                        <p style="color: black;"><strong>Response:</strong> <?= nl2br(htmlspecialchars($response_row['responses'])) ?></p>
                    </div>
            <?php endwhile; ?>
        </div>
    <?php endwhile; ?>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="emergency_reports.php?page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>

<script>
    var coll = document.getElementsByClassName("collapsible");
    var i;

    for (i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function () {
            this.classList.toggle("active");
            var content = this.nextElementSibling;
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        });
    }

    // Initialize filter values on page load
    document.addEventListener("DOMContentLoaded", function () {
        updateFilterValues();
    });
</script>

</body>

</html>