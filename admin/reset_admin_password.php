<?php
require_once __DIR__ . '/../bootstrap.php';

// Only allow from localhost for security
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost'])) {
    die("Access denied. This script can only run from localhost.");
}

$pdo = getDBConnection();

// The password we want to set
$password = 'Admin123!';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Admin Password Reset</h3>";
echo "Password: " . $password . "<br>";
echo "Generated Hash: " . $password_hash . "<br>";

// Update the admin password
$stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");

if ($stmt->execute([$password_hash])) {
    echo "<div style='color: green; font-weight: bold;'>✅ Admin password updated successfully!</div>";
    
    // Verify the new password works
    $verify_stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE username = 'admin'");
    $verify_stmt->execute();
    $new_hash = $verify_stmt->fetch()['password_hash'];
    
    echo "Stored Hash: " . $new_hash . "<br>";
    echo "Verification: " . (password_verify($password, $new_hash) ? "✅ SUCCESS" : "❌ FAILED");
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ Failed to update admin password</div>";
}

echo "<hr>";
echo "<a href='login.php'>Go to Admin Login</a>";

// Delete this file for security
// unlink(__FILE__);
?>