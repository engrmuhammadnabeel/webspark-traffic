<?php
/**
 * Webspark Traffic Exchange Network
 * Secure Database Configuration File
 * 
 * This file handles secure database connections with proper error handling
 * and security measures to prevent SQL injection and other attacks.
 */

// Enable strict error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set to 0 in production, 1 in development
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Define absolute path for includes
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__));
}

// Database configuration with security in mind
define('DB_HOST', 'localhost');
define('DB_NAME', 'webspark_traffic_db');
define('DB_USER', 'webspark_user'); // Use a dedicated user, not root
define('DB_PASS', 'SecurePass123!'); // Change to a strong password
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Webspark Traffic Exchange');
define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']); // Dynamic URL detection
define('ADMIN_EMAIL', 'admin@webspark.com');

// Security settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 15 * 60); // 15 minutes in seconds
define('SESSION_TIMEOUT', 60 * 30); // 30 minutes

// reCAPTCHA Settings - Using Google's TEST keys that always work
define('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
define('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');

// Create logs directory if it doesn't exist
if (!is_dir(ROOT_PATH . '/logs')) {
    mkdir(ROOT_PATH . '/logs', 0755, true);
}

/**
 * Establish secure database connection with PDO
 * @return PDO|null Database connection object or null on failure
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00', NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log error without exposing details to users
            error_log("Database connection failed: " . $e->getMessage());
            
            // Display generic error message to user
            if (ini_get('display_errors')) {
                // Show detailed error only in development
                die("Database connection error: " . $e->getMessage());
            } else {
                // Generic error in production
                die("Service temporarily unavailable. Please try again later.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Secure session initialization with HTTP-only cookies and strict settings
 */
function secureSessionStart() {
    // Set session cookie parameters for enhanced security
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams["lifetime"],
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true, // Requires HTTPS
        'httponly' => true, // Prevents JavaScript access
        'samesite' => 'Strict' // CSRF protection
    ]);
    
    // Set session name and start session
    session_name('WebsparkSecureSession');
    session_start();
    
    // Regenerate session ID periodically to prevent fixation attacks
    if (!isset($_SESSION['last_regeneration'])) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * CSRF token generation and validation functions
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    error_log("CSRF token validation failed");
    return false;
}

/**
 * Input sanitization function
 * @param mixed $data Input data to sanitize
 * @param string $type Type of data (string, email, int, float, url)
 * @return mixed Sanitized data
 */
function sanitizeInput($data, $type = 'string') {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    // Remove whitespace from beginning and end
    $data = trim($data);
    
    // Strip slashes if magic quotes are enabled (deprecated but safe)
    if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
        $data = stripslashes($data);
    }
    
    // Apply type-specific sanitization
    switch ($type) {
        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            break;
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            break;
        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            break;
        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            break;
        case 'string':
        default:
            $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
    }
    
    return $data;
}

/**
 * Output escaping function for preventing XSS
 * @param string $data Data to escape
 * @return string Escaped data
 */
function escapeOutput($data) {
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Verify reCAPTCHA response
 * @param string $response The reCAPTCHA response from the form
 * @return bool True if valid, false if invalid
 */
function verifyRecaptcha($response) {
    // Debug logging
    error_log("reCAPTCHA Debug: Function called. Response: " . ($response ? 'provided' : 'empty'));
    
    // 🔧 FORCE REAL VERIFICATION FOR TESTING
    // Comment out this block to re-enable localhost bypass after testing
    // if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    //     error_log("reCAPTCHA Debug: Localhost bypass activated");
    //     return true;
    // }
    
    // For empty responses, immediately fail
    if (empty($response)) {
        error_log("reCAPTCHA Debug: Empty response - verification failed");
        return false;
    }
    
    error_log("reCAPTCHA Debug: Proceeding with real verification");
    
    // For production, verify with Google
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    try {
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $responseData = json_decode($result);
        
        error_log("reCAPTCHA Debug: Google response: " . ($responseData->success ? 'SUCCESS' : 'FAILED'));
        
        return $responseData->success;
    } catch (Exception $e) {
        error_log("reCAPTCHA Debug: Error during verification: " . $e->getMessage());
        return false;
    }
}

// Initialize secure sessions if not already started
if (session_status() === PHP_SESSION_NONE) {
    secureSessionStart();
}

// Generate CSRF token for forms
$csrf_token = generateCSRFToken();

// Check if HTTPS is being used (important for security)
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    // Redirect to HTTPS if not already using it
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') === false) { // Don't redirect on localhost
        $https_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $https_url");
        exit();
    }
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Initialize database connection
try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    error_log("Failed to initialize database connection: " . $e->getMessage());
    // Don't expose error details to user
    die("System initialization failed. Please contact administrator.");
}
?>