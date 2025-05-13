<?php
require '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();
$supplier_id = $_GET['supplier_id'] ?? null;

// Handle Mark as Paid
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    $purchase_id = $_POST['purchase_id'];
    $stmt = $pdo->prepare("UPDATE products SET is_paid = 1, paid_at = NOW() WHERE id = ?");
    $stmt->execute([$purchase_id]);
    header("Location: supplier_payments.php?supplier_id=" . $supplier_id);
    exit;
}

$pending = $paid = [];
if ($supplier_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE supplier_id = ? AND is_paid = 0 ORDER BY created_at DESC");
    $stmt->execute([$supplier_id]);
    $pending = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM products WHERE supplier_id = ? AND is_paid = 1 ORDER BY paid_at DESC");
    $stmt->execute([$supplier_id]);
    $paid = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supplier Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h3>💵 Supplier Payment Management</h3>

    <!-- Select Supplier -->
    <form method="GET" class="my-3">
        <select name="supplier_id" class="form-select" onchange="this.form.submit()" required>
            <option value="">-- Select Supplier --</option>
            <?php foreach ($suppliers as $sup): ?>
                <option value="<?= $sup['id'] ?>" <?= $sup['id'] == $supplier_id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($sup['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($supplier_id): ?>
        <h5 class="text-primary">📌 Pending Bills</h5>
        <?php if (count($pending)): ?>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr><th>#</th><th>Name</th><th>Barcode</th><th>Qty</th><th>Price</th><th>Discount</th><th>Total</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($pending as $i => $p): 
                $discount = ($p['discount_percent'] ?? 0) / 100 * $p['price'] * $p['quantity'];
                $total = $p['price'] * $p['quantity'] - $discount;
            ?>
                <tr class="table-danger">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= $p['barcode'] ?></td>
                    <td><?= $p['quantity'] ?></td>
                    <td>₨<?= number_format($p['price'], 2) ?></td>
                    <td><?= $p['discount_percent'] ?? 0 ?>%</td>
                    <td><strong>₨<?= number_format($total, 2) ?></strong></td>
                    <td><?= date('d-M-Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="purchase_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="mark_paid" class="btn btn-success btn-sm">Mark as Paid</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="alert alert-success">✅ No pending payments.</div>
        <?php endif; ?>

        <h5 class="text-muted mt-5">📖 Payment History</h5>
        <?php if (count($paid)): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-secondary">
                <tr><th>#</th><th>Name</th><th>Barcode</th><th>Qty</th><th>Price</th><th>Discount</th><th>Paid</th><th>Paid Date</th></tr>
            </thead>
            <tbody>
            <?php foreach ($paid as $i => $p): 
                $discount = ($p['discount_percent'] ?? 0) / 100 * $p['price'] * $p['quantity'];
                $total = $p['price'] * $p['quantity'] - $discount;
            ?>
                <tr class="table-success">
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= $p['barcode'] ?></td>
                    <td><?= $p['quantity'] ?></td>
                    <td>₨<?= number_format($p['price'], 2) ?></td>
                    <td><?= $p['discount_percent'] ?? 0 ?>%</td>
                    <td><strong>₨<?= number_format($total, 2) ?></strong></td>
                    <td><?= date('d-M-Y', strtotime($p['paid_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="alert alert-secondary">No payments recorded yet.</div>
        <?php endif; ?>

        <!-- Print & Export -->
        <div class="mt-4">
            <button onclick="window.print()" class="btn btn-outline-dark">🖨 Print Invoice</button>
            <a href="export_supplier_payments.php?supplier_id=<?= $supplier_id ?>" class="btn btn-outline-primary">⬇ Export CSV</a>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-outline-secondary mt-4">← Back</a>
</div>
</body>
</html>
