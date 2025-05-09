<?php
// Initialize the session
session_start();

// Define base URL if not already defined
if (!isset($base_url)) {
    $base_url = "/accounts_receivable_system";
}
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session
session_destroy();
 
// Redirect to login page
header("location: {$base_url}/auth/login.php");
exit;
?>