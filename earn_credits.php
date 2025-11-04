<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

$pdo = getDBConnection();
$message = '';

// Get a random website to view (excluding user's own websites)
$stmt = $pdo->prepare("
    SELECT w.*, u.username 
    FROM websites w 
    JOIN users u ON w.user_id = u.user_id 
    WHERE w.user_id != ? AND w.is_active = 1 
    ORDER BY RAND() 
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$website_to_view = $stmt->fetch();

// Handle credit earning
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_view'])) {
    $viewed_website_id = (int)$_POST['website_id'];
    
    try {
        // First, get the website owner's ID
        $stmt = $pdo->prepare("SELECT user_id FROM websites WHERE website_id = ?");
        $stmt->execute([$viewed_website_id]);
        $website_owner = $stmt->fetch();

        if (!$website_owner) {
            throw new Exception("Website not found");
        }

        $website_owner_id = $website_owner['user_id'];
        
        // Record the visit
        $stmt = $pdo->prepare("INSERT INTO visits (visitor_id, website_id, visit_date, dwell_time) VALUES (?, ?, NOW(), 30)");
        $stmt->execute([$_SESSION['user_id'], $viewed_website_id]);
        
        // Award credit to viewer
        $stmt = $pdo->prepare("UPDATE users SET credits = credits + 1 WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // ⭐⭐⭐ CRITICAL FIX: Deduct credit from website owner ⭐⭐⭐
        $stmt = $pdo->prepare("UPDATE users SET credits = credits - 1 WHERE user_id = ? AND credits > 0");
        $stmt->execute([$website_owner_id]);
        
        // Update session credits for current user
        $_SESSION['credits'] += 1;
        
        // Update analytics for website owner
        $stmt = $pdo->prepare("
            INSERT INTO analytics (website_id, visit_date, total_visits) 
            VALUES (?, CURDATE(), 1) 
            ON DUPLICATE KEY UPDATE total_visits = total_visits + 1
        ");
        $stmt->execute([$viewed_website_id]);
        
        $message = '<div class="alert alert-success">🎉 You earned 1 credit! The website owner paid 1 credit for your visit.</div>';
        
        // Get new random website
        $stmt = $pdo->prepare("
            SELECT w.*, u.username 
            FROM websites w 
            JOIN users u ON w.user_id = u.user_id 
            WHERE w.user_id != ? AND w.is_active = 1 
            ORDER BY RAND() 
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $website_to_view = $stmt->fetch();
        
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Error processing credit: ' . $e->getMessage() . '</div>';
    }
}

$page_title = "Earn Credits - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Earn Credits</h2>
            <p class="lead">View websites for 30 seconds to earn 1 credit per view</p>
            <?php echo $message; ?>
        </div>
    </div>

    <?php if ($website_to_view): ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">View Website to Earn Credit</h5>
                </div>
                <div class="card-body">
                    <div class="website-preview mb-4">
                        <h4><?php echo htmlspecialchars($website_to_view['website_title']); ?></h4>
                        <p class="text-muted"><?php echo htmlspecialchars($website_to_view['website_description']); ?></p>
                        <p><strong>URL:</strong> 
                            <a href="<?php echo htmlspecialchars($website_to_view['website_url']); ?>" target="_blank" class="text-decoration-none">
                                <?php echo htmlspecialchars($website_to_view['website_url']); ?>
                            </a>
                        </p>
                        <p><strong>Category:</strong> <?php echo ucfirst($website_to_view['website_category']); ?></p>
                        <p><strong>Added by:</strong> <?php echo htmlspecialchars($website_to_view['username']); ?></p>
                    </div>
                    
                    <div class="viewing-instructions">
                        <div class="alert alert-info">
                            <h6>How to earn your credit:</h6>
                            <ol>
                                <li>Click the link below to open the website in a new tab</li>
                                <li>View the website for at least 30 seconds</li>
                                <li>Return here and click "I Viewed the Website"</li>
                                <li>Receive 1 credit instantly (deducted from website owner)</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo htmlspecialchars($website_to_view['website_url']); ?>" target="_blank" class="btn btn-primary btn-lg">
                            <i class="fas fa-external-link-alt me-2"></i>Open Website in New Tab
                        </a>
                        
                        <form method="POST" action="" class="mt-3">
                            <input type="hidden" name="website_id" value="<?php echo $website_to_view['website_id']; ?>">
                            <button type="submit" name="complete_view" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-check-circle me-2"></i>I Viewed the Website (Earn 1 Credit)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Your Credits</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-4 text-primary"><?php echo $_SESSION['credits']; ?></div>
                    <p class="text-muted">Available Credits</p>
                    <small class="text-info">You earn 1 credit per view</small>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Credit System Explained</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-eye text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6>View Websites</h6>
                            <p class="small mb-0">Spend 30 seconds viewing other members' websites</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-plus-circle text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6>You Earn +1 Credit</h6>
                            <p class="small mb-0">Receive 1 credit for each completed view</p>
                        </div>
                    </div>
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-minus-circle text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6>Owner Pays -1 Credit</h6>
                            <p class="small mb-0">Website owner pays 1 credit for your visit</p>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exchange-alt text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6>Balanced Economy</h6>
                            <p class="small mb-0">Sustainable system where traffic is the currency</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get user's earning stats
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total_views FROM visits WHERE visitor_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $stats = $stmt->fetch();
                    ?>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h5 text-primary"><?php echo $stats['total_views']; ?></div>
                            <small class="text-muted">Total Views</small>
                        </div>
                        <div class="col-6">
                            <div class="h5 text-success"><?php echo $stats['total_views']; ?></div>
                            <small class="text-muted">Credits Earned</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-globe fa-4x text-muted mb-3"></i>
                    <h4>No Websites Available to View</h4>
                    <p class="text-muted">There are currently no active websites in the system. Check back later or add your own websites to help grow the network.</p>
                    <a href="my_websites.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Your Website
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
require_once ROOT_PATH . '/includes/footer.php';
?>