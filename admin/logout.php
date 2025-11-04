<?php
require_once __DIR__ . '/../bootstrap.php';

// Log admin logout
if (isset($_SESSION['admin_id'])) {
    $pdo = getDBConnection();
    $log_stmt = $pdo->prepare("INSERT INTO system_logs (log_type, message, user_id, ip_address) VALUES ('security', 'Admin logout', ?, ?)");
    $log_stmt->execute([$_SESSION['admin_id'], $_SERVER['REMOTE_ADDR']]);
}

// Clear admin session
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_role']);

// Redirect to admin login
header("Location: login.php");
exit();
?>