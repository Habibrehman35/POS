<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require '../config.php';

if (!isset($_GET['id'])) {
    die("Product ID is required.");
}

$id = $_GET['id'];

// Optional: check if product was ever sold before allowing deletion

$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

header("Location: products.php");
exit;
?>
