<?php
// Start session
session_start();

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
$is_member = isset($_SESSION['role']) && $_SESSION['role'] == 'member';

// Redirect to login page if not logged in
if (!$is_logged_in && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cooperative Accounts Receivable</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-bank me-2"></i>Cooperative AR System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if ($is_logged_in): ?>
                        <?php if ($is_admin): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/index.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Admin Dashboard
                                </a>
                            </li>
                        <?php elseif ($is_member): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../member/index.php">
                                    <i class="bi bi-person-circle me-1"></i>Member Dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($_SESSION["username"]); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <?php if ($is_member): ?>
                                    <li><a class="dropdown-item" href="../member/profile.php">My Profile</a></li>
                                    <li><a class="dropdown-item" href="../member/transactions.php">My Transactions</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4 mb-4">