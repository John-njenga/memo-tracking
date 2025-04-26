<?php
$servername = "localhost";
$username = "root"; // Change accordingly
$password = "";
$dbname = "strategic_plan";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$today = date("Y-m-d");

// Fetch documents and categorize them
$sql = "SELECT id, title, file_reference, end_date, parties, department, type 
        FROM documents 
        WHERE end_date <= DATE_ADD('$today', INTERVAL 7 DAY) 
        OR end_date < '$today'
        OR end_date = '$today'";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $status = "Due Soon";

    if ($row['end_date'] < $today) {
        $status = "Overdue";
    } elseif ($row['end_date'] == $today) {
        $status = "Done";
    }

    // Prevent duplicate notifications
    $checkSql = "SELECT id FROM notifications WHERE document_id = ? AND status = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("is", $row['id'], $status);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
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
$unreadCount = $countRow['count'];


// Fetch notifications and sort them by is_read (unread first), then by end_date
$notifSql = "SELECT * FROM notifications ORDER BY is_read ASC, end_date DESC";
$notifResult = $conn->query($notifSql);


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .sidebar {
            width: 220px;
            margin-left: 15px;
            background-color: #0621e8;
            color: #fff;
            padding: 20px;
            position: fixed;
            height: 96vh;
            border-radius: 0 15px 15px 0;
            overflow-y: auto;
        }
        .sidebar h5 {
            text-align: center;
        }
        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            font-size: 1.1rem;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #3a0ca3;
        }
        .container {
            margin-left: 300px;
            max-width: 1000px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .status-badge {
            padding: 6px 12px;
            font-size: 0.9rem;
            border-radius: 5px;
            font-weight: bold;
        }
        .notification-title {
            font-size: 1rem;
            font-weight: bold;
        }
        .badge {
            font-size: 0.85rem;
            padding: 6px 10px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h5><i class="fa-solid fa-dove"></i> Swift Tracking</h5>
    <a href="index1.php">
        <i class="fa-brands fa-slack"></i> Dashboard 
    </a>
</div>

<div class="container mt-4">
    <div class="card p-4">
        <h3 class="mb-3">
            <i class="fas fa-bell text-primary"></i> Notifications 
            <span class="badge bg-danger"><?= $unreadCount ?></span>
        </h3>

        <div class="list-group">
            <?php while ($notif = $notifResult->fetch_assoc()): ?>
                <div class="list-group-item d-flex justify-content-between align-items-start" data-id="<?= $notif['id'] ?>" data-read="<?= $notif['is_read'] ?>">
                    <div class="ms-2 me-auto">
                        <div class="notification-title"><i class="fas fa-file-alt"></i> <?= htmlspecialchars($notif['title']) ?></div>
                        <small><strong>Ref:</strong> <?= htmlspecialchars($notif['file_reference']) ?> | <?= htmlspecialchars($notif['department']) ?></small><br>
                        <small><strong>Parties:</strong> <?= htmlspecialchars($notif['parties']) ?></small><br>
                        <small><strong>Type:</strong> <?= htmlspecialchars($notif['type']) ?></small><br>
                        <small>
                            <strong>Status:</strong> 
                            <span class="status-badge 
                                <?= $notif['status'] == 'Overdue' ? 'bg-danger text-white' : ($notif['status'] == 'Due Soon' ? 'bg-warning text-dark' : 'bg-success text-white') ?>">
                                <?= htmlspecialchars($notif['status']) ?>
                            </span>
                        </small><br>
                        <small><strong>Due Date:</strong> <i class="far fa-calendar-alt"></i> <?= htmlspecialchars($notif['end_date']) ?></small>
                    </div>
                    <button class="btn btn-sm btn-success mark-as-read" data-id="<?= $notif['id'] ?>">Mark as Read</button>
                </div>

            <?php endwhile; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Check each notification and disable the "Mark as Read" button if already marked
    $(".list-group-item").each(function() {
        var notifId = $(this).data("id");
        var isRead = $(this).data("read"); // Assuming you are storing the read status in a data attribute

        if (isRead) {
            var button = $(this).find(".mark-as-read");
            button.prop("disabled", true); // Disable the "Mark as Read" button
            $(this).css("background-color", "#f0f0f0"); // Change background color to indicate read
            $(this).find(".notification-title").css("text-decoration", "line-through"); // Strike-through the title
            $(this).find(".status-badge").removeClass("bg-warning bg-danger").addClass("bg-success"); // Change status to "Read"
            $(this).find(".status-badge").text("Read"); // Update status text
            $(this).find("small").css("color", "#888"); // Dim the rest of the text
        }
    });

    $(".mark-as-read").click(function() {
        var notifId = $(this).data("id");
        var button = $(this);
        
        $.ajax({
            url: "mark_as_read.php", // Endpoint to mark notification as read
            type: "POST",
            data: { id: notifId },
            success: function(response) {
                if (response === "success") {
                    // Disable the button and change the notification style
                    button.prop("disabled", true); // Disable the "Mark as Read" button
                    button.closest(".list-group-item").css("background-color", "#f0f0f0"); // Change background color to indicate read
                    button.closest(".list-group-item").find(".notification-title").css("text-decoration", "line-through"); // Strike-through the title
                    button.closest(".list-group-item").find(".status-badge").removeClass("bg-warning bg-danger").addClass("bg-success"); // Change status to "Read"
                    button.closest(".list-group-item").find(".status-badge").text("Read"); // Update status text
                    button.closest(".list-group-item").find("small").css("color", "#888"); // Dim the rest of the text

                    // Move the notification to the bottom of the list
                    button.closest(".list-group-item").appendTo(".list-group");
                }
            }
        });
    });
});



</script>

</body>
</html>
