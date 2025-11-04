<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

$pdo = getDBConnection();

$page_title = "Upgrade Plan - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Upgrade Your Plan</h2>
            <p class="lead">Get more features, credits, and better traffic</p>
        </div>
    </div>

    <!-- Pricing Plans -->
    <div class="row">
        <!-- Free Plan -->
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-header bg-light">
                    <h4 class="my-0 fw-normal">Free</h4>
                </div>
                <div class="card-body">
                    <h1 class="card-title pricing-card-title">$0<small class="text-muted">/mo</small></h1>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li>3 Websites</li>
                        <li>50 Free Credits</li>
                        <li>Basic Analytics</li>
                        <li>Standard Traffic</li>
                        <li class="text-muted">❌ Premium Support</li>
                        <li class="text-muted">❌ Geo-Targeting</li>
                    </ul>
                    <div class="alert alert-secondary">
                        <strong>Current Plan</strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Starter Plan -->
        <div class="col-md-3">
            <div class="card text-center border-primary">
                <div class="card-header bg-primary text-white">
                    <h4 class="my-0 fw-normal">Starter</h4>
                </div>
                <div class="card-body">
                    <h1 class="card-title pricing-card-title">$9.99<small class="text-muted">/mo</small></h1>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li>10 Websites</li>
                        <li>500 Credits/Month</li>
                        <li>Advanced Analytics</li>
                        <li>Priority Traffic</li>
                        <li>✅ Basic Geo-Targeting</li>
                        <li class="text-muted">❌ Premium Support</li>
                    </ul>
                    <form method="GET" action="payment_processor.php">
                        <input type="hidden" name="plan" value="starter">
                        <button type="submit" class="btn btn-lg btn-outline-primary w-100">
                            Select Starter
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Professional Plan -->
        <div class="col-md-3">
            <div class="card text-center border-warning">
                <div class="card-header bg-warning">
                    <h4 class="my-0 fw-normal">Professional</h4>
                </div>
                <div class="card-body">
                    <h1 class="card-title pricing-card-title">$19.99<small class="text-muted">/mo</small></h1>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li>25 Websites</li>
                        <li>1500 Credits/Month</li>
                        <li>Premium Analytics</li>
                        <li>High Priority Traffic</li>
                        <li>✅ Advanced Geo-Targeting</li>
                        <li>✅ Premium Support</li>
                    </ul>
                    <form method="GET" action="payment_processor.php">
                        <input type="hidden" name="plan" value="professional">
                        <button type="submit" class="btn btn-lg btn-warning w-100">
                            Select Professional
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Enterprise Plan -->
        <div class="col-md-3">
            <div class="card text-center border-danger">
                <div class="card-header bg-danger text-white">
                    <h4 class="my-0 fw-normal">Enterprise</h4>
                </div>
                <div class="card-body">
                    <h1 class="card-title pricing-card-title">$49.99<small class="text-muted">/mo</small></h1>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li>Unlimited Websites</li>
                        <li>5000 Credits/Month</li>
                        <li>Enterprise Analytics</li>
                        <li>Highest Priority Traffic</li>
                        <li>✅ Full Geo-Targeting</li>
                        <li>✅ 24/7 Premium Support</li>
                    </ul>
                    <form method="GET" action="payment_processor.php">
                        <input type="hidden" name="plan" value="enterprise">
                        <button type="submit" class="btn btn-lg btn-danger w-100">
                            Select Enterprise
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Comparison -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Feature Comparison</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th>Free</th>
                                    <th>Starter</th>
                                    <th>Professional</th>
                                    <th>Enterprise</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Websites</td>
                                    <td>3</td>
                                    <td>10</td>
                                    <td>25</td>
                                    <td>Unlimited</td>
                                </tr>
                                <tr>
                                    <td>Monthly Credits</td>
                                    <td>Earn Only</td>
                                    <td>500</td>
                                    <td>1500</td>
                                    <td>5000</td>
                                </tr>
                                <tr>
                                    <td>Analytics</td>
                                    <td>Basic</td>
                                    <td>Advanced</td>
                                    <td>Premium</td>
                                    <td>Enterprise</td>
                                </tr>
                                <tr>
                                    <td>Traffic Priority</td>
                                    <td>Standard</td>
                                    <td>Priority</td>
                                    <td>High Priority</td>
                                    <td>Highest Priority</td>
                                </tr>
                                <tr>
                                    <td>Geo-Targeting</td>
                                    <td>❌</td>
                                    <td>✅ Basic</td>
                                    <td>✅ Advanced</td>
                                    <td>✅ Full</td>
                                </tr>
                                <tr>
                                    <td>Support</td>
                                    <td>Community</td>
                                    <td>Email</td>
                                    <td>Priority Email</td>
                                    <td>24/7 Phone & Email</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>
