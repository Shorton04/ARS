<?php
// Include header
include_once "../includes/header.php";

// Include database connection
require_once "../config/database.php";
require_once "../includes/functions.php";

// Define variables and initialize with empty values
$member_id = $amount = $description = "";
$member_id_err = $amount_err = $description_err = "";
$success_message = "";
$member_balance = 0;

// Fetch all members for the dropdown
$members_sql = "SELECT member_id, first_name, last_name FROM members ORDER BY first_name, last_name";
$members_result = mysqli_query($conn, $members_sql);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate member
    if(empty(trim($_POST["member_id"]))){
        $member_id_err = "Please select a member.";
    } else{
        $member_id = trim($_POST["member_id"]);
        // Get member balance
        $member_balance = getMemberBalance($conn, $member_id);
    }
    
    // Validate amount
    if(empty(trim($_POST["amount"]))){
        $amount_err = "Please enter an amount.";
    } elseif(!is_numeric(trim($_POST["amount"])) || floatval(trim($_POST["amount"])) <= 0){
        $amount_err = "Please enter a valid positive amount.";
    } else{
        $amount = trim($_POST["amount"]);
        
        // Check if member has sufficient balance
        if(!empty($member_id)){
            if($amount > $member_balance){
                $amount_err = "Insufficient balance. Available: ₱" . number_format($member_balance, 2);
            }
        }
    }
    
    // Validate description (optional)
    if(!empty(trim($_POST["description"]))){
        $description = trim($_POST["description"]);
    }
    
    // Check input errors before inserting in database
    if(empty($member_id_err) && empty($amount_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO transactions (member_id, type, amount, description) VALUES (?, 'withdrawal', ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ids", $param_member_id, $param_amount, $param_description);
            
            // Set parameters
            $param_member_id = $member_id;
            $param_amount = $amount;
            $param_description = $description;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Set success message
                $success_message = "Withdrawal recorded successfully!";
                
                // Clear form data
                $member_id = $amount = $description = "";
                $member_balance = 0;
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Handle AJAX request for member balance
if(isset($_GET['get_balance']) && !empty($_GET['member_id'])) {
    $ajax_member_id = intval($_GET['member_id']);
    $ajax_balance = getMemberBalance($conn, $ajax_member_id);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'balance' => $ajax_balance]);
    exit;
}
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-dash-circle me-2"></i>Record Withdrawal</h2>
            <a href="view.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Transactions
            </a>
        </div>
        
        <?php if(!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Withdrawal Information</h5>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <label for="member_id" class="col-md-3 col-form-label">Select Member <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <select name="member_id" id="member_id" class="form-select <?php echo (!empty($member_id_err)) ? 'is-invalid' : ''; ?>" required>
                                <option value="">-- Select Member --</option>
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
                            <div class="invalid-feedback"><?php echo $member_id_err; ?></div>
                        </div>
                    </div>
                    
                    <div id="balance-info" class="row mb-3 <?php echo (empty($member_id)) ? 'd-none' : ''; ?>">
                        <div class="col-md-9 offset-md-3">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <span id="balance-display">
                                    <?php if(!empty($member_id)): ?>
                                    Available Balance: ₱<?php echo number_format($member_balance, 2); ?>
                                    <?php else: ?>
                                    Select a member to see available balance
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="amount" class="col-md-3 col-form-label">Amount <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="amount" id="amount" class="form-control <?php echo (!empty($amount_err)) ? 'is-invalid' : ''; ?>" step="0.01" min="0.01" value="<?php echo $amount; ?>" placeholder="Enter withdrawal amount" required>
                                <div class="invalid-feedback"><?php echo $amount_err; ?></div>
                            </div>
                            <small class="text-muted">Amount must not exceed available balance</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="description" class="col-md-3 col-form-label">Description</label>
                        <div class="col-md-9">
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description or purpose of this withdrawal"><?php echo $description; ?></textarea>
                            <small class="text-muted">Optional: Provide details about this withdrawal</small>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-9 offset-md-3">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle me-2"></i>Record Withdrawal
                            </button>
                            <a href="view.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Quick Info Card -->
        <div class="card mt-4 shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Withdrawal Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">What is a Withdrawal?</h6>
                        <p>A withdrawal is when a member takes funds from their account with the cooperative. This decreases their available balance.</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Important Notes</h6>
                        <ul class="mb-0">
                            <li>Members can only withdraw up to their available balance</li>
                            <li>Verify member identity before processing withdrawals</li>
                            <li>Include the purpose of withdrawal in the description</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom JavaScript for balance checking -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const memberSelect = document.getElementById('member_id');
    const balanceInfo = document.getElementById('balance-info');
    const balanceDisplay = document.getElementById('balance-display');
    
    // Update balance when member is selected
    memberSelect.addEventListener('change', function() {
        const memberId = this.value;
        
        if(memberId) {
            // Show balance info section
            balanceInfo.classList.remove('d-none');
            
            // Display loading message
            balanceDisplay.innerHTML = '<small>Loading balance...</small>';
            
            // Fetch member balance via AJAX
            fetch(`withdraw.php?get_balance=true&member_id=${memberId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        const formattedBalance = new Intl.NumberFormat('en-PH', { 
                            style: 'currency', 
                            currency: 'PHP',
                            minimumFractionDigits: 2
                        }).format(data.balance);
                        
                        balanceDisplay.innerHTML = `Available Balance: ${formattedBalance}`;
                        
                        // Store balance for validation
                        document.getElementById('amount').dataset.maxBalance = data.balance;
                    } else {
                        balanceDisplay.innerHTML = 'Error fetching balance.';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    balanceDisplay.innerHTML = 'Error fetching balance.';
                });
        } else {
            // Hide balance info if no member selected
            balanceInfo.classList.add('d-none');
        }
    });
    
    // Validate amount against balance
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('input', function() {
        const maxBalance = parseFloat(this.dataset.maxBalance || 0);
        const amount = parseFloat(this.value || 0);
        
        if(amount > maxBalance) {
            this.classList.add('is-invalid');
            this.setCustomValidity(`Amount exceeds available balance of ₱${maxBalance.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
        } else {
            this.classList.remove('is-invalid');
            this.setCustomValidity('');
        }
    });
});
</script>

<?php include "../includes/footer.php"; ?>