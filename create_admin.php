<?php
// Include database connection
$db_config_path = "../config/database.php";

// Check if the database config file exists
if (!file_exists($db_config_path)) {
    $db_config_path = "config/database.php"; // Try alternate path
    if (!file_exists($db_config_path)) {
        die("Database configuration file not found. Please place this file in your project root or admin directory.");
    }
}

require_once $db_config_path;

// Initialize variables
$success_message = $error_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate username
    if (empty(trim($_POST["username"]))) {
        $error_message = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
        
        // Check if username already exists
        $sql = "SELECT user_id FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $error_message = "This username is already taken.";
                }
            } else {
                $error_message = "Database query failed: " . mysqli_error($conn);
            }
            
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $error_message = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $error_message = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // If no errors, create the user
    if (empty($error_message)) {
        // Create the users table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'member') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        if (!mysqli_query($conn, $create_table_sql)) {
            $error_message = "Error creating users table: " . mysqli_error($conn);
        } else {
            // Insert the new admin user
            $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);
                
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_message = "Admin user created successfully! You can now log in.";
                } else {
                    $error_message = "Error creating user: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        }
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin User</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Create Admin User</h4>
                    </div>
                    <div class="card-body">
                        <?php 
                        if (!empty($success_message)) {
                            echo '<div class="alert alert-success">' . $success_message . '</div>';
                        }
                        
                        if (!empty($error_message)) {
                            echo '<div class="alert alert-danger">' . $error_message . '</div>';
                        }
                        ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Password must be at least 6 characters.</small>
                            </div>
                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary">Create Admin User</button>
                            </div>
                        </form>
                        
                        <div class="mt-3">
                            <a href="../index.php">← Back to Homepage</a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Database Connection Status</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Re-establish connection to check status
                        $check_conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
                        
                        if ($check_conn) {
                            echo '<div class="alert alert-success mb-0">Connected to database: ' . DB_NAME . '</div>';
                            mysqli_close($check_conn);
                        } else {
                            echo '<div class="alert alert-danger mb-0">Database connection failed: ' . mysqli_connect_error() . '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>