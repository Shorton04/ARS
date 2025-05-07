<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Change to your MySQL username
define('DB_PASSWORD', ''); // Change to your MySQL password
define('DB_NAME', 'cooperative_db');

// Connect to database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Check connection
if($conn === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>