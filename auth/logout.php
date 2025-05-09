<?php
// Define base URL if not already defined
if (!isset($base_url)) {
    $base_url = "/accounts_receivable_system";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Confirmation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo $base_url; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Logout Confirmation</h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                            <i class="bi bi-box-arrow-right text-warning" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="mb-3">Are you sure you want to logout?</h4>
                        <p class="text-muted mb-4">You will be logged out of your account and returned to the login screen.</p>
                        
                        <div class="d-grid gap-2 d-flex justify-content-center">
                            <a href="<?php echo $base_url; ?>/auth/logout_process.php" class="btn btn-warning">
                                <i class="bi bi-check-circle me-2"></i>Yes, Logout
                            </a>
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>