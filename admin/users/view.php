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

// Define base URL if not already defined
if (!isset($base_url)) {
    $base_url = "/accounts_receivable_system"; // Use underscore to match your URL format
}

// Handle search
$search = '';
$search_condition = '';

if(isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_condition = " WHERE username LIKE '%".mysqli_real_escape_string($conn, $search)."%' 
                          OR role LIKE '%".mysqli_real_escape_string($conn, $search)."%'";
}

// Fetch all users
$sql = "SELECT user_id, username, role, created_at FROM users" . $search_condition . " ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Check for success/error messages
$success_msg = '';
$error_msg = '';

if(isset($_GET['success'])) {
    if($_GET['success'] == 'deleted') {
        $success_msg = "User was deleted successfully.";
    } elseif($_GET['success'] == 'updated') {
        $success_msg = "User information was updated successfully.";
    }
}

if(isset($_GET['error'])) {
    if($_GET['error'] == 'self_delete') {
        $error_msg = "You cannot delete your own account.";
    } elseif($_GET['error'] == 'delete_failed') {
        $error_msg = "An error occurred while trying to delete the user.";
    }
}
?>

<div class="row">
    <div class="col-md-3">
        <?php include "../../includes/sidebar.php"; ?>
    </div>
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-people me-2"></i>Manage Users</h2>
            <a href="<?php echo $base_url; ?>/admin/users/add.php" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Add New User
            </a>
        </div>
        
        <?php if(!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if(!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="row g-3">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by username or role..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <?php if(!empty($search)): ?>
                            <a href="view.php" class="btn btn-secondary">Clear</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="badge bg-secondary"><?php echo mysqli_num_rows($result); ?> user(s) found</span>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        // Check if there are any users
        if(mysqli_num_rows($result) > 0){
        ?>
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>User List</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
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
                            // Check if it's the current logged-in user (can't delete yourself)
                            $is_current_user = ($row['user_id'] == $_SESSION['user_id']);
                            
                            echo "<tr>";
                            echo "<td>" . $row['user_id'] . "</td>";
                            echo "<td><strong>" . $row['username'] . "</strong></td>";
                            echo "<td><span class='badge " . ($row['role'] == 'admin' ? 'bg-danger' : 'bg-info') . "'>" . ucfirst($row['role']) . "</span></td>";
                            echo "<td>" . date('M d, Y g:i A', strtotime($row['created_at'])) . "</td>";
                            echo "<td>";
                            echo "<div class='btn-group'>";
                            
                            // Edit button
                            echo "<a href='edit.php?id=" . $row['user_id'] . "' class='btn btn-sm btn-info' title='Edit'><i class='bi bi-pencil'></i></a>";
                            
                            // Delete button (with confirmation and disabled for current user)
                            if($is_current_user) {
                                echo "<button class='btn btn-sm btn-secondary' disabled title='Cannot delete your own account'><i class='bi bi-trash'></i></button>";
                            } else {
                                echo "<a href='javascript:void(0);' onclick='return confirmDelete(\"" . $base_url . "/admin/users/delete.php?id=" . $row['user_id'] . "\", \"user\")' class='btn btn-sm btn-danger' title='Delete'><i class='bi bi-trash'></i></a>";
                            }
                            
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
                    <i class='bi bi-info-circle me-2'></i>No users found. <a href='" . $base_url . "/admin/users/add.php' class='alert-link'>Add a new user</a> to get started.
                  </div>";
        }
        
        // Close connection
        mysqli_close($conn);
        ?>
    </div>
</div>

<!-- JavaScript for Delete Confirmation -->
<script>
function confirmDelete(deleteUrl, itemType) {
    // Create modal content
    const modalContent = `
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteConfirmModalLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-4">
                            <i class="bi bi-trash text-danger" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="mb-3">Are you sure?</h4>
                        <p class="text-muted mb-0">
                            You are about to delete this ${itemType}. This action cannot be undone.
                        </p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </button>
                        <a href="${deleteUrl}" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Yes, Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove any existing modal
    const existingModal = document.getElementById('deleteConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to DOM
    document.body.insertAdjacentHTML('beforeend', modalContent);
    
    // Initialize and show modal
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
    
    // Return false to prevent the default link action
    return false;
}
</script>

<?php include "../../includes/footer.php"; ?>