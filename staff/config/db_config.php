<?php
$servername = "localhost";
$username = "dbuser";
$password = "dbpass";
$dbname = "dbname";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
