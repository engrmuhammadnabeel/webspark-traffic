<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

$pdo = getDBConnection();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_website'])) {
    $website_url = sanitizeInput($_POST['website_url'], 'url');
    $website_title = sanitizeInput($_POST['website_title'], 'string');
    $website_description = sanitizeInput($_POST['website_description'], 'string');
    $website_category = sanitizeInput($_POST['website_category'], 'string');
    $target_countries = sanitizeInput($_POST['target_countries'], 'string');

    try {
        // Validate URL
        if (!filter_var($website_url, FILTER_VALIDATE_URL)) {
            $message = '<div class="alert alert-danger">Please enter a valid website URL</div>';
        } else {
            // Check if user has reached website limit
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM websites WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $website_count = $stmt->fetch()['count'];

            if ($website_count >= 3 && $_SESSION['account_type'] === 'free') {
                $message = '<div class="alert alert-warning">Free accounts can only add up to 3 websites. <a href="upgrade.php">Upgrade to add more</a></div>';
            } else {
                // Add website
                $stmt = $pdo->prepare("INSERT INTO websites (user_id, website_url, website_title, website_description, website_category, target_countries, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$_SESSION['user_id'], $website_url, $website_title, $website_description, $website_category, $target_countries]);
                
                $message = '<div class="alert alert-success">Website added successfully!</div>';
            }
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error adding website: ' . $e->getMessage() . '</div>';
    }
}

// Handle website deletion
if (isset($_GET['delete'])) {
    $website_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM websites WHERE website_id = ? AND user_id = ?");
    $stmt->execute([$website_id, $_SESSION['user_id']]);
    $message = '<div class="alert alert-success">Website deleted successfully!</div>';
}

// Get user's websites with visit counts
$websites_stmt = $pdo->prepare("
    SELECT w.*, 
           COUNT(v.visit_id) as total_visits,
           (SELECT COUNT(*) FROM visits v2 WHERE v2.website_id = w.website_id AND DATE(v2.visit_date) = CURDATE()) as today_visits
    FROM websites w 
    LEFT JOIN visits v ON w.website_id = v.website_id 
    WHERE w.user_id = ? 
    GROUP BY w.website_id 
    ORDER BY w.created_at DESC
");
$websites_stmt->execute([$_SESSION['user_id']]);
$websites = $websites_stmt->fetchAll();

// Get current website count for progress display
$current_count = count($websites);
$max_free_websites = 3;
$progress_percentage = min(100, ($current_count / $max_free_websites) * 100);

$page_title = "My Websites - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>My Websites</h2>
            <?php echo $message; ?>
        </div>
    </div>

    <!-- Website Limits Progress -->
    <?php if ($_SESSION['account_type'] === 'free'): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Website Usage
                        <span class="badge bg-<?php echo $current_count >= $max_free_websites ? 'warning' : 'primary'; ?> float-end">
                            <?php echo $current_count; ?>/<?php echo $max_free_websites; ?> websites
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-2" style="height: 20px;">
                        <div class="progress-bar bg-<?php echo $current_count >= $max_free_websites ? 'warning' : 'success'; ?>" 
                             role="progressbar" 
                             style="width: <?php echo $progress_percentage; ?>%"
                             aria-valuenow="<?php echo $progress_percentage; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            <?php echo $current_count; ?> of <?php echo $max_free_websites; ?> websites
                        </div>
                    </div>
                    <?php if ($current_count >= $max_free_websites): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You've reached the free account limit. <a href="upgrade.php" class="alert-link">Upgrade your account</a> to add more websites.
                        </div>
                    <?php else: ?>
                        <small class="text-muted">You can add <?php echo $max_free_websites - $current_count; ?> more website(s) with your free account.</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add Website Form -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Add New Website
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="website_url" class="form-label">Website URL *</label>
                                <input type="url" class="form-control" id="website_url" name="website_url" 
                                       placeholder="https://example.com" required
                                       <?php echo ($_SESSION['account_type'] === 'free' && $current_count >= $max_free_websites) ? 'disabled' : ''; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="website_title" class="form-label">Website Title *</label>
                                <input type="text" class="form-control" id="website_title" name="website_title" 
                                       placeholder="My Awesome Blog" required
                                       <?php echo ($_SESSION['account_type'] === 'free' && $current_count >= $max_free_websites) ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="website_description" class="form-label">Description</label>
                            <textarea class="form-control" id="website_description" name="website_description" 
                                      rows="2" placeholder="Brief description of your website"
                                      <?php echo ($_SESSION['account_type'] === 'free' && $current_count >= $max_free_websites) ? 'disabled' : ''; ?>></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="website_category" class="form-label">Category</label>
                                <select class="form-select" id="website_category" name="website_category"
                                        <?php echo ($_SESSION['account_type'] === 'free' && $current_count >= $max_free_websites) ? 'disabled' : ''; ?>>
                                    <option value="technology">Technology</option>
                                    <option value="business">Business</option>
                                    <option value="health">Health & Fitness</option>
                                    <option value="education">Education</option>
                                    <option value="entertainment">Entertainment</option>
                                    <option value="lifestyle">Lifestyle</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="target_countries" class="form-label">Target Countries</label>
                                <select class="form-select" id="target_countries" name="target_countries"
                                        <?php echo ($_SESSION['account_type'] === 'free' && $current_count >= $max_free_websites) ? 'disabled' : ''; ?>>
                                    <option value="worldwide">Worldwide</option>
                                    <option value="us">United States</option>
                                    <option value="uk">United Kingdom</option>
                                    <option value="ca">Canada</option>
                                    <option value="au">Australia</option>
                                    <option value="eu">European Union</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_website" class="btn btn-primary"
                                <?php echo ($_SESSION['account_type'] === 'free' && $current_count >= $max_free_websites) ? 'disabled' : ''; ?>>
                            <i class="fas fa-plus me-1"></i>Add Website
                        </button>
                        
                        <?php if ($_SESSION['account_type'] === 'free' && $current_count >= $max_free_websites): ?>
                            <a href="upgrade.php" class="btn btn-warning ms-2">
                                <i class="fas fa-crown me-1"></i>Upgrade to Add More
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Website Limits
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p><strong>Free Account:</strong> 3 websites</p>
                        <p><strong>Starter Account:</strong> 10 websites</p>
                        <p><strong>Professional:</strong> 25 websites</p>
                        <p><strong>Enterprise:</strong> Unlimited websites</p>
                    </div>
                    <a href="upgrade.php" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-crown me-1"></i>Upgrade Account
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Total Websites:</span>
                        <strong><?php echo $current_count; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span>Active Websites:</span>
                        <strong>
                            <?php 
                            $active_count = 0;
                            foreach ($websites as $website) {
                                if ($website['is_active']) $active_count++;
                            }
                            echo $active_count;
                            ?>
                        </strong>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span>Total Visits:</span>
                        <strong class="text-success">
                            <?php 
                            $total_visits = 0;
                            foreach ($websites as $website) {
                                $total_visits += $website['total_visits'];
                            }
                            echo $total_visits;
                            ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Websites List -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>My Websites List
                        <span class="badge bg-primary float-end"><?php echo count($websites); ?> websites</span>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (count($websites) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Website</th>
                                        <th>Category</th>
                                        <th>Targeting</th>
                                        <th>Visits</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($websites as $website): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($website['website_title']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($website['website_url']); ?></small><br>
                                            <small class="text-info">
                                                <i class="fas fa-calendar-day me-1"></i>Added: <?php echo date('M j, Y', strtotime($website['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo ucfirst($website['website_category']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="fas fa-globe me-1"></i>
                                                <?php echo ucfirst(str_replace('_', ' ', $website['target_countries'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <div class="h6 mb-0 text-primary"><?php echo $website['total_visits']; ?></div>
                                                <small class="text-muted">total</small>
                                                <?php if ($website['today_visits'] > 0): ?>
                                                    <div class="small text-success">
                                                        +<?php echo $website['today_visits']; ?> today
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $website['is_active'] ? 'success' : 'secondary'; ?>">
                                                <i class="fas fa-<?php echo $website['is_active'] ? 'play' : 'pause'; ?> me-1"></i>
                                                <?php echo $website['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="edit_website.php?id=<?php echo $website['website_id']; ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="Edit Website">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $website['website_id']; ?>" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this website? This action cannot be undone.')"
                                                   title="Delete Website">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                            <h5>No Websites Added Yet</h5>
                            <p class="text-muted">Add your first website to start receiving traffic!</p>
                            <a href="#add-website-form" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Your First Website
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>