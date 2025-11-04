<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

$page_title = "Dashboard - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';

// Fetch user info from DB
$pdo = getDBConnection();
$user_stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch();

// Update session credits from database to ensure accuracy
$_SESSION['credits'] = $user['credits'];

// Count user's websites
$websites_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM websites WHERE user_id = ?");
$websites_stmt->execute([$_SESSION['user_id']]);
$websites_count = $websites_stmt->fetch()['count'];

// Get today's earnings (credits earned)
$today_earnings_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM visits WHERE visitor_id = ? AND DATE(visit_date) = CURDATE()");
$today_earnings_stmt->execute([$_SESSION['user_id']]);
$today_earnings = $today_earnings_stmt->fetch()['count'];

// Get total credits earned (all time)
$total_earned_stmt = $pdo->prepare("SELECT COUNT(*) as count FROM visits WHERE visitor_id = ?");
$total_earned_stmt->execute([$_SESSION['user_id']]);
$total_earned = $total_earned_stmt->fetch()['count'];

// Get visits received (when others viewed user's websites)
$visits_received_stmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM visits v 
    JOIN websites w ON v.website_id = w.website_id 
    WHERE w.user_id = ?
");
$visits_received_stmt->execute([$_SESSION['user_id']]);
$visits_received = $visits_received_stmt->fetch()['count'];
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">

            <!-- Welcome + Logout -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Welcome, <?php echo htmlspecialchars($user['first_name']); ?>! </h2>
                <a href="/webspark-traffic/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>

            <!-- Credit Balance Card -->
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><i class="fas fa-wallet me-2"></i>Your Credit Balance</h4>
                            <p class="mb-0">Available credits to get visitors for your websites</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="display-4 fw-bold"><?php echo $_SESSION['credits']; ?></div>
                            <small>Available Credits</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Quick Actions -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Quick Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="/webspark-traffic/earn_credits.php" class="btn btn-primary mb-2">
                                    <i class="fas fa-coins me-1"></i> Earn Credits
                                </a>
                                <a href="/webspark-traffic/my_websites.php" class="btn btn-success mb-2">
                                    <i class="fas fa-plus me-1"></i> Add Website
                                </a>
                                <a href="/webspark-traffic/analytics.php" class="btn btn-info mb-2">
                                    <i class="fas fa-chart-bar me-1"></i> View Analytics
                                </a>
                                <a href="/webspark-traffic/upgrade.php" class="btn btn-warning">
                                    <i class="fas fa-crown me-1"></i> Upgrade Plan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Account Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Account Type:</strong> 
                                        <span class="badge bg-<?php echo $user['account_type'] === 'premium' ? 'warning' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['account_type']); ?>
                                        </span>
                                    </p>
                                    <p><strong>Member Since:</strong> <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-success mb-2">
                                <i class="fas fa-coins fa-2x"></i>
                            </div>
                            <h4 class="text-success">+<?php echo $total_earned; ?></h4>
                            <p class="text-muted mb-0">Total Earned</p>
                            <small class="text-muted">All-time credits earned</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-info mb-2">
                                <i class="fas fa-eye fa-2x"></i>
                            </div>
                            <h4 class="text-info"><?php echo $visits_received; ?></h4>
                            <p class="text-muted mb-0">Visits Received</p>
                            <small class="text-muted">Views on your websites</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-primary mb-2">
                                <i class="fas fa-globe fa-2x"></i>
                            </div>
                            <h4 class="text-primary"><?php echo $websites_count; ?></h4>
                            <p class="text-muted mb-0">Active Websites</p>
                            <small class="text-muted">In traffic rotation</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="text-warning mb-2">
                                <i class="fas fa-calendar-day fa-2x"></i>
                            </div>
                            <h4 class="text-warning">+<?php echo $today_earnings; ?></h4>
                            <p class="text-muted mb-0">Today's Earnings</p>
                            <small class="text-muted">Credits earned today</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity + Quick Stats -->
            <div class="row">
                <!-- Recent Activity -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $activity_stmt = $pdo->prepare("
                                (SELECT 'visit_earned' as type, visit_date as date, 'Earned 1 credit by viewing a website' as description, 'success' as color
                                 FROM visits WHERE visitor_id = ? 
                                 ORDER BY visit_date DESC LIMIT 3)
                                UNION ALL
                                (SELECT 'visit_received' as type, visit_date as date, CONCAT('Received a visitor on \"', w.website_title, '\"') as description, 'info' as color
                                 FROM visits v 
                                 JOIN websites w ON v.website_id = w.website_id 
                                 WHERE w.user_id = ? 
                                 ORDER BY visit_date DESC LIMIT 2)
                                UNION ALL
                                (SELECT 'website' as type, created_at as date, CONCAT('Added website \"', website_title, '\"') as description, 'primary' as color
                                 FROM websites WHERE user_id = ? 
                                 ORDER BY created_at DESC LIMIT 2)
                                ORDER BY date DESC LIMIT 5
                            ");
                            $activity_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
                            $activities = $activity_stmt->fetchAll();
                            
                            if ($activities):
                                foreach ($activities as $activity):
                                    $icon = '';
                                    $text_class = 'text-' . $activity['color'];
                                    switch($activity['type']) {
                                        case 'visit_earned': $icon = 'fa-arrow-up text-success'; break;
                                        case 'visit_received': $icon = 'fa-eye text-info'; break;
                                        case 'website': $icon = 'fa-globe text-primary'; break;
                                    }
                            ?>
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <i class="fas <?php echo $icon; ?> me-2"></i>
                                        <span class="<?php echo $text_class; ?>">
                                            <?php echo htmlspecialchars($activity['description']); ?>
                                        </span>
                                    </div>
                                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($activity['date'])); ?></small>
                                </div>
                            <?php
                                endforeach;
                            else:
                            ?>
                                <p class="text-muted">No recent activity. Start by adding a website or earning credits!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Performance Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>Available Credits:</span>
                                <strong class="text-primary"><?php echo $_SESSION['credits']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>Today's Earnings:</span>
                                <strong class="text-success">+<?php echo $today_earnings; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>Active Websites:</span>
                                <strong><?php echo $websites_count; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span>Total Earned:</span>
                                <strong class="text-success">+<?php echo $total_earned; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-2">
                                <span>Visits Received:</span>
                                <strong class="text-info"><?php echo $visits_received; ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Credit Tips -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Credit Tips</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex mb-2">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                <small>Earn credits by viewing other websites</small>
                            </div>
                            <div class="d-flex mb-2">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                <small>Spend credits to get visitors to your sites</small>
                            </div>
                            <div class="d-flex">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                <small>Add more websites to receive more traffic</small>
                            </div>
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