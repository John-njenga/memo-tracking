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

// Fetch document data if ID is provided
$document = [];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch document details
    $sql = "SELECT * FROM documents WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $document = $result->fetch_assoc();
    } else {
        echo "Document not found.";
        exit;
    }
} else {
    echo "No document ID provided.";
    exit;
}

// Check if form is submitted
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $fileReference = $_POST['file_reference'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $status = $_POST['status'];
        $department = $_POST['department'];
        $category = $_POST['category'];
        $parties = $_POST['parties'];
        $comments = $_POST['comments'];

        // Prepare and execute SQL query
        $sql = "UPDATE documents SET title = ?, file_reference = ?, start_date = ?, end_date = ?, status = ?, department = ?, category = ?, parties = ?, comments = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssi", $title, $fileReference, $startDate, $endDate, $status, $department,$category,$parties, $comments, $id);

        if ($stmt->execute()) {
            $success = true; // Flag success for later use
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "No document ID provided.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Document</title>
    <style>
        /* Base styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .modal-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .modal-header {
            margin-bottom: 20px;
            text-align: center;
        }

        .modal-header h2 {
            font-size: 1.8rem;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
            width: 100%;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1 1 calc(50% - 15px);
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            font-weight: bold;
            color: #ffffff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        /* Close button */
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.2rem;
            color: #888;
            border: none;
            background: none;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #555;
        }
    </style>
</head>
<body>

<?php if ($success): ?>
    <!-- Success Alert -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            alert("Document updated successfully!");
            window.location.href = "index1.php";
        });
    </script>
<?php endif; ?>

<div class="modal-overlay">
    <div class="modal-card">
        <button class="close-btn" onclick="window.location.href='index1.php'">&times;</button>
        <div class="modal-header">
            <h2>Edit Document</h2>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($document['id']); ?>">

            <div class="form-row">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($document['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="file_reference">File Reference</label>
                    <input type="text" id="file_reference" name="file_reference" value="<?php echo htmlspecialchars($document['file_reference']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($document['start_date']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($document['end_date']); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="New" <?php echo ($document['status'] === 'New' ? 'selected' : ''); ?>>New</option>
                        <option value="In Progress" <?php echo ($document['status'] === 'In Progress' ? 'selected' : ''); ?>>In Progress</option>
                        <option value="Done" <?php echo ($document['status'] === 'Done' ? 'selected' : ''); ?>>Done</option>
                        <option value="Overdue" <?php echo ($document['status'] === 'Overdue' ? 'selected' : ''); ?>>Overdue</option>
                        <option value="Renewal" <?php echo ($document['status'] === 'Renewal' ? 'selected' : ''); ?>>Renewal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($document['department']); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($document['category']); ?>" required>
            </div>

            <div class="form-group">
                <label for="parties">Parties</label>
                <input type="text" id="parties" name="parties" value="<?php echo htmlspecialchars($document['parties']); ?>" required>
            </div>

            <div class="form-group">
                <label for="comments">Comments</label>
                <textarea id="comments" name="comments" rows="4"><?php echo htmlspecialchars($document['comments']); ?></textarea>
            </div>

            <button type="submit" class="btn">Update Document</button>
        </form>
    </div>
</div>

</body>
</html>

