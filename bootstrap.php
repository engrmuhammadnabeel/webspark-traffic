<?php
// bootstrap.php - Setup application environment

// Define absolute path to the project root
define('ROOT_PATH', dirname(__FILE__));

// Set include path to make includes easier
set_include_path(get_include_path() . PATH_SEPARATOR . ROOT_PATH);

// Load configuration (this should define getDBConnection() and DB settings)
require_once ROOT_PATH . '/config.php';

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Refresh session data from database if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT username, first_name, credits, account_type 
                               FROM users 
                               WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_data) {
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['first_name'] = $user_data['first_name'];
            $_SESSION['credits'] = $user_data['credits'];
            $_SESSION['account_type'] = $user_data['account_type'];
        }
    } catch (Exception $e) {
        // Log error in production instead of displaying
        error_log("Session refresh failed: " . $e->getMessage());
    }
}

// ==================================================
// Automatic traffic distribution function
// ==================================================
function distributeTraffic() {
    $pdo = getDBConnection();
    
    try {
        // Get users who have credits and active websites (ONE ROW PER USER - BUG FIX)
        $stmt = $pdo->prepare("
            SELECT u.user_id, u.username, u.credits, 
                   w.website_id, w.website_url, w.website_title 
            FROM users u 
            JOIN websites w ON u.user_id = w.user_id 
            WHERE u.credits > 0 AND w.is_active = 1 
            GROUP BY u.user_id  -- ⭐⭐⭐ CRITICAL FIX: Prevents multiple deductions per user
            ORDER BY RAND() 
            LIMIT 10
        ");
        $stmt->execute();
        $eligible_users = $stmt->fetchAll();
        
        foreach ($eligible_users as $user) {
            // Get a random visitor (excluding the website owner)
            $visitor_stmt = $pdo->prepare("
                SELECT user_id, username 
                FROM users 
                WHERE user_id != ? AND credits >= 0 
                ORDER BY RAND() 
                LIMIT 1
            ");
            $visitor_stmt->execute([$user['user_id']]);
            $visitor = $visitor_stmt->fetch();
            
            if ($visitor) {
                // Record the visit
                $visit_stmt = $pdo->prepare("
                    INSERT INTO visits (visitor_id, website_id, visit_date, dwell_time) 
                    VALUES (?, ?, NOW(), FLOOR(30 + RAND() * 120))
                ");
                $visit_stmt->execute([$visitor['user_id'], $user['website_id']]);
                
                // Deduct 1 credit from website owner
                $credit_stmt = $pdo->prepare("UPDATE users SET credits = credits - 1 WHERE user_id = ?");
                $credit_stmt->execute([$user['user_id']]);
                
                // Update analytics
                $analytics_stmt = $pdo->prepare("
                    INSERT INTO analytics (website_id, visit_date, total_visits, unique_visits, total_dwell_time) 
                    VALUES (?, CURDATE(), 1, 1, 60) 
                    ON DUPLICATE KEY UPDATE 
                    total_visits = total_visits + 1,
                    unique_visits = unique_visits + 1,
                    total_dwell_time = total_dwell_time + 60
                ");
                $analytics_stmt->execute([$user['website_id']]);
                
                // Log the traffic distribution for debugging
                error_log("Traffic distributed: User {$user['user_id']} paid 1 credit for a visit to website {$user['website_id']} from visitor {$visitor['user_id']}");
            }
        }
    } catch (PDOException $e) {
        error_log("Traffic distribution error: " . $e->getMessage());
    }
}

// Distribute traffic with 20% probability on each page load (for demo purposes)
if (rand(1, 5) === 1) { // 20% chance
    distributeTraffic();
}