<?php
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "strategic_plan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

// Fetch user data based on username from session
$username = $_SESSION['username'];
$query = "SELECT username, email FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $email = $_POST['email'];

    // Update user details in the database
    $update_query = "UPDATE users SET username = ?, email = ? WHERE username = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sss", $new_username, $email, $username);

    if ($update_stmt->execute()) {
        $success_message = "Profile updated successfully.";
        $_SESSION['username'] = $new_username; // Update the session variable
        $user['username'] = $new_username;
        $user['email'] = $email;
    } else {
        $error_message = "Failed to update profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #007bff;
            margin-left: 130px;
            margin-right: 130px;
            margin-top: 5px;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .profile-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
        }
        .profile-card {
            width: 350px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        .user-icon {
            font-size: 4rem;
            color: #007bff;
            margin-bottom: 10px;
        }
        .edit-icon {
            font-size: 1.5rem;
            cursor: pointer;
            color: #007bff;
        }
        .form-section {
            display: none;
        }
        .details-section {
            display: block;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .alert {
            margin-bottom: 20px;
        }
        .sidebar {
            width: 220px;
            background-color: #0621e8;
            color: #fff;
            padding: 20px;
            position: fixed;
            height: 98vh;
            margin: 3px;
            border-radius: 0 15px 15px 0;
        }
        .sidebar h4 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #3a0ca3;
        }
    </style>
</head>
<body>

<div class="sidebar">
        <h5 style="padding: 5px;"><i class="fa-solid fa-dove"></i> Swift Tracking</h5>
        <a href="index1.php">
        <i class="fa-brands fa-slack"></i> Dashboard
        </a>
    </div>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index1.php"><i class="fa-brands fa-slack"></i> Dashboard</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="me-2 text-white">Account</span>
            <i class="fas fa-user-circle text-white" style="font-size: 1.5rem;"></i>
        </div>
    </div>
</nav>

<div class="profile-container">
    <div class="profile-card">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="user-icon">
            <i class="fas fa-user-circle"></i>
        </div>

        <div class="details-section">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <i class="fas fa-edit edit-icon" onclick="toggleForm()"></i>
        </div>

        <div class="form-section">
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleForm() {
        document.querySelector('.details-section').style.display = 'none';
        document.querySelector('.form-section').style.display = 'block';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
