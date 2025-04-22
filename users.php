<?php
include 'dbconnection.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's profile details
try {
    $email = $_SESSION['email'];
    $profileStmt = $connection->prepare("SELECT first_name, last_name, profile_image FROM user_table WHERE email = :email");
    $profileStmt->bindParam(':email', $email);
    $profileStmt->execute();
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch all users from the database
try {
    $stmt = $connection->prepare("SELECT id, first_name, last_name, email, is_verified, profile_image FROM user_table");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch the count of verified users
try {
    $verifiedStmt = $connection->prepare("SELECT COUNT(*) AS verified_count FROM user_table WHERE is_verified = 1");
    $verifiedStmt->execute();
    $verifiedCount = $verifiedStmt->fetch(PDO::FETCH_ASSOC)['verified_count'];
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Simulate system health status
$systemHealth = "Good"; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link rel="stylesheet" href="styles.css">
    <title>Users</title>
    <style>
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: fixed;
            height: 100%;
        }

        .sidebar .menu {
            padding: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .sidebar .logout {
            margin: 20px;
        }

        .sidebar .profile {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #495057;
        }

        .sidebar .profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 90px;
        }

        .sidebar .profile .name {
            font-size: 16px;
            font-weight: bold;
            color: white;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }

        .real-time-clock-container {
            background-color: #f8f9fa;
            padding: 10px;
            font-size: 20px;
            font-weight: bold;
            display: flex;
            justify-content: space-around; 
            align-items: center;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .info-box-container {
            display: flex;
            justify-content: space-around;
            gap: 15px; 
            margin-top: 20px;
            flex-wrap: wrap; 
        }

        .system-health-box,
        .verified-users-box,
        .new-box {
            width: 290px; 
            height: 200px; 
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 10px; 
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            transition: transform 0.2s ease-in-out; 
        }

        .system-health-box:hover,
        .verified-users-box:hover,
        .new-box:hover {
            transform: translateY(-5px);
        }

        .system-health-box i,
        .verified-users-box i,
        .new-box i {
            font-size: 40px;
            margin-bottom: 10px;
            color: #007bff;
        }

        .system-health-box.good {
            color: green;
        }

        .system-health-box.bad {
            color: red;
        }

        .verified-users-box span,
        .system-health-box span,
        .new-box span {
            font-size: 16px;
            font-weight: bold;
            color: #333; 
        }

        .table .profile-img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .account-status.verified {
            color: green;
            font-weight: bold;
        }

        .account-status.not-verified {
            color: red;
            font-weight: bold;
        }

        .table-container {
            margin-top: 20px;
            background-color: #f8f9fa;
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px; 
        }

        .table {
            margin: 0; 
            border-radius: 10px; 
            overflow: hidden;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        .sidebar.dark-mode {
            background-color: #23272b;
        }

        .sidebar.dark-mode .menu a {
            color: #ffffff;
        }

        .sidebar.dark-mode .menu a:hover,
        .sidebar.dark-mode .menu a.active {
            background-color: #3a3f44;
        }

        .content.dark-mode {
            background-color: #1e1e1e;
            color: #ffffff;
        }

        .form-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .real-time-clock-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .info-box-container .system-health-box.dark-mode,
        .info-box-container .verified-users-box.dark-mode,
        .info-box-container .new-box.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .table-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .table.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .chart-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        /* General Dark Mode Styles */
        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        .sidebar.dark-mode {
            background-color: #23272b;
        }

        .sidebar.dark-mode .menu a {
            color: #ffffff;
        }

        .sidebar.dark-mode .menu a:hover,
        .sidebar.dark-mode .menu a.active {
            background-color: #3a3f44;
        }

        .content.dark-mode {
            background-color: #1e1e1e;
            color: #ffffff;
        }

        .real-time-clock-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff; /* Adjust text color for better visibility */
        }

        .info-box-container .system-health-box.dark-mode,
        .info-box-container .verified-users-box.dark-mode,
        .info-box-container .new-box.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff; /* Adjust text color for better visibility */
        }

        .info-box-container .system-health-box.dark-mode span,
        .info-box-container .verified-users-box.dark-mode span,
        .info-box-container .new-box.dark-mode span {
            color: #ffffff; /* Ensure text color adjusts in Dark Mode */
        }

        .table-container.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .table.dark-mode {
            background-color: #2c2c2c;
            color: #ffffff;
        }

        .table.dark-mode th,
        .table.dark-mode td {
            color: #ffffff; /* Adjust text color for better visibility */
        }

        .table.dark-mode tbody tr:hover {
            background-color: #3a3f44; /* Add hover effect for rows in Dark Mode */
        }

        /* Highlight for Verified and Not Verified Accounts */
        .account-status.verified {
            color: #28a745; /* Green for verified */
            font-weight: bold;
            background-color: rgba(40, 167, 69, 0.1); /* Light green background */
            padding: 5px 10px;
            border-radius: 5px;
        }

        .account-status.not-verified {
            color: #dc3545; /* Red for not verified */
            font-weight: bold;
            background-color: rgba(220, 53, 69, 0.1); /* Light red background */
            padding: 5px 10px;
            border-radius: 5px;
        }

        /* Default Light Mode Table Styles */
        .table-container {
            background-color: #ffffff; /* Light background */
            color: #000000; /* Dark text */
        }

        .table {
            background-color: #ffffff; /* Light background */
            color: #000000; /* Dark text */
        }

        .table th,
        .table td {
            color: #000000; /* Dark text */
        }

        .table tbody tr:hover {
            background-color: #f1f1f1; 
        }

        /* Dark Mode Table Styles */
        .table-container.dark-mode {
            background-color: #2c2c2c; /* Dark background */
            color: #ffffff; /* Light text */
        }

        .table.dark-mode {
            background-color: #2c2c2c; /* Dark background */
            color: #ffffff; /* Light text */
        }

        .table.dark-mode th,
        .table.dark-mode td {
            color: #ffffff; /* Light text */
        }

        .table.dark-mode tbody tr:hover {
            background-color: #3a3f44; /* Dark hover effect */
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="menu">
            <h4 class="text-center">Dashboard Menu</h4>
            <a href="index.html">Home</a>
            <a href="users.php" class="active">Users</a>
            <a href="analytics.php">Analytics</a>
            <a href="settings.php">Settings</a>
        </div>
        <div class="profile">
            <img id="profileImage" src="<?= htmlspecialchars($profile['profile_image'] ?? 'default-profile.png') ?>" alt="Profile Picture">
            <div id="profileName" class="name">
                <?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?>
            </div>
        </div>
        <div class="logout">
            <a href="logout.php" class="btn btn-danger btn-block">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <!-- Real-Time Clock -->
        <div class="real-time-clock-container">
            <span>Dashboard</span> 
            <span id="realTimeClock"></span> 
        </div>

        <!-- Info Boxes -->
        <div class="info-box-container">
            <!-- Verified Users -->
            <div class="verified-users-box">
                <i class="fas fa-user-check"></i>
                <span>Total Verified Users: <span id="verifiedCount"><?= htmlspecialchars($verifiedCount) ?></span></span>
            </div>

            <!-- New Box -->
            <div class="new-box">
                <i class="fas fa-chart-line"></i>
                <span><strong>Performance:</strong> <span id="performance">Loading...</span></span>
            </div>

            <!-- System Health -->
            <div id="systemHealthBox" class="system-health-box <?= strtolower($systemHealth) ?>">
                <i class="fas fa-heartbeat"></i> 
                <span><strong>System Health:</strong> <?= htmlspecialchars($systemHealth) ?></span>
            </div>
        </div>

        <!-- User Table -->
        <div class="table-container">
            <h1>📚 User Accounts 🎓</h1>
            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Profile</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Account Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td>
                                <?php if ($user['profile_image']): ?>
                                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image" class="profile-img">
                                <?php else: ?>
                                    No Image
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($user['first_name']) ?></td>
                            <td><?= htmlspecialchars($user['last_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td class="<?= $user['is_verified'] ? 'account-status verified' : 'account-status not-verified' ?>">
                                <?= $user['is_verified'] ? 'Verified' : 'Not Verified' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('realTimeClock').textContent = now.toLocaleDateString('en-US', options);
        }
        setInterval(updateClock, 1000);
        updateClock(); // Initialize clock immediately

        // Load Dark Mode Preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            document.querySelector('.sidebar').classList.add('dark-mode');
            document.querySelector('.content').classList.add('dark-mode');
        }
    </script>
    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;

        // Function to enable Dark Mode
        function enableDarkMode() {
            body.classList.add('dark-mode');
            document.querySelector('.sidebar').classList.add('dark-mode');
            document.querySelector('.content').classList.add('dark-mode');
            const formContainer = document.querySelector('.form-container');
            if (formContainer) formContainer.classList.add('dark-mode');
            const tableContainer = document.querySelector('.table-container');
            if (tableContainer) tableContainer.classList.add('dark-mode');
            const chartContainer = document.querySelector('.chart-container');
            if (chartContainer) chartContainer.classList.add('dark-mode');
            const realTimeClock = document.querySelector('.real-time-clock-container');
            if (realTimeClock) realTimeClock.classList.add('dark-mode');
            const infoBoxes = document.querySelectorAll('.info-box-container .system-health-box, .info-box-container .verified-users-box, .info-box-container .new-box');
            infoBoxes.forEach(box => box.classList.add('dark-mode'));
            const table = document.querySelector('.table');
            if (table) table.classList.add('dark-mode');
        }

        // Function to disable Dark Mode
        function disableDarkMode() {
            body.classList.remove('dark-mode');
            document.querySelector('.sidebar').classList.remove('dark-mode');
            document.querySelector('.content').classList.remove('dark-mode');
            const formContainer = document.querySelector('.form-container');
            if (formContainer) formContainer.classList.remove('dark-mode');
            const tableContainer = document.querySelector('..table-container');
            if (tableContainer) tableContainer.classList.remove('dark-mode');
            const chartContainer = document.querySelector('.chart-container');
            if (chartContainer) chartContainer.classList.remove('dark-mode');
            const realTimeClock = document.querySelector('.real-time-clock-container');
            if (realTimeClock) realTimeClock.classList.remove('dark-mode');
            const infoBoxes = document.querySelectorAll('.info-box-container .system-health-box, .info-box-container .verified-users-box, .info-box-container .new-box');
            infoBoxes.forEach(box => box.classList.remove('dark-mode'));
            const table = document.querySelector('.table');
            if (table) table.classList.remove('dark-mode');
        }

        // Load Dark Mode Preference on Page Load
        if (localStorage.getItem('darkMode') === 'enabled') {
            enableDarkMode();
            if (darkModeToggle) darkModeToggle.checked = true;
        }

        // Toggle Dark Mode on Switch Change
        if (darkModeToggle) {
            darkModeToggle.addEventListener('change', () => {
                if (darkModeToggle.checked) {
                    enableDarkMode();
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    disableDarkMode();
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
        }
    </script>
</body>

</html>