<?php
// Start session and check authentication
require_once ROOT_PATH . '/config.php';

$page_title = isset($page_title) ? $page_title : SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/webspark-traffic/assets/css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <!-- Logo link fixed -->
        <a class="navbar-brand" href="/webspark-traffic/index.php">
            <i class="fas fa-exchange-alt me-2"></i>Webspark Traffic
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/my_websites.php">
                            <i class="fas fa-globe me-1"></i>My Websites
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/earn_credits.php">
                            <i class="fas fa-coins me-1"></i>Earn Credits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/analytics.php">
                            <i class="fas fa-chart-bar me-1"></i>Analytics
                        </a>
                    </li>
                    <!-- ✅ Added Feedback link -->
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/feedback.php">
                            <i class="fas fa-comments me-1"></i>Feedback
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/register.php">Register</a>
                    </li>
                    <!-- ✅ Also show Feedback for guests -->
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/feedback.php">
                            <i class="fas fa-comments me-1"></i>Feedback
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['username']); ?>
                            <span class="badge bg-light text-dark ms-1"><?php echo $_SESSION['credits']; ?> credits</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/webspark-traffic/dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a></li>
                            <li><a class="dropdown-item" href="/webspark-traffic/my_websites.php">
                                <i class="fas fa-globe me-2"></i>My Websites
                            </a></li>
                            <li><a class="dropdown-item" href="/webspark-traffic/analytics.php">
                                <i class="fas fa-chart-bar me-2"></i>Analytics
                            </a></li>
                            <li><a class="dropdown-item" href="/webspark-traffic/feedback.php">
                                <i class="fas fa-comments me-2"></i>Feedback
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/webspark-traffic/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/webspark-traffic/login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm ms-2" href="/webspark-traffic/register.php">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
