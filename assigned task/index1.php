 <?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "strategic_plan";  

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['username'])) {
    header("Location: index1.php");
    exit;
}

$user = $_SESSION['username'];

$sql = "SELECT * FROM documents";  
$result = $conn->query($sql);

$status_sql = "SELECT status, COUNT(*) AS count FROM documents GROUP BY status";
$status_result = $conn->query($status_sql);

$documents_sql = "SELECT type, COUNT(*) AS count FROM documents GROUP BY type";
$documents_result = $conn->query($documents_sql);

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index1.php");
    exit();
}
// Get today's date, two weeks ago, and 3 days ahead
$today = date("Y-m-d");
$twoWeeksAgo = date("Y-m-d", strtotime("-14 days"));
$threeDaysAhead = date("Y-m-d", strtotime("+3 days"));

// Fetch all documents to update their status
$sql = "SELECT id, start_date, end_date FROM documents";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];

        // Determine the status
        if ($end_date == $today) {
            $status = "Done";
        } elseif ($end_date < $today) {
            $status = "Overdue";
        } elseif ($end_date > $today && $end_date <= $threeDaysAhead) {
            $status = "Due Soon"; // If due within 3 days
        } elseif ($start_date >= $twoWeeksAgo && $start_date <= $today) {
            $status = "New"; // If started within the last 2 weeks
        } else {
            $status = "In Progress";
        }

        // Update the document's status in the database
        $update_sql = "UPDATE documents SET status = '$status' WHERE id = $id";
        $conn->query($update_sql);
    }
}

// Fetch updated documents
$sql = "SELECT * FROM documents";
$result = $conn->query($sql);

if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = $_GET['message'];
    $type = $_GET['type'];
    echo "<script type='text/javascript'>
            alert('" . $message . "');
          </script>";
}

$typeFilter = isset($_GET['typeFilter']) ? $_GET['typeFilter'] : "";
$sql = "SELECT * FROM documents";
if (!empty($typeFilter)) {
    $sql .= " WHERE type = '" . $conn->real_escape_string($typeFilter) . "'";
}
$result = $conn->query($sql);
// Fetch document types

?>
 <script>
         window.onload = function () {
            // Check if a message exists in the URL
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');
            const type = urlParams.get('type');

            if (message) {
            
                // Clean the URL to prevent the dialog from showing again on reload or navigation
                const url = new URL(window.location.href);
                url.searchParams.delete('message');
                url.searchParams.delete('type');
                window.history.replaceState({}, document.title, url.toString());
            }
        };

function applySortAndGroup() {
  const sortBy = document.getElementById('sortBy').value;
  const groupBy = document.getElementById('groupBy').value;

  let rows = Array.from(document.querySelectorAll('#documentTableBody tr'));

  if (sortBy) {
    rows.sort((rowA, rowB) => {
      const cellA = rowA.querySelector(`[data-column="${sortBy}"]`).innerText;
      const cellB = rowB.querySelector(`[data-column="${sortBy}"]`).innerText;
      
      if (sortBy === 'end_date') {
        // Convert date strings to Date objects for correct sorting
        return new Date(cellA) - new Date(cellB);
      }
      
      return cellA.localeCompare(cellB);
    });
  }

  // If groupBy is selected, group the rows
  let groupedRows = [];
  if (groupBy) {
    groupedRows = rows.reduce((groups, row) => {
      const groupValue = row.querySelector(`[data-column="${groupBy}"]`).innerText;
      if (!groups[groupValue]) {
        groups[groupValue] = [];
      }
      groups[groupValue].push(row);
      return groups;
    }, {});
  } else {
    // If no grouping, simply flatten the array
    groupedRows = { "All": rows };
  }

  // Clear the table body
  const tableBody = document.getElementById('documentTableBody');
  tableBody.innerHTML = '';

  // Append the rows grouped by selected criteria
  for (const group in groupedRows) {
    if (group !== "All") {
      const groupHeaderRow = document.createElement('tr');
      const groupCell = document.createElement('td');
      groupCell.colSpan = 10;
      groupCell.innerHTML = `<strong>${group}</strong>`;
      groupHeaderRow.appendChild(groupCell);
      tableBody.appendChild(groupHeaderRow);
    }

    groupedRows[group].forEach(row => {
      tableBody.appendChild(row);
    });
  }
}

