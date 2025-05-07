<?php
// Include header
include_once "../includes/header.php";

// Redirect if not member
if(!isset($_SESSION["role"]) || $_SESSION["role"] != "member"){
    header("location: ../index.php");
    exit;
}

// Include database connection
require_once "../config/database.php";
require_once "../includes/functions.php";

// Get member details
$sql = "SELECT m.* FROM members m 
        JOIN users u ON m.user_id = u.user_id 
        WHERE u.user_id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $param_user_id);
    $param_user_id = $_SESSION["user_id"];
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 1){
        $member = mysqli_fetch_assoc($result);
        $member_id = $member['member_id'];
        
        // Get member balance
        $balance = getMemberBalance($conn, $member_id);
        
        // Get recent transactions
        $transactions_sql = "SELECT * FROM transactions 
                            WHERE member_id = ? 
                            ORDER BY transaction_date DESC 
                            LIMIT 5";
        $stmt = mysqli_prepare($conn, $transactions_sql);
        mysqli_stmt_bind_param($stmt, "i", $member_id);
        mysqli_stmt_execute($stmt);
        $transactions_result = mysqli_stmt_get_result($stmt);
    } else {
        echo "<div class='alert alert-danger'>No member account found.</div>";
        exit;
    }
} else {
    echo "Oops! Something went wrong.";
    exit;
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>Member Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th>Member ID:</th>
                                <td><?php echo $member['member_id']; ?></td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td><?php echo $member['first_name'] . ' ' . $member['last_name']; ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo $member['email']; ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?php echo $member['phone']; ?></td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td><?php echo $member['address']; ?></td>
                            </tr>
                        </table>
                        <a href="profile.php" class="btn btn-primary">Update Profile</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Account Balance</h5>
                    </div>
                    <div class="card-body text-center">
                        <h1 class="display-4">₱<?php echo number_format($balance, 2); ?></h1>
                        <p class="lead">Current Balance</p>
                        <a href="transactions.php" class="btn btn-success">View All Transactions</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Recent Transactions</h5>
            </div>
            <div class="card-body">
                <?php
                if(mysqli_num_rows($transactions_result) > 0){
                ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while($transaction = mysqli_fetch_assoc($transactions_result)){
                                echo "<tr>";
                                echo "<td>" . $transaction['transaction_id'] . "</td>";
                                echo "<td>";
                                if($transaction['type'] == 'deposit') {
                                    echo "<span class='badge bg-success'>Deposit</span>";
                                } else {
                                    echo "<span class='badge bg-warning text-dark'>Withdrawal</span>";
                                }
                                echo "</td>";
                                echo "<td>₱" . number_format($transaction['amount'], 2) . "</td>";
                                echo "<td>" . $transaction['description'] . "</td>";
                                echo "<td>" . $transaction['transaction_date'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
                } else{
                    echo "<div class='alert alert-info'>No recent transactions found.</div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>