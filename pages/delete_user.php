<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("User ID required.");
}
$id = $_GET['id'];

// Prevent deleting your own account
if ($_SESSION['user_id'] == $id) {
    die("You cannot delete your own account.");
}

// Optional: protect the first admin
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($user) {
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
}

header("Location: users.php");
exit;
?>
