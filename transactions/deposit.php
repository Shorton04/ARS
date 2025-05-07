<?php
// Include header
include_once "../includes/header.php";

// Include database connection
require_once "../config/database.php";

// Define variables and initialize with empty values
$member_id = $amount = $description = "";
$member_id_err = $amount_err = $description_err = "";
$success_message = "";

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
    }
    
    // Validate amount
    if(empty(trim($_POST["amount"]))){
        $amount_err = "Please enter an amount.";
    } elseif(!is_numeric(trim($_POST["amount"])) || floatval(trim($_POST["amount"])) <= 0){
        $amount_err = "Please enter a valid positive amount.";
    } else{
        $amount = trim($_POST["amount"]);
    }
    
    // Validate description (optional)
    if(!empty(trim($_POST["description"]))){
        $description = trim($_POST["description"]);
    }
    
    // Check input errors before inserting in database
    if(empty($member_id_err) && empty($amount_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO transactions (member_id, type, amount, description) VALUES (?, 'deposit', ?, ?)";
         
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
                $success_message = "Deposit recorded successfully!";
                
                // Clear form data
                $member_id = $amount = $description = "";
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-plus-circle me-2"></i>Record Deposit</h2>
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
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Deposit Information</h5>
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
                    
                    <div class="row mb-3">
                        <label for="amount" class="col-md-3 col-form-label">Amount <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" name="amount" id="amount" class="form-control <?php echo (!empty($amount_err)) ? 'is-invalid' : ''; ?>" step="0.01" min="0.01" value="<?php echo $amount; ?>" placeholder="Enter deposit amount" required>
                                <div class="invalid-feedback"><?php echo $amount_err; ?></div>
                            </div>
                            <small class="text-muted">Enter amount in Philippine Peso (₱)</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="description" class="col-md-3 col-form-label">Description</label>
                        <div class="col-md-9">
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Enter description or notes for this deposit"><?php echo $description; ?></textarea>
                            <small class="text-muted">Optional: Provide details about this deposit</small>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-9 offset-md-3">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle me-2"></i>Record Deposit
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
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Deposit Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">What is a Deposit?</h6>
                        <p>A deposit is when a member adds funds to their account with the cooperative. This increases their available balance.</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Recording Tips</h6>
                        <ul class="mb-0">
                            <li>Verify member information before recording</li>
                            <li>Double-check the amount to avoid errors</li>
                            <li>Include a detailed description for reference</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>