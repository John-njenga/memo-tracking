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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documentId = $_POST['document_id'];
    $referenceNumber = $_POST['reference_number'];
    $comment = $_POST['comment'];
    $recipients = $_POST['recipients']; // Array of recipient emails

    // Insert tracking comment
    $sql = "INSERT INTO tracking (document_id, reference_number, comment) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $documentId, $referenceNumber, $comment);

    if ($stmt->execute()) {
        $trackingId = $stmt->insert_id; // Get inserted tracking comment ID

        // Insert recipients into recipients table
        if (!empty($recipients)) {
            $recipientSql = "INSERT INTO tracking_recipients (tracking_id, email) VALUES (?, ?)";
            $recipientStmt = $conn->prepare($recipientSql);

            foreach ($recipients as $recipient) {
                $recipientStmt->bind_param("is", $trackingId, $recipient);
                $recipientStmt->execute();
            }
        }

        header("Location: track_document.php?id=$documentId&ref=$referenceNumber");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tracking Comment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function addRecipient() {
            const recipientList = document.getElementById("recipient-list");
            const inputGroup = document.createElement("div");
            inputGroup.className = "input-group mb-2";

            const input = document.createElement("input");
            input.type = "email";
            input.name = "recipients[]";
            input.className = "form-control";
            input.placeholder = "Enter recipient email";
            input.required = true;

            const removeBtn = document.createElement("button");
            removeBtn.type = "button";
            removeBtn.className = "btn btn-danger";
            removeBtn.innerText = "Remove";
            removeBtn.onclick = function() {
                recipientList.removeChild(inputGroup);
            };

            inputGroup.appendChild(input);
            inputGroup.appendChild(removeBtn);
            recipientList.appendChild(inputGroup);
        }
    </script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center">Add Tracking Comment</h2>

    <form action="add_tracking.php" method="POST" class="mt-4">
        <input type="hidden" name="document_id" value="<?php echo $_GET['id']; ?>">
        <input type="hidden" name="reference_number" value="<?php echo $_GET['ref']; ?>">

        <div class="mb-3">
            <label class="form-label">Comment</label>
            <textarea class="form-control" name="comment" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Recipients</label>
            <div id="recipient-list"></div>
            <button type="button" class="btn btn-primary mt-2" onclick="addRecipient()">Add Recipient</button>
        </div>

        <button type="submit" class="btn btn-success">Submit</button>
        <a href="track_document.php?id=<?php echo $_GET['id']; ?>&ref=<?php echo $_GET['ref']; ?>" class="btn btn-secondary">Back</a>
    </form>
</div>

</body>
</html>
