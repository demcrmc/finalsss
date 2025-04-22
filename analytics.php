<!-- filepath: c:\xampp\htdocs\withdashboard_cosep-main\analytics.php -->
<?php
session_start();
include 'dbconnection.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's profile details
try {
    $email = $_SESSION['email'];
    $stmt = $connection->prepare("SELECT first_name, last_name, profile_image FROM user_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch analytics data (example: total users, verified users, etc.)
try {
    $totalUsersStmt = $connection->prepare("SELECT COUNT(*) AS total_users FROM user_table");
    $totalUsersStmt->execute();
    $totalUsers = $totalUsersStmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $verifiedUsersStmt = $connection->prepare("SELECT COUNT(*) AS verified_users FROM user_table WHERE is_verified = 1");
    $verifiedUsersStmt->execute();
    $verifiedUsers = $verifiedUsersStmt->fetch(PDO::FETCH_ASSOC)['verified_users'];

    $unverifiedUsers = $totalUsers - $verifiedUsers;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js for analytics -->
    <title>Analytics</title>
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

        .chart-container {
            margin-top: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
    </style>
    <style>
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
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="menu">
            <h4 class="text-center">Dashboard Menu</h4>
            <a href="index.html">Home</a>
            <a href="users.php">Users</a>
            <a href="analytics.php" class="active">Analytics</a>
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
        <h1>📊 Analytics</h1>

        <!-- Analytics Summary -->
        <div class="row">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <p class="card-text"><?= htmlspecialchars($totalUsers) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Verified Users</h5>
                        <p class="card-text"><?= htmlspecialchars($verifiedUsers) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Unverified Users</h5>
                        <p class="card-text"><?= htmlspecialchars($unverifiedUsers) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="chart-container">
            <canvas id="userChart"></canvas>
        </div>
    </div>

    <script>
        // Chart.js Configuration
        const ctx = document.getElementById('userChart').getContext('2d');
        const userChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Verified Users', 'Unverified Users'],
                datasets: [{
                    label: 'User Verification Status',
                    data: [<?= htmlspecialchars($verifiedUsers) ?>, <?= htmlspecialchars($unverifiedUsers) ?>],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderColor: ['#28a745', '#dc3545'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

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
        }

        // Function to disable Dark Mode
        function disableDarkMode() {
            body.classList.remove('dark-mode');
            document.querySelector('.sidebar').classList.remove('dark-mode');
            document.querySelector('.content').classList.remove('dark-mode');
            const formContainer = document.querySelector('.form-container');
            if (formContainer) formContainer.classList.remove('dark-mode');
            const tableContainer = document.querySelector('.table-container');
            if (tableContainer) tableContainer.classList.remove('dark-mode');
            const chartContainer = document.querySelector('.chart-container');
            if (chartContainer) chartContainer.classList.remove('dark-mode');
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