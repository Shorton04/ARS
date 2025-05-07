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

// Fetch all users
$sql = "SELECT user_id, username, role, created_at FROM users";
$result = mysqli_query($conn, $sql);
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Users</h2>
            <a href="add.php" class="btn btn-primary">Add New User</a>
        </div>
        
        <?php
        // Check if there are any users
        if(mysqli_num_rows($result) > 0){
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<tr>";
                        echo "<td>" . $row['user_id'] . "</td>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "<td>" . ucfirst($row['role']) . "</td>";
                        echo "<td>" . $row['created_at'] . "</td>";
                        echo "<td>";
                        echo "<a href='edit.php?id=" . $row['user_id'] . "' class='btn btn-sm btn-info me-2'>Edit</a>";
                        echo "<a href='delete.php?id=" . $row['user_id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
        } else{
            echo "<div class='alert alert-info'>No users found.</div>";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>