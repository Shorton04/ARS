<?php
// Include header
include_once "../includes/header.php";

// Include database connection
require_once "../config/database.php";

// Check if filtering by member
$member_filter = "";
$member_id = "";
if(isset($_GET['member_id'])) {
    $member_id = $_GET['member_id'];
    $member_filter = " WHERE t.member_id = " . intval($member_id);
}

// Fetch all transactions with member names
$sql = "SELECT t.transaction_id, t.member_id, CONCAT(m.first_name, ' ', m.last_name) as member_name, 
        t.type, t.amount, t.description, t.transaction_date 
        FROM transactions t
        JOIN members m ON t.member_id = m.member_id" . $member_filter . 
        " ORDER BY t.transaction_date DESC";
$result = mysqli_query($conn, $sql);

// Fetch all members for the dropdown
$members_sql = "SELECT member_id, first_name, last_name FROM members ORDER BY first_name, last_name";
$members_result = mysqli_query($conn, $members_sql);
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Transactions</h2>
            <div>
                <a href="deposit.php" class="btn btn-success me-2">Record Deposit</a>
                <a href="withdraw.php" class="btn btn-warning">Record Withdrawal</a>
            </div>
        </div>
        
        <!-- Filter by member -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                    <div class="col-md-6">
                        <label for="member_id" class="form-label">Filter by Member</label>
                        <select name="member_id" class="form-select">
                            <option value="">All Members</option>
                            <?php
                            while($member_row = mysqli_fetch_assoc($members_result)){
                                $selected = ($member_id == $member_row['member_id']) ? 'selected' : '';
                                echo "<option value='" . $member_row['member_id'] . "' $selected>" . 
                                    $member_row['first_name'] . " " . $member_row['last_name'] . 
                                    "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                        <a href="view.php" class="btn btn-secondary">Clear Filter</a>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        // Check if there are any transactions
        if(mysqli_num_rows($result) > 0){
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while($row = mysqli_fetch_assoc($result)){
                        echo "<tr>";
                        echo "<td>" . $row['transaction_id'] . "</td>";
                        echo "<td>" . $row['member_name'] . "</td>";
                        echo "<td>";
                        if($row['type'] == 'deposit') {
                            echo "<span class='badge bg-success'>Deposit</span>";
                        } else {
                            echo "<span class='badge bg-warning text-dark'>Withdrawal</span>";
                        }
                        echo "</td>";
                        echo "<td>₱" . number_format($row['amount'], 2) . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>" . $row['transaction_date'] . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
        } else{
            echo "<div class='alert alert-info'>No transactions found.</div>";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>