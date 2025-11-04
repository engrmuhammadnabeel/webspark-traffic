<?php
require_once __DIR__ . '/../bootstrap.php';

// Redirect if already logged in as admin
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'], 'string');
    $password = $_POST['password'];
    
    try {
        $pdo = getDBConnection();
        
        // DEBUG: Check if we can connect to the table
        $table_check = $pdo->query("SHOW TABLES LIKE 'admin_users'")->fetch();
        if (!$table_check) {
            $error = "Admin users table not found!";
        } else {
            // DEBUG: Check what users exist
            $all_admins = $pdo->query("SELECT username, is_active FROM admin_users")->fetchAll();
            
            $stmt = $pdo->prepare("SELECT admin_id, username, password_hash, role FROM admin_users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                // DEBUG: Show what we found
                error_log("Found admin: " . print_r($admin, true));
                error_log("Password verify result: " . (password_verify($password, $admin['password_hash']) ? 'true' : 'false'));
                error_log("Input password: " . $password);
                error_log("Stored hash: " . $admin['password_hash']);
                
                if (password_verify($password, $admin['password_hash'])) {
                    // Admin login successful
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_role'] = $admin['role'];
                    
                    // Log admin login
                    $log_stmt = $pdo->prepare("INSERT INTO system_logs (log_type, message, user_id, ip_address) VALUES ('security', 'Admin login', ?, ?)");
                    $log_stmt->execute([$admin['admin_id'], $_SERVER['REMOTE_ADDR']]);
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Password verification failed";
                }
            } else {
                $error = "Admin user not found or inactive. Found these users: " . print_r($all_admins, true);
            }
        }
    } catch (PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Webspark Traffic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
        }
        .admin-login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .debug-info {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="admin-login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="admin-login-card p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                            <h2>Admin Portal</h2>
                            <p class="text-muted">Webspark Traffic Exchange</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <strong>Error:</strong> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Admin Username</label>
                                <input type="text" class="form-control" id="username" name="username" required value="admin">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required value="Admin123!">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Admin Login (Debug)
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="/webspark-traffic/index.php" class="text-muted">
                                <i class="fas fa-arrow-left me-1"></i>Back to Main Site
                            </a>
                        </div>
                        
                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <strong>Demo Credentials:</strong><br>
                                Username: <code>admin</code><br>
                                Password: <code>Admin123!</code>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>