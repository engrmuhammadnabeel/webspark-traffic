<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $type = sanitizeInput($_POST['type'], 'string');
    $subject = sanitizeInput($_POST['subject'], 'string');
    $message = sanitizeInput($_POST['message'], 'string');
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO feedback (user_id, type, subject, message, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 'open', NOW(), NOW())
        ");
        
        if ($stmt->execute([$user_id, $type, $subject, $message])) {
            $_SESSION['success_message'] = "Feedback submitted successfully! We'll get back to you soon.";
        } else {
            $_SESSION['error_message'] = "Failed to submit feedback. Please try again.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error submitting feedback: " . $e->getMessage();
    }
    
    header("Location: my_feedback.php");
    exit();
}
?>