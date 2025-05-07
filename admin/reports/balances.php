<?php
// Include header
include_once "../../includes/header.php";

// Redirect if not admin
if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
    header("location: ../../index.php");
    exit;
}

// Include database connection
require_once "../../config/database.php";
require_once "../../includes/functions.php";

// Fetch all members with their balances
$sql = "SELECT m.member_id, m.first_name, m.last_name, m.email, m.phone FROM members m ORDER BY m.last_name, m.first_name";
$result = mysqli_query($conn, $sql);
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Member Balances Report</h2>
            <button class="btn btn-primary" onclick="window.print()">Print Report</button>
        </div>
        
        <?php
        // Check if there are any members
        if(mysqli_num_rows($result) > 0){
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Deposits</th>
                        <th>Total Withdrawals</th>
                        <th>Current Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total_deposits = 0;
                    $grand_total_withdrawals = 0;
                    $grand_total_balance = 0;
                    
                    while($row = mysqli_fetch_assoc($result)){
                        $member_id = $row['member_id'];
                        
                        // Get deposits
                        $deposits_sql = "SELECT SUM(amount) as total FROM transactions 
                                        WHERE member_id = ? AND type = 'deposit'";
                        $stmt = mysqli_prepare($conn, $deposits_sql);
                        mysqli_stmt_bind_param($stmt, "i", $member_id);
                        mysqli_stmt_execute($stmt);
                        $deposits_result = mysqli_stmt_get_result($stmt);
                        $deposits_row = mysqli_fetch_assoc($deposits_result);
                        $total_deposits = $deposits_row['total'] ?: 0;
                        
                        // Get withdrawals
                        $withdrawals_sql = "SELECT SUM(amount) as total FROM transactions 
                                           WHERE member_id = ? AND type = 'withdrawal'";
                        $stmt = mysqli_prepare($conn, $withdrawals_sql);
                        mysqli_stmt_bind_param($stmt, "i", $member_id);
                        mysqli_stmt_execute($stmt);
                        $withdrawals_result = mysqli_stmt_get_result($stmt);
                        $withdrawals_row = mysqli_fetch_assoc($withdrawals_result);
                        $total_withdrawals = $withdrawals_row['total'] ?: 0;
                        
                        // Calculate balance
                        $balance = $total_deposits - $total_withdrawals;
                        
                        // Update grand totals
                        $grand_total_deposits += $total_deposits;
                        $grand_total_withdrawals += $total_withdrawals;
                        $grand_total_balance += $balance;
                        
                        echo "<tr>";
                        echo "<td>" . $row['member_id'] . "</td>";
                        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['phone'] . "</td>";
                        echo "<td>₱" . number_format($total_deposits, 2) . "</td>";
                        echo "<td>₱" . number_format($total_withdrawals, 2) . "</td>";
                        echo "<td>";
                        if($balance > 0) {
                            echo "<span class='text-success'>₱" . number_format($balance, 2) . "</span>";
                        } elseif($balance < 0) {
                            echo "<span class='text-danger'>₱" . number_format($balance, 2) . "</span>";
                        } else {
                            echo "₱0.00";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                    <tr class="table-dark fw-bold">
                        <td colspan="4">Grand Total</td>
                        <td>₱<?php echo number_format($grand_total_deposits, 2); ?></td>
                        <td>₱<?php echo number_format($grand_total_withdrawals, 2); ?></td>
                        <td>₱<?php echo number_format($grand_total_balance, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
        } else{
            echo "<div class='alert alert-info'>No members found.</div>";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>