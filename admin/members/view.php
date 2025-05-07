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
require_once "../../includes/functions.php";

// Fetch all members
$sql = "SELECT m.member_id, m.first_name, m.last_name, m.email, m.phone FROM members m";
$result = mysqli_query($conn, $sql);
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Members</h2>
            <a href="add.php" class="btn btn-primary">Add New Member</a>
        </div>
        
        <?php
        // Check if there are any members
        if(mysqli_num_rows($result) > 0){
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($row = mysqli_fetch_assoc($result)){
                        // Get member balance
                        $balance = getMemberBalance($conn, $row['member_id']);
                        
                        echo "<tr>";
                        echo "<td>" . $row['member_id'] . "</td>";
                        echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                        echo "<td>" . $row['email'] . "</td>";
                        echo "<td>" . $row['phone'] . "</td>";
                        echo "<td>₱" . number_format($balance, 2) . "</td>";
                        echo "<td>";
                        echo "<a href='edit.php?id=" . $row['member_id'] . "' class='btn btn-sm btn-info me-1'>Edit</a>";
                        echo "<a href='delete.php?id=" . $row['member_id'] . "' class='btn btn-sm btn-danger me-1' onclick='return confirm(\"Are you sure you want to delete this member?\")'>Delete</a>";
                        echo "<a href='../../transactions/view.php?member_id=" . $row['member_id'] . "' class='btn btn-sm btn-secondary'>Transactions</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
        } else{
            echo "<div class='alert alert-info'>No members found.</div>";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>