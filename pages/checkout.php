<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart']) || !isset($_POST['payment_mode'])) {
    die("Invalid request");
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];
$payment_mode = $_POST['payment_mode'];
$total_amount = 0;

foreach ($cart as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

try {
    $pdo->beginTransaction();

    // Insert sale
    $stmt = $pdo->prepare("INSERT INTO sales (user_id, total_amount, payment_mode) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $total_amount, $payment_mode]);
    $sale_id = $pdo->lastInsertId();

    // Insert items + reduce stock
    $insertItem = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
    $updateStock = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");

    foreach ($cart as $item) {
        $insertItem->execute([
            $sale_id,
            $item['id'],
            $item['quantity'],
            $item['price'],
            $item['price'] * $item['quantity']
        ]);
        $updateStock->execute([$item['quantity'], $item['id']]);
    }

    $pdo->commit();
    $_SESSION['cart'] = [];

    header("Location: print_invoice.php?sale_id=$sale_id");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Checkout failed: " . $e->getMessage());
}
?>
