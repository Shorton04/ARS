<?php
// Include header
include_once "../includes/header.php";

// Redirect if not admin
if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
    header("location: ../index.php");
    exit;
}

// Include database connection
require_once "../config/database.php";

// Count users
$sql = "SELECT COUNT(*) as total_users FROM users";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_users = $row['total_users'];

// Count members
$sql = "SELECT COUNT(*) as total_members FROM members";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_members = $row['total_members'];

// Count transactions
$sql = "SELECT COUNT(*) as total_transactions FROM transactions";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_transactions = $row['total_transactions'];

// Get total deposits
$sql = "SELECT SUM(amount) as total_deposits FROM transactions WHERE type = 'deposit'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_deposits = $row['total_deposits'] ?: 0;

// Get total withdrawals
$sql = "SELECT SUM(amount) as total_withdrawals FROM transactions WHERE type = 'withdrawal'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$total_withdrawals = $row['total_withdrawals'] ?: 0;

// Calculate net balance
$net_balance = $total_deposits - $total_withdrawals;
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <h2>Admin Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <h2 class="card-text"><?php echo $total_users; ?></h2>
                        <a href="../admin/users/view.php" class="text-white">Manage Users →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Members</h5>
                        <h2 class="card-text"><?php echo $total_members; ?></h2>
                        <a href="../admin/members/view.php" class="text-white">Manage Members →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Transactions</h5>
                        <h2 class="card-text"><?php echo $total_transactions; ?></h2>
                        <a href="../transactions/view.php" class="text-white">View Transactions →</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th>Total Deposits</th>
                                <td>₱<?php echo number_format($total_deposits, 2); ?></td>
                            </tr>
                            <tr>
                                <th>Total Withdrawals</th>
                                <td>₱<?php echo number_format($total_withdrawals, 2); ?></td>
                            </tr>
                            <tr class="table-info">
                                <th>Net Balance</th>
                                <td>₱<?php echo number_format($net_balance, 2); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="../admin/members/add.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-person-plus"></i> Add New Member
                            </a>
                            <a href="../transactions/deposit.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-cash"></i> Record Deposit
                            </a>
                            <a href="../transactions/withdraw.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-cash-stack"></i> Record Withdrawal
                            </a>
                            <a href="../admin/reports/balances.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-earmark-text"></i> Generate Balance Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>