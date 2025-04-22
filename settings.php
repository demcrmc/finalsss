<?php
session_start();
include 'dbconnection.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's details
try {
    $email = $_SESSION['email'];
    $stmt = $connection->prepare("SELECT first_name, last_name, profile_image FROM user_table WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission for updating username and profile picture
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $profile_image = $user['profile_image']; 

    // Handle profile image upload
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $target_file = $target_dir . basename($_FILES["profileImage"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["profileImage"]["tmp_name"]);
        if ($check === false) {
            $error = "File is not an image.";
        } elseif ($_FILES["profileImage"]["size"] > 2 * 1024 * 1024) {
            $error = "File size must be less than 2MB.";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["profileImage"]["tmp_name"], $target_file)) {
                if (!empty($user['profile_image']) && $user['profile_image'] !== 'default-profile.png' && file_exists($user['profile_image'])) {
                    unlink($user['profile_image']); // Delete the old image
                }
                $profile_image = $target_file; // Update the profile image path
            } else {
                $error = "Error uploading file.";
            }
        }
    }

    // Update the user's details in the database
    if (!isset($error)) {
        try {
            $stmt = $connection->prepare("UPDATE user_table SET first_name = :first_name, last_name = :last_name, profile_image = :profile_image WHERE email = :email");
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':profile_image', $profile_image);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Refresh the user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['profile_image'] = $profile_image;

            $success = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Settings</title>
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

        .sidebar a.active,
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
            width: calc(100% - 250px);
        }

        .form-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-group label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .dark-mode {
            background-color: #343a40;
            color: white;
        }

        .dark-mode .form-container {
            background-color: #495057;
            color: white;
        }

        .dark-mode .sidebar {
            background-color: #23272b;
        }

        .dark-mode .sidebar .menu a {
            color: white;
        }

        .dark-mode .sidebar .menu a:hover,
        .dark-mode .sidebar .menu a.active {
            background-color: #3a3f44;
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
    <style>
        /* Slider Toggle Styles */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 25px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 25px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 19px;
            width: 19px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #007bff;
        }

        input:checked + .slider:before {
            transform: translateX(25px);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="menu">
            <h4>Dashboard Menu</h4>
            <a href="index.html">Home</a>
            <a href="users.php">Users</a>
            <a href="analytics.php">Analytics</a>
            <a href="settings.php" class="active">Settings</a>
        </div>
        <div class="profile">
            <img src="<?= htmlspecialchars($user['profile_image'] ?? 'default-profile.png') ?>" alt="Profile Picture">
            <div class="name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
        </div>
        <div class="logout">
            <a href="logout.php" class="btn btn-danger btn-block">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="form-container">
            <h2 class="text-center">Settings</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <form method="post" action="settings.php" enctype="multipart/form-data">
                <div class="form-group text-center">
                    <img src="<?= htmlspecialchars($user['profile_image'] ?? 'default-profile.png') ?>" alt="Profile Picture" class="rounded-circle" width="100" height="100">
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="profileImage">Profile Picture</label>
                    <input type="file" class="form-control" id="profileImage" name="profileImage" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Update Profile</button>
            </form>
            <hr>
            <!-- Dark Mode Toggle -->
            <div class="form-group text-center">
                <label for="darkModeToggle" class="form-label">Dark Mode</label>
                <label class="switch">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

    <script>
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