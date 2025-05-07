<?php
// Include header
include_once "../includes/header.php";

// Include database connection
require_once "../config/database.php";

// Initialize filter variables
$member_filter = "";
$member_id = "";
$type_filter = "";
$transaction_type = "";
$date_filter = "";
$start_date = "";
$end_date = "";
$where_clause = "";

// Process filters
if(isset($_GET['member_id']) && !empty($_GET['member_id'])) {
    $member_id = intval($_GET['member_id']);
    $member_filter = " t.member_id = " . $member_id;
    $where_clause = " WHERE " . $member_filter;
}

if(isset($_GET['type']) && !empty($_GET['type'])) {
    $transaction_type = mysqli_real_escape_string($conn, $_GET['type']);
    $type_filter = " t.type = '" . $transaction_type . "'";
    if(empty($where_clause)) {
        $where_clause = " WHERE " . $type_filter;
    } else {
        $where_clause .= " AND " . $type_filter;
    }
}

if(isset($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $start_date = mysqli_real_escape_string($conn, $_GET['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_GET['end_date']);
    $date_filter = " DATE(t.transaction_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
    if(empty($where_clause)) {
        $where_clause = " WHERE " . $date_filter;
    } else {
        $where_clause .= " AND " . $date_filter;
    }
}

// Fetch all transactions with member names
$sql = "SELECT t.transaction_id, t.member_id, CONCAT(m.first_name, ' ', m.last_name) as member_name, 
        t.type, t.amount, t.description, t.transaction_date 
        FROM transactions t
        JOIN members m ON t.member_id = m.member_id" . $where_clause . 
        " ORDER BY t.transaction_date DESC";
$result = mysqli_query($conn, $sql);

// Get total transactions
$total_transactions = mysqli_num_rows($result);

// Get sum of deposits and withdrawals in current view
$deposit_sum = 0;
$withdrawal_sum = 0;
if($total_transactions > 0) {
    $sql = "SELECT 
              SUM(CASE WHEN t.type = 'deposit' THEN t.amount ELSE 0 END) as deposit_sum,
              SUM(CASE WHEN t.type = 'withdrawal' THEN t.amount ELSE 0 END) as withdrawal_sum
            FROM transactions t
            JOIN members m ON t.member_id = m.member_id" 
            . $where_clause;
    $sum_result = mysqli_query($conn, $sql);
    $sum_row = mysqli_fetch_assoc($sum_result);
    $deposit_sum = $sum_row['deposit_sum'] ?: 0;
    $withdrawal_sum = $sum_row['withdrawal_sum'] ?: 0;
}

// Fetch all members for the dropdown
$members_sql = "SELECT member_id, first_name, last_name FROM members ORDER BY first_name, last_name";
$members_result = mysqli_query($conn, $members_sql);
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-currency-exchange me-2"></i>Transactions</h2>
            <div>
                <div class="btn-group">
                    <a href="deposit.php" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i> Deposit
                    </a>
                    <a href="withdraw.php" class="btn btn-warning">
                        <i class="bi bi-dash-circle me-1"></i> Withdraw
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Filter Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter Transactions</h5>
            </div>
            <div class="card-body">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                    <div class="col-md-4">
                        <label for="member_id" class="form-label">Member</label>
                        <select name="member_id" class="form-select">
                            <option value="">All Members</option>
                            <?php
                            mysqli_data_seek($members_result, 0);
                            while($member_row = mysqli_fetch_assoc($members_result)){
                                $selected = ($member_id == $member_row['member_id']) ? 'selected' : '';
                                echo "<option value='" . $member_row['member_id'] . "' $selected>" . 
                                    $member_row['first_name'] . " " . $member_row['last_name'] . 
                                    "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label">Transaction Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="deposit" <?php echo ($transaction_type == 'deposit') ? 'selected' : ''; ?>>Deposits</option>
                            <option value="withdrawal" <?php echo ($transaction_type == 'withdrawal') ? 'selected' : ''; ?>>Withdrawals</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                            <span class="input-group-text">to</span>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-filter me-1"></i> Apply Filters
                        </button>
                        <a href="view.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Summary Card -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Total Transactions</h6>
                        <h2 class="card-text display-6"><?php echo $total_transactions; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Total Deposits</h6>
                        <h2 class="card-text display-6 text-success">₱<?php echo number_format($deposit_sum, 2); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Total Withdrawals</h6>
                        <h2 class="card-text display-6 text-warning">₱<?php echo number_format($withdrawal_sum, 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transactions Table -->
        <?php
        if($total_transactions > 0){
        ?>
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul me-2"></i>Transaction List
                        <span class="badge bg-secondary ms-2"><?php echo $total_transactions; ?> found</span>
                    </h5>
                    <button class="btn btn-sm btn-outline-primary btn-print">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Member</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Date/Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>";
                            echo "<td>" . $row['transaction_id'] . "</td>";
                            echo "<td>" . $row['member_name'] . "</td>";
                            echo "<td>";
                            if($row['type'] == 'deposit') {
                                echo "<span class='badge bg-success'><i class='bi bi-arrow-down-circle me-1'></i>Deposit</span>";
                            } else {
                                echo "<span class='badge bg-warning'><i class='bi bi-arrow-up-circle me-1'></i>Withdrawal</span>";
                            }
                            echo "</td>";
                            echo "<td class='fw-bold'>";
                            if($row['type'] == 'deposit') {
                                echo "<span class='text-success'>+₱" . number_format($row['amount'], 2) . "</span>";
                            } else {
                                echo "<span class='text-warning'>-₱" . number_format($row['amount'], 2) . "</span>";
                            }
                            echo "</td>";
                            echo "<td>" . $row['description'] . "</td>";
                            echo "<td>" . date('M d, Y g:i A', strtotime($row['transaction_date'])) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php if($total_transactions > 10): ?>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">Previous</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Next</a>
                        </li>
                    </ul>
                </nav>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="#">Export as PDF</a></li>
                        <li><a class="dropdown-item" href="#">Export as CSV</a></li>
                        <li><a class="dropdown-item" href="#">Export as Excel</a></li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        } else{
            echo "<div class='alert alert-info'>
                    <i class='bi bi-info-circle me-2'></i>No transactions found based on your filter criteria.
                    <a href='view.php' class='alert-link'>Clear filters</a> to see all transactions.
                  </div>";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>