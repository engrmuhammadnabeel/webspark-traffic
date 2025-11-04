<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<h2 style='color: #2e7d32;'>Database Connection Successful! ✅</h2>";
    echo "<p>Connected to database: <strong>" . DB_NAME . "</strong></p>";
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
    $result = $stmt->fetch();
    echo "<p>Number of tables in database: <strong>" . $result['table_count'] . "</strong></p>";
    
    // List all tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p>Tables found:</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table) . "</li>";
    }
    echo "</ul>";
    
    echo "<p style='color: #2e7d32; font-weight: bold;'>All systems operational. Connection is secure.</p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; border: 1px solid #f44336; border-radius: 5px; background-color: #ffebee;'>";
    echo "<h2 style='color: #f44336;'>Connection Failed ❌</h2>";
    echo "<p style='color: #f44336;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Check your database credentials and ensure MySQL is running.</p>";
    echo "<h3>Troubleshooting steps:</h3>";
    echo "<ol>";
    echo "<li>Make sure XAMPP is running with Apache and MySQL started</li>";
    echo "<li>Verify the database name in config.php matches your database</li>";
    echo "<li>Check your database username and password in config.php</li>";
    echo "<li>Ensure the database user has proper privileges</li>";
    echo "</ol>";
    echo "</div>";
}
?>