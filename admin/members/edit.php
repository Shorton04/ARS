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

// Define variables and initialize with empty values
$first_name = $last_name = $email = $phone = $address = $user_id = "";
$first_name_err = $last_name_err = $email_err = $phone_err = $address_err = $user_id_err = "";
$member_id = 0;

// Define the base URL for the project
if (!isset($base_url)) {
    $base_url = "/accounts_receivable_system"; // Use underscore to match your URL format
}

// Fetch users who can be linked to this member
// This includes users with no linked member or the current member's linked user
$sql = "SELECT u.user_id, u.username FROM users u 
        LEFT JOIN members m ON u.user_id = m.user_id 
        WHERE (m.member_id IS NULL OR m.member_id = ?) AND u.role = 'member'";
$stmt = mysqli_prepare($conn, $sql);
$param_member_id = isset($_GET["id"]) ? trim($_GET["id"]) : 0;
mysqli_stmt_bind_param($stmt, "i", $param_member_id);
mysqli_stmt_execute($stmt);
$users_result = mysqli_stmt_get_result($stmt);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Get hidden input value (member_id)
    $member_id = $_POST["member_id"];
    
    // Validate first name
    if(empty(trim($_POST["first_name"]))){
        $first_name_err = "Please enter first name.";
    } else{
        $first_name = trim($_POST["first_name"]);
    }
    
    // Validate last name
    if(empty(trim($_POST["last_name"]))){
        $last_name_err = "Please enter last name.";
    } else{
        $last_name = trim($_POST["last_name"]);
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)){
        $email_err = "Please enter a valid email.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Validate phone (optional)
    if(!empty(trim($_POST["phone"]))){
        $phone = trim($_POST["phone"]);
    }
    
    // Validate address (optional)
    if(!empty(trim($_POST["address"]))){
        $address = trim($_POST["address"]);
    }
    
    // Validate user_id (optional)
    if(!empty(trim($_POST["user_id"]))){
        $user_id = trim($_POST["user_id"]);
    }
    
    // Check input errors before updating in database
    if(empty($first_name_err) && empty($last_name_err) && empty($email_err)){
        
        // Prepare an update statement
        $sql = "UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, user_id = ? WHERE member_id = ?";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Set parameters
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_phone = $phone;
            $param_address = $address;
            $param_member_id = $member_id;
            
            // Handle null value for user_id
            if(empty($user_id)){
                $param_user_id = NULL;
                mysqli_stmt_bind_param($stmt, "sssssii", 
                    $param_first_name, 
                    $param_last_name, 
                    $param_email, 
                    $param_phone, 
                    $param_address, 
                    $param_user_id,
                    $param_member_id
                );
            } else {
                $param_user_id = $user_id;
                mysqli_stmt_bind_param($stmt, "sssssii", 
                    $param_first_name, 
                    $param_last_name, 
                    $param_email, 
                    $param_phone, 
                    $param_address, 
                    $param_user_id,
                    $param_member_id
                );
            }
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records updated successfully. Redirect to members page
                header("location: {$base_url}/admin/members/view.php?success=updated");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
} else{
    // Check if member_id parameter is set
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $member_id = trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM members WHERE member_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_member_id);
            
            // Set parameters
            $param_member_id = $member_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) == 1){
                    // Fetch result row as an associative array
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $first_name = $row["first_name"];
                    $last_name = $row["last_name"];
                    $email = $row["email"];
                    $phone = $row["phone"];
                    $address = $row["address"];
                    $user_id = $row["user_id"];
                } else{
                    // No valid member found with the given ID
                    header("location: {$base_url}/admin/members/view.php");
                    exit();
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
    } else{
        // No valid ID parameter provided
        header("location: {$base_url}/admin/members/view.php");
        exit();
    }
}
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-pencil-square me-2"></i>Edit Member</h2>
            <a href="<?php echo $base_url; ?>/admin/members/view.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Members
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Member Information</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
                    
                    <div class="row mb-3">
                        <label for="first_name" class="col-md-3 col-form-label">First Name</label>
                        <div class="col-md-9">
                            <input type="text" name="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $first_name; ?>" required>
                            <div class="invalid-feedback"><?php echo $first_name_err; ?></div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="last_name" class="col-md-3 col-form-label">Last Name</label>
                        <div class="col-md-9">
                            <input type="text" name="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $last_name; ?>" required>
                            <div class="invalid-feedback"><?php echo $last_name_err; ?></div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="email" class="col-md-3 col-form-label">Email</label>
                        <div class="col-md-9">
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" required>
                            <div class="invalid-feedback"><?php echo $email_err; ?></div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="phone" class="col-md-3 col-form-label">Phone</label>
                        <div class="col-md-9">
                            <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>">
                            <div class="form-text text-muted">Optional</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="address" class="col-md-3 col-form-label">Address</label>
                        <div class="col-md-9">
                            <textarea name="address" class="form-control" rows="3"><?php echo $address; ?></textarea>
                            <div class="form-text text-muted">Optional</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <label for="user_id" class="col-md-3 col-form-label">Link to User Account</label>
                        <div class="col-md-9">
                            <select name="user_id" class="form-select">
                                <option value="">Select User (Optional)</option>
                                <?php
                                while($user_row = mysqli_fetch_assoc($users_result)){
                                    $selected = ($user_id == $user_row['user_id']) ? 'selected' : '';
                                    echo "<option value='" . $user_row['user_id'] . "' $selected>" . $user_row['username'] . "</option>";
                                }
                                ?>
                            </select>
                            <div class="form-text text-muted">Optional: Link this member to a user account</div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo $base_url; ?>/admin/members/view.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>