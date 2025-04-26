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

// Fetch totals by status
$statusTotals = [];
$statusQuery = "SELECT status, COUNT(*) as total FROM documents GROUP BY status";
$statusResult = $conn->query($statusQuery);
while ($row = $statusResult->fetch_assoc()) {
    $statusTotals[] = $row;
}

// Fetch totals by document type
$typeTotals = [];
$typeQuery = "SELECT type, COUNT(*) as total FROM documents GROUP BY type";
$typeResult = $conn->query($typeQuery);
while ($row = $typeResult->fetch_assoc()) {
    $typeTotals[] = $row;
}

// Fetch total count of all documents
$totalDocumentsQuery = "SELECT COUNT(*) as total_documents FROM documents";
$totalDocumentsResult = $conn->query($totalDocumentsQuery);
$totalDocuments = $totalDocumentsResult->fetch_assoc()['total_documents'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swift Report</title>
    <link rel="icon" href="image/kutrrh.jpg" type="image/icon type">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 220px;
            background-color: #0621e8;
            color: #fff;
            padding: 20px;
            position: fixed;
            height: 98vh;
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
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .card {
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    margin: 15px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

.card:hover {
    transform: scale(1.05);
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
}

.card:hover::before {
    content: '';
    position: absolute;
    top: -10px;
    left: -10px;
    right: -10px;
    bottom: -10px;
    border: 2px solid rgba(0, 255, 255, 0.8);
    animation: glow 1s infinite alternate;
}
        .chart-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }
        canvas {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    transition: background-color 0.3s ease;
}

.card-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}



@keyframes glow {
    0% {
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.8), 0 0 20px rgba(0, 255, 255, 0.6), 0 0 30px rgba(0, 255, 255, 0.4);
    }
    100% {
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.4), 0 0 20px rgba(0, 255, 255, 0.3), 0 0 30px rgba(0, 255, 255, 0.2);
    }
}

body.dimming {
    background-color: rgba(0, 0, 0, 0.5);
    pointer-events: none;
}

body.dimming .card {
    pointer-events: auto;
}

    </style>
</head>
<body>
    <div class="sidebar">
        <h5 style="padding: 5px;"><i class="fa-solid fa-dove"></i> Swift Tracking</h5>
        <a href="index1.php">
        <i class="fa-brands fa-slack"></i> Dashboard
</a>
    </div>

    <div class="content">
        <h2 class="text-center mb-4">Document Report</h2>

        <div class="container">
            <div class="row">
                <!-- Total Documents Card -->
                <div class="col-md-4">
                    <div class="card text-center p-3 shadow">
                        <h4>Total Documents</h4>
                        <p class="fs-4 text-primary"><?php echo htmlspecialchars($totalDocuments); ?> Documents</p>
                    </div>
                </div>

                <!-- Status Cards -->
                <?php foreach ($statusTotals as $status) : ?>
                <div class="col-md-4">
                    <div class="card text-center p-3 shadow" onclick="window.location.href='view_documents.php?status=<?php echo urlencode($status['status']); ?>'">
                        <h4>Status: <?php echo htmlspecialchars($status['status']); ?></h4>
                        <p class="fs-4 text-success"><?php echo htmlspecialchars($status['total']); ?> Documents</p>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Document Type Cards -->
            <?php foreach ($typeTotals as $type) : ?>
            <div class="col-md-4">
        <div class="card text-center p-3 shadow" onclick="window.location.href='view_documents.php?type=<?php echo urlencode($type['type']); ?>'">
        <h4>Document Type: <?php echo htmlspecialchars($type['type']); ?></h4>
        <p class="fs-4 text-danger"><?php echo htmlspecialchars($type['total']); ?> Documents</p>
        </div>
        </div>
        <?php endforeach; ?>

            </div>
        </div>

        <!-- Chart Section -->
        <div class="chart-container">
            <div>
                <h2 class="text-center">Status Chart</h2>
                <canvas id="statusChart"></canvas>
            </div>
            <div>
                <h2 class="text-center">Document Type Chart</h2>
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Status Data for Chart
        const statusLabels = <?php echo json_encode(array_column($statusTotals, 'status')); ?>;
        const statusData = <?php echo json_encode(array_column($statusTotals, 'total')); ?>;

        // Document Type Data for Chart
        const typeLabels = <?php echo json_encode(array_column($typeTotals, 'type')); ?>;
        const typeData = <?php echo json_encode(array_column($typeTotals, 'total')); ?>;

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Total Documents by Status',
                    data: statusData,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Document Type Chart
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeData,
                    backgroundColor: ['#007bff', '#ffc107', '#28a745', '#17a2b8', '#dc3545', '#6c757d']
                }]
            },
            options: { responsive: true }
        });
        document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('mouseenter', () => {
        document.body.classList.add('dimming');
    });

    card.addEventListener('mouseleave', () => {
        document.body.classList.remove('dimming');
    });
});

    </script>
</body>
</html>
