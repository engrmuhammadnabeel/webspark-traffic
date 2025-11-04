<?php
require_once __DIR__ . '/bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>