window.onload = () => {
  applySortAndGroup(); 
};

    </script>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swift Tracking</title>
  <link rel="icon" href="image/kutrrh.jpg" type="image/icon type">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f8f9fa;
    }
    .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-icon {
            font-size: 1.5rem;
            color: #007bff;
            cursor: pointer;
        }

        .user-icon:hover {
            color: #0056b3;
        }

        .dropdown-menu {
            min-width: 200px;
        }
        @media (max-width: 768px) {
            #page-content-wrapper {
                margin-left: 0;
                padding: 10px;
            }

            #sidebar-wrapper {
                width: 100%;
                height: auto;
                position: relative;
            }

            .charts-container {
                flex-direction: column;
                align-items: center;
            }
          }
        

    #sidebar-wrapper {
  position: fixed; 
  top: 2px;
  left: 2px;
  width: 250px;
  height: 99vh; 
  background-color: #0621e8;
  padding: 20px 10px;
  border-radius: 10px;
  overflow-y: auto; 
}

    .sidebar-heading {
      font-size: 1.5rem;
      font-weight: bold;
      color: white;
      text-align: center;
      margin: 2px;
    }
  /* Style the dropdowns */
.sort-group-controls {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
  align-items: center;
}

.sort-group-controls label {
  font-weight: bold;
  margin-right: 5px;
}

.sort-group-controls select {
  padding: 8px 12px;
  font-size: 14px;
  border-radius: 4px;
  border: 1px solid #ccc;
  width: 150px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

th, td {
  padding: 12px;
  text-align: left;
  border: 0px solid #ddd;
}

th {
  background-color: #f2f2f2;
  font-weight: bold;
}
    .list-group-item {
      font-size: 1rem;
      border: none;
      color: white;
      background-color: #0621e8;
    }

    .list-group-item:hover {
      background-color: #004080;
    }

    #page-content-wrapper {
  margin-left: 250px; 
  padding: 30px;
    }
    /* Styling for the user icon */
        .author-icon {
        position: relative;
        display: inline-block;
        font-size: 20px;
        color: #007bff;
        text-align: center;
        }
        .author-name {
            margin-top: 5px;
            font-size: 14px;
            color: #007bff;
        }

    .charts-container {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
      
    }

    .chart-card {
      background-color: white;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 10px;
      width: 300px;
      height: 250px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .popup-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      z-index: 9998;
    }

    .popup-form {
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 9999;
      width: 100%;
      max-width: 600px;
      display: none;
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .btn-primary {
      background-color: #004080;
      border: none;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .table-container {
      margin-top: 20px;
    }

    .table th,
    .table td {
      vertical-align: middle;
    }
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
  <div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="text-white" id="sidebar-wrapper">
      <div class="sidebar-heading text-center py-4"><i class="fa-solid fa-dove"></i> Swift Track</div>
        <!-- Add Document Button -->
        <div class="dropdown" id="documentDropdownContainer">
    <button class="btn btn-primary mt-3 dropdown-toggle" id="addDocumentBtn" onclick="showAddDocumentForm()" data-bs-toggle="dropdown" aria-expanded="false">
        + Add New Document
    </button>
    <ul class="dropdown-menu" id="documentDropdown">
        <li class="dropdown-item text-center">Loading...</li> <!-- Placeholder -->
    </ul>
</div>

      <div class="list-group list-group-flush" style="margin-top: 5px;">
        <a href="report.php" class="list-group-item list-group-item-action text-white"><i class="fa-regular fa-file"></i> Report and statistics</a>
      </div>
    </div>

     <!-- Page Content -->
     <div id="page-content-wrapper">
        <nav class="navbar navbar-light bg-light">
            <div>
             <h3> <i class="fa-brands fa-slack"></i> Dashboard</h3>
            </div>
          <div class="position-absolute top-2 end-0 me-5 mt-0">
              <a href="notifications.php" class="btn btn-outline-primary position-relative">
            <i class="fa-solid fa-bell"></i>
            <span id="notification-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            0
        </span>
         </a>
        </div>

            <div class="dropdown">
                <div class="user-icon" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="index.php?logout=true">Logout</a></li>
                </ul>
            </div>
        </nav>


      
            <!-- Charts Section -->
            <div class="charts-container">
                <div class="chart-card">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-card">
                    <canvas id="documentsChart"></canvas>
                </div>
            </div>


      <!-- Table Section -->
      <div class="table-container">
        <h5>Documents</h5>
      <!-- Dropdowns for Sort By and Group By -->
<div class="sort-group-controls">
  <label for="sortBy">Sort By:</label>
  <select id="sortBy" onchange="applySortAndGroup()">
    <option value="">Select Sort By</option>
    <option value="status">Status</option>
    <option value="end_date">End Date</option>
    <option value="author">Author</option>
  </select>
  
  <label for="groupBy">Group By:</label>
  <select id="groupBy" onchange="applySortAndGroup()">
    <option value="">Select Group By</option>
    <option value="status">Status</option>
    <option value="end_date">End Date</option>
    <option value="department">Department</option>
  </select>
</div>

<h2 class="text-center">Document Dashboard</h2>
        <table class="table">
    <thead>
  <tr>
    <th>Ref Number</th>
    <th>Title</th>
    <th>
      Type
      <select id="typeFilter" class="form-select form-select-sm" onchange="filterByType()">
      <option value="">All</option>
        <option value="Performance Contract">Performance Contract</option>
        <option value="Strategic Plan">Strategic Plan</option>
        <option value="MOUs">MOUs</option>
      </select>
    </th>
    <th>Category</th>
    <th>Department</th>
    <th>Status</th>
    <th>Start Date</th>
    <th>End Date</th>
    <th>Comments</th>
    <th>Parties</th>
    <th>Author</th>
    <th>Actions</th>
  </tr>
</thead>

<tbody id="documentTableBody">
  <?php
    if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['file_reference'] . "</td>";
        echo "<td>" . $row['title'] . "</td>";
        echo "<td>" . $row['type'] . "</td>";
        echo "<td>" . $row['category'] . "</td>";
        echo "<td data-column='department'>" . $row['department'] . "</td>";
        echo "<td data-column='status'>" . $row['status'] . "</td>";
        echo "<td>" . $row['start_date'] . "</td>";
        echo "<td data-column='end_date'>" . $row['end_date'] . "</td>";
      
        echo "<td>" . $row['comments'] . "</td>";
        
        echo "<td>" . $row['parties'] . "</td>";
        
        $author = htmlspecialchars($row['author']);
        echo " <td class='author-icon'>
                  <span class='author-icon' data-author='$author'>
                  <i class='fas fa-user'></i>
                  <div class='author-name'>{$row['author']}</div>
                  </td>";

        echo "<td>
                <button class='btn btn-primary btn-sm' onclick='viewDocument(\"" . $row['id'] . "\")'>View</button>
              </td>";
        echo "</tr>";
      }
    }
  ?>
          </tbody>
        </table>
      </div>

      

  <!-- Popup Form -->
