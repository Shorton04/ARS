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

// Handle search
$search = '';
$search_condition = '';

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_condition = " WHERE m.first_name LIKE '%".mysqli_real_escape_string($conn, $search)."%' 
                          OR m.last_name LIKE '%".mysqli_real_escape_string($conn, $search)."%'
                          OR m.email LIKE '%".mysqli_real_escape_string($conn, $search)."%'
                          OR m.phone LIKE '%".mysqli_real_escape_string($conn, $search)."%'";
}

// Fetch all members
$sql = "SELECT m.member_id, m.first_name, m.last_name, m.email, m.phone FROM members m" . $search_condition . " ORDER BY m.last_name, m.first_name";
$result = mysqli_query($conn, $sql);
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-person-badge me-2"></i>Manage Members</h2>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Add New Member
            </a>
        </div>
        
        <!-- Search and Filter Bar -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by name, email or phone..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if(!empty($search)): ?>
                            <a href="view.php" class="btn btn-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="badge bg-secondary"><?php echo mysqli_num_rows($result); ?> member(s) found</span>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        // Check if there are any members
        if(mysqli_num_rows($result) > 0){
        ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                            
                            // Set balance color
                            $balance_class = '';
                            if($balance > 0) {
                                $balance_class = 'text-success';
                            } elseif($balance < 0) {
                                $balance_class = 'text-danger';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . $row['member_id'] . "</td>";
                            echo "<td><strong>" . $row['first_name'] . " " . $row['last_name'] . "</strong></td>";
                            echo "<td><a href='mailto:" . $row['email'] . "'>" . $row['email'] . "</a></td>";
                            echo "<td>" . $row['phone'] . "</td>";
                            echo "<td class='" . $balance_class . " fw-bold'>₱" . number_format($balance, 2) . "</td>";
                            echo "<td>";
                            echo "<div class='btn-group'>";
                            echo "<a href='edit.php?id=" . $row['member_id'] . "' class='btn btn-sm btn-info' title='Edit'><i class='bi bi-pencil'></i></a>";
                            echo "<a href='../../transactions/view.php?member_id=" . $row['member_id'] . "' class='btn btn-sm btn-secondary' title='Transactions'><i class='bi bi-currency-exchange'></i></a>";
                            echo "<a href='delete.php?id=" . $row['member_id'] . "' class='btn btn-sm btn-danger' title='Delete' onclick='return confirm(\"Are you sure you want to delete this member?\")'><i class='bi bi-trash'></i></a>";
                            echo "</div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        } else{
            echo "<div class='alert alert-info'>
                    <i class='bi bi-info-circle me-2'></i>No members found. <a href='add.php' class='alert-link'>Add a new member</a> to get started.
                  </div>";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<?php include "../../includes/footer.php"; ?>