<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "strategic_plan";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get document ID from URL
$documentId = isset($_GET['id']) ? $_GET['id'] : '';

$sql = "SELECT * FROM documents WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $documentId);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 700px;
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #0621e8;
            border-color: #0621e8;
        }
        .btn-primary:hover {
            background-color: #0418b5;
            border-color: #0418b5;
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

<div class="container">
    <div class="card p-4">
        <h3 class="text-center mb-4"><i class="fa-solid fa-file"></i> Document Details</h3>
        <button class="close-btn" onclick="window.location.href='report.php'">&times;</button>

        <?php if ($document): ?>
            <p><strong>Document Name:</strong> <?php echo htmlspecialchars($document['type']); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge bg-<?php echo ($document['status'] == 'Approved') ? 'success' : 'warning'; ?>">
                    <?php echo htmlspecialchars($document['status']); ?>
                </span>
            </p>

            <?php if (!empty($document['file_path'])): ?>
                <div class="text-center mt-3">
                    <a href="<?php echo htmlspecialchars($document['file_path']); ?>" target="_blank" class="btn btn-primary">
                        <i class="fa-solid fa-file-pdf"></i> Open Document
                    </a>
                </div>
            <?php else: ?>
                <p class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> No document available.</p>
            <?php endif; ?>

        <?php else: ?>
            <p class="text-danger"><i class="fa-solid fa-exclamation-circle"></i> Document not found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
