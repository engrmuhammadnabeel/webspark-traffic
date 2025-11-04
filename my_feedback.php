<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/bootstrap.php';

// ✅ Show success/error messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

$page_title = "My Feedback - Webspark Traffic";
require_once ROOT_PATH . '/includes/header.php';

$pdo = getDBConnection();

// Get user's feedback
$stmt = $pdo->prepare("
    SELECT feedback_id, type, subject, message, admin_response, status, created_at, updated_at 
    FROM feedback 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$feedback_list = $stmt->fetchAll();
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">

            <h2>My Feedback & Support Tickets</h2>
            <p class="text-muted">View your submitted feedback and support requests</p>

            <!-- ✅ Success / Error Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Feedback Submission Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Submit New Feedback</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="submit_feedback.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="bug">Bug Report</option>
                                    <option value="feature">Feature Request</option>
                                    <option value="general">General Feedback</option>
                                    <option value="support">Support Request</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>

            <!-- Feedback List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">My Feedback History</h5>
                </div>
                <div class="card-body">
                    <?php if ($feedback_list): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Subject</th>
                                        <th>Status</th>
                                        <th>Admin Response</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feedback_list as $feedback): ?>
                                    <tr>
                                        <td>
                                            <small><?php echo date('M j, Y', strtotime($feedback['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $feedback['type'] === 'bug' ? 'danger' : 
                                                     ($feedback['type'] === 'feature' ? 'info' : 'primary'); 
                                            ?>">
                                                <?php echo ucfirst($feedback['type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($feedback['subject']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo substr(htmlspecialchars($feedback['message']), 0, 50); ?>...</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $feedback['status'] === 'open' ? 'warning' : 
                                                     ($feedback['status'] === 'resolved' ? 'success' : 'secondary'); 
                                            ?>">
                                                <?php echo ucfirst($feedback['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($feedback['admin_response'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#responseModal<?php echo $feedback['feedback_id']; ?>">
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
                                                                <div class="alert alert-info">
                                                                    <strong>Your Original Message:</strong>
                                                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                                                                </div>
                                                                <div class="alert alert-success">
                                                                    <strong>Admin Response:</strong>
                                                                    <p class="mt-2"><?php echo nl2br(htmlspecialchars($feedback['admin_response'])); ?></p>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No response yet</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('M j, Y g:i A', strtotime($feedback['updated_at'])); ?></small>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <p class="text-muted">You haven't submitted any feedback yet.</p>
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
