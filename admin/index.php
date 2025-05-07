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

// Get recent transactions
$sql = "SELECT t.*, CONCAT(m.first_name, ' ', m.last_name) as member_name 
        FROM transactions t
        JOIN members m ON t.member_id = m.member_id
        ORDER BY t.transaction_date DESC
        LIMIT 5";
$recent_transactions = mysqli_query($conn, $sql);

// Get monthly transaction data for chart
$sql = "SELECT 
          MONTH(transaction_date) as month, 
          SUM(CASE WHEN type = 'deposit' THEN amount ELSE 0 END) as deposits,
          SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END) as withdrawals
        FROM transactions
        WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY MONTH(transaction_date)
        ORDER BY MONTH(transaction_date) ASC";
$monthly_data = mysqli_query($conn, $sql);
$chart_data = [];

while($row = mysqli_fetch_assoc($monthly_data)) {
    $month_name = date("M", mktime(0, 0, 0, $row['month'], 10));
    $chart_data[] = [
        'month' => $month_name,
        'deposits' => $row['deposits'],
        'withdrawals' => $row['withdrawals']
    ];
}
$chart_data_json = json_encode($chart_data);
?>

<div class="row">
    <div class="col-lg-3">
        <?php include "../includes/sidebar.php"; ?>
    </div>
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h2>
            <div>
                <span class="text-muted">Today: <?php echo date('F d, Y'); ?></span>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Users</h5>
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                        <h2 class="card-text mt-3"><?php echo $total_users; ?></h2>
                        <a href="../admin/users/view.php" class="text-white mt-auto">Manage Users <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Members</h5>
                            <i class="bi bi-person-badge fs-3"></i>
                        </div>
                        <h2 class="card-text mt-3"><?php echo $total_members; ?></h2>
                        <a href="../admin/members/view.php" class="text-white mt-auto">Manage Members <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card bg-info text-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Transactions</h5>
                            <i class="bi bi-currency-exchange fs-3"></i>
                        </div>
                        <h2 class="card-text mt-3"><?php echo $total_transactions; ?></h2>
                        <a href="../transactions/view.php" class="text-white mt-auto">View Transactions <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">Net Balance</h5>
                            <i class="bi bi-cash-coin fs-3"></i>
                        </div>
                        <h2 class="card-text mt-3">₱<?php echo number_format($net_balance, 2); ?></h2>
                        <a href="../admin/reports/balances.php" class="text-white mt-auto">View Reports <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Financial Summary & Transaction Chart -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Financial Summary</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th><i class="bi bi-arrow-down-circle text-success me-2"></i>Total Deposits</th>
                                    <td class="text-end fw-bold">₱<?php echo number_format($total_deposits, 2); ?></td>
                                </tr>
                                <tr>
                                    <th><i class="bi bi-arrow-up-circle text-warning me-2"></i>Total Withdrawals</th>
                                    <td class="text-end fw-bold">₱<?php echo number_format($total_withdrawals, 2); ?></td>
                                </tr>
                                <tr class="table-info">
                                    <th><i class="bi bi-cash-stack me-2"></i>Net Balance</th>
                                    <td class="text-end fw-bold">₱<?php echo number_format($net_balance, 2); ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="../admin/reports/balances.php" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-text me-2"></i>Generate Balance Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Monthly Transactions</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="transactionsChart" width="400" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Transactions -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
                    <a href="../transactions/view.php" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php if(mysqli_num_rows($recent_transactions) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Member</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($recent_transactions)): ?>
                            <tr>
                                <td><?php echo $row['transaction_id']; ?></td>
                                <td><?php echo $row['member_name']; ?></td>
                                <td>
                                    <?php if($row['type'] == 'deposit'): ?>
                                    <span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i>Deposit</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning"><i class="bi bi-arrow-up-circle me-1"></i>Withdrawal</span>
                                    <?php endif; ?>
                                </td>
                                <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y g:i A', strtotime($row['transaction_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>No recent transactions found.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

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
    const ctx = document.getElementById('transactionsChart').getContext('2d');
    const transactionsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Deposits',
                    data: deposits,
                    backgroundColor: '#4cc9f0',
                    borderColor: '#4cc9f0',
                    borderWidth: 1
                },
                {
                    label: 'Withdrawals',
                    data: withdrawals,
                    backgroundColor: '#f72585',
                    borderColor: '#f72585',
                    borderWidth: 1
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