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

// Get document status and type from URL parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$documentType = isset($_GET['type']) ? $_GET['type'] : '';

// Fetch documents based on status
$documentsByStatus = [];
if ($status) {
    $stmt = $conn->prepare("SELECT * FROM documents WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $documentsByStatus = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch documents based on type
$documentsByType = [];
if ($documentType) {
    $stmt = $conn->prepare("SELECT * FROM documents WHERE type = ?");
    $stmt->bind_param("s", $documentType);
    $stmt->execute();
    $documentsByType = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            display: flex;
        }
        .sidebar {
            width: 220px;
            background-color: #0621e8;
            color: #fff;
            padding: 20px;
            position: fixed;
            height: 100vh;
            border-radius: 0 15px 15px 0;
            top: 0;
            left: 0;
        }
        .sidebar h5 {
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #3a0ca3;
        }
        .content {
            margin-left: 240px;
            padding: 20px;
            width: calc(100% - 240px);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h5><i class="fa-solid fa-dove"></i> Swift Tracking</h5>
        <a href="report.php"><i class="fa-brands fa-slack"></i> Report Dashboard</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container mt-4">
            
            <?php if ($status): ?>
                <h2 class="mb-4">Documents for Status: <?php echo htmlspecialchars($status); ?></h2>
                <?php if ($documentsByStatus): ?>
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Department</th>
                                <th>Category</th>
                                <th>Parties</th>
                                <th>Comments</th>
                                <th>Download</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentsByStatus as $document): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($document['title']); ?></td>
                                <td><?php echo htmlspecialchars($document['type']); ?></td>
                                <td><?php echo htmlspecialchars($document['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($document['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($document['status']); ?></td>
                                <td><?php echo htmlspecialchars($document['department']); ?></td>
                                <td><?php echo htmlspecialchars($document['category']); ?></td>
                                <td><?php echo htmlspecialchars($document['parties']); ?></td>
                                <td><?php echo htmlspecialchars($document['comments']); ?></td>
                                <td>
                                    <a href="generate_pdf.php?id=<?php echo $document['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-download"></i> Download
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="alert alert-warning">No documents found for this status.</p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($documentType): ?>
                <h2 class="mt-5">Documents of Type: <?php echo htmlspecialchars($documentType); ?></h2>
                <?php if ($documentsByType): ?>
                    <table class="table table-bordered mt-3">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Document Name</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documentsByType as $document): ?>
                            <tr>
                                <td><?php echo $document['id']; ?></td>
                                <td><?php echo htmlspecialchars($document['type']); ?></td>
                                <td><?php echo htmlspecialchars($document['status']); ?></td>
                                <td><?php echo htmlspecialchars($document['uploaded_at']); ?></td>
                                <td>
                                    <a href="document_viewer.php?id=<?php echo $document['id']; ?>" class="btn btn-primary">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="alert alert-warning">No documents found for this type.</p>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
