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

// Get document ID and reference number from URL
if (isset($_GET['id']) && isset($_GET['ref'])) {
    $documentId = $_GET['id'];
    $referenceNumber = $_GET['ref'];

    // Fetch tracking data
    $sql = "SELECT t.comment, t.timestamp, r.email, d.start_date, d.end_date FROM tracking t
            LEFT JOIN tracking_recipients r ON t.id = r.tracking_id
            LEFT JOIN documents d ON t.document_id = d.id
            WHERE t.document_id = ? ORDER BY t.timestamp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $documentId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "No document ID provided.";
    exit;
}

$currentDate = new DateTime();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Tracking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 20px auto;
        }
        .timeline::after {
            content: '';
            position: absolute;
            width: 4px;
            background: #007bff;
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -2px;
        }
        .timeline-item {
            padding: 10px 20px;
            position: relative;
            width: 50%;
            text-align: left;
        }
        .left { left: 0; }
        .right { left: 50%; }
        .timeline-item::after {
            content: '\F111';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            width: 20px;
            height: 20px;
            right: -10px;
            background: #007bff;
            border-radius: 50%;
            top: 15px;
        }
        .right::after { left: -10px; }
        .timeline-content {
            background: #fff;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .recipient {
            font-size: 0.9em;
            color: #d63384;
            font-weight: bold;
        }
        .status {
            font-weight: bold;
        }
        .status.overdue { color: red; }
        .status.due-soon { color: orange; }
        .status.on-track { color: green; }
        .sidebar {
            width: 200px;
            background-color: #0621e8;
            color: #fff;
            padding: 20px;
            position: fixed;
            height: 95vh;
            margin: 3px;
            border-radius: 0 15px 15px 0;
        }
        .sidebar h4 {
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
    </style>
</head>
<body class="bg-light">
<div class="sidebar">
        <h5 style="padding: 5px;"><i class="fa-solid fa-dove"></i> Swift Tracking</h5>
        <a href="index1.php">
        <i class="fa-brands fa-slack"></i> Dashboard
        </a>
    </div>
<div class="container mt-4">
    <h2 class="text-center text-primary">Document Tracking</h2>
    <h5 class="text-center">Reference Number: <strong><?php echo htmlspecialchars($referenceNumber); ?></strong></h5>
    <div class="timeline">
        <?php
        if ($result->num_rows > 0) {
            $count = 0;
            while ($row = $result->fetch_assoc()) {
                $side = ($count % 2 == 0) ? "left" : "right";
                $startDate = new DateTime($row['start_date']);
                $endDate = new DateTime($row['end_date']);
                $daysRemaining = $currentDate->diff($endDate)->format('%r%a');
                
                $statusClass = "on-track";
                $statusText = "On Track";
                if ($daysRemaining < 0) {
                    $statusClass = "overdue";
                    $statusText = "Overdue";
                } elseif ($daysRemaining <= 5) {
                    $statusClass = "due-soon";
                    $statusText = "Due Soon";
                }
                
                echo "<div class='timeline-item $side'>
                        <div class='timeline-content'>
                            <p><strong>Comment:</strong> " . htmlspecialchars($row['comment']) . "</p>
                            <p class='recipient'>Recipient: " . htmlspecialchars($row['email']) . "</p>
                            <p><strong>Start Date:</strong> " . htmlspecialchars($row['start_date']) . "</p>
                            <p><strong>End Date:</strong> " . htmlspecialchars($row['end_date']) . "</p>
                            <p><strong>Days Remaining:</strong> " . $daysRemaining . "</p>
                            <p class='status $statusClass'><strong>Status:</strong> " . $statusText . "</p>
                            <small><i class='fa fa-clock'></i> " . htmlspecialchars($row['timestamp']) . "</small>
                        </div>
                      </div>";
                $count++;
            }
        } else {
            echo "<p class='text-center'>No tracking updates found.</p>";
        }
        ?>
    </div>
    <div class="text-center mt-3">
        <a href="add_tracking.php?id=<?php echo $documentId; ?>&ref=<?php echo $referenceNumber; ?>" class="btn btn-primary">Add Comment</a>
        <a href="view_document.php?id=<?php echo $documentId; ?>" class="btn btn-secondary">Back to Document</a>
    </div>
</div>
</body>
</html>
