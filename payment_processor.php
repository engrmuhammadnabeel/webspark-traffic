<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

$pdo = getDBConnection();

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $plan_type = sanitizeInput($_POST['plan_type'], 'string');
    $payment_method = sanitizeInput($_POST['payment_method'], 'string');
    $card_number = sanitizeInput($_POST['card_number'] ?? '', 'string');
    $card_expiry = sanitizeInput($_POST['card_expiry'] ?? '', 'string');
    $card_cvc = sanitizeInput($_POST['card_cvc'] ?? '', 'string');
    
    // Plan pricing
    $plan_prices = [
        'starter' => 9.99,
        'professional' => 19.99,
        'enterprise' => 49.99
    ];
    
    $plan_credits = [
        'starter' => 500,
        'professional' => 1500,
        'enterprise' => 5000
    ];
    
    $amount = $plan_prices[$plan_type] ?? 0;
    $credits = $plan_credits[$plan_type] ?? 0;
    
    try {
        // Simulate payment processing
        $payment_success = true; // In real system, this would be from payment gateway
        
        if ($payment_success) {
            // Update user account
            $update_stmt = $pdo->prepare("
                UPDATE users 
                SET account_type = ?, credits = credits + ?, subscription_start = CURDATE() 
                WHERE user_id = ?
            ");
            $update_stmt->execute([$plan_type, $credits, $_SESSION['user_id']]);
            
            // Record payment
            $payment_stmt = $pdo->prepare("
                INSERT INTO credit_purchases (user_id, credit_amount, purchase_price, payment_method, payment_status, transaction_id) 
                VALUES (?, ?, ?, ?, 'completed', ?)
            ");
            $transaction_id = 'TXN_' . uniqid();
            $payment_stmt->execute([$_SESSION['user_id'], $credits, $amount, $payment_method, $transaction_id]);
            
            // Update session
            $_SESSION['account_type'] = $plan_type;
            $_SESSION['credits'] += $credits;
            
            $_SESSION['payment_success'] = [
                'plan' => $plan_type,
                'amount' => $amount,
                'credits' => $credits,
                'transaction_id' => $transaction_id
            ];
            
            header("Location: payment_success.php");
            exit();
        } else {
            $_SESSION['payment_error'] = "Payment processing failed. Please try again.";
            header("Location: payment_processor.php?plan=" . $plan_type);
            exit();
        }
        
    } catch (Exception $e) {
        $_SESSION['payment_error'] = "Error processing payment: " . $e->getMessage();
        header("Location: payment_processor.php?plan=" . $plan_type);
        exit();
    }
}

// Get plan from URL parameter
$plan_type = $_GET['plan'] ?? 'starter';
$plan_names = [
    'starter' => 'Starter Plan',
    'professional' => 'Professional Plan', 
    'enterprise' => 'Enterprise Plan'
];
$plan_prices = [
    'starter' => 9.99,
    'professional' => 19.99,
    'enterprise' => 49.99
];

$page_title = "Payment - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2>Complete Your Purchase</h2>
            <p class="lead">Secure payment processing</p>
            
            <?php if (isset($_SESSION['payment_error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['payment_error']; ?>
                    <?php unset($_SESSION['payment_error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Order Summary -->
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <h4><?php echo $plan_names[$plan_type]; ?></h4>
                            <p class="text-muted">Monthly Subscription</p>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Plan Price:</span>
                                <strong>$<?php echo number_format($plan_prices[$plan_type], 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Credits Included:</span>
                                <strong>
                                    <?php echo $plan_type === 'starter' ? '500' : ($plan_type === 'professional' ? '1500' : '5000'); ?>
                                </strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><strong>Total:</strong></span>
                                <strong>$<?php echo number_format($plan_prices[$plan_type], 2); ?></strong>
                            </div>
                            
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    This is a demo payment system. No real charges will be made.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Form -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Payment Details</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" id="paymentForm">
                                <input type="hidden" name="plan_type" value="<?php echo $plan_type; ?>">
                                
                                <!-- Payment Method -->
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" checked>
                                            <label class="form-check-label" for="paypal">
                                                <i class="fab fa-paypal me-1"></i>PayPal
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="payment_method" id="stripe" value="stripe">
                                            <label class="form-check-label" for="stripe">
                                                <i class="fab fa-cc-stripe me-1"></i>Stripe
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="payment_method" id="card" value="credit_card">
                                            <label class="form-check-label" for="card">
                                                <i class="fas fa-credit-card me-1"></i>Credit Card
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Credit Card Fields (initially hidden) -->
                                <div id="creditCardFields" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="card_number" class="form-label">Card Number</label>
                                            <input type="text" class="form-control" id="card_number" name="card_number" 
                                                   placeholder="1234 5678 9012 3456" maxlength="19">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="card_expiry" class="form-label">Expiry Date</label>
                                            <input type="text" class="form-control" id="card_expiry" name="card_expiry" 
                                                   placeholder="MM/YY" maxlength="5">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="card_cvc" class="form-label">CVC</label>
                                            <input type="text" class="form-control" id="card_cvc" name="card_cvc" 
                                                   placeholder="123" maxlength="3">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- PayPal/Stripe Info -->
                                <div id="gatewayInfo" class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You will be redirected to a secure payment gateway to complete your purchase.
                                </div>
                                
                                <div class="alert alert-warning">
                                    <small>
                                        <strong>Demo Notice:</strong> This is a simulation. No real payment will be processed. 
                                        Click "Complete Purchase" to simulate a successful payment.
                                    </small>
                                </div>
                                
                                <button type="submit" name="process_payment" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-lock me-2"></i>Complete Purchase - $<?php echo number_format($plan_prices[$plan_type], 2); ?>
                                </button>
                                
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Your payment is secure and encrypted
                                    </small>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Security Badges -->
            <div class="row mt-4">
                <div class="col-md-12 text-center">
                    <div class="card">
                        <div class="card-body">
                            <h6>Trusted & Secure</h6>
                            <div class="d-flex justify-content-center gap-4">
                                <div class="text-center">
                                    <i class="fas fa-shield-alt fa-2x text-success"></i>
                                    <p class="small mb-0">SSL Secure</p>
                                </div>
                                <div class="text-center">
                                    <i class="fas fa-lock fa-2x text-primary"></i>
                                    <p class="small mb-0">256-bit Encryption</p>
                                </div>
                                <div class="text-center">
                                    <i class="fas fa-user-shield fa-2x text-info"></i>
                                    <p class="small mb-0">PCI Compliant</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Show/hide credit card fields based on payment method
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const creditCardFields = document.getElementById('creditCardFields');
        const gatewayInfo = document.getElementById('gatewayInfo');
        
        if (this.value === 'credit_card') {
            creditCardFields.style.display = 'block';
            gatewayInfo.style.display = 'none';
        } else {
            creditCardFields.style.display = 'none';
            gatewayInfo.style.display = 'block';
        }
    });
});

// Format card number
document.getElementById('card_number')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let matches = value.match(/\d{4,16}/g);
    let match = matches && matches[0] || '';
    let parts = [];
    
    for (let i = 0; i < match.length; i += 4) {
        parts.push(match.substring(i, i + 4));
    }
    
    if (parts.length) {
        e.target.value = parts.join(' ');
    } else {
        e.target.value = value;
    }
});

// Format expiry date
document.getElementById('card_expiry')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    if (value.length >= 2) {
        e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
});
</script>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>