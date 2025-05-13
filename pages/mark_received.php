<?php
require '../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $return_id = $_POST['return_id'];

    // Get return info
    $stmt = $pdo->prepare("SELECT product_id, return_qty FROM product_returns WHERE id = ?");
    $stmt->execute([$return_id]);
    $return = $stmt->fetch();

    if ($return) {
        $product_id = $return['product_id'];
        $qty = $return['return_qty'];

        // Update stock
        $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?")->execute([$qty, $product_id]);

        // Mark as received
        $pdo->prepare("UPDATE product_returns SET received = 1 WHERE id = ?")->execute([$return_id]);

        header("Location: return_received.php");
        exit;
    }
}
?>
