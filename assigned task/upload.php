<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "strategic_plan";

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Ensure the user is logged in and the username exists in the session
if (!isset($_SESSION['username'])) {
    die("You must be logged in to upload a file.");
}

$author = $_SESSION['username']; // Get the logged-in user's name

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging step: check if 'type' is being passed from the form
    if (!isset($_POST["type"])) {
        echo "Error: 'type' is not set in the form.<br>";
    } else {
        echo "Type value: " . $_POST["type"] . "<br>";
    }

    // Retrieve form data
    $fileReference = $_POST["fileReference"];
    $title = $_POST["title"];
    $type = isset($_POST["type"]) ? $_POST["type"] : 'document'; // Set default if not set
    $startDate = $_POST["startDate"];
    $endDate = $_POST["endDate"];
    $status = $_POST["status"];
    $department = $_POST["department"];
    $category = $_POST["category"];
    $parties = $_POST["parties"];
    $comments = $_POST["comments"];
    $fileName = $_FILES["fileUpload"]["name"];
    $fileTmpName = $_FILES["fileUpload"]["tmp_name"];
    $uploadDir = "uploads/";

    // Check if document already exists in the database
    $sqlCheck = "SELECT * FROM documents WHERE file_reference = ? AND title = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("ss", $fileReference, $title);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows > 0) {
        // Document already exists, show a message
        $message = "The document has already been uploaded.";
        $type = "error";
    } else {
        // Create uploads directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // File upload path
        $filePath = $uploadDir . basename($fileName);

        // Move the uploaded file
        if (move_uploaded_file($fileTmpName, $filePath)) {
            // Insert form data into the database
            $sql = "INSERT INTO documents (file_reference, title, type, start_date, end_date, status, department, comments, file_path, author,category, parties) 
                    VALUES (?, ?, ?, ?, ?, ? , ?, ?, ?, ?, ? ,?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssss", $fileReference, $title, $type, $startDate, $endDate, $status, $department, $comments, $filePath, $author,$category,$parties);

            if ($stmt->execute()) {
                $message = "File uploaded and data saved successfully!";
                $type = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $type = "error";
            }
            $stmt->close();
        } else {
            $message = "Failed to upload the file.";
            $type = "error";
        }
    }

    // Close the connection
    $conn->close();

    // Redirect back to index1.php with a message
    // You can use URL parameters to pass the message
    header("Location: index1.php?message=" . urlencode($message) . "&type=" . urlencode($type));
    exit(); // Make sure to call exit to stop further script execution
}
?>

<!-- HTML form with auto-filled fields -->
<form method="POST" action="upload.php" enctype="multipart/form-data">
    <label for="fileReference">File Reference</label>
    <input type="text" name="fileReference" required>

    <label for="title">Title</label>
    <input type="text" name="title" required>

  
        <label for="type">Document Type</label>
        <input type="text" name="type">

    <input type="hidden" name="author" value="<?php echo $author; ?>">

    <label for="startDate">Start Date</label>
    <input type="date" name="startDate" required>

    <label for="endDate">End Date</label>
    <input type="date" name="endDate" required>

    <label for="status">Status</label>
    <input type="text" name="status" required>

    <label for="department">Department</label>
    <input type="text" name="department" required>

    <label for="category">Category</label>
    <input type="text" name="category" required>

    <label for="parties">Parties</label>
    <input type="text" name="parties" required>


    <label for="comments">Comments</label>
    <textarea name="comments"></textarea>

    <label for="fileUpload">Upload File</label>
    <input type="file" name="fileUpload" required>

    <button type="submit">Upload</button>
</form>
