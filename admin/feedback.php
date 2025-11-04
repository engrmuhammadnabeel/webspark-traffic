<?php
require_once __DIR__ . '/../bootstrap.php';

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$pdo = getDBConnection();

// Handle feedback actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response'])) {
    $feedback_id = (int)$_POST['feedback_id'];
    $admin_response = sanitizeInput($_POST['response'], 'string');
    $status = sanitizeInput($_POST['status'], 'string');
    
    $stmt = $pdo->prepare("UPDATE feedback SET admin_response = ?, status = ?, updated_at = NOW() WHERE feedback_id = ?");
    if ($stmt->execute([$admin_response, $status, $feedback_id])) {
        $_SESSION['message'] = "Feedback response saved successfully";
    }
    
    header("Location: feedback.php");
    exit();
}

// Get all feedback
$feedback = $pdo->query("
    SELECT f.*, u.username, u.email 
    FROM feedback f 
    JOIN users u ON f.user_id = u.user_id 
    ORDER BY f.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management - Webspark Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Admin Navigation (same as users.php) -->
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

    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container">
            <div class="navbar-nav">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users me-1"></i>Users
                </a>
                <a class="nav-link active" href="feedback.php">
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
            <h2>Feedback Management</h2>
            <?php 
            $open_count = $pdo->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'open'")->fetch()['count'];
            if ($open_count > 0): ?>
                <span class="badge bg-warning"><?php echo $open_count; ?> Open Tickets</span>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($feedback as $item): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($item['subject']); ?></strong>
                            <span class="badge bg-<?php 
                                echo $item['type'] === 'bug' ? 'danger' : 
                                     ($item['type'] === 'feature' ? 'info' : 'primary'); 
                            ?> ms-2">
                                <?php echo ucfirst($item['type']); ?>
                            </span>
                        </div>
                        <span class="badge bg-<?php 
                            echo $item['status'] === 'open' ? 'warning' : 
                                 ($item['status'] === 'resolved' ? 'success' : 'secondary'); 
                        ?>">
                            <?php echo ucfirst($item['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($item['message'])); ?></p>
                        
                        <div class="mb-3">
                            <small class="text-muted">
                                From: <strong><?php echo htmlspecialchars($item['username']); ?></strong> 
                                (<?php echo htmlspecialchars($item['email']); ?>)<br>
                                Submitted: <?php echo date('M j, Y g:i A', strtotime($item['created_at'])); ?>
                            </small>
                        </div>
                        
                        <?php if ($item['admin_response']): ?>
                            <div class="alert alert-info">
                                <strong>Admin Response:</strong><br>
                                <?php echo nl2br(htmlspecialchars($item['admin_response'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="feedback_id" value="<?php echo $item['feedback_id']; ?>">
                            
                            <div class="mb-2">
                                <label class="form-label"><strong>Admin Response:</strong></label>
                                <textarea name="response" class="form-control" rows="3" 
                                          placeholder="Enter your response here..."><?php echo htmlspecialchars($item['admin_response'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <select name="status" class="form-select">
                                        <option value="open" <?php echo $item['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                        <option value="in_progress" <?php echo $item['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $item['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-1"></i>Save Response
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($feedback)): ?>
            <div class="text-center py-5">
                <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                <p class="text-muted">No feedback submitted yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>