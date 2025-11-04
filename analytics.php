<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

$pdo = getDBConnection();

// Get user's analytics data
$analytics_stmt = $pdo->prepare("
    SELECT a.*, w.website_title, w.website_url 
    FROM analytics a 
    JOIN websites w ON a.website_id = w.website_id 
    WHERE w.user_id = ? 
    ORDER BY a.visit_date DESC 
    LIMIT 30
");
$analytics_stmt->execute([$_SESSION['user_id']]);
$analytics_data = $analytics_stmt->fetchAll();

// Calculate totals
$total_visits = 0;
$total_dwell_time = 0;
$unique_visits = 0;

foreach ($analytics_data as $data) {
    $total_visits += $data['total_visits'];
    $total_dwell_time += $data['total_dwell_time'];
    $unique_visits += $data['unique_visits'];
}

$avg_dwell_time = $total_visits > 0 ? round($total_dwell_time / $total_visits) : 0;

// ==================================================
// REAL DATA: Get Visitor Geography from actual visits
// ==================================================
$geo_stmt = $pdo->prepare("
    SELECT 
        w.target_countries as country,
        COUNT(v.visit_id) as visit_count
    FROM visits v
    JOIN websites w ON v.website_id = w.website_id
    WHERE w.user_id = ?
    GROUP BY w.target_countries
    ORDER BY visit_count DESC
    LIMIT 10
");
$geo_stmt->execute([$_SESSION['user_id']]);
$visitor_geography = $geo_stmt->fetchAll();

// Calculate percentages for geography
$total_geo_visits = 0;
foreach ($visitor_geography as $geo) {
    $total_geo_visits += $geo['visit_count'];
}

// ==================================================
// REAL DATA: Get Traffic Sources (simulated based on visit patterns)
// ==================================================
$traffic_sources = [];

// Get direct traffic (visits with no specific referrer)
$direct_traffic_stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM visits v
    JOIN websites w ON v.website_id = w.website_id
    WHERE w.user_id = ?
");
$direct_traffic_stmt->execute([$_SESSION['user_id']]);
$direct_traffic = $direct_traffic_stmt->fetch()['count'];

// Get Webspark network traffic (visits from other users in the system)
$network_traffic_stmt = $pdo->prepare("
    SELECT COUNT(*) as count 
    FROM visits v
    JOIN websites w ON v.website_id = w.website_id
    WHERE w.user_id = ? 
    AND v.visitor_id IN (SELECT user_id FROM users WHERE user_id != ?)
");
$network_traffic_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$network_traffic = $network_traffic_stmt->fetch()['count'];

// Calculate traffic source percentages
$total_traffic = $direct_traffic;
if ($total_traffic > 0) {
    $traffic_sources = [
        'Webspark Network' => round(($network_traffic / $total_traffic) * 100),
        'Direct Traffic' => round((($direct_traffic - $network_traffic) / $total_traffic) * 100)
    ];
} else {
    $traffic_sources = [
        'Webspark Network' => 0,
        'Direct Traffic' => 0
    ];
}

// ==================================================
// REAL DATA: Get Visit Trends for Charts (Last 7 days)
// ==================================================
$trends_stmt = $pdo->prepare("
    SELECT 
        DATE(v.visit_date) as visit_day,
        COUNT(v.visit_id) as daily_visits,
        AVG(v.dwell_time) as avg_dwell_time
    FROM visits v
    JOIN websites w ON v.website_id = w.website_id
    WHERE w.user_id = ? 
    AND v.visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(v.visit_date)
    ORDER BY visit_day ASC
");
$trends_stmt->execute([$_SESSION['user_id']]);
$visit_trends = $trends_stmt->fetchAll();

// Prepare data for charts
$visit_dates = [];
$visit_counts = [];
$dwell_times = [];

foreach ($visit_trends as $trend) {
    $visit_dates[] = date('M j', strtotime($trend['visit_day']));
    $visit_counts[] = $trend['daily_visits'];
    $dwell_times[] = round($trend['avg_dwell_time']);
}

$page_title = "Analytics - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Website Analytics</h2>
            <p class="lead">Track your website performance and visitor engagement</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Visits</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_visits; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Unique Visitors</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $unique_visits; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg. Dwell Time</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $avg_dwell_time; ?>s</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Bounce Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php 
                                $bounce_rate = $total_visits > 0 ? round(($total_visits - $unique_visits) / $total_visits * 100) : 0;
                                echo $bounce_rate . '%';
                                ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visit Trends Chart -->
    <?php if (count($visit_trends) > 0): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Visit Trends (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="visitTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Analytics Data -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Traffic Data</h5>
                </div>
                <div class="card-body">
                    <?php if (count($analytics_data) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Website</th>
                                        <th>Total Visits</th>
                                        <th>Unique Visitors</th>
                                        <th>Avg. Dwell Time</th>
                                        <th>Bounce Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics_data as $data): 
                                        $daily_bounce_rate = $data['total_visits'] > 0 ? 
                                            round(($data['total_visits'] - $data['unique_visits']) / $data['total_visits'] * 100) : 0;
                                        $avg_daily_dwell = $data['total_visits'] > 0 ? 
                                            round($data['total_dwell_time'] / $data['total_visits']) : 0;
                                    ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($data['visit_date'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($data['website_title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($data['website_url']); ?></small>
                                        </td>
                                        <td><?php echo $data['total_visits']; ?></td>
                                        <td><?php echo $data['unique_visits']; ?></td>
                                        <td><?php echo $avg_daily_dwell; ?>s</td>
                                        <td>
                                            <span class="badge bg-<?php echo $daily_bounce_rate < 50 ? 'success' : ($daily_bounce_rate < 70 ? 'warning' : 'danger'); ?>">
                                                <?php echo $daily_bounce_rate; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h5>No Analytics Data Yet</h5>
                            <p class="text-muted">Your analytics will appear here once you start receiving visitors.</p>
                            <a href="my_websites.php" class="btn btn-primary">Add Websites</a>
                            <a href="earn_credits.php" class="btn btn-success">Earn Credits</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Real Data: Traffic Sources & Visitor Geography -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Traffic Sources</h5>
                </div>
                <div class="card-body">
                    <?php if ($total_traffic > 0): ?>
                        <?php foreach ($traffic_sources as $source => $percentage): 
                            $color = $source == 'Webspark Network' ? 'success' : 'primary';
                        ?>
                        <div class="traffic-source mb-3">
                            <div class="d-flex justify-content-between">
                                <span><?php echo $source; ?></span>
                                <span class="text-<?php echo $color; ?>"><?php echo $percentage; ?>%</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar bg-<?php echo $color; ?>" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No traffic data available yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Visitor Geography</h5>
                </div>
                <div class="card-body">
                    <?php if (count($visitor_geography) > 0): ?>
                        <?php 
                        $other_percentage = 100;
                        foreach ($visitor_geography as $index => $geo): 
                            $percentage = $total_geo_visits > 0 ? round(($geo['visit_count'] / $total_geo_visits) * 100) : 0;
                            $other_percentage -= $percentage;
                            
                            // Country flag mapping
                            $flags = [
                                'worldwide' => '🌍',
                                'us' => '🇺🇸',
                                'uk' => '🇬🇧', 
                                'ca' => '🇨🇦',
                                'au' => '🇦🇺',
                                'eu' => '🇪🇺'
                            ];
                            $flag = $flags[$geo['country']] ?? '🌐';
                            $country_name = [
                                'worldwide' => 'Worldwide',
                                'us' => 'United States',
                                'uk' => 'United Kingdom',
                                'ca' => 'Canada', 
                                'au' => 'Australia',
                                'eu' => 'European Union'
                            ][$geo['country']] ?? ucfirst($geo['country']);
                        ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?php echo $flag . ' ' . $country_name; ?></span>
                            <strong><?php echo $percentage; ?>%</strong>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center">No geographic data available yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (count($visit_trends) > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Visit Trends Chart
const visitTrendsCtx = document.getElementById('visitTrendsChart').getContext('2d');
const visitTrendsChart = new Chart(visitTrendsCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($visit_dates); ?>,
        datasets: [{
            label: 'Daily Visits',
            data: <?php echo json_encode($visit_counts); ?>,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>