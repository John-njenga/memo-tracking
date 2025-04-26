<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "strategic_plan";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];  // Get the document ID from the request
$sql = "DELETE FROM documents WHERE id = $id";  // Query to delete the document

// Perform the deletion query
if ($conn->query($sql) === TRUE) {
  $message = "Document deleted successfully";
  $type = "success";
} else {
  $message = "Error deleting document: " . $conn->error;
  $type = "error";
}

$conn->close();

// Redirect back to index1.php with the message and type as URL parameters
header("Location: index1.php?message=" . urlencode($message) . "&type=" . urlencode($type));
exit(); // Make sure to call exit to stop further script execution
?>
