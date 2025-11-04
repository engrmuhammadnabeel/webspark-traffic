<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['payment_success'])) {
    header("Location: upgrade.php");
    exit();
}

$payment_data = $_SESSION['payment_success'];
unset($_SESSION['payment_success']);

$plan_names = [
    'starter' => 'Starter Plan',
    'professional' => 'Professional Plan',
    'enterprise' => 'Enterprise Plan'
];

$page_title = "Payment Successful - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-header bg-success text-white text-center">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h3 class="card-title mb-0">Payment Successful!</h3>
                </div>
                <div class="card-body text-center">
                    <h4 class="text-success">Thank You for Your Purchase!</h4>
                    <p class="lead">Your plan has been upgraded successfully.</p>
                    
                    <div class="alert alert-success">
                        <h5><?php echo $plan_names[$payment_data['plan']]; ?></h5>
                        <p class="mb-1"><strong>Amount Paid:</strong> $<?php echo number_format($payment_data['amount'], 2); ?></p>
                        <p class="mb-1"><strong>Credits Added:</strong> <?php echo number_format($payment_data['credits']); ?></p>
                        <p class="mb-0"><strong>Transaction ID:</strong> <?php echo $payment_data['transaction_id']; ?></p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <a href="dashboard.php" class="btn btn-primary w-100">
                                <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="traffic_exchange.php" class="btn btn-success w-100">
                                <i class="fas fa-exchange-alt me-2"></i>Start Earning
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6>What's Next?</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>✅ Your account has been upgraded</li>
                            <li>✅ Credits have been added to your balance</li>
                            <li>✅ You now have access to premium features</li>
                            <li>📧 A receipt has been sent to your email</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Your New Benefits</h5>
                    <div class="row text-center">
                        <div class="col-md-4">
                            <i class="fas fa-rocket fa-2x text-primary mb-2"></i>
                            <h6>Priority Traffic</h6>
                            <small class="text-muted">Get your websites seen first</small>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                            <h6>Advanced Analytics</h6>
                            <small class="text-muted">Detailed performance insights</small>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-globe-americas fa-2x text-info mb-2"></i>
                            <h6>Geo-Targeting</h6>
                            <small class="text-muted">Reach specific locations</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>