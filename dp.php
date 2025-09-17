<?php
// db.php - Database connection file
$servername = "localhost"; // Assuming local server
$username = "upbek8wm1lktc";
$password = "wkctga6nhgu8";
$dbname = "dbvlrzdgxxgqub";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
