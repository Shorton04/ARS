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
$username = $role = "";
$username_err = $role_err = $password_err = "";
$user_id = 0;

// Define the base URL for the project
if (!isset($base_url)) {
    $base_url = "/accounts_receivable_system"; // Use underscore to match your URL format
}

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Get hidden input value (user_id)
    $user_id = $_POST["user_id"];
    
    // Validate username
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter a username.";
    } else{
        // Prepare a select statement to check if username exists (excluding current user)
        $sql = "SELECT user_id FROM users WHERE username = ? AND user_id != ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_username, $param_user_id);
            
            // Set parameters
            $param_username = trim($_POST["username"]);
            $param_user_id = $user_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = "This username is already taken.";
                } else{
                    $username = trim($_POST["username"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate role
    if(empty(trim($_POST["role"]))){
        $role_err = "Please select a role.";
    } else{
        $role = trim($_POST["role"]);
    }
    
    // Validate password (only if provided - optional on edit)
    $password = trim($_POST["password"]);
    if(!empty($password)){
        if(strlen($password) < 6){
            $password_err = "Password must have at least 6 characters.";
        }
    }
    
    // Check input errors before updating in database
    if(empty($username_err) && empty($role_err) && empty($password_err)){
        // Prepare update statement
        // If password is provided, update it as well
        if(!empty($password)){
            $sql = "UPDATE users SET username = ?, role = ?, password = ? WHERE user_id = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "sssi", $param_username, $param_role, $param_password, $param_user_id);
                
                // Set parameters
                $param_username = $username;
                $param_role = $role;
                $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                $param_user_id = $user_id;
            }
        } else{
            // Don't update password if not provided
            $sql = "UPDATE users SET username = ?, role = ? WHERE user_id = ?";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssi", $param_username, $param_role, $param_user_id);
                
                // Set parameters
                $param_username = $username;
                $param_role = $role;
                $param_user_id = $user_id;
            }
        }
        
        // Attempt to execute the prepared statement
        if(mysqli_stmt_execute($stmt)){
            // Records updated successfully. Redirect to users view page
            header("location: {$base_url}/admin/users/view.php");
            exit();
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }
} else{
    // Check if user_id parameter is set
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $user_id = trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM users WHERE user_id = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_user_id);
            
            // Set parameters
            $param_user_id = $user_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
                
                if(mysqli_num_rows($result) == 1){
                    // Fetch result row as an associative array
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $username = $row["username"];
                    $role = $row["role"];
                } else{
                    // No valid user found with the given ID
                    header("location: {$base_url}/admin/users/view.php");
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
        header("location: {$base_url}/admin/users/view.php");
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
            <h2><i class="bi bi-pencil-square me-2"></i>Edit User</h2>
            <a href="<?php echo $base_url; ?>/admin/users/view.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person me-2"></i>User Information</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                    
                    <div class="mb-3 row">
                        <label for="username" class="col-md-3 col-form-label">Username</label>
                        <div class="col-md-9">
                            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" required>
                            <div class="invalid-feedback"><?php echo $username_err; ?></div>
                        </div>
                    </div>
                    
                    <div class="mb-3 row">
                        <label for="password" class="col-md-3 col-form-label">Password</label>
                        <div class="col-md-9">
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Leave blank to keep current password">
                            <div class="invalid-feedback"><?php echo $password_err; ?></div>
                            <div class="form-text text-muted">Enter a new password only if you want to change it.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3 row">
                        <label for="role" class="col-md-3 col-form-label">Role</label>
                        <div class="col-md-9">
                            <select name="role" class="form-select <?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                                <option value="">Select Role</option>
                                <option value="admin" <?php echo ($role == "admin") ? 'selected' : ''; ?>>Admin</option>
                                <option value="member" <?php echo ($role == "member") ? 'selected' : ''; ?>>Member</option>
                            </select>
                            <div class="invalid-feedback"><?php echo $role_err; ?></div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo $base_url; ?>/admin/users/view.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>