<div class="popup-overlay" id="popupOverlay"></div>
<div class="popup-form card p-4" id="popupForm">
  <h4 id="formTitle" class="mb-4">Document Details</h4>
  <button class="close-btn" onclick="window.location.href='index1.php'">&times;</button>
  <form id="documentForm" action="upload.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" id="documentId" name="documentId">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label for="fileReference" class="form-label">File Reference Number</label>
        <input type="text" class="form-control" id="fileReference" name="fileReference" required>
      </div>
      <div class="col-md-6 mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
      </div>
      <div class="col-md-6 mb-3">
        <label for="startDate" class="form-label">Start Date</label>
        <input type="date" class="form-control" id="startDate" name="startDate" required>
      </div>
      <div class="col-md-6 mb-3">
        <label for="endDate" class="form-label">End Date</label>
        <input type="date" class="form-control" id="endDate" name="endDate" required>
      </div>
      <div class="col-md-6 mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" id="status" name="status" required>
          <option value="New">New</option>
          <option value="In Progress">In Progress</option>
          <option value="Done">Done</option>
          <option value="Due soon">Due soon</option>
          <option value="Overdue">Overdue</option>
          <option value="Renewal">Renewal</option>
        </select>
      </div>
      <div class="col-md-6 mb-3">
        <label for="department" class="form-label">Department</label>
        <input type="text" class="form-control" id="department" name="department" required>
      </div>
      <div class="col-md-6 mb-3">
        <label for="category" class="form-label">Category</label>
        <input type="text" class="form-control" id="category" name="category" required>
      </div>

      <div class="col-md-6 mb-3">
        <label for="parties" class="form-label">Parties</label>
        <input type="text" class="form-control" id="parties" name="parties" placeholder="Enter parties involved" required>
      </div>

      <div class="col-md-12 mb-3">
        <label for="comments" class="form-label">Comments</label>
        <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Add any additional comments"></textarea>
      </div>
      <div class="col-md-6 mb-3">
        <label for="fileUpload" class="form-label">Upload File</label>
        <input type="file" class="form-control" id="fileUpload" name="fileUpload">
      </div>

      <!-- Document Type Field (Dropdown) -->
      <div class="col-md-6 mb-3">
        <label for="type" class="form-label">Document Type</label>
        <select class="form-control" id="type" name="type" required>
    <option value="">Select Document type</option> <!-- Default option -->
