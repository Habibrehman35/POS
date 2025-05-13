<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: pages/dashboard.php");
    exit;
} else {
    header("Location: pages/login.php");
    exit;
}
?>
