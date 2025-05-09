<?php
// Define the base URL for the project - change this to match your setup
$base_url = "/accounts_receivable_system"; // Use underscore to match your URL format
?>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0">Navigation</h5>
    </div>
    <div class="list-group list-group-flush sidebar">
        <a href="<?php echo $base_url; ?>/admin/index.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/index.php') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a href="<?php echo $base_url; ?>/admin/users/view.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/users/') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-people me-2"></i>Manage Users
        </a>
        <a href="<?php echo $base_url; ?>/admin/members/view.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/members/') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-person-badge me-2"></i>Manage Members
        </a>
        <a href="<?php echo $base_url; ?>/transactions/view.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/transactions/') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-currency-exchange me-2"></i>Transactions
        </a>
        <a href="<?php echo $base_url; ?>/admin/reports/balances.php" class="list-group-item list-group-item-action <?php echo (strpos($_SERVER['PHP_SELF'], '/admin/reports/') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-file-earmark-bar-graph me-2"></i>Member Balances
        </a>
        <a href="javascript:void(0);" onclick="confirmLogout()" class="list-group-item list-group-item-action text-danger">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
        </a>
    </div>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2">
            <a href="<?php echo $base_url; ?>/admin/members/add.php" class="btn btn-outline-primary">
                <i class="bi bi-person-plus me-2"></i>Add Member
            </a>
            <a href="<?php echo $base_url; ?>/transactions/deposit.php" class="btn btn-outline-success">
                <i class="bi bi-cash-coin me-2"></i>Record Deposit
            </a>
            <a href="<?php echo $base_url; ?>/transactions/withdraw.php" class="btn btn-outline-warning">
                <i class="bi bi-cash-stack me-2"></i>Record Withdrawal
            </a>
        </div>
    </div>
</div>

<!-- JavaScript for Logout Confirmation -->
<script>
function confirmLogout() {
    // Create modal content
    const modalContent = `
        <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title" id="logoutConfirmModalLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>Confirm Logout
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-4">
                            <i class="bi bi-box-arrow-right text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="mb-3">Are you sure you want to logout?</h4>
                        <p class="text-muted mb-0">You will be logged out of your account and redirected to the login page.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <a href="<?php echo $base_url; ?>/auth/logout_process.php" class="btn btn-warning">
                            <i class="bi bi-check-circle me-2"></i>Yes, Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove any existing modal
    const existingModal = document.getElementById('logoutConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Initialize and show modal
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutConfirmModal'));
    logoutModal.show();
}
</script>