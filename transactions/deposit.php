<?php
// Include header
include_once "../includes/header.php";

// Include database connection
require_once "../config/database.php";

// Define variables and initialize with empty values
$member_id = $amount = $description = "";
$member_id_err = $amount_err = $description_err = "";

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
                // Redirect to transactions page
                header("location: view.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <h2>Record Deposit</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="member_id" class="form-label">Member</label>
                <select name="member_id" class="form-select <?php echo (!empty($member_id_err)) ? 'is-invalid' : ''; ?>">
                    <option value="">Select Member</option>
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
                <span class="invalid-feedback"><?php echo $member_id_err; ?></span>
            </div>    
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="amount" step="0.01" min="0.01" class="form-control <?php echo (!empty($amount_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $amount; ?>">
                    <span class="invalid-feedback"><?php echo $amount_err; ?></span>
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo $description; ?></textarea>
            </div>
            <div class="mb-3">
                <input type="submit" class="btn btn-success" value="Record Deposit">
                <a href="view.php" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>