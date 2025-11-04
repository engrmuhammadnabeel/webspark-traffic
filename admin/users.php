<?php
require_once __DIR__ . '/../bootstrap.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    switch ($_GET['action']) {
        case 'suspend':
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['message'] = "User suspended successfully";
            break;
            
        case 'activate':
            $stmt = $pdo->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['message'] = "User activated successfully";
            break;
            
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['message'] = "User deleted successfully";
            break;
    }
    
    header("Location: users.php");
    exit();
}

// Get all users
$users = $pdo->query("
    SELECT u.*, COUNT(w.website_id) as website_count 
    FROM users u 
    LEFT JOIN websites w ON u.user_id = w.user_id 
    GROUP BY u.user_id 
    ORDER BY u.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Webspark Admin</title>
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
                </span>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Admin Sub Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container">
            <div class="navbar-nav">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a class="nav-link active" href="users.php">
                    <i class="fas fa-users me-1"></i>Users
                </a>
                <a class="nav-link" href="feedback.php">
                    <i class="fas fa-comments me-1"></i>Feedback
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>User Management</h2>
            <span class="badge bg-primary"><?php echo count($users); ?> Users</span>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Account Type</th>
                                <th>Credits</th>
                                <th>Websites</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $user['account_type'] === 'premium' ? 'warning' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['account_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $user['credits']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo $user['website_count']; ?></span>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Suspended</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?php echo date('M j, Y', strtotime($user['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($user['is_active']): ?>
                                            <a href="users.php?action=suspend&id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-outline-warning" 
                                               title="Suspend User">
                                                <i class="fas fa-pause"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="users.php?action=activate&id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-outline-success" 
                                               title="Activate User">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="users.php?action=delete&id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Delete User"
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        
                                        <a href="user_details.php?id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-outline-info" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($users)): ?>
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No users found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>