<?php
require '../config.php';

$supplier_id = $_GET['supplier_id'] ?? null;

if (!$supplier_id) {
    die("Supplier not selected.");
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="supplier_payments.csv"');

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT * FROM products WHERE supplier_id = ? AND is_paid = 1 ORDER BY paid_at DESC");
$stmt->execute([$supplier_id]);
$rows = $stmt->fetchAll();

$output = fopen('php://output', 'w');
fputcsv($output, ['#', 'Product Name', 'Barcode', 'Qty', 'Unit Price', 'Discount (%)', 'Paid Amount', 'Paid Date']);

foreach ($rows as $i => $p) {
    $discount_amt = ($p['discount_percent'] ?? 0) / 100 * $p['price'] * $p['quantity'];
    $total_paid = $p['price'] * $p['quantity'] - $discount_amt;
    fputcsv($output, [
        $i + 1,
        $p['name'],
        $p['barcode'],
        $p['quantity'],
        "₨" . number_format($p['price'], 2),
        $p['discount_percent'] ?? 0,
        "₨" . number_format($total_paid, 2),
        $p['paid_at']
    ]);
}
fclose($output);
exit;
?>
