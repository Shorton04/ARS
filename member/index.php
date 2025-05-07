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
                            LIMIT 10";
        $stmt = mysqli_prepare($conn, $transactions_sql);
        mysqli_stmt_bind_param($stmt, "i", $member_id);
        mysqli_stmt_execute($stmt);
        $transactions_result = mysqli_stmt_get_result($stmt);
        
        // Get monthly transaction history for chart
        $chart_sql = "SELECT 
                        MONTH(transaction_date) as month, 
                        SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END) as deposits,
                        SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END) as withdrawals
                      FROM transactions
                      WHERE member_id = ? AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                      GROUP BY MONTH(transaction_date)
                      ORDER BY MONTH(transaction_date) ASC";
        $stmt = mysqli_prepare($conn, $chart_sql);
        mysqli_stmt_bind_param($stmt, "i", $member_id);
        mysqli_stmt_execute($stmt);
        $chart_result = mysqli_stmt_get_result($stmt);
        
        $chart_data = [];
        while($row = mysqli_fetch_assoc($chart_result)) {
            $month_name = date("M", mktime(0, 0, 0, $row['month'], 10));
            $chart_data[] = [
                'month' => $month_name,
                'deposits' => $row['deposits'],
                'withdrawals' => $row['withdrawals']
            ];
        }
        $chart_data_json = json_encode($chart_data);
        
    } else {
        echo "<div class='alert alert-danger'>
                <i class='bi bi-exclamation-triangle-fill me-2'></i>No member account found associated with your user account.
                Please contact an administrator.
              </div>";
        exit;
    }
} else {
    echo "Oops! Something went wrong.";
    exit;
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-person-circle me-2"></i>Member Dashboard</h2>
            <div>
                <span class="text-muted">Welcome, <?php echo htmlspecialchars($member['first_name']); ?>!</span>
            </div>
        </div>
        
        <!-- Balance Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body p-4 text-center">
                <div class="row align-items-center">
                    <div class="col-md-8 text-md-start">
                        <h4 class="text-muted mb-3">Current Account Balance</h4>
                        <h1 class="display-4 fw-bold mb-0 <?php echo ($balance >= 0) ? 'text-primary' : 'text-danger'; ?>">
                            ₱<?php echo number_format($balance, 2); ?>
                        </h1>
                        <p class="lead mt-2">
                            <?php if($balance < 0): ?>
                                You owe this amount to the cooperative.
                            <?php else: ?>
                                Your available funds in the cooperative.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <div class="d-grid gap-2">
                            <a href="transactions.php" class="btn btn-primary">
                                <i class="bi bi-list-ul me-2"></i>View All Transactions
                            </a>
                            <a href="#" class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-2"></i>Print Statement
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Account Information -->
            <div class="col-md-5 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-center">
                            <div class="avatar-circle mx-auto mb-3">
                                <span class="avatar-initials"><?php echo strtoupper(substr($member['first_name'], 0, 1) . substr($member['last_name'], 0, 1)); ?></span>
                            </div>
                            <h5 class="mb-1"><?php echo $member['first_name'] . ' ' . $member['last_name']; ?></h5>
                            <p class="text-muted mb-0">Member ID: <?php echo $member['member_id']; ?></p>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-1"><i class="bi bi-envelope text-primary"></i></div>
                            <div class="col-11"><?php echo $member['email']; ?></div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-1"><i class="bi bi-telephone text-primary"></i></div>
                            <div class="col-11"><?php echo $member['phone'] ?: 'Not provided'; ?></div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-1"><i class="bi bi-geo-alt text-primary"></i></div>
                            <div class="col-11"><?php echo $member['address'] ?: 'Not provided'; ?></div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="profile.php" class="btn btn-outline-primary">
                                <i class="bi bi-pencil-square me-2"></i>Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Transaction History Chart -->
            <div class="col-md-7 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Transaction History</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="memberTransactionsChart" width="400" height="220"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
                    <a href="transactions.php" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php
                if(mysqli_num_rows($transactions_result) > 0){
                ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while($transaction = mysqli_fetch_assoc($transactions_result)){
                                echo "<tr>";
                                echo "<td>" . $transaction['transaction_id'] . "</td>";
                                echo "<td>";
                                if($transaction['type'] == 'deposit') {
                                    echo "<span class='badge bg-success'><i class='bi bi-arrow-down-circle me-1'></i>Deposit</span>";
                                } else {
                                    echo "<span class='badge bg-warning'><i class='bi bi-arrow-up-circle me-1'></i>Withdrawal</span>";
                                }
                                echo "</td>";
                                echo "<td class='fw-bold'>";
                                if($transaction['type'] == 'deposit') {
                                    echo "<span class='text-success'>+₱" . number_format($transaction['amount'], 2) . "</span>";
                                } else {
                                    echo "<span class='text-warning'>-₱" . number_format($transaction['amount'], 2) . "</span>";
                                }
                                echo "</td>";
                                echo "<td>" . $transaction['description'] . "</td>";
                                echo "<td>" . date('M d, Y g:i A', strtotime($transaction['transaction_date'])) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
                } else{
                    echo "<div class='alert alert-info'>
                            <i class='bi bi-info-circle me-2'></i>No recent transactions found.
                          </div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 80px;
    height: 80px;
    background-color: #4361ee;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
}

.avatar-initials {
    color: white;
    font-size: 32px;
    font-weight: 600;
    line-height: 1;
}
</style>

<?php include "../includes/footer.php"; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart data
    const chartData = <?php echo $chart_data_json; ?>;
    
    // Prepare datasets
    const months = chartData.map(item => item.month);
    const deposits = chartData.map(item => item.deposits);
    const withdrawals = chartData.map(item => item.withdrawals);
    
    // Create chart
    const ctx = document.getElementById('memberTransactionsChart').getContext('2d');
    const memberTransactionsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Deposits',
                    data: deposits,
                    backgroundColor: 'rgba(76, 201, 240, 0.2)',
                    borderColor: '#4cc9f0',
                    borderWidth: 2,
                    pointBackgroundColor: '#4cc9f0',
                    pointRadius: 4,
                    tension: 0.2
                },
                {
                    label: 'Withdrawals',
                    data: withdrawals,
                    backgroundColor: 'rgba(247, 37, 133, 0.2)',
                    borderColor: '#f72585',
                    borderWidth: 2,
                    pointBackgroundColor: '#f72585',
                    pointRadius: 4,
                    tension: 0.2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₱' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>