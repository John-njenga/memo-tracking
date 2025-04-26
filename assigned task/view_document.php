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

// Get the document ID from the URL
if (isset($_GET['id'])) {
    $documentId = $_GET['id'];

    // Fetch the document details from the database
    $sql = "SELECT * FROM documents WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $documentId);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Reset */
        body, h1, h2, p {
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #495057;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #007bff;
            font-size: 26px;
            margin-bottom: 20px;
        }

        /* Modal Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9998;
        }

        /* Modal Card */
        .modal-card {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 800px;
            z-index: 9999;
            padding: 20px;
            font-size: 16px;
            max-height: 98%;
        }

        /* Modal Header */
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f1f1;
            padding-bottom: 10px;
        }

        .modal-header h2 {
            font-size: 24px;
            color: #333;
        }

        .close-btn {
            cursor: pointer;
            background: none;
            border: none;
            font-size: 22px;
            font-weight: bold;
            color: #ff4d4f;
        }

        /* Table Styling */
        .modal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .modal-table th,
        .modal-table td {
            padding: 12px 15px;
            text-align: left;
            border-top: 1px solid #ddd;
        }

        .modal-table th {
            background-color: white;
            color: black;
        }

        .modal-table td {
            background-color: #f8f9fa;
        }

        /* Button Styling */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            margin: 10px 5px;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
        }

        .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }

        .btn-primary:hover,
        .btn-danger:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .modal-card {
                width: 100%;
                margin: 0;
            }

            .modal-table th, .modal-table td {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>
<body>

<!-- Modal Overlay -->
<div id="modalOverlay" class="modal-overlay"></div>

<!-- Modal Card -->
<div id="modalCard" class="modal-card">
    <div class="modal-header">
        <h2>Document Details</h2>
        <button class="close-btn" onclick="redirectToIndex()">×</button>
    </div>

    <table class="modal-table">
        <tr>
            <th>Title:</th>
            <td><?php echo htmlspecialchars($document['title']); ?></td>
        </tr>
        <tr>
            <th>File Reference:</th>
            <td><?php echo htmlspecialchars($document['file_reference']); ?></td>
        </tr>
        <tr>
            <th>Start Date:</th>
            <td><?php echo htmlspecialchars($document['start_date']); ?></td>
        </tr>
        <tr>
            <th>End Date:</th>
            <td><?php echo htmlspecialchars($document['end_date']); ?></td>
        </tr>
        <tr>
            <th>Status:</th>
            <td><?php echo htmlspecialchars($document['status']); ?></td>
        </tr>
        <tr>
            <th>Department:</th>
            <td><?php echo htmlspecialchars($document['department']); ?></td>
        </tr>
        <tr>
            <th>Category:</th>
            <td><?php echo htmlspecialchars($document['category']); ?></td>
        </tr>
        <tr>
            <th>Parties:</th>
            <td><?php echo htmlspecialchars($document['parties']); ?></td>
        </tr>
        <tr>
            <th>Comments:</th>
            <td><?php echo htmlspecialchars($document['comments']); ?></td>
        </tr>
        <tr>
            <th>Type:</th>
            <td><?php echo htmlspecialchars($document['type']); ?></td>
        </tr>
        <tr>
            <th>Document File:</th>
            <td>
                <?php
                if (!empty($document['file_path'])) {
                    echo "<a href='" . htmlspecialchars($document['file_path']) . "' target='_blank' class='btn btn-primary'>Download Document</a>";
                } else {
                    echo "No file uploaded.";
                }
                ?>
            </td>
        </tr>
    </table>

    <div style="text-align: center; margin-top: 20px;">
        <a href="update.php?id=<?php echo $document['id']; ?>" class="btn btn-primary">Edit</a>
        <a href="delete_document.php?id=<?php echo $document['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this document?');">Delete</a>
        <a href="track_document.php?id=<?php echo $document['id']; ?>&ref=<?php echo $document['file_reference']; ?>" class="btn btn-info">Track Document</a>

    </div>
</div>

<script>
    // Redirect to index page
    function redirectToIndex() {
        window.location.href = 'index1.php';
    }
</script>

</body>
</html>
