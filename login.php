<?php
require_once __DIR__ . '/bootstrap.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /webspark-traffic/dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'], 'string');
    $password = $_POST['password'];
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // Debug logging
    error_log("Login Attempt: User: $username, reCAPTCHA: " . ($recaptcha_response ? 'provided' : 'missing'));

    // Verify reCAPTCHA first
    if (!verifyRecaptcha($recaptcha_response)) {
        $error = "❌ Please complete the security verification (reCAPTCHA).";
        error_log("Login Blocked: reCAPTCHA verification failed for user: $username");
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT user_id, username, password_hash, first_name, credits FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['credits'] = $user['credits'];
                
                // Update last login
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
                $updateStmt->execute([$user['user_id']]);
                
                error_log("Login Successful: User: $username, ID: " . $user['user_id']);
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password";
                error_log("Login Failed: Invalid credentials for user: $username");
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again.";
            error_log("Login Error: " . $e->getMessage());
        }
    }
}

$page_title = "Login - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Login to Your Account</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">✅ Registration successful! Please login.</div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username or Email</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <!-- reCAPTCHA for login security -->
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>" data-callback="onRecaptchaSuccess"></div>
                        <div class="form-text text-warning">⚠️ Security verification required</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="/webspark-traffic/register.php">Register here</a></p>
                </div>

                <!-- Testing Notice -->
                <div class="mt-3 p-3 bg-light rounded border">
                    <small class="text-muted">
                        <strong>🔧 Testing Mode:</strong> reCAPTCHA verification is <strong>ENABLED</strong>. 
                        You must complete the reCAPTCHA to login.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- reCAPTCHA script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
// Enable login button when reCAPTCHA is completed
function onRecaptchaSuccess() {
    document.getElementById('loginBtn').disabled = false;
}

// Initially disable login button
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginBtn').disabled = true;
});

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const recaptchaResponse = grecaptcha.getResponse();
    if (!recaptchaResponse) {
        e.preventDefault();
        alert('Please complete the reCAPTCHA verification.');
        return false;
    }
});
</script>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>