</select>

      </div>
    </div>
    <div class="row">
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-success">Submit</button>
      </div>
    </div>
  </form>
</div>

  <script>
    function openForm(title) {
      document.getElementById('popupForm').style.display = 'block';
      document.getElementById('popupOverlay').style.display = 'block';
      document.getElementById('formTitle').textContent = title;
    }

    function viewDocument(id) {
      window.location.href = `view_document.php?id=${id}`;
    }



    const statusData = <?php echo json_encode($status_result->fetch_all(MYSQLI_ASSOC)); ?>;
    const documentData = <?php echo json_encode($documents_result->fetch_all(MYSQLI_ASSOC)); ?>;

    // Status Chart
    const statusLabels = statusData.map(item => item.status);
    const statusCounts = statusData.map(item => item.count);
    new Chart(document.getElementById('statusChart'), {
      type: 'pie',
      data: {
        labels: statusLabels,
        datasets: [{
            label: 'Documents by status',
          data: statusCounts,
          backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#ff9f40','#FF0000'],
        }]
      },
    });

    // Documents Chart
    const departmentLabels = documentData.map(item => item.type);
    const departmentCounts = documentData.map(item => item.count);
    new Chart(document.getElementById('documentsChart'), {
      type: 'bar',
      data: {
        labels: departmentLabels,
        datasets: [{
          label: 'Documents by Type',
          data: departmentCounts,
          backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#ff9f40'],
        }]
      },
    });
    
 // JavaScript function to handle filtering
 function filterByType() {
    const typeFilter = document.getElementById("typeFilter").value;
    const url = new URL(window.location.href);

    if (typeFilter === "") {
      // Clear filter from the URL
      url.searchParams.delete("typeFilter");
    } else {
      // Add or update the typeFilter parameter
      url.searchParams.set("typeFilter", typeFilter);
    }

    // Reload the page with the updated URL
    window.location.href = url.toString();
  }

  // Preserve selected filter in the dropdown on page load
  document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const typeFilter = urlParams.get("typeFilter");
    if (typeFilter) {
      document.getElementById("typeFilter").value = typeFilter;
    }
  });

 
  $(document).ready(function() {
    $("#documentDropdownContainer").hover(function() {
        $.ajax({
            url: "fetch.php", // Fetch document types from the database
            method: "GET",
            success: function(response) {
                $("#documentDropdown").html(response); // Populate dropdown
                $("#documentDropdown").addClass("show"); // Show dropdown
            },
            error: function() {
                $("#documentDropdown").html("<li class='dropdown-item text-danger'>Error loading data</li>");
            }
        });
    }, function() {
        $("#documentDropdown").removeClass("show"); // Hide dropdown on hover out
    });
});


$(document).ready(function () {
        $.ajax({
            url: "pull.php", // Fetch document types
            method: "GET",
            success: function (response) {
                $("#type").html(response); // Populate the <select> dropdown
            },
            error: function () {
                alert("Error fetching document types.");
            }
        });
    });

function showAddDocumentForm() {
    let docName = prompt("Enter new document type:");
    if (docName) {
        // Send to backend to insert into database
        fetch('add_document.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'name=' + encodeURIComponent(docName)
        })
        .then(response => response.text())
        .then(data => {
            alert(data);
            location.reload(); // Refresh to show new document type
        });
    }
}

function fetchNotifications() {
            $.ajax({
                url: 'notification.php',
                method: 'GET',
                success: function(response) {
                    let data = JSON.parse(response);
                    let count = data.count;
                    $("#notification-count").text(count);
                }
            });
        }

        $(document).ready(function() {
            fetchNotifications(); // Fetch on page load
            setInterval(fetchNotifications, 5000); // Refresh every 5 seconds
        });

  </script>
</body>

</html>
