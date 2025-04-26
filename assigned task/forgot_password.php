<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "strategic_plan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50)); // Generate a secure token
        $expires = date("U") + 3600; // 1 hour expiry

        // Delete existing tokens for this user
        $conn->query("DELETE FROM password_reset WHERE email = '$email'");

        // Insert reset token into the database
        $sql = "INSERT INTO password_reset (email, token, expires) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        // Email payload sent to JavaScript for EmailJS API
        $response = [
            'status' => 'success',
            'resetLink' => "http://localhost/reset_password.php?token=" . $token,
            'email' => $email
        ];
        echo json_encode($response);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No account found with that email.']);
        exit;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="email"] {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
        }
        button {
            background: #007bff;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .success-message {
            color: #28a745;
            text-align: center;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Forgot Password</h2>
        <form id="forgotPasswordForm">
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Reset Password</button>
        </form>
        <p id="feedback" class="success-message" style="display:none;"></p>
    </div>

    <script>
        emailjs.init("your_user_id"); // Replace 'your_user_id' with your EmailJS user ID

        document.getElementById('forgotPasswordForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const email = document.getElementById('email').value;

            // Send AJAX request to the PHP script
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const resetLink = data.resetLink;

                    // Send email using EmailJS
                    emailjs.send("your_service_id", "your_template_id", {
                        to_email: data.email,
                        reset_link: resetLink
                    })
                    .then(() => {
                        document.getElementById('feedback').textContent = "Password reset email sent. Check your inbox.";
                        document.getElementById('feedback').style.display = 'block';
                    })
                    .catch(err => {
                        document.getElementById('feedback').textContent = "Failed to send email via EmailJS. Please try again.";
                        document.getElementById('feedback').classList.replace('success-message', 'error-message');
                        document.getElementById('feedback').style.display = 'block';
                    });
                } else {
                    document.getElementById('feedback').textContent = data.message;
                    document.getElementById('feedback').classList.replace('success-message', 'error-message');
                    document.getElementById('feedback').style.display = 'block';
                }
            })
            .catch(err => {
                document.getElementById('feedback').textContent = "An error occurred. Please try again.";
                document.getElementById('feedback').classList.replace('success-message', 'error-message');
                document.getElementById('feedback').style.display = 'block';
            });
        });
    </script>
</body>
</html>
