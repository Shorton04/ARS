<?php
// Include header
include_once "../../includes/header.php";

// Define the base URL for the project - if not already defined in header
if (!isset($base_url)) {
    $base_url = "/accounts_receivable_system"; // Use underscore to match your URL format
}

// Redirect if not admin
if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
    header("location: {$base_url}/index.php");
    exit;
}

// Include database connection
require_once "../../config/database.php";

// Check if the user is trying to delete their own account
if(isset($_GET["id"]) && !empty(trim($_GET["id"])) && $_GET["id"] == $_SESSION["user_id"]){
    // Redirect back to users page with an error message
    header("location: {$base_url}/admin/users/view.php?error=self_delete");
    exit;
}

// Check if user ID is provided
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Prepare a delete statement
    $sql = "DELETE FROM users WHERE user_id = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        
        // Set parameters
        $param_id = trim($_GET["id"]);
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            // Records deleted successfully. Redirect to users page
            header("location: {$base_url}/admin/users/view.php?success=deleted");
            exit();
        } else{
            // If there was an error, redirect with an error message
            header("location: {$base_url}/admin/users/view.php?error=delete_failed");
            exit();
        }
    }
     
    // Close statement
    mysqli_stmt_close($stmt);
    
    // Close connection
    mysqli_close($conn);
} else{
    // No ID parameter provided, redirect to users page
    header("location: {$base_url}/admin/users/view.php");
    exit;
}
?>