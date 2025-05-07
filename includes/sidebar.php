<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">Navigation</h5>
    </div>
    <div class="list-group list-group-flush sidebar">
        <a href="../admin/index.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/index.php') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a href="../admin/users/view.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/users/') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-people me-2"></i>Manage Users
        </a>
        <a href="../admin/members/view.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/members/') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-person-badge me-2"></i>Manage Members
        </a>
        <a href="../transactions/view.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/transactions/') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-currency-exchange me-2"></i>Transactions
        </a>
        <a href="../admin/reports/balances.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/reports/') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>Member Balances
        </a>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2">
            <a href="../admin/members/add.php" class="btn btn-outline-primary">
                <i class="bi bi-person-plus me-2"></i>Add Member
            </a>
            <a href="../transactions/deposit.php" class="btn btn-outline-success">
                <i class="bi bi-cash-coin me-2"></i>Record Deposit
            </a>
            <a href="../transactions/withdraw.php" class="btn btn-outline-warning">
                <i class="bi bi-cash-stack me-2"></i>Record Withdrawal
            </a>
        </div>
    </div>
</div>