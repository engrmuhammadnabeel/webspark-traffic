<?php
require_once __DIR__ . '/../bootstrap.php';

// Only allow this from localhost for security
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die("Access denied");
}

$pdo = getDBConnection();

// Create admin user with proper password hash
$password = 'Admin123!';
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert or update admin user
$stmt = $pdo->prepare("
    INSERT INTO admin_users (username, password_hash, email, role) 
    VALUES ('admin', ?, 'admin@webspark.com', 'superadmin')
    ON DUPLICATE KEY UPDATE password_hash = ?
");

if ($stmt->execute([$password_hash, $password_hash])) {
    echo "Admin user created/updated successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: Admin123!<br>";
    echo "Hash: " . $password_hash . "<br>";
} else {
    echo "Failed to create admin user";
}