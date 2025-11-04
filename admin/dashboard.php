<?php
require_once __DIR__ . '/../bootstrap.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();

// Get dashboard statistics
$users_count = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$websites_count = $pdo->query("SELECT COUNT(*) as count FROM websites WHERE is_active = 1")->fetch()['count'];
$feedback_count = $pdo->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'open'")->fetch()['count'];
$total_credits = $pdo->query("SELECT SUM(credits) as total FROM users")->fetch()['total'];

// Recent feedback
$recent_feedback = $pdo->query("
    SELECT f.*, u.username 
    FROM feedback f 
    JOIN users u ON f.user_id = u.user_id 
    ORDER BY f.created_at DESC 
    LIMIT 5
")->fetchAll();

// Recent user registrations
$recent_users = $pdo->query("
    SELECT user_id, username, email, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Webspark Traffic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt me-2"></i>Webspark Admin
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i><?php echo $_SESSION['admin_username']; ?>
                    <span class="badge bg-secondary ms-1"><?php echo $_SESSION['admin_role']; ?></span>
                </span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Admin Sub Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container">
            <div class="navbar-nav">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users me-1"></i>Users
                </a>
                <a class="nav-link" href="feedback.php">
                    <i class="fas fa-comments me-1"></i>Feedback
                    <?php if ($feedback_count > 0): ?>
                        <span class="badge bg-danger"><?php echo $feedback_count; ?></span>
                    <?php endif; ?>
                </a>
                <a class="nav-link" href="websites.php">
                    <i class="fas fa-globe me-1"></i>Websites
                </a>
                <a class="nav-link" href="system_logs.php">
                    <i class="fas fa-clipboard-list me-1"></i>System Logs
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Dashboard Stats -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $users_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Websites</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $websites_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-globe fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Feedback</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $feedback_count; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-comments fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Credits</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_credits ?? 0; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-coins fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Feedback -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Feedback</h5>
                        <a href="feedback.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_feedback): ?>
                            <?php foreach ($recent_feedback as $feedback): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($feedback['subject']); ?></strong>
                                        <span class="badge bg-<?php echo $feedback['type'] === 'bug' ? 'danger' : 'primary'; ?>">
                                            <?php echo ucfirst($feedback['type']); ?>
                                        </span>
                                    </div>
                                    <small class="text-muted">
                                        By: <?php echo htmlspecialchars($feedback['username']); ?> | 
                                        <?php echo date('M j, g:i A', strtotime($feedback['created_at'])); ?>
                                    </small>
                                    <p class="mb-1 small"><?php echo substr(htmlspecialchars($feedback['message']), 0, 100); ?>...</p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent feedback.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Registrations</h5>
                        <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_users): ?>
                            <?php foreach ($recent_users as $user): ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <small class="text-muted">ID: <?php echo $user['user_id']; ?></small>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($user['email']); ?> | 
                                        <?php echo date('M j, g:i A', strtotime($user['created_at'])); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No recent user registrations.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="users.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-cog me-2"></i>Manage Users
                            </a>
                            <a href="feedback.php" class="btn btn-outline-warning">
                                <i class="fas fa-comment-dots me-2"></i>Review Feedback
                            </a>
                            <a href="websites.php" class="btn btn-outline-success">
                                <i class="fas fa-globe me-2"></i>Manage Websites
                            </a>
                            <a href="system_logs.php" class="btn btn-outline-info">
                                <i class="fas fa-clipboard-list me-2"></i>View Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>