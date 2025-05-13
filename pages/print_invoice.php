<?php
session_start();
require '../config.php';

if (!isset($_GET['sale_id']) || !is_numeric($_GET['sale_id'])) {
    die("Sale ID missing");
}
$sale_id = (int) $_GET['sale_id'];


// Fetch sale info
$stmtSale = $pdo->prepare("SELECT s.*, u.full_name FROM sales s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
$stmtSale->execute([$sale_id]);
$sale = $stmtSale->fetch();

// Fetch sale items (join to products for tax/discount/image)
$stmtItems = $pdo->prepare("SELECT si.*, p.name, p.tax_percent, p.discount_percent FROM sale_items si 
                            JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
$stmtItems->execute([$sale_id]);
$items = $stmtItems->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= $sale_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print { .noprint { display: none; } }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <h4>🧾 POS Invoice #<?= $sale_id ?></h4>
        <p><strong>Cashier:</strong> <?= htmlspecialchars($sale['full_name']) ?> | <strong>Payment:</strong> <?= ucfirst($sale['payment_mode']) ?></p>
        <p><strong>Date:</strong> <?= date("d-M-Y h:i A", strtotime($sale['created_at'])) ?></p>
        <hr>

        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Tax %</th>
                    <th>Discount %</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subtotal = $total_tax = $total_discount = $grand = 0;
                foreach ($items as $item):
                    $qty = $item['quantity'];
                    $price = $item['unit_price'];
                    $tax = $item['tax_percent'] ?? 0;
                    $disc = $item['discount_percent'] ?? 0;

                    $line_total = $price * $qty;
                    $tax_amt = ($line_total * $tax / 100);
                    $disc_amt = ($line_total * $disc / 100);
                    $final_total = $line_total + $tax_amt - $disc_amt;

                    $subtotal += $line_total;
                    $total_tax += $tax_amt;
                    $total_discount += $disc_amt;
                    $grand += $final_total;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $qty ?></td>
                    <td>RS. <?= number_format($price, 2) ?></td>
                    <td><?= $tax ?>%</td>
                    <td><?= $disc ?>%</td>
                    <td>PKR.<?= number_format($final_total, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-end">
            <p><strong>Subtotal:</strong> Rs<?= number_format($subtotal, 2) ?></p>
            <p><strong>Total Tax:</strong> Rs<?= number_format($total_tax, 2) ?></p>
            <p><strong>Total Discount:</strong> -Rs<?= number_format($total_discount, 2) ?></p>
            <h4><strong>Grand Total:</strong> PKR.<?= number_format($grand, 2) ?></h4>
        </div>

        <div class="text-center mt-4 noprint">
            <button onclick="window.print()" class="btn btn-primary">🖨️ Print Invoice</button>
            <a href="sales.php" class="btn btn-outline-secondary">← Back</a>
        </div>
    </div>
</body>
</html>
