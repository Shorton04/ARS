<?php
// Include database connection
require_once "config/database.php";

// Start session
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multipurpose Cooperative - Accounts Receivable System</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Cooperative Accounts Receivable System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($is_logged_in): ?>
                        <?php if ($is_admin): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/index.php">Admin Dashboard</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="member/index.php">Member Dashboard</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="mb-4">Welcome to Multipurpose Cooperative</h1>
                <h3 class="text-muted mb-5">Accounts Receivable System</h3>
            </div>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">About the System</h4>
                        <p class="lead">This system helps manage accounts receivable processes for our multipurpose cooperative members.</p>
                        <p>Key features include:</p>
                        <ul>
                            <li>Member account management</li>
                            <li>Transaction recording (deposits and withdrawals)</li>
                            <li>Real-time balance tracking</li>
                            <li>Financial reporting</li>
                        </ul>
                        
                        <?php if (!$is_logged_in): ?>
                            <div class="text-center mt-4">
                                <p>Please login to access your account.</p>
                                <a href="auth/login.php" class="btn btn-primary btn-lg px-4">Login Now</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center mt-4">
                                <?php if ($is_admin): ?>
                                    <a href="admin/index.php" class="btn btn-primary btn-lg px-4">Go to Admin Dashboard</a>
                                <?php else: ?>
                                    <a href="member/index.php" class="btn btn-primary btn-lg px-4">Go to Member Dashboard</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="mt-5 py-3 bg-light">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Multipurpose Cooperative. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>