<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch sales by this cashier
$stmt = $pdo->prepare("SELECT * FROM sales WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$sales = $stmt->fetchAll();

// Sum total sales
$stmtTotal = $pdo->prepare("SELECT SUM(total_amount) FROM sales WHERE user_id = ?");
$stmtTotal->execute([$user_id]);
$total_sales = $stmtTotal->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Sales Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h3>🧾 My Sales Summary</h3>

    <div class="alert alert-info mb-4">
        <strong>Total Cash Flow:</strong> $<?= number_format($total_sales, 2) ?>
    </div>

    <?php if ($sales): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Invoice #</th>
                    <th>Amount</th>
                    <th>Payment Mode</th>
                    <th>Date</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sales as $sale): ?>
                    <tr>
                        <td>#<?= $sale['id'] ?></td>
                        <td>$<?= number_format($sale['total_amount'], 2) ?></td>
                        <td><?= ucfirst($sale['payment_mode']) ?></td>
                        <td><?= date("d-M-Y h:i A", strtotime($sale['created_at'])) ?></td>
                        <td><a href="print_invoice1.php?sale_id=<?= $sale['id'] ?>" class="btn btn-sm btn-info">Invoice</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No sales found.</div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-outline-secondary mt-4">← Back to Dashboard</a>
</body>
</html>
