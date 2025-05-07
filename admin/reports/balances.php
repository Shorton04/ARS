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

// Handle searching/filtering
$search = '';
$search_condition = '';

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_condition = " WHERE m.first_name LIKE '%".mysqli_real_escape_string($conn, $search)."%' 
                          OR m.last_name LIKE '%".mysqli_real_escape_string($conn, $search)."%'
                          OR m.email LIKE '%".mysqli_real_escape_string($conn, $search)."%'";
}

// Fetch all members with their balances
$sql = "SELECT m.member_id, m.first_name, m.last_name, m.email, m.phone FROM members m"
       . $search_condition . " ORDER BY m.last_name, m.first_name";
$result = mysqli_query($conn, $sql);

// Current date for report
$report_date = date('F d, Y');
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-bar-graph me-2"></i>Member Balances Report</h2>
            <div>
                <button class="btn btn-primary btn-print">
                    <i class="bi bi-printer me-2"></i>Print Report
                </button>
                <div class="dropdown d-inline-block ms-2">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-pdf me-2"></i>Export as PDF</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-filetype-csv me-2"></i>Export as CSV</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-file-excel me-2"></i>Export as Excel</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if(!empty($search)): ?>
                            <a href="balances.php" class="btn btn-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="badge bg-secondary"><?php echo mysqli_num_rows($result); ?> member(s) found</span>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Report Header (Only visible when printing) -->
        <div class="d-none d-print-block mb-4">
            <div class="text-center">
                <h3>Multipurpose Cooperative</h3>
                <h5>Member Balances Report</h5>
                <p>Generated on: <?php echo $report_date; ?></p>
            </div>
        </div>
        
        <?php
        // Check if there are any members
        if(mysqli_num_rows($result) > 0){
        ?>
        <div class="card shadow-sm">
            <div class="card-header bg-light d-print-none">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>Member Balances
                    <span class="badge bg-secondary ms-2"><?php echo date('M d, Y'); ?></span>
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Member ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-end">Total Deposits</th>
                            <th class="text-end">Total Withdrawals</th>
                            <th class="text-end">Current Balance</th>
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
                            
                            // Set balance class based on value
                            $balance_class = '';
                            if($balance > 0) {
                                $balance_class = 'text-success';
                            } elseif($balance < 0) {
                                $balance_class = 'text-danger';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . $row['member_id'] . "</td>";
                            echo "<td><strong>" . $row['first_name'] . " " . $row['last_name'] . "</strong></td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['phone'] . "</td>";
                            echo "<td class='text-end'>₱" . number_format($total_deposits, 2) . "</td>";
                            echo "<td class='text-end'>₱" . number_format($total_withdrawals, 2) . "</td>";
                            echo "<td class='text-end fw-bold " . $balance_class . "'>₱" . number_format($balance, 2) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                        <tr class="table-dark fw-bold">
                            <td colspan="4" class="text-end">Grand Total:</td>
                            <td class="text-end">₱<?php echo number_format($grand_total_deposits, 2); ?></td>
                            <td class="text-end">₱<?php echo number_format($grand_total_withdrawals, 2); ?></td>
                            <td class="text-end <?php echo ($grand_total_balance >= 0) ? 'text-success' : 'text-danger'; ?>">
                                ₱<?php echo number_format($grand_total_balance, 2); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="row mt-4 d-print-none">
            <div class="col-md-4 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-muted mb-0">Total Deposits</h6>
                            <i class="bi bi-arrow-down-circle text-success fs-3"></i>
                        </div>
                        <h3 class="display-6 text-success mb-0">₱<?php echo number_format($grand_total_deposits, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-muted mb-0">Total Withdrawals</h6>
                            <i class="bi bi-arrow-up-circle text-warning fs-3"></i>
                        </div>
                        <h3 class="display-6 text-warning mb-0">₱<?php echo number_format($grand_total_withdrawals, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="card-title text-muted mb-0">Net Balance</h6>
                            <i class="bi bi-cash-stack text-primary fs-3"></i>
                        </div>
                        <h3 class="display-6 <?php echo ($grand_total_balance >= 0) ? 'text-success' : 'text-danger'; ?> mb-0">
                            ₱<?php echo number_format($grand_total_balance, 2); ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Report Footer (Only visible when printing) -->
        <div class="d-none d-print-block mt-4">
            <div class="row">
                <div class="col-md-6">
                    <p>Prepared by: _______________________</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Verified by: _______________________</p>
                </div>
            </div>
        </div>
        
        <?php
        } else{
            echo "<div class='alert alert-info'>
                    <i class='bi bi-info-circle me-2'></i>No members found. <a href='../members/add.php' class='alert-link'>Add a new member</a> to get started.
                  </div>";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<style>
/* Print-specific styles */
@media print {
    body {
        font-size: 12pt;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th, .table td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    
    .table th {
        background-color: #f2f2f2 !important;
        font-weight: bold;
    }
    
    .table-dark td {
        background-color: #f2f2f2 !important;
        color: #000 !important;
        font-weight: bold;
    }
    
    .text-success {
        color: #28a745 !important;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
}
</style>

<?php include "../../includes/footer.php"; ?>