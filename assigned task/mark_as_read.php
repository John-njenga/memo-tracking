<?php
$servername = "localhost";
$username = "root"; // Change accordingly
$password = "";
$dbname = "strategic_plan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (isset($_POST['id'])) {
    $notifId = $_POST['id'];

    // Update the notification's is_read status in the database
    $updateSql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("i", $notifId);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
    $stmt->close();
}
?>



