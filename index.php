<?php
// Load bootstrap first
require_once __DIR__ . '/bootstrap.php';

$page_title = "Home - Webspark Traffic Exchange";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto text-center">
        <h1 class="display-4 mb-4">Drive Targeted Traffic to Your Website</h1>
        <p class="lead mb-4">Webspark Traffic Exchange helps website owners, bloggers, and digital marketers attract real human visitors with geo-targeted and niche-specific traffic options.</p>
        
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mb-5">
            <a href="/webspark-traffic/register.php" class="btn btn-primary btn-lg px-4 gap-3">Get Started</a>
            <a href="/webspark-traffic/login.php" class="btn btn-outline-secondary btn-lg px-4">Login</a>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Real Human Visitors</h5>
                <p class="card-text">Our system ensures all traffic comes from real people, not bots, to maintain quality engagement.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-globe-americas fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Geo-Targeting</h5>
                <p class="card-text">Target visitors from specific countries, regions, or cities to reach your ideal audience.</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Advanced Analytics</h5>
                <p class="card-text">Track your results with real-time analytics, visitor insights, and performance metrics.</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>