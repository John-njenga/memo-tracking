<?php
header("Content-Type: text/html; charset=UTF-8");

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "strategic_plan"; 

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Fetch document types from `document_types` table
$sql = "SELECT name FROM document_types";
$result = $conn->query($sql);

$options = '<option value="">Select Document type</option>'; // Keep default option

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Append each option dynamically
        $options .= '<option value="' . htmlspecialchars($row["name"]) . '">' . htmlspecialchars($row["name"]) . '</option>';
    }
}

echo $options; // Send the options to AJAX
$conn->close();
?>
