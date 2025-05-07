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

// Fetch users without associated member accounts
$sql = "SELECT u.user_id, u.username FROM users u 
        LEFT JOIN members m ON u.user_id = m.user_id 
        WHERE m.member_id IS NULL AND u.role = 'member'";
$users_result = mysqli_query($conn, $sql);

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
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
    
    // Check input errors before inserting in database
    if(empty($first_name_err) && empty($last_name_err) && empty($email_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO members (first_name, last_name, email, phone, address, user_id) VALUES (?, ?, ?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sssssi", $param_first_name, $param_last_name, $param_email, $param_phone, $param_address, $param_user_id);
            
            // Set parameters
            $param_first_name = $first_name;
            $param_last_name = $last_name;
            $param_email = $email;
            $param_phone = $phone;
            $param_address = $address;
            
            // Handle null value for user_id
            if(empty($user_id)){
                $param_user_id = NULL;
                mysqli_stmt_bind_param($stmt, "sssss", $param_first_name, $param_last_name, $param_email, $param_phone, $param_address);
            } else {
                $param_user_id = $user_id;
            }
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to members page
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
        <?php include "../../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <h2>Add New Member</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control <?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $first_name; ?>">
                <span class="invalid-feedback"><?php echo $first_name_err; ?></span>
            </div>    
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control <?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $last_name; ?>">
                <span class="invalid-feedback"><?php echo $last_name_err; ?></span>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="3"><?php echo $address; ?></textarea>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">Link to User Account (Optional)</label>
                <select name="user_id" class="form-select">
                    <option value="">Select User (Optional)</option>
                    <?php
                    while($user_row = mysqli_fetch_assoc($users_result)){
                        echo "<option value='" . $user_row['user_id'] . "'>" . $user_row['username'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <input type="submit" class="btn btn-primary" value="Submit">
                <a href="view.php" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>