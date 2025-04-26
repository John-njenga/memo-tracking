<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "strategic_plan"; 

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["name"])) {
    $name = trim($_POST["name"]);

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO document_types (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            echo "Document type added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Document name cannot be empty!";
    }
}

$conn->close();
?>
