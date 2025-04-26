<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "strategic_plan"; 

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch document types
$sql = "SELECT name FROM document_types";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0): 
    while ($row = $result->fetch_assoc()): 
        $name = htmlspecialchars($row['name']);
?>
        <li>
            <a href="#" class="dropdown-item" onclick="openForm('<?php echo $name; ?>')">
                <?php echo $name; ?>
            </a>
        </li>
<?php 
    endwhile;
else:
?>
    <li class="dropdown-item text-danger">No document types found</li>
<?php
endif;



// Close connection
$conn->close();
?>
