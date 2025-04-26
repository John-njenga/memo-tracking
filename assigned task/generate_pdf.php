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

// Get document ID from request
$documentId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($documentId <= 0) {
    die("Invalid document ID.");
}

// Fetch document details, including the uploaded file
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $documentId);
$stmt->execute();
$result = $stmt->get_result();
$document = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$document) {
    die("Document not found.");
}

// Extract file details
$fileName = $document['file_path']; // Assuming there's a column 'uploaded_file' in the database
$filePath = "uploads/" . $fileName; // Ensure this matches your file storage path
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.6;
            background-color: #f4f4f4;
        }
        .letterhead {
            text-align: center;
            margin-bottom: 20px;
        }
        .letterhead img {
            max-width: 100px;
            height: auto;
        }
        .letterhead h1 {
            font-size: 30px;
            color: #4CAF50;
            margin: 5px 0;
        }
        .letterhead p {
            font-size: 16px;
            margin: 0;
            color: #777;
        }
        .report-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 800px;
            margin: 10px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: #4CAF50;
        }
        .content p {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .content span {
            font-weight: bold;
        }
        .file-container {
            margin-top: 20px;
            text-align: center;
        }
        iframe {
            width: 100%;
            height: 600px;
            border: none;
        }
        .print-button {
            text-align: center;
            margin-top: 20px;
        }
        .print-button button {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }
        .print-button button:hover {
            background-color: #45a049;
        }
        @media print {
            .print-button { display: none; }
            body {
                background-color: white;
                margin: 0;
                padding: 0;
            }
            .report-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>

    <!-- Letterhead Section -->
    <div class="letterhead">
        <img src="image/kutrrh.jpg" alt="Company Logo">
        <h1>Swift Tracking</h1>
        <p> 0706 215 016 | kutrrh.ac.ke</p>
        <hr>
    </div>

    <!-- Report Container -->
    <div class="report-container">
        <h2>Document <?= htmlspecialchars($document['type']) ?></h2>
        <div class="content">
            <p><span>Title:</span> <?= htmlspecialchars($document['title']) ?></p>
            <p><span>Type:</span> <?= htmlspecialchars($document['type']) ?></p>
            <p><span>Start Date:</span> <?= htmlspecialchars($document['start_date']) ?></p>
            <p><span>End Date:</span> <?= htmlspecialchars($document['end_date']) ?></p>
            <p><span>Status:</span> <?= htmlspecialchars($document['status']) ?></p>
            <p><span>Department:</span> <?= htmlspecialchars($document['department']) ?></p>
            <p><span>Category:</span> <?= htmlspecialchars($document['category']) ?></p>
            <p><span>Parties:</span> <?= htmlspecialchars($document['parties']) ?></p>
            <p><span>Comments:</span> <?= htmlspecialchars($document['comments']) ?></p>
        </div>

    </div>

    <!-- Print Button -->
    <div class="print-button">
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>

</body>
</html>
