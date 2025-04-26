<?php
$servername = "localhost";
$username = "root"; // Change accordingly
$password = "";
$dbname = "strategic_plan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check for documents that are due soon, done, or overdue
$today = date("Y-m-d");

// Fetch documents and categorize them
$sql = "SELECT id, title, file_reference, end_date, parties, department, type 
        FROM documents 
        WHERE end_date <= DATE_ADD('$today', INTERVAL 7 DAY) 
        OR end_date < '$today'
        OR end_date = '$today'";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $status = "Due Soon"; // Default status

    if ($row['end_date'] < $today) {
        $status = "Overdue";
    } elseif ($row['end_date'] == $today) {
        $status = "Done";
    }

    // Check if notification already exists to prevent duplicates
    $checkSql = "SELECT id FROM notifications WHERE document_id = ? AND status = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("is", $row['id'], $status);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) { // If no existing notification, insert new one
        $insertSql = "INSERT INTO notifications (document_id, title, file_reference, end_date, parties, department, type, status, is_read)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("isssssss", $row['id'], $row['title'], $row['file_reference'], $row['end_date'], $row['parties'], $row['department'], $row['type'], $status);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $stmt->close();
}

// Fetch unread notifications count
$countSql = "SELECT COUNT(*) as count FROM notifications WHERE is_read = 0";
$countResult = $conn->query($countSql);
$countRow = $countResult->fetch_assoc();

echo json_encode(['count' => $countRow['count']]);

$conn->close();
?>


