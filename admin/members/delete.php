<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Check if member ID is provided
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    $member_id = trim($_GET["id"]);
    
    try {
        // First check if there are any transactions associated with this member
        $check_sql = "SELECT COUNT(*) as count FROM transactions WHERE member_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        
        if(!$check_stmt) {
            throw new Exception("Prepare statement failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($check_stmt, "i", $member_id);
        
        if(!mysqli_stmt_execute($check_stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($check_stmt));
        }
        
        $check_result = mysqli_stmt_get_result($check_stmt);
        $check_row = mysqli_fetch_assoc($check_result);
        
        if($check_row['count'] > 0){
            // Member has transactions - redirect with error
            header("location: {$base_url}/admin/members/view.php?error=has_transactions");
            exit;
        }
        
        // No transactions, safe to delete - prepare a delete statement
        $sql = "DELETE FROM members WHERE member_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if(!$stmt) {
            throw new Exception("Prepare delete statement failed: " . mysqli_error($conn));
        }
        
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $member_id);
        
        // Attempt to execute the prepared statement
        if(!mysqli_stmt_execute($stmt)){
            throw new Exception("Execute delete failed: " . mysqli_stmt_error($stmt));
        }
        
        // Records deleted successfully. Redirect to members page
        header("location: {$base_url}/admin/members/view.php?success=deleted");
        exit();
        
    } catch (Exception $e) {
        // Log the error and display a user-friendly message
        error_log("Member delete error: " . $e->getMessage());
        
        // Redirect with error message
        header("location: {$base_url}/admin/members/view.php?error=delete_failed&message=" . urlencode($e->getMessage()));
        exit();
    } finally {
        // Close any open statements
        if(isset($check_stmt)) {
            mysqli_stmt_close($check_stmt);
        }
        if(isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        
        // Close connection
        mysqli_close($conn);
    }
} else {
    // No ID parameter provided, redirect to members page
    header("location: {$base_url}/admin/members/view.php?error=no_id");
    exit;
}
?>