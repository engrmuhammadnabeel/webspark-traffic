<?php
// Load bootstrap first
require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process registration form
    $username = sanitizeInput($_POST['username'], 'string');
    $email = sanitizeInput($_POST['email'], 'email');
    $password = $_POST['password'];
    $first_name = sanitizeInput($_POST['first_name'], 'string');
    $last_name = sanitizeInput($_POST['last_name'], 'string');
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    
    // Debug logging
    error_log("Registration Attempt: User: $username, Email: $email, reCAPTCHA: " . ($recaptcha_response ? 'provided' : 'missing'));

    // Verify reCAPTCHA first
    if (!verifyRecaptcha($recaptcha_response)) {
        $error = "❌ Please complete the security verification (reCAPTCHA).";
        error_log("Registration Blocked: reCAPTCHA verification failed for user: $username");
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, credits, created_at) 
                                  VALUES (?, ?, ?, ?, ?, 50, NOW())");
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), $first_name, $last_name]);
            
            error_log("Registration Successful: User: $username, Email: $email");
            header("Location: login.php?registered=1");
            exit();
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
            error_log("Registration Error: " . $e->getMessage());
        }
    }
}

$page_title = "Register - Webspark Traffic Exchange";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Create Account</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" id="registrationForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <!-- reCAPTCHA Widget -->
                    <div class="mb-3">
                        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>" data-callback="onRecaptchaSuccess"></div>
                        <div class="form-text text-warning">⚠️ Security verification required</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" id="registerBtn">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="/webspark-traffic/login.php">Login here</a></p>
                </div>

                <!-- Testing Notice -->
                <div class="mt-3 p-3 bg-light rounded border">
                    <small class="text-muted">
                        <strong>🔧 Testing Mode:</strong> reCAPTCHA verification is <strong>ENABLED</strong>. 
                        You must complete the reCAPTCHA to register.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- reCAPTCHA Script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<!-- Form Validation -->
<script>
// Enable register button when reCAPTCHA is completed
function onRecaptchaSuccess() {
    document.getElementById('registerBtn').disabled = false;
}

// Initially disable register button
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('registerBtn').disabled = true;
});

// Password confirmation validation
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const recaptchaResponse = grecaptcha.getResponse();
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('❌ Passwords do not match!');
        return false;
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('❌ Password must be at least 6 characters long!');
        return false;
    }
    
    if (!recaptchaResponse) {
        e.preventDefault();
        alert('❌ Please complete the reCAPTCHA verification.');
        return false;
    }
});
</script>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>