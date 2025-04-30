<?php
session_start();
include 'db_connection.php';

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



// Fetch user data for User Management
$user_result = $conn->query("SELECT id, username, role, active FROM users") or die("Error fetching users: " . $conn->error);

// Fetch agency data for Agency List Maintenance
$agency_result = $conn->query("SELECT id, name, contact_info, agency_number FROM agencies") or die("Error fetching agencies: " . $conn->error);

// Fetch About Us content
$aboutUs = $conn->query("SELECT * FROM content WHERE content_id = 'about_us'")->fetch_assoc();

// Fetch Carousel content
$carousel = $conn->query("SELECT * FROM content WHERE content_id = 'carousel'")->fetch_assoc();

// Fetch data for emergencies received (pending and in-progress)
$received_emergencies = $conn->query("
    SELECT emergency_type, COUNT(*) as count 
    FROM reports 
    WHERE status IN ('pending', 'in-progress') 
    AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY emergency_type
") or die("Error fetching received emergencies: " . $conn->error);

// Fetch data for emergencies resolved
$resolved_emergencies = $conn->query("
    SELECT emergency_agencies, COUNT(*) as count 
    FROM responder_system 
    WHERE status = 'resolved' 
    AND date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY emergency_agencies
") or die("Error fetching resolved emergencies: " . $conn->error);

// Prepare data for charts
$received_data = [];
$received_labels = [];
$received_colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

while ($row = $received_emergencies->fetch_assoc()) {
    $received_labels[] = $row['emergency_type'];
    $received_data[] = $row['count'];
}

$resolved_data = [];
$resolved_labels = [];
$resolved_colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

while ($row = $resolved_emergencies->fetch_assoc()) {
    $resolved_labels[] = $row['emergency_agencies'];
    $resolved_data[] = $row['count'];
}

// Handle form submissions
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // User role update
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_user':
                $user_id = $_POST['user_id'];
                $role = $_POST['role'];
                
                $update_sql = "UPDATE users SET role = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $role, $user_id);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'update_agency':
                $agency_id = $_POST['agency_id'];
                $name = $_POST['name'];
                $contact_info = $_POST['contact_info'];
                $agency_number = $_POST['agency_number'];

                $update_sql = "UPDATE agencies SET name = ?, contact_info = ?, agency_number = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("sssi", $name, $contact_info, $agency_number, $agency_id);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'update_about':
                $title = $_POST['title'];
                $body = $_POST['body'];
                
                $stmt = $conn->prepare("INSERT INTO content (content_id, title, body, created_at) 
                                      VALUES ('about_us', ?, ?, NOW())
                                      ON DUPLICATE KEY UPDATE 
                                      title = VALUES(title), 
                                      body = VALUES(body), 
                                      created_at = NOW()");
                $stmt->bind_param("ss", $title, $body);
                $stmt->execute();
                $stmt->close();
                break;
                
                case 'update_carousel':
                    $content_id = 'carousel';
                    $updateData = ['content_id' => $content_id];
                    
                    // Handle file uploads and deletions
                    for ($i = 1; $i <= 7; $i++) {
                        $fieldName = "image_$i";
                        
                        if (!empty($_FILES[$fieldName]['name'])) {
                            $targetDir = "uploads/carousel/";
                            if (!file_exists($targetDir)) {
                                mkdir($targetDir, 0777, true);
                            }
                            
                            $fileName = basename($_FILES[$fieldName]["name"]);
                            $targetFile = $targetDir . uniqid() . "_" . $fileName;
                            
                            if (move_uploaded_file($_FILES[$fieldName]["tmp_name"], $targetFile)) {
                                $updateData[$fieldName] = $targetFile;
                            }
                        } elseif (isset($_POST["delete_image_$i"])) {
                            $updateData[$fieldName] = null;
                        } else {
                            // Keep existing image if not updating
                            $updateData[$fieldName] = $carousel[$fieldName] ?? null;
                        }
                    }
                    
                    // Build the parameter types string
                    $types = str_repeat('s', count($updateData)); // All parameters are strings (including NULL)
                    
                    // Build the SQL query
                    $columns = implode(', ', array_keys($updateData));
                    $placeholders = implode(', ', array_fill(0, count($updateData), '?'));
                    
                    // For ON DUPLICATE KEY UPDATE, we need to build the update part
                    $updateParts = [];
                    foreach (array_keys($updateData) as $column) {
                        $updateParts[] = "$column = VALUES($column)";
                    }
                    $updateStr = implode(', ', $updateParts);
                    
                    $sql = "INSERT INTO content ($columns, created_at) 
                           VALUES ($placeholders, NOW())
                           ON DUPLICATE KEY UPDATE 
                           $updateStr, created_at = NOW()";
                    
                    $stmt = $conn->prepare($sql);
                    if (!$stmt) {
                        die("Prepare failed: " . $conn->error);
                    }
                    
                    // Bind parameters
                    $params = array_values($updateData);
                    $stmt->bind_param($types, ...$params);
                    
                    if (!$stmt->execute()) {
                        die("Execute failed: " . $stmt->error);
                    }
                    $stmt->close();
                    
                    $_SESSION['message'] = "Carousel updated successfully!";
                    header("Location: admin_dashboard.php");
                    exit();
        }
        
        $_SESSION['message'] = "Update successful!";
        header("Location: admin_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background-color: #720D2A;
            color: white;
            padding: 20px;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar h2 {
            text-align: center;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li {
            margin: 15px 0;
        }
        .sidebar ul li a {
            text-decoration: none;
            color: white;
            padding: 10px;
            display: block;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar ul li a:hover {
            background-color: #a81c3b;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
            background-color: #ecf0f1;
            overflow-y: auto;
        }
        h2 {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #720D2A;
            color: white;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
        .edit-form {
            display: none;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .edit-form.active {
            display: block;
        }
        .edit-form input, .edit-form select, .edit-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .edit-form button {
            padding: 8px 15px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-form button[type="submit"] {
            background-color: #2c3e50;
            color: white;
        }
        .edit-form button[type="button"] {
            background-color: #95a5a6;
            color: white;
        }
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-box {
            flex: 1;
            min-width: 300px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .chart-title {
            text-align: center;
            margin-bottom: 15px;
            color: #720D2A;
            font-weight: bold;
        }
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        #userTable tbody tr {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        #userTable tbody tr:hover {
            background-color: #f5f5f5;
        }
        #userTable tbody tr td:last-child {
            cursor: default;
        }
        #userTable tbody tr:hover td:last-child {
            background-color: transparent;
        }
        .tab-content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .preview-image {
            max-width: 100px;
            max-height: 100px;
            margin: 5px;
        }
        .nav-tabs {
            border-bottom: 1px solid #ddd;
        }
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
            padding: 0.5rem 1rem;
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .image-upload-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        height: 100%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .image-preview-container {
        border: 2px dashed #dee2e6;
        border-radius: 6px;
        padding: 10px;
        background-color: white;
        margin-bottom: 10px;
        text-align: center;
    }
    
    .preview-image {
        max-width: 100%;
        max-height: 150px;
        object-fit: contain;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    
    .file-upload-wrapper {
        position: relative;
        overflow: hidden;
    }
    
    .file-upload-wrapper input[type="file"] {
        padding: 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: white;
    }
    
    .image-actions {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .form-check-label {
        margin-left: 5px;
        font-size: 14px;
    }
    
    .btn-primary {
        background-color: #720D2A;
        border-color: #720D2A;
    }
    
    .btn-primary:hover {
        background-color: #5a0a21;
        border-color: #5a0a21;
    }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="Alt Logo.png" alt="E-Alert Logo" style="height: 40px; margin-right: 10px; margin-bottom: 10px;">
            <h2>Admin Panel</h2>
            <?php if (!empty($user_username)): ?>
                <div style="color: white; font-size: 14px; margin-top: 5px;">
                    Logged in as <?= htmlspecialchars($user_username) ?> (<?= htmlspecialchars($user_role) ?>)
                </div>
            <?php endif; ?>
        </div>
        <ul>
            <li><a href="#" onclick="showSection('admin-dashboard')">Dashboard</a></li>
            <li><a href="#" onclick="showSection('user-management')">User Management</a></li>
            <li><a href="#" onclick="showSection('content-management')">Content Management</a></li>
            <li><a href="#" onclick="showSection('agency-list')">Agency List</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="content">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo strpos($_SESSION['message'], 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div id="admin-dashboard" class="section active">
            <h2>Emergency Statistics (Last 30 Days)</h2>
            
            <div class="chart-container">
                <div class="chart-box">
                    <div class="chart-title">Emergencies Received</div>
                    <div class="chart-wrapper">
                        <canvas id="receivedChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-box">
                    <div class="chart-title">Emergencies Resolved</div>
                    <div class="chart-wrapper">
                        <canvas id="resolvedChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="chart-container">
                <div class="chart-box">
                    <h3>Emergencies Received Summary</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Emergency Type</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $received_total = array_sum($received_data);
                            foreach ($received_labels as $index => $label): 
                                $percentage = $received_total > 0 ? round(($received_data[$index] / $received_total) * 100, 2) : 0;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($label) ?></td>
                                    <td><?= $received_data[$index] ?></td>
                                    <td><?= $percentage ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="chart-box">
                    <h3>Emergencies Resolved Summary</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Agency</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $resolved_total = array_sum($resolved_data);
                            foreach ($resolved_labels as $index => $label): 
                                $percentage = $resolved_total > 0 ? round(($resolved_data[$index] / $resolved_total) * 100, 2) : 0;
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($label) ?></td>
                                    <td><?= $resolved_data[$index] ?></td>
                                    <td><?= $percentage ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="user-management" class="section">
            <h2>User Management</h2>
            <table id="userTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $user_result->data_seek(0); // Reset pointer
                    while ($row = $user_result->fetch_assoc()): ?>
                        <tr data-id="<?= $row['id'] ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td><?= $row['active'] ? 'Active' : 'Inactive' ?></td>
                            <td>
                                <button onclick="editUser(<?= $row['id'] ?>, '<?= htmlspecialchars($row['role']) ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <form id="edit-user-form" class="edit-form" method="POST" action="admin_dashboard.php">
                <h3>Edit User Role</h3>
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="user_id">
                <label for="role">Role:</label>
                <select name="role" id="user_role">
                    <option value="reporter">Reporter</option>
                    <option value="admin">Admin</option>
                    <option value="receiver">Receiver</option>
                </select>
                <button type="submit">Save</button>
                <button type="button" onclick="closeForm('edit-user-form')">Cancel</button>
            </form>
        </div>

        <div id="content-management" class="section">
            <h2>Content Management</h2>
            
            <ul class="nav nav-tabs" id="contentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">About Us</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="carousel-tab" data-bs-toggle="tab" data-bs-target="#carousel" type="button" role="tab">Carousel Images</button>
                </li>
            </ul>
            
            <div class="tab-content" id="contentTabsContent">
                <!-- About Us Tab -->
                <div class="tab-pane fade show active" id="about" role="tabpanel">
                    <form action="admin_dashboard.php" method="POST">
                        <input type="hidden" name="action" value="update_about">
                        
                        <div class="mb-3">
                            <label class="form-label">Title:</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($aboutUs['title'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Content:</label>
                            <textarea name="body" class="form-control" rows="10" required><?php echo htmlspecialchars($aboutUs['body'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update About Us</button>
                    </form>
                </div>
                
                <!-- Carousel Tab -->
        <div class="tab-pane fade" id="carousel" role="tabpanel">
        <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_carousel">
            
            <div class="row">
                <?php for ($i = 1; $i <= 7; $i++): ?>
                    <div class="col-md-4 mb-4">
                        <div class="image-upload-card">
                            <label class="form-label">Image <?php echo $i; ?>:</label>
                            <?php if (!empty($carousel["image_$i"])): ?>
                                <div class="landscape-preview-container">
                                    <img src="<?php echo htmlspecialchars($carousel["image_$i"]); ?>" class="landscape-image">
                                    <div class="image-actions mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="delete_image_<?php echo $i; ?>" id="delete_image_<?php echo $i; ?>">
                                            <label class="form-check-label" for="delete_image_<?php echo $i; ?>">
                                                Delete this image
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="file-upload-wrapper mt-2">
                                <input type="file" name="image_<?php echo $i; ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary px-4 py-2">Update Carousel</button>
            </div>
        </form>
    </div>
            </div>
        </div>

        <div id="agency-list" class="section">
            <h2>Agency List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Agency Name</th>
                        <th>Contact Info</th>
                        <th>Number of Agency</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $agency_result->data_seek(0); // Reset pointer
                    while ($row = $agency_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['contact_info']) ?></td>
                            <td><?= htmlspecialchars($row['agency_number'] ?? '0') ?></td>
                            <td>
                                <button onclick="editAgency(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>', '<?= htmlspecialchars($row['contact_info']) ?>', '<?= htmlspecialchars($row['agency_number'] ?? '0') ?>')">Edit</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <form id="edit-agency-form" class="edit-form" method="POST" action="admin_dashboard.php">
                <h3>Edit Agency</h3>
                <input type="hidden" name="action" value="update_agency">
                <input type="hidden" name="agency_id" id="agency_id">
                <label for="name">Agency Name:</label>
                <input type="text" name="name" id="agency_name">
                <label for="contact_info">Contact Info:</label>
                <input type="text" name="contact_info" id="agency_contact_info">
                <label for="agency_number">Agency Number:</label>
                <input type="text" name="agency_number" id="agency_number">
                <button type="submit">Save</button>
                <button type="button" onclick="closeForm('edit-agency-form')">Cancel</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showSection(sectionId) {
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
            section.style.display = 'none';
        });
        document.getElementById(sectionId).classList.add('active');
        document.getElementById(sectionId).style.display = 'block';
    }

    function editUser(id, role) {
        event.stopPropagation();
        document.getElementById('user_id').value = id;
        document.getElementById('user_role').value = role;
        document.getElementById('edit-user-form').classList.add('active');
    }
    
    function editAgency(id, name, contact_info, agency_number) {
        document.getElementById('agency_id').value = id;
        document.getElementById('agency_name').value = name;
        document.getElementById('agency_contact_info').value = contact_info;
        document.getElementById('agency_number').value = agency_number;
        document.getElementById('edit-agency-form').classList.add('active');
    }
    
    function closeForm(formId) {
        document.getElementById(formId).classList.remove('active');
    }

    // Register the plugin
Chart.register(ChartDataLabels);

document.addEventListener('DOMContentLoaded', function() {
    // Function to calculate percentage
    function calculatePercentage(value, total) {
        return Math.round((value / total) * 100);
    }

    // Received Emergencies Chart
    const receivedCtx = document.getElementById('receivedChart').getContext('2d');
    const receivedTotal = <?= array_sum($received_data) ?>;
    const receivedChart = new Chart(receivedCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($received_labels) ?>,
            datasets: [{
                data: <?= json_encode($received_data) ?>,
                backgroundColor: <?= json_encode(array_slice($received_colors, 0, count($received_labels))) ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = calculatePercentage(value, receivedTotal);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    formatter: (value) => {
                        return calculatePercentage(value, receivedTotal) + '%';
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    anchor: 'center',
                    align: 'center',
                    offset: 0
                }
            }
        },
        plugins: [ChartDataLabels]
    });

        // Resolved Emergencies Chart
    const resolvedCtx = document.getElementById('resolvedChart').getContext('2d');
    const resolvedTotal = <?= array_sum($resolved_data) ?>;
    const resolvedChart = new Chart(resolvedCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($resolved_labels) ?>,
            datasets: [{
                data: <?= json_encode($resolved_data) ?>,
                backgroundColor: <?= json_encode(array_slice($resolved_colors, 0, count($resolved_labels))) ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = calculatePercentage(value, resolvedTotal);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    formatter: (value) => {
                        return calculatePercentage(value, resolvedTotal) + '%';
                    },
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 12
                    },
                    anchor: 'center',
                    align: 'center',
                    offset: 0
                }
            }
        },
        plugins: [ChartDataLabels]
    });

        // Make user table rows clickable
        document.querySelectorAll('#userTable tbody tr').forEach(row => {
            row.addEventListener('click', function(e) {
                if (!e.target.closest('button')) {
                    const userId = row.dataset.id;
                    window.location.href = `user_info.php?id=${userId}`;
                }
            });
            
            row.style.cursor = 'pointer';
        });

        // Initialize Bootstrap tabs
        const tabElms = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabElms.forEach(tabEl => {
            tabEl.addEventListener('click', function (event) {
                event.preventDefault();
                const tab = new bootstrap.Tab(tabEl);
                tab.show();
            });
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>