<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart']) || !isset($_POST['payment_mode'])) {
    die("Invalid request");
}

// ✅ Journal Entry Helper
function insertJournalEntry($pdo, $date, $account, $desc, $debit, $credit, $refType, $refId) {
    $stmt = $pdo->prepare("INSERT INTO general_journal 
        (entry_date, account, description, debit, credit, reference_type, reference_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date, $account, $desc, $debit, $credit, $refType, $refId]);
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

    // 🧾 Insert sale
    $stmt = $pdo->prepare("INSERT INTO sales (user_id, total_amount, payment_mode) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $total_amount, $payment_mode]);
    $sale_id = $pdo->lastInsertId();

    // 🧾 Insert sale items + reduce stock
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

    // 📒 Add journal entries
    $date = date('Y-m-d');
    $description = "Sale to customer (Sale ID #$sale_id)";
    insertJournalEntry($pdo, $date, "Cash", $description, $total_amount, 0, "Sale", $sale_id);
    insertJournalEntry($pdo, $date, "Sales Revenue", $description, 0, $total_amount, "Sale", $sale_id);

    // ✅ Finalize
    $pdo->commit();
    $_SESSION['cart'] = [];
    header("Location: print_invoice.php?sale_id=$sale_id");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Checkout failed: " . $e->getMessage());
}
?>
