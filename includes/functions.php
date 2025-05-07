<?php
// Calculate member balance based on transactions
function getMemberBalance($conn, $member_id) {
    $balance = 0;
    
    // Get deposits
    $sql = "SELECT SUM(amount) as total FROM transactions 
            WHERE member_id = ? AND type = 'deposit'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $deposits = $row['total'] ?: 0;
    
    // Get withdrawals
    $sql = "SELECT SUM(amount) as total FROM transactions 
            WHERE member_id = ? AND type = 'withdrawal'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $member_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $withdrawals = $row['total'] ?: 0;
    
    // Calculate balance
    $balance = $deposits - $withdrawals;
    
    return $balance;
}

// Sanitize user input
function sanitize($conn, $data) {
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($data)));
}
?>