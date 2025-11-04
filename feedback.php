<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

$pdo = getDBConnection();
$message = '';

// Handle form submission (UPDATED with error logging)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $subject = sanitizeInput($_POST['subject'], 'string');
    $message_text = sanitizeInput($_POST['message'], 'string');
    $type = sanitizeInput($_POST['type'], 'string');
    
    // Debug: Log incoming form data
    error_log("Feedback Form Data - Subject: $subject, Type: $type, User ID: " . $_SESSION['user_id']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, subject, message, type, created_at) VALUES (?, ?, ?, ?, NOW())");
        $result = $stmt->execute([$_SESSION['user_id'], $subject, $message_text, $type]);
        
        if ($result) {
            $message = '<div class="alert alert-success">Thank you for your feedback! We will review it shortly.</div>';
            error_log("Feedback submitted successfully for user: " . $_SESSION['user_id']);
        } else {
            $message = '<div class="alert alert-danger">Failed to submit feedback. Please try again.</div>';
            error_log("Feedback submission failed - execute returned false");
        }
    } catch (PDOException $e) {
        $message = '<div class="alert alert-danger">Error submitting feedback: ' . $e->getMessage() . '</div>';
        error_log("Feedback PDO Error: " . $e->getMessage());
    }
}

// ✅ Updated SQL query
$feedback_stmt = $pdo->prepare("SELECT feedback_id, type, subject, message, admin_response, status, created_at, updated_at FROM feedback WHERE user_id = ? ORDER BY created_at DESC");
$feedback_stmt->execute([$_SESSION['user_id']]);
$user_feedback = $feedback_stmt->fetchAll();

$page_title = "Feedback & Support - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Feedback & Support</h2>
            <p class="lead">Report issues, suggest features, or get help</p>
            <?php echo $message; ?>
        </div>
    </div>

    <div class="row">
        <!-- Feedback Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Submit Feedback</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="type" class="form-label">Feedback Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="general">General Feedback</option>
                                <option value="bug">Bug Report</option>
                                <option value="feature">Feature Request</option>
                                <option value="abuse">Report Abuse</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" placeholder="Brief description of your feedback" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="6" placeholder="Please provide detailed information..." required></textarea>
                        </div>
                        
                        <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Help</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="my_websites.php" class="btn btn-outline-primary">
                            <i class="fas fa-globe me-2"></i>Manage Websites
                        </a>
                        <a href="earn_credits.php" class="btn btn-outline-success">
                            <i class="fas fa-coins me-2"></i>Earn Credits
                        </a>
                        <a href="analytics.php" class="btn btn-outline-info">
                            <i class="fas fa-chart-bar me-2"></i>View Analytics
                        </a>
                    </div>
                    
                    <div class="mt-4">
                        <h6>Common Issues:</h6>
                        <ul class="small">
                            <li>Website not receiving traffic?</li>
                            <li>Credit balance issues?</li>
                            <li>Technical problems?</li>
                            <li>Report abusive content</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Previous Feedback -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Your Previous Feedback</h5>
                </div>
                <div class="card-body">
                    <?php if (count($user_feedback) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Response</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($user_feedback as $feedback): ?>
                                    <tr>
                                        <td><?php echo date('M j, Y', strtotime($feedback['created_at'])); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($feedback['type']) {
                                                    'bug' => 'danger',
                                                    'feature' => 'info',
                                                    'abuse' => 'warning',
                                                    default => 'primary'
                                                };
                                            ?>">
                                                <?php echo ucfirst($feedback['type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($feedback['subject']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($feedback['status']) {
                                                    'open' => 'secondary',
                                                    'in_progress' => 'warning',
                                                    'resolved' => 'success',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $feedback['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($feedback['admin_response'])): ?>
                                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#responseModal<?php echo $feedback['feedback_id']; ?>">
                                                    View Response
                                                </button>
                                                
                                                <!-- Response Modal -->
                                                <div class="modal fade" id="responseModal<?php echo $feedback['feedback_id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Admin Response</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-light border">
                                                                    <strong>Your Original Message:</strong>
                                                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                                                                </div>
                                                                <div class="alert alert-info">
                                                                    <strong>Admin Response:</strong>
                                                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($feedback['admin_response'])); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No response yet</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5>No Feedback Submitted Yet</h5>
                            <p class="text-muted">Your feedback submissions will appear here.</p>
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
